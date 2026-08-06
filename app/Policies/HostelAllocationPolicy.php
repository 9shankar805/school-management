<?php

namespace App\Policies;

use App\Models\HostelAllocation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HostelAllocationPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'hostel-warden']);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, HostelAllocation $hostelAllocation): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, HostelAllocation $hostelAllocation): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, HostelAllocation $hostelAllocation): bool
    {
        return $this->isAdmin($user);
    }
}
