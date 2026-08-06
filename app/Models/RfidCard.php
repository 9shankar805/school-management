<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RfidCard extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'user_id', 'card_uid', 'card_type', 'is_active', 'issued_at', 'expires_at', 'notes',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'issued_at'  => 'datetime',
        'expires_at' => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsValidAttribute(): bool
    {
        return $this->is_active && ! $this->is_expired;
    }
}
