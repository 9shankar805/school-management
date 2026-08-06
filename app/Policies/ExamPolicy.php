<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole([
            'admin', 'principal', 'exam-controller',
        ]);
    }

    private function isTeacher(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'class-teacher', 'hod']);
    }

    private function isStudent(User $user): bool
    {
        return $user->hasAnyRole(['student', 'parent']);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user)
            || $this->isTeacher($user)
            || $this->isStudent($user);
    }

    public function view(User $user, Exam $exam): bool
    {
        return $this->isAdmin($user)
            || $this->isTeacher($user)
            || $this->isStudent($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Exam $exam): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $this->isAdmin($user);
    }
}
