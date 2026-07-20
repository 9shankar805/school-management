<?php
namespace App\Http\Controllers;
use App\Models\Hostel;
use App\Models\User;
use Illuminate\Http\Request;

class HostelController extends Controller
{
    public function __construct() { $this->middleware(['auth', 'can:view hostel']); }
    public function index() { 
        $hostels = Hostel::with('warden')->get();
        $wardens = User::role('Hostel Warden')->get();
        return view('hostels.hostels.index', compact('hostels', 'wardens'));
    }
    public function store(Request $request) {
        $this->authorize('create hostel');
        Hostel::create($request->validate([
            'name' => 'required|string', 'type' => 'required|in:Boys,Girls,Mixed',
            'address' => 'nullable|string', 'intake_capacity' => 'required|integer',
            'warden_id' => 'nullable|exists:users,id', 'description' => 'nullable|string'
        ]));
        return back()->with('status', 'Hostel created.');
    }
    public function update(Request $request, $id) {
        $this->authorize('edit hostel');
        Hostel::findOrFail($id)->update($request->validate([
            'name' => 'required|string', 'type' => 'required|in:Boys,Girls,Mixed',
            'address' => 'nullable|string', 'intake_capacity' => 'required|integer',
            'warden_id' => 'nullable|exists:users,id', 'description' => 'nullable|string'
        ]));
        return back()->with('status', 'Hostel updated.');
    }
    public function destroy($id) {
        $this->authorize('delete hostel');
        Hostel::findOrFail($id)->delete();
        return back()->with('status', 'Hostel deleted.');
    }
}
