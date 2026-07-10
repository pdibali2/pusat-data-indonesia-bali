<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginVerification extends Model
{
    protected $table = 'login_verifications';

    protected $fillable = [
        'user_id',
        'new_session_id',
        'device_type',
        'browser',
        'estimated_location',
        'ip_address',
        'status',
        'responded_by_session_id',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function newSession(): BelongsTo
    {
        return $this->belongsTo(UserSession::class, 'new_session_id', 'user_session_id');
    }

    public function respondedBySession(): BelongsTo
    {
        return $this->belongsTo(UserSession::class, 'responded_by_session_id', 'user_session_id');
    }
}
