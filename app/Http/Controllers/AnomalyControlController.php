<?php

namespace App\Http\Controllers;

use App\Models\Anomaly;
use App\Models\AnomalyReview;
use App\Models\AnomalyRule;
use App\Models\Data;
use App\Models\Metadata;
use App\Models\Location;
use App\Services\AnomalyDetectionService;
use App\Services\AuditTrailService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AnomalyControlController extends Controller
{
    public function __construct(
        private readonly AnomalyDetectionService $detector,
        private readonly WorkflowService         $workflow,
        private readonly AuditTrailService       $auditTrail,
    ) {}

    // ══════════════════════════════════════════════════════════
    // INDEX — Halaman Control Data Anomali
    // ══════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = Anomaly::with([
            'data.metadata',
            'data.location',
            'data.time',
            'data.user',
            'data.produsen',
            'reviews.reviewer',
        ]);

        // ── Filter severity ───────────────────────────────────
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // ── Filter status ─────────────────────────────────────
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: tampilkan yang belum resolved
            $query->whereIn('status', [
                Anomaly::STATUS_WARNING,
                Anomaly::STATUS_UNDER_REVIEW,
            ]);
        }

        // ── Filter metadata ───────────────────────────────────
        if ($request->filled('metadata_id')) {
            $query->whereHas('data', fn($q) =>
                $q->where('metadata_id', $request->metadata_id)
            );
        }

        // ── Filter anomaly_type ───────────────────────────────
        if ($request->filled('anomaly_type')) {
            $query->where('anomaly_type', $request->anomaly_type);
        }

        // ── Search ─────────────────────────────────────────────
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('message', 'like', "%{$s}%")
                  ->orWhereHas('data.metadata', fn($m) => $m->where('nama', 'like', "%{$s}%"))
                  ->orWhereHas('data.location', fn($l) => $l->where('nama_wilayah', 'like', "%{$s}%"));
            });
        }

        // ── Sort: critical & high dulu, lalu terbaru ──────────
        $anomalies = $query
            ->orderByRaw("FIELD(severity, 'critical','high','medium','low')")
            ->orderBy('detected_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // ── Data untuk filter dropdown ─────────────────────────
        $metadataList  = Metadata::where('status', 2)->orderBy('nama')->get(['metadata_id', 'nama']);
        $severityOpts  = Anomaly::severityOptions();
        $statusOpts    = Anomaly::statusOptions();
        $typeOpts      = [
            Anomaly::TYPE_EXTREME_INCREASE => 'Kenaikan Ekstrem',
            Anomaly::TYPE_EXTREME_DECREASE => 'Penurunan Ekstrem',
            Anomaly::TYPE_SOURCE_CONFLICT  => 'Konflik Sumber Data',
            Anomaly::TYPE_UNREASONABLE     => 'Nilai Tidak Wajar',
        ];

        // ── Stats untuk header ────────────────────────────────
        $stats = $this->workflow->getControlStats();

        // ── Trend chart (7 hari terakhir) ─────────────────────
        $trendData = $this->workflow->getAnomalyTrend(7);

        $statusData = [
            'labels' => ['Warning', 'Under Review', 'Approved', 'Approved+Note', 'Rejected'],
            'values' => [
                Anomaly::where('status', Anomaly::STATUS_WARNING)->count(),
                Anomaly::where('status', Anomaly::STATUS_UNDER_REVIEW)->count(),
                Anomaly::where('status', Anomaly::STATUS_APPROVED)->count(),
                Anomaly::where('status', Anomaly::STATUS_APPROVED_WITH_NOTE)->count(),
                Anomaly::where('status', Anomaly::STATUS_REJECTED)->count(),
            ],
        ];

        return view('pages.anomaly.control.index', compact(
            'anomalies',
            'metadataList',
            'severityOpts',
            'statusOpts',
            'typeOpts',
            'stats',
            'trendData',
            'statusData',
        ));
    }

    public function scanAll(Request $request)
    {
        try {
            $metaId = $request->input('metadata_id');

            $stats = $this->detector->scanExistingData(
                100,    // batchSize
                true,   // scanAll
                $metaId // metadata filter (nullable)
            );

            return response()->json([
                'success' => true,
                'message' => "Scan selesai. {$stats['scanned']} data diperiksa, {$stats['anomaliesFound']} anomali ditemukan.",
                'stats'   => $stats,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Scan gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════
    // SHOW — Detail satu anomali + histori review + perbandingan
    // ══════════════════════════════════════════════════════════

    public function show(Anomaly $anomaly)
    {
        $anomaly->load([
            'data.metadata',
            'data.location',
            'data.time',
            'data.user',
            'data.produsen',
            'data.rujukan',
            'reviews.reviewer',
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'anomaly' => [
                    'message'           => $anomaly->message,
                    'severity_badge'    => $anomaly->severity,
                    'table_name'        => $anomaly->table_name,
                    'anomaly_type'      => ucwords(str_replace('_', ' ', $anomaly->anomaly_type)),
                    'previous_value'    => $anomaly->previous_value,
                    'current_value'     => $anomaly->current_value,
                    'percentage_change' => $anomaly->percentage_change,
                    'detected_at'       => \Carbon\Carbon::parse($anomaly->detected_at)->format('d M Y H:i'),
                ]
            ]);
        }

        $data = $anomaly->data;

        // ── Histori keputusan stakeholder ─────────────────────
        $decisionHistory = $this->workflow->getDecisionHistory($data->id);

        // ── Perbandingan antar sumber ─────────────────────────
        $sourceComparison = $this->detector->compareSourceValues(
            $data->metadata_id,
            $data->location_id,
            $data->time_id,
        );

        // ── Audit trail record ini ────────────────────────────
        $auditHistory = $this->auditTrail->getHistory('data', $data->id);

        // ── Data untuk form review ────────────────────────────
        $decisionOptions = AnomalyReview::decisionOptions();

        return view('pages.anomaly.control.show', compact(
            'anomaly',
            'data',
            'decisionHistory',
            'sourceComparison',
            'auditHistory',
            'decisionOptions',
        ));
    }

    // ══════════════════════════════════════════════════════════
    // REVIEW — Proses keputusan reviewer (POST)
    // ══════════════════════════════════════════════════════════

    public function review(Request $request, Anomaly $anomaly)
    {
        $request->validate([
            'decision'      => 'required|in:approved,approved_with_note,rejected,revised',
            'justification' => 'nullable|string|max:2000',
            'notes'         => 'nullable|string|max:1000',
        ]);

        try {
            $review = $this->workflow->reviewAnomaly(
                anomaly:       $anomaly,
                decision:      $request->decision,
                justification: $request->justification,
                notes:         $request->notes,
            );

            $label = $review->decision_label;

            if ($request->wantsJson()) {
                return response()->json([
                    'success'          => true,
                    'message'          => "Anomali #{$anomaly->anomalies_id} berhasil diproses: {$label}.",
                    'new_status'       => $anomaly->fresh()->status,
                    'new_status_label' => $anomaly->fresh()->status_label,
                    'new_status_style' => $anomaly->fresh()->status_style,
                    'workflow_status'  => $anomaly->data?->fresh()->workflow_status,
                ]);
            }

            return redirect()
                ->route('anomaly.control.index')
                ->with('success', "Anomali #{$anomaly->anomalies_id} berhasil diproses: {$label}.");

        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\RuntimeException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 409);
            }
            return back()->withErrors(['decision' => $e->getMessage()]);
        }
    }

    // ══════════════════════════════════════════════════════════
    // BULK REVIEW — approve/reject banyak anomali (POST)
    // ══════════════════════════════════════════════════════════

    public function bulkReview(Request $request)
    {
        $request->validate([
            'anomaly_ids'   => 'required|array|min:1',
            'anomaly_ids.*' => 'integer|exists:anomalies,anomalies_id',
            'decision'      => 'required|in:approved,approved_with_note,rejected,revised',
            'justification' => 'nullable|string|max:2000',
        ]);

        // Jika decision butuh justification, wajib diisi
        if (AnomalyReview::requiresJustification($request->decision)
            && empty(trim($request->justification ?? ''))
        ) {
            return back()->withErrors([
                'justification' => "Justification wajib diisi untuk keputusan '{$request->decision}'.",
            ])->withInput();
        }

        $result = $this->workflow->bulkReview(
            anomalyIds:    $request->anomaly_ids,
            decision:      $request->decision,
            justification: $request->justification,
        );

        $msg = "{$result['success']} anomali berhasil diproses.";
        if ($result['skipped'] > 0) {
            $msg .= " {$result['skipped']} dilewati (sudah resolved).";
        }

        return redirect()->route('anomaly.control.index')->with('success', $msg);
    }

    // ══════════════════════════════════════════════════════════
    // SUBMIT FOR REVIEW — operator kirim ke Under Review (POST)
    // ══════════════════════════════════════════════════════════

    public function submitForReview(Request $request, Data $datum)
    {
        $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $this->workflow->submitForReview($datum, $request->note);

            return redirect()
                ->route('anomaly.control.index')
                ->with('success', "Data #{$datum->id} berhasil dikirim untuk review.");

        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ══════════════════════════════════════════════════════════
    // COMPARE SOURCES — AJAX perbandingan sumber data
    // ══════════════════════════════════════════════════════════

    public function compareSources(Request $request)
    {
        $request->validate([
            'metadata_id' => 'required|integer|exists:metadata,metadata_id',
            'location_id' => 'required|integer|exists:location,location_id',
            'time_id'     => 'required|integer|exists:time,time_id',
        ]);

        $comparison = $this->detector->compareSourceValues(
            $request->metadata_id,
            $request->location_id,
            $request->time_id,
        );

        return response()->json([
            'success' => true,
            'data'    => $comparison->values(),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // AUDIT TRAIL — AJAX histori perubahan satu data
    // ══════════════════════════════════════════════════════════

    public function auditHistory(Request $request, int $dataId)
    {
        $history = $this->auditTrail->getHistory('data', $dataId);

        return response()->json([
            'success' => true,
            'data'    => $history->map(fn($h) => [
                'audit_trail_id' => $h->audit_trail_id,
                'action_type'    => $h->action_type,
                'action_label'   => $h->action_label,
                'action_style'   => $h->action_style,
                'action_icon'    => $h->action_icon,
                'old_value'      => $h->old_value,
                'new_value'      => $h->new_value,
                'reason'         => $h->reason,
                'user_name'      => $h->user?->name ?? 'Sistem',
                'created_at'     => $h->created_at?->format('d/m/Y H:i'),
            ]),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // ANOMALY RULES — kelola threshold (index & update)
    // ══════════════════════════════════════════════════════════

    public function rules()
    {
        $rules = AnomalyRule::with('metadata')
            ->orderBy('metadata_id')
            ->orderBy('frekuensi')
            ->get();

        $metadataList = Metadata::where('status', 2)->orderBy('nama')->get(['metadata_id', 'nama']);

        return view('pages.anomaly.control.rules', compact('rules', 'metadataList'));
    }

    public function updateRule(Request $request, AnomalyRule $rule)
    {
        $request->validate([
            'threshold_low'      => 'required|numeric|min:0',
            'threshold_medium'   => 'required|numeric|gt:threshold_low',
            'threshold_high'     => 'required|numeric|gt:threshold_medium',
            'threshold_critical' => 'required|numeric|gt:threshold_high',
            'is_active'          => 'boolean',
        ]);

        $old = $rule->only([
            'threshold_low', 'threshold_medium', 'threshold_high', 'threshold_critical', 'is_active',
        ]);

        $rule->update([
            'threshold_low'      => $request->threshold_low,
            'threshold_medium'   => $request->threshold_medium,
            'threshold_high'     => $request->threshold_high,
            'threshold_critical' => $request->threshold_critical,
            'is_active'          => $request->boolean('is_active', true),
        ]);

        $this->auditTrail->recordUpdated(
            tableName: 'anomaly_rules',
            recordId:  $rule->anomaly_rules_id,
            oldValue:  $old,
            newValue:  $rule->fresh()->only([
                'threshold_low', 'threshold_medium', 'threshold_high', 'threshold_critical', 'is_active',
            ]),
            reason: 'Update threshold anomaly rule oleh admin',
        );

        return redirect()
            ->route('anomaly.control.rules')
            ->with('success', "Rule \"{$rule->name}\" berhasil diperbarui.");
    }

    // ══════════════════════════════════════════════════════════
    // STATS — AJAX data untuk grafik trend
    // ══════════════════════════════════════════════════════════

    public function trendStats(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $days = min(max($days, 7), 90); // clamp: 7–90 hari

        $trend = $this->workflow->getAnomalyTrend($days);
        $stats = $this->workflow->getControlStats();

        return response()->json([
            'success' => true,
            'trend'   => $trend,
            'stats'   => $stats,
        ]);
    }
}