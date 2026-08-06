<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class FeeInstallment extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'invoice_id', 'installment_number', 'amount', 'due_date', 'paid_date', 'status',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'due_date' => 'date',
        'paid_date'=> 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'paid'    => '<span class="badge bg-success">Paid</span>',
            'pending' => $this->is_overdue
                ? '<span class="badge bg-danger">Overdue</span>'
                : '<span class="badge bg-warning text-dark">Pending</span>',
            'overdue' => '<span class="badge bg-danger">Overdue</span>',
            default   => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }
}
