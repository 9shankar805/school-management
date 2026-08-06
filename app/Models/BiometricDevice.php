<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class BiometricDevice extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name', 'serial_number', 'location', 'type',
        'ip_address', 'api_key', 'is_active', 'last_seen_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function scans() { return $this->hasMany(RfidScan::class, 'device_id'); }

    /** Verify a device API key — constant-time comparison */
    public static function findByApiKey(string $key): ?self
    {
        return self::where('is_active', true)
            ->get()
            ->first(fn($d) => hash_equals($d->api_key, $key));
    }
}
