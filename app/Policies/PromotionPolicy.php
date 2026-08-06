<?php

namespace App\Policies;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromotionPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'principal']);
    }

    private function isLocked(Promotion $promotion): bool
    {
        return (bool) ($promotion->is_locked ?? false);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, Promotion $promotion): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Promotion $promotion): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Promotion $promotion): bool
    {
        if ($this->isLocked($promotion)) {
            return false;
        }

        return $this->isAdmin($user);
    }
}
