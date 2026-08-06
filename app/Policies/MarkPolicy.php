<?php

namespace App\Policies;

use App\Models\Mark;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MarkPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole([
            'admin', 'principal', 'vice-principal', 'exam-controller',
        ]);
    }

    private function isTeacher(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'class-teacher']);
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

    public function view(User $user, Mark $mark): bool
    {
        return $this->isAdmin($user)
            || $this->isTeacher($user)
            || $this->isStudentOrParent($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $this->isTeacher($user);
    }

    public function update(User $user, Mark $mark): bool
    {
        return $this->isAdmin($user) || $this->isTeacher($user);
    }

    public function delete(User $user, Mark $mark): bool
    {
        return $this->isAdmin($user);
    }
}
