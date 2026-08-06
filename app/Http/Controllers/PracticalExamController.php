<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\PracticalExam;
use App\Models\PracticalExamMark;
use App\Models\SchoolClass;
use App\Models\User;
use App\Traits\SchoolSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PracticalExamController extends Controller
{
    use SchoolSession;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view exams');
    }

    public function index(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $query     = PracticalExam::with(['course', 'schoolClass', 'examiner'])
            ->where('session_id', $sessionId);

        if ($classId = $request->input('class_id')) {
            $query->where('class_id', $classId);
        }
        if ($courseId = $request->input('course_id')) {
            $query->where('course_id', $courseId);
        }

        $practicals = $query->orderBy('exam_date', 'desc')->paginate(20)->withQueryString();
        $classes    = SchoolClass::orderBy('class_name')->get();
        $courses    = Course::where('session_id', $sessionId)->orderBy('course_name')->get();
        $teachers   = User::role(['teacher', 'class-teacher'])->orderBy('first_name')->get();

        return view('practical-exams.index', compact('practicals', 'classes', 'courses', 'teachers', 'sessionId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create exams');
        $sessionId = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'title'               => 'required|string|max:200',
            'description'         => 'nullable|string',
            'course_id'           => 'nullable|exists:courses,id',
            'class_id'            => 'nullable|exists:school_classes,id',
            'exam_date'           => 'required|date',
            'total_marks'         => 'required|integer|min:1',
            'pass_marks'          => 'required|integer|min:0',
            'venue'               => 'nullable|string|max:200',
            'equipment_required'  => 'nullable|string',
            'examiner_id'         => 'nullable|exists:users,id',
        ]);

        $data['session_id'] = $sessionId;
        PracticalExam::create($data);

        return back()->with('status', 'Practical exam created.');
    }

    public function show(int $id)
    {
        $sessionId  = $this->getSchoolCurrentSession();
        $practical  = PracticalExam::with(['course', 'schoolClass', 'examiner', 'marks.student'])->findOrFail($id);

        // Students in the target class
        $students = $practical->class_id
            ? User::role('student')
                ->whereHas('academic_info', fn($q) => $q->where('class_id', $practical->class_id)->where('session_id', $sessionId))
                ->orderBy('first_name')->get()
            : collect();

        $existingMarks = $practical->marks->keyBy('student_id');

        return view('practical-exams.show', compact('practical', 'students', 'existingMarks'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create exams');
        PracticalExam::findOrFail($id)->update($request->validate([
            'title'               => 'required|string|max:200',
            'description'         => 'nullable|string',
            'exam_date'           => 'required|date',
            'total_marks'         => 'required|integer|min:1',
            'pass_marks'          => 'required|integer|min:0',
            'venue'               => 'nullable|string|max:200',
            'equipment_required'  => 'nullable|string',
            'examiner_id'         => 'nullable|exists:users,id',
        ]));
        return back()->with('status', 'Practical exam updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create exams');
        PracticalExam::findOrFail($id)->delete();
        return redirect()->route('practical-exams.index')->with('status', 'Practical exam deleted.');
    }

    /** Save marks for all students in bulk */
    public function saveMarks(Request $request, int $id)
    {
        $this->authorize('create exams');
        $practical = PracticalExam::findOrFail($id);

        $request->validate([
            'marks'          => 'required|array',
            'marks.*.student_id'    => 'required|exists:users,id',
            'marks.*.marks_obtained'=> 'required|integer|min:0',
            'marks.*.remarks'       => 'nullable|string|max:500',
        ]);

        foreach ($request->marks as $row) {
            PracticalExamMark::updateOrCreate(
                ['practical_exam_id' => $practical->id, 'student_id' => $row['student_id']],
                [
                    'marks_obtained' => min((int) $row['marks_obtained'], $practical->total_marks),
                    'remarks'        => $row['remarks'] ?? null,
                    'recorded_by'    => auth()->id(),
                ]
            );
        }

        return back()->with('status', 'Marks saved.');
    }

    /** PDF mark sheet */
    public function pdf(int $id)
    {
        $practical = PracticalExam::with(['course', 'schoolClass', 'marks.student'])->findOrFail($id);
        $pdf = Pdf::loadView('practical-exams.pdf', compact('practical'))
                  ->setPaper('A4')
                  ->setOptions(['dpi' => 120]);
        return $pdf->download("practical-{$practical->id}-marks.pdf");
    }
}
