<?php

namespace App\Http\Controllers;

use App\Models\TeacherContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeacherContractController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:view teachers']);
    }

    public function store(Request $request, int $teacherId)
    {
        $this->authorize('create teachers');

        $data = $request->validate([
            'contract_type' => 'required|in:' . implode(',', array_keys(TeacherContract::TYPES)),
            'position'      => 'nullable|string|max:255',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after:start_date',
            'basic_salary'  => 'required|numeric|min:0',
            'terms'         => 'nullable|string|max:2000',
            'attachment'    => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')
                ->store("teacher-contracts/{$teacherId}", 'public');
        }
        unset($data['attachment']);

        TeacherContract::create(array_merge($data, [
            'teacher_id' => $teacherId,
            'status'     => 'active',
            'created_by' => auth()->id(),
        ]));

        return back()->with('status', 'Contract created.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create teachers');
        $contract = TeacherContract::findOrFail($id);
        $contract->update($request->validate([
            'status' => 'required|in:active,expired,terminated,renewed',
        ]));
        return back()->with('status', 'Contract status updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create teachers');
        $c = TeacherContract::findOrFail($id);
        if ($c->attachment_path) Storage::disk('public')->delete($c->attachment_path);
        $c->delete();
        return back()->with('status', 'Contract deleted.');
    }
}
