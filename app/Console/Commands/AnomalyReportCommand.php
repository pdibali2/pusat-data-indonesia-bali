<?php

namespace App\Console\Commands;

use App\Models\Anomaly;
use App\Models\AnomalyReview;
use App\Models\Data;
use App\Services\WorkflowService;
use Illuminate\Console\Command;

class AnomalyReportCommand extends Command
{
    protected $signature = 'anomaly:report
                            {--days=30 : Rentang hari untuk laporan}
                            {--format=table : Format output (table|json)}';

    protected $description = 'Tampilkan laporan ringkasan anomali data statistik';

    public function __construct(
        private readonly WorkflowService $workflow,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $format = $this->option('format');
        $since  = now()->subDays($days);

        $this->newLine();
        $this->line("  <fg=cyan;options=bold>LAPORAN ANOMALI — {$days} Hari Terakhir</>");
        $this->line("  Periode: {$since->format('d/m/Y')} s/d " . now()->format('d/m/Y'));
        $this->newLine();

        // ── Statistik utama ───────────────────────────────────
        $stats = $this->workflow->getControlStats();

        $totalAnomaly = Anomaly::where('detected_at', '>=', $since)->count();
        $resolved     = Anomaly::where('detected_at', '>=', $since)
            ->whereIn('status', [
                Anomaly::STATUS_APPROVED,
                Anomaly::STATUS_APPROVED_WITH_NOTE,
                Anomaly::STATUS_REJECTED,
                Anomaly::STATUS_REVISED,
            ])->count();

        $resolutionRate = $totalAnomaly > 0
            ? round(($resolved / $totalAnomaly) * 100, 1)
            : 0;

        // ── Rata-rata waktu review (jam) ───────────────────────
        $avgReviewHours = AnomalyReview::where('created_at', '>=', $since)
            ->join('anomalies', 'anomalies.anomalies_id', '=', 'anomaly_reviews.anomaly_id')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, anomalies.detected_at, anomaly_reviews.created_at)) as avg_hours')
            ->value('avg_hours');

        if ($format === 'json') {
            $this->line(json_encode([
                'period_days'       => $days,
                'total_anomalies'   => $totalAnomaly,
                'pending_warning'   => $stats['total_warning'],
                'pending_review'    => $stats['total_under_review'],
                'resolved'          => $resolved,
                'resolution_rate'   => $resolutionRate,
                'critical_pending'  => $stats['critical_pending'],
                'avg_review_hours'  => round((float) $avgReviewHours, 1),
            ], JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        // ── Tabel ringkasan ───────────────────────────────────
        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Total Anomali Terdeteksi', $totalAnomaly],
                ['Menunggu Review (Warning)', $stats['total_warning']],
                ['Sedang Direview',           $stats['total_under_review']],
                ['Sudah Resolved',            $resolved],
                ['Resolution Rate',           "{$resolutionRate}%"],
                ['Critical Belum Resolved',   $stats['critical_pending']],
                ['High Belum Resolved',        $stats['high_pending']],
                ['Rata-rata Waktu Review',     round((float) $avgReviewHours, 1) . ' jam'],
            ]
        );

        // ── Top 5 metadata dengan anomali terbanyak ──────────
        $this->newLine();
        $this->line('  <options=bold>Top 5 Metadata dengan Anomali Terbanyak:</>');

        $topMetadata = Anomaly::where('detected_at', '>=', $since)
            ->join('data', 'data.id', '=', 'anomalies.id')
            ->join('metadata', 'metadata.metadata_id', '=', 'data.metadata_id')
            ->selectRaw('metadata.nama, COUNT(anomalies.anomalies_id) as total')
            ->groupBy('metadata.metadata_id', 'metadata.nama')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        if ($topMetadata->isEmpty()) {
            $this->info('  Tidak ada data.');
        } else {
            $this->table(
                ['Metadata', 'Jumlah Anomali'],
                $topMetadata->map(fn($r) => [$r->nama, $r->total])->toArray()
            );
        }

        // ── Breakdown severity ────────────────────────────────
        $this->newLine();
        $this->line('  <options=bold>Breakdown Severity (periode ini):</>');

        $bySeverity = Anomaly::where('detected_at', '>=', $since)
            ->selectRaw('severity, COUNT(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity')
            ->toArray();

        $this->table(
            ['Severity', 'Total', 'Bar'],
            array_map(function ($sev, $label) use ($bySeverity, $totalAnomaly) {
                $count = $bySeverity[$sev] ?? 0;
                $pct   = $totalAnomaly > 0 ? round($count / $totalAnomaly * 20) : 0;
                $bar   = str_repeat('█', $pct) . str_repeat('░', 20 - $pct);
                return [$label, $count, $bar];
            }, ['critical','high','medium','low'], ['🔴 Critical','🟠 High','🟡 Medium','🔵 Low'])
        );

        if ($stats['critical_pending'] > 0) {
            $this->newLine();
            $this->error("  ⚠  PERHATIAN: Ada {$stats['critical_pending']} anomali Critical yang belum direview!");
            $this->line('  → Segera buka: /anomaly/control?severity=critical');
        }

        $this->newLine();
        return self::SUCCESS;
    }
}