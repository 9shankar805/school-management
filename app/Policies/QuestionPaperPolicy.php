<?php

namespace App\Policies;

use App\Models\QuestionPaper;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy for QuestionPaper model.
 *
 * Role hierarchy assumed (highest → lowest):
 *   super-admin  → bypassed globally in AuthServiceProvider (Gate::before)
 *   admin / principal / vice-principal / exam-controller → full access
 *   hod / academic-coordinator                           → review + approve
 *   teacher / class-teacher                              → own papers only
 *   parent / student / guest                             → no access
 */
class QuestionPaperPolicy
{
    use HandlesAuthorization;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole([
            'admin', 'principal', 'vice-principal', 'exam-controller',
        ]);
    }

    private function isReviewer(User $user): bool
    {
        return $user->hasAnyRole([
            'admin', 'principal', 'vice-principal', 'exam-controller',
            'hod', 'academic-coordinator',
        ]);
    }

    private function isTeacher(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'class-teacher']);
    }

    private function isOwner(User $user, QuestionPaper $paper): bool
    {
        return (int) $paper->created_by === $user->id;
    }

    // ── Policy methods ────────────────────────────────────────────────────────

    /**
     * List all papers (index).
     * Admins / reviewers see all; teachers see only theirs (filtered in controller).
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user)
            || $this->isReviewer($user)
            || $this->isTeacher($user);
    }

    /**
     * View a single paper.
     */
    public function view(User $user, QuestionPaper $paper): bool
    {
        if ($this->isAdmin($user) || $this->isReviewer($user)) {
            return true;
        }

        // Teachers can only view their own papers
        return $this->isTeacher($user) && $this->isOwner($user, $paper);
    }

    /**
     * Create a new paper.
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $this->isTeacher($user);
    }

    /**
     * Edit / update paper details.
     * Only editable while in draft / submitted / reviewed status.
     */
    public function update(User $user, QuestionPaper $paper): bool
    {
        if ($paper->is_locked) {
            return false;
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        // Teachers may only edit their own non-locked papers
        return $this->isTeacher($user) && $this->isOwner($user, $paper);
    }

    /**
     * Delete a paper.
     * Only admins or the owning teacher may delete, and never when locked.
     */
    public function delete(User $user, QuestionPaper $paper): bool
    {
        if ($paper->is_locked) {
            return false;
        }

        return $this->isAdmin($user)
            || ($this->isTeacher($user) && $this->isOwner($user, $paper));
    }

    /**
     * Submit a draft paper for review.
     * Only the paper's creator (or an admin) may submit.
     */
    public function submit(User $user, QuestionPaper $paper): bool
    {
        if ($paper->status !== 'draft') {
            return false;
        }

        return $this->isAdmin($user)
            || ($this->isTeacher($user) && $this->isOwner($user, $paper));
    }

    /**
     * Mark a submitted paper as reviewed (HOD / Principal / Exam Controller).
     */
    public function review(User $user, QuestionPaper $paper = null): bool
    {
        return $this->isReviewer($user);
    }

    /**
     * Approve a reviewed paper (Principal / Admin / Exam Controller).
     */
    public function approve(User $user, QuestionPaper $paper): bool
    {
        return $this->isAdmin($user)
            || $user->hasAnyRole(['principal', 'vice-principal', 'exam-controller']);
    }

    /**
     * Lock an approved paper before printing.
     */
    public function lock(User $user, QuestionPaper $paper): bool
    {
        if ($paper->status !== 'approved') {
            return false;
        }

        return $this->isAdmin($user)
            || $user->hasAnyRole(['principal', 'exam-controller']);
    }
}
