<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\SalaryComponent;
use App\Models\TeacherPayroll;
use App\Models\User;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherPayrollController extends Controller
{
    public function __construct(private readonly PayrollService $payroll)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->isStudent() || $user->isParent() || !$user->isStaff()) {
            abort(403, 'Unauthorized to access payroll.');
        }

        $isPrivileged = $user->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'vice-principal', 'hr-manager', 'hr']);

        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year', now()->year);
        $calendarType = $request->query('cal', 'bs'); // 'bs' (Bikram Sambat) or 'ad' (Gregorian)
        $statusFilter = $request->query('status', 'all');
        $deptFilter   = $request->query('department_id', 'all');
        $search       = trim($request->query('search', ''));

        $query = TeacherPayroll::with(['teacher.departments', 'processor', 'approver'])
            ->where('month', $month)
            ->where('year', $year);

        if (!$isPrivileged) {
            $query->where('teacher_id', $user->id);
        } else {
            if ($statusFilter === 'paid') {
                $query->where('status', 'paid');
            } elseif ($statusFilter === 'approved') {
                $query->where('status', 'draft')->where('is_approved', true);
            } elseif ($statusFilter === 'draft') {
                $query->where('status', 'draft')->where('is_approved', false);
            }

            if ($deptFilter !== 'all' && is_numeric($deptFilter)) {
                $query->whereHas('teacher.departments', function ($q) use ($deptFilter) {
                    $q->where('departments.id', $deptFilter);
                });
            }

            if ($search !== '') {
                $query->whereHas('teacher', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('bank_account_no', 'like', "%{$search}%");
                    });
                });
            }
        }

        $payrolls = $query->latest('updated_at')->get();

        // Calculate aggregate statistics for the entire month/year
        $allPeriodQuery = TeacherPayroll::where('month', $month)->where('year', $year);
        if (!$isPrivileged) {
            $allPeriodQuery->where('teacher_id', $user->id);
        }
        $allPeriodPayrolls = $allPeriodQuery->get();

        $totalGross = (float) $allPeriodPayrolls->sum('gross_salary');
        $totalNet = (float) $allPeriodPayrolls->sum('net_salary');
        $totalPaid = (float) $allPeriodPayrolls->where('status', 'paid')->sum('net_salary');
        $totalPending = (float) $allPeriodPayrolls->where('status', 'draft')->sum('net_salary');
        $totalTds = (float) $allPeriodPayrolls->sum(fn($p) => $p->tax_deduction ?: $p->tds_payable);
        $totalCitSsf = (float) $allPeriodPayrolls->sum(fn($p) => $p->cit_deduction + $p->ssf_deduction);
        $totalDeductions = (float) $allPeriodPayrolls->sum(fn($p) => $p->total_deductions);

        $totalSlips = $allPeriodPayrolls->count();
        $paidCount = $allPeriodPayrolls->where('status', 'paid')->count();
        $approvedCount = $allPeriodPayrolls->where('status', 'draft')->where('is_approved', true)->count();
        $draftCount = $allPeriodPayrolls->where('status', 'draft')->where('is_approved', false)->count();
        $disbursedPercent = $totalSlips > 0 ? (int) round(($paidCount / $totalSlips) * 100) : 0;

        // Teachers list for dropdown / quick payroll generation
        $teachers = $isPrivileged
            ? User::where(function ($q) {
                $q->role(['teacher', 'class-teacher'])->orWhereIn('role', ['teacher', 'class-teacher']);
            })
            ->with(['departments', 'teacherContracts' => function ($q) {
                $q->where('status', 'active')->latest();
            }])
            ->orderBy('first_name')
            ->get()
            : collect([$user]);

        $departments = Department::orderBy('name')->get();
        $components = SalaryComponent::active()->orderBy('sort_order')->get();

        $nepaliMonths = [
            1 => 'Baisakh', 2 => 'Jestha', 3 => 'Ashadh', 4 => 'Shrawan',
            5 => 'Bhadra', 6 => 'Ashwin', 7 => 'Kartik', 8 => 'Mangsir',
            9 => 'Poush', 10 => 'Magh', 11 => 'Falgun', 12 => 'Chaitra',
        ];

        $englishMonths = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        return view('teachers.payroll.index', compact(
            'payrolls', 'teachers', 'departments', 'components',
            'month', 'year', 'calendarType', 'statusFilter', 'deptFilter', 'search',
            'totalGross', 'totalNet', 'totalPaid', 'totalPending',
            'totalTds', 'totalCitSsf', 'totalDeductions',
            'totalSlips', 'paidCount', 'approvedCount', 'draftCount', 'disbursedPercent',
            'nepaliMonths', 'englishMonths', 'isPrivileged'
        ));
    }

    public function store(Request $request)
    {
        $this->authorizeStaffManagement();

        $data = $request->validate([
            'teacher_id'          => 'required|exists:users,id',
            'month'               => 'required|integer|min:1|max:12',
            'year'                => 'required|integer|min:2000|max:2100',
            'payment_method'      => 'nullable|string|in:cash,bank_transfer,cheque',
            'notes'               => 'nullable|string|max:500',
            'basic_salary'        => 'nullable|numeric|min:0',
            'allowances'          => 'nullable|numeric|min:0',
            'overtime'            => 'nullable|numeric|min:0',
            'bonus'               => 'nullable|numeric|min:0',
            'tax_deduction'       => 'nullable|numeric|min:0',
            'cit_deduction'       => 'nullable|numeric|min:0',
            'ssf_deduction'       => 'nullable|numeric|min:0',
            'late_fine'           => 'nullable|numeric|min:0',
            'loan_deduction'      => 'nullable|numeric|min:0',
            'advance_deduction'   => 'nullable|numeric|min:0',
            'insurance_deduction' => 'nullable|numeric|min:0',
            'other_deductions'    => 'nullable|numeric|min:0',
            'working_days'        => 'nullable|integer|min:0|max:31',
            'present_days'        => 'nullable|integer|min:0|max:31',
            'absent_days'         => 'nullable|integer|min:0|max:31',
            'leave_days'          => 'nullable|integer|min:0|max:31',
            'status'              => 'nullable|in:draft,paid',
        ]);

        $teacher = User::findOrFail($data['teacher_id']);

        if ($request->filled('basic_salary')) {
            $basic    = (float) $data['basic_salary'];
            $allow    = (float) ($data['allowances'] ?? 0);
            $overtime = (float) ($data['overtime'] ?? 0);
            $bonus    = (float) ($data['bonus'] ?? 0);
            $tds      = (float) ($data['tax_deduction'] ?? 0);
            $cit      = (float) ($data['cit_deduction'] ?? 0);
            $ssf      = (float) ($data['ssf_deduction'] ?? 0);
            $late     = (float) ($data['late_fine'] ?? 0);
            $loan     = (float) ($data['loan_deduction'] ?? 0);
            $advance  = (float) ($data['advance_deduction'] ?? 0);
            $ins      = (float) ($data['insurance_deduction'] ?? 0);
            $other    = (float) ($data['other_deductions'] ?? 0);

            $gross    = $basic + $allow + $overtime + $bonus;
            $totalDed = $tds + $cit + $ssf + $late + $loan + $advance + $ins + $other;
            $net      = max(0, $gross - $totalDed);

            $workingDays = $data['working_days'] ?? Carbon::create($data['year'], $data['month'])->daysInMonth;

            // Attendance check
            $attendance = \App\Models\TeacherAttendance::where('teacher_id', $data['teacher_id'])
                ->whereMonth('date', $data['month'])
                ->whereYear('date', $data['year'])
                ->get();

            $presentDays = $data['present_days'] ?? $attendance->whereIn('status', ['present', 'late', 'half_day'])->count();
            $absentDays  = $data['absent_days']  ?? $attendance->where('status', 'absent')->count();
            $leaveDays   = $data['leave_days']   ?? $attendance->where('status', 'on_leave')->count();

            $payload = [
                'basic_salary'        => $basic,
                'allowances'          => $allow,
                'overtime'            => $overtime,
                'bonus'               => $bonus,
                'gross_salary'        => $gross,
                'tax_deduction'       => $tds,
                'tds_payable'         => $tds,
                'cit_deduction'       => $cit,
                'ssf_deduction'       => $ssf,
                'late_fine'           => $late,
                'loan_deduction'      => $loan,
                'advance_deduction'   => $advance,
                'insurance_deduction' => $ins,
                'other_deductions'    => $other,
                'net_salary'          => $net,
                'working_days'        => $workingDays,
                'present_days'        => $presentDays,
                'absent_days'         => $absentDays,
                'leave_days'          => $leaveDays,
                'notes'               => $data['notes'] ?? null,
                'payment_method'      => $data['payment_method'] ?? null,
            ];
        } else {
            // Auto-compute from PayrollService
            $payload = $this->payroll->computeForStaff($teacher, (int) $data['month'], (int) $data['year']);
            $bonus = (float) ($data['bonus'] ?? 0);
            $payload['bonus'] = $bonus;
            $payload['gross_salary'] += $bonus;
            $payload['net_salary']   += $bonus;
            unset($payload['_earnings'], $payload['_deductions']);
            if (isset($data['notes'])) $payload['notes'] = $data['notes'];
            if (isset($data['payment_method'])) $payload['payment_method'] = $data['payment_method'];
        }

        TeacherPayroll::updateOrCreate(
            ['teacher_id' => $data['teacher_id'], 'month' => $data['month'], 'year' => $data['year']],
            array_merge($payload, [
                'processed_by' => auth()->id(),
                'status'       => $data['status'] ?? 'draft',
                'payment_date' => ($data['status'] ?? 'draft') === 'paid' ? now()->toDateString() : null,
            ])
        );

        return back()->with('status', "Payroll record for {$teacher->full_name} saved successfully.");
    }

    public function bulkGenerate(Request $request)
    {
        $this->authorizeStaffManagement();

        $data = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000|max:2100',
        ]);

        $teachers = User::where(function ($q) {
            $q->role(['teacher', 'class-teacher'])->orWhereIn('role', ['teacher', 'class-teacher']);
        })->get();

        $generated = 0;
        $skipped   = 0;

        foreach ($teachers as $teacher) {
            $exists = TeacherPayroll::where('teacher_id', $teacher->id)
                ->where('month', $data['month'])
                ->where('year', $data['year'])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Fallback to active contract salary if teacher basic_salary is not set
            if (empty($teacher->basic_salary) || $teacher->basic_salary <= 0) {
                $contractSalary = $teacher->teacherContracts()->where('status', 'active')->value('basic_salary');
                if ($contractSalary) {
                    $teacher->basic_salary = $contractSalary;
                }
            }

            try {
                $computed = $this->payroll->computeForStaff($teacher, (int) $data['month'], (int) $data['year']);
                unset($computed['_earnings'], $computed['_deductions']);

                TeacherPayroll::create(array_merge($computed, [
                    'teacher_id'   => $teacher->id,
                    'month'        => $data['month'],
                    'year'         => $data['year'],
                    'processed_by' => auth()->id(),
                    'status'       => 'draft',
                    'is_approved'  => false,
                ]));
                $generated++;
            } catch (\Throwable) {
                $skipped++;
            }
        }

        return back()->with('status', "Bulk Payroll Completed: {$generated} faculty slips generated, {$skipped} already existed or skipped.");
    }

    public function approve($id)
    {
        $this->authorizeStaffManagement();
        $payroll = TeacherPayroll::findOrFail($id);
        $payroll->update([
            'is_approved' => true,
            'approved_by' => auth()->id(),
        ]);

        return back()->with('status', "Salary slip for {$payroll->teacher?->full_name} has been approved.");
    }

    public function markPaid(Request $request, $id)
    {
        $this->authorizeStaffManagement();
        $payroll = TeacherPayroll::findOrFail($id);

        $method = $request->input('payment_method', 'bank_transfer');
        $date   = $request->input('payment_date', now()->toDateString());

        $payroll->update([
            'status'         => 'paid',
            'payment_date'   => $date,
            'payment_method' => $method,
            'is_approved'    => true,
            'approved_by'    => $payroll->approved_by ?: auth()->id(),
        ]);

        return back()->with('status', "Disbursement of Rs. " . number_format($payroll->net_salary, 2) . " marked as paid via {$method}.");
    }

    public function bulkPay(Request $request)
    {
        $this->authorizeStaffManagement();

        $data = $request->validate([
            'ids'            => 'required|array|min:1',
            'ids.*'          => 'exists:teacher_payrolls,id',
            'payment_method' => 'nullable|string|in:cash,bank_transfer,cheque',
            'payment_date'   => 'nullable|date',
            'action'         => 'required|string|in:mark_paid,bulk_approve',
        ]);

        $method = $data['payment_method'] ?? 'bank_transfer';
        $date   = $data['payment_date'] ?? now()->toDateString();
        $count  = count($data['ids']);

        if ($data['action'] === 'bulk_approve') {
            TeacherPayroll::whereIn('id', $data['ids'])->update([
                'is_approved' => true,
                'approved_by' => auth()->id(),
            ]);
            return back()->with('status', "Bulk Action Completed: {$count} slips successfully approved.");
        }

        TeacherPayroll::whereIn('id', $data['ids'])->update([
            'status'         => 'paid',
            'payment_date'   => $date,
            'payment_method' => $method,
            'is_approved'    => true,
            'approved_by'    => auth()->id(),
        ]);

        return back()->with('status', "Disbursement Completed: {$count} salary slips marked as paid via {$method}.");
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorizeStaffManagement();

        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year', now()->year);

        $nepaliMonths = [
            1 => 'Baisakh', 2 => 'Jestha', 3 => 'Ashadh', 4 => 'Shrawan',
            5 => 'Bhadra', 6 => 'Ashwin', 7 => 'Kartik', 8 => 'Mangsir',
            9 => 'Poush', 10 => 'Magh', 11 => 'Falgun', 12 => 'Chaitra',
        ];
        $monthName = $nepaliMonths[$month] ?? Carbon::create()->month($month)->format('F');

        $payrolls = TeacherPayroll::with(['teacher.departments'])
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $filename = "Bank_Transfer_Advice_Faculty_{$monthName}_{$year}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($payrolls) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'SN',
                'Employee ID',
                'Faculty Name',
                'Email',
                'Phone',
                'Department',
                'Bank Name',
                'Account Number',
                'Basic Salary (NPR)',
                'Allowances (NPR)',
                'Overtime (NPR)',
                'Bonus (NPR)',
                'Gross Salary (NPR)',
                'TDS / Tax (NPR)',
                'CIT (NPR)',
                'SSF (NPR)',
                'Late Fine (NPR)',
                'Other Deductions (NPR)',
                'Net Salary (NPR)',
                'Status',
                'Payment Method',
                'Disbursement Date',
            ]);

            foreach ($payrolls as $idx => $p) {
                $t = $p->teacher;
                $dept = $t?->departments?->pluck('name')->join(', ') ?: 'General';
                fputcsv($handle, [
                    $idx + 1,
                    'EMP-' . ($t?->id ?? $p->teacher_id),
                    $t?->full_name ?? 'N/A',
                    $t?->email ?? '',
                    $t?->phone ?? '',
                    $dept,
                    $t?->bank_name ?? 'Not Specified',
                    $t?->bank_account_no ?? 'Not Specified',
                    number_format($p->basic_salary, 2, '.', ''),
                    number_format($p->allowances, 2, '.', ''),
                    number_format($p->overtime, 2, '.', ''),
                    number_format($p->bonus, 2, '.', ''),
                    number_format($p->gross_salary, 2, '.', ''),
                    number_format($p->tax_deduction ?: $p->tds_payable, 2, '.', ''),
                    number_format($p->cit_deduction, 2, '.', ''),
                    number_format($p->ssf_deduction, 2, '.', ''),
                    number_format($p->late_fine, 2, '.', ''),
                    number_format($p->other_deductions, 2, '.', ''),
                    number_format($p->net_salary, 2, '.', ''),
                    strtoupper($p->status),
                    $p->payment_method ?? 'Bank Transfer',
                    $p->payment_date ? $p->payment_date->format('Y-m-d') : 'Pending',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function slip(int $id)
    {
        $user = auth()->user();
        if (!$user || $user->isStudent() || $user->isParent() || !$user->isStaff()) {
            abort(403, 'Unauthorized.');
        }

        $payroll = TeacherPayroll::with(['teacher.departments', 'processor', 'approver'])->findOrFail($id);

        $isPrivileged = $user->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'vice-principal', 'hr-manager', 'hr']);
        if (!$isPrivileged && $payroll->teacher_id !== $user->id) {
            abort(403, 'Unauthorized to view other staff salary slips.');
        }

        $pdf = Pdf::loadView('teachers.payroll.slip', compact('payroll'))
            ->setPaper('A4')
            ->setOptions(['dpi' => 120, 'defaultFont' => 'sans-serif']);

        return $pdf->stream("salary-slip-{$payroll->teacher_id}-{$payroll->month}-{$payroll->year}.pdf");
    }

    public function destroy(int $id)
    {
        $this->authorizeStaffManagement();
        $payroll = TeacherPayroll::findOrFail($id);
        $payroll->delete();
        return back()->with('status', 'Payroll slip deleted successfully.');
    }

    private function authorizeStaffManagement(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'vice-principal', 'hr-manager', 'hr'])) {
            abort(403, 'Unauthorized: Staff / Payroll management rights required.');
        }
    }
}
