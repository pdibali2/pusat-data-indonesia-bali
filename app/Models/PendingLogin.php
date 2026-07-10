<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingLogin extends Model
{
    public const LOGIN_CONFIRMATION_TIMEOUT_MINUTES = 2;

    protected $table = 'pending_logins';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_address',
        'device_info',
        'target_session_id',
        'session_token',
        'user_agent',
        'device_type',
        'browser',
        'estimated_location',
        'status',
        'created_at',
        'expires_at',
        'responded_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function targetSession(): BelongsTo
    {
        return $this->belongsTo(UserSession::class, 'target_session_id', 'user_session_id');
    }
}
