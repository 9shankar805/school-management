<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Syllabus;
use App\Models\Course;

class SyllabusSeeder extends Seeder
{
    public function run()
    {
        $courses = Course::all();
        foreach ($courses as $course) {
            Syllabus::firstOrCreate([
                'syllabus_name' => $course->course_name . ' Syllabus',
                'class_id' => $course->class_id,
                'course_id' => $course->id,
                'session_id' => $course->session_id,
            ], [
                'syllabus_file_path' => 'dummy_syllabus.pdf',
            ]);
        }
    }
}
