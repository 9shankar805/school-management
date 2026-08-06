<?php

namespace App\Policies;

use App\Models\Hostel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HostelPolicy
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

    public function view(User $user, Hostel $hostel): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Hostel $hostel): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Hostel $hostel): bool
    {
        return $this->isAdmin($user);
    }
}
