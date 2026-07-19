<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradingSystem;
use App\Models\SchoolClass;
use App\Models\Semester;

class GradingSystemSeeder extends Seeder
{
    public function run()
    {
        $classes = SchoolClass::all();
        $semesters = Semester::all();

        foreach ($classes as $class) {
            foreach ($semesters as $semester) {
                if ($class->session_id == $semester->session_id) {
                    GradingSystem::firstOrCreate([
                        'system_name' => 'Standard 100 Point Scale',
                        'class_id' => $class->id,
                        'semester_id' => $semester->id,
                        'session_id' => $class->session_id,
                    ]);
                }
            }
        }
    }
}
