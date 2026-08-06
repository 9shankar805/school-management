<?php

namespace App\Policies;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScholarshipPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'principal']);
    }

    private function isTeacher(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'class-teacher', 'hod']);
    }

    private function isStudentOrParent(User $user): bool
    {
        return $user->hasAnyRole(['student', 'parent']);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user)
            || $this->isTeacher($user)
            || $this->isStudentOrParent($user);
    }

    public function view(User $user, Scholarship $scholarship): bool
    {
        if ($this->isAdmin($user) || $this->isTeacher($user)) {
            return true;
        }

        if ($this->isStudentOrParent($user)) {
            return $user->id === $scholarship->student_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Scholarship $scholarship): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Scholarship $scholarship): bool
    {
        return $this->isAdmin($user);
    }
}
