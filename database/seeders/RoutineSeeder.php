<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Routine;
use App\Models\Course;
use App\Models\Section;

class RoutineSeeder extends Seeder
{
    public function run()
    {
        $courses = Course::all();
        $sections = Section::all();

        foreach ($courses as $course) {
            $section = $sections->where('class_id', $course->class_id)->first();
            if ($section) {
                // Just add a dummy routine for Monday (weekday 1)
                Routine::firstOrCreate([
                    'weekday' => 1,
                    'class_id' => $course->class_id,
                    'section_id' => $section->id,
                    'course_id' => $course->id,
                    'session_id' => $course->session_id,
                ], [
                    'start' => '09:00',
                    'end' => '10:00',
                ]);
            }
        }
    }
}
