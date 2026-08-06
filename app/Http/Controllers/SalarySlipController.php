<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use App\Models\SalarySlip;
use App\Models\StaffSalaryStructure;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Salary slip management for non-teaching staff.
 * Teachers are handled by TeacherPayrollController.
 *
 * Payroll is now computed from configurable salary components
 * (salary_components + staff_salary_structures tables) via PayrollService.
 */
class SalarySlipController extends Controller
{
    public function __construct(private readonly PayrollService $payroll)
    {
        $this->middleware(['auth', 'can:manage finance']);
    }

    // ── Salary Slips ─────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year',  now()->year);

        $slips = SalarySlip::with('staff')
            ->where('month', $month)
            ->where('year', $year)
            ->orderBy('status')
            ->get();

        $staff      = User::whereHas('roles', fn($q) => $q->whereNotIn('name', ['student', 'parent', 'guest']))->orderBy('first_name')->get();
        $totalPaid  = $slips->where('status', 'paid')->sum('net_salary');
        $components = SalaryComponent::active()->orderBy('sort_order')->get();

        return view('finance.salary-slips', compact('slips', 'staff', 'month', 'year', 'totalPaid', 'components'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'staff_id'       => 'required|exists:users,id',
            'month'          => 'required|integer|min:1|max:12',
            'year'           => 'required|integer|min:2000|max:2100',
            'payment_method' => 'nullable|in:cash,bank_transfer,cheque',
            'notes'          => 'nullable|string|max:500',
            // Manual overrides (optional — if empty, PayrollService computes from components)
            'basic_salary'     => 'nullable|numeric|min:0',
            'allowances'       => 'nullable|numeric|min:0',
            'overtime'         => 'nullable|numeric|min:0',
            'bonus'            => 'nullable|numeric|min:0',
            'tax_deduction'    => 'nullable|numeric|min:0',
            'provident_fund'   => 'nullable|numeric|min:0',
            'cit_deduction'    => 'nullable|numeric|min:0',
            'ssf_deduction'    => 'nullable|numeric|min:0',
            'late_fine'        => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
        ]);

        $staff = User::findOrFail($data['staff_id']);

        // If key salary fields are provided manually, use them; otherwise auto-compute
        if ($request->filled('basic_salary')) {
            $computed = [
                'basic_salary'     => (float) $data['basic_salary'],
                'allowances'       => (float) ($data['allowances'] ?? 0),
                'overtime'         => (float) ($data['overtime'] ?? 0),
                'bonus'            => (float) ($data['bonus'] ?? 0),
                'tax_deduction'    => (float) ($data['tax_deduction'] ?? 0),
                'tds_payable'      => (float) ($data['tax_deduction'] ?? 0),
                'provident_fund'   => (float) ($data['provident_fund'] ?? 0),
                'cit_deduction'    => (float) ($data['cit_deduction'] ?? 0),
                'ssf_deduction'    => (float) ($data['ssf_deduction'] ?? 0),
                'late_fine'        => (float) ($data['late_fine'] ?? 0),
                'other_deductions' => (float) ($data['other_deductions'] ?? 0),
            ];
            $computed['gross_salary'] = $computed['basic_salary']
                + $computed['allowances'] + $computed['overtime'] + $computed['bonus'];
            $computed['net_salary'] = $computed['gross_salary']
                - $computed['tax_deduction'] - $computed['provident_fund']
                - $computed['cit_deduction'] - $computed['ssf_deduction']
                - $computed['late_fine'] - $computed['other_deductions'];

            // Still pull attendance for the record
            $workingDays = Carbon::create($data['year'], $data['month'])->daysInMonth;
            $attendance  = \App\Models\StaffAttendance::where('staff_id', $data['staff_id'])
                ->whereMonth('date', $data['month'])->whereYear('date', $data['year'])->get();

            $computed['working_days'] = $workingDays;
            $computed['present_days'] = $attendance->whereIn('status', ['present', 'late', 'half_day'])->count();
            $computed['absent_days']  = $attendance->where('status', 'absent')->count();
            $computed['leave_days']   = $attendance->where('status', 'on_leave')->count();
        } else {
            // Auto-compute from salary components
            $computed = $this->payroll->computeForStaff($staff, (int) $data['month'], (int) $data['year']);
            // Add bonus from request
            $computed['bonus'] = (float) ($data['bonus'] ?? 0);
            $computed['gross_salary'] += $computed['bonus'];
            $computed['net_salary']   += $computed['bonus'];
        }

        SalarySlip::updateOrCreate(
            ['staff_id' => $data['staff_id'], 'month' => $data['month'], 'year' => $data['year']],
            array_merge($computed, [
                'payment_method' => $data['payment_method'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'processed_by'   => auth()->id(),
                'status'         => 'draft',
            ])
        );

        return back()->with('status', 'Salary slip generated.');
    }

    /** Bulk-generate salary slips for all staff for a given month/year */
    public function bulkGenerate(Request $request)
    {
        $data = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000|max:2100',
        ]);

        $allStaff = User::whereHas('roles', fn($q) =>
            $q->whereNotIn('name', ['student', 'parent', 'guest', 'super-admin'])
        )->get();

        $generated = 0;
        $skipped   = 0;

        foreach ($allStaff as $staff) {
            // Skip if slip already exists for this month
            $exists = SalarySlip::where('staff_id', $staff->id)
                ->where('month', $data['month'])
                ->where('year', $data['year'])
                ->exists();

            if ($exists) { $skipped++; continue; }

            try {
                $computed = $this->payroll->computeForStaff($staff, (int) $data['month'], (int) $data['year']);
                SalarySlip::create(array_merge($computed, [
                    'staff_id'     => $staff->id,
                    'month'        => $data['month'],
                    'year'         => $data['year'],
                    'processed_by' => auth()->id(),
                    'status'       => 'draft',
                ]));
                $generated++;
            } catch (\Throwable $e) {
                $skipped++;
            }
        }

        return back()->with('status', "Bulk payroll: {$generated} slips generated, {$skipped} skipped.");
    }

    public function approve(int $id)
    {
        SalarySlip::findOrFail($id)->update(['status' => 'approved']);
        return back()->with('status', 'Salary slip approved.');
    }

    public function markPaid(int $id)
    {
        SalarySlip::findOrFail($id)->update([
            'status'       => 'paid',
            'payment_date' => today()->toDateString(),
        ]);
        return back()->with('status', 'Salary slip marked as paid. Ledger entry auto-posted.');
    }

    public function destroy(int $id)
    {
        SalarySlip::findOrFail($id)->delete();
        return back()->with('status', 'Salary slip deleted.');
    }

    public function pdf(int $id)
    {
        $slip = SalarySlip::with('staff', 'processor')->findOrFail($id);
        $pdf  = Pdf::loadView('finance.salary-slip-pdf', compact('slip'))
                   ->setPaper('A4')
                   ->setOptions(['dpi' => 120, 'defaultFont' => 'sans-serif']);
        return $pdf->stream("salary-slip-{$slip->staff_id}-{$slip->month}-{$slip->year}.pdf");
    }

    // ── Salary Components CRUD ────────────────────────────────────────────────

    public function components()
    {
        $components = SalaryComponent::orderBy('type')->orderBy('sort_order')->get();
        return view('finance.salary-components', compact('components'));
    }

    public function storeComponent(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'code'          => 'required|string|max:20|unique:salary_components,code',
            'type'          => 'required|in:earning,deduction',
            'calculation'   => 'required|in:fixed,percentage,attendance,formula',
            'default_value' => 'required|numeric|min:0',
            'formula'       => 'nullable|string|max:200',
            'is_taxable'    => 'boolean',
            'sort_order'    => 'nullable|integer|min:0',
        ]);
        SalaryComponent::create(array_merge($data, ['is_active' => true, 'is_system' => false]));
        return back()->with('status', 'Salary component added.');
    }

    public function updateComponent(Request $request, int $id)
    {
        $component = SalaryComponent::findOrFail($id);
        if ($component->is_system) {
            return back()->with('error', 'System components cannot be edited.');
        }
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'calculation'   => 'required|in:fixed,percentage,attendance,formula',
            'default_value' => 'required|numeric|min:0',
            'formula'       => 'nullable|string|max:200',
            'is_taxable'    => 'boolean',
            'is_active'     => 'boolean',
            'sort_order'    => 'nullable|integer|min:0',
        ]);
        $component->update(array_merge($data, [
            'is_taxable' => $request->boolean('is_taxable'),
            'is_active'  => $request->boolean('is_active'),
        ]));
        return back()->with('status', 'Component updated.');
    }

    public function destroyComponent(int $id)
    {
        $component = SalaryComponent::findOrFail($id);
        if ($component->is_system) {
            return back()->with('error', 'System components cannot be deleted.');
        }
        $component->delete();
        return back()->with('status', 'Component deleted.');
    }

    // ── Staff salary structure (per-staff component amounts) ─────────────────

    public function staffStructure(int $staffId)
    {
        $staff      = User::findOrFail($staffId);
        $components = SalaryComponent::active()->orderBy('sort_order')->get();
        $structures = StaffSalaryStructure::with('component')
            ->where('user_id', $staffId)->current()->get()->keyBy('component_id');
        return view('finance.staff-salary-structure', compact('staff', 'components', 'structures'));
    }

    public function saveStaffStructure(Request $request, int $staffId)
    {
        $data = $request->validate([
            'components'               => 'required|array',
            'components.*.component_id'=> 'required|exists:salary_components,id',
            'components.*.amount'      => 'required|numeric|min:0',
            'components.*.effective_from' => 'nullable|date',
        ]);

        $user = User::findOrFail($staffId);

        foreach ($data['components'] as $row) {
            StaffSalaryStructure::updateOrCreate(
                [
                    'user_id'      => $staffId,
                    'component_id' => $row['component_id'],
                ],
                [
                    'amount'         => $row['amount'],
                    'effective_from' => $row['effective_from'] ?? today(),
                    'effective_to'   => null,
                ]
            );
        }

        return back()->with('status', 'Salary structure saved for ' . $user->full_name . '.');
    }

    // ── Tax Rate Management ───────────────────────────────────────────────────

    public function taxRates()
    {
        $rates = TaxRate::orderBy('type')->orderBy('min_salary')->get();
        return view('finance.tax-rates', compact('rates'));
    }

    public function storeTaxRate(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'code'           => 'nullable|string|max:30|unique:tax_rates,code',
            'type'           => 'required|in:income_tax,vat,provident_fund,other',
            'rate'           => 'required|numeric|min:0|max:100',
            'min_salary'     => 'nullable|numeric|min:0',
            'max_salary'     => 'nullable|numeric|gt:min_salary',
            'effective_from' => 'nullable|date',
            'effective_to'   => 'nullable|date|after_or_equal:effective_from',
            'applies_to'     => 'nullable|in:teacher,staff,all',
            'description'    => 'nullable|string|max:500',
        ]);
        TaxRate::create(array_merge($data, ['is_active' => true]));
        return back()->with('status', 'Tax rate added.');
    }

    public function updateTaxRate(Request $request, int $id)
    {
        TaxRate::findOrFail($id)->update($request->validate([
            'name'           => 'required|string|max:100',
            'rate'           => 'required|numeric|min:0|max:100',
            'min_salary'     => 'nullable|numeric|min:0',
            'max_salary'     => 'nullable|numeric',
            'effective_from' => 'nullable|date',
            'effective_to'   => 'nullable|date',
            'applies_to'     => 'nullable|in:teacher,staff,all',
            'is_active'      => 'boolean',
        ]) + ['is_active' => $request->boolean('is_active')]);
        return back()->with('status', 'Tax rate updated.');
    }

    public function destroyTaxRate(int $id)
    {
        TaxRate::findOrFail($id)->delete();
        return back()->with('status', 'Tax rate deleted.');
    }
}
