<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use App\Models\AssignedTeacher;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Exam;
use App\Models\LeaveApplication;
use App\Models\Mark;
use App\Models\Notice;
use App\Models\Promotion;
use App\Models\SchoolSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassTeacherController extends Controller
{
    /**
     * Resolve the active class teacher assignment for the user.
     */
    protected function getAssignment(Request $request)
    {
        $user = $request->user();
        $sessionId = SchoolSession::current()?->id ?? SchoolSession::latest('id')->value('id') ?? 1;

        if ($user && $user->hasRole(['super-admin', 'admin', 'principal'])) {
            $classId   = $request->query('class_id');
            $sectionId = $request->query('section_id');

            if ($classId && $sectionId) {
                return AssignedTeacher::with(['schoolClass', 'section'])
                    ->where('class_id', $classId)
                    ->where('section_id', $sectionId)
                    ->where('session_id', $sessionId)
                    ->first();
            }

            return AssignedTeacher::with(['schoolClass', 'section'])
                ->where('session_id', $sessionId)
                ->where('is_class_teacher', true)
                ->first();
        }

        return AssignedTeacher::with(['schoolClass', 'section'])
            ->where('teacher_id', $user?->id)
            ->where('session_id', $sessionId)
            ->where('is_class_teacher', true)
            ->first();
    }

    /**
     * GET /api/teacher-app/class-teacher/overview
     * High-level summary of the class in-charge section.
     */
    public function overview(Request $request): JsonResponse
    {
        $assignment = $this->getAssignment($request);

        if (!$assignment) {
            return response()->json([
                'status'  => 'success',
                'is_class_teacher' => false,
                'message' => 'You are currently not assigned as a Class In-Charge for any section.',
            ]);
        }

        $sessionId = $assignment->session_id;
        $classId   = $assignment->class_id;
        $sectionId = $assignment->section_id;
        $today     = now()->toDateString();

        $enrolledCount = Promotion::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('session_id', $sessionId)
            ->count();

        $todayAttendances = Attendance::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereDate('date', $today)
            ->get();

        $presentCount = $todayAttendances->where('status', 'on')->count();
        $absentCount  = $todayAttendances->where('status', 'off')->count();

        $studentUserIds = Promotion::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('session_id', $sessionId)
            ->pluck('student_id')
            ->all();

        $pendingLeavesCount = LeaveApplication::whereIn('user_id', $studentUserIds)
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'status' => 'success',
            'is_class_teacher' => true,
            'data'   => [
                'class_id'             => $classId,
                'class_name'           => $assignment->schoolClass?->class_name,
                'section_id'           => $sectionId,
                'section_name'         => $assignment->section?->section_name,
                'total_students'       => $enrolledCount,
                'attendance_taken'     => $todayAttendances->isNotEmpty(),
                'today_present_count'  => $presentCount,
                'today_absent_count'   => $absentCount,
                'pending_leaves_count' => $pendingLeavesCount,
            ],
        ]);
    }

    /**
     * GET /api/teacher-app/class-teacher/students
     * Full roster with parent contacts, roll number, and emergency details.
     */
    public function students(Request $request): JsonResponse
    {
        $assignment = $this->getAssignment($request);
        if (!$assignment) {
            return response()->json(['status' => 'error', 'message' => 'No assigned class found.'], 404);
        }

        $sessionId = $assignment->session_id;

        $promotions = Promotion::with(['student.academic_info', 'student.parent'])
            ->where('class_id', $assignment->class_id)
            ->where('section_id', $assignment->section_id)
            ->where('session_id', $sessionId)
            ->orderBy('roll_no')
            ->get();

        $data = $promotions->map(function ($p) {
            $stu = $p->student;
            $parent = $stu?->parent;
            return [
                'id'            => $stu?->id,
                'roll_no'       => $p->roll_no,
                'admission_no'  => $stu?->academic_info?->admission_no ?? $p->id_card_number,
                'full_name'     => $stu?->full_name,
                'email'         => $stu?->email,
                'phone'         => $stu?->phone,
                'gender'        => $stu?->gender,
                'avatar'        => $stu?->avatar,
                'parent_name'   => $parent?->full_name,
                'parent_phone'  => $parent?->phone,
                'parent_email'  => $parent?->email,
                'address'       => $stu?->address,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * GET /api/teacher-app/class-teacher/leaves
     * View pending leave requests from parents.
     */
    public function leaves(Request $request): JsonResponse
    {
        $assignment = $this->getAssignment($request);
        if (!$assignment) {
            return response()->json(['status' => 'error', 'message' => 'No assigned class found.'], 404);
        }

        $studentUserIds = Promotion::where('class_id', $assignment->class_id)
            ->where('section_id', $assignment->section_id)
            ->where('session_id', $assignment->session_id)
            ->pluck('student_id')
            ->all();

        $leaves = LeaveApplication::whereIn('user_id', $studentUserIds)
            ->with(['user', 'leaveType'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->latest()
            ->get();

        $data = $leaves->map(function ($l) {
            return [
                'id'          => $l->id,
                'student_name'=> $l->user?->full_name,
                'student_id'  => $l->user_id,
                'leave_type'  => $l->leaveType?->name ?? 'General Leave',
                'start_date'  => $l->start_date ? $l->start_date->format('Y-m-d') : null,
                'end_date'    => $l->end_date ? $l->end_date->format('Y-m-d') : null,
                'reason'      => $l->reason,
                'status'      => $l->status,
                'created_at'  => $l->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * POST /api/teacher-app/class-teacher/leaves/{id}/action
     */
    public function handleLeave(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status'  => 'required|in:approved,rejected',
            'remarks' => 'nullable|string|max:255',
        ]);

        $leave = LeaveApplication::find($id);
        if (!$leave) {
            return response()->json(['status' => 'error', 'message' => 'Leave application not found.'], 404);
        }

        $leave->status           = $request->status;
        $leave->reviewed_by      = $request->user()?->id;
        $leave->reviewed_at      = now();
        $leave->rejection_reason = $request->remarks;
        $leave->save();

        return response()->json([
            'status'  => 'success',
            'message' => "Student leave application has been {$request->status} successfully.",
            'data'    => $leave,
        ]);
    }

    /**
     * POST /api/teacher-app/class-teacher/diary
     * Post a digital school diary / circular entry to parents of this class.
     */
    public function postDiary(Request $request): JsonResponse
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'notice_date' => 'nullable|date',
        ]);

        $assignment = $this->getAssignment($request);
        if (!$assignment) {
            return response()->json(['status' => 'error', 'message' => 'You are not assigned as a Class In-Charge.'], 403);
        }

        $notice = Notice::create([
            'title'        => $request->title,
            'notice'       => $request->description,
            'priority'     => 'normal',
            'audience'     => 'parents',
            'session_id'   => $assignment->session_id,
            'published_by' => $request->user()?->id,
            'school_id'    => config('tenant.school')?->id ?? $request->user()?->school_id,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Class diary announcement posted successfully to all parents.',
            'data'    => $notice,
        ], 201);
    }

    /**
     * PUT /api/teacher-app/class-teacher/students/{id}
     * Class In-Charge or Administrator updates student details (name, roll_no, parent info, phone, address).
     */
    public function updateStudent(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $sessionId = SchoolSession::current()?->id ?? SchoolSession::latest('id')->value('id') ?? 1;

        $student = \App\Models\User::role('student')->find($id);
        if (!$student) {
            $student = \App\Models\User::find($id);
        }
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Student record not found.'], 404);
        }

        // Find student promotion in current session
        $promo = Promotion::where('student_id', $student->id)
            ->where('session_id', $sessionId)
            ->first();

        // Check if caller is class teacher for this student's class/section or admin/principal
        $isAdmin = $user && $user->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'vice-principal', 'academic-coordinator']);
        $isClassTeacherOfStudent = false;

        if ($promo && $user) {
            $isClassTeacherOfStudent = AssignedTeacher::where('teacher_id', $user->id)
                ->where('class_id', $promo->class_id)
                ->where('section_id', $promo->section_id)
                ->where('session_id', $sessionId)
                ->where('is_class_teacher', true)
                ->exists();
        }

        if (!$isAdmin && !$isClassTeacherOfStudent) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Only the assigned Class In-Charge or School Administrator can edit student details.'
            ], 403);
        }

        $request->validate([
            'first_name'   => 'nullable|string|max:100',
            'last_name'    => 'nullable|string|max:100',
            'name'         => 'nullable|string|max:200',
            'roll_no'      => 'nullable|string|max:50',
            'phone'        => 'nullable|string|max:50',
            'email'        => 'nullable|email|max:150',
            'gender'       => 'nullable|string|max:20',
            'address'      => 'nullable|string|max:255',
            'parent_name'  => 'nullable|string|max:150',
            'parent_phone' => 'nullable|string|max:50',
            'father_name'  => 'nullable|string|max:150',
            'father_phone' => 'nullable|string|max:50',
            'mother_name'  => 'nullable|string|max:150',
            'mother_phone' => 'nullable|string|max:50',
        ]);

        // Update student names and basic details
        if ($request->filled('first_name')) {
            $student->first_name = $request->first_name;
        }
        if ($request->filled('last_name')) {
            $student->last_name = $request->last_name;
        }
        if ($request->filled('name') && !$request->filled('first_name')) {
            $parts = explode(' ', trim($request->name), 2);
            $student->first_name = $parts[0];
            $student->last_name  = $parts[1] ?? '';
        }
        if ($request->has('phone')) {
            $student->phone = $request->phone;
        }
        if ($request->has('email') && $request->email) {
            $student->email = $request->email;
        }
        if ($request->has('address')) {
            $student->address = $request->address;
        }
        if ($request->has('gender')) {
            $student->gender = $request->gender;
        }
        $student->save();

        // Update roll number in promotion
        if ($promo && $request->has('roll_no')) {
            $promo->roll_no = $request->roll_no;
            $promo->save();
        }

        // Update parent info
        $parentName = $request->parent_name ?: $request->father_name;
        $parentPhone = $request->parent_phone ?: $request->father_phone;

        if ($parentName || $parentPhone || $request->has('mother_name') || $request->has('mother_phone') || $request->has('address')) {
            \App\Models\StudentParentInfo::updateOrCreate(
                ['student_id' => $student->id],
                [
                    'father_name'    => $parentName ?: '',
                    'father_phone'   => $parentPhone ?: '',
                    'mother_name'    => $request->mother_name ?: '',
                    'mother_phone'   => $request->mother_phone ?: '',
                    'parent_address' => $request->address ?: '',
                    'school_id'      => $student->school_id ?? 1,
                ]
            );

            // Also update linked parent user account if exists
            $parentUser = $student->parents()->first();
            if ($parentUser) {
                if ($parentName) {
                    $parts = explode(' ', trim($parentName), 2);
                    $parentUser->first_name = $parts[0];
                    $parentUser->last_name  = $parts[1] ?? '';
                }
                if ($parentPhone) {
                    $parentUser->phone = $parentPhone;
                }
                $parentUser->save();
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Student details updated successfully.',
            'student' => [
                'id'           => $student->id,
                'name'         => trim($student->first_name . ' ' . $student->last_name),
                'first_name'   => $student->first_name,
                'last_name'    => $student->last_name,
                'roll_no'      => $promo?->roll_no ?? $request->roll_no,
                'phone'        => $student->phone,
                'parent_name'  => $parentName,
                'parent_phone' => $parentPhone,
                'address'      => $student->address,
                'gender'       => $student->gender,
            ]
        ]);
    }
}
