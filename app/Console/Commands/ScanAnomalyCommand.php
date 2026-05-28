<?php

namespace App\Console\Commands;

use App\Models\Anomaly;
use App\Models\Data;
use App\Services\AnomalyDetectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScanAnomalyCommand extends Command
{
    protected $signature = 'anomaly:scan
                            {--batch=100        : Jumlah record diproses per batch}
                            {--metadata=        : Filter hanya metadata_id tertentu (pisah koma)}
                            {--location=        : Filter hanya location_id tertentu (pisah koma)}
                            {--year=            : Filter hanya tahun tertentu}
                            {--all              : Scan ulang semua data, termasuk yang sudah di-screen}
                            {--dry-run          : Simulasi tanpa menyimpan ke database}
                            {--reset-workflow   : Reset workflow_status ke draft sebelum scan (pakai dengan --all)}';

    protected $description = 'Scan seluruh data statistik dan deteksi anomali secara otomatis';

    public function __construct(
        private readonly AnomalyDetectionService $detector,
    ) {
        parent::__construct();
    }

    // ══════════════════════════════════════════════════════════
    // HANDLE
    // ══════════════════════════════════════════════════════════

    public function handle(): int
    {
        $this->printBanner();

        $batchSize   = (int) $this->option('batch');
        $isDryRun    = (bool) $this->option('dry-run');
        $scanAll     = (bool) $this->option('all');
        $resetWf     = (bool) $this->option('reset-workflow');

        // ── Konfirmasi jika --all + --reset-workflow ───────────
        if ($scanAll && $resetWf && !$this->confirmReset()) {
            return self::FAILURE;
        }

        // ── Build query ───────────────────────────────────────
        $query = $this->buildQuery($scanAll);

        $totalToScan = $query->count();

        if ($totalToScan === 0) {
            $this->info('  ✓ Tidak ada data yang perlu di-scan.');
            $this->newLine();
            return self::SUCCESS;
        }

        $this->info("  Data yang akan di-scan: <comment>{$totalToScan}</comment>");

        if ($isDryRun) {
            $this->warn('  [DRY RUN] Tidak ada perubahan yang akan disimpan.');
        }

        $this->newLine();

        // ── Reset workflow jika diminta ───────────────────────
        if ($scanAll && $resetWf && !$isDryRun) {
            $this->resetWorkflow($query->pluck('id')->toArray());
        }

        // ── Progress bar ──────────────────────────────────────
        $bar = $this->output->createProgressBar($totalToScan);
        $bar->setFormat(
            " %current%/%max% [%bar%] %percent:3s%%  ⏱ %elapsed:6s% / ~%estimated:-6s%\n" .
            " Anomali: <fg=yellow>%message%</>"
        );
        $bar->setMessage('0');
        $bar->start();

        // ── Jalankan scanning ─────────────────────────────────
        $stats = $this->runScan($query, $batchSize, $isDryRun, $bar);

        $bar->finish();
        $this->newLine(2);

        // ── Tampilkan hasil ───────────────────────────────────
        $this->printSummary($stats, $isDryRun);

        return self::SUCCESS;
    }

    // ══════════════════════════════════════════════════════════
    // BUILD QUERY
    // ══════════════════════════════════════════════════════════

    private function buildQuery(bool $scanAll): \Illuminate\Database\Eloquent\Builder
    {
        $query = Data::query()
            ->where('status', Data::STATUS_AVAILABLE)
            ->whereNotNull('number_value')
            ->with(['metadata', 'time', 'location']);

        // Hanya data draft (belum pernah di-screen) kecuali --all
        if (!$scanAll) {
            $query->where('workflow_status', Data::WORKFLOW_DRAFT);
        }

        // Filter opsional: metadata_id
        if ($this->option('metadata')) {
            $ids = array_map('intval', explode(',', $this->option('metadata')));
            $query->whereIn('metadata_id', $ids);
            $this->info("  Filter metadata_id: " . implode(', ', $ids));
        }

        // Filter opsional: location_id
        if ($this->option('location')) {
            $ids = array_map('intval', explode(',', $this->option('location')));
            $query->whereIn('location_id', $ids);
            $this->info("  Filter location_id: " . implode(', ', $ids));
        }

        // Filter opsional: tahun
        if ($this->option('year')) {
            $year = (int) $this->option('year');
            $query->whereHas('time', fn($q) => $q->where('year', $year));
            $this->info("  Filter tahun: {$year}");
        }

        return $query;
    }

    // ══════════════════════════════════════════════════════════
    // RUN SCAN
    // ══════════════════════════════════════════════════════════

    private function runScan(
        \Illuminate\Database\Eloquent\Builder $query,
        int   $batchSize,
        bool  $isDryRun,
        mixed $bar,
    ): array {
        $stats = [
            'scanned'         => 0,
            'anomalies_found' => 0,
            'skipped'         => 0,
            'by_severity'     => ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0],
            'by_type'         => [],
            'errors'          => [],
        ];

        $query->chunkById($batchSize, function ($chunk) use (&$stats, $isDryRun, $bar) {
            foreach ($chunk as $data) {
                try {
                    if ($isDryRun) {
                        // Dry run: simulasi tanpa simpan ke DB
                        $result = $this->simulateScan($data);
                    } else {
                        $result = $this->detector->screenData($data);
                    }

                    $stats['scanned']++;

                    if ($result['anomalies_found']) {
                        $stats['anomalies_found']++;

                        foreach ($result['anomalies'] as $anomaly) {
                            $sev = $anomaly->severity ?? ($anomaly['severity'] ?? 'low');
                            $typ = $anomaly->anomaly_type ?? ($anomaly['anomaly_type'] ?? 'unknown');

                            $stats['by_severity'][$sev] = ($stats['by_severity'][$sev] ?? 0) + 1;
                            $stats['by_type'][$typ]     = ($stats['by_type'][$typ]     ?? 0) + 1;
                        }
                    }

                    $bar->setMessage((string) $stats['anomalies_found']);
                    $bar->advance();

                } catch (\Throwable $e) {
                    $stats['skipped']++;
                    $stats['errors'][] = "data#{$data->id}: " . $e->getMessage();
                    $bar->advance();
                }
            }
        });

        return $stats;
    }

    // ══════════════════════════════════════════════════════════
    // DRY RUN SIMULATOR
    // Menjalankan logika deteksi tapi tidak menyimpan ke DB
    // ══════════════════════════════════════════════════════════

    private function simulateScan(Data $data): array
    {
        // Wrap dalam transaction yang di-rollback agar tidak ada perubahan tersimpan
        $result = ['anomalies_found' => false, 'anomalies' => [], 'workflow_status' => 'draft'];

        DB::transaction(function () use ($data, &$result) {
            $result = $this->detector->screenData($data);
            // Rollback otomatis oleh DB::transaction dengan throw
            throw new \RuntimeException('__dry_run_rollback__');
        }, 1);

        return $result;
    }

    // ══════════════════════════════════════════════════════════
    // RESET WORKFLOW
    // ══════════════════════════════════════════════════════════

    private function resetWorkflow(array $ids): void
    {
        $this->info('  Mereset workflow_status ke draft…');

        // Hapus anomali lama yang sudah ada
        Anomaly::whereIn('id', $ids)->delete();

        // Reset workflow ke draft
        Data::whereIn('id', $ids)->update([
            'workflow_status' => Data::WORKFLOW_DRAFT,
            'reviewer_note'   => null,
            'reviewed_by'     => null,
            'reviewed_at'     => null,
        ]);

        $this->info('  Reset selesai. Memulai scan baru…');
        $this->newLine();
    }

    // ══════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════

    private function confirmReset(): bool
    {
        $this->warn('  ⚠  --reset-workflow akan menghapus semua anomali lama dan mereset');
        $this->warn('       workflow_status ke draft untuk data yang dipilih.');

        return $this->confirm('  Lanjutkan?', false);
    }

    private function printBanner(): void
    {
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>╔════════════════════════════════════════╗</>');
        $this->line('  <fg=cyan;options=bold>║      ANOMALY SCANNER — Data Statistik  ║</>');
        $this->line('  <fg=cyan;options=bold>╚════════════════════════════════════════╝</>');
        $this->newLine();
        $this->info('  Memulai scanning pada: ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();
    }

    private function printSummary(array $stats, bool $isDryRun): void
    {
        $mode = $isDryRun ? ' <fg=yellow>[DRY RUN]</>' : '';
        $this->line("  <options=bold>Hasil Scanning{$mode}</>");
        $this->newLine();

        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total Di-scan',      number_format($stats['scanned'])],
                ['Anomali Ditemukan',  number_format($stats['anomalies_found'])],
                ['Dilewati (error)',   number_format($stats['skipped'])],
            ]
        );

        // Breakdown by severity
        if (array_sum($stats['by_severity']) > 0) {
            $this->newLine();
            $this->line('  <options=bold>Breakdown Severity:</>');
            $this->table(
                ['Severity', 'Jumlah'],
                [
                    ['🔴 Critical', number_format($stats['by_severity']['critical'])],
                    ['🟠 High',     number_format($stats['by_severity']['high'])],
                    ['🟡 Medium',   number_format($stats['by_severity']['medium'])],
                    ['🔵 Low',      number_format($stats['by_severity']['low'])],
                ]
            );
        }

        // Breakdown by type
        if (!empty($stats['by_type'])) {
            $this->newLine();
            $this->line('  <options=bold>Breakdown Tipe Anomali:</>');
            $typeRows = array_map(
                fn($type, $count) => [
                    match($type) {
                        'extreme_increase' => 'Kenaikan Ekstrem',
                        'extreme_decrease' => 'Penurunan Ekstrem',
                        'source_conflict'  => 'Konflik Sumber Data',
                        'unreasonable_value' => 'Nilai Tidak Wajar',
                        default => $type,
                    },
                    number_format($count),
                ],
                array_keys($stats['by_type']),
                array_values($stats['by_type'])
            );
            $this->table(['Tipe', 'Jumlah'], $typeRows);
        }

        // Error detail
        if (!empty($stats['errors'])) {
            $this->newLine();
            $this->warn('  Error yang ditemukan:');
            foreach (array_slice($stats['errors'], 0, 10) as $err) {
                $this->warn("    • {$err}");
            }
            if (count($stats['errors']) > 10) {
                $remaining = count($stats['errors']) - 10;
                $this->warn("    … dan {$remaining} error lainnya.");
            }
        }

        $this->newLine();

        if (!$isDryRun && $stats['anomalies_found'] > 0) {
            $this->line('  <fg=yellow>⚠  Ada anomali yang perlu direview.</>');
            $this->line('  <fg=cyan>→  Buka halaman Control: /anomaly/control</>');
        } elseif ($stats['anomalies_found'] === 0) {
            $this->line('  <fg=green>✓  Tidak ada anomali ditemukan. Data bersih!</>');
        }

        $this->newLine();
        $this->info('  Selesai pada: ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();
    }
}