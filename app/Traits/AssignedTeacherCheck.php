<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\AssignedTeacherRepository;

trait AssignedTeacherCheck {
    /**
     * @param  \Illuminate\Http\Request $request
     * @param int $current_school_session_id
     * @return void
    */
    public function checkIfLoggedInUserIsAssignedTeacher(Request $request, $current_school_session_id) {
        $user = Auth::user();

        // Admins, Super Admins, Exam Controllers and users with create marks permission can enter marks for any class
        if ($user && ($user->hasAnyRole(['Super Admin', 'Admin', 'Exam Controller']) || $user->can('create marks'))) {
            return;
        }

        $assignedTeacherRepository = new AssignedTeacherRepository();
        $assignedTeacher = $assignedTeacherRepository->getAssignedTeacher(
            $current_school_session_id, 
            $request->query('semester_id', $request->semester_id), 
            $request->query('class_id', $request->class_id), 
            $request->query('section_id', $request->section_id), 
            $request->query('course_id', $request->course_id)
        );
        
        if($assignedTeacher === null || $assignedTeacher->teacher_id !== $user->id) {
            abort(403, 'Only assigned course teachers or authorized administrators can enter marks for this course.');
        }
    }
}