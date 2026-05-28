<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AnomalyRule;
use App\Models\AnomalyReview;

class Anomaly extends Model
{
    protected $table      = 'anomalies';
    protected $primaryKey = 'anomalies_id';
    public    $timestamps = true;

    // ─── SEVERITY CONSTANTS ───────────────────────────────────
    const SEVERITY_LOW      = 'low';
    const SEVERITY_MEDIUM   = 'medium';
    const SEVERITY_HIGH     = 'high';
    const SEVERITY_CRITICAL = 'critical';

    // ─── TYPE CONSTANTS ───────────────────────────────────────
    const TYPE_EXTREME_INCREASE = 'extreme_increase';
    const TYPE_EXTREME_DECREASE = 'extreme_decrease';
    const TYPE_SOURCE_CONFLICT  = 'source_conflict';
    const TYPE_UNREASONABLE     = 'unreasonable_value';

    // ─── WORKFLOW STATUS CONSTANTS ────────────────────────────
    const STATUS_WARNING            = 'warning';
    const STATUS_UNDER_REVIEW       = 'under_review';
    const STATUS_APPROVED           = 'approved';
    const STATUS_APPROVED_WITH_NOTE = 'approved_with_note';
    const STATUS_REJECTED           = 'rejected';
    const STATUS_REVISED            = 'revised';

    protected $fillable = [
        'id',           // FK ke data.id
        'table_name',
        'anomaly_type',
        'severity',
        'previous_value',
        'current_value',
        'percentage_change',
        'message',
        'status',
        'detected_at',
    ];

    protected $casts = [
        'previous_value'    => 'decimal:4',
        'current_value'     => 'decimal:4',
        'percentage_change' => 'decimal:4',
        'detected_at'       => 'datetime',
    ];

    // ─── RELASI ───────────────────────────────────────────────

    /**
     * Data yang memiliki anomali ini
     * FK: anomalies.id → data.id
     */
    public function data(): BelongsTo
    {
        return $this->belongsTo(Data::class, 'id', 'id');
    }

    /**
     * Semua review/keputusan untuk anomali ini
     * FK: anomaly_reviews.anomaly_id → anomalies.anomalies_id
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(AnomalyReview::class, 'anomaly_id', 'anomalies_id')
                    ->orderBy('created_at', 'desc');
    }

    public function latestReview(): HasMany
    {
        return $this->hasMany(AnomalyReview::class, 'anomaly_id', 'anomalies_id')
                    ->latest()
                    ->limit(1);
    }

    // ─── SCOPES ───────────────────────────────────────────────

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePendingReview($query)
    {
        return $query->whereIn('status', [
            self::STATUS_WARNING,
            self::STATUS_UNDER_REVIEW,
        ]);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }

    // ─── ACCESSORS ────────────────────────────────────────────

    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_LOW      => 'Low',
            self::SEVERITY_MEDIUM   => 'Medium',
            self::SEVERITY_HIGH     => 'High',
            self::SEVERITY_CRITICAL => 'Critical',
            default                 => ucfirst($this->severity),
        };
    }

    public function getSeverityStyleAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_LOW      => 'background:#dbeafe; color:#1d4ed8;',
            self::SEVERITY_MEDIUM   => 'background:#fef9c3; color:#a16207;',
            self::SEVERITY_HIGH     => 'background:#ffedd5; color:#c2410c;',
            self::SEVERITY_CRITICAL => 'background:#fee2e2; color:#b91c1c;',
            default                 => 'background:#f3f4f6; color:#374151;',
        };
    }

    public function getSeverityIconAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_LOW      => 'fas fa-info-circle',
            self::SEVERITY_MEDIUM   => 'fas fa-exclamation-circle',
            self::SEVERITY_HIGH     => 'fas fa-exclamation-triangle',
            self::SEVERITY_CRITICAL => 'fas fa-skull-crossbones',
            default                 => 'fas fa-question-circle',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_WARNING            => 'Warning',
            self::STATUS_UNDER_REVIEW       => 'Under Review',
            self::STATUS_APPROVED           => 'Approved',
            self::STATUS_APPROVED_WITH_NOTE => 'Approved with Note',
            self::STATUS_REJECTED           => 'Rejected',
            self::STATUS_REVISED            => 'Revised',
            default                         => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusStyleAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_WARNING            => 'background:#fef9c3; color:#a16207;',
            self::STATUS_UNDER_REVIEW       => 'background:#e0f2fe; color:#0369a1;',
            self::STATUS_APPROVED           => 'background:#dcfce7; color:#15803d;',
            self::STATUS_APPROVED_WITH_NOTE => 'background:#d1fae5; color:#065f46;',
            self::STATUS_REJECTED           => 'background:#fee2e2; color:#b91c1c;',
            self::STATUS_REVISED            => 'background:#f3e8ff; color:#6d28d9;',
            default                         => 'background:#f3f4f6; color:#374151;',
        };
    }

    public function getAnomalyTypeLabelAttribute(): string
    {
        return match ($this->anomaly_type) {
            self::TYPE_EXTREME_INCREASE => 'Kenaikan Ekstrem',
            self::TYPE_EXTREME_DECREASE => 'Penurunan Ekstrem',
            self::TYPE_SOURCE_CONFLICT  => 'Konflik Sumber Data',
            self::TYPE_UNREASONABLE     => 'Nilai Tidak Wajar',
            default                     => ucfirst(str_replace('_', ' ', $this->anomaly_type)),
        };
    }

    public function getFormattedPercentageChangeAttribute(): string
    {
        if ($this->percentage_change === null) return '-';
        $val  = (float) $this->percentage_change;
        $sign = $val >= 0 ? '+' : '';
        return $sign . number_format($val, 2) . '%';
    }

    // ─── HELPERS ──────────────────────────────────────────────

    public function isPendingReview(): bool
    {
        return in_array($this->status, [
            self::STATUS_WARNING,
            self::STATUS_UNDER_REVIEW,
        ]);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_APPROVED_WITH_NOTE,
            self::STATUS_REJECTED,
            self::STATUS_REVISED,
        ]);
    }

    // ─── STATIC HELPERS ───────────────────────────────────────

    /**
     * Hitung severity dari persentase perubahan.
     * Baca threshold dari anomaly_rules, fallback ke default jika belum ada.
     */
    public static function calculateSeverity(
        float  $percentageChange,
        int    $metadataId = 0,
        string $frekuensi  = ''
    ): string {
        $abs = abs($percentageChange);

        try {
            $rule      = AnomalyRule::resolveForData($metadataId, $frekuensi);
            $tLow      = (float) ($rule->threshold_low      ?? 20);
            $tMedium   = (float) ($rule->threshold_medium   ?? 100);
            $tHigh     = (float) ($rule->threshold_high     ?? 500);
            $tCritical = (float) ($rule->threshold_critical ?? 5000);
        } catch (\Throwable) {
            [$tLow, $tMedium, $tHigh, $tCritical] = [20, 100, 500, 5000];
        }

        return match (true) {
            $abs >= $tCritical => self::SEVERITY_CRITICAL,
            $abs >= $tHigh     => self::SEVERITY_HIGH,
            $abs >= $tMedium   => self::SEVERITY_MEDIUM,
            default            => self::SEVERITY_LOW,
        };
    }

    public static function severityOptions(): array
    {
        return [
            self::SEVERITY_LOW      => 'Low',
            self::SEVERITY_MEDIUM   => 'Medium',
            self::SEVERITY_HIGH     => 'High',
            self::SEVERITY_CRITICAL => 'Critical',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_WARNING            => 'Warning',
            self::STATUS_UNDER_REVIEW       => 'Under Review',
            self::STATUS_APPROVED           => 'Approved',
            self::STATUS_APPROVED_WITH_NOTE => 'Approved with Note',
            self::STATUS_REJECTED           => 'Rejected',
            self::STATUS_REVISED            => 'Revised',
        ];
    }
}