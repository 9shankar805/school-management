<?php

namespace App\Http\Controllers;

use App\Models\TeacherDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeacherDocumentController extends Controller
{
    public function __construct() { $this->middleware(['auth', 'can:view teachers']); }

    public function store(Request $request, int $teacherId)
    {
        $this->authorize('create teachers');
        $request->validate([
            'document_type' => 'required|in:' . implode(',', array_keys(TeacherDocument::TYPES)),
            'title'         => 'required|string|max:255',
            'file'          => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx',
        ]);
        $file = $request->file('file');
        TeacherDocument::create([
            'teacher_id'    => $teacherId,
            'document_type' => $request->document_type,
            'title'         => $request->title,
            'file_path'     => $file->store("teacher-documents/{$teacherId}", 'public'),
            'file_name'     => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'uploaded_by'   => auth()->id(),
        ]);
        return back()->with('status', 'Document uploaded.');
    }

    public function verify(int $id)
    {
        $this->authorize('create teachers');
        $doc = TeacherDocument::findOrFail($id);
        $doc->update(['is_verified' => !$doc->is_verified]);
        return back()->with('status', $doc->is_verified ? 'Document verified.' : 'Verification removed.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create teachers');
        $doc = TeacherDocument::findOrFail($id);
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();
        return back()->with('status', 'Document deleted.');
    }
}
