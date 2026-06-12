<?php

namespace App\Services;

use App\Models\Anomaly;
use App\Models\AnomalyRule;
use App\Models\Data;
use App\Models\Metadata;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class AnomalyDetectionService
{
    public function __construct(
        private readonly AuditTrailService $auditTrail,
    ) {}

    // ══════════════════════════════════════════════════════════
    // ENTRY POINT — dipanggil setelah data disimpan
    // ══════════════════════════════════════════════════════════

    /**
     * Jalankan semua pemeriksaan anomali untuk satu record data.
     * Dipanggil dari DataController::store() dan DataImport.
     *
     * @return array{anomalies_found: bool, anomalies: Anomaly[], workflow_status: string}
     */
    public function screenData(Data $data, array $enabledChecks = ['percentage_change', 'source_conflict', 'unreasonable_value']): array
    {
        $data->loadMissing(['metadata', 'time', 'location']);

        $detected = [];

        // 1. Cek kenaikan / penurunan ekstrem vs histori
        if (in_array('percentage_change', $enabledChecks, true)) {
            $changeAnomaly = $this->checkPercentageChange($data);
            if ($changeAnomaly) $detected[] = $changeAnomaly;
        }

        // 2. Cek konflik antar sumber (data metadata+lokasi+waktu yang sama, produsen beda)
        if (in_array('source_conflict', $enabledChecks, true)) {
            $conflictAnomaly = $this->checkSourceConflict($data);
            if ($conflictAnomaly) $detected[] = $conflictAnomaly;
        }

        // 3. Cek nilai tidak wajar (outlier statistik dari histori)
        if (in_array('unreasonable_value', $enabledChecks, true)) {
            $unreasonableAnomaly = $this->checkUnreasonableValue($data);
            if ($unreasonableAnomaly) $detected[] = $unreasonableAnomaly;
        }

        // Update workflow_status berdasarkan hasil screening
        $workflowStatus = empty($detected)
            ? Data::WORKFLOW_DRAFT
            : Data::WORKFLOW_WARNING;

        $data->update(['workflow_status' => $workflowStatus]);

        // Catat ke audit trail
        $this->auditTrail->recordScreened('data', $data->id, [
            'anomalies_found' => count($detected),
            'workflow_status' => $workflowStatus,
            'checks_run'      => $enabledChecks,
        ]);

        return [
            'anomalies_found' => !empty($detected),
            'anomalies'       => $detected,
            'workflow_status' => $workflowStatus,
        ];
    }

    // ══════════════════════════════════════════════════════════
    // CHECK 1 — KENAIKAN / PENURUNAN EKSTREM
    // ══════════════════════════════════════════════════════════

    /**
     * Bandingkan nilai data saat ini dengan nilai historis sebelumnya
     * pada metadata + lokasi yang sama, periode sebelumnya.
     */
    private function checkPercentageChange(Data $data): ?Anomaly
    {
        if ($data->number_value === null) return null;

        $previousData = $this->getPreviousPeriodData($data);
        if (!$previousData || $previousData->number_value === null) return null;

        $prev    = (float) $previousData->number_value;
        $current = (float) $data->number_value;

        // Hindari pembagian nol
        if ($prev == 0) {
            // Nilai dari 0 ke angka — langsung critical
            if ($current != 0) {
                return $this->createAnomaly($data, [
                    'anomaly_type'      => Anomaly::TYPE_EXTREME_INCREASE,
                    'severity'          => Anomaly::SEVERITY_CRITICAL,
                    'previous_value'    => $prev,
                    'current_value'     => $current,
                    'percentage_change' => null,
                    'message'           => "Nilai berubah dari 0 menjadi {$current}. "
                                        . "Tidak dapat menghitung persentase (pembagi nol).",
                ]);
            }
            return null;
        }

        $percentageChange = (($current - $prev) / abs($prev)) * 100;

        // Ambil threshold sesuai metadata & frekuensi
        $frekuensi = strtolower($data->metadata?->frekuensi_penerbitan ?? '');
        $rule      = AnomalyRule::resolveForData((int) $data->metadata_id, $frekuensi);

        $tLow      = (float) ($rule->threshold_low      ?? 20);
        $tMedium   = (float) ($rule->threshold_medium   ?? 100);
        $tHigh     = (float) ($rule->threshold_high     ?? 500);
        $tCritical = (float) ($rule->threshold_critical ?? 5000);

        $absChange = abs($percentageChange);

        // Tidak anomali jika perubahan di bawah threshold low
        if ($absChange < $tLow) return null;

        // Hitung severity
        $severity = match (true) {
            $absChange >= $tCritical => Anomaly::SEVERITY_CRITICAL,
            $absChange >= $tHigh     => Anomaly::SEVERITY_HIGH,
            $absChange >= $tMedium   => Anomaly::SEVERITY_MEDIUM,
            default                  => Anomaly::SEVERITY_LOW,
        };

        $type    = $percentageChange > 0 ? Anomaly::TYPE_EXTREME_INCREASE : Anomaly::TYPE_EXTREME_DECREASE;
        $arah    = $percentageChange > 0 ? 'naik' : 'turun';
        $periode = $this->formatPeriodeLabel($data);
        $prevPeriode = $this->formatPeriodeLabel($previousData);

        return $this->createAnomaly($data, [
            'anomaly_type'      => $type,
            'severity'          => $severity,
            'previous_value'    => $prev,
            'current_value'     => $current,
            'percentage_change' => $percentageChange,
            'message'           => "Nilai {$arah} {$absChange}% dari {$prev} ({$prevPeriode}) "
                                 . "menjadi {$current} ({$periode}). "
                                 . "Threshold {$severity}: ≥{$this->getThresholdForSeverity($severity, $rule)}%.",
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // CHECK 2 — KONFLIK ANTAR SUMBER DATA
    // ══════════════════════════════════════════════════════════

    /**
     * Cek apakah metadata + lokasi + waktu yang sama memiliki nilai berbeda
     * dari produsen yang berbeda.
     */
    private function checkSourceConflict(Data $data): ?Anomaly
    {
        if ($data->number_value === null) return null;

        // Cari data lain dengan metadata + lokasi + waktu sama tapi produsen beda
        $conflicts = Data::where('metadata_id', $data->metadata_id)
            ->where('location_id', $data->location_id)
            ->where('time_id', $data->time_id)
            ->where('id', '!=', $data->id)
            ->where('status', Data::STATUS_AVAILABLE)
            ->whereNotNull('number_value')
            ->with('produsen')
            ->get();

        if ($conflicts->isEmpty()) return null;

        // Hitung selisih terbesar
        $maxDiff        = 0;
        $conflictSource = null;
        $conflictValue  = null;

        foreach ($conflicts as $conflict) {
            $diff = abs((float) $data->number_value - (float) $conflict->number_value);
            if ($diff > $maxDiff) {
                $maxDiff        = $diff;
                $conflictSource = $conflict->produsen?->nama_produsen ?? "Produsen #{$conflict->produsen_id}";
                $conflictValue  = (float) $conflict->number_value;
            }
        }

        if ($maxDiff == 0) return null;

        // Hitung persentase selisih relatif terhadap nilai saat ini
        $current = (float) $data->number_value;
        $pctDiff = $current > 0 ? ($maxDiff / $current) * 100 : 0;

        $frekuensi = strtolower($data->metadata?->frekuensi_penerbitan ?? '');
        $severity  = Anomaly::calculateSeverity($pctDiff, (int) $data->metadata_id, $frekuensi);

        $mySource = $data->produsen?->nama_produsen ?? "Produsen #{$data->produsen_id}";

        return $this->createAnomaly($data, [
            'anomaly_type'      => Anomaly::TYPE_SOURCE_CONFLICT,
            'severity'          => $severity,
            'previous_value'    => $conflictValue,
            'current_value'     => $current,
            'percentage_change' => $pctDiff,
            'message'           => "Konflik sumber data: nilai dari {$mySource} ({$current}) "
                                 . "berbeda {$maxDiff} dengan {$conflictSource} ({$conflictValue}). "
                                 . "Selisih relatif: " . number_format($pctDiff, 2) . "%.",
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // CHECK 3 — NILAI TIDAK WAJAR (OUTLIER STATISTIK)
    // ══════════════════════════════════════════════════════════

    /**
     * Bandingkan dengan mean ± 3 standar deviasi dari histori metadata + lokasi.
     * Jika nilai berada di luar batas, dianggap tidak wajar.
     */
    private function checkUnreasonableValue(Data $data): ?Anomaly
    {
        if ($data->number_value === null) return null;

        // Ambil histori: metadata + lokasi yang sama (gunakan AnomalyStatisticsService untuk min history)
        // Gunakan ONLY STATUS_AVAILABLE untuk konsistensi dengan DataImport & AnomalyControlController
        $history = Data::where('metadata_id', $data->metadata_id)
            ->where('location_id', $data->location_id)
            ->where('id', '!=', $data->id)
            ->where('status', Data::STATUS_AVAILABLE)
            ->whereNotNull('number_value')
            ->orderBy('time_id', 'desc')
            ->limit(20)
            ->pluck('number_value')
            ->map(fn($v) => (float) $v)
            ->toArray();

        // Gunakan AnomalyStatisticsService untuk deteksi konsisten di semua lapisan
        $outlierInfo = AnomalyStatisticsService::detectOutlier(
            (float) $data->number_value,
            $history,
            [
                'min_history' => AnomalyStatisticsService::MIN_HISTORY_FOR_MEANINGFUL_STATS,
                'threshold'   => AnomalyStatisticsService::DB_ZSCORE_THRESHOLD,
            ]
        );

        if ($outlierInfo === null) return null;

        $severity = match (true) {
            $outlierInfo['z_score'] >= 6 => Anomaly::SEVERITY_CRITICAL,
            $outlierInfo['z_score'] >= 5 => Anomaly::SEVERITY_HIGH,
            $outlierInfo['z_score'] >= 4 => Anomaly::SEVERITY_MEDIUM,
            default                       => Anomaly::SEVERITY_LOW,
        };

        $mean      = $outlierInfo['mean'];
        $stddev    = $outlierInfo['stddev'];
        $zScore    = $outlierInfo['z_score'] ?? 0;
        $current   = (float) $data->number_value;
        $lowerBound = $outlierInfo['lower_bound'];
        $upperBound = $outlierInfo['upper_bound'];

        return $this->createAnomaly($data, [
            'anomaly_type'      => Anomaly::TYPE_UNREASONABLE,
            'severity'          => $severity,
            'previous_value'    => $mean,
            'current_value'     => $current,
            'percentage_change' => $zScore,   // gunakan z-score sebagai "% change"
            'message'           => sprintf(
                "Nilai %s berada di luar batas wajar. "
                . "Rata-rata histori: %s, StdDev: %s, Batas: [%s – %s], n=%d. "
                . "Z-score: %.2f (ambang batas: 3.0).",
                $current,
                $mean,
                $stddev,
                $lowerBound,
                $upperBound,
                $outlierInfo['n'],
                $zScore
            ),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // BATCH SCREENING — untuk data historis / Command
    // ══════════════════════════════════════════════════════════

    /**
     * Scan semua data existing yang belum pernah di-screen.
     * Dipanggil dari Artisan Command: anomaly:scan
     *
     * @param  int       $batchSize    Jumlah record per batch
     * @param  callable  $onProgress   Callback progress (dipanggil setiap batch)
     * @return array{scanned: int, anomalies_found: int, skipped: int}
     */
    public function scanExistingData(int $batchSize = 100, bool $scanAll = false, ?int $metadataId = null, array $enabledChecks = ['percentage_change', 'source_conflict', 'unreasonable_value'], ?callable $onProgress = null): array
    {
        $scanned        = 0;
        $anomaliesFound = 0;
        $skipped        = 0;

        Data::where('status', Data::STATUS_AVAILABLE)
            ->whereNotNull('number_value')
            ->when(!$scanAll, fn($query) => $query->where('workflow_status', Data::WORKFLOW_DRAFT))
            ->when($metadataId, fn($q) => $q->where('metadata_id', $metadataId))
            ->with(['metadata', 'time', 'location'])
            ->chunkById($batchSize, function ($chunk) use (
                &$scanned, &$anomaliesFound, &$skipped, $enabledChecks, $onProgress
            ) {
                foreach ($chunk as $data) {
                    try {
                        $result = $this->screenData($data, $enabledChecks);
                        $scanned++;
                        if ($result['anomalies_found']) $anomaliesFound++;
                    } catch (\Throwable $e) {
                        $skipped++;
                        // Log error tanpa menghentikan proses batch
                        logger()->error("AnomalyDetection::scanExistingData gagal pada data#{$data->id}: " . $e->getMessage());
                    }
                }

                if ($onProgress) {
                    $onProgress($scanned, $anomaliesFound);
                }
            });

        return compact('scanned', 'anomaliesFound', 'skipped');
    }

    // ══════════════════════════════════════════════════════════
    // PERBANDINGAN ANTAR SUMBER — untuk halaman Control
    // ══════════════════════════════════════════════════════════

    /**
     * Ambil semua nilai dari berbagai produsen untuk
     * metadata + lokasi + waktu yang sama.
     * Digunakan untuk tabel perbandingan di halaman Control.
     *
     * @return Collection<array{produsen: string, value: float, selisih: float, status: string}>
     */
    public function compareSourceValues(int $metadataId, int $locationId, int $timeId): Collection
    {
        $rows = Data::where('metadata_id', $metadataId)
            ->where('location_id', $locationId)
            ->where('time_id', $timeId)
            ->whereNotNull('number_value')
            ->with('produsen')
            ->get();

        if ($rows->isEmpty()) return collect();

        // Hitung rata-rata sebagai baseline
        $avg = $rows->avg('number_value');

        return $rows->map(function (Data $d) use ($avg) {
            $value   = (float) $d->number_value;
            $selisih = $value - $avg;
            $pctDiff = $avg > 0 ? abs($selisih / $avg) * 100 : 0;

            return [
                'data_id'       => $d->id,
                'produsen_id'   => $d->produsen_id,
                'produsen'      => $d->produsen?->nama_produsen ?? "Produsen #{$d->produsen_id}",
                'value'         => $value,
                'avg_baseline'  => round($avg, 4),
                'selisih'       => round($selisih, 4),
                'pct_diff'      => round($pctDiff, 2),
                'conflict'      => $pctDiff >= 5,  // flag konflik jika selisih > 5%
                'workflow'      => $d->workflow_status,
            ];
        });
    }

    // ══════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════

    /**
     * Ambil data periode sebelumnya (metadata + lokasi + produsen sama).
     * Urutan periode: time_id lebih kecil = periode lebih lama.
     */
    private function getPreviousPeriodData(Data $data): ?Data
    {
        return Data::where('metadata_id', $data->metadata_id)
            ->where('location_id', $data->location_id)
            ->where('produsen_id', $data->produsen_id)
            ->where('time_id', '<', $data->time_id)
            ->where('status', Data::STATUS_AVAILABLE)
            ->whereNotNull('number_value')
            ->orderBy('time_id', 'desc')
            ->first();
    }

    /**
     * Buat dan simpan record Anomaly ke database.
     */
    private function createAnomaly(Data $data, array $attributes): Anomaly
    {
        $existing = Anomaly::where('id', $data->id)
            ->where('table_name', 'data')
            ->where('anomaly_type', $attributes['anomaly_type'])
            ->orderBy('anomalies_id')
            ->get();

        if ($existing->isNotEmpty()) {
            $first = $existing->shift();
            if ($existing->isNotEmpty()) {
                Anomaly::where('id', $data->id)
                    ->where('table_name', 'data')
                    ->where('anomaly_type', $attributes['anomaly_type'])
                    ->where('anomalies_id', '!=', $first->anomalies_id)
                    ->delete();
            }
            return $first;
        }

        return Anomaly::create(array_merge([
            'id'          => $data->id,   // FK ke data.id
            'table_name'  => 'data',
            'status'      => Anomaly::STATUS_WARNING,
            'detected_at' => now(),
        ], $attributes));
    }

    /**
     * Format label periode dari relasi time untuk pesan anomali.
     */
    private function formatPeriodeLabel(Data $data): string
    {
        $t = $data->time;
        if (!$t) return "time_id:{$data->time_id}";

        if ($t->month  != 0) return "{$t->year}/Bulan-{$t->month}";
        if ($t->quarter != 0) return "{$t->year}/Q{$t->quarter}";
        if ($t->semester != 0) return "{$t->year}/S{$t->semester}";
        if ($t->year    != 0) return (string) $t->year;
        return "Dekade-{$t->decade}";
    }

    /**
     * Ambil nilai threshold untuk severity tertentu dari rule.
     */
    private function getThresholdForSeverity(string $severity, AnomalyRule $rule): float
    {
        return match ($severity) {
            Anomaly::SEVERITY_LOW      => (float) $rule->threshold_low,
            Anomaly::SEVERITY_MEDIUM   => (float) $rule->threshold_medium,
            Anomaly::SEVERITY_HIGH     => (float) $rule->threshold_high,
            Anomaly::SEVERITY_CRITICAL => (float) $rule->threshold_critical,
            default                    => 0,
        };
    }
}