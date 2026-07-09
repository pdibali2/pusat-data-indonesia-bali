<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table      = 'layanan';
    protected $primaryKey = 'layanan_id';

    protected $fillable = [
        'nama_layanan',
        'harga',
        'durasi',
        'durasi_type',
        'status',
        'is_popular',
        'urutan',
        'thumbnail',
        'audience_type',
        'category',
        'organization_id',
        'max_seats',
        'max_concurrent_sessions',
        'max_templates',
    ];

    protected $casts = [
        'harga'                  => 'decimal:2',
        'is_popular'             => 'boolean',
        'organization_id'        => 'integer',
        'max_seats'              => 'integer',
        'max_concurrent_sessions'=> 'integer',
        'max_templates'          => 'integer',
    ];

    // ── Relations ──────────────────────────────────────────────
    public function fiturs()
    {
        return $this->hasMany(LayananFitur::class, 'layanan_id', 'layanan_id')
                    ->orderBy('urutan');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    // ── Helpers ────────────────────────────────────────────────
    public function isPublish(): bool  { return $this->status === 'publish'; }
    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isTakedown(): bool { return $this->status === 'takedown'; }

    public function getHargaFormatAttribute(): string
    {
        $harga = (float) ($this->harga ?? 0);

        return 'Rp ' . number_format($harga, 0, ',', '.');
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

        if ($this->durasi_type === 'selamanya') {
            return 'Selamanya';
        }

        return $this->durasi . ' ' . ($map[$this->durasi_type] ?? $this->durasi_type);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail
            ? asset('storage/' . $this->thumbnail)
            : null;
    }
}