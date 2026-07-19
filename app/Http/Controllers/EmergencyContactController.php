<?php

namespace App\Http\Controllers;

use App\Models\EmergencyContact;
use Illuminate\Http\Request;

class EmergencyContactController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:view students']);
    }

    public function store(Request $request, int $studentId)
    {
        $this->authorize('create students');

        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'relationship'          => 'required|string|max:100',
            'phone'                 => 'required|string|max:30',
            'phone_alt'             => 'nullable|string|max:30',
            'email'                 => 'nullable|email|max:255',
            'address'               => 'nullable|string|max:500',
            'is_primary'            => 'nullable|boolean',
            'is_authorized_pickup'  => 'nullable|boolean',
        ]);

        // Only one primary contact at a time
        if (!empty($data['is_primary'])) {
            EmergencyContact::where('student_id', $studentId)->update(['is_primary' => false]);
        }

        EmergencyContact::create(array_merge($data, [
            'student_id'            => $studentId,
            'is_primary'            => $request->boolean('is_primary'),
            'is_authorized_pickup'  => $request->boolean('is_authorized_pickup'),
        ]));

        return back()->with('status', 'Emergency contact added.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create students');
        $contact = EmergencyContact::findOrFail($id);

        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'relationship'          => 'required|string|max:100',
            'phone'                 => 'required|string|max:30',
            'phone_alt'             => 'nullable|string|max:30',
            'email'                 => 'nullable|email|max:255',
            'address'               => 'nullable|string|max:500',
            'is_primary'            => 'nullable|boolean',
            'is_authorized_pickup'  => 'nullable|boolean',
        ]);

        if (!empty($data['is_primary'])) {
            EmergencyContact::where('student_id', $contact->student_id)
                ->where('id', '!=', $id)
                ->update(['is_primary' => false]);
        }

        $contact->update(array_merge($data, [
            'is_primary'           => $request->boolean('is_primary'),
            'is_authorized_pickup' => $request->boolean('is_authorized_pickup'),
        ]));

        return back()->with('status', 'Contact updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create students');
        EmergencyContact::findOrFail($id)->delete();
        return back()->with('status', 'Contact removed.');
    }
}
