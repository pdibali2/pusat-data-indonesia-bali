<?php

namespace App\Services;

use App\Models\Anomaly;
use App\Models\AnomalyReview;
use App\Models\Data;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowService
{
    public function __construct(
        private readonly AuditTrailService $auditTrail,
    ) {}

    // ══════════════════════════════════════════════════════════
    // REVIEW ANOMALI — dipanggil dari AnomalyControlController
    // ══════════════════════════════════════════════════════════

    /**
     * Proses keputusan reviewer terhadap satu anomali.
     *
     * Flow:
     *   approved           → anomaly.status = approved, data.workflow_status = approved
     *   approved_with_note → anomaly.status = approved_with_note, data.workflow_status = approved_with_note
     *   rejected           → anomaly.status = rejected, data.workflow_status = rejected
     *   revised            → anomaly.status = revised, data.workflow_status = revised
     *
     * @throws ValidationException jika justification wajib tapi kosong
     * @throws \RuntimeException   jika anomali sudah resolved
     */
    public function reviewAnomaly(
        Anomaly $anomaly,
        string  $decision,
        ?string $justification = null,
        ?string $notes         = null,
    ): AnomalyReview {
        // Guard: anomali yang sudah resolved tidak bisa di-review ulang
        if ($anomaly->isResolved()) {
            throw new \RuntimeException(
                "Anomali #{$anomaly->anomalies_id} sudah diproses (status: {$anomaly->status_label}). "
                . "Tidak dapat di-review ulang."
            );
        }

        // Validasi: keputusan tertentu wajib isi justification
        if (AnomalyReview::requiresJustification($decision) && empty(trim($justification ?? ''))) {
            throw ValidationException::withMessages([
                'justification' => "Justification wajib diisi untuk keputusan '{$decision}'.",
            ]);
        }

        $reviewer = Auth::user();

        DB::beginTransaction();
        try {
            // 1. Simpan log review
            $review = AnomalyReview::create([
                'anomaly_id'    => $anomaly->anomalies_id,
                'reviewer_id'   => $reviewer->user_id,
                'decision'      => $decision,
                'justification' => $justification,
                'notes'         => $notes,
            ]);

            // 2. Update status anomali
            $anomaly->update(['status' => $decision]);

            // 3. Update workflow_status pada data
            $data = $anomaly->data;
            if ($data) {
                $oldWorkflow = $data->workflow_status;

                $data->update([
                    'workflow_status' => $decision,
                    'reviewer_note'   => $justification ?? $notes,
                    'reviewed_by'     => $reviewer->user_id,
                    'reviewed_at'     => now(),
                ]);

                // 4. Catat ke audit trail
                $this->auditTrail->record(
                    tableName:  'data',
                    recordId:   $data->id,
                    actionType: $this->mapDecisionToAction($decision),
                    oldValue:   ['workflow_status' => $oldWorkflow],
                    newValue:   ['workflow_status' => $decision],
                    reason:     $justification ?? $notes,
                    userId:     $reviewer->user_id,
                );
            }

            DB::commit();
            return $review;

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ══════════════════════════════════════════════════════════
    // SUBMIT FOR REVIEW — operator mengirim ke Under Review
    // ══════════════════════════════════════════════════════════

    /**
     * Operator/admin mengirim data anomali ke status Under Review
     * agar administrator bisa melakukan review formal.
     */
    public function submitForReview(Data $data, ?string $note = null): Data
    {
        if ($data->workflow_status !== Data::WORKFLOW_WARNING) {
            throw new \RuntimeException(
                "Hanya data dengan status Warning yang dapat dikirim untuk review. "
                . "Status saat ini: {$data->workflow_status_label}."
            );
        }

        $oldStatus = $data->workflow_status;

        $data->update([
            'workflow_status' => Data::WORKFLOW_UNDER_REVIEW,
            'reviewer_note'   => $note,
        ]);

        // Update semua anomali aktif pada data ini ke under_review
        $data->anomalies()
            ->whereIn('status', [Anomaly::STATUS_WARNING])
            ->update(['status' => Anomaly::STATUS_UNDER_REVIEW]);

        $this->auditTrail->record(
            tableName:  'data',
            recordId:   $data->id,
            actionType: 'updated',
            oldValue:   ['workflow_status' => $oldStatus],
            newValue:   ['workflow_status' => Data::WORKFLOW_UNDER_REVIEW],
            reason:     $note ?? 'Dikirim untuk review',
            userId:     Auth::user()?->user_id,
        );

        return $data->fresh();
    }

    // ══════════════════════════════════════════════════════════
    // BULK APPROVE — approve banyak anomali sekaligus
    // ══════════════════════════════════════════════════════════

    /**
     * Approve banyak anomali sekaligus.
     * Digunakan untuk tombol "Approve All" di halaman Control.
     *
     * @param  array<int>  $anomalyIds  Array anomalies_id
     * @param  string      $decision    approved | approved_with_note
     * @param  string|null $justification
     * @return array{success: int, skipped: int}
     */
    public function bulkReview(
        array   $anomalyIds,
        string  $decision,
        ?string $justification = null,
    ): array {
        $success = 0;
        $skipped = 0;

        $anomalies = Anomaly::whereIn('anomalies_id', $anomalyIds)
            ->with('data')
            ->get();

        foreach ($anomalies as $anomaly) {
            try {
                $this->reviewAnomaly($anomaly, $decision, $justification);
                $success++;
            } catch (\Throwable) {
                $skipped++;
            }
        }

        return compact('success', 'skipped');
    }

    // ══════════════════════════════════════════════════════════
    // APPROVE DATA (tanpa anomali) — flow approval biasa
    // ══════════════════════════════════════════════════════════

    /**
     * Approve data yang tidak memiliki anomali (flow normal).
     * Menggantikan DataController::approve() yang lama.
     */
    public function approveData(Data $data, ?string $note = null): Data
    {
        $oldStatus    = $data->status;
        $oldWorkflow  = $data->workflow_status;

        $data->update([
            'status'          => Data::STATUS_AVAILABLE,
            'workflow_status' => Data::WORKFLOW_APPROVED,
            'reviewer_note'   => $note,
            'reviewed_by'     => Auth::user()?->user_id,
            'reviewed_at'     => now(),
        ]);

        $this->auditTrail->recordApproved(
            tableName: 'data',
            recordId:  $data->id,
            note:      $note,
            userId:    Auth::user()?->user_id,
        );

        return $data->fresh();
    }

    /**
     * Tolak data (flow normal).
     */
    public function rejectData(Data $data, string $reason): Data
    {
        $data->update([
            'status'          => Data::STATUS_REJECTED,
            'workflow_status' => Data::WORKFLOW_REJECTED,
            'reviewer_note'   => $reason,
            'reviewed_by'     => Auth::user()?->user_id,
            'reviewed_at'     => now(),
        ]);

        $this->auditTrail->recordRejected(
            tableName: 'data',
            recordId:  $data->id,
            reason:    $reason,
            userId:    Auth::user()?->user_id,
        );

        return $data->fresh();
    }

    // ══════════════════════════════════════════════════════════
    // QUERY HELPERS — untuk halaman Control
    // ══════════════════════════════════════════════════════════

    /**
     * Statistik ringkasan untuk header halaman Control.
     */
    public function getControlStats(): array
    {
        return [
            'total_warning'      => Anomaly::where('status', Anomaly::STATUS_WARNING)->count(),
            'total_under_review' => Anomaly::where('status', Anomaly::STATUS_UNDER_REVIEW)->count(),
            'total_resolved'     => Anomaly::whereIn('status', [
                Anomaly::STATUS_APPROVED,
                Anomaly::STATUS_APPROVED_WITH_NOTE,
                Anomaly::STATUS_REJECTED,
                Anomaly::STATUS_REVISED,
            ])->count(),
            'critical_pending'   => Anomaly::where('severity', Anomaly::SEVERITY_CRITICAL)
                ->whereIn('status', [Anomaly::STATUS_WARNING, Anomaly::STATUS_UNDER_REVIEW])
                ->count(),
            'high_pending'       => Anomaly::where('severity', Anomaly::SEVERITY_HIGH)
                ->whereIn('status', [Anomaly::STATUS_WARNING, Anomaly::STATUS_UNDER_REVIEW])
                ->count(),
        ];
    }

    /**
     * Histori keputusan stakeholder untuk ditampilkan di timeline.
     *
     * @param  int  $dataId  data.id
     * @return \Illuminate\Database\Eloquent\Collection<AnomalyReview>
     */
    public function getDecisionHistory(int $dataId): \Illuminate\Database\Eloquent\Collection
    {
        return AnomalyReview::with(['reviewer', 'anomaly'])
            ->whereHas('anomaly', fn($q) => $q->where('id', $dataId))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Trend anomali per hari untuk grafik (30 hari terakhir).
     *
     * @return \Illuminate\Support\Collection<array{date: string, count: int, severity: string}>
     */
    public function getAnomalyTrend(int $days = 30): \Illuminate\Support\Collection
    {
        return Anomaly::where('detected_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(detected_at) as date, severity, COUNT(*) as total')
            ->groupBy('date', 'severity')
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(function ($group, $date) {
                $bySeverity = $group->pluck('total', 'severity')->toArray();
                return [
                    'date'     => $date,
                    'total'    => $group->sum('total'),
                    'low'      => $bySeverity['low']      ?? 0,
                    'medium'   => $bySeverity['medium']   ?? 0,
                    'high'     => $bySeverity['high']     ?? 0,
                    'critical' => $bySeverity['critical'] ?? 0,
                ];
            })
            ->values();
    }

    // ══════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════

    private function mapDecisionToAction(string $decision): string
    {
        return match ($decision) {
            AnomalyReview::DECISION_APPROVED,
            AnomalyReview::DECISION_APPROVED_WITH_NOTE => 'approved',
            AnomalyReview::DECISION_REJECTED           => 'rejected',
            AnomalyReview::DECISION_REVISED            => 'revised',
            default                                    => 'updated',
        };
    }
}