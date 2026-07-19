<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StudentAcademicInfo;
use App\Models\Promotion;
use App\Models\SchoolSession;
use App\Models\SchoolClass;
use App\Models\Section;

class StudentAcademicInfoSeeder extends Seeder
{
    public function run()
    {
        $students = User::where('role', 'student')->get();
        // Seed to 2025-2026
        $session = SchoolSession::where('session_name', '2025-2026')->first();
        if (!$session) {
            $session = SchoolSession::first();
        }
        $class = SchoolClass::where('session_id', $session->id)->first();
        $section = Section::where('session_id', $session->id)->first();

        foreach ($students as $index => $student) {
            // Give them a registration number
            StudentAcademicInfo::firstOrCreate(
                ['student_id' => $student->id],
                ['board_reg_no' => 'REG-' . (1000 + $index)]
            );

            // Enroll them into a class if a class and section exists
            if ($session && $class && $section) {
                Promotion::firstOrCreate([
                    'student_id' => $student->id,
                    'session_id' => $session->id,
                ], [
                    'class_id' => $class->id,
                    'section_id' => $section->id,
                    'id_card_number' => 'ID-' . (1000 + $index),
                ]);
            }
        }
    }
}
