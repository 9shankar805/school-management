@extends('layouts.app')
@section('title', 'AI Assistant Dashboard')

@section('content')
<style>
    .ai-header-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: #ffffff;
        box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);
    }
    
    .ai-feature-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        position: relative;
        z-index: 1;
    }
    
    .ai-feature-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        border-radius: 20px;
        padding: 2px;
        background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(168,85,247,0.2), rgba(236,72,153,0.2));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0;
        transition: opacity 0.4s ease;
        z-index: -1;
    }
    
    .ai-feature-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.15), 0 8px 10px -6px rgba(99, 102, 241, 0.1);
        border-color: transparent;
    }
    
    .ai-feature-card:hover::before {
        opacity: 1;
    }

    .ai-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        margin-bottom: 1.25rem;
        transition: transform 0.4s ease;
    }
    
    .ai-feature-card:hover .ai-icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }
    
    .ai-bg-pattern {
        position: absolute;
        top: 0; right: 0;
        opacity: 0.03;
        font-size: 140px;
        line-height: 1;
        transform: translate(20%, -20%) rotate(15deg);
        z-index: 0;
        transition: all 0.4s ease;
    }
    
    .ai-feature-card:hover .ai-bg-pattern {
        opacity: 0.06;
        transform: translate(15%, -15%) rotate(5deg) scale(1.1);
    }

    /* Gradients */
    .grad-indigo-purple { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; box-shadow: 0 4px 12px rgba(99,102,241,0.3); }
    .grad-blue-cyan { background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%); color: white; box-shadow: 0 4px 12px rgba(59,130,246,0.3); }
    .grad-emerald-teal { background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%); color: white; box-shadow: 0 4px 12px rgba(16,185,129,0.3); }
    .grad-amber-orange { background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); color: white; box-shadow: 0 4px 12px rgba(245,158,11,0.3); }
    .grad-rose-pink { background: linear-gradient(135deg, #e11d48 0%, #ec4899 100%); color: white; box-shadow: 0 4px 12px rgba(225,29,72,0.3); }
    .grad-fuchsia-indigo { background: linear-gradient(135deg, #d946ef 0%, #6366f1 100%); color: white; box-shadow: 0 4px 12px rgba(217,70,239,0.3); }
    
    /* Blob Animation */
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob {
        animation: blob 7s infinite;
    }
    .animation-delay-2000 {
        animation-delay: 2s;
    }
</style>

<div class="container-fluid px-4 py-5 position-relative">
    
    <!-- Background Animated Blobs -->
    <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="z-index:-1; opacity:0.6; pointer-events:none;">
        <div class="position-absolute top-0 start-0 rounded-circle bg-indigo-200 opacity-50 mix-blend-multiply filter blur-xl animate-blob" style="width:300px; height:300px; filter: blur(60px); left:-10%;"></div>
        <div class="position-absolute top-0 end-0 rounded-circle bg-purple-200 opacity-50 mix-blend-multiply filter blur-xl animate-blob animation-delay-2000" style="width:400px; height:400px; filter: blur(80px); right:-5%;"></div>
    </div>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-5 position-relative z-index-1">
        <div>
            <div class="d-flex align-items-center gap-4 mb-2">
                <div class="ai-header-icon">
                    <i class="bi bi-stars fs-3"></i>
                </div>
                <div>
                    <h2 class="h2 font-black text-slate-800 mb-1" style="letter-spacing: -0.5px;">Intelligent <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 to-purple-600">AI Assistant</span></h2>
                    <p class="text-slate-500 text-sm mb-0 fw-medium">Harness the power of artificial intelligence to automate, predict, and optimize school management.</p>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-indigo-100 text-indigo-700 px-3 py-2 rounded-xl border border-indigo-200 shadow-sm font-semibold">
                <i class="bi bi-cpu me-1"></i> Groq Llama 3.3 Powered
            </span>
        </div>
    </div>

    <!-- AI Feature Cards Grid -->
    <div class="row g-4 position-relative z-index-1">
        
        <!-- Homework Generator -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('ai_features.web.homework') }}" class="text-decoration-none">
                <div class="card ai-feature-card h-100 p-4">
                    <i class="bi bi-pencil-square ai-bg-pattern text-indigo-500"></i>
                    <div class="ai-icon-wrapper grad-indigo-purple">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <h5 class="font-bold text-slate-800 mb-2">Homework Generator</h5>
                    <p class="text-slate-500 text-sm mb-0 leading-relaxed">Automatically generate topic-specific assignments and structured question papers tailored to any grade level.</p>
                </div>
            </a>
        </div>

        <!-- Notice Writer -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('ai_features.web.notice') }}" class="text-decoration-none">
                <div class="card ai-feature-card h-100 p-4">
                    <i class="bi bi-envelope-paper ai-bg-pattern text-blue-500"></i>
                    <div class="ai-icon-wrapper grad-blue-cyan">
                        <i class="bi bi-envelope-paper"></i>
                    </div>
                    <h5 class="font-bold text-slate-800 mb-2">Notice Writer</h5>
                    <p class="text-slate-500 text-sm mb-0 leading-relaxed">Draft professional emails, circulars, and announcements instantly with the perfect tone and grammar.</p>
                </div>
            </a>
        </div>

        <!-- Attendance Analyzer -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('ai_features.web.attendance') }}" class="text-decoration-none">
                <div class="card ai-feature-card h-100 p-4">
                    <i class="bi bi-calendar-check ai-bg-pattern text-emerald-500"></i>
                    <div class="ai-icon-wrapper grad-emerald-teal">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h5 class="font-bold text-slate-800 mb-2">Attendance Insights</h5>
                    <p class="text-slate-500 text-sm mb-0 leading-relaxed">Identify behavioral trends and receive AI-driven insights on student attendance patterns.</p>
                </div>
            </a>
        </div>

        <!-- Fee Predictor -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('ai_features.web.fee') }}" class="text-decoration-none">
                <div class="card ai-feature-card h-100 p-4">
                    <i class="bi bi-cash-coin ai-bg-pattern text-amber-500"></i>
                    <div class="ai-icon-wrapper grad-amber-orange">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h5 class="font-bold text-slate-800 mb-2">Fee Default Predictor</h5>
                    <p class="text-slate-500 text-sm mb-0 leading-relaxed">Predict which students are likely to default on fee payments based on historical transaction data.</p>
                </div>
            </a>
        </div>

        <!-- Performance Predictor -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('ai_features.web.performance') }}" class="text-decoration-none">
                <div class="card ai-feature-card h-100 p-4">
                    <i class="bi bi-graph-up ai-bg-pattern text-rose-500"></i>
                    <div class="ai-icon-wrapper grad-rose-pink">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h5 class="font-bold text-slate-800 mb-2">Performance Predictor</h5>
                    <p class="text-slate-500 text-sm mb-0 leading-relaxed">Analyze historical grades and predict future student academic trajectories and final scores.</p>
                </div>
            </a>
        </div>

        <!-- AI Chatbot -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('ai_features.web.chatbot') }}" class="text-decoration-none">
                <div class="card ai-feature-card h-100 p-4">
                    <i class="bi bi-chat-left-dots ai-bg-pattern text-fuchsia-500"></i>
                    <div class="ai-icon-wrapper grad-fuchsia-indigo">
                        <i class="bi bi-chat-left-dots"></i>
                    </div>
                    <h5 class="font-bold text-slate-800 mb-2">AI Data Chatbot</h5>
                    <p class="text-slate-500 text-sm mb-0 leading-relaxed">Interact with a smart conversational assistant that understands your school's database in real-time.</p>
                </div>
            </a>
        </div>

        <!-- Timetable Optimizer -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('ai_features.web.timetable') }}" class="text-decoration-none">
                <div class="card ai-feature-card h-100 p-4">
                    <i class="bi bi-table ai-bg-pattern text-blue-500"></i>
                    <div class="ai-icon-wrapper grad-blue-cyan" style="background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);">
                        <i class="bi bi-table"></i>
                    </div>
                    <h5 class="font-bold text-slate-800 mb-2">Timetable Optimizer</h5>
                    <p class="text-slate-500 text-sm mb-0 leading-relaxed">Optimize your complex class schedules to resolve teacher conflicts and maximize resource efficiency.</p>
                </div>
            </a>
        </div>

        <!-- Report Summariser -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('ai_features.web.report') }}" class="text-decoration-none">
                <div class="card ai-feature-card h-100 p-4">
                    <i class="bi bi-file-earmark-bar-graph ai-bg-pattern text-orange-500"></i>
                    <div class="ai-icon-wrapper grad-amber-orange" style="background: linear-gradient(135deg, #f97316 0%, #ef4444 100%);">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                    </div>
                    <h5 class="font-bold text-slate-800 mb-2">Report Summarizer</h5>
                    <p class="text-slate-500 text-sm mb-0 leading-relaxed">Summarize long, tedious text reports into quick, actionable bullet points in seconds.</p>
                </div>
            </a>
        </div>

        <!-- Remedial Classes Recommender -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('ai_features.web.remedial') }}" class="text-decoration-none">
                <div class="card ai-feature-card h-100 p-4">
                    <i class="bi bi-person-lines-fill ai-bg-pattern text-teal-500"></i>
                    <div class="ai-icon-wrapper grad-emerald-teal" style="background: linear-gradient(135deg, #0d9488 0%, #059669 100%);">
                        <i class="bi bi-person-lines-fill"></i>
                    </div>
                    <h5 class="font-bold text-slate-800 mb-2">Remedial Recommender</h5>
                    <p class="text-slate-500 text-sm mb-0 leading-relaxed">Identify students struggling with certain topics and recommend targeted, personalized support.</p>
                </div>
            </a>
        </div>
        
    </div>
</div>
@endsection
