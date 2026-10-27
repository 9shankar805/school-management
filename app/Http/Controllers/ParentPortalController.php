<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignedTeacher;
use App\Models\Attendance;
use App\Models\Conversation;
use App\Models\Exam;
use App\Models\FinalMark;
use App\Models\Invoice;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Mark;
use App\Models\ParentTeacherMessage;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Repositories\NoticeRepository;
use App\Traits\SchoolSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\MedicalRecord;
use App\Models\DisciplinaryRecord;
use App\Models\StudentDocument;
use App\Models\Event;

class ParentPortalController extends Controller
{
    use SchoolSession;

    public function __construct()
    {
        $this->middleware('auth');
        // Parent-only routes are guarded individually via resolveChild() + 403
        // Admin-facing methods (linkStudent, unlinkStudent, adminLinkPage) are open to any auth'd admin
    }

    // -------------------------------------------------------------------------
    // Helper: resolve child and enforce ownership
    // -------------------------------------------------------------------------
    private function resolveChild(int $studentId): User
    {
        $parent = auth()->user();
        $child  = $parent->children()->where('users.id', $studentId)->first();

        if (! $child) {
            abort(403, 'You are not authorised to view data for this student.');
        }

        return $child;
    }

    // -------------------------------------------------------------------------
    // Attendance
    // -------------------------------------------------------------------------
    public function attendance(Request $request, int $studentId)
    {
        $child     = $this->resolveChild($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year', now()->year);

        // All attendance for this session
        $all = Attendance::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->get();

        $total   = $all->count();
        $present = $all->filter(fn($a) => in_array($a->status, ['on', 'present', '1']))->count();
        $absent  = $all->filter(fn($a) => in_array($a->status, ['off', 'absent', '0']))->count();
        $pct     = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        $deficit = $total > 0 ? max(0, (int) ceil(($total * 0.75 - $present) / (1 - 0.75))) : 0;

        // Monthly records for calendar
        $monthly = Attendance::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy(fn($a) => \Carbon\Carbon::parse($a->date)->day);

        // Per-course breakdown
        $courseBreakdown = Attendance::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->with('course')
            ->get()
            ->groupBy('course_id')
            ->map(function ($records) {
                $t = $records->count();
                $p = $records->filter(fn($a) => in_array($a->status, ['on', 'present', '1']))->count();
                return (object) [
                    'course'   => $records->first()->course,
                    'total'    => $t,
                    'present'  => $p,
                    'absent'   => $t - $p,
                    'pct'      => $t > 0 ? round($p / $t * 100, 1) : 0,
                ];
            })->values();

        // 12-week trend (ApexCharts)
        $trendRaw = Attendance::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->where('date', '>=', now()->subWeeks(12)->toDateString())
            ->select(
                DB::raw('YEARWEEK(date, 1) as yw'),
                DB::raw("SUM(CASE WHEN status IN ('on','present','1') THEN 1 ELSE 0 END) as present"),
                DB::raw("SUM(CASE WHEN status IN ('off','absent','0') THEN 1 ELSE 0 END) as absent")
            )
            ->groupBy('yw')
            ->orderBy('yw')
            ->get();

        $trendWeeks   = $trendRaw->pluck('yw');
        $trendPresent = $trendRaw->pluck('present');
        $trendAbsent  = $trendRaw->pluck('absent');

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        return view('parent.attendance', compact(
            'child', 'month', 'year', 'daysInMonth',
            'total', 'present', 'absent', 'pct', 'deficit',
            'monthly', 'courseBreakdown',
            'trendWeeks', 'trendPresent', 'trendAbsent', 'sessionId'
        ));
    }

    // -------------------------------------------------------------------------
    // Results / Marks
    // -------------------------------------------------------------------------
    public function results(Request $request, int $studentId)
    {
        $child     = $this->resolveChild($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        $examId   = $request->query('exam_id');
        $courseId = $request->query('course_id');

        $marksQuery = Mark::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->with(['exam', 'course']);

        if ($examId)   { $marksQuery->where('exam_id', $examId); }
        if ($courseId) { $marksQuery->where('course_id', $courseId); }

        $marks  = $marksQuery->latest()->get();
        $exams  = Exam::where('session_id', $sessionId)->orderBy('start_date')->get();
        $courses = Mark::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->distinct()
            ->pluck('course_id');

        // Performance trend: avg marks per exam ordered by start_date
        $trend = Mark::where('marks.student_id', $studentId)
            ->where('marks.session_id', $sessionId)
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->select('exams.exam_name as exam_name', DB::raw('ROUND(AVG(marks.marks), 1) as avg'))
            ->groupBy('marks.exam_id', 'exams.exam_name', 'exams.start_date')
            ->orderBy('exams.start_date')
            ->get();

        // Course bar chart
        $courseTrend = Mark::where('marks.student_id', $studentId)
            ->where('marks.session_id', $sessionId)
            ->join('courses', 'marks.course_id', '=', 'courses.id')
            ->select('courses.course_name as course_name', DB::raw('ROUND(AVG(marks.marks), 1) as avg'))
            ->groupBy('marks.course_id', 'courses.course_name')
            ->get();

        return view('parent.results', compact(
            'child', 'marks', 'exams', 'trend', 'courseTrend', 'sessionId', 'examId', 'courseId'
        ));
    }

    // -------------------------------------------------------------------------
    // Fees
    // -------------------------------------------------------------------------
    public function fees(int $studentId)
    {
        $child    = $this->resolveChild($studentId);
        $invoices = Invoice::where('student_id', $studentId)
            ->with('payments')
            ->latest()
            ->get()
            ->map(function ($inv) {
                $paid      = $inv->payments->sum('amount');
                $remaining = max(0, $inv->amount - $paid);
                $display   = $inv->status;
                if ($inv->status === 'unpaid' && $inv->due_date && $inv->due_date < now()->toDateString()) {
                    $display = 'overdue';
                }
                $inv->amount_paid    = $paid;
                $inv->remaining      = $remaining;
                $inv->display_status = $display;
                return $inv;
            });

        $totalOutstanding = $invoices->whereIn('display_status', ['unpaid', 'overdue'])->sum('remaining');

        // Payment history (all payments across child's invoices)
        $invoiceIds = Invoice::where('student_id', $studentId)->pluck('id');
        $paymentHistory = Payment::whereIn('invoice_id', $invoiceIds)
            ->with('invoice')
            ->latest()
            ->get();

        return view('parent.fees', compact('child', 'invoices', 'totalOutstanding', 'paymentHistory'));
    }

    public function payInvoice(int $studentId, int $invoiceId)
    {
        $child   = $this->resolveChild($studentId);
        $invoice = Invoice::with('payments')->where('student_id', $studentId)->findOrFail($invoiceId);

        // Use invoice->amount_due which includes cumulative arrears and fines
        $remaining = $invoice->amount_due;

        return view('parent.pay-invoice', compact('child', 'invoice', 'remaining'));
    }

    public function processInvoicePayment(Request $request, int $studentId, int $invoiceId)
    {
        $child   = $this->resolveChild($studentId);
        $invoice = Invoice::where('student_id', $studentId)->findOrFail($invoiceId);

        $request->validate([
            'amount_paid'    => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,online,cheque,esewa,khalti,fonepay,bank',
        ]);

        $amountPaid = (float) $request->amount_paid;

        // Payment::booted() → created() automatically calls updateInvoiceStatus()
        // so we do NOT need to manually update invoice status here.
        $payment = Payment::create([
            'invoice_id'     => $invoice->id,
            'amount'         => $amountPaid,
            'payment_date'   => now()->toDateString(),
            'payment_method' => $request->payment_method,
            'gateway'        => $request->payment_method,
            'recorded_by'    => auth()->id(),
            'school_id'      => $invoice->school_id ?? auth()->user()->school_id,
        ]);

        // Notify parent with correct amount from the saved Payment record
        try {
            auth()->user()->notify(new GeneralNotification(
                title:      '✅ Payment Received',
                message:    "NPR " . number_format($payment->amount, 2) . " payment recorded for invoice: \"{$invoice->title}\". Receipt: {$payment->receipt_number}.",
                type:       'fee',
                actionUrl:  route('parent.fees', $studentId),
                actionText: 'View Fees',
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Parent payment notification failed: ' . $e->getMessage());
        }

        return redirect()->route('parent.fees', $studentId)
            ->with('status', '✅ Payment of NPR ' . number_format($amountPaid, 2) . ' recorded. Receipt: ' . $payment->receipt_number);
    }

    // -------------------------------------------------------------------------
    // Assignments
    // -------------------------------------------------------------------------
    public function assignments(int $studentId)
    {
        $child     = $this->resolveChild($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        // Get child's current class/section from promotions
        $promotion = Promotion::where('student_id', $studentId)->latest()->first();

        $assignments = collect();
        if ($promotion) {
            $assignments = Assignment::where('class_id', $promotion->class_id)
                ->where('section_id', $promotion->section_id)
                ->where('session_id', $sessionId)
                ->with(['course', 'teacher'])
                ->latest()
                ->get();
        }

        return view('parent.assignments', compact('child', 'assignments', 'promotion'));
    }

    // -------------------------------------------------------------------------
    // Leave Application
    // -------------------------------------------------------------------------
    public function leave(int $studentId)
    {
        $child        = $this->resolveChild($studentId);
        $leaveTypes   = LeaveType::where('is_active', true)->get();
        $applications = LeaveApplication::where('user_id', $studentId)
            ->with('leaveType')
            ->latest()
            ->get();

        return view('parent.leave', compact('child', 'leaveTypes', 'applications'));
    }

    public function submitLeave(Request $request, int $studentId)
    {
        $child = $this->resolveChild($studentId);

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date'     => ['required', 'date', 'after_or_equal:' . now()->subDays(90)->toDateString()],
            'to_date'       => 'required|date|after_or_equal:from_date',
            'reason'        => 'required|string|min:10|max:1000',
        ]);

        $from  = Carbon::parse($request->from_date);
        $to    = Carbon::parse($request->to_date);
        $days  = $from->diffInWeekdays($to) + 1;

        $application = LeaveApplication::create([
            'user_id'       => $studentId,
            'submitted_by'  => auth()->id(),
            'leave_type_id' => $request->leave_type_id,
            'from_date'     => $request->from_date,
            'to_date'       => $request->to_date,
            'total_days'    => $days,
            'reason'        => $request->reason,
            'status'        => 'pending',
        ]);

        // Notify class-teacher(s) assigned to child's section
        $promotion = Promotion::where('student_id', $studentId)->latest()->first();
        if ($promotion) {
            $classTeachers = User::role('class-teacher')->get();
            foreach ($classTeachers as $teacher) {
                $teacher->notify(new GeneralNotification(
                    'Leave Request',
                    "{$child->full_name}'s parent has submitted a leave application from {$request->from_date} to {$request->to_date}."
                ));
            }
        }

        return redirect()->route('parent.leave', $studentId)->with('status', 'Leave application submitted.');
    }

    public function cancelLeave(Request $request, int $studentId, int $applicationId)
    {
        $this->resolveChild($studentId);

        $application = LeaveApplication::where('user_id', $studentId)
            ->where('id', $applicationId)
            ->where('status', 'pending')
            ->firstOrFail();

        $application->update(['status' => 'cancelled']);

        return back()->with('status', 'Leave application cancelled.');
    }

    // -------------------------------------------------------------------------
    // Homework tracking (parent view)
    // -------------------------------------------------------------------------
    public function homework(int $studentId)
    {
        $child     = $this->resolveChild($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        $promotion = Promotion::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->latest()->first();

        $homeworks = collect();
        if ($promotion) {
            $homeworks = \App\Models\Homework::where('class_id', $promotion->class_id)
                ->where('section_id', $promotion->section_id)
                ->where('session_id', $sessionId)
                ->with(['course', 'teacher'])
                ->withCount('submissions')
                ->latest()
                ->get()
                ->map(function ($hw) use ($studentId) {
                    // Attach this child's submission if any
                    $hw->mySubmission = $hw->submissions()
                        ->where('student_id', $studentId)
                        ->first();
                    // Attach parent diary sign-off if any
                    $hw->myAcknowledgement = \App\Models\HomeworkAcknowledgement::with('parent')
                        ->where('homework_id', $hw->id)
                        ->where('student_id', $studentId)
                        ->first();
                    return $hw;
                });
        }

        return view('parent.homework', compact('child', 'homeworks', 'promotion'));
    }

    /**
     * Parent Sign-off / Acknowledgement of Student Homework Diary (अभिभावक हस्ताक्षर).
     */
    public function acknowledgeHomework(Request $request, int $studentId, int $homeworkId)
    {
        $child = $this->resolveChild($studentId);
        $parentId = auth()->id();

        $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        \App\Models\HomeworkAcknowledgement::updateOrCreate(
            [
                'homework_id' => $homeworkId,
                'student_id'  => $studentId,
            ],
            [
                'parent_id'       => $parentId,
                'school_id'       => auth()->user()->school_id ?? 1,
                'acknowledged_at' => now(),
                'remarks'         => $request->input('remarks'),
            ]
        );

        return back()->with('status', 'गृहकार्य डायरी प्रमाणीकरण सम्पन्न भयो (Homework acknowledged successfully).');
    }

    /**
     * Parent View for Official Notices & Circulars (सूचना तथा परिपत्र).
     */
    public function notices(int $studentId)
    {
        $child     = $this->resolveChild($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        $notices = \App\Models\Notice::with('publisher')
            ->where('session_id', $sessionId)
            ->whereIn('audience', ['all', 'parents', 'students'])
            ->latest('id')
            ->paginate(12);

        return view('parent.notices', compact('child', 'notices'));
    }

    /**
     * Parent View: Child's Virtual & Online Classes (Zoom / Google Meet).
     */
    public function onlineClasses(int $studentId)
    {
        $child     = $this->resolveChild($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        $promotion = Promotion::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->latest()->first();

        $classes = collect();
        if ($promotion) {
            $classes = \App\Models\OnlineClass::with(['course', 'teacher'])
                ->where('session_id', $sessionId)
                ->where('class_id', $promotion->class_id)
                ->where(function($q) use ($promotion) {
                    $q->whereNull('section_id')->orWhere('section_id', $promotion->section_id);
                })
                ->orderByDesc('scheduled_at')
                ->paginate(12);
        }

        return view('parent.online-classes', compact('child', 'classes', 'promotion'));
    }

    // -------------------------------------------------------------------------
    // Timetable (parent view — child's class schedule)
    // -------------------------------------------------------------------------
    public function timetable(int $studentId)
    {
        $child     = $this->resolveChild($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        $promotion = Promotion::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->latest()->first();

        $slots = collect();
        if ($promotion) {
            $slots = \App\Models\Routine::where('session_id', $sessionId)
                ->where('class_id', $promotion->class_id)
                ->where('section_id', $promotion->section_id)
                ->with(['course', 'teacher'])
                ->orderBy('weekday')
                ->orderBy('start')
                ->get()
                ->groupBy('weekday');
        }

        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
                 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

        $todayWeekday = now()->isoWeekday();

        return view('parent.timetable', compact('child', 'slots', 'days', 'todayWeekday', 'promotion'));
    }

    // -------------------------------------------------------------------------
    // Transport info (parent view)
    // -------------------------------------------------------------------------
    public function transport(int $studentId)
    {
        $child = $this->resolveChild($studentId);

        $transport = \App\Models\StudentTransport::where('student_id', $studentId)
            ->with(['route.stops', 'route.vehicle', 'route.driver', 'stop'])
            ->latest()
            ->first();

        return view('parent.transport', compact('child', 'transport'));
    }

    // -------------------------------------------------------------------------
    // Messaging
    // -------------------------------------------------------------------------
    public function conversations()
    {
        $parent        = auth()->user();
        $conversations = Conversation::where('parent_id', $parent->id)
            ->with(['teacher', 'student', 'latestMessage'])
            ->withCount(['messages as unread_count' => function ($q) use ($parent) {
                $q->where('sender_id', '!=', $parent->id)->whereNull('read_at');
            }])
            ->orderByDesc('updated_at')
            ->get();

        $children = $parent->children()->get();

        return view('parent.conversations', compact('conversations', 'children'));
    }

    public function newConversation(Request $request)
    {
        $parent   = auth()->user();
        $children = $parent->children()->get();

        // Teachers: those assigned to any of the parent's children classes
        $childIds = $children->pluck('id');
        $promotions = Promotion::whereIn('student_id', $childIds)->get();
        $classIds   = $promotions->pluck('class_id')->unique();
        $sectionIds = $promotions->pluck('section_id')->unique();

        $teachers = \App\Models\AssignedTeacher::whereIn('class_id', $classIds)
            ->whereIn('section_id', $sectionIds)
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->unique('id')
            ->filter();

        return view('parent.new-conversation', compact('children', 'teachers'));
    }

    public function storeConversation(Request $request)
    {
        $parent = auth()->user();

        $request->validate([
            'student_id' => 'required|integer',
            'teacher_id' => 'required|exists:users,id',
            'subject'    => 'required|string|max:150',
            'body'       => 'required|string|min:1|max:2000',
        ]);

        // Validate child ownership
        $child = $this->resolveChild((int) $request->student_id);

        $conversation = Conversation::create([
            'parent_id'  => $parent->id,
            'teacher_id' => $request->teacher_id,
            'student_id' => $child->id,
            'subject'    => $request->subject,
        ]);

        ParentTeacherMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $parent->id,
            'body'            => $request->body,
        ]);

        // Notify teacher
        $teacher = User::find($request->teacher_id);
        if ($teacher) {
            $teacher->notify(new GeneralNotification(
                'New Message',
                "{$parent->full_name} started a conversation: {$request->subject}"
            ));
        }

        return redirect()->route('parent.conversation.show', $conversation->id)
            ->with('status', 'Conversation started.');
    }

    public function showConversation(int $conversationId)
    {
        $parent       = auth()->user();
        $conversation = Conversation::where('parent_id', $parent->id)
            ->with(['teacher', 'student', 'messages.sender'])
            ->findOrFail($conversationId);

        // Mark incoming messages as read
        ParentTeacherMessage::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $parent->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('parent.conversation-thread', compact('conversation'));
    }

    public function sendMessage(Request $request, int $conversationId)
    {
        $parent       = auth()->user();
        $conversation = Conversation::where('parent_id', $parent->id)->findOrFail($conversationId);

        $request->validate(['body' => 'required|string|min:1|max:2000']);

        ParentTeacherMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $parent->id,
            'body'            => $request->body,
        ]);

        $conversation->touch();

        // Notify teacher
        $teacher = $conversation->teacher;
        if ($teacher) {
            $teacher->notify(new GeneralNotification(
                'New Message',
                "{$parent->full_name} replied to: {$conversation->subject}"
            ));
        }

        return back()->with('status', 'Message sent.');
    }

    // -------------------------------------------------------------------------
    // Performance Trends
    // -------------------------------------------------------------------------
    public function performance(int $studentId)
    {
        $child     = $this->resolveChild($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        $markCount = Mark::where('student_id', $studentId)->where('session_id', $sessionId)->count();

        // Exam trend (line chart)
        $examTrend = Mark::where('marks.student_id', $studentId)
            ->where('marks.session_id', $sessionId)
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->select('exams.exam_name as exam_name', DB::raw('ROUND(AVG(marks.marks), 1) as avg'))
            ->groupBy('marks.exam_id', 'exams.exam_name', 'exams.start_date')
            ->orderBy('exams.start_date')
            ->get();

        // Course bar chart
        $courseTrend = Mark::where('marks.student_id', $studentId)
            ->where('marks.session_id', $sessionId)
            ->join('courses', 'marks.course_id', '=', 'courses.id')
            ->select('courses.course_name as course_name', DB::raw('ROUND(AVG(marks.marks), 1) as avg'))
            ->groupBy('marks.course_id', 'courses.course_name')
            ->get();

        // Multi-session comparison
        $sessions = Mark::where('student_id', $studentId)
            ->distinct()
            ->pluck('session_id');

        $multiSession = [];
        if ($sessions->count() > 1) {
            foreach ($sessions as $sid) {
                $multiSession[$sid] = Mark::where('marks.student_id', $studentId)
                    ->where('marks.session_id', $sid)
                    ->join('exams', 'marks.exam_id', '=', 'exams.id')
                    ->select('exams.exam_name as exam_name', DB::raw('ROUND(AVG(marks.marks), 1) as avg'))
                    ->groupBy('marks.exam_id', 'exams.exam_name', 'exams.start_date')
                    ->orderBy('exams.start_date')
                    ->get();
            }
        }

        return view('parent.performance', compact(
            'child', 'markCount', 'examTrend', 'courseTrend', 'multiSession', 'sessionId'
        ));
    }

    // -------------------------------------------------------------------------
    // Link management (admin-facing) — JSON endpoints for AJAX search
    // -------------------------------------------------------------------------
    public function searchStudentsForLink(Request $request)
    {
        $q       = $request->query('q', '');
        $results = User::role('student')
            ->where(function ($query) use ($q) {
                $query->where('first_name', 'like', "%{$q}%")
                      ->orWhere('last_name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->select('id', 'first_name', 'last_name', 'email')
            ->limit(15)
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'text' => $u->full_name . ' — ' . $u->email]);

        return response()->json($results);
    }

    public function linkStudent(Request $request, int $parentId)
    {
        $parent = User::findOrFail($parentId);
        abort_unless($parent->hasRole('parent'), 422, 'User is not a parent.');

        $request->validate([
            'student_id'   => 'required|exists:users,id',
            'relationship' => 'required|in:father,mother,guardian',
            'is_primary'   => 'boolean',
        ]);

        $student = User::findOrFail($request->student_id);
        abort_unless($student->hasRole('student'), 422, 'Target user is not a student.');

        $parent->children()->syncWithoutDetaching([
            $request->student_id => [
                'relationship' => $request->relationship,
                'is_primary'   => $request->boolean('is_primary', false),
            ],
        ]);

        return back()->with('status', 'Student linked to parent successfully.');
    }

    public function unlinkStudent(Request $request, int $parentId, int $studentId)
    {
        $parent = User::findOrFail($parentId);
        $parent->children()->detach($studentId);

        return back()->with('status', 'Link removed.');
    }

    public function adminLinkPage()
    {
        return view('parent.admin-link');
    }

    // -------------------------------------------------------------------------
    // Teacher reply (staff-facing — teacher replies from their inbox)
    // -------------------------------------------------------------------------
    public function teacherInbox()
    {
        $teacher = auth()->user();

        $conversations = Conversation::where('teacher_id', $teacher->id)
            ->with(['parent', 'student', 'latestMessage'])
            ->withCount(['messages as unread_count' => function ($q) use ($teacher) {
                $q->where('sender_id', '!=', $teacher->id)->whereNull('read_at');
            }])
            ->orderByDesc('updated_at')
            ->get();

        return view('teachers.messages', compact('conversations'));
    }

    public function teacherShowConversation(int $conversationId)
    {
        $teacher      = auth()->user();
        $conversation = Conversation::where('teacher_id', $teacher->id)
            ->with(['parent', 'student', 'messages.sender'])
            ->findOrFail($conversationId);

        // Mark parent messages as read
        ParentTeacherMessage::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $teacher->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('teachers.conversation-thread', compact('conversation'));
    }

    public function teacherReply(Request $request, int $conversationId)
    {
        $teacher      = auth()->user();
        $conversation = Conversation::where('teacher_id', $teacher->id)->findOrFail($conversationId);

        $request->validate(['body' => 'required|string|min:1|max:2000']);

        ParentTeacherMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $teacher->id,
            'body'            => $request->body,
        ]);

        $conversation->touch();

        // Notify parent
        $parent = $conversation->parent;
        if ($parent) {
            $parent->notify(new GeneralNotification(
                title:     'New Message from Teacher',
                message:   "{$teacher->full_name} replied to: {$conversation->subject}",
                type:      'general',
                actionUrl: '/parent/conversations/' . $conversation->id,
            ));
        }

        return back()->with('status', 'Reply sent.');
    }
    // -------------------------------------------------------------------------
    // Teachers Directory (from Phase 5)
    // -------------------------------------------------------------------------
    public function teachers(int $studentId)
    {
        $child     = $this->resolveChild($studentId);
        $sessionId = $this->getSchoolCurrentSession();
        $promotion = Promotion::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->latest()->first();

        $teachers = collect();
        if ($promotion) {
            $assigned = AssignedTeacher::with(['teacher', 'course', 'schoolClass', 'section'])
                ->where('class_id', $promotion->class_id)
                ->where('section_id', $promotion->section_id)
                ->where('session_id', $sessionId)
                ->get();

            $teachers = $assigned->groupBy('teacher_id')->map(function ($items) {
                $teacher = $items->first()->teacher;
                $subjects = $items->map(fn($i) => $i->course->name ?? '')->filter()->unique()->values();
                $teacher->subjects_taught = $subjects;
                return $teacher;
            })->values();
        }

        return view('parent.teachers', compact('child', 'teachers'));
    }

    // -------------------------------------------------------------------------
    // Meetings (Mocked) (from Phase 5)
    // -------------------------------------------------------------------------
    public function meetings(int $studentId)
    {
        $child = $this->resolveChild($studentId);
        
        // Mock data
        $upcoming = [
            ['id' => 1, 'teacher' => 'Mr. Smith', 'subject' => 'Math Performance', 'date' => now()->addDays(2)->format('Y-m-d H:i'), 'status' => 'confirmed']
        ];
        $past = [
            ['id' => 2, 'teacher' => 'Mrs. Doe', 'subject' => 'General checkin', 'date' => now()->subDays(10)->format('Y-m-d H:i'), 'status' => 'completed']
        ];

        return view('parent.meetings', compact('child', 'upcoming', 'past'));
    }

    public function storeMeeting(Request $request, int $studentId)
    {
        $child = $this->resolveChild($studentId);
        $request->validate(['teacher_id' => 'required', 'subject' => 'required', 'date' => 'required']);
        return back()->with('status', 'Meeting request submitted successfully (mock).');
    }

    // -------------------------------------------------------------------------
    // Events (from Phase 5)
    // -------------------------------------------------------------------------
    public function events(int $studentId)
    {
        $child = $this->resolveChild($studentId);
        $schoolId = auth()->user()->school_id ?? 1;
        $events = Event::where('school_id', $schoolId)->where('start', '>=', now())->orderBy('start')->get();
        
        return view('parent.events', compact('child', 'events'));
    }

    // -------------------------------------------------------------------------
    // Health (Medical Records) (from Phase 6)
    // -------------------------------------------------------------------------
    public function health(int $studentId)
    {
        $child  = $this->resolveChild($studentId);
        $record = MedicalRecord::where('student_id', $studentId)->first();
        
        return view('parent.health', compact('child', 'record'));
    }

    public function updateHealth(Request $request, int $studentId)
    {
        $child = $this->resolveChild($studentId);
        $validated = $request->validate([
            'emergency_medical_notes' => 'nullable|string',
            'doctor_name'             => 'nullable|string|max:255',
            'doctor_phone'            => 'nullable|string|max:50',
        ]);

        $record = MedicalRecord::firstOrCreate(
            ['student_id' => $child->id],
            ['school_id' => auth()->user()->school_id ?? 1]
        );
        $record->update($validated);

        return back()->with('status', 'Health record updated securely.');
    }

    // -------------------------------------------------------------------------
    // Behavior (Disciplinary Records) (from Phase 6)
    // -------------------------------------------------------------------------
    public function behavior(int $studentId)
    {
        $child = $this->resolveChild($studentId);
        $records = DisciplinaryRecord::with('reporter')
            ->where('student_id', $child->id)
            ->orderBy('incident_date', 'desc')
            ->get();

        $active = $records->where('resolved', false)->values();
        $history = $records->where('resolved', true)->values();

        return view('parent.behavior', compact('child', 'active', 'history'));
    }

    // -------------------------------------------------------------------------
    // Documents (from Phase 6)
    // -------------------------------------------------------------------------
    public function documents(int $studentId)
    {
        $child = $this->resolveChild($studentId);
        $documents = StudentDocument::where('student_id', $child->id)->orderBy('created_at', 'desc')->get();
        return view('parent.documents', compact('child', 'documents'));
    }

    public function uploadDocument(Request $request, int $studentId)
    {
        $child = $this->resolveChild($studentId);
        $request->validate([
            'document_type' => 'required|string',
            'title'         => 'required|string|max:255',
            'file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->store('student_documents/' . $child->id, 'public');

        StudentDocument::create([
            'school_id'     => auth()->user()->school_id ?? 1,
            'student_id'    => $child->id,
            'document_type' => $request->document_type,
            'title'         => $request->title,
            'file_path'     => $path,
            'file_name'     => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'uploaded_by'   => auth()->id(),
            'is_verified'   => false,
        ]);

        return back()->with('status', 'Document uploaded successfully.');
    }

    public function downloadDocument(int $studentId, int $id)
    {
        $child = $this->resolveChild($studentId);
        $document = StudentDocument::where('student_id', $child->id)->findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found on server.');
        }
        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
