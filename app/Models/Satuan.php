<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satuan extends Model
{
    protected $table      = 'satuan';
    protected $primaryKey = 'satuan_id';

    protected $fillable = [
        'nama_satuan',
        'simbol',
        'nilai_konversi',
    ];

    protected $casts = [
        'nilai_konversi' => 'decimal:8',
    ];

    public function data(): HasMany
    {
        return $this->hasMany(Data::class, 'satuan_id', 'satuan_id');
    }

    /**
     * Konversi nilai dari satuan asal ke satuan tujuan.
     * Return null jika salah satu satuan tidak ditemukan.
     */
    public static function convertValue(float $value, int $fromSatuanId, int $toSatuanId): ?float
    {
        if ($fromSatuanId === $toSatuanId) {
            return $value;
        }

        $from = static::find($fromSatuanId);
        $to   = static::find($toSatuanId);

        if (!$from || !$to || (float) $to->nilai_konversi == 0) {
            return null;
        }

        // Normalisasi ke satuan dasar dulu, lalu konversi ke satuan tujuan
        $baseValue = $value * (float) $from->nilai_konversi;
        return $baseValue / (float) $to->nilai_konversi;
    }

    /**
     * Cek apakah dua nilai (dengan satuan masing-masing) sama persis
     * setelah dikonversi ke satuan yang sama.
     */
    public static function isEquivalent(float $valueA, int $satuanIdA, float $valueB, int $satuanIdB): bool
    {
        $converted = static::convertValue($valueA, $satuanIdA, $satuanIdB);
        if ($converted === null) return false;

        // Toleransi floating point kecil (bukan toleransi bisnis — cuma untuk hindari 2.9999999 != 3)
        return abs($converted - $valueB) < 1e-6;
    }
}