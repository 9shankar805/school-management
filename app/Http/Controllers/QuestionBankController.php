<?php

namespace App\Http\Controllers;

use App\Interfaces\QuestionBankInterface;
use App\Models\QuestionBank;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    public function __construct(protected QuestionBankInterface $repo)
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $this->authorize('view exams');

        $filters = $request->only(['subject', 'chapter', 'question_type', 'difficulty', 'bloom', 'category_id', 'search']);

        // Role filter — teachers see only their own questions
        if (auth()->user()->hasAnyRole(['teacher', 'class-teacher'])) {
            $filters['created_by'] = auth()->id();
        }

        $questions  = $this->repo->search($filters);
        $categories = $this->repo->getAllCategories();
        $subjects   = $this->repo->getSubjects();
        $tags       = $this->repo->getAllTags();
        $types      = QuestionBank::QUESTION_TYPES;
        $diffs      = QuestionBank::DIFFICULTIES;
        $blooms     = QuestionBank::BLOOM_LEVELS;

        return view('question-papers.bank.index', compact(
            'questions', 'categories', 'subjects', 'tags',
            'types', 'diffs', 'blooms', 'filters'
        ));
    }

    public function create()
    {
        $this->authorize('create exams');

        $categories = $this->repo->getAllCategories();
        $subjects   = $this->repo->getSubjects();
        $tags       = $this->repo->getAllTags();
        $types      = QuestionBank::QUESTION_TYPES;
        $diffs      = QuestionBank::DIFFICULTIES;
        $blooms     = QuestionBank::BLOOM_LEVELS;

        return view('question-papers.bank.create', compact(
            'categories', 'subjects', 'tags', 'types', 'diffs', 'blooms'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create exams');

        $data = $request->validate([
            'question_type'    => 'required|in:' . implode(',', array_keys(QuestionBank::QUESTION_TYPES)),
            'question_text'    => 'required|string',
            'answer_text'      => 'nullable|string',
            'options'          => 'nullable|array',
            'correct_answer'   => 'nullable|string|max:255',
            'allocated_marks'  => 'required|numeric|min:0.25|max:100',
            'difficulty'       => 'required|in:easy,medium,hard',
            'subject'          => 'nullable|string|max:100',
            'chapter'          => 'nullable|string|max:100',
            'bloom_taxonomy'   => 'nullable|in:' . implode(',', array_keys(QuestionBank::BLOOM_LEVELS)),
            'learning_outcome' => 'nullable|string|max:500',
            'category_id'      => 'nullable|integer|exists:question_categories,id',
            'tag_ids'          => 'nullable|array',
            'tag_ids.*'        => 'integer|exists:question_tags,id',
        ]);

        $question = $this->repo->create($data, $request->input('tag_ids', []));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $this->repo->uploadImage($question->id, $image);
            }
        }

        return redirect()->route('question-bank.index')
            ->with('status', 'Question added to bank.');
    }

    public function show(int $id)
    {
        $this->authorize('view exams');
        $question = $this->repo->findById($id);
        return view('question-papers.bank.show', compact('question'));
    }

    public function edit(int $id)
    {
        $this->authorize('create exams');

        $question   = $this->repo->findById($id);
        $categories = $this->repo->getAllCategories();
        $subjects   = $this->repo->getSubjects();
        $tags       = $this->repo->getAllTags();
        $types      = QuestionBank::QUESTION_TYPES;
        $diffs      = QuestionBank::DIFFICULTIES;
        $blooms     = QuestionBank::BLOOM_LEVELS;

        return view('question-papers.bank.edit', compact(
            'question', 'categories', 'subjects', 'tags', 'types', 'diffs', 'blooms'
        ));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create exams');

        $data = $request->validate([
            'question_type'    => 'required|in:' . implode(',', array_keys(QuestionBank::QUESTION_TYPES)),
            'question_text'    => 'required|string',
            'answer_text'      => 'nullable|string',
            'options'          => 'nullable|array',
            'correct_answer'   => 'nullable|string|max:255',
            'allocated_marks'  => 'required|numeric|min:0.25|max:100',
            'difficulty'       => 'required|in:easy,medium,hard',
            'subject'          => 'nullable|string|max:100',
            'chapter'          => 'nullable|string|max:100',
            'bloom_taxonomy'   => 'nullable|in:' . implode(',', array_keys(QuestionBank::BLOOM_LEVELS)),
            'learning_outcome' => 'nullable|string|max:500',
            'category_id'      => 'nullable|integer|exists:question_categories,id',
            'tag_ids'          => 'nullable|array',
            'tag_ids.*'        => 'integer|exists:question_tags,id',
        ]);

        $this->repo->update($id, $data, $request->input('tag_ids', []));

        return redirect()->route('question-bank.index')
            ->with('status', 'Question updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create exams');
        $this->repo->delete($id);
        return back()->with('status', 'Question deleted from bank.');
    }

    public function duplicate(int $id)
    {
        $this->authorize('create exams');
        $copy = $this->repo->duplicate($id);
        return redirect()->route('question-bank.edit', $copy->id)
            ->with('status', 'Question duplicated. Edit it below.');
    }

    /** POST /question-bank/{id}/images */
    public function uploadImage(Request $request, int $id)
    {
        $this->authorize('create exams');
        $request->validate(['image' => 'required|image|max:4096']);
        $image = $this->repo->uploadImage($id, $request->file('image'), $request->input('caption'));
        return response()->json(['id' => $image->id, 'url' => asset('storage/' . $image->file_path)]);
    }

    /** DELETE /question-bank/images/{imageId} */
    public function destroyImage(int $imageId)
    {
        $this->authorize('create exams');
        $this->repo->deleteImage($imageId);
        return response()->json(['success' => true]);
    }
}
