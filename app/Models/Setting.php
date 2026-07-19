<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'school_id', 'group', 'key', 'value', 'type', 'label', 'is_public', 'is_encrypted',
    ];

    protected $casts = [
        'is_public'    => 'boolean',
        'is_encrypted' => 'boolean',
    ];

    // -----------------------------------------------------------------------
    // Static helpers — the primary way to read/write settings throughout app
    // -----------------------------------------------------------------------

    /**
     * Get a setting value with an optional default.
     * Results are cached for 60 minutes per school.
     */
    public static function get(string $key, mixed $default = null, ?int $schoolId = null): mixed
    {
        $cacheKey = "settings:{$schoolId}:{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default, $schoolId) {
            $setting = static::where('key', $key)
                ->where('school_id', $schoolId)
                ->first();

            if (! $setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set (upsert) a setting value and bust the cache.
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?int $schoolId = null): void
    {
        static::updateOrCreate(
            ['key' => $key, 'school_id' => $schoolId],
            ['group' => $group, 'value' => is_array($value) ? json_encode($value) : $value]
        );

        Cache::forget("settings:{$schoolId}:{$key}");
    }

    /**
     * Return all settings for a group as key => value array.
     */
    public static function group(string $group, ?int $schoolId = null): array
    {
        return static::where('group', $group)
            ->where('school_id', $schoolId)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    // -----------------------------------------------------------------------
    // Internal helpers
    // -----------------------------------------------------------------------

    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }
}
