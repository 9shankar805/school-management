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

        $request->validate([
            'document_type' => 'required|in:' . implode(',', array_keys(StudentDocument::TYPES)),
            'title'         => 'required|string|max:255',
            'file'          => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $file     = $request->file('file');
        $path     = $file->store("student-documents/{$studentId}", 'public');

        StudentDocument::create([
            'student_id'    => $studentId,
            'document_type' => $request->document_type,
            'title'         => $request->title,
            'file_path'     => $path,
            'file_name'     => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'notes'         => $request->notes,
            'uploaded_by'   => auth()->id(),
            'is_verified'   => false,
        ]);

        return back()->with('status', 'Document uploaded successfully.');
    }

    public function verify(int $id)
    {
        $this->authorize('create students');
        $doc = StudentDocument::findOrFail($id);
        $doc->update(['is_verified' => !$doc->is_verified]);

        return back()->with('status', $doc->is_verified ? 'Document verified.' : 'Verification removed.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create students');
        $doc = StudentDocument::findOrFail($id);
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        return back()->with('status', 'Document deleted.');
    }
}
