<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamRuleStoreRequest;
use App\Models\ExamRule;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Repositories\ExamRuleRepository;
use App\Interfaces\SchoolSessionInterface;

class ExamRuleController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $exam_id = $request->query('exam_id', 0);
        $examRuleRepository = new ExamRuleRepository();
        $exam_rules = $examRuleRepository->getAll($current_school_session_id, $exam_id);

        $data = [
            'exam_rules' => $exam_rules
        ];
        return view('exams.view-rule', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $exam_id = $request->query('exam_id');

        $data = [
            'exam_id' => $exam_id,
            'current_school_session_id' => $current_school_session_id,
        ];

        return view('exams.add-rule', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ExamRuleStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ExamRuleStoreRequest $request)
    {
        try {
            $examRuleRepository = new ExamRuleRepository();
            $examRuleRepository->create($request->validated());

            return back()->with('status', 'Exam rule creation was successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ExamRule  $examRule
     * @return \Illuminate\Http\Response
     */
    public function show(ExamRule $examRule)
    {
        return view('exams.view-rule');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $examRuleRepository = new ExamRuleRepository();
        $exam_rule = $examRuleRepository->getById($request->exam_rule_id);
        $data = [
            'exam_rule_id'  => $request->exam_rule_id,
            'exam_rule'     => $exam_rule,
        ];
        return view('exams.edit-rule', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $sessionId = $this->getSchoolCurrentSession();
            $schoolId  = auth()->user()->school_id ?? null;
            $ruleId    = (int) $request->exam_rule_id;
            $examId    = (int) $request->exam_id;
            $courseId  = (int) $request->course_id;

            // No rule yet — create exam + rule
            if ($ruleId === 0) {
                // Find or create exam for this course
                if ($examId === 0) {
                    $course = \App\Models\Course::find($courseId);
                    $semId  = \App\Models\Semester::where('session_id', $sessionId)->value('id');
                    $exam   = \App\Models\Exam::firstOrCreate([
                        'session_id'  => $sessionId,
                        'class_id'    => $course?->class_id,
                        'course_id'   => $courseId,
                        'semester_id' => $semId,
                    ], [
                        'school_id'  => $schoolId,
                        'exam_name'  => 'Terminal',
                        'start_date' => now(),
                        'end_date'   => now()->addDays(7),
                    ]);
                    $examId = $exam->id;
                }
                \App\Models\ExamRule::create([
                    'school_id'               => $schoolId,
                    'exam_id'                 => $examId,
                    'session_id'              => $sessionId,
                    'total_marks'             => $request->total_marks,
                    'pass_marks'              => $request->pass_marks,
                    'marks_distribution_note' => '{}',
                ]);
                return back()->with('status', 'Exam rule created!');
            }

            // Rule exists — update it
            \App\Models\ExamRule::where('id', $ruleId)->update([
                'total_marks' => $request->total_marks,
                'pass_marks'  => $request->pass_marks,
            ]);

            return back()->with('status', 'Exam rule updated!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExamRule  $examRule
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExamRule $examRule)
    {
        //
    }
}
