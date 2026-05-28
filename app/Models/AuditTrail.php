<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditTrail extends Model
{
    protected $table      = 'audit_trails';
    protected $primaryKey = 'audit_trail_id';

    // Audit trail hanya punya created_at, tidak ada updated_at
    const UPDATED_AT = null;

    // ─── ACTION TYPE CONSTANTS ────────────────────────────────
    const ACTION_CREATED   = 'created';
    const ACTION_UPDATED   = 'updated';
    const ACTION_DELETED   = 'deleted';
    const ACTION_APPROVED  = 'approved';
    const ACTION_REJECTED  = 'rejected';
    const ACTION_REVISED   = 'revised';
    const ACTION_SCREENED  = 'screened';   // screening otomatis oleh sistem
    const ACTION_IMPORTED  = 'imported';   // import dari Excel

    protected $fillable = [
        'user_id',
        'table_name',
        'record_id',
        'action_type',
        'old_value',
        'new_value',
        'reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_value'  => 'array',
        'new_value'  => 'array',
        'created_at' => 'datetime',
    ];

    // ─── RELASI ───────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // ─── ACCESSORS ────────────────────────────────────────────

    public function getActionLabelAttribute(): string
    {
        return match ($this->action_type) {
            self::ACTION_CREATED  => 'Dibuat',
            self::ACTION_UPDATED  => 'Diperbarui',
            self::ACTION_DELETED  => 'Dihapus',
            self::ACTION_APPROVED => 'Disetujui',
            self::ACTION_REJECTED => 'Ditolak',
            self::ACTION_REVISED  => 'Direvisi',
            self::ACTION_SCREENED => 'Screening Otomatis',
            self::ACTION_IMPORTED => 'Import Excel',
            default               => ucfirst(str_replace('_', ' ', $this->action_type)),
        };
    }

    public function getActionStyleAttribute(): string
    {
        return match ($this->action_type) {
            self::ACTION_CREATED  => 'background:#dcfce7; color:#15803d;',
            self::ACTION_UPDATED  => 'background:#e0f2fe; color:#0369a1;',
            self::ACTION_DELETED  => 'background:#fee2e2; color:#b91c1c;',
            self::ACTION_APPROVED => 'background:#dcfce7; color:#15803d;',
            self::ACTION_REJECTED => 'background:#fee2e2; color:#b91c1c;',
            self::ACTION_REVISED  => 'background:#f3e8ff; color:#6d28d9;',
            self::ACTION_SCREENED => 'background:#fef9c3; color:#a16207;',
            self::ACTION_IMPORTED => 'background:#f0f9ff; color:#0369a1;',
            default               => 'background:#f3f4f6; color:#374151;',
        };
    }

    public function getActionIconAttribute(): string
    {
        return match ($this->action_type) {
            self::ACTION_CREATED  => 'fas fa-plus-circle',
            self::ACTION_UPDATED  => 'fas fa-edit',
            self::ACTION_DELETED  => 'fas fa-trash',
            self::ACTION_APPROVED => 'fas fa-check-circle',
            self::ACTION_REJECTED => 'fas fa-times-circle',
            self::ACTION_REVISED  => 'fas fa-redo',
            self::ACTION_SCREENED => 'fas fa-search',
            self::ACTION_IMPORTED => 'fas fa-file-import',
            default               => 'fas fa-circle',
        };
    }

    // ─── STATIC FACTORY METHOD ────────────────────────────────

    /**
     * Buat audit trail baru dengan data lengkap.
     * Dipanggil dari AuditTrailService — jangan panggil langsung dari controller.
     */
    public static function record(
        string  $tableName,
        int|string $recordId,
        string  $actionType,
        ?array  $oldValue   = null,
        ?array  $newValue   = null,
        ?int    $userId     = null,
        ?string $reason     = null,
        ?string $ipAddress  = null,
        ?string $userAgent  = null,
    ): static {
        return static::create([
            'user_id'    => $userId,
            'table_name' => $tableName,
            'record_id'  => (string) $recordId,
            'action_type'=> $actionType,
            'old_value'  => $oldValue,
            'new_value'  => $newValue,
            'reason'     => $reason,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}