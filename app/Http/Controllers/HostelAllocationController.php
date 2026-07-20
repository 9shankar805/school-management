<?php
namespace App\Http\Controllers;
use App\Models\HostelAllocation;
use App\Models\Hostel;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Http\Request;

class HostelAllocationController extends Controller
{
    public function __construct() { $this->middleware(['auth', 'can:manage hostel allocations']); }
    public function index() {
        $allocations = HostelAllocation::with(['student', 'hostel', 'room', 'bed'])->get();
        $students = User::role('student')->get();
        $hostels = Hostel::with('rooms.beds')->get();
        return view('hostels.allocations.index', compact('allocations', 'students', 'hostels'));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'student_id' => 'required|exists:users,id', 'hostel_id' => 'required|exists:hostels,id',
            'hostel_room_id' => 'required|exists:hostel_rooms,id', 'hostel_bed_id' => 'required|exists:hostel_beds,id',
            'start_date' => 'required|date', 'end_date' => 'nullable|date', 'status' => 'required|in:Active,Inactive'
        ]);
        $allocation = HostelAllocation::create($data);
        
        // Integration with Finance: create an invoice
        $room = $allocation->room;
        if($room->cost_per_bed > 0) {
            Invoice::create([
                'student_id' => $allocation->student_id,
                'title' => 'Hostel Fee - ' . $room->hostel->name,
                'amount' => $room->cost_per_bed,
                'status' => 'unpaid',
                'due_date' => \Carbon\Carbon::parse($allocation->start_date)->addDays(7),
                'description' => 'Hostel fee for room ' . $room->room_number . ' bed ' . $allocation->bed->name
            ]);
        }
        // Mark bed as occupied
        $allocation->bed->update(['status' => 'Occupied']);

        return back()->with('status', 'Allocation created and Invoice generated.');
    }
    public function update(Request $request, $id) {
        HostelAllocation::findOrFail($id)->update($request->validate([
            'start_date' => 'required|date', 'end_date' => 'nullable|date', 'status' => 'required|in:Active,Inactive'
        ]));
        return back()->with('status', 'Allocation updated.');
    }
    public function destroy($id) {
        $allocation = HostelAllocation::findOrFail($id);
        $allocation->bed->update(['status' => 'Available']);
        $allocation->delete();
        return back()->with('status', 'Allocation deleted.');
    }
}
