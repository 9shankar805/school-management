<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:view students']);
    }

    public function store(Request $request, int $studentId)
    {
        $this->authorize('create students');

        $types = implode(',', array_keys(Scholarship::TYPES));

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'type'         => "required|in:{$types}",
            'amount'       => 'nullable|numeric|min:0',
            'percentage'   => 'nullable|string|max:10',
            'awarded_date' => 'required|date',
            'expiry_date'  => 'nullable|date|after:awarded_date',
            'criteria'     => 'nullable|string|max:2000',
            'notes'        => 'nullable|string|max:2000',
        ]);

        Scholarship::create(array_merge($data, [
            'student_id' => $studentId,
            'status'     => 'active',
            'awarded_by' => auth()->id(),
        ]));

        return back()->with('status', 'Scholarship added.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create students');
        $scholarship = Scholarship::findOrFail($id);

        $data = $request->validate([
            'status'     => 'required|in:active,expired,revoked',
            'notes'      => 'nullable|string|max:2000',
            'expiry_date'=> 'nullable|date',
        ]);

        $scholarship->update($data);
        return back()->with('status', 'Scholarship updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create students');
        Scholarship::findOrFail($id)->delete();
        return back()->with('status', 'Scholarship removed.');
    }
}
