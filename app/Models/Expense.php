<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'title', 'category', 'amount', 'expense_date',
        'payment_method', 'reference', 'recorded_by', 'notes', 'attachment',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('expense_date', $year)->whereMonth('expense_date', $month);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('category', 'like', "%{$term}%")
              ->orWhere('reference', 'like', "%{$term}%");
        });
    }
}
