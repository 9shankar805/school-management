@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto max-w-4xl">

        <nav class="text-xs text-slate-400 mb-5">
            <a href="{{ route('admissions.index') }}" class="hover:text-indigo-600">Admissions</a>
            <span class="mx-1">/</span> New Application
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-7 tracking-tight">New Admission Application</h1>

        @if($errors->any())
        <div class="mb-5 p-4 bg-rose-50 border border-rose-200 rounded-xl text-sm text-rose-700">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admissions.store') }}" class="space-y-6">
            @csrf

            {{-- Step 1: Applicant info --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-700 mb-4"><span class="inline-flex w-5 h-5 rounded-full bg-indigo-600 text-white text-[10px] items-center justify-center mr-2">1</span>Applicant Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([['first_name','First Name',true],['last_name','Last Name',true],['email','Email Address',false],['phone','Phone',false],['birthday','Date of Birth',false,'date'],['nationality','Nationality',false],['religion','Religion',false],['blood_type','Blood Type',false],] as [$f,$l,$req,$type])
                    @php $type ??= 'text'; @endphp
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ $l }}@if($req) <span class="text-rose-400">*</span>@endif</label>
                        <input type="{{ $type }}" name="{{ $f }}" value="{{ old($f) }}" {{ $req ? 'required' : '' }}
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    @endforeach
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Gender</label>
                        <select name="gender" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— Select —</option>
                            @foreach(['Male','Female','Other'] as $g)<option value="{{ $g }}" {{ old('gender')===$g ? 'selected' : '' }}>{{ $g }}</option>@endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Address</label>
                        <textarea name="address" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Step 2: Academic --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-700 mb-4"><span class="inline-flex w-5 h-5 rounded-full bg-indigo-600 text-white text-[10px] items-center justify-center mr-2">2</span>Academic Request</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Session</label>
                        <select name="session_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— Select —</option>
                            @foreach($sessions as $s)<option value="{{ $s->id }}" {{ old('session_id')==$s->id ? 'selected' : '' }}>{{ $s->session_name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Requested Class</label>
                        <select name="class_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— Select —</option>
                            @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id')==$c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>@endforeach
                        </select>
                    </div>
                    @foreach([['previous_school','Previous School'],['previous_class','Previous Class/Grade']] as [$f,$l])
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ $l }}</label>
                        <input type="text" name="{{ $f }}" value="{{ old($f) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Step 3: Guardian --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-700 mb-4"><span class="inline-flex w-5 h-5 rounded-full bg-indigo-600 text-white text-[10px] items-center justify-center mr-2">3</span>Guardian Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([['guardian_name','Guardian Name'],['guardian_phone','Phone'],['guardian_email','Email'],['guardian_relation','Relationship']] as [$f,$l])
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ $l }}</label>
                        <input type="text" name="{{ $f }}" value="{{ old($f) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Submit Application</button>
                <a href="{{ route('admissions.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
