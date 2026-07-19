<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssignedTeacher;
use App\Models\User;
use App\Models\Course;
use App\Models\Section;

class AssignedTeacherSeeder extends Seeder
{
    public function run()
    {
        $teachers = User::where('role', 'teacher')->get();
        $courses = Course::all();
        $sections = Section::all();

        if ($teachers->isEmpty() || $courses->isEmpty() || $sections->isEmpty()) {
            return;
        }

        // Just assign each course to a random teacher for demo purposes
        foreach ($courses as $course) {
            $teacher = $teachers->random();
            $section = $sections->where('class_id', $course->class_id)->random();

            if ($section) {
                AssignedTeacher::firstOrCreate([
                    'teacher_id' => $teacher->id,
                    'semester_id' => $course->semester_id,
                    'class_id' => $course->class_id,
                    'section_id' => $section->id,
                    'course_id' => $course->id,
                    'session_id' => $course->session_id,
                ]);
            }
        }
    }
}
