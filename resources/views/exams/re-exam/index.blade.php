@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-arrow-repeat me-2"></i>Re-Exam Applications</h1>
                <p class="text-slate-400 text-sm mt-0.5">Supplementary exam requests and results</p>
            </div>
            @role('student')
            <a href="{{ route('re-exam.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i> Apply for Re-Exam
            </a>
            @endrole
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif
        @include('session-messages')

        {{-- Status filter --}}
        <form method="GET" class="flex gap-2 mb-6 flex-wrap">
            @foreach(array_merge([''=>'All'], $statuses) as $key => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-medium transition {{ request('status', '') === $key ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                {{ $label }}
            </a>
            @endforeach
        </form>

        @if($applications->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
            <i class="bi bi-arrow-repeat text-5xl mb-3 block"></i>
            <p class="text-sm">No re-exam applications found.</p>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-xs text-slate-400 bg-slate-50 text-left">
                        <th class="px-5 py-3 font-medium">Student</th>
                        <th class="px-4 py-3 font-medium">Course</th>
                        <th class="px-4 py-3 font-medium">Semester</th>
                        <th class="px-4 py-3 font-medium text-center">Original Marks</th>
                        <th class="px-4 py-3 font-medium text-center">Re-Exam Marks</th>
                        <th class="px-4 py-3 font-medium">Re-Exam Date</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($applications as $app)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $app->student?->avatar }}" class="w-7 h-7 rounded-full object-cover" alt="">
                                    <span class="font-medium text-slate-700">{{ $app->student?->full_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $app->course?->course_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $app->semester?->semester_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $app->original_marks }}</td>
                            <td class="px-4 py-3 text-center font-semibold {{ $app->re_exam_marks ? 'text-emerald-600' : 'text-slate-300' }}">
                                {{ $app->re_exam_marks ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $app->re_exam_date?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $app->status_badge }}">
                                    {{ \App\Models\ReExamApplication::STATUSES[$app->status] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1.5 flex-wrap">
                                    @can('create exams')
                                    @if($app->isPending)
                                    <form method="POST" action="{{ route('re-exam.review', $app->id) }}" class="flex gap-1">
                                        @csrf
                                        <input type="hidden" name="action" value="approved">
                                        <button class="text-[10px] px-2 py-1 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('re-exam.review', $app->id) }}" class="flex gap-1">
                                        @csrf
                                        <input type="hidden" name="action" value="rejected">
                                        <button class="text-[10px] px-2 py-1 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition">Reject</button>
                                    </form>
                                    @endif
                                    @if(in_array($app->status, ['approved','scheduled']))
                                    <form method="POST" action="{{ route('re-exam.result', $app->id) }}" class="flex items-center gap-1">
                                        @csrf
                                        <input type="number" name="re_exam_marks" step="0.5" min="0" max="100" class="w-20 border border-slate-200 rounded-lg px-2 py-0.5 text-xs" placeholder="Marks">
                                        <button class="text-[10px] px-2 py-1 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition">Enter</button>
                                    </form>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-100">{{ $applications->links() }}</div>
        </div>
        @endif
    </div>
</div>
@endsection
