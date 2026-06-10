<?php

namespace App\Imports;

use App\Models\Anomaly;
use App\Models\Data;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DataImport
{
    // ── Konfigurasi ──────────────────────────────────────────
    private const HEADER_ROW  = 3;
    private const DATA_ROW    = 4;
    private const BATCH_SIZE  = 200;
    private const COL_META_ID = 0;
    private const COL_META_NM = 1;
    private const COL_LOC_ID  = 2;
    private const COL_LOC_NM  = 3;
    private const COL_RUJUKAN = 4;
    private const COL_PERIOD  = 5;
    

    /**
     * Threshold Modified Z-Score untuk deteksi outlier.
     *
     * Nilai standar industri: 3.5
     * Semakin kecil → semakin sensitif.
     * Referensi: Iglewicz & Hoaglin (1993), "How to Detect and Handle Outliers"
     */
    // Threshold standard z-score untuk deteksi outlier vs DB historis.
    // Konsisten dengan AnomalyDetectionService::checkUnreasonableValue() yang pakai z > 3.
    private const DB_ZSCORE_THRESHOLD = 3.0;
    
    // Minimum data historis DB agar z-score bermakna (konsisten dengan enrichAnomalyContext).
    private const DB_HISTORY_MIN_ROWS = 3;

    private const OUTLIER_MZSCORE_THRESHOLD      = 3.5;   // base threshold (sama)
    private const OUTLIER_COV_SCALE_FACTOR       = 0.8;   // pengali CoV ke threshold
    private const OUTLIER_MIN_COV_TO_SCALE       = 0.6;   // CoV minimum sebelum scaling aktif

    private const OUTLIER_MIN_ROWS_FOR_SCALING  = 3;
    private const OUTLIER_MIN_PCT_FROM_MEDIAN   = 0.50;
    private const OUTLIER_NEAR_ZERO_THRESHOLD   = 5.0;
    private const OUTLIER_NEAR_ZERO_MIN_ABS     = 10.0;

    private const BULAN_MAP = [
        'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'mei' => 5, 'jun' => 6,
        'jul' => 7, 'agu' => 8, 'aug' => 8, 'sep' => 9, 'okt' => 10, 'oct' => 10,
        'nov' => 11, 'des' => 12, 'dec' => 12,
    ];

    // ── State ─────────────────────────────────────────────────
    private int   $userId;
    private bool  $skipDuplicates;
    private array $errors          = [];
    private array $duplicates      = [];
    private array $invalid_metadata = [];
    private array $outliers        = [];   // ← BARU: baris yang terdeteksi outlier
    private int   $imported        = 0;
    private int   $skipped         = 0;
    private array $pendingAnomalyKeys = [];
    private int   $anomaliesCreated   = 0;

    private array $timeCache    = [];
    private array $existingSet  = [];
    private array $metadataCache = [];
    private array $rujukanCache  = [];

    public function __construct(int $userId = 0, bool $skipDuplicates = true)
    {
        $this->userId         = $userId ?: (Auth::check() ? Auth::user()->user_id : 0);
        $this->skipDuplicates = $skipDuplicates;
    }

    // ══════════════════════════════════════════════════════════
    // ENTRY POINT — PREVIEW
    // ══════════════════════════════════════════════════════════

    public function preview(string $filePath): array
    {
        [$periodCols, $dataRows, $rujukanColIndex] = $this->readExcel($filePath);
    
        $this->preloadMetadataCache($dataRows);
        $this->buildExistingSet($periodCols);
        $this->preloadRujukanCache($dataRows, $rujukanColIndex);
    
        // ── Parse semua baris dulu ─────────────────────────────────────────────
        $previewRows      = [];
        $errors           = [];
        $duplicates       = [];
        $invalid_metadata = [];
    
        foreach ($dataRows as $rowNum => $row) {
            $result = $this->parseRow(
                $row, $periodCols, $rowNum,
                dryRun: true,
                rujukanColIndex: $rujukanColIndex
            );
    
            foreach ($result['records'] as $rec) {
                // Default: belum diperiksa outlier
                $rec['is_outlier']   = false;
                $rec['outlier_info'] = null;
                $rec['include']      = true;
    
                $key = "{$rec['metadata_id']}_{$rec['location_id']}_{$rec['time_id']}_{$rec['rujukan_id']}";
    
                if (isset($this->existingSet[$key])) {
                    $duplicates[] = array_merge($rec, ['row' => $rowNum]);
                } else {
                    $previewRows[] = array_merge($rec, ['row' => $rowNum]);
                }
            }
    
            foreach ($result['errors'] as $err) {
                $errors[] = array_merge($err, ['row' => $rowNum]);
            }
            foreach ($result['invalid_metadata'] as $inv) {
                $invalid_metadata[] = array_merge($inv, ['row' => $rowNum]);
            }
        }
    
        // ── Deteksi outlier berbasis DB (batch per metadata+location) ──────────
        // Ini menghasilkan nilai yang SAMA PERSIS dengan yang akan disimpan
        // di tabel anomalies oleh createAnomaliesForPendingKeys().
        $previewRows = $this->detectOutliersViaDb($previewRows);
        $previewRows = $this->detectOutliersIntraSeries($previewRows);
    
        // ── Kumpulkan outlier untuk return value ───────────────────────────────
        $outliers = array_filter($previewRows, fn($r) => $r['is_outlier']);
        $outliers = array_values($outliers);
    
        // Deduplikasi invalid_metadata
        $seenMetaIds   = [];
        $uniqueInvalid = [];
        foreach ($invalid_metadata as $inv) {
            $mid = $inv['metadata_id'] ?? null;
            if ($mid !== null && !isset($seenMetaIds[$mid])) {
                $seenMetaIds[$mid] = true;
                $uniqueInvalid[]   = $inv;
            }
        }
    
        return [
            'success'           => true,
            'rows'              => $previewRows,
            'errors'            => $errors,
            'duplicates'        => $duplicates,
            'invalid_metadata'  => $uniqueInvalid,
            'outliers'          => $outliers,
            'total_rows'        => count($dataRows),
            'valid'             => count($previewRows),
            'duplicate'         => count($duplicates),
            'error'             => count($errors),
            'outlier_count'     => count($outliers),
            'invalid_meta_count'=> count($uniqueInvalid),
            'period_type'       => $this->detectPeriodType($periodCols[0] ?? ''),
            'period_cols'       => $periodCols,
        ];
    }

    private function detectOutliersIntraSeries(array $previewRows): array
    {
        // Group semua nilai per metadata+location — susun sebagai "baris lintas periode"
        $seriesMap = [];
        foreach ($previewRows as $idx => $rec) {
            if ($rec['is_outlier']) continue;
            $key = "{$rec['metadata_id']}_{$rec['location_id']}";
            $seriesMap[$key][] = ['idx' => $idx, 'value' => (float) $rec['number_value']];
        }

        foreach ($seriesMap as $members) {
            if (count($members) < 3) continue;

            $values  = array_column($members, 'value');
            $median  = $this->median($values);
            $mzScores = $this->calculateModifiedZScores($values);

            foreach ($members as $i => $m) {
                $mz = abs($mzScores[$i]);
                if ($mz <= self::OUTLIER_MZSCORE_THRESHOLD) continue;

                // Minimum deviation guard (sama dengan detectOutliersInRows)
                $absDeviation = abs($m['value'] - $median);
                if (abs($median) < self::OUTLIER_NEAR_ZERO_THRESHOLD) {
                    if ($absDeviation < self::OUTLIER_NEAR_ZERO_MIN_ABS) continue;
                } else {
                    if (($absDeviation / abs($median)) < self::OUTLIER_MIN_PCT_FROM_MEDIAN) continue;
                }

                $pctFromMean = $median > 0
                    ? round((($m['value'] - $median) / abs($median)) * 100, 2)
                    : null;

                $n = count($values);
                $mean = array_sum($values) / $n;
                $stddev = $this->stdDev($values, $mean);

                $previewRows[$m['idx']]['is_outlier']  = true;
                $previewRows[$m['idx']]['outlier_info'] = [
                    'mean'          => round($median, 4), // gunakan median sebagai "mean" untuk konsistensi UI
                    'stddev'        => round($stddev, 4),
                    'z_score'       => round($mz, 4),     // MZ-score ditampilkan sebagai z
                    'lower'         => round($median - 3 * $stddev, 4),
                    'upper'         => round($median + 3 * $stddev, 4),
                    'n'             => $n,
                    'direction'     => $m['value'] > $median ? 'high' : 'low',
                    'pct_from_mean' => $pctFromMean,
                    'severity'      => match(true) {
                        $mz >= 10 => 'critical',
                        $mz >= 6  => 'high',
                        default   => 'medium',
                    },
                    'no_stddev'     => false,
                    'source'        => 'intra_series',
                ];
            }
        }

        return $previewRows;
    }

    // ══════════════════════════════════════════════════════════
    // ENTRY POINT — IMPORT
    // ══════════════════════════════════════════════════════════

    /**
     * @param  array<string, bool>  $excludedKeys
     *         Key format: "{metadata_id}_{location_id}_{time_id}_{rujukan_id}"
     *         Record yang ada di sini TIDAK diimport (dikecualikan user dari UI).
     */
    public function import(string $filePath, array $excludedKeys = [], array $anomalyKeys = []): array
    {
        [$periodCols, $dataRows, $rujukanColIndex] = $this->readExcel($filePath);

        $this->preloadMetadataCache($dataRows);
        $this->preloadTimeCache($periodCols);
        $this->buildExistingSet($periodCols);
        $this->preloadRujukanCache($dataRows, $rujukanColIndex);

        $buffer    = [];
        $now       = Carbon::now()->format('Y-m-d H:i:s');
        $insertSet = [];
        $this->pendingAnomalyKeys = [];
        $this->anomaliesCreated   = 0;

        DB::beginTransaction();
        try {
            foreach ($dataRows as $rowNum => $row) {
                $result = $this->parseRow(
                    $row, $periodCols, $rowNum,
                    dryRun: false,
                    rujukanColIndex: $rujukanColIndex
                );

                foreach ($result['records'] as $rec) {
                    $key = "{$rec['metadata_id']}_{$rec['location_id']}_{$rec['time_id']}_{$rec['rujukan_id']}";

                    // Skip jika user memilih untuk tidak menyertakan record outlier ini
                    if (isset($excludedKeys[$key])) {
                        $this->skipped++;
                        continue;
                    }

                    if (isset($this->existingSet[$key]) || isset($insertSet[$key])) {
                        if ($this->skipDuplicates) {
                            $this->skipped++;
                            $this->duplicates[] = $rec;
                            continue;
                        }
                    }

                    $recordIsAnomaly = isset($anomalyKeys[$key]);
                    if ($recordIsAnomaly) {
                        $this->pendingAnomalyKeys[$key] = true;
                    }

                    $insertSet[$key] = true;
                    $buffer[] = [
                        'user_id'      => $this->userId,
                        'metadata_id'  => $rec['metadata_id'],
                        'location_id'  => $rec['location_id'],
                        'time_id'      => $rec['time_id'],
                        'number_value' => $rec['number_value'],
                        'rujukan_id'   => $rec['rujukan_id'],
                        'produsen_id'  => $rec['produsen_id'] ?? null,
                        'status'       => Data::STATUS_PENDING,
                        'workflow_status' => $recordIsAnomaly ? Data::WORKFLOW_WARNING : Data::WORKFLOW_DRAFT,
                        'date_inputed' => $now,
                    ];

                    if (count($buffer) >= self::BATCH_SIZE) {
                        DB::table('data')->insert($buffer);
                        $this->imported += count($buffer);
                        $buffer = [];
                    }
                }

                foreach ($result['errors'] as $err) {
                    $this->errors[] = array_merge($err, ['row' => $rowNum]);
                }
                foreach ($result['invalid_metadata'] as $inv) {
                    $this->invalid_metadata[] = array_merge($inv, ['row' => $rowNum]);
                }
            }

            if (!empty($buffer)) {
                DB::table('data')->insert($buffer);
                $this->imported += count($buffer);
            }

            if (!empty($this->pendingAnomalyKeys)) {
                $this->createAnomaliesForPendingKeys($now);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success'      => true,
            'imported'     => $this->imported,
            'skipped'      => $this->skipped,
            'errors'       => count($this->errors),
            'skipped_meta' => count(array_unique(array_column($this->invalid_metadata, 'metadata_id'))),
            'anomalies_created' => $this->anomaliesCreated,
            'message'      => $this->buildSummaryMessage(),
        ];
    }

    private function detectOutliersViaDb(array $previewRows): array
    {
        if (empty($previewRows)) return $previewRows;
    
        // ── Kumpulkan kombinasi unik (metadata_id, location_id) ───────────────
        // Sekaligus kumpulkan semua time_id yang akan diimport per kombinasi,
        // agar bisa di-exclude dari histori (data baru tidak boleh jadi histori diri sendiri).
        $groups = [];
        foreach ($previewRows as $idx => $rec) {
            $mid = $rec['metadata_id'];
            $lid = $rec['location_id'];
            $key = "{$mid}-{$lid}";
    
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'metadata_id' => $mid,
                    'location_id' => $lid,
                    'new_time_ids'=> [],   // time_id yang akan diimport — exclude dari histori
                    'indices'     => [],   // index di $previewRows untuk kombinasi ini
                ];
            }
            $groups[$key]['new_time_ids'][] = $rec['time_id'];
            $groups[$key]['indices'][]      = $idx;
        }
    
        // ── Batch: hitung stats DB per kombinasi ──────────────────────────────
        $statsPerGroup = [];
        foreach ($groups as $key => $group) {
            $history = DB::table('data')
                ->where('metadata_id', $group['metadata_id'])
                ->where('location_id', $group['location_id'])
                // Exclude time_id yang sedang diimport agar tidak muncul sebagai histori diri sendiri.
                // Catatan: data belum ada di DB saat preview, jadi sebenarnya tidak perlu,
                // tapi ini jaga-jaga untuk kasus re-preview setelah partial import.
                ->whereNotIn('time_id', array_unique($group['new_time_ids']))
                // Hanya ambil data yang sudah approved/available sebagai baseline bersih
                ->where('status', \App\Models\Data::STATUS_AVAILABLE)
                ->whereNotNull('number_value')
                ->orderBy('time_id', 'desc')
                ->limit(20)
                ->pluck('number_value')
                ->map(fn($v) => (float) $v)
                ->toArray();
    
            $n = count($history);
    
            if ($n < self::DB_HISTORY_MIN_ROWS) {
                // Tidak cukup data historis — tidak bisa hitung z-score bermakna.
                // Tandai sebagai null agar UI menampilkan info yang jujur.
                $statsPerGroup[$key] = null;
                continue;
            }
    
            $mean   = array_sum($history) / $n;
            $var    = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $history)) / $n;
            $stddev = sqrt($var);
    
            $statsPerGroup[$key] = [
                'mean'   => round($mean, 4),
                'stddev' => round($stddev, 4),
                'lower'  => round($mean - 3 * $stddev, 4),
                'upper'  => round($mean + 3 * $stddev, 4),
                'n'      => $n,
            ];
        }
    
        // ── Tandai outlier per record ─────────────────────────────────────────
        foreach ($previewRows as $idx => &$rec) {
            $key   = "{$rec['metadata_id']}-{$rec['location_id']}";
            $stats = $statsPerGroup[$key] ?? null;
    
            if ($stats === null) {
                // Tidak ada histori cukup — tidak bisa menentukan outlier
                $rec['is_outlier']   = false;
                $rec['outlier_info'] = null;
                continue;
            }
    
            $current = (float) $rec['number_value'];
            $mean    = (float) $stats['mean'];
            $stddev  = (float) $stats['stddev'];
    
            // Jika stddev = 0 (semua data historis identik), gunakan deviation absolut
            if ($stddev < 1e-10) {
                if (abs($current - $mean) < 1e-10) {
                    // Nilai sama persis dengan semua histori → bukan outlier
                    $rec['is_outlier']   = false;
                    $rec['outlier_info'] = null;
                } else {
                    // Nilai beda dari histori yang semua identik → flag sebagai outlier
                    $pctFromMean = $mean > 0
                        ? round((($current - $mean) / abs($mean)) * 100, 2)
                        : null;
                    $rec['is_outlier']   = true;
                    $rec['outlier_info'] = [
                        'mean'         => $mean,
                        'stddev'       => 0.0,
                        'z_score'      => null,   // tidak bisa hitung
                        'lower'        => $mean,
                        'upper'        => $mean,
                        'n'            => $stats['n'],
                        'direction'    => $current > $mean ? 'high' : 'low',
                        'pct_from_mean'=> $pctFromMean,
                        'severity'     => 'medium',
                        'no_stddev'    => true,   // flag untuk UI: tampilkan pesan khusus
                    ];
                }
                continue;
            }
    
            // Hitung standard z-score — IDENTIK dengan createAnomaliesForPendingKeys()
            $zScore = abs(($current - $mean) / $stddev);
    
            if ($zScore <= self::DB_ZSCORE_THRESHOLD) {
                // Di bawah threshold → bukan outlier
                $rec['is_outlier']   = false;
                $rec['outlier_info'] = null;
                continue;
            }
    
            // Hitung severity — IDENTIK dengan createAnomaliesForPendingKeys()
            $severity = match(true) {
                $zScore >= 10 => 'critical',
                $zScore >= 6  => 'high',
                $zScore >= 3  => 'medium',
                default       => 'low',
            };
    
            $pctFromMean = $mean > 0
                ? round((($current - $mean) / abs($mean)) * 100, 2)
                : null;
    
            $rec['is_outlier']   = true;
            $rec['outlier_info'] = [
                'mean'         => $mean,                     // = previous_value di anomali DB
                'stddev'       => round($stddev, 4),
                'z_score'      => round($zScore, 4),         // = percentage_change di anomali DB
                'lower'        => $stats['lower'],
                'upper'        => $stats['upper'],
                'n'            => $stats['n'],
                'direction'    => $current > $mean ? 'high' : 'low',
                'pct_from_mean'=> $pctFromMean,
                'severity'     => $severity,
                'no_stddev'    => false,
            ];
        }
        unset($rec);
    
        return $previewRows;
    }

    private function createAnomaliesForPendingKeys(string $insertedAt): void
    {
        if (empty($this->pendingAnomalyKeys)) {
            return;
        }

        $criteria = [];
        foreach (array_keys($this->pendingAnomalyKeys) as $key) {
            [$metadataId, $locationId, $timeId, $rujukanId] = explode('_', $key);
            $criteria[] = [
                'metadata_id' => (int) $metadataId,
                'location_id' => (int) $locationId,
                'time_id'     => (int) $timeId,
                'rujukan_id'  => $rujukanId === '' ? null : (int) $rujukanId,
            ];
        }

        $query = DB::table('data')->where('date_inputed', $insertedAt);
        $query->where(function ($q) use ($criteria) {
            foreach ($criteria as $c) {
                $q->orWhere(function ($qq) use ($c) {
                    $qq->where('metadata_id', $c['metadata_id'])
                    ->where('location_id', $c['location_id'])
                    ->where('time_id', $c['time_id']);
                    if ($c['rujukan_id'] === null) {
                        $qq->whereNull('rujukan_id');
                    } else {
                        $qq->where('rujukan_id', $c['rujukan_id']);
                    }
                });
            }
        });

        $rows = $query->get(['id', 'number_value', 'metadata_id', 'location_id']);

        foreach ($rows as $row) {
            $currentValue = (float) $row->number_value;

            // ── Histori: izinkan pending + available ──────────────
            $history = DB::table('data')
                ->where('metadata_id', $row->metadata_id)
                ->where('location_id', $row->location_id)
                ->where('id', '!=', $row->id)
                ->whereIn('status', [0, 1])  // 0=pending, 1=available
                ->whereNotNull('number_value')
                ->orderBy('time_id', 'desc')
                ->limit(20)
                ->pluck('number_value')
                ->map(fn($v) => (float) $v)
                ->toArray();

            $previousValue    = null;
            $percentageChange = null;
            $severity         = Anomaly::SEVERITY_MEDIUM;
            $message          = 'Data ditandai sebagai outlier oleh pengguna saat import.';

            if (count($history) >= 1) {
                $mean   = array_sum($history) / count($history);
                $var    = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $history)) / count($history);
                $stdDev = sqrt($var);

                $previousValue = round($mean, 4);

                if ($stdDev > 0) {
                    $zScore           = abs(($currentValue - $mean) / $stdDev);
                    $percentageChange = round($zScore, 4);
                    $upperBound       = round($mean + 3 * $stdDev, 2);
                    $lowerBound       = round($mean - 3 * $stdDev, 2);

                    $message = sprintf(
                        'Outlier ditandai pengguna saat import. Z-score=%.2f '
                        . '(mean=%.2f, σ=%.2f, batas=[%s – %s], n=%d)',
                        $zScore, $mean, $stdDev,
                        $lowerBound, $upperBound,
                        count($history)
                    );

                    $severity = match(true) {
                        $zScore >= 10 => Anomaly::SEVERITY_CRITICAL,
                        $zScore >= 6  => Anomaly::SEVERITY_HIGH,
                        $zScore >= 3  => Anomaly::SEVERITY_MEDIUM,
                        default       => Anomaly::SEVERITY_LOW,
                    };
                } else {
                    $message = sprintf(
                        'Outlier ditandai pengguna saat import. Mean histori=%.2f (n=%d, σ≈0)',
                        $mean, count($history)
                    );
                }
            }

            $anomaly = Anomaly::firstOrCreate(
                [
                    'id'           => $row->id,
                    'table_name'   => 'data',
                    'anomaly_type' => Anomaly::TYPE_UNREASONABLE,
                ],
                [
                    'severity'          => $severity,
                    'previous_value'    => $previousValue,
                    'current_value'     => $currentValue,
                    'percentage_change' => $percentageChange,
                    'message'           => $message,
                    'status'            => Anomaly::STATUS_WARNING,
                    'detected_at'       => now(),
                ]
            );

            if ($anomaly->wasRecentlyCreated ?? false) {
                $this->anomaliesCreated++;
            }

            // ── Audit trail — agar konsisten dengan AnomalyDetectionService ──
            DB::table('audit_trails')->insert([
                'user_id'    => $this->userId ?: null,
                'table_name' => 'data',
                'record_id'  => (string) $row->id,
                'action_type'=> 'screened',
                'old_value'  => null,
                'new_value'  => json_encode([
                    'anomalies_found' => 1,
                    'workflow_status' => 'warning',
                    'checks_run'      => ['manual_outlier_flag_import'],
                    'z_score'         => $percentageChange,
                    'mean'            => $previousValue,
                    'history_n'       => count($history),
                ]),
                'reason'     => 'Screening otomatis sistem',
                'ip_address' => null,
                'user_agent' => null,
                'created_at' => now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════
    // OUTLIER DETECTION — Modified Z-Score (Iglewicz & Hoaglin)
    // ══════════════════════════════════════════════════════════
    private function detectOutliersInRows(array $dataRows, array $periodCols): array
    {
        $outlierMap = [];
    
        // Hitung jumlah baris per metadata_id untuk CoV scaling decision
        $rowsPerMeta = [];
        foreach ($dataRows as $row) {
            $metaId = isset($row[self::COL_META_ID]) ? (int) $row[self::COL_META_ID] : null;
            if ($metaId) {
                $rowsPerMeta[$metaId] = ($rowsPerMeta[$metaId] ?? 0) + 1;
            }
        }
    
        foreach ($dataRows as $rowNum => $row) {
    
            // Kumpulkan nilai numerik per kolom periode
            $periodValues = [];
            foreach ($periodCols as $pi => $periodLabel) {
                $colIndex = self::COL_PERIOD + $pi;
                $rawValue = $row[$colIndex] ?? null;
                if ($rawValue !== null && $rawValue !== '' && is_numeric($rawValue)) {
                    $periodValues[$periodLabel] = (float) $rawValue;
                }
            }
    
            // Minimal 3 nilai numerik agar deteksi bermakna
            if (count($periodValues) < 3) continue;
    
            $values   = array_values($periodValues);
            $labels   = array_keys($periodValues);
            $median   = $this->median($values);
            $mzScores = $this->calculateModifiedZScores($values);
    
            $metadataId  = isset($row[self::COL_META_ID]) ? (int) $row[self::COL_META_ID] : null;
            $rowCountForMeta = $metadataId ? ($rowsPerMeta[$metadataId] ?? 1) : 1;
    
            // ── LAYER 1: Adaptive Threshold ────────────────────────
            // CoV scaling hanya aktif jika metadata ini punya banyak baris (multi-wilayah).
            // Untuk single-row metadata (misal: data narkoba hanya Provinsi Bali),
            // threshold tetap base tanpa scaling — tidak ada alasan untuk menaikkan threshold.
            $adaptiveThreshold = self::OUTLIER_MZSCORE_THRESHOLD;
    
            if ($rowCountForMeta >= self::OUTLIER_MIN_ROWS_FOR_SCALING) {
                $mean   = array_sum($values) / count($values);
                $stdDev = $this->stdDev($values, $mean);
                $cov    = ($mean != 0) ? ($stdDev / abs($mean)) : 0;
    
                if ($cov > self::OUTLIER_MIN_COV_TO_SCALE) {
                    $adaptiveThreshold = self::OUTLIER_MZSCORE_THRESHOLD
                        * (1 + self::OUTLIER_COV_SCALE_FACTOR * ($cov - self::OUTLIER_MIN_COV_TO_SCALE));
                }
            }
    
            foreach ($labels as $idx => $periodLabel) {
                $mz  = $mzScores[$idx];
                $val = $periodValues[$periodLabel];
    
                // Layer 1: cek adaptive threshold
                if (abs($mz) <= $adaptiveThreshold) continue;
    
                // ── LAYER 2: Minimum Deviation Guard ───────────────
                // Tujuan: hindari false positive ketika MAD sangat kecil
                // tapi deviasi absolut sebenarnya tidak signifikan.
                //
                // Special case: jika median mendekati nol, persentase tidak bermakna.
                // Gunakan deviasi absolut sebagai ukuran.
                $absDeviation = abs($val - $median);
    
                if (abs($median) < self::OUTLIER_NEAR_ZERO_THRESHOLD) {
                    // Near-zero median: cek deviasi absolut minimum
                    if ($absDeviation < self::OUTLIER_NEAR_ZERO_MIN_ABS) continue;
                } else {
                    // Normal median: cek persentase dari median
                    $pctDeviation = $absDeviation / abs($median);
                    if ($pctDeviation < self::OUTLIER_MIN_PCT_FROM_MEDIAN) continue;
                }
    
                // Lolos kedua layer → ini outlier
                $pctDiff = $median != 0
                    ? round((($val - $median) / abs($median)) * 100, 2)
                    : null;
    
                $outlierMap[$rowNum][$periodLabel] = [
                    'period_label'       => $periodLabel,
                    'value'              => $val,
                    'modified_zscore'    => round($mz, 4),
                    'adaptive_threshold' => round($adaptiveThreshold, 4),
                    'median_row'         => round($median, 4),
                    'pct_from_median'    => $pctDiff,
                    'direction'          => $val > $median ? 'high' : 'low',
                    'threshold'          => self::OUTLIER_MZSCORE_THRESHOLD,
                ];
            }
        }
    
        return $outlierMap;
    }

    /**
     * Hitung Modified Z-Score untuk array nilai.
     * MZ_i = 0.6745 × (x_i − median) / MAD
     */
    private function calculateModifiedZScores(array $values): array
    {
        $median = $this->median($values);
        $mad    = $this->medianAbsoluteDeviation($values, $median);
    
        if ($mad < 1e-10) {
            return array_fill(0, count($values), 0.0);
        }
    
        return array_map(
            fn($v) => 0.6745 * ($v - $median) / $mad,
            $values
        );
    }

    /**
     * Median dari array nilai.
     */
    private function median(array $values): float
    {
        $sorted = $values;
        sort($sorted);
        $count = count($sorted);
        $mid   = (int) floor($count / 2);
    
        return $count % 2 === 0
            ? ($sorted[$mid - 1] + $sorted[$mid]) / 2
            : (float) $sorted[$mid];
    }

    /**
     * Median Absolute Deviation.
     */
    private function medianAbsoluteDeviation(array $values, float $median): float
    {
        $deviations = array_map(fn($v) => abs($v - $median), $values);
        return $this->median($deviations);
    }

    /**
     * Standard Deviation (populasi).
     */
    private function stdDev(array $values, float $mean): float
    {
        if (count($values) < 2) return 0.0;
        $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / count($values);
        return sqrt($variance);
    }


    // ══════════════════════════════════════════════════════════
    // EXCEL READER
    // ══════════════════════════════════════════════════════════

    private function readExcel(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $ws = $spreadsheet->getSheetByName('Data Import') ?? $spreadsheet->getActiveSheet();

        $maxRow = $ws->getHighestDataRow();
        $maxCol = $ws->getHighestDataColumn();

        $headerRow = [];
        foreach ($ws->getRowIterator(self::HEADER_ROW, self::HEADER_ROW) as $row) {
            foreach ($row->getCellIterator('A', $maxCol) as $cell) {
                $val = $cell->getValue();
                if (is_float($val) && floor($val) == $val) $val = (int) $val;
                $headerRow[] = $val;
            }
        }

        $rujukanColIndex = null;
        foreach ($headerRow as $i => $val) {
            if (strtolower(trim((string) $val)) === 'rujukan_id') {
                $rujukanColIndex = $i;
                break;
            }
        }

        $periodCols = array_slice($headerRow, self::COL_PERIOD);

        $dataRows = [];
        for ($r = self::DATA_ROW; $r <= $maxRow; $r++) {
            $rowData = [];
            foreach ($ws->getRowIterator($r, $r) as $row) {
                foreach ($row->getCellIterator('A', $maxCol) as $cell) {
                    $val = $cell->getValue();
                    if (is_float($val) && floor($val) == $val) $val = (int) $val;
                    $rowData[] = $val;
                }
            }
            if (empty(array_filter($rowData, fn($v) => $v !== null && $v !== ''))) continue;
            $dataRows[$r] = $rowData;
        }

        return [$periodCols, $dataRows, $rujukanColIndex];
    }

    // ══════════════════════════════════════════════════════════
    // ROW PARSER
    // ══════════════════════════════════════════════════════════

    private function parseRow(
        array $row,
        array $periodCols,
        int   $rowNum,
        bool  $dryRun,
        ?int  $rujukanColIndex = null
    ): array {
        $records         = [];
        $errors          = [];
        $invalid_metadata = [];

        $metadataId = isset($row[self::COL_META_ID]) ? (int) $row[self::COL_META_ID] : null;
        $locationId = isset($row[self::COL_LOC_ID]) && $row[self::COL_LOC_ID] !== '' && $row[self::COL_LOC_ID] !== null
            ? (int) $row[self::COL_LOC_ID]
            : null;
        $metaNama   = $row[self::COL_META_NM] ?? '-';
        $locNama    = $row[self::COL_LOC_NM]  ?? '-';
        $rujukanId  = ($rujukanColIndex !== null && isset($row[$rujukanColIndex]))
            ? (int) $row[$rujukanColIndex]
            : null;

        if (!$metadataId) {
            $errors[] = ['message' => "Baris $rowNum: metadata_id kosong atau tidak valid.", 'row' => $rowNum];
            $metadataId = null;
        } else {
            $metaValidation = $this->metadataCache[$metadataId] ?? null;
            if (!$metaValidation['valid']) {
                $invalid_metadata[] = [
                    'metadata_id'   => $metadataId,
                    'nama_metadata' => $metaNama,
                    'reason'        => $metaValidation['reason'],
                    'row'           => $rowNum,
                ];
                $metadataId = null;
            }
        }

        if ($locationId === null) {
            $errors[] = ['message' => "Baris $rowNum: location_id kosong atau tidak valid.", 'row' => $rowNum];
        } else {
            if (!DB::table('location')->where('location_id', $locationId)->exists()) {
                $errors[] = [
                    'message' => "Baris $rowNum: location_id '$locationId' tidak ditemukan.",
                    'row'     => $rowNum,
                ];
                $locationId = null;
            }
        }

        if (!$metadataId || $locationId === null) {
            return compact('records', 'errors', 'invalid_metadata');
        }

        $metaValidation = $this->metadataCache[$metadataId];

        foreach ($periodCols as $pi => $periodLabel) {
            $colIndex = self::COL_PERIOD + $pi;
            $rawValue = $row[$colIndex] ?? null;

            if ($rawValue === null || $rawValue === '') continue;

            if (!is_numeric($rawValue)) {
                $errors[] = [
                    'message'     => "Baris $rowNum, kolom '$periodLabel': nilai '$rawValue' bukan angka.",
                    'metadata_id' => $metadataId,
                    'location_id' => $locationId,
                    'period'      => $periodLabel,
                ];
                continue;
            }

            $timeId = $this->resolveTimeId((string) $periodLabel);
            if (!$timeId) {
                $errors[] = [
                    'message'     => "Baris $rowNum, kolom '$periodLabel': time_id tidak ditemukan.",
                    'metadata_id' => $metadataId,
                    'location_id' => $locationId,
                    'period'      => $periodLabel,
                ];
                continue;
            }

            $records[] = [
                'metadata_id'   => $metadataId,
                'nama_metadata' => $metaNama,
                'satuan_data'   => $metaValidation['satuan_data'] ?? null,
                'location_id'   => $locationId,
                'nama_wilayah'  => $locNama,
                'time_id'       => $timeId,
                'period_label'  => (string) $periodLabel,
                'rujukan_id'    => $rujukanId,
                'nama_rujukan'  => $this->rujukanCache[$rujukanId]['nama'] ?? null,
                'produsen_id'   => $this->rujukanCache[$rujukanId]['produsen_id'] ?? null,
                'number_value'  => (float) $rawValue,
            ];
        }

        return [
            'records'          => $records,
            'errors'           => $errors,
            'invalid_metadata' => $invalid_metadata,
        ];
    }

    // ══════════════════════════════════════════════════════════
    // PRELOAD HELPERS
    // ══════════════════════════════════════════════════════════

    private function preloadRujukanCache(array $dataRows, ?int $rujukanColIndex): void
    {
        if ($rujukanColIndex === null) return;

        $ids = array_unique(array_filter(array_map(
            fn($row) => isset($row[$rujukanColIndex]) ? (int) $row[$rujukanColIndex] : null,
            $dataRows
        )));

        if (empty($ids)) return;

        $rows = DB::table('rujukan')
            ->whereIn('rujukan_id', $ids)
            ->get(['rujukan_id', 'nama_rujukan', 'produsen_id']);

        foreach ($rows as $r) {
            $this->rujukanCache[$r->rujukan_id] = [
                'nama'        => $r->nama_rujukan,
                'produsen_id' => $r->produsen_id,
            ];
        }
    }

    private function preloadMetadataCache(array $dataRows): void
    {
        $ids = array_unique(array_filter(array_map(
            fn($row) => isset($row[self::COL_META_ID]) ? (int) $row[self::COL_META_ID] : null,
            $dataRows
        )));

        if (empty($ids)) return;

        $rows = DB::table('metadata')
            ->whereIn('metadata_id', $ids)
            ->get(['metadata_id', 'nama', 'status', 'alias', 'tipe_data', 'satuan_data', 'frekuensi_penerbitan']);

        $found = [];
        foreach ($rows as $r) $found[$r->metadata_id] = $r;

        foreach ($ids as $id) {
            if (!isset($found[$id])) {
                $this->metadataCache[$id] = ['valid' => false, 'reason' => 'not_found', 'nama' => null];
            } elseif ((int) $found[$id]->status !== 2) {
                $this->metadataCache[$id] = [
                    'valid'        => false,
                    'reason'       => 'not_active',
                    'status'       => (int) $found[$id]->status,
                    'status_label' => $this->metadataStatusLabel((int) $found[$id]->status),
                    'nama'         => $found[$id]->nama,
                ];
            } else {
                $this->metadataCache[$id] = [
                    'valid'               => true,
                    'reason'              => null,
                    'nama'                => $found[$id]->nama,
                    'satuan_data'         => $found[$id]->satuan_data,
                    'tipe_data'           => $found[$id]->tipe_data,
                    'alias'               => $found[$id]->alias,
                    'frekuensi_penerbitan'=> $found[$id]->frekuensi_penerbitan,
                ];
            }
        }
    }

    private function metadataStatusLabel(int $status): string
    {
        return match ($status) {
            0       => 'Draft',
            1       => 'Menunggu Review',
            2       => 'Active',
            3       => 'Nonaktif',
            default => "Status $status",
        };
    }

    private function resolveTimeId(string $label): ?int
    {
        $cacheKey = strtolower(trim($label));
        if (array_key_exists($cacheKey, $this->timeCache)) return $this->timeCache[$cacheKey];

        $params = $this->parseTimeLabel($label);
        if (!$params) return $this->timeCache[$cacheKey] = null;

        $timeId = DB::table('time')
            ->where('decade',   $params['decade'])
            ->where('year',     $params['year'])
            ->where('semester', $params['semester'])
            ->where('quarter',  $params['quarter'])
            ->where('month',    $params['month'])
            ->value('time_id');

        return $this->timeCache[$cacheKey] = $timeId;
    }

    public function parseTimeLabel(string $label): ?array
    {
        $label = trim($label);

        if (is_numeric($label) && strlen($label) === 4) {
            $year = (int) $label;
            return ['decade' => (int)(floor($year / 10) * 10), 'year' => $year, 'semester' => 0, 'quarter' => 0, 'month' => 0];
        }
        if (preg_match('/^(\d{4})_S([12])$/i', $label, $m)) {
            $year = (int) $m[1]; $semester = (int) $m[2];
            return ['decade' => (int)(floor($year / 10) * 10), 'year' => $year, 'semester' => $semester, 'quarter' => 0, 'month' => 0];
        }
        if (preg_match('/^(\d{4})_Q([1-4])$/i', $label, $m)) {
            $year = (int) $m[1]; $quarter = (int) $m[2]; $semester = $quarter <= 2 ? 1 : 2;
            return ['decade' => (int)(floor($year / 10) * 10), 'year' => $year, 'semester' => $semester, 'quarter' => $quarter, 'month' => 0];
        }
        if (preg_match('/^([A-Za-z]{3})_(\d{4})$/', $label, $m)) {
            $month = self::BULAN_MAP[strtolower($m[1])] ?? null;
            if ($month) {
                $year = (int) $m[2]; $semester = $month <= 6 ? 1 : 2;
                return ['decade' => (int)(floor($year / 10) * 10), 'year' => $year, 'semester' => $semester, 'quarter' => 0, 'month' => $month];
            }
        }

        return null;
    }

    private function detectPeriodType(string $label): string
    {
        $label = trim((string) $label);
        if (is_numeric($label) && strlen($label) === 4)   return 'tahunan';
        if (preg_match('/^\d{4}_S[12]$/i', $label))       return 'semester';
        if (preg_match('/^\d{4}_Q[1-4]$/i', $label))      return 'quarter';
        if (preg_match('/^[A-Za-z]{3}_\d{4}$/', $label))  return 'bulanan';
        return 'unknown';
    }

    private function preloadTimeCache(array $periodCols): void
    {
        $paramsMap = [];
        foreach ($periodCols as $label) {
            $p = $this->parseTimeLabel((string) $label);
            if ($p) $paramsMap[strtolower(trim((string) $label))] = $p;
        }
        if (empty($paramsMap)) return;

        $query = DB::table('time');
        $first = true;
        foreach ($paramsMap as $params) {
            $method = $first ? 'where' : 'orWhere';
            $query->$method(fn($q) =>
                $q->where('decade',   $params['decade'])
                  ->where('year',     $params['year'])
                  ->where('semester', $params['semester'])
                  ->where('quarter',  $params['quarter'])
                  ->where('month',    $params['month'])
            );
            $first = false;
        }

        $timeRows = $query->get(['time_id', 'decade', 'year', 'semester', 'quarter', 'month']);
        foreach ($paramsMap as $cacheKey => $params) {
            foreach ($timeRows as $tr) {
                if ($tr->decade == $params['decade'] && $tr->year == $params['year']
                    && $tr->semester == $params['semester'] && $tr->quarter == $params['quarter']
                    && $tr->month == $params['month']
                ) {
                    $this->timeCache[$cacheKey] = $tr->time_id;
                    break;
                }
            }
        }
    }

    private function buildExistingSet(array $periodCols): void
    {
        $timeIds = array_unique(array_filter(array_map(
            fn($label) => $this->resolveTimeId((string) $label),
            $periodCols
        )));

        if (empty($timeIds)) return;

        $existing = DB::table('data')
            ->whereIn('time_id', $timeIds)
            ->select('metadata_id', 'location_id', 'time_id', 'rujukan_id')
            ->get();

        foreach ($existing as $row) {
            $this->existingSet["{$row->metadata_id}_{$row->location_id}_{$row->time_id}_{$row->rujukan_id}"] = true;
        }
    }

    // ══════════════════════════════════════════════════════════
    // GETTERS
    // ══════════════════════════════════════════════════════════

    public function getImportedCount(): int     { return $this->imported; }
    public function getSkippedCount(): int      { return $this->skipped; }
    public function getErrors(): array          { return $this->errors; }
    public function getDuplicates(): array      { return $this->duplicates; }
    public function getOutliers(): array        { return $this->outliers; }
    public function getInvalidMetadata(): array { return $this->invalid_metadata; }

    private function buildSummaryMessage(): string
    {
        $msg = "Berhasil mengimpor {$this->imported} data.";
        if ($this->skipped > 0)       $msg .= " {$this->skipped} data dilewati.";
        if (count($this->errors) > 0) $msg .= " " . count($this->errors) . " baris gagal.";

        $invalidMeta = array_unique(array_column($this->invalid_metadata, 'metadata_id'));
        if (!empty($invalidMeta)) {
            $msg .= " " . count($invalidMeta) . " metadata tidak aktif/tidak ditemukan dilewati.";
        }
        return $msg;
    }
}