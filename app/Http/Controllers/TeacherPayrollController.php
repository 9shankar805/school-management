<?php

namespace App\Http\Controllers;

use App\Models\TeacherPayroll;
use App\Models\TeacherAttendance;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherPayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:view teachers']);
    }

    public function index(Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year',  now()->year);

        $payrolls = TeacherPayroll::with('teacher')
            ->where('month', $month)->where('year', $year)
            ->get();

        $teachers = User::role(['teacher', 'class-teacher'])->get();
        $totalPaid = $payrolls->where('status', 'paid')->sum('net_salary');

        return view('teachers.payroll.index', compact('payrolls', 'teachers', 'month', 'year', 'totalPaid'));
    }

    /** Create / update payroll for a teacher-month */
    public function store(Request $request)
    {
        $this->authorize('create teachers');
        $data = $request->validate([
            'teacher_id'       => 'required|exists:users,id',
            'month'            => 'required|integer|min:1|max:12',
            'year'             => 'required|integer|min:2000|max:2100',
            'basic_salary'     => 'required|numeric|min:0',
            'allowances'       => 'nullable|numeric|min:0',
            'overtime'         => 'nullable|numeric|min:0',
            'tax_deduction'    => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:500',
        ]);

        $data['allowances']       = $data['allowances'] ?? 0;
        $data['overtime']         = $data['overtime'] ?? 0;
        $data['tax_deduction']    = $data['tax_deduction'] ?? 0;
        $data['other_deductions'] = $data['other_deductions'] ?? 0;
        $data['gross_salary']     = $data['basic_salary'] + $data['allowances'] + $data['overtime'];
        $data['net_salary']       = $data['gross_salary'] - $data['tax_deduction'] - $data['other_deductions'];

        // Pull attendance for the month
        $attendance = TeacherAttendance::where('teacher_id', $data['teacher_id'])
            ->whereMonth('date', $data['month'])
            ->whereYear('date', $data['year'])
            ->get();

        $data['working_days'] = Carbon::create($data['year'], $data['month'])->daysInMonth;
        $data['present_days'] = $attendance->whereIn('status', ['present', 'late', 'half_day'])->count();
        $data['absent_days']  = $attendance->where('status', 'absent')->count();
        $data['leave_days']   = $attendance->where('status', 'on_leave')->count();
        $data['processed_by'] = auth()->id();
        $data['status']       = 'draft';

        $payroll = TeacherPayroll::updateOrCreate(
            ['teacher_id' => $data['teacher_id'], 'month' => $data['month'], 'year' => $data['year']],
            $data
        );

        return back()->with('status', "Payroll saved for {$payroll->month_name} {$data['year']}.");
    }

    /** Mark payroll as paid */
    public function markPaid(int $id)
    {
        $this->authorize('create teachers');
        TeacherPayroll::findOrFail($id)->update([
            'status'       => 'paid',
            'payment_date' => now()->toDateString(),
        ]);
        return back()->with('status', 'Payroll marked as paid.');
    }

    /** Generate salary slip PDF */
    public function slip(int $id)
    {
        $payroll = TeacherPayroll::with('teacher', 'processor')->findOrFail($id);
        $pdf = Pdf::loadView('teachers.payroll.slip', compact('payroll'))
            ->setPaper('A4')
            ->setOptions(['dpi' => 120, 'defaultFont' => 'sans-serif']);
        return $pdf->stream("salary-slip-{$payroll->teacher->id}-{$payroll->month}-{$payroll->year}.pdf");
    }

    public function destroy(int $id)
    {
        $this->authorize('create teachers');
        TeacherPayroll::findOrFail($id)->delete();
        return back()->with('status', 'Payroll record deleted.');
    }
}
