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
        $sessionId  = $this->currentSessionId();
        $classId    = (int) ($request->input('class_id') ?: 0);
        $sectionId  = (int) ($request->input('section_id') ?: 0);
        $courseId   = (int) ($request->input('course_id') ?: 0);
        $semesterId = (int) ($request->input('semester_id') ?: 0);
        $examParam  = $request->input('exam_name') ?: $request->input('exam_id') ?: ($id && $id !== 'marks' ? $id : null);

        // 1. Resolve Semester if possible
        $semester = null;
        if ($semesterId > 0) {
            $semester = Semester::find($semesterId);
        }
        if (!$semester && !empty($examParam)) {
            if (is_numeric($examParam) && (int)$examParam > 0) {
                $semester = Semester::find((int)$examParam);
            }
            if (!$semester) {
                $cleanParam = trim(preg_replace('/(examination|assessment|term|terminal|exam)/i', '', (string)$examParam));
                $semester = Semester::where('session_id', $sessionId)
                    ->where(function($q) use ($examParam, $cleanParam) {
                        $q->where('semester_name', $examParam)
                          ->orWhere('semester_name', 'like', "%{$examParam}%");
                        if (!empty($cleanParam)) {
                            $q->orWhere('semester_name', 'like', "%{$cleanParam}%");
                        }
                    })
                    ->first();
            }
        }
        if ($semester) {
            $semesterId = $semester->id;
        }

        // 2. Resolve all corresponding exam IDs for this semester & class
        $examIds = collect();
        if ($semesterId > 0) {
            $examIds = Exam::where('session_id', $sessionId)
                ->when($classId > 0, fn($q) => $q->where('class_id', $classId))
                ->where('semester_id', $semesterId)
                ->when($courseId > 0, fn($q) => $q->where('course_id', $courseId))
                ->pluck('id');
        }

        // Fallback: search by exam_name
        if ($examIds->isEmpty() && !empty($examParam)) {
            $examIds = Exam::where('session_id', $sessionId)
                ->when($classId > 0, fn($q) => $q->where('class_id', $classId))
                ->where(function($q) use ($examParam) {
                    $q->where('exam_name', $examParam)
                      ->orWhere('exam_name', 'like', "%{$examParam}%");
                })
                ->when($courseId > 0, fn($q) => $q->where('course_id', $courseId))
                ->pluck('id');
        }

        // If examParam was directly an exam ID
        if ($examIds->isEmpty() && is_numeric($examParam) && (int)$examParam > 0) {
            $singleExam = Exam::find((int)$examParam);
            if ($singleExam) {
                if ($singleExam->semester_id && $courseId <= 0) {
                    $examIds = Exam::where('session_id', $sessionId)
                        ->when($classId > 0, fn($q) => $q->where('class_id', $classId))
                        ->where('semester_id', $singleExam->semester_id)
                        ->pluck('id');
                }
                if ($examIds->isEmpty()) {
                    $examIds = collect([$singleExam->id]);
                }
            }
        }

        // 3. Build query
        $marksQuery = Mark::where('session_id', $sessionId);

        if ($classId > 0) {
            $marksQuery->where('class_id', $classId);
        }

        if ($sectionId > 0) {
            $marksQuery->where(function($q) use ($sectionId) {
                $q->where('section_id', $sectionId)
                  ->orWhere('section_id', 0)
                  ->orWhereNull('section_id');
            });
        }

        if ($courseId > 0) {
            $marksQuery->where('course_id', $courseId);
        }

        if ($examIds->isNotEmpty()) {
            // Only restrict to examIds if matching marks exist, so marks are never hidden
            $marksWithExams = (clone $marksQuery)->whereIn('exam_id', $examIds)->exists();
            if ($marksWithExams) {
                $marksQuery->whereIn('exam_id', $examIds);
            }
        }

        $classCourses = $classId > 0 ? Course::where('class_id', $classId)->get()->keyBy('id') : collect();

        $marks = $marksQuery->with(['student', 'course'])->get()->map(function ($m) use ($classCourses) {
            $course = $m->course ?? $classCourses->get($m->course_id);
            $courseName = $course?->course_name ?? '';
            $isPractical = (bool) preg_match('/(science|sci|physic|chem|bio|comput|hpe|health|vocat|art|oral)/i', $courseName);
            $th = $isPractical ? round($m->marks * 0.75, 1) : (float)$m->marks;
            $pr = $isPractical ? round($m->marks * 0.25, 1) : 0.0;
            return [
                'id'            => $m->id,
                'student_id'    => $m->student_id,
                'student_name'  => trim(($m->student?->first_name ?? '') . ' ' . ($m->student?->last_name ?? '')),
                'marks'         => (float)$m->marks,
                'score'         => (float)$m->marks,
                'theory'        => (float)$th,
                'practical'     => (float)$pr,
                'has_practical' => $isPractical,
                'class_id'      => $m->class_id,
                'section_id'    => $m->section_id,
                'course_id'     => $m->course_id,
                'course_name'   => $courseName,
                'exam_id'       => $m->exam_id,
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
     * Real database upsert for student marks across all subjects or single subject.
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

        // 1. Resolve Semester
        $semester = null;
        if ($semesterId > 0) {
            $semester = Semester::find($semesterId);
        }
        if (!$semester && !empty($examName)) {
            $cleanExamName = trim(preg_replace('/(examination|assessment|term|terminal|exam)/i', '', (string)$examName));
            $semester = Semester::where('session_id', $sessionId)
                ->where(function($q) use ($examName, $cleanExamName) {
                    $q->where('semester_name', $examName)
                      ->orWhere('semester_name', 'like', "%{$examName}%");
                    if (!empty($cleanExamName)) {
                        $q->orWhere('semester_name', 'like', "%{$cleanExamName}%");
                    }
                })
                ->first();
        }
        if (!$semester) {
            $semester = Semester::firstOrCreate(
                ['session_id' => $sessionId, 'semester_name' => $examName],
                [
                    'school_id'  => $teacher?->school_id,
                    'start_date' => now(),
                    'end_date'   => now()->addMonths(3),
                ]
            );
        }
        $resolvedSemesterId = $semester?->id ?? ($semesterId > 0 ? $semesterId : 1);
        $semesterTitle = $semester?->semester_name ?? $examName;

        // 2. Build or find Exam records for every course submitted (like web MarkController)
        $marksInput = $request->input('marks');
        $submittedCourseIds = collect($marksInput)
            ->map(fn($m) => (int)($m['course_id'] ?? $courseId))
            ->filter(fn($cId) => $cId > 0)
            ->unique()
            ->values()
            ->toArray();

        if (empty($submittedCourseIds) && $courseId > 0) {
            $submittedCourseIds = [$courseId];
        }

        $examCache = [];
        foreach ($submittedCourseIds as $cId) {
            $exam = Exam::where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('course_id', $cId)
                ->when($resolvedSemesterId > 0, fn($q) => $q->where('semester_id', $resolvedSemesterId))
                ->first();

            if (!$exam) {
                $exam = Exam::create([
                    'school_id'   => $teacher?->school_id,
                    'exam_name'   => $semesterTitle,
                    'start_date'  => now(),
                    'end_date'    => now()->addDays(7),
                    'semester_id' => $resolvedSemesterId,
                    'class_id'    => $classId,
                    'course_id'   => $cId,
                    'session_id'  => $sessionId,
                ]);
            }

            // Ensure ExamRule exists
            ExamRule::firstOrCreate(
                ['exam_id' => $exam->id, 'session_id' => $sessionId],
                [
                    'school_id'               => $teacher?->school_id,
                    'total_marks'             => 100,
                    'pass_marks'              => 40,
                    'marks_distribution_note' => 'Standard 100 marks',
                ]
            );

            $examCache[$cId] = $exam;
        }

        // Student section fallback map
        $studentSectionMap = [];
        if ($classId) {
            $studentIds = collect($marksInput)->pluck('student_id')->toArray();
            $studentSectionMap = Promotion::where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->whereIn('student_id', $studentIds)
                ->pluck('section_id', 'student_id')
                ->toArray();
        }

        $savedCount = 0;
        foreach ($marksInput as $m) {
            $stId = (int) $m['student_id'];
            $secId = $sectionId > 0 ? $sectionId : (int)($studentSectionMap[$stId] ?? 1);
            $cId = (int)($m['course_id'] ?? ($courseId > 0 ? $courseId : 1));
            $clsId = $classId > 0 ? $classId : (int)($m['class_id'] ?? 1);

            $examForCourse = $examCache[$cId] ?? null;
            $exId = $examForCourse ? $examForCourse->id : ($examId > 0 ? $examId : 1);

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
            'status'      => 'success',
            'message'     => "Successfully recorded {$savedCount} mark entries for {$semesterTitle} in the database.",
            'count'       => $savedCount,
            'semester_id' => $resolvedSemesterId,
            'exam_id'     => $examId,
        ]);
    }
}
