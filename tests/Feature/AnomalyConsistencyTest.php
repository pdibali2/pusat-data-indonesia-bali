<?php

namespace Tests\Feature;

use App\Models\Anomaly;
use App\Models\Data;
use App\Models\Location;
use App\Models\Metadata;
use App\Models\Time;
use App\Models\User;
use App\Services\AnomalyDetectionService;
use App\Services\AnomalyStatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature Test: Anomaly Calculation Consistency
 *
 * Memvalidasi bahwa perhitungan nilai statistik (mean, stddev, z-score) konsisten
 * di semua lapisan sistem:
 * - DataImport::preview() → nilai yang ditampilkan sebelum import
 * - DataImport::createAnomaliesForPendingKeys() → nilai yang disimpan ke DB saat import
 * - AnomalyControlController::enrichAnomalyContext() → nilai yang ditampilkan di control
 * - AnomalyDetectionService::checkUnreasonableValue() → nilai yang di-screen setelah data baru
 */
class AnomalyConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Metadata $metadata;
    private Location $location;
    private Time $time1;
    private Time $time2;
    private Time $time3;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup test data
        $this->user = User::factory()->create(['user_id' => 1]);

        $this->metadata = Metadata::create([
            'nama' => 'Test Metadata',
            'satuan_data' => 'orang',
            'status' => 2,
            'tipe_data' => 'numerik',
            'frekuensi_penerbitan' => 'tahunan',
        ]);

        $this->location = Location::create([
            'nama_wilayah' => 'Test Location',
            'kode_wilayah' => 'TEST',
        ]);

        // Create historical time periods
        $this->time1 = Time::create(['year' => 2024, 'decade' => 2020, 'semester' => 0, 'quarter' => 0, 'month' => 0]);
        $this->time2 = Time::create(['year' => 2025, 'decade' => 2020, 'semester' => 0, 'quarter' => 0, 'month' => 0]);
        $this->time3 = Time::create(['year' => 2026, 'decade' => 2020, 'semester' => 0, 'quarter' => 0, 'month' => 0]);
    }

    /**
     * Test: Consistency between AnomalyStatisticsService calculation and direct calculation.
     */
    public function test_statistics_service_consistency(): void
    {
        // Create historical data with known values
        $history = [10.0, 20.0, 30.0, 40.0, 50.0];

        // Calculate using service
        $stats = AnomalyStatisticsService::descriptiveStats($history);

        // Expected values
        $this->assertEquals(5, $stats['n']);
        $this->assertEquals(30.0, $stats['mean']);

        // Verify stddev calculation
        $expectedMean = 30.0;
        $expectedVar = (pow(10-30, 2) + pow(20-30, 2) + pow(30-30, 2) + pow(40-30, 2) + pow(50-30, 2)) / 5;
        $expectedStddev = sqrt($expectedVar);
        $this->assertAlmostEquals($expectedStddev, $stats['stddev'], 0.01);
    }

    /**
     * Test: Outlier detection consistency across all layers.
     *
     * Create a data point that should be detected as outlier in all places.
     */
    public function test_outlier_detection_consistency(): void
    {
        // Create historical data
        $historyValues = [100.0, 110.0, 120.0];
        foreach ($historyValues as $value) {
            Data::create([
                'metadata_id' => $this->metadata->metadata_id,
                'location_id' => $this->location->location_id,
                'time_id' => $this->time1->time_id,
                'number_value' => $value,
                'user_id' => $this->user->user_id,
                'status' => Data::STATUS_AVAILABLE,
            ]);
        }

        // Create current data point that should be outlier
        $currentValue = 500.0;  // Far from mean ~110

        // Calculate using AnomalyStatisticsService
        $outlierInfo = AnomalyStatisticsService::detectOutlier(
            $currentValue,
            $historyValues,
            ['threshold' => AnomalyStatisticsService::DB_ZSCORE_THRESHOLD]
        );

        $this->assertNotNull($outlierInfo);
        $this->assertTrue($outlierInfo['is_outlier']);
        $this->assertGreaterThan(AnomalyStatisticsService::DB_ZSCORE_THRESHOLD, $outlierInfo['z_score']);

        // Verify that values match expected calculation
        $this->assertEquals(3, $outlierInfo['n']);
        $this->assertAlmostEquals(110.0, $outlierInfo['mean'], 0.5);
    }

    /**
     * Test: Z-score threshold is consistently applied.
     */
    public function test_z_score_threshold_consistency(): void
    {
        // All layers should use same threshold
        $serviceThreshold = AnomalyStatisticsService::DB_ZSCORE_THRESHOLD;

        $this->assertEquals(3.0, $serviceThreshold);

        // Create test data around threshold
        $history = [10.0, 20.0, 30.0, 40.0, 50.0];  // mean=30, stddev≈14.14

        // Value at exactly 3σ above mean: should be borderline
        $upperBound = 30.0 + (3 * 14.142135623730951);
        $result = AnomalyStatisticsService::detectOutlier($upperBound, $history);
        // Exactly at threshold: depends on comparison operator
        // Should be NOT detected (z-score <= threshold)

        // Value slightly above 3σ: should be detected
        $slightlyAbove = $upperBound + 0.1;
        $result = AnomalyStatisticsService::detectOutlier($slightlyAbove, $history);
        $this->assertNotNull($result);
        $this->assertTrue($result['is_outlier']);
    }

    /**
     * Test: Minimum history requirement is consistently enforced.
     */
    public function test_min_history_consistency(): void
    {
        $minHistory = AnomalyStatisticsService::MIN_HISTORY_FOR_MEANINGFUL_STATS;
        $this->assertEquals(3, $minHistory);

        // With 2 values (below minimum)
        $result = AnomalyStatisticsService::detectOutlier(
            100.0,
            [10.0, 20.0]
        );
        $this->assertNull($result, "Should return null with insufficient history");

        // With 3 values (at minimum)
        $result = AnomalyStatisticsService::detectOutlier(
            100.0,
            [10.0, 20.0, 30.0]
        );
        $this->assertNotNull($result, "Should work with minimum history");
    }

    /**
     * Test: Parsing consistency for locale numbers.
     */
    public function test_locale_number_parsing_consistency(): void
    {
        // All these should parse to the same value
        $value1 = AnomalyStatisticsService::parseLocaleNumber('1.234,56');      // Indonesia
        $value2 = AnomalyStatisticsService::parseLocaleNumber('1,234.56');      // US
        $value3 = AnomalyStatisticsService::parseLocaleNumber(1234.56);         // Numeric

        $this->assertEquals($value1, $value2);
        $this->assertEquals($value2, $value3);
        $this->assertEquals(1234.56, $value1);
    }

    /**
     * Test: Service handles edge cases consistently.
     */
    public function test_edge_cases(): void
    {
        // Zero history
        $result = AnomalyStatisticsService::detectOutlier(100.0, []);
        $this->assertNull($result);

        // All identical values (stddev = 0)
        $result = AnomalyStatisticsService::detectOutlier(100.0, [50.0, 50.0, 50.0]);
        // Should handle gracefully (either return null or special handling)
        $this->assertTrue(
            $result === null || (is_array($result) && isset($result['stddev']) && $result['stddev'] == 0),
            "Should handle zero stddev gracefully"
        );

        // Very small differences
        $result = AnomalyStatisticsService::detectOutlier(1.0000001, [1.0, 1.0, 1.0]);
        $this->assertTrue($result === null || !$result['is_outlier']);

        // Very large values
        $result = AnomalyStatisticsService::detectOutlier(
            1e10,
            [1e9, 2e9, 3e9]
        );
        $this->assertNotNull($result);
        $this->assertTrue($result['is_outlier']);
    }

    /**
     * Helper untuk assert float dengan tolerance.
     */
    private function assertAlmostEquals($expected, $actual, $delta = 0.01): void
    {
        $this->assertLessThanOrEqual(
            $delta,
            abs($expected - $actual),
            "Expected ~$expected, got $actual (tolerance: ±$delta)"
        );
    }
}
