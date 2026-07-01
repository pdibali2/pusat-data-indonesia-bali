<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Klasifikasi extends Model
{
    protected $table = 'klasifikasi';

    protected $primaryKey = 'klasifikasi_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'klasifikasi_id',
        'nama_klasifikasi',
        'icon',
        'status',
    ];

    // ── Relations ──────────────────────────────────────────────
    public function metadata(): HasMany
    {
        return $this->hasMany(Metadata::class, 'klasifikasi_id', 'klasifikasi_id');
    }
}