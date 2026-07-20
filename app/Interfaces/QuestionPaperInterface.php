<?php

namespace App\Interfaces;

use App\Models\QuestionPaper;
use App\Models\QuestionQuestion;
use App\Models\QuestionSection;
use App\Models\QuestionVersion;
use Illuminate\Database\Eloquent\Collection;

interface QuestionPaperInterface
{
    // ── CRUD ─────────────────────────────────────────────────────────────────

    public function getAll(int $sessionId, ?int $userId = null, ?string $status = null): Collection;

    public function findById(int $id): QuestionPaper;

    public function create(array $data): QuestionPaper;

    public function update(int $id, array $data): QuestionPaper;

    public function delete(int $id): void;

    // ── Sections ─────────────────────────────────────────────────────────────

    public function addSection(int $paperId, array $data): QuestionSection;

    public function updateSection(int $sectionId, array $data): QuestionSection;

    public function deleteSection(int $sectionId): void;

    public function reorderSections(int $paperId, array $orderedIds): void;

    // ── Questions ─────────────────────────────────────────────────────────────

    public function addQuestion(int $sectionId, array $data): QuestionQuestion;

    public function updateQuestion(int $questionId, array $data): QuestionQuestion;

    public function deleteQuestion(int $questionId): void;

    public function reorderQuestions(int $sectionId, array $orderedIds): void;

    public function renumberQuestions(int $paperId): void;

    // ── Approval workflow ─────────────────────────────────────────────────────

    public function advanceStatus(int $paperId, string $action, ?string $comments = null): QuestionPaper;

    // ── Version history ───────────────────────────────────────────────────────

    public function getVersions(int $paperId): Collection;

    public function restoreVersion(int $versionId): QuestionPaper;
}
