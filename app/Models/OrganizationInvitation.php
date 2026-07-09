<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationInvitation extends Model
{
    protected $table = 'organization_invitations';
    protected $primaryKey = 'organization_invitation_id';

    protected $fillable = [
        'organization_id',
        'email',
        'token',
        'user_id',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'invited')
            ->where(function ($sub) {
                $sub->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    public function getIsExpiredAttribute(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }
}
