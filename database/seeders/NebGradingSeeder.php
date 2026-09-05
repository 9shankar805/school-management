<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradingSystem;
use App\Models\GradeRule;
use App\Models\SchoolClass;
use App\Models\Semester;

/**
 * NebGradingSeeder
 *
 * Seeds NEB (National Examinations Board) standard grading systems for:
 *   1. SEE — Secondary Education Examination (Grade 10 / कक्षा १०)
 *   2. Grade 11 & 12 — Higher Secondary (NEB +2)
 *
 * NEB official grade bands (published in NEB Examination Regulations):
 *   A+  = 90–100   GPA 4.0   Outstanding
 *   A   = 80–89.99 GPA 3.6   Excellent
 *   B+  = 70–79.99 GPA 3.2   Very Good
 *   B   = 60–69.99 GPA 2.8   Good
 *   C+  = 50–59.99 GPA 2.4   Satisfactory
 *   C   = 40–49.99 GPA 2.0   Acceptable
 *   D   = 35–39.99 GPA 1.6   Partially Acceptable (basic pass for SEE)
 *   NG  =  0–34.99 GPA 0.0   Not Graded (fail)
 *
 * Running:
 *   php artisan db:seed --class=NebGradingSeeder
 */
class NebGradingSeeder extends Seeder
{
    /** NEB official grade bands */
    private array $nebRules = [
        ['grade' => 'A+', 'point' => 4.0, 'start_at' => 90,   'end_at' => 100,   'remark' => 'Outstanding'],
        ['grade' => 'A',  'point' => 3.6, 'start_at' => 80,   'end_at' => 89.99, 'remark' => 'Excellent'],
        ['grade' => 'B+', 'point' => 3.2, 'start_at' => 70,   'end_at' => 79.99, 'remark' => 'Very Good'],
        ['grade' => 'B',  'point' => 2.8, 'start_at' => 60,   'end_at' => 69.99, 'remark' => 'Good'],
        ['grade' => 'C+', 'point' => 2.4, 'start_at' => 50,   'end_at' => 59.99, 'remark' => 'Satisfactory'],
        ['grade' => 'C',  'point' => 2.0, 'start_at' => 40,   'end_at' => 49.99, 'remark' => 'Acceptable'],
        ['grade' => 'D',  'point' => 1.6, 'start_at' => 35,   'end_at' => 39.99, 'remark' => 'Partially Acceptable'],
        ['grade' => 'NG', 'point' => 0.0, 'start_at' => 0,    'end_at' => 34.99, 'remark' => 'Not Graded'],
    ];

    public function run(): void
    {
        $systemNames = [
            'NEB SEE Standard (Grade 10)',
            'NEB +2 Standard (Grade 11-12)',
        ];

        // Get all active sessions to ensure grading systems are available
        $sessions = \App\Models\SchoolSession::all();
        if ($sessions->isEmpty()) {
            $this->command?->warn('No school sessions found. NEB grading seeded globally (no session_id).');
            $sessions = collect([null]);
        }

        foreach ($sessions as $session) {
            $sessionId = $session?->id;
            $schoolId  = $session?->school_id ?? null;

            foreach ($systemNames as $systemName) {
                $gs = GradingSystem::firstOrCreate(
                    [
                        'system_name' => $systemName,
                        'session_id'  => $sessionId,
                    ],
                    [
                        'class_id'    => null,
                        'semester_id' => null,
                        'school_id'   => $schoolId,
                    ]
                );

                foreach ($this->nebRules as $rule) {
                    GradeRule::firstOrCreate(
                        [
                            'grading_system_id' => $gs->id,
                            'grade'             => $rule['grade'],
                        ],
                        [
                            'point'      => $rule['point'],
                            'start_at'   => $rule['start_at'],
                            'end_at'     => $rule['end_at'],
                            'session_id' => $sessionId,
                        ]
                    );
                }

                $this->command?->info("  ✓ {$systemName} seeded (session: {$sessionId})");
            }
        }

        $this->command?->info('NEB grading systems seeded successfully.');
    }
}
