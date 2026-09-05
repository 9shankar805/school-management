<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\StudentDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * GET /api/documents/child/{childId}
     */
    public function index(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $documents = StudentDocument::where('student_id', $childId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($doc) {
                return collect($doc)->merge([
                    'url'        => $doc->url,
                    'human_size' => $doc->human_size,
                    'is_image'   => $doc->isImage(),
                    'is_pdf'     => $doc->isPdf(),
                ]);
            });

        return response()->json([
            'status' => 'success',
            'data'   => $documents,
        ]);
    }

    /**
     * GET /api/documents/child/{childId}
     */
    public function byChild(Request $request, $childId): JsonResponse
    {
        return $this->index($request, $childId);
    }

    /**
     * GET /api/documents/type/{type}
     */
    public function byType(Request $request, $type): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');

        $documents = StudentDocument::whereIn('student_id', $childIds)
            ->where('document_type', $type)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($doc) {
                return collect($doc)->merge([
                    'url'        => $doc->url,
                    'human_size' => $doc->human_size,
                    'is_image'   => $doc->isImage(),
                    'is_pdf'     => $doc->isPdf(),
                ]);
            });

        return response()->json([
            'status' => 'success',
            'data'   => $documents,
        ]);
    }

    /**
     * POST /api/documents/child/{childId}/upload
     */
    public function upload(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $validated = $request->validate([
            'document_type' => 'required|string',
            'title'         => 'required|string|max:255',
            'file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB limit
            'notes'         => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store('student_documents/' . $childId, 'public');

        $document = StudentDocument::create([
            'school_id'     => $request->user()->school_id ?? 1,
            'student_id'    => $childId,
            'document_type' => $validated['document_type'],
            'title'         => $validated['title'],
            'file_path'     => $path,
            'file_name'     => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'notes'         => $validated['notes'],
            'uploaded_by'   => $request->user()->id,
            'is_verified'   => false,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Document uploaded successfully.',
            'data'    => collect($document)->merge(['url' => $document->url]),
        ], 201);
    }

    /**
     * GET /api/documents/{id}/download
     */
    public function download(Request $request, $id)
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $document = StudentDocument::whereIn('student_id', $childIds)->findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json(['status' => 'error', 'message' => 'File not found on server.'], 404);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * DELETE /api/documents/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $document = StudentDocument::whereIn('student_id', $childIds)->findOrFail($id);

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Document deleted successfully.',
        ]);
    }
}
