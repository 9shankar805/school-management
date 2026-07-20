<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Ebook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'category_id',
        'isbn',
        'description',
        'file_path',
        'cover_image',
        'file_type',
        'file_size',
        'pages',
        'publication_year',
        'publisher',
        'access_level',
        'is_active',
        'download_count',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'download_count' => 'integer',
        'file_size'      => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /** Human-readable file size (e.g. "2.4 MB") */
    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $size  = $this->file_size;
        $i     = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 1) . ' ' . $units[$i];
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image
            ? Storage::url($this->cover_image)
            : null;
    }

    public function getFileIconAttribute(): string
    {
        return match ($this->file_type) {
            'pdf'  => 'bi-file-earmark-pdf text-danger',
            'epub' => 'bi-book text-success',
            'mobi' => 'bi-book-half text-info',
            default => 'bi-file-earmark text-secondary',
        };
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }

    /** Scopes */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('access_level', 'public');
    }

    public function scopeForMembers($query)
    {
        return $query->whereIn('access_level', ['public', 'members_only']);
    }
}
