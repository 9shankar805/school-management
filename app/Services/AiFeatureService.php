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
        $prompt = "Act as a teacher. Generate a 5-question homework assignment or question paper about '{$topic}' suitable for {$gradeLevel} level students. Include a mix of multiple choice and short answer questions, followed by the answer key.";
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
