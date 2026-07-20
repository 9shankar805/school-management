<?php

$controllersDir = __DIR__ . '/app/Http/Controllers';
$viewsDir = __DIR__ . '/resources/views/hostels';

if (!is_dir($viewsDir)) mkdir($viewsDir, 0755, true);

// 1. HostelController
file_put_contents("$controllersDir/HostelController.php", "<?php
namespace App\Http\Controllers;
use App\Models\Hostel;
use App\Models\User;
use Illuminate\Http\Request;

class HostelController extends Controller
{
    public function __construct() { \$this->middleware(['auth', 'can:view hostel']); }
    public function index() { 
        \$hostels = Hostel::with('warden')->get();
        \$wardens = User::role('Hostel Warden')->get();
        return view('hostels.hostels.index', compact('hostels', 'wardens'));
    }
    public function store(Request \$request) {
        \$this->authorize('create hostel');
        Hostel::create(\$request->validate([
            'name' => 'required|string', 'type' => 'required|in:Boys,Girls,Mixed',
            'address' => 'nullable|string', 'intake_capacity' => 'required|integer',
            'warden_id' => 'nullable|exists:users,id', 'description' => 'nullable|string'
        ]));
        return back()->with('status', 'Hostel created.');
    }
    public function update(Request \$request, \$id) {
        \$this->authorize('edit hostel');
        Hostel::findOrFail(\$id)->update(\$request->validate([
            'name' => 'required|string', 'type' => 'required|in:Boys,Girls,Mixed',
            'address' => 'nullable|string', 'intake_capacity' => 'required|integer',
            'warden_id' => 'nullable|exists:users,id', 'description' => 'nullable|string'
        ]));
        return back()->with('status', 'Hostel updated.');
    }
    public function destroy(\$id) {
        \$this->authorize('delete hostel');
        Hostel::findOrFail(\$id)->delete();
        return back()->with('status', 'Hostel deleted.');
    }
}
");

// 2. HostelRoomController
file_put_contents("$controllersDir/HostelRoomController.php", "<?php
namespace App\Http\Controllers;
use App\Models\HostelRoom;
use App\Models\Hostel;
use Illuminate\Http\Request;

class HostelRoomController extends Controller
{
    public function __construct() { \$this->middleware(['auth', 'can:manage hostel rooms']); }
    public function index() {
        \$rooms = HostelRoom::with('hostel')->get();
        \$hostels = Hostel::all();
        return view('hostels.rooms.index', compact('rooms', 'hostels'));
    }
    public function store(Request \$request) {
        HostelRoom::create(\$request->validate([
            'hostel_id' => 'required|exists:hostels,id', 'room_number' => 'required|string',
            'room_type' => 'required|in:AC,Non-AC', 'capacity' => 'required|integer',
            'cost_per_bed' => 'required|numeric', 'description' => 'nullable|string'
        ]));
        return back()->with('status', 'Room created.');
    }
    public function update(Request \$request, \$id) {
        HostelRoom::findOrFail(\$id)->update(\$request->validate([
            'hostel_id' => 'required|exists:hostels,id', 'room_number' => 'required|string',
            'room_type' => 'required|in:AC,Non-AC', 'capacity' => 'required|integer',
            'cost_per_bed' => 'required|numeric', 'description' => 'nullable|string'
        ]));
        return back()->with('status', 'Room updated.');
    }
    public function destroy(\$id) {
        HostelRoom::findOrFail(\$id)->delete();
        return back()->with('status', 'Room deleted.');
    }
}
");

// 3. HostelBedController
file_put_contents("$controllersDir/HostelBedController.php", "<?php
namespace App\Http\Controllers;
use App\Models\HostelBed;
use Illuminate\Http\Request;

class HostelBedController extends Controller
{
    public function __construct() { \$this->middleware(['auth', 'can:manage hostel rooms']); }
    public function index() {
        \$beds = HostelBed::with('room.hostel')->get();
        return view('hostels.beds.index', compact('beds'));
    }
    public function store(Request \$request) {
        HostelBed::create(\$request->validate([
            'hostel_room_id' => 'required|exists:hostel_rooms,id', 'name' => 'required|string',
            'status' => 'required|in:Available,Occupied,Maintenance'
        ]));
        return back()->with('status', 'Bed created.');
    }
    public function update(Request \$request, \$id) {
        HostelBed::findOrFail(\$id)->update(\$request->validate([
            'name' => 'required|string', 'status' => 'required|in:Available,Occupied,Maintenance'
        ]));
        return back()->with('status', 'Bed updated.');
    }
    public function destroy(\$id) {
        HostelBed::findOrFail(\$id)->delete();
        return back()->with('status', 'Bed deleted.');
    }
}
");

// 4. HostelAllocationController
file_put_contents("$controllersDir/HostelAllocationController.php", "<?php
namespace App\Http\Controllers;
use App\Models\HostelAllocation;
use App\Models\Hostel;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Http\Request;

class HostelAllocationController extends Controller
{
    public function __construct() { \$this->middleware(['auth', 'can:manage hostel allocations']); }
    public function index() {
        \$allocations = HostelAllocation::with(['student', 'hostel', 'room', 'bed'])->get();
        \$students = User::role('student')->get();
        \$hostels = Hostel::with('rooms.beds')->get();
        return view('hostels.allocations.index', compact('allocations', 'students', 'hostels'));
    }
    public function store(Request \$request) {
        \$data = \$request->validate([
            'student_id' => 'required|exists:users,id', 'hostel_id' => 'required|exists:hostels,id',
            'hostel_room_id' => 'required|exists:hostel_rooms,id', 'hostel_bed_id' => 'required|exists:hostel_beds,id',
            'start_date' => 'required|date', 'end_date' => 'nullable|date', 'status' => 'required|in:Active,Inactive'
        ]);
        \$allocation = HostelAllocation::create(\$data);
        
        // Integration with Finance: create an invoice
        \$room = \$allocation->room;
        if(\$room->cost_per_bed > 0) {
            Invoice::create([
                'student_id' => \$allocation->student_id,
                'title' => 'Hostel Fee - ' . \$room->hostel->name,
                'amount' => \$room->cost_per_bed,
                'status' => 'unpaid',
                'due_date' => \Carbon\Carbon::parse(\$allocation->start_date)->addDays(7),
                'description' => 'Hostel fee for room ' . \$room->room_number . ' bed ' . \$allocation->bed->name
            ]);
        }
        // Mark bed as occupied
        \$allocation->bed->update(['status' => 'Occupied']);

        return back()->with('status', 'Allocation created and Invoice generated.');
    }
    public function update(Request \$request, \$id) {
        HostelAllocation::findOrFail(\$id)->update(\$request->validate([
            'start_date' => 'required|date', 'end_date' => 'nullable|date', 'status' => 'required|in:Active,Inactive'
        ]));
        return back()->with('status', 'Allocation updated.');
    }
    public function destroy(\$id) {
        \$allocation = HostelAllocation::findOrFail(\$id);
        \$allocation->bed->update(['status' => 'Available']);
        \$allocation->delete();
        return back()->with('status', 'Allocation deleted.');
    }
}
");

// 5. HostelAttendanceController
file_put_contents("$controllersDir/HostelAttendanceController.php", "<?php
namespace App\Http\Controllers;
use App\Models\HostelAttendance;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use Illuminate\Http\Request;

class HostelAttendanceController extends Controller
{
    public function __construct() { \$this->middleware(['auth', 'can:manage hostel attendance']); }
    public function index(Request \$request) {
        \$hostels = Hostel::all();
        \$date = \$request->date ?? date('Y-m-d');
        \$hostel_id = \$request->hostel_id;
        
        \$students = [];
        if(\$hostel_id) {
            \$students = HostelAllocation::with('student')->where('hostel_id', \$hostel_id)->where('status', 'Active')->get();
        }
        
        \$attendances = HostelAttendance::where('date', \$date)->when(\$hostel_id, fn(\$q) => \$q->where('hostel_id', \$hostel_id))->get()->keyBy('student_id');
        
        return view('hostels.attendances.index', compact('hostels', 'date', 'hostel_id', 'students', 'attendances'));
    }
    public function store(Request \$request) {
        \$request->validate(['hostel_id' => 'required', 'date' => 'required|date', 'attendance' => 'required|array']);
        foreach(\$request->attendance as \$student_id => \$status) {
            HostelAttendance::updateOrCreate(
                ['hostel_id' => \$request->hostel_id, 'student_id' => \$student_id, 'date' => \$request->date],
                ['status' => \$status]
            );
        }
        return back()->with('status', 'Attendance saved.');
    }
}
");

// 6. HostelVisitorController
file_put_contents("$controllersDir/HostelVisitorController.php", "<?php
namespace App\Http\Controllers;
use App\Models\HostelVisitor;
use App\Models\Hostel;
use App\Models\User;
use Illuminate\Http\Request;

class HostelVisitorController extends Controller
{
    public function __construct() { \$this->middleware(['auth', 'can:manage hostel visitors']); }
    public function index() {
        \$visitors = HostelVisitor::with('hostel', 'student')->get();
        \$hostels = Hostel::all();
        \$students = User::role('student')->get();
        return view('hostels.visitors.index', compact('visitors', 'hostels', 'students'));
    }
    public function store(Request \$request) {
        HostelVisitor::create(\$request->validate([
            'hostel_id' => 'required', 'student_id' => 'required', 'visitor_name' => 'required|string',
            'relation' => 'required|string', 'date' => 'required|date', 'in_time' => 'required',
            'out_time' => 'nullable', 'purpose' => 'nullable|string'
        ]));
        return back()->with('status', 'Visitor logged.');
    }
    public function update(Request \$request, \$id) {
        HostelVisitor::findOrFail(\$id)->update(\$request->validate([
            'out_time' => 'required'
        ]));
        return back()->with('status', 'Visitor out time logged.');
    }
    public function destroy(\$id) {
        HostelVisitor::findOrFail(\$id)->delete();
        return back()->with('status', 'Visitor deleted.');
    }
}
");

// 7. HostelMaintenanceRequestController
file_put_contents("$controllersDir/HostelMaintenanceRequestController.php", "<?php
namespace App\Http\Controllers;
use App\Models\HostelMaintenanceRequest;
use App\Models\Hostel;
use App\Models\HostelRoom;
use Illuminate\Http\Request;

class HostelMaintenanceRequestController extends Controller
{
    public function __construct() { \$this->middleware(['auth', 'can:manage hostel maintenance']); }
    public function index() {
        \$requests = HostelMaintenanceRequest::with('hostel', 'room', 'reporter')->get();
        \$hostels = Hostel::with('rooms')->get();
        return view('hostels.maintenance.index', compact('requests', 'hostels'));
    }
    public function store(Request \$request) {
        \$data = \$request->validate([
            'hostel_id' => 'required', 'hostel_room_id' => 'required', 'issue_type' => 'required|string',
            'description' => 'required|string', 'priority' => 'required|in:Low,Medium,High'
        ]);
        \$data['reported_by_id'] = auth()->id();
        \$data['status'] = 'Pending';
        HostelMaintenanceRequest::create(\$data);
        return back()->with('status', 'Maintenance request created.');
    }
    public function update(Request \$request, \$id) {
        HostelMaintenanceRequest::findOrFail(\$id)->update(\$request->validate([
            'status' => 'required|in:Pending,In Progress,Resolved'
        ]));
        return back()->with('status', 'Request status updated.');
    }
    public function destroy(\$id) {
        HostelMaintenanceRequest::findOrFail(\$id)->delete();
        return back()->with('status', 'Request deleted.');
    }
}
");

echo "Controllers generated.\n";

// Basic View Stubs
$viewStub = "@extends('layouts.app')
@section('title', 'Hostel Management')
@section('content')
<div class=\"container mx-auto px-4 py-8\">
    <h2 class=\"text-2xl font-bold mb-6\">{{ \$title }}</h2>
    <div class=\"bg-white rounded-lg shadow p-6\">
        @if(session('status'))
            <div class=\"bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4\">
                {{ session('status') }}
            </div>
        @endif
        <p>This is a generated view for {{ \$title }}. You will need to implement the table and forms.</p>
    </div>
</div>
@endsection";

$views = [
    'hostels/index.blade.php' => 'Hostels',
    'rooms/index.blade.php' => 'Rooms',
    'beds/index.blade.php' => 'Beds',
    'allocations/index.blade.php' => 'Allocations',
    'attendances/index.blade.php' => 'Attendance',
    'visitors/index.blade.php' => 'Visitors',
    'maintenance/index.blade.php' => 'Maintenance Requests',
];

foreach($views as $path => $title) {
    $fullPath = "$viewsDir/$path";
    $dir = dirname($fullPath);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($fullPath, str_replace('{{ $title }}', $title, $viewStub));
}

echo "Views generated.\n";
