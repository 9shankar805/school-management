<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Models\LeaveApplication;
use App\Models\StaffContract;
use App\Models\TeacherPayroll;

class HrController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr');
    }

    /**
     * HR Dashboard Overview
     */
    public function index()
    {
        try {
            $totalStaff = User::where(function($q) {
                $q->whereDoesntHave('roles', fn($r) => $r->whereIn('name', ['student', 'parent']))
                  ->where(function($sub) {
                      $sub->whereNull('role')
                          ->orWhereNotIn('role', ['student', 'parent']);
                  });
            })->count();

            $pendingLeaves = LeaveApplication::where('status', 'pending')->count();
            $activeJobs = JobPosting::where('status', 'open')->orWhere('status', 'published')->count();
            $recentApplications = JobApplication::with('jobPosting')->orderBy('created_at', 'desc')->take(5)->get();

            return view('hr.dashboard', compact('totalStaff', 'pendingLeaves', 'activeJobs', 'recentApplications'));
        } catch (\Exception $e) {
            return view('hr.dashboard', [
                'totalStaff' => 0,
                'pendingLeaves' => 0,
                'activeJobs' => 0,
                'recentApplications' => collect(),
                'error' => 'Unable to load HR dashboard data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Leave Management Workflow
     */
    public function leaves()
    {
        try {
            $leaves = LeaveApplication::with(['user', 'leaveType'])->orderBy('created_at', 'desc')->paginate(15);
            return view('hr.leaves', compact('leaves'));
        } catch (\Exception $e) {
            return view('hr.leaves', ['leaves' => null, 'error' => 'Unable to load leave data: ' . $e->getMessage()]);
        }
    }

    public function updateLeaveStatus(Request $request, $id)
    {
        try {
            $leave = LeaveApplication::findOrFail($id);
            $leave->status = $request->status; // 'approved' or 'rejected'
            $leave->save();

            return redirect()->back()->with('success', 'Leave application status updated.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to update leave status: ' . $e->getMessage());
        }
    }

    /**
     * Recruitment & Job Postings
     */
    public function recruitment()
    {
        try {
            $jobs = JobPosting::withCount('applications')->orderBy('created_at', 'desc')->paginate(10);
            $applications = JobApplication::with(['jobPosting', 'user'])->orderBy('created_at', 'desc')->take(10)->get();
            return view('hr.recruitment', compact('jobs', 'applications'));
        } catch (\Exception $e) {
            return view('hr.recruitment', [
                'jobs' => null,
                'applications' => collect(),
                'error' => 'Unable to load recruitment data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Payroll Processing Interface
     */
    public function payroll()
    {
        try {
            $payrolls = TeacherPayroll::with('teacher')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->paginate(15);
            return view('hr.payroll', compact('payrolls'));
        } catch (\Exception $e) {
            return view('hr.payroll', ['payrolls' => null, 'error' => 'Unable to load payroll data: ' . $e->getMessage()]);
        }
    }

    /**
     * Employee Lifecycle Records
     */
    public function employees()
    {
        try {
            $contracts = StaffContract::with('user')->orderBy('end_date', 'asc')->paginate(15);
            return view('hr.employees', compact('contracts'));
        } catch (\Exception $e) {
            return view('hr.employees', ['contracts' => null, 'error' => 'Unable to load employee data: ' . $e->getMessage()]);
        }
    }
}
