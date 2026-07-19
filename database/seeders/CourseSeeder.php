<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\SchoolSession;
use App\Models\Semester;
use App\Models\SchoolClass;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $sessions = SchoolSession::all();

        $courseNames = ['Mathematics', 'English', 'Science', 'History', 'Geography', 'Computer Science', 'Physics', 'Chemistry', 'Biology'];

        foreach ($sessions as $session) {
            $semesters = Semester::where('session_id', $session->id)->get();
            $classes = SchoolClass::where('session_id', $session->id)->get();

            foreach ($classes as $class) {
                foreach ($semesters as $semester) {
                    foreach ($courseNames as $courseName) {
                        Course::firstOrCreate([
                            'course_name' => $courseName,
                            'class_id' => $class->id,
                            'semester_id' => $semester->id,
                            'session_id' => $session->id,
                        ], [
                            'course_type' => 'Core',
                        ]);
                    }
                }
            }
        }
    }
}
