<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorAuth extends Model
{
    protected $table = 'two_factor_auth';

    protected $fillable = [
        'user_id', 'enabled', 'method', 'secret', 'recovery_codes', 'confirmed_at',
    ];

    protected $hidden = ['secret', 'recovery_codes'];

    protected $casts = [
        'enabled'      => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConfirmed(): bool
    {
        return $this->enabled && $this->confirmed_at !== null;
    }
}
