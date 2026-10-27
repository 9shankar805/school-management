<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LiveQuiz;
use App\Models\LiveQuizQuestion;
use App\Models\SchoolClass;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class LiveQuizController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of live quizzes.
     */
    public function index()
    {
        $quizzes = LiveQuiz::with(['questions', 'course', 'schoolClass', 'creator'])
            ->withCount(['questions', 'sessions'])
            ->latest()
            ->paginate(15);

        return view('live-quiz.index', compact('quizzes'));
    }

    /**
     * Show form to create a new live quiz.
     */
    public function create()
    {
        $classes = SchoolClass::orderBy('class_name')->get();
        $courses = Course::orderBy('course_name')->get();

        return view('live-quiz.create', compact('classes', 'courses'));
    }

    /**
     * Store a newly created live quiz with questions.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'category'           => 'nullable|string|max:100',
            'class_id'           => 'nullable|exists:school_classes,id',
            'course_id'          => 'nullable|exists:courses,id',
            'mode'               => 'required|in:mcq,buzzer,mixed',
            'default_time_limit' => 'required|integer|min:5|max:180',
            'default_points'     => 'required|integer|min:10|max:1000',
            'speed_bonus'        => 'nullable|boolean',
            'questions'          => 'nullable|array',
        ]);

        $quiz = LiveQuiz::create([
            'created_by'         => Auth::id(),
            'title'              => $validated['title'],
            'description'        => $validated['description'] ?? null,
            'category'           => $validated['category'] ?? 'General',
            'class_id'           => $validated['class_id'] ?? null,
            'course_id'          => $validated['course_id'] ?? null,
            'mode'               => $validated['mode'],
            'default_time_limit' => $validated['default_time_limit'],
            'default_points'     => $validated['default_points'],
            'speed_bonus'        => $request->has('speed_bonus'),
            'is_active'          => true,
        ]);

        if (!empty($request->questions) && is_array($request->questions)) {
            $order = 1;
            foreach ($request->questions as $qData) {
                if (empty($qData['question_text'])) continue;

                $qType = $qData['question_type'] ?? 'mcq';
                $options = [];
                if ($qType !== 'direct') {
                    $options = [
                        ['key' => 'A', 'text' => $qData['option_a'] ?? 'Option A'],
                        ['key' => 'B', 'text' => $qData['option_b'] ?? 'Option B'],
                        ['key' => 'C', 'text' => $qData['option_c'] ?? 'Option C'],
                        ['key' => 'D', 'text' => $qData['option_d'] ?? 'Option D'],
                    ];
                }

                $correctOpt = null;
                if ($qType !== 'direct') {
                    $correctOpt = strtoupper((string)($qData['correct_option'] ?? 'A'));
                } else {
                    $correctOpt = $qData['correct_option'] ?? null;
                }

                LiveQuizQuestion::create([
                    'live_quiz_id'       => $quiz->id,
                    'question_text'      => $qData['question_text'],
                    'question_type'      => $qType,
                    'options'            => $options,
                    'correct_option'     => $correctOpt,
                    'direct_answer'      => $qData['direct_answer'] ?? null,
                    'points'             => $qData['points'] ?? $quiz->default_points,
                    'time_limit_seconds' => $qData['time_limit_seconds'] ?? $quiz->default_time_limit,
                    'pass_time_seconds'  => $qData['pass_time_seconds'] ?? 15,
                    'pass_points'        => $qData['pass_points'] ?? 50,
                    'explanation'        => $qData['explanation'] ?? null,
                    'sort_order'         => $order++,
                ]);
            }
        }

        return redirect()->route('live-quiz.show', $quiz->id)
            ->with('success', 'Live Quiz created successfully! You can now launch the competition.');
    }

    /**
     * Display the specified live quiz.
     */
    public function show($id)
    {
        $quiz = LiveQuiz::with(['questions', 'course', 'schoolClass', 'sessions.teams'])
            ->findOrFail($id);

        return view('live-quiz.show', compact('quiz'));
    }

    /**
     * Show form to edit the live quiz.
     */
    public function edit($id)
    {
        $quiz = LiveQuiz::with('questions')->findOrFail($id);
        $classes = SchoolClass::orderBy('class_name')->get();
        $courses = Course::orderBy('course_name')->get();

        return view('live-quiz.edit', compact('quiz', 'classes', 'courses'));
    }

    /**
     * Update the specified live quiz.
     */
    public function update(Request $request, $id)
    {
        $quiz = LiveQuiz::findOrFail($id);

        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'category'           => 'nullable|string|max:100',
            'class_id'           => 'nullable|exists:school_classes,id',
            'course_id'          => 'nullable|exists:courses,id',
            'mode'               => 'required|in:mcq,buzzer,mixed',
            'default_time_limit' => 'required|integer|min:5|max:180',
            'default_points'     => 'required|integer|min:10|max:1000',
            'speed_bonus'        => 'nullable|boolean',
            'is_active'          => 'nullable|boolean',
        ]);

        $quiz->update([
            'title'              => $validated['title'],
            'description'        => $validated['description'] ?? null,
            'category'           => $validated['category'] ?? 'General',
            'class_id'           => $validated['class_id'] ?? null,
            'course_id'          => $validated['course_id'] ?? null,
            'mode'               => $validated['mode'],
            'default_time_limit' => $validated['default_time_limit'],
            'default_points'     => $validated['default_points'],
            'speed_bonus'        => $request->has('speed_bonus'),
            'is_active'          => $request->has('is_active'),
        ]);

        return redirect()->route('live-quiz.show', $quiz->id)
            ->with('success', 'Quiz updated successfully.');
    }

    /**
     * Delete a live quiz.
     */
    public function destroy($id)
    {
        $quiz = LiveQuiz::findOrFail($id);
        $quiz->delete();

        return redirect()->route('live-quiz.index')
            ->with('success', 'Quiz deleted successfully.');
    }

    /**
     * Add a single question to an existing quiz.
     */
    public function storeQuestion(Request $request, $quizId)
    {
        $quiz = LiveQuiz::findOrFail($quizId);

        $validated = $request->validate([
            'question_text'      => 'required|string',
            'question_type'      => 'required|in:mcq,buzzer,true_false,direct',
            'option_a'           => 'nullable|string',
            'option_b'           => 'nullable|string',
            'option_c'           => 'nullable|string',
            'option_d'           => 'nullable|string',
            'correct_option'     => 'nullable|string|max:20',
            'direct_answer'      => 'nullable|string',
            'points'             => 'required|integer|min:10|max:1000',
            'time_limit_seconds' => 'required|integer|min:5|max:180',
            'pass_time_seconds'  => 'nullable|integer|min:5|max:180',
            'pass_points'        => 'nullable|integer|min:5|max:500',
            'explanation'        => 'nullable|string',
        ]);

        $qType = $validated['question_type'];
        $options = [];
        if ($qType !== 'direct') {
            $options = [
                ['key' => 'A', 'text' => $validated['option_a'] ?? 'Option A'],
                ['key' => 'B', 'text' => $validated['option_b'] ?? 'Option B'],
                ['key' => 'C', 'text' => $validated['option_c'] ?? 'Option C'],
                ['key' => 'D', 'text' => $validated['option_d'] ?? 'Option D'],
            ];
        }

        $correctOpt = null;
        if ($qType !== 'direct') {
            $correctOpt = strtoupper((string)($validated['correct_option'] ?? 'A'));
        } else {
            $correctOpt = $validated['correct_option'] ?? null;
        }

        $maxOrder = LiveQuizQuestion::where('live_quiz_id', $quizId)->max('sort_order') ?? 0;

        LiveQuizQuestion::create([
            'live_quiz_id'       => $quiz->id,
            'question_text'      => $validated['question_text'],
            'question_type'      => $qType,
            'options'            => $options,
            'correct_option'     => $correctOpt,
            'direct_answer'      => $validated['direct_answer'] ?? null,
            'points'             => $validated['points'],
            'time_limit_seconds' => $validated['time_limit_seconds'],
            'pass_time_seconds'  => $validated['pass_time_seconds'] ?? 15,
            'pass_points'        => $validated['pass_points'] ?? 50,
            'explanation'        => $validated['explanation'] ?? null,
            'sort_order'         => $maxOrder + 1,
        ]);

        return redirect()->route('live-quiz.show', $quizId)
            ->with('success', 'Question added successfully.');
    }

    /**
     * Delete a question.
     */
    public function deleteQuestion($quizId, $questionId)
    {
        $question = LiveQuizQuestion::where('live_quiz_id', $quizId)->findOrFail($questionId);
        $question->delete();

        return redirect()->route('live-quiz.show', $quizId)
            ->with('success', 'Question removed.');
    }

    /**
     * Seed a rich demo competition quiz.
     */
    public function seedDemoQuiz()
    {
        $userId = Auth::id() ?: \App\Models\User::value('id') ?: 1;

        $quiz = LiveQuiz::create([
            'created_by'         => $userId,
            'title'              => 'Inter-House Science & General Knowledge Championship 2026',
            'description'        => 'Exciting multi-group live competition quiz for school houses and classes!',
            'category'           => 'Science & General Knowledge',
            'mode'               => 'mcq',
            'default_time_limit' => 25,
            'default_points'     => 100,
            'speed_bonus'        => true,
            'is_active'          => true,
        ]);

        $sampleQuestions = [
            [
                'text' => 'What is the closest planet to the Sun in our Solar System?',
                'options' => [
                    ['key' => 'A', 'text' => 'Venus'],
                    ['key' => 'B', 'text' => 'Mercury'],
                    ['key' => 'C', 'text' => 'Mars'],
                    ['key' => 'D', 'text' => 'Jupiter'],
                ],
                'correct' => 'B',
                'time' => 20,
                'points' => 100,
                'explanation' => 'Mercury is the closest planet to the Sun with an orbital period of 88 earth days.'
            ],
            [
                'text' => 'Which gas do green plants absorb from the atmosphere during photosynthesis?',
                'options' => [
                    ['key' => 'A', 'text' => 'Oxygen'],
                    ['key' => 'B', 'text' => 'Nitrogen'],
                    ['key' => 'C', 'text' => 'Carbon Dioxide'],
                    ['key' => 'D', 'text' => 'Hydrogen'],
                ],
                'correct' => 'C',
                'time' => 20,
                'points' => 100,
                'explanation' => 'Plants absorb Carbon Dioxide (CO2) and water in the presence of sunlight to produce glucose and oxygen.'
            ],
            [
                'text' => 'What is the highest mountain peak in the world?',
                'options' => [
                    ['key' => 'A', 'text' => 'K2'],
                    ['key' => 'B', 'text' => 'Kangchenjunga'],
                    ['key' => 'C', 'text' => 'Mount Everest (Sagarmatha)'],
                    ['key' => 'D', 'text' => 'Lhotse'],
                ],
                'correct' => 'C',
                'time' => 20,
                'points' => 100,
                'explanation' => 'Mount Everest is the highest mountain above sea level, located in Nepal at 8,848.86 meters.'
            ],
            [
                'text' => 'How many bones are there in the adult human body?',
                'options' => [
                    ['key' => 'A', 'text' => '196'],
                    ['key' => 'B', 'text' => '206'],
                    ['key' => 'C', 'text' => '216'],
                    ['key' => 'D', 'text' => '300'],
                ],
                'correct' => 'B',
                'time' => 25,
                'points' => 120,
                'explanation' => 'An adult human skeleton consists of 206 bones.'
            ],
            [
                'text'        => 'Name the three primary colors of light in physics and digital displays.',
                'type'        => 'direct',
                'options'     => [],
                'correct'     => 'Red, Green, Blue',
                'direct_ans'  => 'Red, Green, Blue (RGB)',
                'time'        => 60,
                'pass_time'   => 15,
                'points'      => 100,
                'pass_points' => 50,
                'explanation' => 'Red, Green, and Blue are the additive primary colors of light used in monitors and television screens.'
            ],
            [
                'text'        => 'Which ancient civilization built the monumental pyramids at Giza?',
                'type'        => 'direct',
                'options'     => [],
                'correct'     => 'Ancient Egyptians',
                'direct_ans'  => 'Ancient Egyptians',
                'time'        => 45,
                'pass_time'   => 15,
                'points'      => 100,
                'pass_points' => 50,
                'explanation' => 'The Great Pyramid of Giza was built for the Pharaoh Khufu in ancient Egypt.'
            ],
            [
                'text'        => 'What is the chemical symbol for Gold on the Periodic Table?',
                'type'        => 'mcq',
                'options'     => [
                    ['key' => 'A', 'text' => 'Ag'],
                    ['key' => 'B', 'text' => 'Fe'],
                    ['key' => 'C', 'text' => 'Au'],
                    ['key' => 'D', 'text' => 'Gd'],
                ],
                'correct'     => 'C',
                'direct_ans'  => 'Au',
                'time'        => 25,
                'pass_time'   => 15,
                'points'      => 150,
                'pass_points' => 50,
                'explanation' => 'Au comes from the Latin word "Aurum", meaning shining dawn.'
            ],
        ];

        $order = 1;
        foreach ($sampleQuestions as $item) {
            LiveQuizQuestion::create([
                'live_quiz_id'       => $quiz->id,
                'question_text'      => $item['text'],
                'question_type'      => $item['type'] ?? 'mcq',
                'options'            => $item['options'] ?? [],
                'correct_option'     => $item['correct'],
                'direct_answer'      => $item['direct_ans'] ?? $item['correct'],
                'points'             => $item['points'],
                'time_limit_seconds' => $item['time'],
                'pass_time_seconds'  => $item['pass_time'] ?? 15,
                'pass_points'        => $item['pass_points'] ?? 50,
                'explanation'        => $item['explanation'],
                'sort_order'         => $order++,
            ]);
        }

        return redirect()->route('live-quiz.show', $quiz->id)
            ->with('success', 'Demo Quiz seeded successfully with Direct Turn & Pass questions and MCQ rounds!');
    }
}
