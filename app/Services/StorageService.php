<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * StorageService — Module 23
 *
 * Single entry-point for all file storage operations.
 * Automatically routes to the correct disk based on the
 * file category and the FILESYSTEM_DRIVER env variable.
 *
 * Disk routing:
 *   - private  → 's3-private'  (prod) | 'local'  (dev)
 *   - public   → 's3-public'   (prod) | 'public' (dev)
 *   - backup   → 'backups'     (prod) | 'local'  (dev)
 *   - general  → default disk from config
 *
 * Usage:
 *   $path = app(StorageService::class)->store($file, 'student-docs', 'private');
 *   $url  = app(StorageService::class)->url($path, 'private');
 *   app(StorageService::class)->delete($path, 'private');
 */
class StorageService
{
    /**
     * Store an uploaded file.
     *
     * @param  UploadedFile  $file
     * @param  string        $directory  Sub-directory within the disk (e.g. 'student-docs')
     * @param  string        $category   'private' | 'public' | 'backup' | 'general'
     * @param  string|null   $filename   Optional custom filename (without extension)
     * @return string  The stored path relative to the disk root
     */
    public function store(
        UploadedFile $file,
        string $directory = 'uploads',
        string $category = 'private',
        ?string $filename = null
    ): string {
        $disk = $this->resolveDisk($category);

        if (\App\Services\ImageWebpService::isImage($file)) {
            return \App\Services\ImageWebpService::storeAsWebp($file, $directory, $disk, $filename);
        }

        $name = $filename
            ? Str::slug($filename) . '.' . $file->getClientOriginalExtension()
            : $file->hashName();

        return $file->storeAs($directory, $name, ['disk' => $disk]);
    }

    /**
     * Store raw content (string or stream) to a disk path.
     */
    public function put(string $path, mixed $content, string $category = 'private'): bool
    {
        return Storage::disk($this->resolveDisk($category))->put($path, $content);
    }

    /**
     * Return a URL for a stored file.
     * Private files get a temporary signed URL (expiry from config).
     * Public files get a permanent URL.
     */
    public function url(string $path, string $category = 'private'): string
    {
        $disk = $this->resolveDisk($category);

        if ($category === 'public') {
            return Storage::disk($disk)->url($path);
        }

        // For local disk, use a signed route instead of a real S3 signed URL
        if ($this->isLocal()) {
            return url(Storage::disk($disk)->url($path));
        }

        $expiry = now()->addSeconds(
            (int) config('filesystems.disks.' . $disk . '.temporary_url_expiry', 900)
        );

        return Storage::disk($disk)->temporaryUrl($path, $expiry);
    }

    /**
     * Delete a stored file.
     */
    public function delete(string $path, string $category = 'private'): bool
    {
        return Storage::disk($this->resolveDisk($category))->delete($path);
    }

    /**
     * Check if a file exists.
     */
    public function exists(string $path, string $category = 'private'): bool
    {
        return Storage::disk($this->resolveDisk($category))->exists($path);
    }

    /**
     * Get file size in bytes.
     */
    public function size(string $path, string $category = 'private'): int
    {
        return Storage::disk($this->resolveDisk($category))->size($path);
    }

    /**
     * Move a file from one category/path to another.
     * Useful when a document is published (private → public).
     */
    public function move(
        string $fromPath,
        string $fromCategory,
        string $toPath,
        string $toCategory
    ): bool {
        $content = Storage::disk($this->resolveDisk($fromCategory))->get($fromPath);

        if ($content === null) {
            return false;
        }

        $stored = $this->put($toPath, $content, $toCategory);

        if ($stored) {
            $this->delete($fromPath, $fromCategory);
        }

        return $stored;
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve the actual disk name from a category + environment.
     */
    protected function resolveDisk(string $category): string
    {
        // In local/testing environments always use local disks
        if ($this->isLocal()) {
            return match ($category) {
                'public' => 'public',
                default  => 'local',
            };
        }

        return match ($category) {
            'private' => 's3-private',
            'public'  => 's3-public',
            'backup'  => 'backups',
            default   => config('filesystems.default', 's3'),
        };
    }

    /**
     * True when running on a non-cloud local disk (dev / test / CI).
     */
    protected function isLocal(): bool
    {
        $driver = config('filesystems.default', 'local');

        return in_array($driver, ['local', 'public', 'minio'], true)
            || app()->environment('testing', 'local');
    }
}
