<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AiFeatureWebController extends Controller
{
    public function __construct()
    {
        // Require auth for AI features
        $this->middleware('auth');
    }

    public function index()
    {
        return view('ai-features.index');
    }

    public function homework()
    {
        return view('ai-features.homework');
    }

    public function notice()
    {
        return view('ai-features.notice');
    }

    public function attendance()
    {
        return view('ai-features.attendance');
    }

    public function feePrediction()
    {
        return view('ai-features.fee-prediction');
    }

    public function performance()
    {
        return view('ai-features.performance');
    }

    public function chatbot()
    {
        return view('ai-features.chatbot');
    }

    public function timetable()
    {
        return view('ai-features.timetable');
    }

    public function reportSummariser()
    {
        return view('ai-features.report');
    }

    public function remedial()
    {
        return view('ai-features.remedial');
    }
}
