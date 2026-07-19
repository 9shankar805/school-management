<?php

namespace App\Http\Controllers;

use App\Models\HouseAssignment;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;

class HouseAssignmentController extends Controller
{
    use SchoolSession;

    public function __construct()
    {
        $this->middleware(['can:view students']);
    }

    public function store(Request $request, int $studentId)
    {
        $this->authorize('create students');

        $sessionId = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'house_name'    => 'required|string|max:100',
            'house_color'   => 'nullable|string|max:50',
            'captain_name'  => 'nullable|string|max:255',
            'notes'         => 'nullable|string|max:1000',
        ]);

        HouseAssignment::updateOrCreate(
            ['student_id' => $studentId, 'session_id' => $sessionId],
            $data
        );

        return back()->with('status', 'House assignment saved.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create students');
        HouseAssignment::findOrFail($id)->delete();
        return back()->with('status', 'House assignment removed.');
    }
}
