<?php

namespace App\Policies;

use App\Models\FinalMark;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FinalMarkPolicy
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
        return $user->hasAnyRole(['teacher', 'class-teacher']);
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

    public function view(User $user, FinalMark $finalMark): bool
    {
        if ($this->isAdmin($user) || $this->isTeacher($user)) {
            return true;
        }

        if ($this->isStudent($user)) {
            return $user->id === $finalMark->student_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, FinalMark $finalMark): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, FinalMark $finalMark): bool
    {
        return $this->isAdmin($user);
    }
}
