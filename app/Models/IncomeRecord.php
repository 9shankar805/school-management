<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeRecord extends Model
{
    use HasTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'title', 'category', 'amount', 'income_date',
        'payment_method', 'reference', 'recorded_by', 'notes', 'attachment',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'income_date' => 'date',
    ];

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('income_date', $year)->whereMonth('income_date', $month);
    }
}
