<?php

namespace App\Http\Controllers;

use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:view students']);
    }

    public function store(Request $request, int $studentId)
    {
        $this->authorize('create students');

        $user = auth()->user();
        if ($user->school_id && !$user->hasRole('super-admin')) {
            User::where('school_id', $user->school_id)->findOrFail($studentId);
        }

        $request->validate([
            'document_type' => 'required|in:' . implode(',', array_keys(StudentDocument::TYPES)),
            'title'         => 'required|string|max:255',
            'file'          => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $file     = $request->file('file');
        $path     = $file->store("student-documents/{$studentId}", 'public');
        $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($file->getClientOriginalName()));

        StudentDocument::create([
            'school_id'     => $user->school_id,
            'student_id'    => $studentId,
            'document_type' => $request->document_type,
            'title'         => $request->title,
            'file_path'     => $path,
            'file_name'     => $safeName,
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'notes'         => $request->notes,
            'uploaded_by'   => $user->id,
            'is_verified'   => false,
        ]);

        return back()->with('status', 'Document uploaded successfully.');
    }

    public function verify(int $id)
    {
        $this->authorize('create students');

        $user = auth()->user();
        $query = StudentDocument::query();
        if ($user->school_id && !$user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        }

        $doc = $query->findOrFail($id);
        $doc->update(['is_verified' => !$doc->is_verified]);

        return back()->with('status', $doc->is_verified ? 'Document verified.' : 'Verification removed.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create students');

        $user = auth()->user();
        $query = StudentDocument::query();
        if ($user->school_id && !$user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        }

        $doc = $query->findOrFail($id);
        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();

        return back()->with('status', 'Document deleted.');
    }
}
