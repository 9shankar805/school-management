<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AssignedTeacher;

trait AssignedTeacherCheck {
    /**
     * Check if the user has management / administrator authority over marks.
     *
     * @param  \App\Models\User|null $user
     * @return bool
     */
    public function isMarksManagementUser($user = null): bool {
        $user = $user ?? Auth::user();
        if (!$user) return false;

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        $managementRoles = [
            'Super Admin', 'super-admin',
            'Admin', 'admin',
            'Exam Controller', 'exam-controller',
            'Principal', 'principal',
            'Vice Principal', 'vice-principal',
            'academic-coordinator', 'Academic Coordinator',
            'organization-owner', 'Owner'
        ];

        return $user->hasAnyRole($managementRoles);
    }

    /**
     * Check if user is the assigned class teacher for given class & section.
     *
     * @param  \App\Models\User|null $user
     * @param  int $sessionId
     * @param  int $classId
     * @param  int $sectionId
     * @return bool
     */
    public function isAssignedClassTeacher($user, $sessionId, $classId, $sectionId = 0): bool {
        $user = $user ?? Auth::user();
        if (!$user || empty($classId)) return false;

        return AssignedTeacher::where('teacher_id', $user->id)
            ->where('class_id', (int)$classId)
            ->where('is_class_teacher', true)
            ->where(function($q) use ($sessionId) {
                $q->where('session_id', $sessionId)
                  ->orWhereNull('session_id')
                  ->orWhere('session_id', 0);
            })
            ->when(!empty($sectionId) && (int)$sectionId > 0, function($q) use ($sectionId) {
                $q->where(function($sq) use ($sectionId) {
                    $sq->where('section_id', (int)$sectionId)
                      ->orWhere('section_id', 0)
                      ->orWhereNull('section_id');
                });
            })
            ->exists();
    }

    /**
     * Ensure logged-in teacher is assigned to the course, class, and section.
     * Administrators and designated Class Teachers bypass subject restrictions.
     *
     * @param  \Illuminate\Http\Request $request
     * @param int $current_school_session_id
     * @return void
    */
    public function checkIfLoggedInUserIsAssignedTeacher(Request $request, $current_school_session_id) {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Full management bypass for School Admins, Principals, and Exam Controllers
        if ($this->isMarksManagementUser($user)) {
            return;
        }

        $classId    = (int) $request->query('class_id', $request->input('class_id'));
        $sectionId  = (int) $request->query('section_id', $request->input('section_id', 0));
        $courseId   = $request->query('course_id', $request->input('course_id'));
        $semesterId = (int) $request->query('semester_id', $request->input('semester_id', 0));

        // Check if user is the designated Class Teacher (Class In-Charge) for this class & section
        $isClassTeacher = $this->isAssignedClassTeacher($user, $current_school_session_id, $classId, $sectionId);

        // If accessing all subjects (Master Grade Ledger)
        if (empty($courseId) || $courseId === 'all' || (int)$courseId === 0) {
            if (!$isClassTeacher) {
                abort(403, 'Access Restricted: Only designated class teachers (Class In-Charge) and administrators can enter marks across all subjects. Subject teachers can only enter marks for their assigned courses.');
            }
            return;
        }

        // Class Teachers have authority over ALL subjects in their class
        if ($isClassTeacher) {
            return;
        }

        // Regular Subject Teachers: Verify assignment for this specific course
        $assigned = AssignedTeacher::where('teacher_id', $user->id)
            ->where('class_id', $classId)
            ->where(function($q) use ($current_school_session_id) {
                $q->where('session_id', $current_school_session_id)
                  ->orWhereNull('session_id')
                  ->orWhere('session_id', 0);
            })
            ->where(function($q) use ($courseId, $classId) {
                $q->where('course_id', (int)$courseId);
                $course = \App\Models\Course::find($courseId);
                if ($course) {
                    $matchingCourseIds = \App\Models\Course::where('class_id', $classId)
                        ->where('course_name', $course->course_name)
                        ->pluck('id')
                        ->toArray();
                    if (!empty($matchingCourseIds)) {
                        $q->orWhereIn('course_id', $matchingCourseIds);
                    }
                }
            })
            ->when($sectionId > 0, function($q) use ($sectionId) {
                $q->where(function($sq) use ($sectionId) {
                    $sq->where('section_id', (int)$sectionId)
                      ->orWhere('section_id', 0)
                      ->orWhereNull('section_id');
                });
            })
            ->when($semesterId > 0, fn($q) => $q->where(function($sq) use ($semesterId) {
                $sq->where('semester_id', $semesterId)->orWhere('semester_id', 0)->orWhereNull('semester_id');
            }))
            ->exists();

        if (!$assigned) {
            $assigned = AssignedTeacher::where('teacher_id', $user->id)
                ->where('class_id', $classId)
                ->where(function($q) use ($current_school_session_id) {
                    $q->where('session_id', $current_school_session_id)
                      ->orWhereNull('session_id')
                      ->orWhere('session_id', 0);
                })
                ->where(function($q) {
                    $q->whereNull('course_id')->orWhere('course_id', 0);
                })
                ->when($sectionId > 0, function($q) use ($sectionId) {
                    $q->where(function($sq) use ($sectionId) {
                        $sq->where('section_id', (int)$sectionId)
                          ->orWhere('section_id', 0)
                          ->orWhereNull('section_id');
                    });
                })
                ->exists();
        }

        if (!$assigned) {
            abort(403, 'Access Restricted: You are not assigned to teach this subject for the selected class and section.');
        }
    }
}