<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

/**
 * Accounting ledger entry — double-entry bookkeeping row.
 */
class LedgerEntry extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'entry_date',
        'type',          // income | expense | fee_payment | payroll | hostel | transport | other
        'account',       // account head string (e.g. "Tuition Fees", "Salaries")
        'debit',
        'credit',
        'balance',       // running balance (computed on insert)
        'reference_type', // morph type (Payment, Expense, SalarySlip, etc.)
        'reference_id',
        'description',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'debit'      => 'decimal:2',
        'credit'     => 'decimal:2',
        'balance'    => 'decimal:2',
    ];

    public function reference()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
