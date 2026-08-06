<?php

namespace App\Policies;

use App\Models\ResultSheet;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResultSheetPolicy
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

    public function view(User $user, ResultSheet $resultSheet): bool
    {
        if ($this->isAdmin($user) || $this->isTeacher($user)) {
            return true;
        }

        if ($this->isStudentOrParent($user)) {
            return $user->id === $resultSheet->student_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, ResultSheet $resultSheet): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, ResultSheet $resultSheet): bool
    {
        return $this->isAdmin($user);
    }
}
