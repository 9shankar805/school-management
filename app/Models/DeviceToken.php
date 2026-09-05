<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceToken extends Model
{
    protected $fillable = [
        'user_id',
        'school_id',
        'token',
        'provider',
        'platform',
        'device_name',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Upsert a token for a user (insert or update last_used_at).
     */
    public static function register(int $userId, string $token, array $extra = []): self
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'token' => $token],
            array_merge([
                'school_id'   => $extra['school_id']   ?? null,
                'provider'    => $extra['provider']    ?? 'fcm',
                'platform'    => $extra['platform']    ?? 'web',
                'device_name' => $extra['device_name'] ?? null,
                'last_used_at'=> now(),
            ], $extra)
        );
    }
}
