<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number','supplier_id','raised_by','approved_by',
        'order_date','expected_date','received_date',
        'total_amount','status','notes',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
        'total_amount'  => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $po) {
            if (empty($po->po_number)) {
                $po->po_number = 'PO-' . strtoupper(Str::random(8));
            }
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function raisedBy()
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'      => '<span class="badge bg-secondary">Draft</span>',
            'pending'    => '<span class="badge bg-warning text-dark">Pending Approval</span>',
            'approved'   => '<span class="badge bg-info text-dark">Approved</span>',
            'ordered'    => '<span class="badge bg-primary">Ordered</span>',
            'partial'    => '<span class="badge bg-warning text-dark">Partially Received</span>',
            'received'   => '<span class="badge bg-success">Received</span>',
            'cancelled'  => '<span class="badge bg-danger">Cancelled</span>',
            default      => '<span class="badge bg-light text-dark">' . ucfirst($this->status) . '</span>',
        };
    }

    /** Recalculate total from line items */
    public function recalculateTotal(): void
    {
        $this->update(['total_amount' => $this->items()->sum('total_price')]);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('po_number', 'like', "%{$term}%")
              ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$term}%"));
        });
    }
}
