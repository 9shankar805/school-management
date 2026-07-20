<?php

namespace App\Repositories;

use App\Models\QuestionBank;
use App\Models\QuestionCategory;
use App\Models\QuestionImage;
use App\Models\QuestionTag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class QuestionBankRepository
{
    // ── Questions ─────────────────────────────────────────────────────────────

    /**
     * Paginated + filterable list of bank questions.
     */
    public function search(array $filters = [], int $perPage = 20)
    {
        $query = QuestionBank::with(['category', 'tags', 'creator'])
            ->where('is_active', true);

        if (! empty($filters['subject']))       $query->where('subject', $filters['subject']);
        if (! empty($filters['chapter']))       $query->where('chapter', $filters['chapter']);
        if (! empty($filters['question_type'])) $query->where('question_type', $filters['question_type']);
        if (! empty($filters['difficulty']))    $query->where('difficulty', $filters['difficulty']);
        if (! empty($filters['bloom']))         $query->where('bloom_taxonomy', $filters['bloom']);
        if (! empty($filters['category_id']))   $query->where('category_id', $filters['category_id']);
        if (! empty($filters['created_by']))    $query->where('created_by', $filters['created_by']);

        if (! empty($filters['tag_ids'])) {
            $query->whereHas('tags', fn($q) => $q->whereIn('question_tags.id', $filters['tag_ids']));
        }

        if (! empty($filters['search'])) {
            $query->where('question_text', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();
    }

    public function findById(int $id): QuestionBank
    {
        return QuestionBank::with(['category', 'tags', 'images', 'creator'])->findOrFail($id);
    }

    public function create(array $data, array $tagIds = []): QuestionBank
    {
        $question = QuestionBank::create(array_merge($data, ['created_by' => auth()->id()]));
        if ($tagIds) {
            $question->tags()->sync($tagIds);
        }
        return $question;
    }

    public function update(int $id, array $data, array $tagIds = []): QuestionBank
    {
        $question = QuestionBank::findOrFail($id);
        $question->update($data);
        if ($tagIds !== null) {
            $question->tags()->sync($tagIds);
        }
        return $question->fresh();
    }

    public function delete(int $id): void
    {
        $question = QuestionBank::findOrFail($id);
        // Delete stored images
        foreach ($question->images as $img) {
            Storage::delete($img->file_path);
        }
        $question->delete();
    }

    public function duplicate(int $id): QuestionBank
    {
        $original = $this->findById($id);
        $copy     = $original->replicate()->fill(['created_by' => auth()->id()]);
        $copy->save();
        $copy->tags()->sync($original->tags->pluck('id')->toArray());
        return $copy;
    }

    // ── Image upload ──────────────────────────────────────────────────────────

    public function uploadImage(int $bankId, UploadedFile $file, ?string $caption = null): QuestionImage
    {
        $path = $file->store("question-images/{$bankId}", 'public');

        return QuestionImage::create([
            'bank_id'       => $bankId,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_size'     => $file->getSize(),
            'mime_type'     => $file->getMimeType(),
            'caption'       => $caption,
            'uploaded_by'   => auth()->id(),
        ]);
    }

    public function deleteImage(int $imageId): void
    {
        $img = QuestionImage::findOrFail($imageId);
        Storage::disk('public')->delete($img->file_path);
        $img->delete();
    }

    // ── Categories ────────────────────────────────────────────────────────────

    public function getAllCategories(): Collection
    {
        return QuestionCategory::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function createCategory(array $data): QuestionCategory
    {
        return QuestionCategory::create($data);
    }

    public function updateCategory(int $id, array $data): QuestionCategory
    {
        $cat = QuestionCategory::findOrFail($id);
        $cat->update($data);
        return $cat->fresh();
    }

    public function deleteCategory(int $id): void
    {
        QuestionCategory::findOrFail($id)->delete();
    }

    // ── Tags ──────────────────────────────────────────────────────────────────

    public function getAllTags(): Collection
    {
        return QuestionTag::orderBy('name')->get();
    }

    public function createTag(array $data): QuestionTag
    {
        return QuestionTag::create($data);
    }

    // ── Distinct subject / chapter lists (for filter dropdowns) ──────────────

    public function getSubjects(): array
    {
        return QuestionBank::distinct()->orderBy('subject')->pluck('subject')->filter()->values()->toArray();
    }

    public function getChapters(?string $subject = null): array
    {
        return QuestionBank::when($subject, fn($q) => $q->where('subject', $subject))
            ->distinct()->orderBy('chapter')->pluck('chapter')->filter()->values()->toArray();
    }
}
