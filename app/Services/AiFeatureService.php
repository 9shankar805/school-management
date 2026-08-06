<?php

namespace App\Services;

class AiFeatureService
{
    protected $freeAiService;

    public function __construct(FreeAiService $freeAiService)
    {
        $this->freeAiService = $freeAiService;
    }

    public function analyzeAttendancePattern(array $attendanceData): string
    {
        $prompt = "Act as an education analyst. I am providing attendance data for a student. Analyze the pattern and highlight any concerning trends, potential reasons, and recommendations for intervention.\n\nData: " . json_encode($attendanceData);
        return $this->freeAiService->generate($prompt);
    }

    public function predictFeeDefault(array $feeHistory): string
    {
        $prompt = "Act as a financial analyst for a school. Analyze this fee payment history and predict the likelihood of future fee defaults. Provide a risk score (Low, Medium, High) and brief reasoning.\n\nHistory: " . json_encode($feeHistory);
        return $this->freeAiService->generate($prompt);
    }

    public function predictPerformance(array $gradesData): string
    {
        $prompt = "Act as an academic advisor. Analyze these student grades across subjects and predict their future performance. Identify strengths and areas needing attention.\n\nGrades: " . json_encode($gradesData);
        return $this->freeAiService->generate($prompt);
    }

    public function chatWithBot(string $userQuery, array $context = []): string
    {
        $prompt = "You are a helpful school assistant chatbot speaking to a student or parent. Provide a polite and concise answer to their query based on this context (if any): " . json_encode($context) . "\n\nQuery: " . $userQuery;
        return $this->freeAiService->generate($prompt);
    }

    public function generateHomework(string $topic, string $gradeLevel): string
    {
        $prompt = "You are an expert school educator. Create a 5-question homework assignment specifically for students in {$gradeLevel}.\n\n"
            . "Topic: \"{$topic}\"\n"
            . "Grade Level: {$gradeLevel}\n\n"
            . "CRITICAL INSTRUCTIONS FOR GRADE COMPLEXITY:\n"
            . "- You MUST strictly calibrate the vocabulary, mathematical rigor, and conceptual depth to {$gradeLevel}.\n"
            . "- For Primary (Grade 1-5): Use simple wording, basic counting, or simple visual/arithmetic concepts suitable for 6-10 year olds.\n"
            . "- For Lower Secondary (Grade 6-8): Focus on core formulas, 2-step word problems, and middle school concepts.\n"
            . "- For High School (Grade 9-12 / High School): Use advanced formulas, calculus/pre-calculus/quadratic concepts, formal proof reasoning, and rigorous analytical questions.\n\n"
            . "Structure:\n"
            . "1. Title: Homework Worksheet - {$topic} ({$gradeLevel})\n"
            . "2. 3 Multiple Choice Questions (A, B, C, D)\n"
            . "3. 2 Short Answer / Numerical Problem Questions\n"
            . "4. Complete Answer Key with brief explanations\n";

        return $this->freeAiService->generate($prompt);
    }

    public function optimizeTimetable(array $currentTimetable): string
    {
        $prompt = "Act as a school administrator. Review this class timetable and suggest optimizations for better flow, reducing teacher fatigue, and balancing subject loads across the week.\n\nTimetable: " . json_encode($currentTimetable);
        return $this->freeAiService->generate($prompt);
    }

    public function summarizeReport(string $reportText): string
    {
        $prompt = "Summarize the following school report into a brief, easy-to-read executive summary with key bullet points.\n\nReport: " . $reportText;
        return $this->freeAiService->generate($prompt);
    }

    public function writeNotice(string $subject, string $audience): string
    {
        $prompt = "Write a professional and polite school notice/email regarding '{$subject}'. The target audience is '{$audience}'. Keep it clear and concise.";
        return $this->freeAiService->generate($prompt);
    }

    public function recommendRemedial(array $performanceData): string
    {
        $prompt = "Act as a special education coordinator. Based on this student's performance data, recommend specific remedial classes, topics to focus on, and learning strategies.\n\nData: " . json_encode($performanceData);
        return $this->freeAiService->generate($prompt);
    }
}
