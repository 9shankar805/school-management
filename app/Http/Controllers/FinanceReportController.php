<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\IncomeRecord;
use App\Models\Invoice;
use App\Models\Payment;
use App\Exports\FinanceReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FinanceReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage finance');
    }

    public function form()
    {
        return view('finance.reports');
    }

    public function export(Request $request)
    {
        $data = $request->validate([
            'report_type' => 'required|in:fee_collection,expenses,income,outstanding,ledger',
            'format'      => 'required|in:pdf,excel',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ]);

        $type     = $data['report_type'];
        $format   = $data['format'];
        $dateFrom = $data['date_from'] ?? null;
        $dateTo   = $data['date_to']   ?? null;

        $user = auth()->user();
        $schoolId = $user->school_id;
        $school = \App\Models\School::find($schoolId)
            ?? $user->school
            ?? config('tenant.school')
            ?? \App\Models\School::first();

        $records = match ($type) {
            'fee_collection' => Payment::with(['invoice.student'])
                ->when($schoolId, fn($q) => $q->where(function($sq) use ($schoolId) {
                    $sq->where('school_id', $schoolId)
                       ->orWhereHas('invoice', fn($iq) => $iq->where('school_id', $schoolId));
                }))
                ->when($dateFrom, fn($q) => $q->where('payment_date', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->where('payment_date', '<=', $dateTo))
                ->latest('payment_date')->get(),

            'expenses' => Expense::with('recordedBy')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->when($dateFrom, fn($q) => $q->where('expense_date', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->where('expense_date', '<=', $dateTo))
                ->latest('expense_date')->get(),

            'income' => IncomeRecord::with('recordedBy')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->when($dateFrom, fn($q) => $q->where('income_date', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->where('income_date', '<=', $dateTo))
                ->latest('income_date')->get(),

            'outstanding' => Invoice::with('student')
                ->where('status', 'unpaid')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->when($dateTo, fn($q) => $q->where('due_date', '<=', $dateTo))
                ->orderBy('due_date')->get(),

            'ledger' => collect([
                'income'   => IncomeRecord::when($schoolId, fn($q) => $q->where('school_id', $schoolId))->when($dateFrom, fn($q) => $q->where('income_date', '>=', $dateFrom))->when($dateTo, fn($q) => $q->where('income_date', '<=', $dateTo))->sum('amount'),
                'fees'     => Payment::when($schoolId, fn($q) => $q->where(function($sq) use ($schoolId) {
                                  $sq->where('school_id', $schoolId)->orWhereHas('invoice', fn($iq) => $iq->where('school_id', $schoolId));
                              }))->when($dateFrom, fn($q) => $q->where('payment_date', '>=', $dateFrom))->when($dateTo, fn($q) => $q->where('payment_date', '<=', $dateTo))->sum('amount'),
                'expenses' => Expense::when($schoolId, fn($q) => $q->where('school_id', $schoolId))->when($dateFrom, fn($q) => $q->where('expense_date', '>=', $dateFrom))->when($dateTo, fn($q) => $q->where('expense_date', '<=', $dateTo))->sum('amount'),
            ]),
        };

        $title = ucwords(str_replace('_', ' ', $type)) . ' Report';

        if ($format === 'pdf') {
            $logoSrc = $this->getSchoolLogoBase64($school);
            $pdf = Pdf::loadView('finance.reports-pdf', compact('records', 'type', 'title', 'dateFrom', 'dateTo', 'school', 'logoSrc'))
                      ->setPaper('a4', 'landscape');
            return $pdf->download("finance-{$type}-report.pdf");
        }

        return Excel::download(new FinanceReportExport($records, $type, $school), "finance-{$type}-report.xlsx");
    }

    /**
     * Get Base64 encoded logo for DomPDF rendering.
     */
    public function getSchoolLogoBase64($school = null): string
    {
        $school = $school ?? \App\Models\School::find(optional(auth()->user())->school_id) ?? optional(auth()->user())->school ?? config('tenant.school') ?? \App\Models\School::first();
        if ($school && $school->logo) {
            $clean = ltrim(str_replace('storage/', '', $school->logo), '/');
            $candidates = [
                storage_path('app/public/' . $clean),
                public_path('storage/' . $clean),
                public_path($clean),
            ];
            foreach ($candidates as $filePath) {
                if (file_exists($filePath)) {
                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    if ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
                        $im = @imagecreatefromwebp($filePath);
                        if ($im) {
                            ob_start();
                            imagepng($im);
                            $pngData = ob_get_clean();
                            imagedestroy($im);
                            if ($pngData) {
                                return 'data:image/png;base64,' . base64_encode($pngData);
                            }
                        }
                    }
                    $data = @file_get_contents($filePath);
                    if ($data) {
                        $mime = ($ext === 'png') ? 'image/png' : (($ext === 'svg') ? 'image/svg+xml' : 'image/jpeg');
                        return 'data:' . $mime . ';base64,' . base64_encode($data);
                    }
                }
            }
        }
        return '';
    }

    // PDF & Print receipt for a single payment (Standard A5, Dual-Copy A4, 80mm POS Thermal)
    public function receipt(Request $request, int $paymentId)
    {
        $payment = Payment::with([
            'invoice.student.parent_info',
            'invoice.schoolClass',
            'invoice.section',
            'invoice.session',
            'invoice.items.feeType',
            'recordedBy',
            'bankAccount',
        ])->findOrFail($paymentId);

        $school = \App\Models\School::find(
            $payment->invoice?->school_id
            ?? optional(auth()->user())->school_id
            ?? optional($payment->invoice?->student)->school_id
        ) ?? optional(auth()->user())->school ?? config('tenant.school') ?? \App\Models\School::first();

        $logoSrc = $this->getSchoolLogoBase64($school);
        $format = $request->query('format', 'standard');

        if ($format === 'dual') {
            $pdf = Pdf::loadView('finance.receipt-dual-copy', compact('payment', 'school', 'logoSrc'))
                      ->setPaper('a4', 'portrait');
            return $pdf->stream("receipt-dual-{$payment->id}.pdf");
        }

        if ($format === 'thermal') {
            return view('finance.receipt-thermal', compact('payment', 'school', 'logoSrc'));
        }

        $pdf = Pdf::loadView('finance.receipt-pdf', compact('payment', 'school', 'logoSrc'))
                  ->setPaper('a5');
        return $pdf->stream("receipt-{$payment->id}.pdf");
    }
}
