<?php

namespace App\Interfaces;

use App\Models\QuestionBank;
use App\Models\QuestionCategory;
use App\Models\QuestionImage;
use App\Models\QuestionTag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface QuestionBankInterface
{
    // ── Questions ─────────────────────────────────────────────────────────────

    public function search(array $filters = [], int $perPage = 20);

    public function findById(int $id): QuestionBank;

    public function create(array $data, array $tagIds = []): QuestionBank;

    public function update(int $id, array $data, array $tagIds = []): QuestionBank;

    public function delete(int $id): void;

    public function duplicate(int $id): QuestionBank;

    // ── Image upload ──────────────────────────────────────────────────────────

    public function uploadImage(int $bankId, UploadedFile $file, ?string $caption = null): QuestionImage;

    public function deleteImage(int $imageId): void;

    // ── Categories ────────────────────────────────────────────────────────────

    public function getAllCategories(): Collection;

    public function createCategory(array $data): QuestionCategory;

    public function updateCategory(int $id, array $data): QuestionCategory;

    public function deleteCategory(int $id): void;

    // ── Tags ──────────────────────────────────────────────────────────────────

    public function getAllTags(): Collection;

    public function createTag(array $data): QuestionTag;

    // ── Filter helpers ────────────────────────────────────────────────────────

    public function getSubjects(): array;

    public function getChapters(?string $subject = null): array;
}
