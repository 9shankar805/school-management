<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'author',
        'publisher',
        'edition',
        'publication_year',
        'language',
        'isbn',
        'barcode',
        'qty',
        'available_qty',
        'price',
        'shelf_location',
        'description',
        'cover_image',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'qty'          => 'integer',
        'available_qty'=> 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    public function issues()
    {
        return $this->hasMany(BookIssue::class, 'book_id');
    }

    public function activeIssues()
    {
        return $this->hasMany(BookIssue::class, 'book_id')
                    ->whereIn('status', ['issued', 'overdue']);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getIsAvailableAttribute(): bool
    {
        return $this->available_qty > 0;
    }

    public function getAvailabilityBadgeAttribute(): string
    {
        if ($this->available_qty <= 0) {
            return '<span class="badge bg-danger">Out of Stock</span>';
        }
        if ($this->available_qty <= 2) {
            return '<span class="badge bg-warning text-dark">Low Stock (' . $this->available_qty . ')</span>';
        }
        return '<span class="badge bg-success">Available (' . $this->available_qty . ')</span>';
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('available_qty', '>', 0);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('author', 'like', "%{$term}%")
              ->orWhere('isbn', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%")
              ->orWhere('publisher', 'like', "%{$term}%");
        });
    }
}
