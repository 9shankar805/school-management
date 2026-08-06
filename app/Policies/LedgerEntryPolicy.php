<?php

namespace App\Policies;

use App\Models\LedgerEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LedgerEntryPolicy
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

    public function view(User $user, LedgerEntry $ledgerEntry): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, LedgerEntry $ledgerEntry): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, LedgerEntry $ledgerEntry): bool
    {
        return false;
    }
}
