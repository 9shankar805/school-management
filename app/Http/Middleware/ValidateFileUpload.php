<?php

namespace App\Http\Middleware;

use App\Services\AntiVirusScanService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Validates all file uploads in the request against an allowlist of MIME types.
 *
 * Checks (in order):
 *   1. File size   ≤ MAX_SIZE
 *   2. Extension   not in BLOCKED_EXTENSIONS (double-extension attack guard)
 *   3. MIME type   in ALLOWED_MIMES  (finfo detection, not client-provided)
 *   4. AV scan     via AntiVirusScanService (no-op by default, pluggable)
 *
 * Returns JSON error for API requests, redirect+withErrors for web requests.
 *
 * Apply to route groups:  ->middleware('validate.upload')
 */
class ValidateFileUpload
{
    /** Allowed MIME types — finfo-detected, not client-provided Content-Type. */
    private const ALLOWED_MIMES = [
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        // Archives (bulk imports)
        'application/zip',
        'application/x-zip-compressed',
    ];

    /** Maximum file size in bytes (20 MB). */
    private const MAX_SIZE = 20 * 1024 * 1024;

    /**
     * Blocked extensions regardless of MIME type.
     * Guards against double-extension attacks (e.g. malware.php.jpg).
     */
    private const BLOCKED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'exe', 'sh', 'bash', 'bat', 'cmd', 'com', 'msi',
        'js', 'jsx', 'ts', 'vbs', 'vbe', 'wsf', 'wsh',
        'jar', 'class', 'war',
        'py', 'pyc', 'rb', 'pl', 'cgi', 'htaccess', 'htpasswd',
        'svg',  // SVG can carry XSS — block at upload, serve via safe renderer
    ];

    public function __construct(private AntiVirusScanService $avScanner) {}

    public function handle(Request $request, Closure $next): mixed
    {
        /** @var UploadedFile[] $files */
        $files = $this->flattenFiles($request->allFiles());

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            if ($error = $this->validateFile($file, $request)) {
                return $this->errorResponse($request, $error);
            }
        }

        return $next($request);
    }

    // -----------------------------------------------------------------------

    private function validateFile(UploadedFile $file, Request $request): ?string
    {
        // 1. Size check
        if ($file->getSize() > self::MAX_SIZE) {
            return 'File too large. Maximum allowed size is 20 MB.';
        }

        // 2. Extension blocklist (double-extension guard)
        $ext = strtolower($file->getClientOriginalExtension());
        if (in_array($ext, self::BLOCKED_EXTENSIONS, true)) {
            Log::warning('Blocked upload: disallowed extension', [
                'filename' => $file->getClientOriginalName(),
                'ext'      => $ext,
                'ip'       => $request->ip(),
                'user_id'  => $request->user()?->id,
            ]);
            return 'File type not allowed.';
        }

        // 3. MIME type allowlist (finfo — not client-provided)
        $mime = $file->getMimeType();
        if ($mime && ! in_array($mime, self::ALLOWED_MIMES, true)) {
            Log::warning('Blocked upload: disallowed MIME', [
                'filename' => $file->getClientOriginalName(),
                'mime'     => $mime,
                'ip'       => $request->ip(),
                'user_id'  => $request->user()?->id,
            ]);
            return "Files of type '{$mime}' are not permitted.";
        }

        // 4. AV scan hook (no-op by default; configure a real scanner in AntiVirusScanService)
        $scanResult = $this->avScanner->scan($file->getRealPath());
        if (! $scanResult->isClean()) {
            Log::error('Blocked upload: AV scan failed', [
                'filename' => $file->getClientOriginalName(),
                'threat'   => $scanResult->threat(),
                'ip'       => $request->ip(),
                'user_id'  => $request->user()?->id,
            ]);
            return 'File failed security scan and was rejected.';
        }

        return null;
    }

    /** Return a JSON error for API callers, redirect+errors for web callers. */
    private function errorResponse(Request $request, string $message): mixed
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => 'error', 'message' => $message], 422);
        }

        return back()->withErrors(['file' => $message]);
    }

    private function flattenFiles(array $files): array
    {
        $flat = [];
        array_walk_recursive($files, function ($file) use (&$flat) {
            $flat[] = $file;
        });
        return $flat;
    }
}
