<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class StaffExitRecord extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'user_id', 'exit_type', 'notice_date', 'last_working_date',
        'reason', 'notes', 'clearance_done', 'dues_settled', 'processed_by',
    ];

    protected $casts = [
        'notice_date'       => 'date',
        'last_working_date' => 'date',
        'clearance_done'    => 'boolean',
        'dues_settled'      => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getExitTypeLabelAttribute(): string
    {
        return match ($this->exit_type) {
            'resignation'   => 'Resignation',
            'termination'   => 'Termination',
            'retirement'    => 'Retirement',
            'contract_end'  => 'Contract End',
            default         => 'Other',
        };
    }
}
