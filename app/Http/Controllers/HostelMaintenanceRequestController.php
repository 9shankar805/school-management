<?php
namespace App\Http\Controllers;
use App\Models\HostelMaintenanceRequest;
use App\Models\Hostel;
use App\Models\HostelRoom;
use Illuminate\Http\Request;

class HostelMaintenanceRequestController extends Controller
{
    public function __construct() { $this->middleware(['auth', 'can:manage hostel maintenance']); }
    public function index() {
        $requests = HostelMaintenanceRequest::with('hostel', 'room', 'reporter')->get();
        $hostels = Hostel::with('rooms')->get();
        return view('hostels.maintenance.index', compact('requests', 'hostels'));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'hostel_id' => 'required', 'hostel_room_id' => 'required', 'issue_type' => 'required|string',
            'description' => 'required|string', 'priority' => 'required|in:Low,Medium,High'
        ]);
        $data['reported_by_id'] = auth()->id();
        $data['status'] = 'Pending';
        HostelMaintenanceRequest::create($data);
        return back()->with('status', 'Maintenance request created.');
    }
    public function update(Request $request, $id) {
        HostelMaintenanceRequest::findOrFail($id)->update($request->validate([
            'status' => 'required|in:Pending,In Progress,Resolved'
        ]));
        return back()->with('status', 'Request status updated.');
    }
    public function destroy($id) {
        HostelMaintenanceRequest::findOrFail($id)->delete();
        return back()->with('status', 'Request deleted.');
    }
}
