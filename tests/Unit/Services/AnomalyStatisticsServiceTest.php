<?php

namespace Tests\Unit\Services;

use App\Services\AnomalyStatisticsService;
use PHPUnit\Framework\TestCase;

/**
 * Test untuk AnomalyStatisticsService
 *
 * Memvalidasi:
 * - Parsing numerik dengan lokalisasi Indonesia
 * - Perhitungan mean, median, stddev
 * - Perhitungan z-score
 * - Deteksi outlier
 */
class AnomalyStatisticsServiceTest extends TestCase
{
    /**
     * Test parsing nilai numerik dari berbagai format.
     */
    public function test_parse_locale_number_various_formats(): void
    {
        // Format Indonesia (koma = desimal, titik = pemisah ribuan)
        $this->assertEquals(1234.56, AnomalyStatisticsService::parseLocaleNumber('1.234,56'));

        // Format US (titik = desimal)
        $this->assertEquals(1234.56, AnomalyStatisticsService::parseLocaleNumber('1,234.56'));
        $this->assertEquals(1234.56, AnomalyStatisticsService::parseLocaleNumber('1234.56'));

        // Hanya koma (assume 1-2 digit = desimal)
        $this->assertEquals(123.45, AnomalyStatisticsService::parseLocaleNumber('123,45'));

        // Numeric
        $this->assertEquals(1234.56, AnomalyStatisticsService::parseLocaleNumber(1234.56));
        $this->assertEquals(1234.0, AnomalyStatisticsService::parseLocaleNumber(1234));

        // Spasi (hapus)
        $this->assertEquals(1234.56, AnomalyStatisticsService::parseLocaleNumber('1.234 , 56'));
    }

    /**
     * Test mean calculation.
     */
    public function test_mean(): void
    {
        $values = [10.0, 20.0, 30.0];
        $this->assertEquals(20.0, AnomalyStatisticsService::mean($values));

        $values = [1.5, 2.5, 3.5, 4.5];
        $this->assertEquals(3.0, AnomalyStatisticsService::mean($values));

        $values = [];
        $this->assertEquals(0.0, AnomalyStatisticsService::mean($values));
    }

    /**
     * Test median calculation.
     */
    public function test_median(): void
    {
        // Odd count
        $values = [10.0, 30.0, 20.0];
        $this->assertEquals(20.0, AnomalyStatisticsService::median($values));

        // Even count
        $values = [10.0, 20.0, 30.0, 40.0];
        $this->assertEquals(25.0, AnomalyStatisticsService::median($values));

        // Single value
        $values = [42.0];
        $this->assertEquals(42.0, AnomalyStatisticsService::median($values));

        // Empty
        $values = [];
        $this->assertEquals(0.0, AnomalyStatisticsService::median($values));
    }

    /**
     * Test standard deviation calculation.
     */
    public function test_stddev(): void
    {
        // Known values: mean=3, values=[1,2,3,4,5]
        // variance = ((1-3)^2 + (2-3)^2 + (3-3)^2 + (4-3)^2 + (5-3)^2) / 5
        //         = (4 + 1 + 0 + 1 + 4) / 5 = 10/5 = 2
        // stddev = sqrt(2) ≈ 1.414
        $values = [1.0, 2.0, 3.0, 4.0, 5.0];
        $mean = 3.0;
        $expected = sqrt(2.0);
        $this->assertAlmostEquals($expected, AnomalyStatisticsService::stdDev($values, $mean), 0.001);

        // Zero stddev (all identical)
        $values = [5.0, 5.0, 5.0, 5.0];
        $this->assertEquals(0.0, AnomalyStatisticsService::stdDev($values, 5.0));

        // Single value should return 0
        $values = [42.0];
        $this->assertEquals(0.0, AnomalyStatisticsService::stdDev($values));
    }

    /**
     * Test z-score calculation.
     */
    public function test_z_score(): void
    {
        // z = |value - mean| / stddev
        $value = 10.0;
        $mean = 5.0;
        $stddev = 2.0;
        // z = |10 - 5| / 2 = 2.5
        $this->assertEquals(2.5, AnomalyStatisticsService::zScore($value, $mean, $stddev));

        // Zero stddev should return null
        $value = 10.0;
        $mean = 5.0;
        $stddev = 0.0;
        $this->assertNull(AnomalyStatisticsService::zScore($value, $mean, $stddev));

        // Negative value
        $value = 1.0;
        $mean = 5.0;
        $stddev = 2.0;
        // z = |1 - 5| / 2 = 2.0
        $this->assertEquals(2.0, AnomalyStatisticsService::zScore($value, $mean, $stddev));
    }

    /**
     * Test outlier detection for normal case.
     */
    public function test_detect_outlier_normal(): void
    {
        // History: [10, 20, 30, 40, 50]
        // mean=30, stddev=14.14
        // Values: 30 ± 3*stddev ≈ 30 ± 42.43 = [-12.43, 72.43]
        $history = [10.0, 20.0, 30.0, 40.0, 50.0];

        // Value within range should NOT be outlier
        $currentValue = 35.0;
        $result = AnomalyStatisticsService::detectOutlier($currentValue, $history);
        $this->assertNull($result);

        // Value far outside range should be outlier
        $currentValue = 100.0;
        $result = AnomalyStatisticsService::detectOutlier($currentValue, $history);
        $this->assertNotNull($result);
        $this->assertTrue($result['is_outlier']);
        $this->assertGreaterThan(3, $result['z_score']);
    }

    /**
     * Test outlier detection with insufficient history.
     */
    public function test_detect_outlier_insufficient_history(): void
    {
        // Only 2 values, need 3 minimum
        $history = [10.0, 20.0];
        $currentValue = 100.0;

        $result = AnomalyStatisticsService::detectOutlier($currentValue, $history);
        $this->assertNull($result);
    }

    /**
     * Test descriptive stats.
     */
    public function test_descriptive_stats(): void
    {
        $values = [10.0, 20.0, 30.0, 40.0, 50.0];
        $stats = AnomalyStatisticsService::descriptiveStats($values);

        $this->assertEquals(5, $stats['n']);
        $this->assertEquals(30.0, $stats['mean']);
        $this->assertEquals(30.0, $stats['median']);
        $this->assertEquals(10.0, $stats['min']);
        $this->assertEquals(50.0, $stats['max']);
        $this->assertGreaterThan(0, $stats['stddev']);
        $this->assertIsFloat($stats['stddev']);
    }

    /**
     * Test minimum history enforcement.
     */
    public function test_min_history_constants(): void
    {
        $this->assertEquals(3, AnomalyStatisticsService::MIN_HISTORY_FOR_MEANINGFUL_STATS);
        $this->assertEquals(3.0, AnomalyStatisticsService::DB_ZSCORE_THRESHOLD);
    }

    /**
     * Helper untuk assert float dengan tolerance.
     */
    private function assertAlmostEquals($expected, $actual, $delta = 0.01): void
    {
        $this->assertLessThanOrEqual($delta, abs($expected - $actual),
            "Expected $expected, got $actual (delta: $delta)");
    }
}
