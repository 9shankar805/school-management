<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\StaffInterface;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    protected $staffRepository;

    public function __construct(StaffInterface $staffRepository) {
        $this->middleware('auth');
        $this->middleware('role_or_permission:view staff|manage staff|super-admin|admin|principal|hr-manager|hr')->only(['index']);
        $this->middleware('role_or_permission:create staff|manage staff|super-admin|admin|principal|hr-manager|hr')->except(['index']);
        $this->staffRepository = $staffRepository;
    }

    public static function getStaffRoles(): array
    {
        return [
            'accountant'           => ['label' => 'Accountant', 'desc' => 'Fee billing, salary slips, expense vouchers, accounting ledgers', 'icon' => 'bi-calculator'],
            'librarian'            => ['label' => 'Librarian', 'desc' => 'Book cataloging, student/faculty lending, circulation log', 'icon' => 'bi-journals'],
            'receptionist'         => ['label' => 'Receptionist / Front Desk', 'desc' => 'Visitor passes, call logs, initial parent queries', 'icon' => 'bi-telephone'],
            'hr-manager'           => ['label' => 'HR Manager', 'desc' => 'Staff recruitment, contracts, leave approvals, payroll oversight', 'icon' => 'bi-briefcase'],
            'attendance-officer'   => ['label' => 'Attendance Officer', 'desc' => 'Daily staff and student roll calls, late arrivals', 'icon' => 'bi-calendar-check'],
            'admission-officer'    => ['label' => 'Admission Officer', 'desc' => 'Inquiry management, student registrations, entrance tests', 'icon' => 'bi-person-badge'],
            'transport-manager'    => ['label' => 'Transport Manager', 'desc' => 'Fleet routes, bus tracking, driver assignments, fuel log', 'icon' => 'bi-bus-front'],
            'hostel-manager'       => ['label' => 'Hostel Manager / Warden', 'desc' => 'Hostel buildings, bed allocation, room maintenance', 'icon' => 'bi-building'],
            'exam-controller'      => ['label' => 'Exam Controller', 'desc' => 'Exam schedules, mark sheets, grade reports, certificates', 'icon' => 'bi-clipboard-data'],
            'academic-coordinator' => ['label' => 'Academic Coordinator', 'desc' => 'Curriculum planning, routines, academic schedules', 'icon' => 'bi-mortarboard'],
            'vice-principal'       => ['label' => 'Vice Principal', 'desc' => 'Academic supervision, institutional operations', 'icon' => 'bi-shield-check'],
            'nurse'                => ['label' => 'School Nurse / Medical', 'desc' => 'Student clinic, health checkups, medical records', 'icon' => 'bi-heart-pulse'],
            'lab-assistant'        => ['label' => 'Lab Assistant', 'desc' => 'Science lab equipment, computer lab maintenance', 'icon' => 'bi-motherboard'],
            'staff'                => ['label' => 'General Staff / Support', 'desc' => 'General non-teaching school administrative staff', 'icon' => 'bi-person'],
        ];
    }

    public function index(Request $request) {
        $search = $request->query('search');
        $role   = $request->query('role');
        $staffs = $this->staffRepository->getAllStaff($search, $role);
        $roles  = self::getStaffRoles();

        return view('staff.index', compact('staffs', 'roles', 'search', 'role'));
    }

    public function create() {
        $assignableRoles = self::getStaffRoles();
        return view('staff.create', compact('assignableRoles'));
    }

    public function store(Request $request) {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|max:255|unique:users,email',
            'role'       => 'required|string|not_in:super-admin,student,parent',
            'password'   => 'required|string|min:8|confirmed',
            'phone'      => 'nullable|string|max:30',
            'gender'     => 'nullable|string|in:Male,Female,Other',
            'address'    => 'nullable|string|max:255',
            'address2'   => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:100',
            'zip'        => 'nullable|string|max:20',
            'photo'      => 'nullable|image|max:2048',
        ]);

        $this->staffRepository->create($request);
        return redirect()->route('staff.index')->with('status', 'Staff member added successfully.');
    }

    public function edit($id) {
        $staff = $this->staffRepository->findById($id);
        $assignableRoles = self::getStaffRoles();
        return view('staff.edit', compact('staff', 'assignableRoles'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|max:255|unique:users,email,'.$id,
            'role'       => 'nullable|string|not_in:super-admin,student,parent',
            'password'   => 'nullable|string|min:8|confirmed',
            'phone'      => 'nullable|string|max:30',
            'gender'     => 'nullable|string|in:Male,Female,Other',
            'address'    => 'nullable|string|max:255',
            'address2'   => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:100',
            'zip'        => 'nullable|string|max:20',
            'photo'      => 'nullable|image|max:2048',
        ]);

        $this->staffRepository->update($request, $id);
        return redirect()->route('staff.index')->with('status', 'Staff member updated successfully.');
    }

    public function destroy($id) {
        $this->staffRepository->delete($id);
        return redirect()->route('staff.index')->with('status', 'Staff member removed successfully.');
    }
}
