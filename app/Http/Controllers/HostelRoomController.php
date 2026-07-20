<?php
namespace App\Http\Controllers;
use App\Models\HostelRoom;
use App\Models\Hostel;
use Illuminate\Http\Request;

class HostelRoomController extends Controller
{
    public function __construct() { $this->middleware(['auth', 'can:manage hostel rooms']); }
    public function index() {
        $rooms = HostelRoom::with('hostel')->get();
        $hostels = Hostel::all();
        return view('hostels.rooms.index', compact('rooms', 'hostels'));
    }
    public function store(Request $request) {
        HostelRoom::create($request->validate([
            'hostel_id' => 'required|exists:hostels,id', 'room_number' => 'required|string',
            'room_type' => 'required|in:AC,Non-AC', 'capacity' => 'required|integer',
            'cost_per_bed' => 'required|numeric', 'description' => 'nullable|string'
        ]));
        return back()->with('status', 'Room created.');
    }
    public function update(Request $request, $id) {
        HostelRoom::findOrFail($id)->update($request->validate([
            'hostel_id' => 'required|exists:hostels,id', 'room_number' => 'required|string',
            'room_type' => 'required|in:AC,Non-AC', 'capacity' => 'required|integer',
            'cost_per_bed' => 'required|numeric', 'description' => 'nullable|string'
        ]));
        return back()->with('status', 'Room updated.');
    }
    public function destroy($id) {
        HostelRoom::findOrFail($id)->delete();
        return back()->with('status', 'Room deleted.');
    }
}
