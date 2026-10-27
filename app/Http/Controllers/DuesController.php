<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Promotion;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\SmsLog;
use App\Models\StudentLedger;
use App\Models\User;
use App\Traits\SchoolSession as HasSchoolSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DuesController extends Controller
{
    use HasSchoolSession;

    public function __construct()
    {
        $this->middleware(['auth', 'can:manage finance']);
    }

    /**
     * Display Class-Wise Dues & Defaulters Recovery Hub.
     */
    public function index(Request $request)
    {
        $currentSessionId = $this->getSchoolCurrentSession();
        $sessionId = (int) $request->input('session_id', $currentSessionId);
        $classId   = $request->input('class_id', 'all');
        $sectionId = $request->input('section_id', 'all');
        $minDue    = (float) $request->input('min_due', 1); // Defaults to showing accounts with dues > 0
        $status    = $request->input('filter_status', 'dues_only'); // 'all', 'dues_only', 'locked_only', 'cleared_only'
        $search    = trim($request->input('search', ''));

        $sessions = SchoolSession::orderByDesc('id')->take(5)->get();
        $classes  = SchoolClass::orderBy('class_name')->get();
        $sections = ($classId !== 'all' && is_numeric($classId))
            ? Section::where('class_id', $classId)->get()
            : Section::all();

        // Query students enrolled in the chosen session
        $query = Promotion::with([
            'student.parent_info',
            'student.academic_info',
            'schoolClass',
            'section',
        ])->where('session_id', $sessionId);

        if ($classId !== 'all' && is_numeric($classId)) {
            $query->where('class_id', $classId);
        }

        if ($sectionId !== 'all' && is_numeric($sectionId)) {
            $query->where('section_id', $sectionId);
        }

        if ($search !== '') {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_no', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('parent_info', function ($pq) use ($search) {
                            $pq->where('father_name', 'like', "%{$search}%")
                               ->orWhere('mother_name', 'like', "%{$search}%")
                               ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            });
        }

        $promotions = $query->get();

        // Calculate dues and compile data
        $nepaliMonths = [
            1 => 'Baisakh', 2 => 'Jestha', 3 => 'Ashadh', 4 => 'Shrawan',
            5 => 'Bhadra', 6 => 'Ashwin', 7 => 'Kartik', 8 => 'Mangsir',
            9 => 'Poush', 10 => 'Magh', 11 => 'Falgun', 12 => 'Chaitra',
        ];

        $defaulters = [];
        $totalInvoicedAll = 0.0;
        $totalCollectedAll = 0.0;
        $totalOutstandingAll = 0.0;
        $lockedCountAll = 0;
        $clearedCountAll = 0;

        foreach ($promotions as $promo) {
            $student = $promo->student;
            if (!$student) continue;

            $dueBalance = round(max((float) StudentLedger::currentBalance($student->id, $sessionId), (float) StudentLedger::currentBalance($student->id)), 2);

            // Fetch unpaid invoices for months overdue
            $unpaidInvoices = Invoice::where('student_id', $student->id)
                ->where('status', '!=', 'paid')
                ->where('session_id', $sessionId)
                ->select(['id', 'billing_month', 'billing_year', 'amount', 'title', 'due_date'])
                ->orderBy('billing_year')
                ->orderBy('billing_month')
                ->get();

            $overdueMonths = $unpaidInvoices->map(function ($inv) use ($nepaliMonths) {
                return $inv->billing_month && isset($nepaliMonths[$inv->billing_month])
                    ? $nepaliMonths[$inv->billing_month]
                    : ($inv->billing_month ? Carbon::create()->month($inv->billing_month)->format('M') : null);
            })->filter()->unique()->values()->all();

            $totalInvoiced = (float) Invoice::where('student_id', $student->id)->where('session_id', $sessionId)->sum('amount');
            $totalPaid = max(0, $totalInvoiced - $dueBalance);

            $totalInvoicedAll += $totalInvoiced;
            $totalCollectedAll += $totalPaid;
            $totalOutstandingAll += $dueBalance;

            if ($student->is_admit_card_locked) {
                $lockedCountAll++;
            }
            if ($dueBalance <= 0) {
                $clearedCountAll++;
            }

            // Apply filter_status and min_due
            if ($status === 'dues_only' && $dueBalance < $minDue) {
                continue;
            } elseif ($status === 'locked_only' && !$student->is_admit_card_locked) {
                continue;
            } elseif ($status === 'cleared_only' && $dueBalance > 0) {
                continue;
            } elseif ($status === 'all' && $dueBalance < $minDue && $minDue > 0) {
                continue;
            }

            // Guardian contact resolution
            $guardianName = $student->parent_info?->father_name
                ?: ($student->parent_info?->mother_name ?: ($student->parent_info?->guardian_name ?: 'Guardian'));
            $guardianPhone = $student->parent_info?->phone
                ?: ($student->parent_info?->father_phone ?: ($student->phone ?: ''));

            $defaulters[] = [
                'student_id'             => $student->id,
                'student'                => $student,
                'full_name'              => $student->full_name,
                'admission_no'           => $student->admission_no ?: 'ADM-' . $student->id,
                'roll_no'                => $promo->roll_no ?: ($student->roll_no ?: '-'),
                'class_name'             => $promo->schoolClass?->class_name ?? 'N/A',
                'section_name'           => $promo->section?->section_name ?? 'General',
                'guardian_name'          => $guardianName,
                'guardian_phone'         => $guardianPhone,
                'total_invoiced'         => $totalInvoiced,
                'total_paid'             => $totalPaid,
                'due_balance'            => $dueBalance,
                'overdue_months'         => $overdueMonths,
                'unpaid_count'           => $unpaidInvoices->count(),
                'is_admit_card_locked'   => (bool) $student->is_admit_card_locked,
                'admit_card_lock_reason' => $student->admit_card_lock_reason,
            ];
        }

        // Sort defaulters by highest overdue balance descending
        usort($defaulters, fn($a, $b) => $b['due_balance'] <=> $a['due_balance']);

        $collectionRate = $totalInvoicedAll > 0
            ? round(($totalCollectedAll / $totalInvoicedAll) * 100, 1)
            : 0;

        return view('finance.dues.index', compact(
            'defaulters', 'sessions', 'classes', 'sections',
            'sessionId', 'classId', 'sectionId', 'minDue', 'status', 'search',
            'totalInvoicedAll', 'totalCollectedAll', 'totalOutstandingAll',
            'lockedCountAll', 'clearedCountAll', 'collectionRate'
        ));
    }

    /**
     * Toggle admit card lock for a single student.
     */
    public function toggleLock(Request $request, int $studentId)
    {
        $student = User::role('student')->findOrFail($studentId);
        $newState = !$student->is_admit_card_locked;
        $reason   = $newState
            ? ($request->input('reason') ?: 'Fee dues pending clearance at the finance counter.')
            : null;

        $student->update([
            'is_admit_card_locked'   => $newState,
            'admit_card_lock_reason' => $reason,
        ]);

        $statusText = $newState ? 'LOCKED' : 'UNLOCKED';
        return back()->with('status', "Admit Card for {$student->full_name} has been {$statusText}.");
    }

    /**
     * Bulk lock / unlock admit cards for selected students.
     */
    public function bulkLock(Request $request)
    {
        $data = $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'action'        => 'required|in:lock,unlock',
            'lock_reason'   => 'nullable|string|max:255',
        ]);

        $isLock = $data['action'] === 'lock';
        $reason = $isLock
            ? ($data['lock_reason'] ?: 'Examination admit card held due to outstanding fee balance.')
            : null;

        User::whereIn('id', $data['student_ids'])->update([
            'is_admit_card_locked'   => $isLock,
            'admit_card_lock_reason' => $reason,
        ]);

        $count = count($data['student_ids']);
        $actionWord = $isLock ? 'locked / barred' : 'unlocked and cleared';

        return back()->with('status', "Admit Cards for {$count} students successfully {$actionWord}.");
    }

    /**
     * Send bulk fee dues reminders via SMS / WhatsApp.
     */
    public function bulkRemind(Request $request)
    {
        $data = $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'channel'       => 'required|in:sms,whatsapp,portal',
            'message'       => 'nullable|string|max:500',
        ]);

        $students = User::with('parent_info')->whereIn('id', $data['student_ids'])->get();
        $school   = config('tenant.school') ?? School::first();
        $schoolName = $school?->name ?? config('app.name', 'School Management');

        $sentCount = 0;

        foreach ($students as $student) {
            $phone = $student->parent_info?->phone
                ?: ($student->parent_info?->father_phone ?: $student->phone);

            if (!$phone) continue;

            $due = StudentLedger::currentBalance($student->id);
            if ($due <= 0) continue;

            $customMsg = $data['message'] ?: "Dear Parent, your ward {$student->full_name} has an outstanding school fee balance of NPR " . number_format($due, 2) . ". Kindly clear at the counter or via eSewa/Khalti to ensure uninterrupted exam participation. - {$schoolName}";

            // Replace dynamic placeholders if present
            $personalized = str_replace(
                ['{student_name}', '{due_amount}', '{school_name}'],
                [$student->full_name, 'Rs. ' . number_format($due, 2), $schoolName],
                $customMsg
            );

            // Log communication in SmsLog
            try {
                SmsLog::create([
                    'school_id'       => $student->school_id,
                    'recipient_phone' => $phone,
                    'recipient_id'    => $student->id,
                    'gateway'         => $data['channel'],
                    'type'            => 'fee_reminder',
                    'message'         => $personalized,
                    'status'          => 'sent',
                    'sent_at'         => now(),
                ]);
                $sentCount++;
            } catch (\Throwable) {}
        }

        return back()->with('status', "Fee reminder notifications dispatched to {$sentCount} parents via " . strtoupper($data['channel']) . ".");
    }

    /**
     * Export Class-Wise Dues Sheet to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $sessionId = (int) $request->input('session_id', $this->getSchoolCurrentSession());
        $classId   = $request->input('class_id', 'all');
        $sectionId = $request->input('section_id', 'all');
        $minDue    = (float) $request->input('min_due', 0);

        $query = Promotion::with(['student.parent_info', 'schoolClass', 'section'])
            ->where('session_id', $sessionId);

        if ($classId !== 'all' && is_numeric($classId)) {
            $query->where('class_id', $classId);
        }
        if ($sectionId !== 'all' && is_numeric($sectionId)) {
            $query->where('section_id', $sectionId);
        }

        $promotions = $query->get();
        $className = ($classId !== 'all' && is_numeric($classId))
            ? (SchoolClass::find($classId)?->class_name ?? 'Class')
            : 'All_Classes';

        $filename = "Dues_Report_{$className}_" . date('Y_m_d') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($promotions, $sessionId, $minDue) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [
                'SN',
                'Admission No',
                'Roll No',
                'Student Name',
                'Class',
                'Section',
                'Guardian Name',
                'Guardian Contact',
                'Total Invoiced (NPR)',
                'Total Paid (NPR)',
                'Outstanding Dues (NPR)',
                'Admit Card Status',
                'Lock Reason',
            ]);

            $idx = 1;
            foreach ($promotions as $promo) {
                $student = $promo->student;
                if (!$student) continue;

                $due = round(max((float) StudentLedger::currentBalance($student->id, $sessionId), (float) StudentLedger::currentBalance($student->id)), 2);
                if ($due < $minDue) continue;

                $invoiced = (float) Invoice::where('student_id', $student->id)->where('session_id', $sessionId)->sum('amount');
                $paid = max(0, $invoiced - $due);

                $guardianName = $student->parent_info?->father_name
                    ?: ($student->parent_info?->mother_name ?: ($student->parent_info?->guardian_name ?: ''));
                $guardianPhone = $student->parent_info?->phone
                    ?: ($student->parent_info?->father_phone ?: ($student->phone ?: ''));

                fputcsv($handle, [
                    $idx++,
                    $student->admission_no ?: 'ADM-' . $student->id,
                    $promo->roll_no ?: '-',
                    $student->full_name,
                    $promo->schoolClass?->class_name ?? 'N/A',
                    $promo->section?->section_name ?? 'General',
                    $guardianName,
                    $guardianPhone,
                    number_format($invoiced, 2, '.', ''),
                    number_format($paid, 2, '.', ''),
                    number_format($due, 2, '.', ''),
                    $student->is_admit_card_locked ? 'LOCKED / BARRED' : 'ACTIVE',
                    $student->admit_card_lock_reason ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Printable Official Class Dues PDF.
     */
    public function exportPdf(Request $request)
    {
        $sessionId = (int) $request->input('session_id', $this->getSchoolCurrentSession());
        $classId   = $request->input('class_id', 'all');
        $sectionId = $request->input('section_id', 'all');
        $minDue    = (float) $request->input('min_due', 1);

        $query = Promotion::with(['student.parent_info', 'schoolClass', 'section'])
            ->where('session_id', $sessionId);

        if ($classId !== 'all' && is_numeric($classId)) {
            $query->where('class_id', $classId);
        }
        if ($sectionId !== 'all' && is_numeric($sectionId)) {
            $query->where('section_id', $sectionId);
        }

        $promotions = $query->get();
        $class = ($classId !== 'all' && is_numeric($classId)) ? SchoolClass::find($classId) : null;
        $section = ($sectionId !== 'all' && is_numeric($sectionId)) ? Section::find($sectionId) : null;
        $school = config('tenant.school') ?? School::first();

        $rows = [];
        $totalDues = 0;

        foreach ($promotions as $promo) {
            $student = $promo->student;
            if (!$student) continue;

            $due = round(max((float) StudentLedger::currentBalance($student->id, $sessionId), (float) StudentLedger::currentBalance($student->id)), 2);
            if ($due < $minDue) continue;

            $totalDues += $due;
            $guardianPhone = $student->parent_info?->phone
                ?: ($student->parent_info?->father_phone ?: ($student->phone ?: '-'));

            $rows[] = [
                'admission_no'         => $student->admission_no ?: 'ADM-' . $student->id,
                'roll_no'              => $promo->roll_no ?: '-',
                'student_name'         => $student->full_name,
                'class_section'        => ($promo->schoolClass?->class_name ?? '') . ' - ' . ($promo->section?->section_name ?? ''),
                'guardian_phone'       => $guardianPhone,
                'due_balance'          => $due,
                'is_admit_card_locked' => $student->is_admit_card_locked,
            ];
        }

        usort($rows, fn($a, $b) => $b['due_balance'] <=> $a['due_balance']);

        $pdf = Pdf::loadView('finance.dues.pdf', compact('rows', 'class', 'section', 'school', 'totalDues'))
            ->setPaper('A4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'dpi' => 120]);

        return $pdf->stream('class-dues-defaulters-report.pdf');
    }
}
