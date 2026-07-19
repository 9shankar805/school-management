<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\Course;

class ExamSeeder extends Seeder
{
    public function run()
    {
        $courses = Course::all();
        
        foreach ($courses as $course) {
            Exam::firstOrCreate([
                'exam_name' => 'Mid-Term Exam',
                'class_id' => $course->class_id,
                'course_id' => $course->id,
                'semester_id' => $course->semester_id,
                'session_id' => $course->session_id,
            ], [
                'start_date' => now()->addMonths(2),
                'end_date' => now()->addMonths(2)->addHours(2),
            ]);
            
            Exam::firstOrCreate([
                'exam_name' => 'Final Exam',
                'class_id' => $course->class_id,
                'course_id' => $course->id,
                'semester_id' => $course->semester_id,
                'session_id' => $course->session_id,
            ], [
                'start_date' => now()->addMonths(5),
                'end_date' => now()->addMonths(5)->addHours(2),
            ]);
        }
    }
}
