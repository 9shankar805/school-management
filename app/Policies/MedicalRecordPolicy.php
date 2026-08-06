<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MedicalRecordPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'principal', 'nurse']);
    }

    private function isParent(User $user): bool
    {
        return $user->hasAnyRole(['parent', 'student']);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $this->isParent($user);
    }

    public function view(User $user, MedicalRecord $medicalRecord): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->isParent($user)) {
            return $user->id === $medicalRecord->student_id
                || $user->id === ($medicalRecord->parent_id ?? null);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->isAdmin($user);
    }
}
