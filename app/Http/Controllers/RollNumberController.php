<?php

namespace App\Http\Controllers;

use App\Models\AcademicSetting;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\TerminalRollNumber;
use App\Models\User;
use App\Repositories\ResultRepository;
use App\Traits\SchoolSession as SchoolSessionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RollNumberController extends Controller
{
    use SchoolSessionTrait;

    public function __construct()
    {
        $this->middleware(['can:edit users']);
    }

    /**
     * Display Roll Number Management dashboard for class/section.
     */
    public function index(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $rollNumberMode = Setting::get('roll_number_mode', 'yearly');

        $classes = SchoolClass::where('session_id', $sessionId)->orderBy('class_name')->get();
        $classId = (int) $request->query('class_id', $classes->first()?->id ?? 0);

        $sections = collect();
        if ($classId) {
            $sections = Section::where('class_id', $classId)->orderBy('section_name')->get();
        }
        $sectionId = (int) $request->query('section_id', $sections->first()?->id ?? 0);

        $semesters = Semester::where('session_id', $sessionId)->orderBy('id')->get();
        $semesterId = (int) $request->query('semester_id', $semesters->first()?->id ?? 0);

        $promotions = collect();
        if ($classId && $sectionId) {
            $promotions = Promotion::with(['student.academic_info'])
                ->where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->get();

            // If terminal-wise mode is active and a semester is selected, fetch terminal roll numbers
            if ($rollNumberMode === 'terminal' && $semesterId > 0) {
                $termRolls = TerminalRollNumber::where('session_id', $sessionId)
                    ->where('semester_id', $semesterId)
                    ->where('class_id', $classId)
                    ->where('section_id', $sectionId)
                    ->get()
                    ->keyBy('student_id');

                foreach ($promotions as $promo) {
                    $termRecord = $termRolls->get($promo->student_id);
                    $promo->terminal_roll_no = $termRecord?->roll_no;
                    $promo->source_rank      = $termRecord?->rank;
                    $promo->source_terminal  = $termRecord?->sourceSemester?->semester_name;
                    $promo->effective_roll   = $promo->terminal_roll_no ?? $promo->roll_no;
                }

                $promotions = $promotions->sortBy(function ($p) {
                    $val = $p->effective_roll;
                    if ($val !== null && is_numeric($val)) {
                        return (int)$val;
                    }
                    return $p->student?->first_name ?? '';
                })->values();
            } else {
                foreach ($promotions as $promo) {
                    $promo->effective_roll = $promo->roll_no;
                }

                $promotions = $promotions->sortBy(function ($p) {
                    if ($p->roll_no !== null && is_numeric($p->roll_no)) {
                        return (int)$p->roll_no;
                    }
                    return $p->student?->first_name ?? '';
                })->values();
            }
        }

        $selectedClass    = $classes->firstWhere('id', $classId);
        $selectedSection  = $sections->firstWhere('id', $sectionId);
        $selectedSemester = $semesters->firstWhere('id', $semesterId);

        return view('roll-numbers.index', compact(
            'classes',
            'sections',
            'promotions',
            'classId',
            'sectionId',
            'selectedClass',
            'selectedSection',
            'sessionId',
            'rollNumberMode',
            'semesters',
            'semesterId',
            'selectedSemester'
        ));
    }

    /**
     * Bulk update roll numbers manually.
     */
    public function updateBulk(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|exists:school_classes,id',
            'section_id'  => 'required|exists:sections,id',
            'semester_id' => 'nullable|integer',
            'roll_no'     => 'required|array',
            'roll_no.*'   => 'nullable|string|max:30',
        ]);

        $sessionId      = $this->getSchoolCurrentSession();
        $rollNos        = $request->input('roll_no', []);
        $semesterId     = (int) $request->input('semester_id', 0);
        $rollNumberMode = Setting::get('roll_number_mode', 'yearly');
        $schoolId       = config('tenant.school_id') ?? auth()->user()?->school_id;

        DB::transaction(function () use ($sessionId, $request, $rollNos, $semesterId, $rollNumberMode, $schoolId) {
            foreach ($rollNos as $studentId => $rollNo) {
                $trimmed = !is_null($rollNo) && trim($rollNo) !== '' ? trim($rollNo) : null;

                // Update session promotion record
                Promotion::where('session_id', $sessionId)
                    ->where('class_id', $request->class_id)
                    ->where('section_id', $request->section_id)
                    ->where('student_id', $studentId)
                    ->update(['roll_no' => $trimmed]);

                // Update user profile
                User::where('id', $studentId)->update(['roll_no' => $trimmed]);

                // If in terminal mode and a target semester was specified, upsert terminal roll number
                if ($rollNumberMode === 'terminal' && $semesterId > 0 && $trimmed !== null) {
                    TerminalRollNumber::updateOrCreate(
                        [
                            'session_id'  => $sessionId,
                            'semester_id' => $semesterId,
                            'student_id'  => $studentId,
                        ],
                        [
                            'school_id'  => $schoolId,
                            'class_id'   => $request->class_id,
                            'section_id' => $request->section_id,
                            'roll_no'    => $trimmed,
                        ]
                    );
                }
            }
        });

        return back()->with('status', 'Roll numbers updated successfully!');
    }

    /**
     * Auto-assign sequential roll numbers based on sorting rules.
     */
    public function autoAssign(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|exists:school_classes,id',
            'section_id'  => 'required|exists:sections,id',
            'semester_id' => 'nullable|integer',
            'sort_by'     => 'required|in:name_asc,name_desc,admission_no,id_card_number',
            'start_no'    => 'required|integer|min:1',
            'prefix'      => 'nullable|string|max:10',
            'pad_length'  => 'nullable|integer|min:0|max:5',
        ]);

        $sessionId      = $this->getSchoolCurrentSession();
        $classId        = $request->class_id;
        $sectionId      = $request->section_id;
        $semesterId     = (int) $request->input('semester_id', 0);
        $sortBy         = $request->sort_by;
        $startNo        = (int) $request->start_no;
        $prefix         = $request->prefix ?? '';
        $padLength      = (int) ($request->pad_length ?? 0);
        $rollNumberMode = Setting::get('roll_number_mode', 'yearly');
        $schoolId       = config('tenant.school_id') ?? auth()->user()?->school_id;

        $promotions = Promotion::with(['student.academic_info'])
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->get();

        if ($promotions->isEmpty()) {
            return back()->with('error', 'No students found in the selected class and section.');
        }

        // Sort promotions
        $sorted = $promotions->sort(function ($a, $b) use ($sortBy) {
            switch ($sortBy) {
                case 'name_desc':
                    return strcasecmp($b->student?->first_name . ' ' . $b->student?->last_name, $a->student?->first_name . ' ' . $a->student?->last_name);
                case 'admission_no':
                    return strcasecmp($a->student?->academic_info?->admission_no ?? '', $b->student?->academic_info?->admission_no ?? '');
                case 'id_card_number':
                    return strcasecmp($a->id_card_number ?? '', $b->id_card_number ?? '');
                case 'name_asc':
                default:
                    return strcasecmp($a->student?->first_name . ' ' . $a->student?->last_name, $b->student?->first_name . ' ' . $b->student?->last_name);
            }
        })->values();

        DB::transaction(function () use ($sorted, $startNo, $prefix, $padLength, $sessionId, $classId, $sectionId, $semesterId, $rollNumberMode, $schoolId) {
            $currentNum = $startNo;
            foreach ($sorted as $promo) {
                $numberPart = $padLength > 0 ? sprintf("%0{$padLength}d", $currentNum) : (string) $currentNum;
                $generatedRoll = $prefix . $numberPart;

                $promo->update(['roll_no' => $generatedRoll]);
                User::where('id', $promo->student_id)->update(['roll_no' => $generatedRoll]);

                if ($rollNumberMode === 'terminal' && $semesterId > 0) {
                    TerminalRollNumber::updateOrCreate(
                        [
                            'session_id'  => $sessionId,
                            'semester_id' => $semesterId,
                            'student_id'  => $promo->student_id,
                        ],
                        [
                            'school_id'  => $schoolId,
                            'class_id'   => $classId,
                            'section_id' => $sectionId,
                            'roll_no'    => $generatedRoll,
                        ]
                    );
                }

                $currentNum++;
            }
        });

        return back()->with('status', "Successfully auto-assigned roll numbers to {$sorted->count()} students!");
    }

    /**
     * Show Roll Number Reassignment by Terminal Exam Rank dashboard & preview.
     */
    public function showReassign(Request $request)
    {
        $allSessions = SchoolSession::orderByDesc('id')->get();
        $currentSessionId = $this->getSchoolCurrentSession();

        $sessionId = (int) $request->query('session_id', $currentSessionId);
        $selectedSession = $allSessions->firstWhere('id', $sessionId) ?? $allSessions->first();
        if ($selectedSession) {
            $sessionId = $selectedSession->id;
        }

        $classes = SchoolClass::where('session_id', $sessionId)->orderBy('class_name')->get();
        $classId = (int) $request->query('class_id', $classes->first()?->id ?? 0);

        $sections = collect();
        if ($classId) {
            $sections = Section::where('class_id', $classId)->orderBy('section_name')->get();
        }
        $sectionId = (int) $request->query('section_id', 0); // 0 = all sections

        $semesters = Semester::where('session_id', $sessionId)->orderBy('id')->get();

        // Default source semester: Terminal 1 or first semester
        $sourceSemesterId = (int) $request->query('source_semester_id', $semesters->first()?->id ?? 0);

        // Default target semester: next semester after source, or 2nd semester
        $defaultTargetId = 0;
        if ($sourceSemesterId) {
            $foundSource = false;
            foreach ($semesters as $sem) {
                if ($foundSource) {
                    $defaultTargetId = $sem->id;
                    break;
                }
                if ($sem->id == $sourceSemesterId) {
                    $foundSource = true;
                }
            }
        }
        if (!$defaultTargetId) {
            $defaultTargetId = $semesters->count() > 1 ? $semesters->skip(1)->first()?->id : $semesters->first()?->id ?? 0;
        }

        $targetSemesterId = (int) $request->query('target_semester_id', $defaultTargetId);

        // Reassignment settings
        $startNo            = max(1, (int) $request->query('start_no', 1));
        $prefix             = (string) $request->query('prefix', '');
        $padLength          = max(0, min(5, (int) $request->query('pad_length', 0)));
        $unrankedRule       = $request->query('unranked_rule', 'end_by_name');
        $scope              = $request->query('scope', 'section'); // 'section' or 'class'
        $updateActiveRoster = $request->query('update_active_roster', '1') !== '0';

        $selectedClass    = $classes->firstWhere('id', $classId);
        $selectedSection  = $sections->firstWhere('id', $sectionId);
        $sourceSemester   = $semesters->firstWhere('id', $sourceSemesterId);
        $targetSemester   = $semesters->firstWhere('id', $targetSemesterId);
        $rollNumberMode   = Setting::get('roll_number_mode', 'yearly');

        // Calculate preview data
        $previewData = $this->calculateReassignment(
            $sessionId,
            $classId,
            $sectionId,
            $sourceSemesterId,
            $targetSemesterId,
            $startNo,
            $prefix,
            $padLength,
            $unrankedRule,
            $scope
        );

        return view('roll-numbers.reassign', compact(
            'allSessions',
            'sessionId',
            'selectedSession',
            'classes',
            'classId',
            'selectedClass',
            'sections',
            'sectionId',
            'selectedSection',
            'semesters',
            'sourceSemesterId',
            'targetSemesterId',
            'sourceSemester',
            'targetSemester',
            'startNo',
            'prefix',
            'padLength',
            'unrankedRule',
            'scope',
            'updateActiveRoster',
            'rollNumberMode',
            'previewData'
        ));
    }

    /**
     * Apply roll number reassignment to the database.
     */
    public function applyReassign(Request $request)
    {
        $request->validate([
            'session_id'           => 'required|exists:school_sessions,id',
            'class_id'             => 'required|exists:school_classes,id',
            'section_id'           => 'nullable|integer',
            'source_semester_id'   => 'required|exists:semesters,id|different:target_semester_id',
            'target_semester_id'   => 'required|exists:semesters,id',
            'roll_no'              => 'required|array',
            'roll_no.*'            => 'nullable|string|max:30',
            'meta'                 => 'nullable|array',
            'update_active_roster' => 'nullable|boolean',
        ]);

        $sessionId          = (int) $request->session_id;
        $classId            = (int) $request->class_id;
        $targetSemesterId   = (int) $request->target_semester_id;
        $sourceSemesterId   = (int) $request->source_semester_id;
        $rolls              = $request->input('roll_no', []);
        $meta               = $request->input('meta', []);
        $updateActiveRoster = $request->boolean('update_active_roster', true);
        $schoolId           = config('tenant.school_id') ?? auth()->user()?->school_id;

        $targetSemester = Semester::find($targetSemesterId);
        $sourceSemester = Semester::find($sourceSemesterId);
        $schoolClass    = SchoolClass::find($classId);

        $appliedCount = 0;

        DB::transaction(function () use (
            $sessionId,
            $classId,
            $targetSemesterId,
            $sourceSemesterId,
            $rolls,
            $meta,
            $updateActiveRoster,
            $schoolId,
            &$appliedCount
        ) {
            foreach ($rolls as $studentId => $rollNo) {
                $trimmed = !is_null($rollNo) && trim($rollNo) !== '' ? trim($rollNo) : null;
                if ($trimmed === null) {
                    continue;
                }

                $studentMeta  = $meta[$studentId] ?? [];
                $studentSecId = (int) ($studentMeta['section_id'] ?? 0);

                // Upsert Terminal Roll Number record
                TerminalRollNumber::updateOrCreate(
                    [
                        'session_id'  => $sessionId,
                        'semester_id' => $targetSemesterId,
                        'student_id'  => $studentId,
                    ],
                    [
                        'school_id'          => $schoolId,
                        'class_id'           => $classId,
                        'section_id'         => $studentSecId > 0 ? $studentSecId : 0,
                        'roll_no'            => $trimmed,
                        'source_semester_id' => $sourceSemesterId,
                        'rank'               => !empty($studentMeta['rank']) ? (int) $studentMeta['rank'] : null,
                        'total_marks'        => isset($studentMeta['total_marks']) && $studentMeta['total_marks'] !== '' ? (float) $studentMeta['total_marks'] : null,
                        'gpa'                => isset($studentMeta['gpa']) && $studentMeta['gpa'] !== '' ? (float) $studentMeta['gpa'] : null,
                        'percentage'         => isset($studentMeta['percentage']) && $studentMeta['percentage'] !== '' ? (float) $studentMeta['percentage'] : null,
                    ]
                );

                // Update active roster in promotions & users
                if ($updateActiveRoster) {
                    Promotion::where('session_id', $sessionId)
                        ->where('class_id', $classId)
                        ->where('student_id', $studentId)
                        ->update(['roll_no' => $trimmed]);

                    User::where('id', $studentId)->update(['roll_no' => $trimmed]);
                }

                $appliedCount++;
            }
        });

        $sourceName = $sourceSemester?->semester_name ?? 'Source Terminal';
        $targetName = $targetSemester?->semester_name ?? 'Target Terminal';
        $className  = $schoolClass?->class_name ?? 'Selected Class';

        return redirect()->route('roll-numbers.reassign', [
            'session_id'           => $sessionId,
            'class_id'             => $classId,
            'section_id'           => $request->section_id ?? 0,
            'source_semester_id'   => $sourceSemesterId,
            'target_semester_id'   => $targetSemesterId,
            'update_active_roster' => $updateActiveRoster ? '1' : '0',
        ])->with('status', "Success! Reassigned roll numbers for {$appliedCount} students in {$className} for {$targetName} based on {$sourceName} exam rankings.");
    }

    /**
     * Compute proposed roll numbers for preview.
     */
    protected function calculateReassignment(
        int $sessionId,
        int $classId,
        int $sectionId,
        int $sourceSemesterId,
        int $targetSemesterId,
        int $startNo,
        string $prefix,
        int $padLength,
        string $unrankedRule,
        string $scope
    ): array {
        if ($classId <= 0 || $sourceSemesterId <= 0 || $targetSemesterId <= 0) {
            return [
                'groups'         => [],
                'total_students' => 0,
                'ranked_count'   => 0,
                'unranked_count' => 0,
                'has_exam_data'  => false,
            ];
        }

        $allSections = Section::where('class_id', $classId)->orderBy('section_name')->get();

        if ($sectionId > 0) {
            $sectionsToProcess = $allSections->where('id', $sectionId);
        } else {
            $sectionsToProcess = $scope === 'section' && $allSections->isNotEmpty()
                ? $allSections
                : collect([null]); // class-wide
        }

        $resultRepo     = new ResultRepository();
        $previewGroups  = [];
        $totalStudents  = 0;
        $rankedCount    = 0;
        $unrankedCount  = 0;
        $overallHasData = false;

        foreach ($sectionsToProcess as $section) {
            $currentSecId = $section?->id ?? 0;

            // Fetch promotions
            $promoQuery = Promotion::with(['student.academic_info'])
                ->where('session_id', $sessionId)
                ->where('class_id', $classId);

            if ($currentSecId > 0) {
                $promoQuery->where('section_id', $currentSecId);
            }

            $promotions = $promoQuery->get();
            if ($promotions->isEmpty()) {
                continue;
            }

            // Fetch exam results from source semester
            $examResults = $resultRepo->getClassResults($sourceSemesterId, $classId, $currentSecId, $sessionId);
            $hasData = $examResults->isNotEmpty();
            if ($hasData) {
                $overallHasData = true;
            }

            // Sort exam results: rank ASC, tie-breaker student name ASC
            $sortedResults = $examResults->sort(function ($a, $b) {
                if ($a['rank'] !== $b['rank']) {
                    return $a['rank'] <=> $b['rank'];
                }
                $nameA = trim(($a['student']?->first_name ?? '') . ' ' . ($a['student']?->last_name ?? ''));
                $nameB = trim(($b['student']?->first_name ?? '') . ' ' . ($b['student']?->last_name ?? ''));
                return strcasecmp($nameA, $nameB);
            })->values();

            // Fetch existing terminal roll numbers for comparison
            $existingTargetRolls = TerminalRollNumber::where('session_id', $sessionId)
                ->where('semester_id', $targetSemesterId)
                ->where('class_id', $classId)
                ->when($currentSecId > 0, fn($q) => $q->where('section_id', $currentSecId))
                ->get()
                ->keyBy('student_id');

            $rows             = [];
            $rankedStudentIds = [];
            $currNum          = $startNo;

            // 1. Process ranked students
            foreach ($sortedResults as $res) {
                $studentId = $res['student']->id;
                $promo = $promotions->firstWhere('student_id', $studentId);
                if (!$promo) {
                    continue;
                }

                $rankedStudentIds[] = $studentId;

                $numberPart = $padLength > 0 ? sprintf("%0{$padLength}d", $currNum) : (string) $currNum;
                $newRoll    = $prefix . $numberPart;

                // Old roll number (current assigned target roll or promo roll)
                $oldRoll = $existingTargetRolls->get($studentId)?->roll_no ?? $promo->roll_no;

                // Movement trend
                $trend     = 'same';
                $trendDiff = 0;
                if ($oldRoll !== null && is_numeric($oldRoll) && is_numeric($newRoll)) {
                    $diff = (int)$oldRoll - (int)$newRoll;
                    if ($diff > 0) {
                        $trend     = 'up';
                        $trendDiff = $diff;
                    } elseif ($diff < 0) {
                        $trend     = 'down';
                        $trendDiff = abs($diff);
                    }
                }

                $rows[] = [
                    'student'      => $res['student'],
                    'student_id'   => $studentId,
                    'promotion'    => $promo,
                    'section_id'   => $promo->section_id,
                    'section_name' => $section?->section_name ?? ($promo->section?->section_name ?? 'Default'),
                    'old_roll_no'  => $oldRoll,
                    'new_roll_no'  => $newRoll,
                    'rank'         => $res['rank'],
                    'total_marks'  => $res['totalMarks'] ?? 0,
                    'total_max'    => $res['totalMax'] ?? 0,
                    'percentage'   => $res['percentage'] ?? 0,
                    'gpa'          => $res['gpa'] ?? 0,
                    'passed'       => $res['passed'] ?? 0,
                    'failed'       => $res['failed'] ?? 0,
                    'is_ranked'    => true,
                    'trend'        => $trend,
                    'trend_diff'   => $trendDiff,
                ];

                $currNum++;
                $rankedCount++;
            }

            // 2. Process unranked students (enrolled in promotion but no marks in source semester)
            $unrankedPromos = $promotions->whereNotIn('student_id', $rankedStudentIds);

            if ($unrankedRule !== 'keep_existing') {
                $sortedUnranked = $unrankedPromos->sort(function ($a, $b) use ($unrankedRule) {
                    if ($unrankedRule === 'end_by_roll') {
                        $rollA = is_numeric($a->roll_no) ? (int)$a->roll_no : 9999;
                        $rollB = is_numeric($b->roll_no) ? (int)$b->roll_no : 9999;
                        return $rollA <=> $rollB;
                    }
                    return strcasecmp(
                        ($a->student?->first_name ?? '') . ' ' . ($a->student?->last_name ?? ''),
                        ($b->student?->first_name ?? '') . ' ' . ($b->student?->last_name ?? '')
                    );
                });

                foreach ($sortedUnranked as $promo) {
                    $numberPart = $padLength > 0 ? sprintf("%0{$padLength}d", $currNum) : (string) $currNum;
                    $newRoll    = $prefix . $numberPart;

                    $oldRoll = $existingTargetRolls->get($promo->student_id)?->roll_no ?? $promo->roll_no;

                    $trend     = 'same';
                    $trendDiff = 0;
                    if ($oldRoll !== null && is_numeric($oldRoll) && is_numeric($newRoll)) {
                        $diff = (int)$oldRoll - (int)$newRoll;
                        if ($diff > 0) {
                            $trend     = 'up';
                            $trendDiff = $diff;
                        } elseif ($diff < 0) {
                            $trend     = 'down';
                            $trendDiff = abs($diff);
                        }
                    }

                    $rows[] = [
                        'student'      => $promo->student,
                        'student_id'   => $promo->student_id,
                        'promotion'    => $promo,
                        'section_id'   => $promo->section_id,
                        'section_name' => $section?->section_name ?? ($promo->section?->section_name ?? 'Default'),
                        'old_roll_no'  => $oldRoll,
                        'new_roll_no'  => $newRoll,
                        'rank'         => null,
                        'total_marks'  => null,
                        'total_max'    => null,
                        'percentage'   => null,
                        'gpa'          => null,
                        'passed'       => null,
                        'failed'       => null,
                        'is_ranked'    => false,
                        'trend'        => $trend,
                        'trend_diff'   => $trendDiff,
                    ];

                    $currNum++;
                    $unrankedCount++;
                }
            } else {
                foreach ($unrankedPromos as $promo) {
                    $oldRoll = $existingTargetRolls->get($promo->student_id)?->roll_no ?? $promo->roll_no;

                    $rows[] = [
                        'student'      => $promo->student,
                        'student_id'   => $promo->student_id,
                        'promotion'    => $promo,
                        'section_id'   => $promo->section_id,
                        'section_name' => $section?->section_name ?? ($promo->section?->section_name ?? 'Default'),
                        'old_roll_no'  => $oldRoll,
                        'new_roll_no'  => $oldRoll,
                        'rank'         => null,
                        'total_marks'  => null,
                        'total_max'    => null,
                        'percentage'   => null,
                        'gpa'          => null,
                        'passed'       => null,
                        'failed'       => null,
                        'is_ranked'    => false,
                        'trend'        => 'same',
                        'trend_diff'   => 0,
                    ];

                    $unrankedCount++;
                }
            }

            $totalStudents += count($rows);

            $previewGroups[] = [
                'section'   => $section,
                'rows'      => $rows,
                'has_data'  => $hasData,
                'top_score' => $rows[0]['total_marks'] ?? null,
                'top_gpa'   => $rows[0]['gpa'] ?? null,
            ];
        }

        return [
            'groups'         => $previewGroups,
            'total_students' => $totalStudents,
            'ranked_count'   => $rankedCount,
            'unranked_count' => $unrankedCount,
            'has_exam_data'  => $overallHasData,
        ];
    }
}
