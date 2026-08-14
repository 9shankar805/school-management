<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Submitted — {{ $school->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body style="background:#f8fafc;font-family:'Inter',sans-serif" class="min-h-screen flex flex-col justify-between">

    <header class="bg-white border-b border-slate-200 py-4 shadow-sm">
        <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($school->logo)
                <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200">
                @else
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg">
                    {{ strtoupper(substr($school->name, 0, 1)) }}
                </div>
                @endif
                <div>
                    <h1 class="font-extrabold text-slate-900 text-base leading-tight">{{ $school->name }}</h1>
                    <p class="text-xs text-slate-500 font-medium">{{ $school->district ? $school->district . ', ' : '' }}{{ $school->province ?? 'Nepal' }}</p>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-xl mx-auto px-4 py-12 my-auto text-center">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl p-8 sm:p-12">
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">
                <i class="bi bi-check-circle-fill"></i>
            </div>

            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-2 tracking-tight">Application Submitted!</h2>
            <p class="text-sm text-slate-600 font-medium mb-6">
                Thank you for applying to <strong>{{ $school->name }}</strong>. Your application has been received and is currently under review by our admissions department.
            </p>

            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 text-left mb-8 space-y-2 text-xs">
                <div class="flex justify-between border-b border-slate-200 pb-2">
                    <span class="text-slate-500 font-medium">Application Number:</span>
                    <span class="font-extrabold text-indigo-600 text-sm tracking-wider">{{ $application->application_number }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-200 pb-2">
                    <span class="text-slate-500 font-medium">Applicant Name:</span>
                    <span class="font-bold text-slate-800">{{ $application->applicant_name }}</span>
                </div>
                @if($application->schoolClass)
                <div class="flex justify-between border-b border-slate-200 pb-2">
                    <span class="text-slate-500 font-medium">Applying Class:</span>
                    <span class="font-bold text-slate-800">{{ $application->schoolClass->class_name }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-slate-500 font-medium">Status:</span>
                    <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px] bg-amber-100 text-amber-700">Pending Review</span>
                </div>
            </div>

            <div class="space-y-3">
                <a href="{{ route('school.landing', ['slug' => $school->slug]) }}" class="inline-flex items-center justify-center gap-2 w-full py-3.5 px-6 rounded-xl font-bold text-white text-sm bg-indigo-600 hover:bg-indigo-700 shadow-md transition">
                    <i class="bi bi-house-door-fill"></i> Return to School Home
                </a>
            </div>
        </div>
    </main>

    <footer class="text-center py-6 text-xs text-slate-400">
        &copy; {{ date('Y') }} {{ $school->name }} — Powered by NepalSchoolHub
    </footer>
</body>
</html>
