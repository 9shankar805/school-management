<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Per-staff salary component assignment.
 *
 * Stores the actual amount (or %) for each component per staff member.
 * Example:
 *   user_id=5 (Ram Sharma), component_id=1 (Basic), amount=30000
 *   user_id=5 (Ram Sharma), component_id=2 (HRA),   amount=4000
 *   user_id=5 (Ram Sharma), component_id=3 (Medical),amount=1500
 */
class StaffSalaryStructure extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $table = 'staff_salary_structures';

    protected $fillable = [
        'school_id',
        'user_id',
        'component_id',
        'amount',
        'effective_from',
        'effective_to',
        'notes',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'effective_from' => 'date',
        'effective_to'   => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function staff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function component()
    {
        return $this->belongsTo(SalaryComponent::class, 'component_id');
    }

    // ── Scope — only currently active assignments ─────────────────────────────

    public function scopeCurrent($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('effective_to')
              ->orWhere('effective_to', '>=', today());
        })->where(function ($q) {
            $q->whereNull('effective_from')
              ->orWhere('effective_from', '<=', today());
        });
    }
}
