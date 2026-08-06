@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header & Breadcrumbs --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-pencil-square"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Edit Teacher Profile</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Update details for {{ $teacher->first_name }} {{ $teacher->last_name }}</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{route('home')}}" class="hover:text-indigo-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li><a href="{{url()->previous()}}" class="hover:text-indigo-600 transition">Teachers</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Edit Teacher</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    <form action="{{route('school.teacher.update')}}" method="POST" id="editTeacherForm" class="space-y-6">
        @csrf
        <input type="hidden" name="teacher_id" value="{{$teacher->id}}">

        {{-- Section 1: Teacher Profile & Photo --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">1. Teacher Profile & Photo</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                
                {{-- Photo Uploader Dropzone --}}
                <div class="md:col-span-12 lg:col-span-4 flex flex-col">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Update Photo <span class="text-slate-400 font-normal">(Auto-compressed to &le; 500 KB)</span>
                    </label>
                    
                    @if($teacher->photo)
                    <div class="mb-3 flex items-center gap-3 p-2 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        <img src="{{ asset('storage/' . $teacher->photo) }}" class="w-12 h-12 rounded-xl object-cover border" alt="Current Photo">
                        <div>
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-0">Current Photo</p>
                            <p class="text-[10px] text-slate-400 mb-0">Select new image below to replace</p>
                        </div>
                    </div>
                    @endif

                    <div class="photo-dropzone relative flex-1 min-h-[140px] border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-500 rounded-2xl p-4 flex flex-col items-center justify-center text-center cursor-pointer transition-all bg-slate-50/50 dark:bg-slate-800/30 group">
                        <input class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10" type="file" id="formFile" accept="image/*" onchange="previewFile()">
                        
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i class="bi bi-cloud-arrow-up text-xl"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-0.5">Click to change or drag & drop</p>
                        <p class="text-[10px] text-slate-400">PNG, JPG or WEBP (&le; 500 KB)</p>
                    </div>

                    <div id="previewPhoto"></div>
                    <input type="hidden" id="photoHiddenInput" name="photo" value="">
                </div>

                {{-- Personal Fields --}}
                <div class="md:col-span-12 lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="inputFirstName" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            First Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputFirstName" name="first_name" required value="{{$teacher->first_name}}">
                    </div>

                    <div>
                        <label for="inputLastName" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Last Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputLastName" name="last_name" required value="{{$teacher->last_name}}">
                    </div>

                    <div>
                        <label for="inputEmail" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputEmail" name="email" required value="{{$teacher->email}}">
                    </div>

                    <div>
                        <label for="inputGender" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Gender <span class="text-rose-500">*</span>
                        </label>
                        <select id="inputGender" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" name="gender" required>
                            <option value="Male" {{($teacher->gender == 'Male')?'selected':null}}>Male</option>
                            <option value="Female" {{($teacher->gender == 'Female')?'selected':null}}>Female</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="inputNationality" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nationality <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputNationality" name="nationality" required value="{{$teacher->nationality}}">
                    </div>
                </div>

            </div>
        </div>

        {{-- Section 2: Contact & Address --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-emerald-500 rounded-full"></span>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">2. Contact & Address</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="inputPhone" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Phone Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputPhone" name="phone" required value="{{$teacher->phone}}">
                </div>

                <div>
                    <label for="inputCity" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        City <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputCity" name="city" required value="{{$teacher->city}}">
                </div>

                <div>
                    <label for="inputZip" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Zip Code <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputZip" name="zip" required value="{{$teacher->zip}}">
                </div>

                <div class="md:col-span-2">
                    <label for="inputAddress" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Street Address <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputAddress" name="address" required value="{{$teacher->address}}">
                </div>

                <div>
                    <label for="inputAddress2" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Address Line 2 <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputAddress2" name="address2" value="{{$teacher->address2}}">
                </div>
            </div>
        </div>

        {{-- Submit Action Bar --}}
        <div class="flex items-center justify-between pt-2 pb-12">
            <a href="{{url()->previous()}}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold transition">
                Cancel
            </a>
            <button type="submit" class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 hover:shadow-indigo-600/35 transition-all flex items-center gap-2">
                <i class="bi bi-person-check text-sm"></i> Update Teacher Profile
            </button>
        </div>
    </form>
</div>

@include('components.photos.photo-input')
@endsection
