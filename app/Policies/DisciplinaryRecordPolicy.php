<?php

namespace App\Policies;

use App\Models\DisciplinaryRecord;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DisciplinaryRecordPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'principal', 'class-teacher']);
    }

    private function isTeacher(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'hod']);
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

    public function view(User $user, DisciplinaryRecord $disciplinaryRecord): bool
    {
        if ($this->isAdmin($user) || $this->isTeacher($user)) {
            return true;
        }

        if ($this->isStudentOrParent($user)) {
            return $user->id === $disciplinaryRecord->student_id
                || $user->id === ($disciplinaryRecord->parent_id ?? null);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, DisciplinaryRecord $disciplinaryRecord): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, DisciplinaryRecord $disciplinaryRecord): bool
    {
        return $this->isAdmin($user);
    }
}
