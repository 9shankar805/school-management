<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole([
            'admin', 'principal', 'vice-principal', 'hod',
        ]);
    }

    private function isTeacher(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'class-teacher']);
    }

    private function isStudentOrParent(User $user): bool
    {
        return $user->hasAnyRole(['student', 'parent']);
    }

    private function isOlderThanSevenDays(Attendance $attendance): bool
    {
        if (! isset($attendance->attendance_date)) {
            return false;
        }

        return now()->diffInDays($attendance->attendance_date) > 7;
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user)
            || $this->isTeacher($user)
            || $this->isStudentOrParent($user);
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if ($this->isAdmin($user) || $this->isTeacher($user)) {
            return true;
        }

        if ($this->isStudentOrParent($user)) {
            return $user->id === $attendance->student_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $this->isTeacher($user);
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $this->isAdmin($user) || $this->isTeacher($user);
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        if ($this->isOlderThanSevenDays($attendance)) {
            return false;
        }

        return $this->isAdmin($user);
    }
}
