<?php

namespace App\Http\Controllers;

use App\Models\StaffTraining;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffTrainingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view staff');
    }

    public function index(Request $request)
    {
        $query = StaffTraining::with('user');

        if ($search = $request->input('search')) {
            $query->whereHas('user', fn($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%"))
                ->orWhere('title', 'like', "%{$search}%");
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $trainings = $query->latest('start_date')->paginate(25)->withQueryString();
        $staff     = User::where(function($q) {
            $q->whereDoesntHave('roles', fn($r) => $r->whereIn('name', ['student', 'parent']))
              ->where(function($sub) {
                  $sub->whereNull('role')->orWhereNotIn('role', ['student', 'parent']);
              });
        })->orderBy('first_name')->get();

        return view('staff.training.index', compact('trainings', 'staff'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'        => 'required|integer|exists:users,id',
            'title'          => 'required|string|max:200',
            'type'           => 'required|string|max:100',
            'provider'       => 'nullable|string|max:200',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'duration_hours' => 'nullable|integer|min:1',
            'is_completed'   => 'boolean',
            'notes'          => 'nullable|string|max:1000',
            'certificate'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('certificate')) {
            $data['certificate_path'] = $request->file('certificate')->store('staff/training', 'public');
        }
        $data['is_completed'] = $request->boolean('is_completed');

        StaffTraining::create($data);
        return back()->with('status', 'Training record added.');
    }

    public function update(Request $request, int $id)
    {
        $training = StaffTraining::findOrFail($id);
        $data     = $request->validate([
            'title'          => 'required|string|max:200',
            'type'           => 'required|string|max:100',
            'provider'       => 'nullable|string|max:200',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'duration_hours' => 'nullable|integer|min:1',
            'is_completed'   => 'boolean',
            'notes'          => 'nullable|string|max:1000',
        ]);
        $data['is_completed'] = $request->boolean('is_completed');
        $training->update($data);
        return back()->with('status', 'Training record updated.');
    }

    public function destroy(int $id)
    {
        $training = StaffTraining::findOrFail($id);
        if ($training->certificate_path) Storage::disk('public')->delete($training->certificate_path);
        $training->delete();
        return back()->with('status', 'Training record deleted.');
    }
}
