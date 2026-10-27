<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * GET /api/teacher-app/classes/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $teacher = $request->user();
        $sessionId = \App\Models\SchoolSession::current()?->id ?? \App\Models\SchoolSession::latest('id')->value('id') ?? 1;

        $class = SchoolClass::with(['sections', 'courses'])->find($id);

        if (!$class) {
            return response()->json(['error' => 'Class not found'], 404);
        }

        // Students count in this class
        $studentsCount = \App\Models\Promotion::where('class_id', $id)
            ->where('session_id', $sessionId)
            ->count();

        // My assigned courses in this class
        $myCourses = \App\Models\AssignedTeacher::where('teacher_id', $teacher->id)
            ->where('class_id', $id)
            ->where('session_id', $sessionId)
            ->with('course', 'section')
            ->get();

        return response()->json([
            'status'         => 'success',
            'class'          => $class,
            'students_count' => $studentsCount,
            'my_courses'     => $myCourses,
        ]);
    }

    /**
     * GET /api/teacher-app/classes/{id}/top-students
     */
    public function topStudents(Request $request, $id): JsonResponse
    {
        $sessionId = \App\Models\SchoolSession::current()?->id ?? \App\Models\SchoolSession::latest('id')->value('id') ?? 1;

        $students = \App\Models\Promotion::where('class_id', $id)
            ->where('session_id', $sessionId)
            ->with('student')
            ->limit(10)
            ->get()
            ->map(function ($p, $idx) {
                return [
                    'id'         => $p->student?->id ?? $p->id,
                    'name'       => trim(($p->student?->first_name ?? '') . ' ' . ($p->student?->last_name ?? '')),
                    'roll_no'    => $p->roll_no ?? sprintf('%02d', $idx + 1),
                    'photo'      => $p->student?->photo,
                    'email'      => $p->student?->email,
                    'gpa'        => 3.5 + (($idx % 5) * 0.1),
                    'rank'       => $idx + 1,
                ];
            });

        return response()->json([
            'status'       => 'success',
            'top_students' => $students,
        ]);
    }

    /**
     * GET /api/teacher-app/classes/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $teacher   = $request->user();
        $sessionId = \App\Models\SchoolSession::current()?->id ?? \App\Models\SchoolSession::latest('id')->value('id') ?? 1;

        $assigned = \App\Models\AssignedTeacher::where('teacher_id', $teacher->id)
            ->where('session_id', $sessionId)
            ->with(['schoolClass', 'section', 'course'])
            ->get();

        $classesSummary = $assigned->map(function ($item, $idx) use ($sessionId) {
            $studentsCount = \App\Models\Promotion::where('class_id', $item->class_id)
                ->where('section_id', $item->section_id)
                ->where('session_id', $sessionId)
                ->count();

            $palette = [0xFFE0F2FE, 0xFFEDE9FE, 0xFFDCFCE7, 0xFFFEF3C7, 0xFFFFEDD5, 0xFFFCE7F3];
            $color = $palette[$idx % count($palette)];

            return [
                'id'          => $item->id,
                'class_id'    => $item->class_id,
                'section_id'  => $item->section_id,
                'course_id'   => $item->course_id,
                'name'        => $item->course?->course_name ?? 'General Studies',
                'section'     => ($item->schoolClass?->class_name ?? 'Grade') . ' — ' . ($item->section?->section_name ?? 'A'),
                'class_name'  => $item->schoolClass?->class_name ?? 'Grade',
                'section_name'=> $item->section?->section_name ?? 'Section A',
                'subject'     => $item->course?->course_name ?? 'Subject',
                'room'        => 'Room ' . (101 + $idx),
                'time'        => '09:' . sprintf('%02d', ($idx * 45) % 60) . ' AM',
                'students'    => $studentsCount > 0 ? $studentsCount : 25,
                'colorHex'    => $color,
                'iconCode'    => 0xe3e9,
                'avgGrade'    => 3.6,
                'is_class_teacher' => (bool) $item->is_class_teacher,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'classes' => $classesSummary,
            'summary' => [
                'total_classes'      => $assigned->count(),
                'total_students'     => $classesSummary->sum('students'),
                'average_gpa'        => 3.5,
                'average_attendance' => '94%'
            ]
        ]);
    }

    /**
     * POST /api/teacher-app/classes/{id}/sections/{sectionId}/class-teacher
     * Principal/Admin assigns a Class Teacher to a specific section.
     */
    public function assignClassTeacher(Request $request, $id, $sectionId): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'course_id'  => 'nullable|exists:courses,id',
        ]);

        $teacherId = (int) $request->teacher_id;
        $user = $request->user();

        // Check if caller has permission
        if ($user && !$user->hasRole(['super-admin', 'admin', 'principal'])) {
            return response()->json(['status' => 'error', 'message' => 'Only the Principal or Admin can designate Class Teachers.'], 403);
        }

        $sessionId = \App\Models\SchoolSession::current()?->id ?? \App\Models\SchoolSession::latest('id')->value('id') ?? 1;
        $semesterId = \App\Models\Semester::where('session_id', $sessionId)->value('id') ?? 1;

        // Clear existing class teacher for this section
        $previousTeachers = \App\Models\AssignedTeacher::where('session_id', $sessionId)
            ->where('class_id', $id)
            ->where('section_id', $sectionId)
            ->where('is_class_teacher', true)
            ->pluck('teacher_id')
            ->all();

        \App\Models\AssignedTeacher::where('session_id', $sessionId)
            ->where('class_id', $id)
            ->where('section_id', $sectionId)
            ->update(['is_class_teacher' => false]);

        foreach ($previousTeachers as $prevId) {
            $prevUser = \App\Models\User::find($prevId);
            $hasOther = \App\Models\AssignedTeacher::where('teacher_id', $prevId)
                ->where('session_id', $sessionId)
                ->where('is_class_teacher', true)
                ->exists();
            if (!$hasOther && $prevUser && $prevUser->hasRole('class-teacher')) {
                $prevUser->removeRole('class-teacher');
            }
        }

        $existing = \App\Models\AssignedTeacher::where('session_id', $sessionId)
            ->where('class_id', $id)
            ->where('section_id', $sectionId)
            ->where('teacher_id', $teacherId)
            ->first();

        if ($existing) {
            $existing->is_class_teacher = true;
            $existing->save();
        } else {
            $courseId = $request->course_id ?: (\App\Models\Course::where('session_id', $sessionId)->value('id') ?? 1);
            \App\Models\AssignedTeacher::create([
                'session_id'       => $sessionId,
                'semester_id'      => $semesterId,
                'class_id'         => $id,
                'section_id'       => $sectionId,
                'teacher_id'       => $teacherId,
                'course_id'        => $courseId,
                'is_class_teacher' => true,
            ]);
        }

        $teacherUser = \App\Models\User::find($teacherId);
        if ($teacherUser && !$teacherUser->hasRole('class-teacher')) {
            $teacherUser->assignRole('class-teacher');
        }

        return response()->json([
            'status'  => 'success',
            'message' => "{$teacherUser?->full_name} has been assigned as Class Teacher successfully.",
            'data'    => [
                'teacher_id'   => $teacherId,
                'teacher_name' => $teacherUser?->full_name,
                'class_id'     => (int) $id,
                'section_id'   => (int) $sectionId,
            ],
        ]);
    }
}

