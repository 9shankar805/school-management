<?php
namespace App\Http\Controllers;
use App\Models\HostelBed;
use Illuminate\Http\Request;

class HostelBedController extends Controller
{
    public function __construct() { $this->middleware(['auth', 'can:manage hostel rooms']); }
    public function index() {
        $beds = HostelBed::with('room.hostel')->get();
        return view('hostels.beds.index', compact('beds'));
    }
    public function store(Request $request) {
        HostelBed::create($request->validate([
            'hostel_room_id' => 'required|exists:hostel_rooms,id', 'name' => 'required|string',
            'status' => 'required|in:Available,Occupied,Maintenance'
        ]));
        return back()->with('status', 'Bed created.');
    }
    public function update(Request $request, $id) {
        HostelBed::findOrFail($id)->update($request->validate([
            'name' => 'required|string', 'status' => 'required|in:Available,Occupied,Maintenance'
        ]));
        return back()->with('status', 'Bed updated.');
    }
    public function destroy($id) {
        HostelBed::findOrFail($id)->delete();
        return back()->with('status', 'Bed deleted.');
    }
}
