<?php

namespace App\Policies;

use App\Models\AdmissionApplication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdmissionApplicationPolicy
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

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $this->isTeacher($user);
    }

    public function view(User $user, AdmissionApplication $admissionApplication): bool
    {
        if ($this->isAdmin($user) || $this->isTeacher($user)) {
            return true;
        }

        return $user->id === ($admissionApplication->applicant_id ?? null)
            || $user->email === ($admissionApplication->email ?? null);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->hasAnyRole(['guest']);
    }

    public function update(User $user, AdmissionApplication $admissionApplication): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->id === ($admissionApplication->applicant_id ?? null);
    }

    public function delete(User $user, AdmissionApplication $admissionApplication): bool
    {
        return $this->isAdmin($user);
    }
}
