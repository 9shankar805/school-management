<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'uploaded_by', 'model_type', 'model_id', 'collection',
        'file_name', 'original_name', 'mime_type', 'disk', 'path',
        'size', 'extension', 'is_public', 'custom_properties',
    ];

    protected $casts = [
        'is_public'         => 'boolean',
        'custom_properties' => 'array',
        'size'              => 'integer',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    // -----------------------------------------------------------------------
    // Accessors
    // -----------------------------------------------------------------------

    public function getUrlAttribute(): string
    {
        if ($this->is_public) {
            return Storage::disk($this->disk)->url($this->path);
        }

        return route('file.serve', ['id' => $this->id]);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeCollection($query, string $collection)
    {
        return $query->where('collection', $collection);
    }

    public function scopePublicFiles($query)
    {
        return $query->where('is_public', true);
    }
}
