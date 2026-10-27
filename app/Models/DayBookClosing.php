<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayBookClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'opening_balance',
        'cash_inflow',
        'online_inflow',
        'total_inflow',
        'cash_outflow',
        'online_outflow',
        'total_outflow',
        'expected_cash',
        'actual_cash',
        'difference',
        'closing_balance',
        'denominations',
        'notes',
        'status',
        'closed_by',
        'closed_at',
        'unlocked_by',
        'unlocked_at',
    ];

    protected $casts = [
        'date'            => 'date',
        'opening_balance' => 'decimal:2',
        'cash_inflow'     => 'decimal:2',
        'online_inflow'   => 'decimal:2',
        'total_inflow'    => 'decimal:2',
        'cash_outflow'    => 'decimal:2',
        'online_outflow'  => 'decimal:2',
        'total_outflow'   => 'decimal:2',
        'expected_cash'   => 'decimal:2',
        'actual_cash'     => 'decimal:2',
        'difference'      => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'denominations'   => 'array',
        'closed_at'       => 'datetime',
        'unlocked_at'     => 'datetime',
    ];

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function unlockedByUser()
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }
}
