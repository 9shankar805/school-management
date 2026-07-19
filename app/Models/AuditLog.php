<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'user_type', 'event', 'auditable_type', 'auditable_id',
        'old_values', 'new_values', 'url', 'ip_address', 'user_agent', 'tags',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Audit logs are never modified after creation
    public $timestamps = true;
    const UPDATED_AT = null;

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    // -----------------------------------------------------------------------
    // Static helper — write a log entry from anywhere
    // -----------------------------------------------------------------------

    public static function record(
        string $event,
        mixed  $model = null,
        array  $oldValues = [],
        array  $newValues = [],
        string $tags = ''
    ): static {
        $request = request();

        return static::create([
            'user_id'         => auth()->id(),
            'user_type'       => auth()->check() ? get_class(auth()->user()) : null,
            'event'           => $event,
            'auditable_type'  => $model ? get_class($model) : null,
            'auditable_id'    => $model?->getKey(),
            'old_values'      => $oldValues,
            'new_values'      => $newValues,
            'url'             => $request?->fullUrl(),
            'ip_address'      => $request?->ip(),
            'user_agent'      => $request?->userAgent(),
            'tags'            => $tags,
        ]);
    }
}
