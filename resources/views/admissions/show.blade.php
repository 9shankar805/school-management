@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto max-w-4xl">

        <nav class="text-xs text-slate-400 mb-5">
            <a href="{{ route('admissions.index') }}" class="hover:text-indigo-600">Admissions</a>
            <span class="mx-1">/</span> {{ $application->application_number }}
        </nav>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        {{-- Header --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6 flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-xl font-bold text-slate-800">{{ $application->applicant_name }}</h1>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-medium {{ $application->status_badge }}">{{ ucwords(str_replace('_',' ',$application->status)) }}</span>
                </div>
                <p class="text-xs text-slate-400">{{ $application->application_number }} · Applied {{ $application->created_at->format('d M Y') }}</p>
                @if($application->student)
                <a href="{{ route('student.profile.show', $application->student_id) }}" class="text-xs text-indigo-600 hover:underline mt-1 inline-block">→ View enrolled student profile</a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Details --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-slate-700">Applicant Details</div>
                    <table class="w-full text-sm divide-y divide-slate-50">
                        @foreach([['Birthday',$application->birthday?->format('d M Y')],['Gender',$application->gender],['Nationality',$application->nationality],['Religion',$application->religion],['Blood Type',$application->blood_type],['Phone',$application->phone],['Email',$application->email],['Address',$application->address],['Requested Class',$application->schoolClass?->class_name],['Previous School',$application->previous_school],['Guardian',$application->guardian_name . ' (' . $application->guardian_relation . ')'],['Guardian Phone',$application->guardian_phone]] as [$l,$v])
                        @if($v)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-2.5 text-slate-400 font-medium w-40">{{ $l }}</td>
                            <td class="px-5 py-2.5 text-slate-700">{{ $v }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </table>
                </div>
                @if($application->reviewer_notes)
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 text-sm text-slate-600">
                    <p class="font-medium text-slate-700 mb-1">Reviewer Notes</p>
                    <p>{{ $application->reviewer_notes }}</p>
                    @if($application->reviewer)<p class="text-xs text-slate-400 mt-1">— {{ $application->reviewer->full_name }}, {{ $application->reviewed_at?->format('d M Y') }}</p>@endif
                </div>
                @endif
            </div>

            {{-- Actions panel --}}
            <div class="space-y-4">
                @can('create students')
                @if(in_array($application->status, ['pending','under_review']))
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <p class="text-sm font-semibold text-slate-700 mb-4">Update Status</p>
                    <form method="POST" action="{{ route('admissions.review', $application->id) }}" class="space-y-3">
                        @csrf
                        <select name="status" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="under_review">Under Review</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                        <textarea name="reviewer_notes" rows="3" placeholder="Notes (optional)" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                        <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Update</button>
                    </form>
                </div>
                @endif

                @if($application->status === 'approved')
                <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-5">
                    <p class="text-sm font-semibold text-emerald-700 mb-4"><i class="bi bi-person-check me-1"></i>Enroll Student</p>
                    <form method="POST" action="{{ route('admissions.enroll', $application->id) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Email <span class="text-rose-400">*</span></label>
                            <input type="email" name="email" value="{{ $application->email }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Password <span class="text-rose-400">*</span></label>
                            <input type="password" name="password" required minlength="8" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Session <span class="text-rose-400">*</span></label>
                            <select name="session_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                @foreach(\App\Models\SchoolSession::latest()->take(5)->get() as $s)
                                <option value="{{ $s->id }}" {{ $application->session_id==$s->id ? 'selected' : '' }}>{{ $s->session_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Class <span class="text-rose-400">*</span></label>
                            <select name="class_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                @foreach($classes as $c)<option value="{{ $c->id }}" {{ $application->class_id==$c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Section <span class="text-rose-400">*</span></label>
                            <select name="section_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                @foreach(\App\Models\Section::all() as $s)<option value="{{ $s->id }}">{{ $s->section_name }}</option>@endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition">Enroll Now</button>
                    </form>
                </div>
                @endif
                @endcan

                @can('create students')
                <form method="POST" action="{{ route('admissions.destroy', $application->id) }}">
                    @csrf @method('DELETE')
                    <button onclick="return confirm('Delete this application?')" class="w-full py-2 border border-rose-200 text-rose-600 hover:bg-rose-50 rounded-lg text-sm font-medium transition">Delete Application</button>
                </form>
                @endcan
            </div>
        </div>

    </div>
</div>
@endsection
