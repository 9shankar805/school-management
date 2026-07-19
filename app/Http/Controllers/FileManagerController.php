<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MediaFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * GET /files
     * List files for the authenticated user's school.
     */
    public function index(Request $request): JsonResponse
    {
        $files = MediaFile::query()
            ->where('uploaded_by', auth()->id())
            ->when($request->collection, fn($q) => $q->where('collection', $request->collection))
            ->when($request->search, fn($q) => $q->where('original_name', 'like', "%{$request->search}%"))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 24));

        return response()->json(['status' => 'success', 'data' => $files]);
    }

    /**
     * POST /files/upload
     * Accepts one or more file uploads with optional model binding.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'files'        => ['required', 'array', 'max:10'],
            'files.*'      => ['required', 'file', 'max:20480'], // 20 MB max per file
            'collection'   => ['sometimes', 'string', 'max:60'],
            'model_type'   => ['sometimes', 'string'],
            'model_id'     => ['sometimes', 'integer'],
        ]);

        $collection = $request->input('collection', 'default');
        $uploaded   = [];

        foreach ($request->file('files') as $file) {
            $extension  = $file->getClientOriginalExtension();
            $fileName   = Str::uuid() . '.' . $extension;
            $directory  = 'uploads/' . $collection . '/' . now()->format('Y/m');

            $path = $file->storeAs($directory, $fileName, 'local');

            $media = MediaFile::create([
                'uploaded_by'    => auth()->id(),
                'collection'     => $collection,
                'model_type'     => $request->model_type,
                'model_id'       => $request->model_id,
                'file_name'      => $fileName,
                'original_name'  => $file->getClientOriginalName(),
                'mime_type'      => $file->getMimeType(),
                'disk'           => 'local',
                'path'           => $path,
                'size'           => $file->getSize(),
                'extension'      => $extension,
                'is_public'      => false,
            ]);

            AuditLog::record('file_uploaded', $media);
            $uploaded[] = $media;
        }

        return response()->json([
            'status'  => 'success',
            'message' => count($uploaded) . ' file(s) uploaded.',
            'data'    => $uploaded,
        ], 201);
    }

    /**
     * GET /files/{id}/serve
     * Serve a private file with auth check.
     */
    public function serve(int $id): StreamedResponse
    {
        $file = MediaFile::findOrFail($id);

        // Ownership / permission check
        if ($file->uploaded_by !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to access this file.');
        }

        abort_unless(Storage::disk($file->disk)->exists($file->path), 404);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    /**
     * DELETE /files/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $file = MediaFile::findOrFail($id);

        if ($file->uploaded_by !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        // Remove from disk
        Storage::disk($file->disk)->delete($file->path);

        AuditLog::record('file_deleted', $file);
        $file->delete(); // soft-delete

        return response()->json(['status' => 'success', 'message' => 'File deleted.']);
    }
}
