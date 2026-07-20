<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BookCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'color'];

    // Auto-generate slug from name
    protected static function booted(): void
    {
        static::creating(function (self $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function (self $category) {
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function books()
    {
        return $this->hasMany(Book::class, 'category_id');
    }

    public function ebooks()
    {
        return $this->hasMany(Ebook::class, 'category_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getBookCountAttribute(): int
    {
        return $this->books()->count();
    }
}
