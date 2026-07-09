<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    protected $table = 'user_sessions';
    protected $primaryKey = 'user_session_id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'session_id',
        'session_token',
        'ip_address',
        'user_agent',
        'login_at',
        'is_active',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
