@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header & Breadcrumbs --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-person-workspace"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Add New Teacher</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Register a new faculty member into the system</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{route('home')}}" class="hover:text-indigo-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li><a href="#" class="hover:text-indigo-600 transition">Teachers</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Add Teacher</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    <form action="{{route('school.teacher.create')}}" method="POST" id="addTeacherForm" class="space-y-6">
        @csrf

        {{-- Section 1: Basic Profile & Photo --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">1. Teacher Profile & Photo</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                
                {{-- Photo Uploader Dropzone --}}
                <div class="md:col-span-12 lg:col-span-4 flex flex-col">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Teacher Photo <span class="text-slate-400 font-normal">(Auto-compressed to &le; 500 KB)</span>
                    </label>
                    
                    <div class="photo-dropzone relative flex-1 min-h-[160px] border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-500 rounded-2xl p-4 flex flex-col items-center justify-center text-center cursor-pointer transition-all bg-slate-50/50 dark:bg-slate-800/30 group">
                        <input class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10" type="file" id="formFile" accept="image/*" onchange="previewFile()">
                        
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i class="bi bi-cloud-arrow-up text-2xl"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Click to upload or drag & drop</p>
                        <p class="text-[11px] text-slate-400">PNG, JPG or WEBP (Max 500 KB auto compressed)</p>
                    </div>

                    <div id="previewPhoto"></div>
                    <input type="hidden" id="photoHiddenInput" name="photo" value="">
                </div>

                {{-- Personal Information Fields --}}
                <div class="md:col-span-12 lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="inputFirstName" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            First Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputFirstName" name="first_name" placeholder="First Name" required value="{{old('first_name')}}">
                    </div>

                    <div>
                        <label for="inputLastName" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Last Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputLastName" name="last_name" placeholder="Last Name" required value="{{old('last_name')}}">
                    </div>

                    <div>
                        <label for="inputEmail" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputEmail" name="email" placeholder="teacher@school.edu" required value="{{old('email')}}">
                    </div>

                    <div>
                        <label for="inputPassword" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputPassword" name="password" placeholder="••••••••" required>
                    </div>

                    <div>
                        <label for="inputGender" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Gender <span class="text-rose-500">*</span>
                        </label>
                        <select id="inputGender" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" name="gender" required>
                            <option value="Male" {{old('gender') == 'Male' || old('gender') == 'male' ? 'selected' : ''}}>Male</option>
                            <option value="Female" {{old('gender') == 'Female' || old('gender') == 'female' ? 'selected' : ''}}>Female</option>
                        </select>
                    </div>

                    <div>
                        <label for="inputNationality" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Nationality <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputNationality" name="nationality" placeholder="e.g. Nepali, German..." required value="{{old('nationality', 'Nepali')}}">
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
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputPhone" name="phone" placeholder="+977 9800000000" required value="{{old('phone')}}">
                </div>

                <div>
                    <label for="inputCity" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        City <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputCity" name="city" placeholder="Kathmandu, Pokhara..." required value="{{old('city')}}">
                </div>

                <div>
                    <label for="inputZip" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Zip Code <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputZip" name="zip" placeholder="44600" required value="{{old('zip')}}">
                </div>

                <div class="md:col-span-2">
                    <label for="inputAddress" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Street Address <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputAddress" name="address" placeholder="634 Main Street" required value="{{old('address')}}">
                </div>

                <div>
                    <label for="inputAddress2" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Address Line 2 <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputAddress2" name="address2" placeholder="Apartment, suite, or floor" value="{{old('address2')}}">
                </div>
            </div>
        </div>

        {{-- Submit Action Bar --}}
        <div class="flex items-center justify-between pt-2 pb-12">
            <a href="{{route('home')}}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold transition">
                Cancel
            </a>
            <button type="submit" class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 hover:shadow-indigo-600/35 transition-all flex items-center gap-2">
                <i class="bi bi-person-check text-sm"></i> Add Teacher
            </button>
        </div>
    </form>
</div>

@include('components.photos.photo-input')
@endsection
