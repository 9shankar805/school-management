<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visitor extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'phone',
        'email',
        'id_type',
        'id_number',
        'organization',
        'purpose',
        'person_to_meet',
        'badge_number',
        'check_in_at',
        'check_out_at',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'check_in_at'  => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCheckedOut($query)
    {
        return $query->where('status', 'checked_out');
    }

    public static function generateBadgeNumber(): string
    {
        $prefix = 'VIS-' . today()->format('ymd') . '-';
        $latest = static::withoutGlobalScopes()
            ->withTrashed()
            ->where('badge_number', 'like', $prefix . '%')
            ->orderByDesc('badge_number')
            ->first();

        $next = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest->badge_number, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        do {
            $candidate = $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()->withTrashed()->where('badge_number', $candidate)->exists();
            if ($exists) {
                $next++;
            }
        } while ($exists);

        return $candidate;
    }
}
