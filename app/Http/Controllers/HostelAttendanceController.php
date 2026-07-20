<?php
namespace App\Http\Controllers;
use App\Models\HostelAttendance;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use Illuminate\Http\Request;

class HostelAttendanceController extends Controller
{
    public function __construct() { $this->middleware(['auth', 'can:manage hostel attendance']); }
    public function index(Request $request) {
        $hostels = Hostel::all();
        $date = $request->date ?? date('Y-m-d');
        $hostel_id = $request->hostel_id;
        
        $students = [];
        if($hostel_id) {
            $students = HostelAllocation::with('student')->where('hostel_id', $hostel_id)->where('status', 'Active')->get();
        }
        
        $attendances = HostelAttendance::where('date', $date)->when($hostel_id, fn($q) => $q->where('hostel_id', $hostel_id))->get()->keyBy('student_id');
        
        return view('hostels.attendances.index', compact('hostels', 'date', 'hostel_id', 'students', 'attendances'));
    }
    public function store(Request $request) {
        $request->validate(['hostel_id' => 'required', 'date' => 'required|date', 'attendance' => 'required|array']);
        foreach($request->attendance as $student_id => $status) {
            HostelAttendance::updateOrCreate(
                ['hostel_id' => $request->hostel_id, 'student_id' => $student_id, 'date' => $request->date],
                ['status' => $status]
            );
        }
        return back()->with('status', 'Attendance saved.');
    }
}
