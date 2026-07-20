<?php

namespace App\Repositories;

use App\Interfaces\QuestionPaperInterface;
use App\Models\QuestionApproval;
use App\Models\QuestionPaper;
use App\Models\QuestionQuestion;
use App\Models\QuestionSection;
use App\Models\QuestionVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class QuestionPaperRepository implements QuestionPaperInterface
{
    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function getAll(int $sessionId, ?int $userId = null, ?string $status = null): Collection
    {
        return QuestionPaper::with(['creator', 'course', 'template'])
            ->where('session_id', $sessionId)
            ->when($userId, fn($q) => $q->where('created_by', $userId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('updated_at')
            ->get();
    }

    public function findById(int $id): QuestionPaper
    {
        return QuestionPaper::with([
            'template',
            'sections.questions.images',
            'versions',
            'approvals.reviewer',
            'creator',
            'approver',
        ])->findOrFail($id);
    }

    public function create(array $data): QuestionPaper
    {
        return DB::transaction(function () use ($data) {
            $paper = QuestionPaper::create($data);
            // Auto-create version 1
            $paper->saveVersion('Initial draft');
            return $paper;
        });
    }

    public function update(int $id, array $data): QuestionPaper
    {
        $paper = QuestionPaper::findOrFail($id);
        $paper->update($data);
        return $paper->fresh();
    }

    public function delete(int $id): void
    {
        QuestionPaper::findOrFail($id)->delete();
    }

    // ── Sections & Questions ──────────────────────────────────────────────────

    public function addSection(int $paperId, array $data): QuestionSection
    {
        $maxOrder = QuestionSection::where('paper_id', $paperId)->max('sort_order') ?? -1;
        return QuestionSection::create(array_merge($data, [
            'paper_id'   => $paperId,
            'sort_order' => $maxOrder + 1,
        ]));
    }

    public function updateSection(int $sectionId, array $data): QuestionSection
    {
        $section = QuestionSection::findOrFail($sectionId);
        $section->update($data);
        return $section->fresh();
    }

    public function deleteSection(int $sectionId): void
    {
        QuestionSection::findOrFail($sectionId)->delete();
    }

    public function addQuestion(int $sectionId, array $data): QuestionQuestion
    {
        $maxOrder = QuestionQuestion::where('section_id', $sectionId)->max('sort_order') ?? -1;
        $question = QuestionQuestion::create(array_merge($data, [
            'section_id' => $sectionId,
            'sort_order' => $maxOrder + 1,
        ]));

        // Recalculate parent section marks
        QuestionSection::find($sectionId)?->recalcMarks();

        return $question;
    }

    public function updateQuestion(int $questionId, array $data): QuestionQuestion
    {
        $question = QuestionQuestion::findOrFail($questionId);
        $question->update($data);
        QuestionSection::find($question->section_id)?->recalcMarks();
        return $question->fresh();
    }

    public function deleteQuestion(int $questionId): void
    {
        $question  = QuestionQuestion::findOrFail($questionId);
        $sectionId = $question->section_id;
        $question->delete();
        QuestionSection::find($sectionId)?->recalcMarks();
    }

    /**
     * Re-order questions within a section from a given ordered array of IDs.
     */
    public function reorderQuestions(int $sectionId, array $orderedIds): void
    {
        foreach ($orderedIds as $i => $questionId) {
            QuestionQuestion::where('id', $questionId)
                ->where('section_id', $sectionId)
                ->update(['sort_order' => $i]);
        }
    }

    /**
     * Re-order sections within a paper from a given ordered array of IDs.
     */
    public function reorderSections(int $paperId, array $orderedIds): void
    {
        foreach ($orderedIds as $i => $sectionId) {
            QuestionSection::where('id', $sectionId)
                ->where('paper_id', $paperId)
                ->update(['sort_order' => $i]);
        }
    }

    // ── Auto-numbering ────────────────────────────────────────────────────────

    /**
     * Regenerate sequential question numbers for every question in a paper.
     * Format: 1, 2, 3 … for top-level, 1.1, 1.2 … can be extended.
     */
    public function renumberQuestions(int $paperId): void
    {
        $paper = QuestionPaper::with('sections.questions')->find($paperId);
        if (! $paper) return;

        $num = 1;
        foreach ($paper->sections as $section) {
            foreach ($section->questions as $question) {
                $question->update(['numbering' => (string) $num]);
                $num++;
            }
        }
    }

    // ── Approval workflow ─────────────────────────────────────────────────────

    /**
     * Advance the paper status and log the approval action.
     * Returns the updated QuestionPaper.
     */
    public function advanceStatus(int $paperId, string $action, ?string $comments = null): QuestionPaper
    {
        $validTransitions = [
            'draft'     => ['submitted'],
            'submitted' => ['reviewed', 'rejected'],
            'reviewed'  => ['approved', 'rejected'],
            'approved'  => ['locked'],
            'locked'    => ['printed'],
        ];

        return DB::transaction(function () use ($paperId, $action, $comments, $validTransitions) {
            $paper   = QuestionPaper::findOrFail($paperId);
            $allowed = $validTransitions[$paper->status] ?? [];

            if (! in_array($action, $allowed)) {
                throw new \InvalidArgumentException("Cannot transition from '{$paper->status}' to '{$action}'.");
            }

            // Special case: 'rejected' → back to 'draft' for rework
            $newStatus = $action === 'rejected' ? 'draft' : $action;

            $paper->update([
                'status'      => $newStatus,
                'approved_by' => in_array($action, ['approved', 'locked']) ? auth()->id() : $paper->approved_by,
                'approved_at' => $action === 'approved' ? now()              : $paper->approved_at,
            ]);

            QuestionApproval::create([
                'paper_id'    => $paperId,
                'reviewer_id' => auth()->id(),
                'action'      => $action,
                'comments'    => $comments,
                'actioned_at' => now(),
            ]);

            // Snapshot on submit + lock
            if (in_array($action, ['submitted', 'locked'])) {
                $paper->fresh()->saveVersion("Status changed to {$newStatus}");
            }

            return $paper->fresh();
        });
    }

    // ── Version history ───────────────────────────────────────────────────────

    public function getVersions(int $paperId): Collection
    {
        return QuestionVersion::with('saver')
            ->where('paper_id', $paperId)
            ->orderByDesc('version_number')
            ->get();
    }

    public function restoreVersion(int $versionId): QuestionPaper
    {
        return DB::transaction(function () use ($versionId) {
            $version  = QuestionVersion::with('paper')->findOrFail($versionId);
            $snapshot = $version->snapshot;

            $paper = $version->paper;

            // Restore paper meta
            $paper->update($snapshot['paper'] ?? []);

            // Rebuild sections + questions
            $paper->sections()->delete();
            foreach ($snapshot['sections'] ?? [] as $sData) {
                $questions = $sData['questions'] ?? [];
                unset($sData['questions'], $sData['id'], $sData['paper_id'], $sData['created_at'], $sData['updated_at']);
                $section = $paper->sections()->create($sData);
                foreach ($questions as $qData) {
                    unset($qData['id'], $qData['section_id'], $qData['created_at'], $qData['updated_at']);
                    $section->questions()->create($qData);
                }
            }

            $paper->saveVersion("Restored from version {$version->version_number}");
            return $paper->fresh();
        });
    }
}
