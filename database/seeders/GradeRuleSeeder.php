<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradingSystem;
use App\Models\GradeRule;

class GradeRuleSeeder extends Seeder
{
    public function run()
    {
        $gradingSystems = GradingSystem::all();
        
        $rules = [
            ['grade' => 'A+', 'point' => 4.0, 'start_at' => 90, 'end_at' => 100],
            ['grade' => 'A', 'point' => 3.7, 'start_at' => 80, 'end_at' => 89.99],
            ['grade' => 'B', 'point' => 3.0, 'start_at' => 70, 'end_at' => 79.99],
            ['grade' => 'C', 'point' => 2.0, 'start_at' => 60, 'end_at' => 69.99],
            ['grade' => 'D', 'point' => 1.0, 'start_at' => 50, 'end_at' => 59.99],
            ['grade' => 'F', 'point' => 0.0, 'start_at' => 0, 'end_at' => 49.99],
        ];

        foreach ($gradingSystems as $system) {
            foreach ($rules as $rule) {
                GradeRule::firstOrCreate([
                    'grade' => $rule['grade'],
                    'grading_system_id' => $system->id,
                    'session_id' => $system->session_id,
                ], [
                    'point' => $rule['point'],
                    'start_at' => $rule['start_at'],
                    'end_at' => $rule['end_at'],
                ]);
            }
        }
    }
}
