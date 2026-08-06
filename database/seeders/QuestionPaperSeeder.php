<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionPaper;
use App\Models\QuestionSection;
use App\Models\QuestionQuestion;
use App\Models\QuestionPaperTemplate;
use App\Models\School;
use App\Models\SchoolSession;
use App\Models\SchoolClass;
use App\Models\Course;
use App\Models\Exam;
use App\Models\User;

class QuestionPaperSeeder extends Seeder
{
    public function run(): void
    {
        $schools = School::all();
        if ($schools->isEmpty()) {
            return;
        }

        foreach ($schools as $school) {
            $session = SchoolSession::where('school_id', $school->id)->latest()->first();
            if (!$session) continue;

            $teacher = User::where('school_id', $school->id)->role(['teacher', 'class-teacher'])->first()
                ?? User::where('school_id', $school->id)->first();
            if (!$teacher) continue;

            $approver = User::where('school_id', $school->id)->role(['principal', 'academic-coordinator', 'admin', 'super-admin'])->first()
                ?? $teacher;

            $classes = SchoolClass::where('school_id', $school->id)->get();
            if ($classes->isEmpty()) continue;

            $courses = Course::where('school_id', $school->id)->get();
            $exam    = Exam::where('school_id', $school->id)->first();

            // Resolve named templates (seeded by QuestionPaperTemplateSeeder)
            $tmplClassic  = QuestionPaperTemplate::where('name', 'Classic School Header')->first();
            $tmplFormal   = QuestionPaperTemplate::where('name', 'Formal Board Exam')->first();
            $tmplMinimal  = QuestionPaperTemplate::where('name', 'Minimal Modern')->first();
            // Fallback: any template that exists
            $tmplFallback = $tmplClassic ?? $tmplFormal ?? $tmplMinimal ?? QuestionPaperTemplate::first();

            // ── 1. MATHEMATICS QUESTION PAPER ─────────────────────────────────────
            $mathCourse = $courses->firstWhere('course_name', 'Mathematics') ?? $courses->first();
            $targetClass = $classes[0];

            $mathPaper = QuestionPaper::updateOrCreate(
                ['school_id' => $school->id, 'title' => 'Mid-Term Board Examination 2026 - Mathematics & Calculus'],
                [
                    'template_id'  => ($tmplClassic ?? $tmplFallback)?->id,
                    'exam_id'      => $exam?->id,
                    'class_id'     => $targetClass->id,
                    'section_id'   => null,
                    'course_id'    => $mathCourse?->id,
                    'session_id'   => $session->id,
                    'exam_name'    => 'Mid-Term Examination 2026',
                    'subject'      => 'Mathematics',
                    'class_label'  => $targetClass->class_name,
                    'duration'     => '180 Minutes (3 Hours)',
                    'full_marks'   => 100,
                    'pass_marks'   => 40,
                    'exam_date'    => '2026-09-15',
                    'paper_size'   => 'A4',
                    'orientation'  => 'portrait',
                    'status'       => 'approved',
                    'created_by'   => $teacher->id,
                    'approved_by'  => $approver->id,
                    'approved_at'  => now(),
                ]
            );

            // Section A: Multiple Choice Questions
            $secA = QuestionSection::updateOrCreate(
                ['paper_id' => $mathPaper->id, 'title' => 'Group A: Multiple Choice Questions (MCQs)'],
                ['school_id' => $school->id, 'instructions' => 'Select the single correct choice for each question. Each question carries 1 mark.', 'total_marks' => 10, 'sort_order' => 1]
            );

            $mcqs = [
                [
                    'q' => 'What is the derivative of f(x) = x^3 + 4x^2 - 7x + 12 with respect to x?',
                    'opts' => ['A' => '3x^2 + 8x - 7', 'B' => 'x^2 + 8x + 7', 'C' => '3x^2 + 4x - 7', 'D' => '6x + 8'],
                    'ans' => 'A',
                    'ch' => 'Calculus & Derivatives',
                ],
                [
                    'q' => 'Evaluate the indefinite integral ∫ (2x + 5) dx.',
                    'opts' => ['A' => 'x^2 + 5x + C', 'B' => '2x^2 + 5x + C', 'C' => 'x^2 + C', 'D' => '2x^2 + C'],
                    'ans' => 'A',
                    'ch' => 'Integration',
                ],
                [
                    'q' => 'Calculate the determinant of matrix M = [[3, 4], [2, 5]].',
                    'opts' => ['A' => '7', 'B' => '12', 'C' => '23', 'D' => '15'],
                    'ans' => 'A',
                    'ch' => 'Matrices & Determinants',
                ],
                [
                    'q' => 'If sin(θ) = 3/5 in Quadrant I, what is cos(θ)?',
                    'opts' => ['A' => '4/5', 'B' => '-4/5', 'C' => '3/4', 'D' => '5/4'],
                    'ans' => 'A',
                    'ch' => 'Trigonometry',
                ],
                [
                    'q' => 'Find the sum of the first 20 terms of an AP with first term a = 2 and common difference d = 3.',
                    'opts' => ['A' => '610', 'B' => '590', 'C' => '620', 'D' => '580'],
                    'ans' => 'A',
                    'ch' => 'Sequences & Series',
                ]
            ];

            foreach ($mcqs as $idx => $m) {
                QuestionQuestion::updateOrCreate(
                    ['section_id' => $secA->id, 'numbering' => 'Q' . ($idx + 1)],
                    [
                        'school_id'       => $school->id,
                        'question_type'   => 'mcq',
                        'question_text'   => $m['q'],
                        'options'         => $m['opts'],
                        'correct_answer'  => $m['ans'],
                        'allocated_marks' => 2,
                        'difficulty'      => 'medium',
                        'chapter'         => $m['ch'],
                        'bloom_taxonomy'  => 'Understanding',
                        'sort_order'      => $idx + 1,
                    ]
                );
            }

            // Section B: Short Answer Questions
            $secB = QuestionSection::updateOrCreate(
                ['paper_id' => $mathPaper->id, 'title' => 'Group B: Short Conceptual Questions'],
                ['school_id' => $school->id, 'instructions' => 'Answer all questions concisely showing step-by-step mathematical reasoning.', 'total_marks' => 30, 'sort_order' => 2]
            );

            $shortQs = [
                ['number' => 'Q6', 'q' => 'Prove using vector geometry that the diagonals of a rhombus are perpendicular to each other.', 'marks' => 6, 'ch' => 'Vector Analysis'],
                ['number' => 'Q7', 'q' => 'Solve the quadratic equation 3x^2 - 14x + 8 = 0 using the method of factorization and quadratic formula.', 'marks' => 6, 'ch' => 'Algebraic Equations'],
                ['number' => 'Q8', 'q' => 'Find the equation of the straight line passing through (3, -4) and perpendicular to 2x + 5y - 7 = 0.', 'marks' => 6, 'ch' => 'Coordinate Geometry'],
                ['number' => 'Q9', 'q' => 'Calculate the mean and standard deviation for the given frequency distribution of student scores.', 'marks' => 6, 'ch' => 'Statistics & Data Analysis'],
                ['number' => 'Q10', 'q' => 'Evaluate the limit lim(x→0) (sin(5x) / x) using L\'Hôpital\'s Rule.', 'marks' => 6, 'ch' => 'Limits & Continuity'],
            ];

            foreach ($shortQs as $idx => $sq) {
                QuestionQuestion::updateOrCreate(
                    ['section_id' => $secB->id, 'numbering' => $sq['number']],
                    [
                        'school_id'       => $school->id,
                        'question_type'   => 'short_answer',
                        'question_text'   => $sq['q'],
                        'allocated_marks' => $sq['marks'],
                        'difficulty'      => 'medium',
                        'chapter'         => $sq['ch'],
                        'bloom_taxonomy'  => 'Applying',
                        'sort_order'      => $idx + 1,
                    ]
                );
            }

            // Section C: Analytical & Long Problems
            $secC = QuestionSection::updateOrCreate(
                ['paper_id' => $mathPaper->id, 'title' => 'Group C: Analytical & Comprehensive Problems'],
                ['school_id' => $school->id, 'instructions' => 'Show detailed step-by-step proofs, diagrams, and calculations. Answer all 4 questions.', 'total_marks' => 60, 'sort_order' => 3]
            );

            $longQs = [
                ['number' => 'Q11', 'q' => 'Solve the system of linear equations using Matrix Inversion Method:\n  2x + 3y - z = 7\n  x - y + 2z = 3\n  3x + y + z = 10', 'marks' => 15, 'ch' => 'Matrix Algebra'],
                ['number' => 'Q12', 'q' => 'Find the maximum and minimum values of f(x, y) = x^2 + y^2 subject to the linear constraint x + 2y = 5 using Lagrange Multipliers.', 'marks' => 15, 'ch' => 'Multivariate Calculus'],
                ['number' => 'Q13', 'q' => 'Calculate the total volume of the solid generated by revolving the parabolic region bounded by y = x^2 and y = 4 around the x-axis.', 'marks' => 15, 'ch' => 'Definite Integrals & Applications'],
                ['number' => 'Q14', 'q' => 'State and prove Bayes\' Theorem for conditional probability. An industrial test detects a defect with 95% accuracy. Calculate the probability of a true positive given 2% background defect rate.', 'marks' => 15, 'ch' => 'Probability Distributions'],
            ];

            foreach ($longQs as $idx => $lq) {
                QuestionQuestion::updateOrCreate(
                    ['section_id' => $secC->id, 'numbering' => $lq['number']],
                    [
                        'school_id'       => $school->id,
                        'question_type'   => 'long_answer',
                        'question_text'   => $lq['q'],
                        'allocated_marks' => $lq['marks'],
                        'difficulty'      => 'hard',
                        'chapter'         => $lq['ch'],
                        'bloom_taxonomy'  => 'Analyzing',
                        'sort_order'      => $idx + 1,
                    ]
                );
            }

            // Recalculate marks
            $secA->recalcMarks();
            $secB->recalcMarks();
            $secC->recalcMarks();


            // ── 2. PHYSICS QUESTION PAPER ─────────────────────────────────────────
            $phyCourse = $courses->firstWhere('course_name', 'Physics') ?? ($courses[1] ?? $courses->first());
            $targetClass2 = $classes[1] ?? $targetClass;

            $phyPaper = QuestionPaper::updateOrCreate(
                ['school_id' => $school->id, 'title' => 'Mid-Term Board Examination 2026 - General & Applied Physics'],
                [
                    'template_id'  => ($tmplFormal ?? $tmplFallback)?->id,
                    'exam_id'      => $exam?->id,
                    'class_id'     => $targetClass2->id,
                    'section_id'   => null,
                    'course_id'    => $phyCourse?->id,
                    'session_id'   => $session->id,
                    'exam_name'    => 'Mid-Term Examination 2026',
                    'subject'      => 'Physics',
                    'class_label'  => $targetClass2->class_name,
                    'duration'     => '180 Minutes (3 Hours)',
                    'full_marks'   => 100,
                    'pass_marks'   => 40,
                    'exam_date'    => '2026-09-16',
                    'paper_size'   => 'A4',
                    'orientation'  => 'portrait',
                    'status'       => 'approved',
                    'created_by'   => $teacher->id,
                    'approved_by'  => $approver->id,
                    'approved_at'  => now(),
                ]
            );

            $pSecA = QuestionSection::updateOrCreate(
                ['paper_id' => $phyPaper->id, 'title' => 'Section A: Objective Concepts & MCQs'],
                ['school_id' => $school->id, 'instructions' => 'Choose the single correct response for each question.', 'total_marks' => 20, 'sort_order' => 1]
            );

            $phyMcqs = [
                ['q' => 'Which law of thermodynamics defines the concept of absolute temperature?', 'opts' => ['A' => 'Zeroth Law', 'B' => 'First Law', 'C' => 'Second Law', 'D' => 'Third Law'], 'ans' => 'D', 'ch' => 'Thermodynamics'],
                ['q' => 'What is the speed of electromagnetic waves in a vacuum?', 'opts' => ['A' => '3 x 10^8 m/s', 'B' => '3 x 10^6 m/s', 'C' => '1.5 x 10^8 m/s', 'D' => '9.8 m/s^2'], 'ans' => 'A', 'ch' => 'Optics & Waves'],
                ['q' => 'The SI unit of magnetic flux density (B) is:', 'opts' => ['A' => 'Tesla', 'B' => 'Weber', 'C' => 'Henry', 'D' => 'Farad'], 'ans' => 'A', 'ch' => 'Electromagnetism'],
                ['q' => 'A projectile launched at 45 degrees achieves maximum:', 'opts' => ['A' => 'Horizontal Range', 'B' => 'Vertical Height', 'C' => 'Flight Time', 'D' => 'Acceleration'], 'ans' => 'A', 'ch' => 'Kinematics'],
            ];

            foreach ($phyMcqs as $idx => $pm) {
                QuestionQuestion::updateOrCreate(
                    ['section_id' => $pSecA->id, 'numbering' => 'Q' . ($idx + 1)],
                    [
                        'school_id'       => $school->id,
                        'question_type'   => 'mcq',
                        'question_text'   => $pm['q'],
                        'options'         => $pm['opts'],
                        'correct_answer'  => $pm['ans'],
                        'allocated_marks' => 5,
                        'difficulty'      => 'medium',
                        'chapter'         => $pm['ch'],
                        'bloom_taxonomy'  => 'Remembering',
                        'sort_order'      => $idx + 1,
                    ]
                );
            }

            $pSecB = QuestionSection::updateOrCreate(
                ['paper_id' => $phyPaper->id, 'title' => 'Section B: Theoretical Derivations & Problems'],
                ['school_id' => $school->id, 'instructions' => 'Answer all derivation and problem solving questions.', 'total_marks' => 80, 'sort_order' => 2]
            );

            $phyLongs = [
                ['number' => 'Q5', 'q' => 'State Bernoulli\'s Principle for fluid dynamics. Derive the Bernoulli equation for steady, incompressible, non-viscous fluid flow.', 'marks' => 20, 'ch' => 'Fluid Dynamics'],
                ['number' => 'Q6', 'q' => 'Derive Lens Maker\'s Formula for a thin convex lens: 1/f = (μ - 1)(1/R1 - 1/R2). Calculate focal length when R1 = +20cm, R2 = -30cm, and glass refractive index μ = 1.5.', 'marks' => 20, 'ch' => 'Optics'],
                ['number' => 'Q7', 'q' => 'Explain the working principle of a Carnot Heat Engine. Derive the formula for maximum efficiency η = 1 - (T2/T1). Calculate efficiency when operating between 600K and 300K.', 'marks' => 20, 'ch' => 'Thermodynamics'],
                ['number' => 'Q8', 'q' => 'State Faraday\'s Laws of Electromagnetic Induction and Lenz\'s Law. Derive the induced EMF in a coil rotating in a uniform magnetic field.', 'marks' => 20, 'ch' => 'Electromagnetism'],
            ];

            foreach ($phyLongs as $idx => $pl) {
                QuestionQuestion::updateOrCreate(
                    ['section_id' => $pSecB->id, 'numbering' => $pl['number']],
                    [
                        'school_id'       => $school->id,
                        'question_type'   => 'long_answer',
                        'question_text'   => $pl['q'],
                        'allocated_marks' => $pl['marks'],
                        'difficulty'      => 'hard',
                        'chapter'         => $pl['ch'],
                        'bloom_taxonomy'  => 'Analyzing',
                        'sort_order'      => $idx + 1,
                    ]
                );
            }

            $pSecA->recalcMarks();
            $pSecB->recalcMarks();


            // ── 3. COMPUTER SCIENCE QUESTION PAPER ───────────────────────────────
            $csCourse = $courses->firstWhere('course_name', 'Computer Science') ?? ($courses[2] ?? $courses->first());

            $csPaper = QuestionPaper::updateOrCreate(
                ['school_id' => $school->id, 'title' => 'Mid-Term Board Examination 2026 - Computer Science & Data Structures'],
                [
                    'template_id'  => ($tmplMinimal ?? $tmplFallback)?->id,
                    'exam_id'      => $exam?->id,
                    'class_id'     => $targetClass->id,
                    'section_id'   => null,
                    'course_id'    => $csCourse?->id,
                    'session_id'   => $session->id,
                    'exam_name'    => 'Mid-Term Examination 2026',
                    'subject'      => 'Computer Science',
                    'class_label'  => $targetClass->class_name,
                    'duration'     => '180 Minutes (3 Hours)',
                    'full_marks'   => 100,
                    'pass_marks'   => 40,
                    'exam_date'    => '2026-09-18',
                    'paper_size'   => 'A4',
                    'orientation'  => 'portrait',
                    'status'       => 'approved',
                    'created_by'   => $teacher->id,
                    'approved_by'  => $approver->id,
                    'approved_at'  => now(),
                ]
            );

            $cSecA = QuestionSection::updateOrCreate(
                ['paper_id' => $csPaper->id, 'title' => 'Group A: Programming & Database Fundamentals'],
                ['school_id' => $school->id, 'instructions' => 'Answer all short questions and SQL queries.', 'total_marks' => 40, 'sort_order' => 1]
            );

            $csShorts = [
                ['number' => 'Q1', 'q' => 'Differentiate between Stack and Queue data structures with real-world examples and Push/Pop vs Enqueue/Dequeue operations.', 'marks' => 10, 'ch' => 'Data Structures'],
                ['number' => 'Q2', 'q' => 'Write SQL queries for:\n  a) Create a students table with id, name, and roll_no.\n  b) Select all students scoring above 80 marks.\n  c) Update class_id to 10 for student ID 5.', 'marks' => 10, 'ch' => 'Relational Databases (SQL)'],
                ['number' => 'Q3', 'q' => 'Explain the concept of Object-Oriented Programming (OOP) principles: Encapsulation, Inheritance, Polymorphism, and Abstraction.', 'marks' => 10, 'ch' => 'Object Oriented Programming'],
                ['number' => 'Q4', 'q' => 'What is the difference between Primary Key, Foreign Key, and Unique Key in database design?', 'marks' => 10, 'ch' => 'Database Normalization'],
            ];

            foreach ($csShorts as $idx => $cs) {
                QuestionQuestion::updateOrCreate(
                    ['section_id' => $cSecA->id, 'numbering' => $cs['number']],
                    [
                        'school_id'       => $school->id,
                        'question_type'   => 'short_answer',
                        'question_text'   => $cs['q'],
                        'allocated_marks' => $cs['marks'],
                        'difficulty'      => 'medium',
                        'chapter'         => $cs['ch'],
                        'bloom_taxonomy'  => 'Applying',
                        'sort_order'      => $idx + 1,
                    ]
                );
            }

            $cSecB = QuestionSection::updateOrCreate(
                ['paper_id' => $csPaper->id, 'title' => 'Group B: Algorithms & System Architecture'],
                ['school_id' => $school->id, 'instructions' => 'Write complete code algorithms, flowcharts, and architectural breakdowns.', 'total_marks' => 60, 'sort_order' => 2]
            );

            $csLongs = [
                ['number' => 'Q5', 'q' => 'Write a complete recursive algorithm or C++/Python program to implement Binary Search on a sorted array of N elements. Explain time complexity O(log N).', 'marks' => 20, 'ch' => 'Searching & Sorting Algorithms'],
                ['number' => 'Q6', 'q' => 'Explain the 3-tier Web Application Architecture (Client/UI, Application/Logic Server, Database). Detail HTTP GET vs POST request/response cycles.', 'marks' => 20, 'ch' => 'Web Technologies & APIs'],
                ['number' => 'Q7', 'q' => 'Write an algorithm to insert a new node into a Binary Search Tree (BST) and perform In-Order traversal.', 'marks' => 20, 'ch' => 'Trees & Graphs'],
            ];

            foreach ($csLongs as $idx => $cl) {
                QuestionQuestion::updateOrCreate(
                    ['section_id' => $cSecB->id, 'numbering' => $cl['number']],
                    [
                        'school_id'       => $school->id,
                        'question_type'   => 'long_answer',
                        'question_text'   => $cl['q'],
                        'allocated_marks' => $cl['marks'],
                        'difficulty'      => 'hard',
                        'chapter'         => $cl['ch'],
                        'bloom_taxonomy'  => 'Creating',
                        'sort_order'      => $idx + 1,
                    ]
                );
            }

            $cSecA->recalcMarks();
            $cSecB->recalcMarks();
        }

        echo "Question Papers successfully seeded with full sections, MCQs, and long questions!" . PHP_EOL;
    }
}
