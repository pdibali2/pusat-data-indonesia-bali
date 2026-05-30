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
    private const OUTLIER_MZSCORE_THRESHOLD = 3.5;

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

        // ── Deteksi outlier per baris sebelum parse record ──
        $outlierMap = $this->detectOutliersInRows($dataRows, $periodCols);

        $previewRows     = [];
        $errors          = [];
        $duplicates      = [];
        $invalid_metadata = [];
        $outliers        = [];   // baris dengan outlier untuk ditampilkan di UI

        foreach ($dataRows as $rowNum => $row) {
            $result = $this->parseRow(
                $row, $periodCols, $rowNum,
                dryRun: true,
                rujukanColIndex: $rujukanColIndex
            );

            // Tandai setiap record dengan info outlier
            $rowOutliers = $outlierMap[$rowNum] ?? [];

            foreach ($result['records'] as $rec) {
                $periodLabel = $rec['period_label'];
                $isOutlier   = isset($rowOutliers[$periodLabel]);

                $rec['is_outlier']    = $isOutlier;
                $rec['outlier_info']  = $isOutlier ? $rowOutliers[$periodLabel] : null;
                $rec['include']       = true; // default: ikut sertakan

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

        // ── Kumpulkan semua record outlier dalam format khusus untuk UI ──
        foreach ($previewRows as $rec) {
            if ($rec['is_outlier']) {
                $outliers[] = $rec;
            }
        }

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
            'outliers'          => $outliers,             // ← BARU
            'total_rows'        => count($dataRows),
            'valid'             => count($previewRows),
            'duplicate'         => count($duplicates),
            'error'             => count($errors),
            'outlier_count'     => count($outliers),      // ← BARU
            'invalid_meta_count'=> count($uniqueInvalid),
            'period_type'       => $this->detectPeriodType($periodCols[0] ?? ''),
            'period_cols'       => $periodCols,
        ];
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

        $rows = $query->get(['id', 'number_value']);
        foreach ($rows as $row) {
            $anomaly = Anomaly::firstOrCreate([
                'id'           => $row->id,
                'table_name'   => 'data',
                'anomaly_type' => Anomaly::TYPE_UNREASONABLE,
            ], [
                'severity'          => Anomaly::SEVERITY_MEDIUM,
                'previous_value'    => null,
                'current_value'     => $row->number_value,
                'percentage_change' => null,
                'message'           => 'Data ditandai sebagai outlier oleh pengguna saat import.',
                'status'            => Anomaly::STATUS_WARNING,
                'detected_at'       => now(),
            ]);

            if ($anomaly->wasRecentlyCreated ?? false) {
                $this->anomaliesCreated++;
            }
        }
    }

    // ══════════════════════════════════════════════════════════
    // OUTLIER DETECTION — Modified Z-Score (Iglewicz & Hoaglin)
    // ══════════════════════════════════════════════════════════

    /**
     * Deteksi outlier untuk setiap baris Excel menggunakan Modified Z-Score.
     *
     * Mengapa Modified Z-Score, bukan Z-Score standar?
     * - Data statistik di Excel sering hanya 3–10 titik waktu (dataset kecil).
     * - Z-Score standar menggunakan mean & stddev yang sangat dipengaruhi outlier itu sendiri.
     * - Modified Z-Score menggunakan MEDIAN dan MAD (Median Absolute Deviation)
     *   yang jauh lebih robust terhadap outlier.
     *
     * Rumus: MZ_i = 0.6745 × (x_i − median) / MAD
     * Flag sebagai outlier jika |MZ_i| > 3.5 
     *
     * Syarat minimum: baris harus punya ≥ 3 nilai numerik agar deteksi bermakna.
     *
     * @return array<int, array<string, array>> $outlierMap[rowNum][periodLabel] = info
     */
    private function detectOutliersInRows(array $dataRows, array $periodCols): array
    {
        $outlierMap = [];

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

            // Perlu minimal 3 nilai untuk deteksi bermakna
            if (count($periodValues) < 3) continue;

            $values = array_values($periodValues);
            $mzScores = $this->calculateModifiedZScores($values);
            $labels   = array_keys($periodValues);

            foreach ($labels as $idx => $periodLabel) {
                $mz  = $mzScores[$idx];
                $val = $periodValues[$periodLabel];

                if (abs($mz) > self::OUTLIER_MZSCORE_THRESHOLD) {
                    $median  = $this->median($values);
                    $pctDiff = $median > 0
                        ? round((($val - $median) / $median) * 100, 2)
                        : null;

                    $outlierMap[$rowNum][$periodLabel] = [
                        'period_label'   => $periodLabel,
                        'value'          => $val,
                        'modified_zscore'=> round($mz, 4),
                        'median_row'     => round($median, 4),
                        'pct_from_median'=> $pctDiff,
                        'direction'      => $val > $median ? 'high' : 'low',
                        'threshold'      => self::OUTLIER_MZSCORE_THRESHOLD,
                    ];
                }
            }
        }

        return $outlierMap;
    }

    /**
     * Hitung Modified Z-Score untuk array nilai.
     *
     * MZ_i = 0.6745 × (x_i − median(X)) / MAD(X)
     *
     * Konstanta 0.6745 adalah invers dari CDF normal standar pada 75% (Q3),
     * sehingga untuk distribusi normal, Modified Z-Score ≈ Z-Score standar.
     */
    private function calculateModifiedZScores(array $values): array
    {
        $median = $this->median($values);
        $mad    = $this->medianAbsoluteDeviation($values, $median);

        // Jika MAD = 0, semua nilai identik atau hampir identik — tidak ada outlier
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
     * Median Absolute Deviation: median(|x_i − median(X)|)
     */
    private function medianAbsoluteDeviation(array $values, float $median): float
    {
        $deviations = array_map(fn($v) => abs($v - $median), $values);
        return $this->median($deviations);
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
        $locationId = isset($row[self::COL_LOC_ID])  ? (int) $row[self::COL_LOC_ID]  : null;
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

        if (!$locationId) {
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

        if (!$metadataId || !$locationId) {
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