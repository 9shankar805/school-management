<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'accountant']);
    }

    private function isStudentOrParent(User $user): bool
    {
        return $user->hasAnyRole(['student', 'parent']);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $this->isStudentOrParent($user);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->isStudentOrParent($user)) {
            return $user->id === $invoice->student_id
                || $user->id === ($invoice->parent_id ?? null);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->isAdmin($user);
    }
}
