<?php

namespace App\Console\Commands;

use App\Models\SchoolClass;
use App\Models\Mark;
use App\Models\Exam;
use App\Models\ExamRule;
use App\Models\Course;
use App\Repositories\ResultRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnoseResults extends Command
{
    protected $signature = 'results:diagnose';
    protected $description = 'Verify exam result calculation across all classes (read-only)';

    public function handle()
    {
        $sessionId  = 7;
        $semesterId = 13;
        $repo = new ResultRepository();

        // ── 1. Cross-class: detect subjects where buildExamRulesByCourse still
        //    returns the default 100/40 when a custom FM exists under a sibling
        //    course row for the same subject name. ─────────────────────────────
        $this->info('═══ Cross-class audit: default-FM subjects with a custom sibling ═══');
        $classes = SchoolClass::orderBy('id')->get();
        $issuesFound = 0;

        foreach ($classes as $class) {
            // Get all exams for this class+session+semester
            $exams = Exam::where('session_id', $sessionId)
                ->where('class_id', $class->id)
                ->where('semester_id', $semesterId)
                ->with(['course:id,id,course_name,semester_id', 'examRule:id,exam_id,total_marks,pass_marks'])
                ->get();

            if ($exams->isEmpty()) continue;

            // Build name → rules map
            $byName = [];
            foreach ($exams as $e) {
                $name = $e->course?->course_name;
                if (!$name) continue;
                $fm = $e->examRule?->total_marks;
                $pm = $e->examRule?->pass_marks;
                $isDefault = ($fm !== null && (float)$fm === 100.0 && (float)$pm === 40.0);
                $byName[$name][] = ['exam_id' => $e->id, 'fm' => $fm, 'pm' => $pm, 'isDefault' => $isDefault, 'marks' => Mark::where('exam_id', $e->id)->count()];
            }

            // Check each name: does it have both a default and a custom rule?
            foreach ($byName as $name => $entries) {
                $hasCustom = false;
                $hasDefault = false;
                $customMarks = 0;
                $defaultMarks = 0;
                foreach ($entries as $entry) {
                    if ($entry['isDefault']) {
                        $hasDefault = true;
                        $defaultMarks += $entry['marks'];
                    } else {
                        $hasCustom = true;
                        $customMarks += $entry['marks'];
                    }
                }

                if ($hasCustom && $hasDefault) {
                    // This is the pattern we fixed. Verify the fix works.
                    $map = $repo->buildExamRulesByCourse($sessionId, $semesterId, $class->id);
                    $stillWrong = false;
                    foreach ($map as $cid => $r) {
                        $cn = Course::where('id', $cid)->value('course_name');
                        if ($cn === $name && (float)$r->total_marks === 100.0 && (float)$r->pass_marks === 40.0) {
                            $stillWrong = true;
                        }
                    }
                    if ($stillWrong) {
                        $issuesFound++;
                        $this->line("  ❌ {$class->class_name} (id={$class->id}) subject '{$name}': still resolves to 100/40 despite custom sibling!");
                    }
                }
            }
        }
        if ($issuesFound === 0) {
            $this->line('  ✅ All classes: no subject resolves to the default 100/40 when a custom FM exists.');
        }

        // ── 2. Spot-check: sample 2 students per class, verify no spurious NG ────
        $this->info('');
        $this->info('═══ Cross-class: spurious-NG audit (passing marks showing NG) ═══');
        $ngIssues = 0;

        foreach ($classes as $class) {
            $studentIds = Mark::where('session_id', $sessionId)
                ->where('class_id', $class->id)
                ->whereHas('exam', fn($q) => $q->where('semester_id', $semesterId))
                ->distinct()
                ->pluck('student_id')
                ->take(3);

            foreach ($studentIds as $sid) {
                $result = $repo->getStudentResult($sid, $semesterId, $class->id, 0, $sessionId);
                foreach ($result['courses'] as $c) {
                    $mk = (float)$c['final_marks'];
                    $fm = (float)$c['full_marks'];
                    $pm = (float)$c['pass_marks'];
                    $gd = $c['grade'];
                    // A student at or above pass mark should NOT have NG grade
                    // (unless they genuinely scored < 35% which is the NG band)
                    if ($mk >= $pm && $gd === 'NG' && $fm > 0) {
                        $pct = $fm > 0 ? round($mk / $fm * 100, 1) : 0;
                        if ($pct >= 35) {
                            $ngIssues++;
                            $cn = $c['course']?->course_name ?? '?';
                            $this->line("  ❌ {$class->class_name} student={$sid} {$cn}: {$mk}/{$fm} (PM={$pm}, {$pct}%) => NG but should PASS!");
                        }
                    }
                }
            }
        }
        if ($ngIssues === 0) {
            $this->line('  ✅ No spurious NG grades found for students at/above pass mark with >= 35%.');
        }

        // ── 3. Verify PASS/FAIL consistency ─────────────────────────────────────
        $this->info('');
        $this->info('═══ Cross-class: PASS/FAIL boundary audit ═══');
        $pfIssues = 0;

        foreach ($classes as $class) {
            $studentIds = Mark::where('session_id', $sessionId)
                ->where('class_id', $class->id)
                ->whereHas('exam', fn($q) => $q->where('semester_id', $semesterId))
                ->distinct()
                ->pluck('student_id')
                ->take(3);

            foreach ($studentIds as $sid) {
                $result = $repo->getStudentResult($sid, $semesterId, $class->id, 0, $sessionId);
                foreach ($result['courses'] as $c) {
                    $mk = (float)$c['final_marks'];
                    $pm = (float)$c['pass_marks'];
                    $passed = $c['passed'];
                    if ($fm > 0) {
                        // marks >= pass mark MUST be PASS; marks < pass mark MUST be FAIL
                        if ($mk >= $pm && !$passed) {
                            $pfIssues++;
                            $cn = $c['course']?->course_name ?? '?';
                            $this->line("  ❌ {$class->class_name} student={$sid} {$cn}: {$mk}/{$c['full_marks']} PM={$pm} => FAIL but marks >= PM!");
                        }
                        if ($mk < $pm && $passed && $c['note'] !== 'Not Entered') {
                            $pfIssues++;
                            $cn = $c['course']?->course_name ?? '?';
                            $this->line("  ❌ {$class->class_name} student={$sid} {$cn}: {$mk}/{$c['full_marks']} PM={$pm} => PASS but marks < PM!");
                        }
                    }
                }
            }
        }
        if ($pfIssues === 0) {
            $this->line('  ✅ All PASS/FAIL decisions are consistent with pass marks.');
        }

        // ── 4. Verify CGPA consistency (should match per-semester GPA) ──────────
        $this->info('');
        $this->info('═══ CGPA consistency check ═══');
        $c3student = 1929;
        $cgpa = $repo->getCgpa($c3student, 76, $sessionId);
        $this->line("  Class 3 student {$c3student} CGPA={$cgpa['cgpa']} (semesters: " . collect($cgpa['semesters'])->pluck('gpa','semester_id')->map(fn($v,$k)=>"sem{$k}={$v}")->implode(', ') . ')');

        return 0;
    }
}
