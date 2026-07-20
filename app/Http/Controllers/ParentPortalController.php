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
        $present = $all->where('status', 'present')->count();
        $absent  = $all->where('status', 'absent')->count();
        $pct     = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        $deficit = $total > 0 ? max(0, (int) ceil(($total * 0.75 - $present) / (1 - 0.75))) : 0;

        // Monthly records for calendar
        $monthly = Attendance::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get()
            ->groupBy(fn($a) => Carbon::parse($a->created_at)->day);

        // Per-course breakdown
        $courseBreakdown = Attendance::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->with('course')
            ->get()
            ->groupBy('course_id')
            ->map(function ($records) {
                $t = $records->count();
                $p = $records->where('status', 'present')->count();
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
            ->where('created_at', '>=', now()->subWeeks(12))
            ->select(
                DB::raw('YEARWEEK(created_at, 1) as yw'),
                DB::raw("SUM(status='present') as present"),
                DB::raw("SUM(status='absent') as absent")
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
        $trend = Mark::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->select('exams.exam_name as exam_name', DB::raw('ROUND(AVG(marks.marks), 1) as avg'))
            ->groupBy('marks.exam_id', 'exams.exam_name', 'exams.start_date')
            ->orderBy('exams.start_date')
            ->get();

        // Course bar chart
        $courseTrend = Mark::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->join('courses', 'marks.course_id', '=', 'courses.id')
            ->select('courses.name as course_name', DB::raw('ROUND(AVG(marks.marks), 1) as avg'))
            ->groupBy('marks.course_id', 'courses.name')
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
                $paid      = $inv->payments->sum('amount_paid');
                $remaining = max(0, $inv->amount - $paid);
                $display   = $inv->status;
                if ($inv->status === 'unpaid' && $inv->due_date && $inv->due_date < now()->toDateString()) {
                    $display = 'overdue';
                }
                $inv->amount_paid  = $paid;
                $inv->remaining    = $remaining;
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
        $invoice = Invoice::where('student_id', $studentId)->findOrFail($invoiceId);

        $paid      = $invoice->payments->sum('amount_paid');
        $remaining = max(0, $invoice->amount - $paid);

        return view('parent.pay-invoice', compact('child', 'invoice', 'remaining'));
    }

    public function processInvoicePayment(Request $request, int $studentId, int $invoiceId)
    {
        $child   = $this->resolveChild($studentId);
        $invoice = Invoice::where('student_id', $studentId)->findOrFail($invoiceId);

        $request->validate([
            'amount_paid'    => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,online,cheque',
        ]);

        $payment = Payment::create([
            'invoice_id'     => $invoice->id,
            'amount_paid'    => $request->amount_paid,
            'payment_date'   => now()->toDateString(),
            'payment_method' => $request->payment_method,
        ]);

        // Update invoice status
        $totalPaid = $invoice->payments()->sum('amount_paid') + $payment->amount_paid;
        $invoice->update([
            'status' => $totalPaid >= $invoice->amount ? 'paid' : 'unpaid',
        ]);

        // Notify parent via general notification (receipt)
        auth()->user()->notify(new GeneralNotification(
            title:   'Payment Received',
            message: "Payment of {$payment->amount_paid} recorded for invoice: {$invoice->title}.",
            type:    'fee',
            actionUrl: '/parent/' . $studentId . '/fees',
        ));

        return redirect()->route('parent.fees', $studentId)->with('status', 'Payment recorded successfully.');
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
        $examTrend = Mark::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->select('exams.exam_name as exam_name', DB::raw('ROUND(AVG(marks.marks), 1) as avg'))
            ->groupBy('marks.exam_id', 'exams.exam_name', 'exams.start_date')
            ->orderBy('exams.start_date')
            ->get();

        // Course bar chart
        $courseTrend = Mark::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->join('courses', 'marks.course_id', '=', 'courses.id')
            ->select('courses.name as course_name', DB::raw('ROUND(AVG(marks.marks), 1) as avg'))
            ->groupBy('marks.course_id', 'courses.name')
            ->get();

        // Multi-session comparison
        $sessions = Mark::where('student_id', $studentId)
            ->distinct()
            ->pluck('session_id');

        $multiSession = [];
        if ($sessions->count() > 1) {
            foreach ($sessions as $sid) {
                $multiSession[$sid] = Mark::where('student_id', $studentId)
                    ->where('session_id', $sid)
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

    public function adminLinkPage()
    {
        return view('parent.admin-link');
    }
}
