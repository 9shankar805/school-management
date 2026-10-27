<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamSeatAllocation;
use App\Models\Promotion;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentLedger;
use App\Models\User;
use App\Traits\SchoolSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AdmitCardController extends Controller
{
    use SchoolSession;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Download or view Admit Card for a single student.
     * Enforces Veda-style Fee Clearance check if accessed by student or parent.
     */
    public function single(Request $request, int $examId, int $studentId)
    {
        $user = auth()->user();
        $exam = Exam::with(['semester', 'schoolClass'])->findOrFail($examId);
        $student = User::with('academic_info')->findOrFail($studentId);

        // Security check
        if ($user->hasRole('student') && $user->id !== $studentId) {
            abort(403, 'Unauthorized access to student admit card.');
        }

        $sessionId = $exam->session_id ?? $this->getSchoolCurrentSession();

        // Fee clearance and administrative lock check for students / parents (Veda Model)
        $dueBalance = round(max((float) StudentLedger::currentBalance($studentId, $sessionId), (float) StudentLedger::currentBalance($studentId)), 2);
        if (($user->hasRole('student') || $user->hasRole('parent')) && ($dueBalance > 0 || $student->is_admit_card_locked)) {
            $lockMsg = $student->is_admit_card_locked && $student->admit_card_lock_reason
                ? $student->admit_card_lock_reason
                : ($dueBalance > 0
                    ? 'Outstanding fee dues of NPR ' . number_format($dueBalance, 2) . ' must be cleared prior to examination.'
                    : 'Admit card is held by the finance administration.');
            return redirect()->back()->with('error', '⚠️ Admit Card Locked: ' . $lockMsg);
        }

        // Get student's class and section
        $promotion = Promotion::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->first();

        $classId   = $promotion?->class_id ?? $exam->class_id;
        $sectionId = $promotion?->section_id ?? 0;
        $class     = SchoolClass::find($classId);
        $section   = Section::find($sectionId);

        // Timetable and seat allocations for this exam
        $schedules = ExamSchedule::with(['hall', 'invigilator'])
            ->where('exam_id', $examId)
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();

        // Get seat allocation if exists
        $allocations = ExamSeatAllocation::whereIn('schedule_id', $schedules->pluck('id'))
            ->where('student_id', $studentId)
            ->with(['schedule.hall'])
            ->get()
            ->keyBy('schedule_id');

        $courses = Course::uniqueByName()->where('class_id', $classId)->orderBy('course_name')->get();
        $school  = School::find($student->school_id) ?? config('tenant.school') ?? School::first();

        $data = [
            'school'      => $school,
            'exam'        => $exam,
            'student'     => $student,
            'promotion'   => $promotion,
            'class'       => $class,
            'section'     => $section,
            'schedules'   => $schedules,
            'allocations' => $allocations,
            'courses'     => $courses,
            'dueBalance'  => $dueBalance,
            'feeStatus'   => $dueBalance <= 0 ? 'CLEARED' : 'PENDING',
        ];

        $pdf = Pdf::loadView('exams.pdf.admit-card', ['cards' => [$data]])
            ->setPaper('A4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'dpi' => 120, 'isRemoteEnabled' => true]);

        return $pdf->stream("admit-card-{$student->full_name}.pdf");
    }

    /**
     * Batch generate Admit Cards for an entire class/section (for school administration).
     */
    public function batch(Request $request, int $examId)
    {
        $request->validate([
            'class_id'   => 'required|integer',
            'section_id' => 'nullable|integer',
        ]);

        $exam      = Exam::with(['semester', 'schoolClass'])->findOrFail($examId);
        $sessionId = $exam->session_id ?? $this->getSchoolCurrentSession();
        $classId   = (int) $request->class_id;
        $sectionId = (int) $request->section_id;

        $promoQuery = Promotion::with(['student.academic_info'])
            ->where('session_id', $sessionId)
            ->where('class_id', $classId);

        if ($sectionId > 0) {
            $promoQuery->where('section_id', $sectionId);
        }

        $promotions = $promoQuery->get();
        if ($promotions->isEmpty()) {
            return redirect()->back()->with('error', 'No enrolled students found for the selected class/section.');
        }

        $schedules = ExamSchedule::with(['hall', 'invigilator'])
            ->where('exam_id', $examId)
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();

        $courses = Course::uniqueByName()->where('class_id', $classId)->orderBy('course_name')->get();
        $class   = SchoolClass::find($classId);
        $section = Section::find($sectionId);
        $school  = config('tenant.school') ?? School::find($exam->school_id) ?? School::first();

        $cards = [];
        foreach ($promotions as $promo) {
            $student = $promo->student;
            if (!$student) continue;

            $allocations = ExamSeatAllocation::whereIn('schedule_id', $schedules->pluck('id'))
                ->where('student_id', $student->id)
                ->with(['schedule.hall'])
                ->get()
                ->keyBy('schedule_id');

            $dueBalance = StudentLedger::currentBalance($student->id, $sessionId);

            $cards[] = [
                'school'      => $school,
                'exam'        => $exam,
                'student'     => $student,
                'promotion'   => $promo,
                'class'       => $class,
                'section'     => $section ?? Section::find($promo->section_id),
                'schedules'   => $schedules,
                'allocations' => $allocations,
                'courses'     => $courses,
                'dueBalance'  => $dueBalance,
                'feeStatus'   => $dueBalance <= 0 ? 'CLEARED' : 'PENDING',
            ];
        }

        $pdf = Pdf::loadView('exams.pdf.admit-card', compact('cards'))
            ->setPaper('A4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'dpi' => 120, 'isRemoteEnabled' => true]);

        return $pdf->stream("admit-cards-{$exam->exam_name}-{$class->class_name}.pdf");
    }
}
