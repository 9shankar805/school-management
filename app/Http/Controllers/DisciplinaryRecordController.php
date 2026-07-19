<?php

namespace App\Http\Controllers;

use App\Models\DisciplinaryRecord;
use Illuminate\Http\Request;

class DisciplinaryRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:view students']);
    }

    public function store(Request $request, int $studentId)
    {
        $this->authorize('create students');

        $data = $request->validate([
            'incident_date' => 'required|date',
            'severity'      => 'required|in:minor,moderate,major',
            'incident_type' => 'required|string|max:255',
            'description'   => 'required|string|max:3000',
            'action_taken'  => 'nullable|string|max:2000',
            'parent_notified' => 'nullable|string|max:1000',
        ]);

        DisciplinaryRecord::create(array_merge($data, [
            'student_id'  => $studentId,
            'reported_by' => auth()->id(),
            'resolved'    => false,
        ]));

        return back()->with('status', 'Disciplinary record added.');
    }

    public function resolve(int $id)
    {
        $this->authorize('create students');
        $record = DisciplinaryRecord::findOrFail($id);
        $record->update(['resolved' => !$record->resolved]);
        return back()->with('status', $record->resolved ? 'Marked as resolved.' : 'Marked as unresolved.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create students');
        DisciplinaryRecord::findOrFail($id)->delete();
        return back()->with('status', 'Record deleted.');
    }
}
