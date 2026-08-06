<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
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

    public function view(User $user, Payment $payment): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->isStudentOrParent($user)) {
            return $user->id === $payment->student_id
                || $user->id === ($payment->parent_id ?? null);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->isAdmin($user);
    }
}
