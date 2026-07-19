<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\ExamRule;

class ExamRuleSeeder extends Seeder
{
    public function run()
    {
        $exams = Exam::all();
        
        foreach ($exams as $exam) {
            ExamRule::firstOrCreate([
                'exam_id' => $exam->id,
                'session_id' => $exam->session_id,
            ], [
                'total_marks' => 100,
                'pass_marks' => 40,
                'marks_distribution_note' => '{"Written": 70, "MCQ": 30}',
            ]);
        }
    }
}
