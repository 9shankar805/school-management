<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\AssignedTeacher;

class AssignmentSeeder extends Seeder
{
    public function run()
    {
        $assignedTeachers = AssignedTeacher::all();
        foreach ($assignedTeachers as $assigned) {
            Assignment::firstOrCreate([
                'assignment_name' => 'Homework 1',
                'teacher_id' => $assigned->teacher_id,
                'semester_id' => $assigned->semester_id,
                'class_id' => $assigned->class_id,
                'section_id' => $assigned->section_id,
                'course_id' => $assigned->course_id,
                'session_id' => $assigned->session_id,
            ], [
                'assignment_file_path' => 'dummy_assignment.pdf',
            ]);
        }
    }
}
