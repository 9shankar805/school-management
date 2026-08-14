<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageWebpService
{
    /**
     * Check if an uploaded file or path is a convertable image.
     */
    public static function isImage(UploadedFile|string $file): bool
    {
        if ($file instanceof UploadedFile) {
            $mime = strtolower($file->getMimeType());
            $ext  = strtolower($file->getClientOriginalExtension());
        } else {
            if (!file_exists($file)) return false;
            $mime = strtolower(mime_content_type($file) ?: '');
            $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        }

        $imageMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/x-ms-bmp'];
        $imageExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

        return in_array($mime, $imageMimes, true) || in_array($ext, $imageExts, true);
    }

    /**
     * Convert any image file to optimized WebP binary string.
     *
     * @param UploadedFile|string $file
     * @param int $quality Quality from 1 to 100 (default 82)
     * @return string|null WebP binary data or null if conversion failed
     */
    public static function convertToWebpBinary(UploadedFile|string $file, int $quality = 82): ?string
    {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        if (!$filePath || !file_exists($filePath)) return null;

        $mime = $file instanceof UploadedFile ? strtolower($file->getMimeType()) : strtolower(mime_content_type($filePath) ?: '');

        $img = null;

        // Try GD string loader first (supports JPEG, PNG, GIF, BMP, WEBP)
        if (function_exists('imagecreatefromstring')) {
            $raw = @file_get_contents($filePath);
            if ($raw) {
                $img = @imagecreatefromstring($raw);
            }
        }

        if (!$img) {
            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $img = @imagecreatefromjpeg($filePath);
                    break;
                case 'image/png':
                    $img = @imagecreatefrompng($filePath);
                    break;
                case 'image/gif':
                    $img = @imagecreatefromgif($filePath);
                    break;
                case 'image/webp':
                    $img = @imagecreatefromwebp($filePath);
                    break;
                case 'image/bmp':
                case 'image/x-ms-bmp':
                    $img = @imagecreatefrombmp($filePath);
                    break;
            }
        }

        if (!$img) return null;

        // Preserve alpha transparency for PNG / WebP
        imagealphablending($img, true);
        imagesavealpha($img, true);

        ob_start();
        $success = @imagewebp($img, null, $quality);
        $webpContent = ob_get_clean();
        imagedestroy($img);

        return ($success && $webpContent) ? $webpContent : null;
    }

    /**
     * Store an uploaded file to disk. Converts images to .webp automatically.
     * Non-image files (PDF, DOCX, CSV) are stored as normal.
     *
     * @param UploadedFile $file
     * @param string $directory Subfolder inside disk (e.g. 'school-logos', 'admissions/photos')
     * @param string $disk Storage disk ('public', 'local', 's3-public', etc.)
     * @param string|null $filename Custom base filename without extension
     * @param int $quality WebP quality (default 82)
     * @return string Path relative to storage disk
     */
    public static function storeAsWebp(
        UploadedFile $file,
        string $directory = 'uploads',
        string $disk = 'public',
        ?string $filename = null,
        int $quality = 82
    ): string {
        $cleanDir = rtrim($directory, '/');

        if (self::isImage($file)) {
            $webpData = self::convertToWebpBinary($file, $quality);

            if ($webpData !== null) {
                $baseName = $filename
                    ? Str::slug(pathinfo($filename, PATHINFO_FILENAME))
                    : Str::random(40);

                $targetPath = $cleanDir . '/' . $baseName . '.webp';
                Storage::disk($disk)->put($targetPath, $webpData);
                return $targetPath;
            }
        }

        // Fallback for documents or if GD conversion is skipped: store as normal
        $name = $filename
            ? Str::slug(pathinfo($filename, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension()
            : $file->hashName();

        return $file->storeAs($cleanDir, $name, ['disk' => $disk]);
    }
}
