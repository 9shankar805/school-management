<?php

namespace App\Http\Controllers;

use App\Models\StaffExitRecord;
use App\Models\User;
use Illuminate\Http\Request;

class StaffExitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view staff');
    }

    public function index(Request $request)
    {
        $query = StaffExitRecord::with(['user', 'processedBy']);

        if ($search = $request->input('search')) {
            $query->whereHas('user', fn($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%"));
        }
        if ($type = $request->input('exit_type')) {
            $query->where('exit_type', $type);
        }

        $exits = $query->latest('last_working_date')->paginate(25)->withQueryString();
        $staff = User::where(function($q) {
            $q->whereDoesntHave('roles', fn($r) => $r->whereIn('name', ['student', 'parent']))
              ->where(function($sub) {
                  $sub->whereNull('role')->orWhereNotIn('role', ['student', 'parent']);
              });
        })->orderBy('first_name')->get();

        return view('staff.exit.index', compact('exits', 'staff'));
    }

    public function store(Request $request)
    {
        $this->authorize('create staff');
        $data = $request->validate([
            'user_id'           => 'required|integer|exists:users,id',
            'exit_type'         => 'required|in:resignation,termination,retirement,contract_end,other',
            'notice_date'       => 'required|date',
            'last_working_date' => 'required|date|after_or_equal:notice_date',
            'reason'            => 'nullable|string|max:500',
            'notes'             => 'nullable|string|max:1000',
            'clearance_done'    => 'boolean',
            'dues_settled'      => 'boolean',
        ]);

        $data['clearance_done'] = $request->boolean('clearance_done');
        $data['dues_settled']   = $request->boolean('dues_settled');
        $data['processed_by']   = auth()->id();

        StaffExitRecord::create($data);
        return back()->with('status', 'Exit record created.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create staff');
        $exit = StaffExitRecord::findOrFail($id);
        $exit->update([
            'clearance_done' => $request->boolean('clearance_done'),
            'dues_settled'   => $request->boolean('dues_settled'),
            'notes'          => $request->input('notes'),
        ]);
        return back()->with('status', 'Exit record updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create staff');
        StaffExitRecord::findOrFail($id)->delete();
        return back()->with('status', 'Exit record deleted.');
    }
}
