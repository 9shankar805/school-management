<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PayrollResource;
use App\Models\SalarySlip;
use App\Models\TaxRate;
use App\Models\TeacherPayroll;
use Illuminate\Http\Request;

/**
 * Payroll REST API — salary slips, teacher payrolls, tax rates.
 * Used by the mobile app (staff can view their own slips).
 */
class PayrollApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // ── Teacher payrolls ──────────────────────────────────────────────────────

    public function teacherIndex(Request $request)
    {
        $user  = $request->user();
        $query = TeacherPayroll::with('teacher')
            ->orderByDesc('year')->orderByDesc('month');

        // Teachers see only their own payrolls
        if ($user->hasAnyRole(['teacher', 'class-teacher']) && ! $user->can('view teachers')) {
            $query->where('teacher_id', $user->id);
        }

        if ($month = $request->input('month')) {
            $query->where('month', $month);
        }
        if ($year = $request->input('year')) {
            $query->where('year', $year);
        }
        if ($teacherId = $request->input('teacher_id')) {
            if ($user->can('view teachers')) {
                $query->where('teacher_id', $teacherId);
            }
        }

        return PayrollResource::collection($query->paginate($request->input('per_page', 12)));
    }

    public function teacherShow(int $id)
    {
        $user    = request()->user();
        $payroll = TeacherPayroll::with('teacher')->findOrFail($id);

        if ($user->hasAnyRole(['teacher', 'class-teacher'])
            && ! $user->can('view teachers')
            && $payroll->teacher_id !== $user->id) {
            abort(403, 'You can only view your own payroll.');
        }

        return new PayrollResource($payroll);
    }

    // ── Staff salary slips ────────────────────────────────────────────────────

    public function staffIndex(Request $request)
    {
        $user  = $request->user();
        $query = SalarySlip::with('staff')
            ->orderByDesc('year')->orderByDesc('month');

        // Staff members see only their own slips
        if (! $user->can('manage finance')) {
            $query->where('staff_id', $user->id);
        }

        if ($month = $request->input('month')) {
            $query->where('month', $month);
        }
        if ($year = $request->input('year')) {
            $query->where('year', $year);
        }

        return PayrollResource::collection($query->paginate($request->input('per_page', 12)));
    }

    public function staffShow(int $id)
    {
        $user = request()->user();
        $slip = SalarySlip::with('staff')->findOrFail($id);

        if (! $user->can('manage finance') && $slip->staff_id !== $user->id) {
            abort(403, 'You can only view your own salary slip.');
        }

        return new PayrollResource($slip);
    }

    // ── Tax rates (admin) ─────────────────────────────────────────────────────

    public function taxRates()
    {
        $this->authorize('manage finance');

        $rates = TaxRate::where('is_active', true)
            ->orderBy('type')
            ->orderBy('min_salary')
            ->get()
            ->map(fn($r) => [
                'id'             => $r->id,
                'name'           => $r->name,
                'type'           => $r->type,
                'rate'           => (float) $r->rate,
                'min_salary'     => $r->min_salary ? (float) $r->min_salary : null,
                'max_salary'     => $r->max_salary ? (float) $r->max_salary : null,
                'applies_to'     => $r->applies_to,
                'effective_from' => $r->effective_from?->format('Y-m-d'),
                'effective_to'   => $r->effective_to?->format('Y-m-d'),
            ]);

        return response()->json(['data' => $rates]);
    }
}
