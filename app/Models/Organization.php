<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organization extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $table = 'organizations';
    protected $primaryKey = 'organization_id';

    protected $fillable = [
        'name',
        'owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class, 'organization_id', 'organization_id');
    }

    public function activeMembers(): HasMany
    {
        return $this->members()->where('status', 'active');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Layanan::class, 'organization_id', 'organization_id');
    }
}
