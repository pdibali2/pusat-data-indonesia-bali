<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    protected $table      = 'transaksi';
    protected $primaryKey = 'transaksi_id';

    protected $fillable = [
        'user_id',
        'layanan_id',
        'nama_layanan',
        'harga',
        'durasi',
        'durasi_type',
        'order_id',
        'snap_token',
        'payment_type',
        'midtrans_transaction_id',
        'status',
        'aktif_mulai',
        'aktif_sampai',
        'midtrans_payload',
    ];

    protected $casts = [
        'harga'           => 'decimal:2',
        'aktif_mulai'     => 'datetime',
        'aktif_sampai'    => 'datetime',
        'midtrans_payload' => 'array',
    ];

    // ── Relations ──────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id', 'layanan_id');
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopePending($query)    { return $query->where('status', 'pending'); }
    public function scopeSuccess($query)    { return $query->where('status', 'success'); }
    public function scopeFailed($query)     { return $query->where('status', 'failed'); }
    public function scopeCancelled($query)  { return $query->where('status', 'cancelled'); }

    // ── Helpers ────────────────────────────────────────────────
    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isSuccess(): bool   { return $this->status === 'success'; }
    public function isFailed(): bool    { return $this->status === 'failed'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    public function isAktif(): bool
    {
        if (! $this->isSuccess()) return false;
        if (is_null($this->aktif_sampai)) return true; // selamanya
        return now()->lessThanOrEqualTo($this->aktif_sampai);
    }

    public function getHargaFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    public function getDurasiLabelAttribute(): string
    {
        $map = [
            'harian'    => 'Hari',
            'mingguan'  => 'Minggu',
            'bulanan'   => 'Bulan',
            'tahunan'   => 'Tahun',
            'selamanya' => 'Selamanya',
        ];

        if ($this->durasi_type === 'selamanya') return 'Selamanya';

        return $this->durasi . ' ' . ($map[$this->durasi_type] ?? $this->durasi_type);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'success'   => '<span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Berhasil</span>',
            'pending'   => '<span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 text-xs font-medium px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span> Menunggu</span>',
            'failed'    => '<span class="inline-flex items-center gap-1 bg-red-50 text-red-700 text-xs font-medium px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> Gagal</span>',
            'cancelled' => '<span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-xs font-medium px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> Dibatalkan</span>',
            default     => $this->status,
        };
    }

    // ── Generate unique order_id ───────────────────────────────
    public static function generateOrderId(int $userId): string
    {
        return 'TRX-' . $userId . '-' . time();
    }

    // ── Hitung aktif_sampai berdasarkan durasi_type ────────────
    public static function hitungAktifSampai(string $durasi_type, int $durasi, Carbon $mulai): ?Carbon
    {
        if ($durasi_type === 'selamanya') return null;

        return match($durasi_type) {
            'harian'   => $mulai->copy()->addDays($durasi),
            'mingguan' => $mulai->copy()->addWeeks($durasi),
            'bulanan'  => $mulai->copy()->addMonths($durasi),
            'tahunan'  => $mulai->copy()->addYears($durasi),
            default    => $mulai->copy()->addMonths($durasi),
        };
    }
}