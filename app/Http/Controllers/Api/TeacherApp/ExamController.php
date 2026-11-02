<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use App\Models\AssignedTeacher;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamRule;
use App\Models\Mark;
use App\Models\Promotion;
use App\Models\SchoolSession;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    protected function currentSessionId(): int
    {
        return (int) (\App\Models\SchoolSession::current()?->id
            ?? \App\Models\SchoolSession::latest('id')->value('id')
            ?? 1);
    }

    /**
     * GET /api/teacher-app/exams
     */
    public function index(Request $request): JsonResponse
    {
        $sessionId = $this->currentSessionId();
        $teacher = $request->user();

        // 1. Semesters / Terminal Examination Terms
        $semesters = Semester::where('session_id', $sessionId)
            ->when($teacher?->school_id, fn($q) => $q->where('school_id', $teacher->school_id))
            ->get();

        $terminalList = $semesters->map(function ($s) {
            return [
                'id'         => $s->id,
                'title'      => $s->semester_name,
                'name'       => $s->semester_name,
                'start_date' => $s->start_date ? $s->start_date->format('Y-m-d') : null,
                'end_date'   => $s->end_date ? $s->end_date->format('Y-m-d') : null,
            ];
        })->toArray();

        if (empty($terminalList)) {
            $terminalList = [
                ['id' => 1, 'title' => 'First Terminal Examination 2081', 'name' => 'First Terminal Examination 2081'],
                ['id' => 2, 'title' => 'Second Terminal Examination 2081', 'name' => 'Second Terminal Examination 2081'],
                ['id' => 3, 'title' => 'Third Terminal Examination 2081', 'name' => 'Third Terminal Examination 2081'],
                ['id' => 4, 'title' => 'Annual Examination 2081', 'name' => 'Annual Examination 2081'],
            ];
        }

        // 2. Scheduled Course Exams
        $examsQuery = Exam::where('session_id', $sessionId)
            ->when($teacher?->school_id, fn($q) => $q->where('school_id', $teacher->school_id))
            ->with(['course', 'schoolClass', 'semester']);

        if ($request->filled('class_id')) {
            $examsQuery->where('class_id', $request->class_id);
        }

        $exams = $examsQuery->get()->map(function ($e) {
            return [
                'id'          => $e->id,
                'title'       => $e->exam_name,
                'name'        => $e->exam_name,
                'start_date'  => $e->start_date?->format('Y-m-d'),
                'end_date'    => $e->end_date?->format('Y-m-d'),
                'class_id'    => $e->class_id,
                'course_id'   => $e->course_id,
                'semester_id' => $e->semester_id,
                'class_name'  => $e->schoolClass?->class_name,
                'course_name' => $e->course?->course_name,
                'status'      => $e->is_upcoming ? 'upcoming' : ($e->is_ongoing ? 'ongoing' : 'past'),
            ];
        });

        return response()->json([
            'status'               => 'success',
            'exams'                => $exams,
            'terminal_assessments' => $terminalList,
        ]);
    }

    /**
     * GET /api/teacher-app/exams/marks or GET /api/teacher-app/exams/{id}/marks
     */
    public function getMarks(Request $request, $id = null): JsonResponse
    {
        $sessionId = $this->currentSessionId();
        $classId   = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $courseId  = $request->input('course_id');
        $examId    = $id && $id !== 'marks' && is_numeric($id) ? (int)$id : $request->input('exam_id');

        $marksQuery = Mark::where('session_id', $sessionId);

        if ($classId) $marksQuery->where('class_id', $classId);
        if ($sectionId) $marksQuery->where('section_id', $sectionId);
        if ($courseId) $marksQuery->where('course_id', $courseId);
        if ($examId) $marksQuery->where('exam_id', $examId);

        $marks = $marksQuery->with('student')->get()->map(function ($m) {
            return [
                'id'         => $m->id,
                'student_id' => $m->student_id,
                'marks'      => (float)$m->marks,
                'score'      => (float)$m->marks,
                'class_id'   => $m->class_id,
                'section_id' => $m->section_id,
                'course_id'  => $m->course_id,
                'exam_id'    => $m->exam_id,
            ];
        });

        // Also return real subjects/courses for this class if class_id is passed
        $courses = collect();
        if ($classId) {
            $courses = Course::where('class_id', $classId)
                ->where(function($q) use ($sessionId) {
                    $q->where('session_id', $sessionId)->orWhereNull('session_id');
                })
                ->ordered()
                ->get()
                ->unique('course_name')
                ->values()
                ->map(function ($c) {
                    $clean = preg_replace('/[^A-Za-z]/', '', $c->course_name);
                    $code = strtoupper(substr($clean, 0, 3));
                    $isPractical = (bool) preg_match('/(science|sci|physic|chem|bio|comput|hpe|health|vocat|art)/i', $c->course_name);
                    return [
                        'id'            => $c->id,
                        'name'          => $c->course_name,
                        'course_name'   => $c->course_name,
                        'code'          => $code ?: 'SUB',
                        'fm'            => 100.0,
                        'pm'            => 40.0,
                        'has_practical' => $isPractical,
                        'th_fm'         => $isPractical ? 75.0 : 100.0,
                        'pr_fm'         => $isPractical ? 25.0 : 0.0,
                    ];
                });
            if ($courses->isEmpty()) {
                $courses = Course::where('class_id', $classId)
                    ->ordered()
                    ->get()
                    ->unique('course_name')
                    ->values()
                    ->map(function ($c) {
                        $clean = preg_replace('/[^A-Za-z]/', '', $c->course_name);
                        $code = strtoupper(substr($clean, 0, 3));
                        $isPractical = (bool) preg_match('/(science|sci|physic|chem|bio|comput|hpe|health|vocat|art)/i', $c->course_name);
                        return [
                            'id'            => $c->id,
                            'name'          => $c->course_name,
                            'course_name'   => $c->course_name,
                            'code'          => $code ?: 'SUB',
                            'fm'            => 100.0,
                            'pm'            => 40.0,
                            'has_practical' => $isPractical,
                            'th_fm'         => $isPractical ? 75.0 : 100.0,
                            'pr_fm'         => $isPractical ? 25.0 : 0.0,
                        ];
                    });
            }
        }

        return response()->json([
            'status'  => 'success',
            'marks'   => $marks,
            'courses' => $courses,
        ]);
    }

    /**
     * POST /api/teacher-app/exams
     */
    public function store(Request $request): JsonResponse
    {
        $sessionId = $this->currentSessionId();
        $teacher   = $request->user();

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'date'             => 'required|date',
            'duration_minutes' => 'nullable|integer',
            'class_id'         => 'required|integer',
            'course_id'        => 'nullable|integer',
            'semester_id'      => 'nullable|integer',
        ]);

        $exam = Exam::create([
            'school_id'   => $teacher?->school_id,
            'session_id'  => $sessionId,
            'exam_name'   => $validated['title'],
            'start_date'  => $validated['date'],
            'end_date'    => $validated['date'],
            'class_id'    => $validated['class_id'],
            'course_id'   => $validated['course_id'] ?? 1,
            'semester_id' => $validated['semester_id'] ?? null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Exam scheduled successfully.',
            'exam'    => $exam,
        ]);
    }

    /**
     * GET /api/teacher-app/exams/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $exam = Exam::with(['course', 'schoolClass', 'semester'])->find($id);

        if (!$exam) {
            return response()->json(['error' => 'Exam not found'], 404);
        }

        return response()->json(['status' => 'success', 'exam' => $exam]);
    }

    /**
     * PUT /api/teacher-app/exams/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $exam = Exam::find($id);
        if (!$exam) {
            return response()->json(['error' => 'Exam not found'], 404);
        }

        $validated = $request->validate([
            'title'     => 'sometimes|string|max:255',
            'date'      => 'sometimes|date',
            'class_id'  => 'sometimes|integer',
            'course_id' => 'sometimes|integer',
        ]);

        if (isset($validated['title'])) $exam->exam_name = $validated['title'];
        if (isset($validated['date'])) {
            $exam->start_date = $validated['date'];
            $exam->end_date = $validated['date'];
        }
        if (isset($validated['class_id'])) $exam->class_id = $validated['class_id'];
        if (isset($validated['course_id'])) $exam->course_id = $validated['course_id'];
        $exam->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Exam updated successfully.',
            'exam'    => $exam,
        ]);
    }

    /**
     * DELETE /api/teacher-app/exams/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $exam = Exam::find($id);
        if ($exam) $exam->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Exam deleted successfully.'
        ]);
    }

    /**
     * GET /api/teacher-app/exams/{id}/results
     */
    public function results(Request $request, $id): JsonResponse
    {
        $marks = Mark::where('exam_id', $id)
            ->with('student')
            ->get()
            ->map(fn($m) => [
                'student_id' => $m->student_id,
                'name'       => trim(($m->student?->first_name ?? '') . ' ' . ($m->student?->last_name ?? '')),
                'score'      => (float) $m->marks,
            ]);

        return response()->json([
            'status'  => 'success',
            'exam_id' => $id,
            'results' => $marks,
        ]);
    }

    /**
     * PUT /api/teacher-app/exams/{id}/marks or POST /api/teacher-app/exams/marks
     * Real database upsert for student marks.
     */
    public function submitMarks(Request $request, $id = null): JsonResponse
    {
        $sessionId = $this->currentSessionId();
        $teacher   = $request->user();

        $request->validate([
            'marks'              => 'required|array|min:1',
            'marks.*.student_id' => 'required|integer',
        ]);

        $classId    = (int) ($request->input('class_id') ?: 0);
        $sectionId  = (int) ($request->input('section_id') ?: 0);
        $courseId   = (int) ($request->input('course_id') ?: 0);
        $semesterId = (int) ($request->input('semester_id') ?: 0);
        $examName   = $request->input('exam_name', 'Terminal Examination');
        $examId     = $id && $id !== 'marks' && is_numeric($id) ? (int)$id : (int) $request->input('exam_id', 0);

        // Find or create the exam record
        if (!$examId && $classId && $courseId) {
            $exam = Exam::where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('course_id', $courseId)
                ->when($semesterId > 0, fn($q) => $q->where('semester_id', $semesterId))
                ->first();

            if (!$exam) {
                $exam = Exam::create([
                    'school_id'   => $teacher?->school_id,
                    'exam_name'   => $examName,
                    'start_date'  => now(),
                    'end_date'    => now()->addDays(7),
                    'semester_id' => $semesterId > 0 ? $semesterId : null,
                    'class_id'    => $classId,
                    'course_id'   => $courseId,
                    'session_id'  => $sessionId,
                ]);
            }
            $examId = $exam->id;
        }

        // Student section fallback map
        $studentSectionMap = [];
        if ($classId) {
            $studentIds = collect($request->input('marks'))->pluck('student_id')->toArray();
            $studentSectionMap = Promotion::where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->whereIn('student_id', $studentIds)
                ->pluck('section_id', 'student_id')
                ->toArray();
        }

        $savedCount = 0;
        foreach ($request->input('marks') as $m) {
            $stId = (int) $m['student_id'];
            $secId = $sectionId > 0 ? $sectionId : (int)($studentSectionMap[$stId] ?? 1);
            $cId = $courseId > 0 ? $courseId : (int)($m['course_id'] ?? 1);
            $clsId = $classId > 0 ? $classId : (int)($m['class_id'] ?? 1);
            $exId = $examId > 0 ? $examId : 1;

            $th = isset($m['theory']) ? (float)$m['theory'] : null;
            $pr = isset($m['practical']) ? (float)$m['practical'] : null;

            if ($th !== null && $pr !== null && ($th > 0 || $pr > 0)) {
                $score = $th + $pr;
            } elseif (isset($m['score'])) {
                $score = (float)$m['score'];
            } elseif (isset($m['marks'])) {
                $score = (float)$m['marks'];
            } else {
                $score = (float)($m['mark'] ?? 0);
            }

            Mark::updateOrCreate(
                [
                    'session_id' => $sessionId,
                    'class_id'   => $clsId,
                    'course_id'  => $cId,
                    'exam_id'    => $exId,
                    'student_id' => $stId,
                ],
                [
                    'school_id'  => $teacher?->school_id,
                    'section_id' => $secId,
                    'marks'      => $score,
                ]
            );
            $savedCount++;
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Successfully recorded marks for {$savedCount} students in the database.",
            'count'   => $savedCount,
            'exam_id' => $examId,
        ]);
    }
}
