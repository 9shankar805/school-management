<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
use App\Repositories\QuestionBankRepository;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $this->authorize('view exams');

        $repo    = new QuestionBankRepository();
        $filters = $request->only(['subject', 'chapter', 'question_type', 'difficulty', 'bloom', 'category_id', 'search']);

        // Role filter — teachers see only their own + others' shared questions
        if (auth()->user()->hasAnyRole(['teacher', 'class-teacher'])) {
            $filters['created_by'] = auth()->id();
        }

        $questions  = $repo->search($filters);
        $categories = $repo->getAllCategories();
        $subjects   = $repo->getSubjects();
        $tags       = $repo->getAllTags();
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

        $repo       = new QuestionBankRepository();
        $categories = $repo->getAllCategories();
        $subjects   = $repo->getSubjects();
        $tags       = $repo->getAllTags();
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

        $repo     = new QuestionBankRepository();
        $question = $repo->create($data, $request->input('tag_ids', []));

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $repo->uploadImage($question->id, $image, null);
            }
        }

        return redirect()->route('question-bank.index')
            ->with('status', 'Question added to bank.');
    }

    public function show(int $id)
    {
        $this->authorize('view exams');

        $repo     = new QuestionBankRepository();
        $question = $repo->findById($id);

        return view('question-papers.bank.show', compact('question'));
    }

    public function edit(int $id)
    {
        $this->authorize('create exams');

        $repo       = new QuestionBankRepository();
        $question   = $repo->findById($id);
        $categories = $repo->getAllCategories();
        $subjects   = $repo->getSubjects();
        $tags       = $repo->getAllTags();
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

        (new QuestionBankRepository())->update($id, $data, $request->input('tag_ids', []));

        return redirect()->route('question-bank.index')
            ->with('status', 'Question updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create exams');
        (new QuestionBankRepository())->delete($id);
        return back()->with('status', 'Question deleted from bank.');
    }

    public function duplicate(int $id)
    {
        $this->authorize('create exams');
        $copy = (new QuestionBankRepository())->duplicate($id);
        return redirect()->route('question-bank.edit', $copy->id)
            ->with('status', 'Question duplicated. Edit it below.');
    }

    /** POST /question-bank/{id}/images — upload image to a bank question */
    public function uploadImage(Request $request, int $id)
    {
        $this->authorize('create exams');
        $request->validate(['image' => 'required|image|max:4096']);
        $repo  = new QuestionBankRepository();
        $image = $repo->uploadImage($id, $request->file('image'), $request->input('caption'));
        return response()->json(['id' => $image->id, 'url' => $image->url]);
    }

    /** DELETE /question-bank/images/{imageId} */
    public function destroyImage(int $imageId)
    {
        $this->authorize('create exams');
        (new QuestionBankRepository())->deleteImage($imageId);
        return response()->json(['success' => true]);
    }
}
