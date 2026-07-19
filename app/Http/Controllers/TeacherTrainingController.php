<?php

namespace App\Http\Controllers;

use App\Models\TeacherTraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeacherTrainingController extends Controller
{
    public function __construct() { $this->middleware(['auth', 'can:view teachers']); }

    public function store(Request $request, int $teacherId)
    {
        $this->authorize('create teachers');
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'organizer'      => 'nullable|string|max:255',
            'type'           => 'required|in:' . implode(',', array_keys(TeacherTraining::TYPES)),
            'from_date'      => 'required|date',
            'to_date'        => 'nullable|date|after_or_equal:from_date',
            'hours'          => 'nullable|integer|min:1',
            'certificate_no' => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:1000',
            'attachment'     => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')
                ->store("teacher-training/{$teacherId}", 'public');
        }
        unset($data['attachment']);
        TeacherTraining::create(array_merge($data, ['teacher_id' => $teacherId]));
        return back()->with('status', 'Training record added.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create teachers');
        $tr = TeacherTraining::findOrFail($id);
        if ($tr->attachment_path) Storage::disk('public')->delete($tr->attachment_path);
        $tr->delete();
        return back()->with('status', 'Training record deleted.');
    }
}
