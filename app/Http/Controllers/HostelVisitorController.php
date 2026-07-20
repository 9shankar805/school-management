<?php
namespace App\Http\Controllers;
use App\Models\HostelVisitor;
use App\Models\Hostel;
use App\Models\User;
use Illuminate\Http\Request;

class HostelVisitorController extends Controller
{
    public function __construct() { $this->middleware(['auth', 'can:manage hostel visitors']); }
    public function index() {
        $visitors = HostelVisitor::with('hostel', 'student')->get();
        $hostels = Hostel::all();
        $students = User::role('student')->get();
        return view('hostels.visitors.index', compact('visitors', 'hostels', 'students'));
    }
    public function store(Request $request) {
        HostelVisitor::create($request->validate([
            'hostel_id' => 'required', 'student_id' => 'required', 'visitor_name' => 'required|string',
            'relation' => 'required|string', 'date' => 'required|date', 'in_time' => 'required',
            'out_time' => 'nullable', 'purpose' => 'nullable|string'
        ]));
        return back()->with('status', 'Visitor logged.');
    }
    public function update(Request $request, $id) {
        HostelVisitor::findOrFail($id)->update($request->validate([
            'out_time' => 'required'
        ]));
        return back()->with('status', 'Visitor out time logged.');
    }
    public function destroy($id) {
        HostelVisitor::findOrFail($id)->delete();
        return back()->with('status', 'Visitor deleted.');
    }
}
