<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use App\Models\StaffSalaryStructure;
use App\Models\TeacherPayroll;
use App\Models\User;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherPayrollController extends Controller
{
    public function __construct(private readonly PayrollService $payroll)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user  = auth()->user();
        if (!$user || $user->isStudent() || $user->isParent() || !$user->isStaff()) {
            abort(403, 'Unauthorized to access payroll.');
        }

        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year',  now()->year);

        $query = TeacherPayroll::with('teacher')
            ->where('month', $month)
            ->where('year', $year);

        if ($user->hasAnyRole(['teacher', 'class-teacher']) && !$user->hasAnyRole(['super-admin', 'admin', 'principal', 'hr-manager', 'hr'])) {
            $query->where('teacher_id', $user->id);
        }

        $payrolls  = $query->get();
        $teachers  = $user->hasAnyRole(['super-admin', 'admin', 'principal', 'hr-manager', 'hr'])
            ? User::where(function($q) { $q->role(['teacher', 'class-teacher'])->orWhereIn('role', ['teacher', 'class-teacher']); })->orderBy('first_name')->get()
            : collect([$user]);
        $totalPaid = $payrolls->where('status', 'paid')->sum('net_salary');
        $components = SalaryComponent::active()->orderBy('sort_order')->get();

        return view('teachers.payroll.index', compact(
            'payrolls', 'teachers', 'month', 'year', 'totalPaid', 'components'
        ));
    }

    /**
     * Create / update payroll for a teacher-month.
     * Auto-computes from salary components if basic_salary not manually provided.
     */
    public function store(Request $request)
    {
        $this->authorize('create teachers');

        $data = $request->validate([
            'teacher_id'       => 'required|exists:users,id',
            'month'            => 'required|integer|min:1|max:12',
            'year'             => 'required|integer|min:2000|max:2100',
            'payment_method'   => 'nullable|in:cash,bank_transfer,cheque',
            'notes'            => 'nullable|string|max:500',
            // Optional manual overrides
            'basic_salary'     => 'nullable|numeric|min:0',
            'allowances'       => 'nullable|numeric|min:0',
            'overtime'         => 'nullable|numeric|min:0',
            'bonus'            => 'nullable|numeric|min:0',
            'tax_deduction'    => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'cit_deduction'    => 'nullable|numeric|min:0',
            'ssf_deduction'    => 'nullable|numeric|min:0',
            'late_fine'        => 'nullable|numeric|min:0',
        ]);

        $teacher = User::findOrFail($data['teacher_id']);

        if ($request->filled('basic_salary')) {
            // Manual mode
            $basic    = (float) $data['basic_salary'];
            $allow    = (float) ($data['allowances'] ?? 0);
            $overtime = (float) ($data['overtime'] ?? 0);
            $bonus    = (float) ($data['bonus'] ?? 0);
            $tds      = (float) ($data['tax_deduction'] ?? 0);
            $other    = (float) ($data['other_deductions'] ?? 0);
            $cit      = (float) ($data['cit_deduction'] ?? 0);
            $ssf      = (float) ($data['ssf_deduction'] ?? 0);
            $late     = (float) ($data['late_fine'] ?? 0);
            $gross    = $basic + $allow + $overtime + $bonus;
            $net      = $gross - $tds - $other - $cit - $ssf - $late;

            $workingDays = Carbon::create($data['year'], $data['month'])->daysInMonth;
            $attendance  = \App\Models\TeacherAttendance::where('teacher_id', $data['teacher_id'])
                ->whereMonth('date', $data['month'])->whereYear('date', $data['year'])->get();

            $computed = [
                'basic_salary'     => $basic,
                'allowances'       => $allow,
                'overtime'         => $overtime,
                'bonus'            => $bonus,
                'gross_salary'     => $gross,
                'tax_deduction'    => $tds,
                'tds_payable'      => $tds,
                'other_deductions' => $other,
                'cit_deduction'    => $cit,
                'ssf_deduction'    => $ssf,
                'late_fine'        => $late,
                'net_salary'       => max(0, $net),
                'working_days'     => $workingDays,
                'present_days'     => $attendance->whereIn('status', ['present', 'late', 'half_day'])->count(),
                'absent_days'      => $attendance->where('status', 'absent')->count(),
                'leave_days'       => $attendance->where('status', 'on_leave')->count(),
            ];
        } else {
            // Auto-compute from components
            $computed = $this->payroll->computeForStaff($teacher, (int) $data['month'], (int) $data['year']);
            $computed['bonus']       = (float) ($data['bonus'] ?? 0);
            $computed['gross_salary'] += $computed['bonus'];
            $computed['net_salary']  += $computed['bonus'];
            unset($computed['_earnings'], $computed['_deductions']);
        }

        $payroll = TeacherPayroll::updateOrCreate(
            ['teacher_id' => $data['teacher_id'], 'month' => $data['month'], 'year' => $data['year']],
            array_merge($computed, [
                'processed_by' => auth()->id(),
                'status'       => 'draft',
            ])
        );

        return back()->with('status', "Payroll saved for {$payroll->month_name} {$data['year']}.");
    }

    /** Bulk-generate payroll for all teachers for a given month */
    public function bulkGenerate(Request $request)
    {
        $this->authorize('create teachers');
        $data = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000|max:2100',
        ]);

        $teachers  = User::role(['teacher', 'class-teacher'])->get();
        $generated = 0;
        $skipped   = 0;

        foreach ($teachers as $teacher) {
            $exists = TeacherPayroll::where('teacher_id', $teacher->id)
                ->where('month', $data['month'])->where('year', $data['year'])->exists();

            if ($exists) { $skipped++; continue; }

            try {
                $computed = $this->payroll->computeForStaff($teacher, (int) $data['month'], (int) $data['year']);
                unset($computed['_earnings'], $computed['_deductions']);
                TeacherPayroll::create(array_merge($computed, [
                    'teacher_id'   => $teacher->id,
                    'month'        => $data['month'],
                    'year'         => $data['year'],
                    'processed_by' => auth()->id(),
                    'status'       => 'draft',
                ]));
                $generated++;
            } catch (\Throwable) {
                $skipped++;
            }
        }

        return back()->with('status', "Bulk payroll: {$generated} slips generated, {$skipped} skipped.");
    }

    public function approve(int $id)
    {
        $this->authorize('create teachers');
        TeacherPayroll::findOrFail($id)->update(['is_approved' => true, 'approved_by' => auth()->id()]);
        return back()->with('status', 'Payroll approved.');
    }

    public function markPaid(int $id)
    {
        $this->authorize('create teachers');
        TeacherPayroll::findOrFail($id)->update([
            'status'       => 'paid',
            'payment_date' => now()->toDateString(),
        ]);
        return back()->with('status', 'Payroll marked as paid. Ledger entry auto-posted.');
    }

    public function slip(int $id)
    {
        $user    = auth()->user();
        if (!$user || $user->isStudent() || $user->isParent() || !$user->isStaff()) {
            abort(403, 'Unauthorized.');
        }

        $payroll = TeacherPayroll::with('teacher', 'processor')->findOrFail($id);

        if ($user->hasAnyRole(['teacher', 'class-teacher']) && !$user->hasAnyRole(['super-admin', 'admin', 'principal', 'hr-manager', 'hr'])
            && $payroll->teacher_id !== $user->id) {
            abort(403, 'Unauthorized to view other staff salary slips.');
        }

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
