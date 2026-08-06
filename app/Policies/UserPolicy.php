<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole([
            'admin', 'principal', 'vice-principal',
        ]);
    }

    private function isTeacher(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'class-teacher', 'hod']);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $this->isTeacher($user);
    }

    public function view(User $user, User $model): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->isTeacher($user)) {
            return true;
        }

        return $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, User $model): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $this->isAdmin($user);
    }
}
