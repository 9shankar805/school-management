<?php

namespace App\Http\Controllers;

use App\Models\TeacherQualification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeacherQualificationController extends Controller
{
    public function __construct() { $this->middleware(['auth', 'can:view teachers']); }

    public function store(Request $request, int $teacherId)
    {
        $this->authorize('create teachers');
        $data = $request->validate([
            'type'          => 'required|in:' . implode(',', array_keys(TeacherQualification::TYPES)),
            'title'         => 'required|string|max:255',
            'institution'   => 'required|string|max:255',
            'field_of_study'=> 'nullable|string|max:255',
            'start_year'    => 'nullable|integer|min:1950|max:' . now()->year,
            'end_year'      => 'nullable|integer|min:1950|max:' . now()->year + 5,
            'grade'         => 'nullable|string|max:50',
            'attachment'    => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);
        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store("teacher-qualifications/{$teacherId}", 'public');
        }
        unset($data['attachment']);
        TeacherQualification::create(array_merge($data, ['teacher_id' => $teacherId]));
        return back()->with('status', 'Qualification added.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create teachers');
        $q = TeacherQualification::findOrFail($id);
        if ($q->attachment_path) Storage::disk('public')->delete($q->attachment_path);
        $q->delete();
        return back()->with('status', 'Qualification removed.');
    }
}
