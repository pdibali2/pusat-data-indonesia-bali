<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnomalyRule extends Model
{
    protected $table      = 'anomaly_rules';
    protected $primaryKey = 'anomaly_rules_id';
    public    $timestamps = true;

    protected $fillable = [
        'name',
        'code',
        'rule_type',
        'threshold_low',
        'threshold_medium',
        'threshold_high',
        'threshold_critical',
        'metadata_id',
        'frekuensi',
        'is_active',
        'description',
    ];

    protected $casts = [
        'threshold_low'      => 'decimal:4',
        'threshold_medium'   => 'decimal:4',
        'threshold_high'     => 'decimal:4',
        'threshold_critical' => 'decimal:4',
        'is_active'          => 'boolean',
        'metadata_id'        => 'integer',
    ];

    // ─── RELASI ───────────────────────────────────────────────

    public function metadata(): BelongsTo
    {
        return $this->belongsTo(Metadata::class, 'metadata_id', 'metadata_id');
    }

    // ─── SCOPES ───────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── STATIC HELPERS ───────────────────────────────────────

    /**
     * Cari rule paling spesifik untuk metadata & frekuensi tertentu.
     *
     * Prioritas:
     *   1. metadata_id + frekuensi cocok
     *   2. metadata_id cocok, frekuensi null (berlaku semua frekuensi)
     *   3. metadata_id null (rule global)
     *   4. Hardcoded default (fallback terakhir)
     */
    public static function resolveForData(int $metadataId, string $frekuensi): static
    {
        // 1. Spesifik: metadata + frekuensi
        $rule = static::active()
            ->where('code', 'percentage_change')
            ->where('metadata_id', $metadataId)
            ->where('frekuensi', $frekuensi)
            ->first();

        if ($rule) return $rule;

        // 2. Spesifik metadata, semua frekuensi
        $rule = static::active()
            ->where('code', 'percentage_change')
            ->where('metadata_id', $metadataId)
            ->whereNull('frekuensi')
            ->first();

        if ($rule) return $rule;

        // 3. Global (berlaku semua metadata)
        $rule = static::active()
            ->where('code', 'percentage_change')
            ->whereNull('metadata_id')
            ->first();

        if ($rule) return $rule;

        // 4. Fallback hardcoded — tidak query DB lagi
        return new static([
            'threshold_low'      => 20,
            'threshold_medium'   => 100,
            'threshold_high'     => 500,
            'threshold_critical' => 5000,
        ]);
    }

    // ─── ACCESSORS ────────────────────────────────────────────

    public function getThresholdSummaryAttribute(): string
    {
        return "Low ≥{$this->threshold_low}% | Medium ≥{$this->threshold_medium}% "
             . "| High ≥{$this->threshold_high}% | Critical ≥{$this->threshold_critical}%";
    }
}