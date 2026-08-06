<?php

namespace App\Policies;

use App\Models\FeeCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeeCategoryPolicy
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

    public function view(User $user, FeeCategory $feeCategory): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, FeeCategory $feeCategory): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, FeeCategory $feeCategory): bool
    {
        return $this->isAdmin($user);
    }
}
