<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnomalyReview extends Model
{
    protected $table      = 'anomaly_reviews';
    protected $primaryKey = 'anomaly_reviews_id';
    public    $timestamps = true;

    // ─── DECISION CONSTANTS ───────────────────────────────────
    const DECISION_APPROVED           = 'approved';
    const DECISION_APPROVED_WITH_NOTE = 'approved_with_note';
    const DECISION_REJECTED           = 'rejected';
    const DECISION_REVISED            = 'revised';

    protected $fillable = [
        'anomaly_id',    // FK → anomalies.anomalies_id
        'reviewer_id',   // FK → user.user_id
        'decision',
        'justification',
        'notes',
    ];

    // ─── RELASI ───────────────────────────────────────────────

    public function anomaly(): BelongsTo
    {
        return $this->belongsTo(Anomaly::class, 'anomaly_id', 'anomalies_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id', 'user_id');
    }

    // ─── ACCESSORS ────────────────────────────────────────────

    public function getDecisionLabelAttribute(): string
    {
        return match ($this->decision) {
            self::DECISION_APPROVED           => 'Disetujui',
            self::DECISION_APPROVED_WITH_NOTE => 'Disetujui dengan Catatan',
            self::DECISION_REJECTED           => 'Ditolak',
            self::DECISION_REVISED            => 'Perlu Revisi',
            default                           => ucfirst(str_replace('_', ' ', $this->decision)),
        };
    }

    public function getDecisionStyleAttribute(): string
    {
        return match ($this->decision) {
            self::DECISION_APPROVED           => 'background:#dcfce7; color:#15803d;',
            self::DECISION_APPROVED_WITH_NOTE => 'background:#d1fae5; color:#065f46;',
            self::DECISION_REJECTED           => 'background:#fee2e2; color:#b91c1c;',
            self::DECISION_REVISED            => 'background:#f3e8ff; color:#6d28d9;',
            default                           => 'background:#f3f4f6; color:#374151;',
        };
    }

    public function getDecisionIconAttribute(): string
    {
        return match ($this->decision) {
            self::DECISION_APPROVED           => 'fas fa-check-circle',
            self::DECISION_APPROVED_WITH_NOTE => 'fas fa-check-double',
            self::DECISION_REJECTED           => 'fas fa-times-circle',
            self::DECISION_REVISED            => 'fas fa-redo',
            default                           => 'fas fa-circle',
        };
    }

    // ─── STATIC HELPERS ───────────────────────────────────────

    public static function decisionOptions(): array
    {
        return [
            self::DECISION_APPROVED           => 'Disetujui',
            self::DECISION_APPROVED_WITH_NOTE => 'Disetujui dengan Catatan',
            self::DECISION_REJECTED           => 'Ditolak',
            self::DECISION_REVISED            => 'Perlu Revisi',
        ];
    }

    /**
     * Keputusan yang wajib menyertakan justification
     */
    public static function requiresJustification(string $decision): bool
    {
        return in_array($decision, [
            self::DECISION_APPROVED_WITH_NOTE,
            self::DECISION_REJECTED,
            self::DECISION_REVISED,
        ]);
    }
}