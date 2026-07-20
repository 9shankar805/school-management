<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AiFeatureService;
use Illuminate\Http\Request;

class AiFeatureController extends Controller
{
    protected $aiFeatureService;

    public function __construct(AiFeatureService $aiFeatureService)
    {
        $this->aiFeatureService = $aiFeatureService;
    }

    public function attendanceAnalysis(Request $request)
    {
        $request->validate(['attendance_data' => 'required|array']);
        return $this->respond($this->aiFeatureService->analyzeAttendancePattern($request->input('attendance_data')));
    }

    public function feePrediction(Request $request)
    {
        $request->validate(['fee_history' => 'required|array']);
        return $this->respond($this->aiFeatureService->predictFeeDefault($request->input('fee_history')));
    }

    public function performancePrediction(Request $request)
    {
        $request->validate(['grades_data' => 'required|array']);
        return $this->respond($this->aiFeatureService->predictPerformance($request->input('grades_data')));
    }

    public function chatbot(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'context' => 'nullable|array'
        ]);
        return $this->respond($this->aiFeatureService->chatWithBot($request->input('query'), $request->input('context', [])));
    }

    public function homeworkGenerator(Request $request)
    {
        $request->validate([
            'topic' => 'required|string',
            'grade_level' => 'required|string',
        ]);
        return $this->respond($this->aiFeatureService->generateHomework($request->input('topic'), $request->input('grade_level')));
    }

    public function timetableOptimizer(Request $request)
    {
        $request->validate(['timetable' => 'required|array']);
        return $this->respond($this->aiFeatureService->optimizeTimetable($request->input('timetable')));
    }

    public function reportSummariser(Request $request)
    {
        $request->validate(['report_text' => 'required|string']);
        return $this->respond($this->aiFeatureService->summarizeReport($request->input('report_text')));
    }

    public function noticeWriter(Request $request)
    {
        $request->validate([
            'subject' => 'required|string',
            'audience' => 'required|string',
        ]);
        return $this->respond($this->aiFeatureService->writeNotice($request->input('subject'), $request->input('audience')));
    }

    public function recommendRemedial(Request $request)
    {
        $request->validate(['performance_data' => 'required|array']);
        return $this->respond($this->aiFeatureService->recommendRemedial($request->input('performance_data')));
    }

    private function respond(string $response)
    {
        return response()->json([
            'success' => true,
            'response' => $response,
        ]);
    }
}
