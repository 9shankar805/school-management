<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Semester;
use App\Models\SchoolSession;

class SemesterSeeder extends Seeder
{
    public function run()
    {
        $sessions = SchoolSession::all();

        $semesters = [
            ['semester_name' => 'First Semester', 'start_date' => '2026-09-01', 'end_date' => '2027-01-15'],
            ['semester_name' => 'Second Semester', 'start_date' => '2027-01-16', 'end_date' => '2027-06-30']
        ];

        foreach ($sessions as $session) {
            foreach ($semesters as $semester) {
                Semester::firstOrCreate([
                    'semester_name' => $semester['semester_name'],
                    'session_id' => $session->id,
                ], $semester);
            }
        }
    }
}
