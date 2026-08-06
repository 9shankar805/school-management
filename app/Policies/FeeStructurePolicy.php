<?php

namespace App\Policies;

use App\Models\FeeStructure;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeeStructurePolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'accountant']);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, FeeStructure $feeStructure): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, FeeStructure $feeStructure): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, FeeStructure $feeStructure): bool
    {
        return $this->isAdmin($user);
    }
}
