@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header & Breadcrumbs --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-person-plus-fill"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Add New Student</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Register a student and assign academic information</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{route('home')}}" class="hover:text-indigo-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li><a href="#" class="hover:text-indigo-600 transition">Students</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Add Student</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    {{-- Notice Box --}}
    <div class="mb-6 p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/60 flex items-start gap-3">
        <i class="bi bi-info-circle-fill text-indigo-600 dark:text-indigo-400 text-lg mt-0.5 flex-shrink-0"></i>
        <div class="text-xs text-indigo-900 dark:text-indigo-200 leading-relaxed">
            <span class="font-bold">Prerequisite Check:</span> Please ensure you have created the required <span class="font-semibold underline">Class</span> and <span class="font-semibold underline">Section</span> prior to registering a new student.
        </div>
    </div>

    <form action="{{route('school.student.create')}}" method="POST" id="addStudentForm" class="space-y-6">
        @csrf

        {{-- Section 1: Basic Profile & Photo --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">1. Basic Profile & Photo</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                
                {{-- Photo Uploader Dropzone --}}
                <div class="md:col-span-12 lg:col-span-4 flex flex-col">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Student Photo <span class="text-slate-400 font-normal">(Auto-compressed to &le; 500 KB)</span>
                    </label>
                    
                    <div class="photo-dropzone relative flex-1 min-h-[160px] border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-500 rounded-2xl p-4 flex flex-col items-center justify-center text-center cursor-pointer transition-all bg-slate-50/50 dark:bg-slate-800/30 group">
                        <input class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10" type="file" id="formFile" accept="image/*" onchange="previewFile()">
                        
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <i class="bi bi-cloud-arrow-up text-2xl"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Click to upload or drag & drop</p>
                        <p class="text-[11px] text-slate-400">PNG, JPG or WEBP (Max 500 KB limit handled automatically)</p>
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
                        <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputFirstName" name="first_name" placeholder="First Name" required value="{{old('first_name')}}">
                    </div>

                    <div>
                        <label for="inputLastName" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Last Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputLastName" name="last_name" placeholder="Last Name" required value="{{old('last_name')}}">
                    </div>

                    <div>
                        <label for="inputEmail4" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputEmail4" name="email" placeholder="student@school.edu" required value="{{old('email')}}">
                    </div>

                    <div>
                        <label for="inputPassword4" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputPassword4" name="password" placeholder="••••••••" required minlength="8">
                    </div>

                    <div>
                        <label for="inputBirthday" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Date of Birth <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputBirthday" name="birthday" required value="{{old('birthday')}}">
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

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="inputBloodType" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Blood Group <span class="text-rose-500">*</span>
                            </label>
                            <select id="inputBloodType" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" name="blood_type" required>
                                <option {{old('blood_type') == 'A+' ? 'selected' : ''}}>A+</option>
                                <option {{old('blood_type') == 'A-' ? 'selected' : ''}}>A-</option>
                                <option {{old('blood_type') == 'B+' ? 'selected' : ''}}>B+</option>
                                <option {{old('blood_type') == 'B-' ? 'selected' : ''}}>B-</option>
                                <option {{old('blood_type') == 'O+' ? 'selected' : ''}}>O+</option>
                                <option {{old('blood_type') == 'O-' ? 'selected' : ''}}>O-</option>
                                <option {{old('blood_type') == 'AB+' ? 'selected' : ''}}>AB+</option>
                                <option {{old('blood_type') == 'AB-' ? 'selected' : ''}}>AB-</option>
                                <option {{old('blood_type') == 'other' ? 'selected' : ''}}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label for="inputReligion" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Religion <span class="text-rose-500">*</span>
                            </label>
                            <select id="inputReligion" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" name="religion" required>
                                <option {{old('religion') == 'Hinduism' ? 'selected' : ''}}>Hinduism</option>
                                <option {{old('religion') == 'Buddhism' ? 'selected' : ''}}>Buddhism</option>
                                <option {{old('religion') == 'Islam' ? 'selected' : ''}}>Islam</option>
                                <option {{old('religion') == 'Christianity' ? 'selected' : ''}}>Christianity</option>
                                <option {{old('religion') == 'Judaism' ? 'selected' : ''}}>Judaism</option>
                                <option {{old('religion') == 'Others' || old('religion') == 'Other' ? 'selected' : ''}}>Other</option>
                            </select>
                        </div>
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
                    <label for="inputRollNo" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Roll No. <span class="text-slate-400 font-normal">(Optional / e.g. 01, 12)</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputRollNo" name="roll_no" placeholder="e.g. 01" value="{{old('roll_no')}}">
                </div>

                <div>
                    <label for="inputIdCardNumber" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        ID Card Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputIdCardNumber" name="id_card_number" placeholder="e.g. 2026-01-02-01" required value="{{old('id_card_number')}}">
                </div>

                <div>
                    <label for="inputCity" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        City <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputCity" name="city" placeholder="Kathmandu, Pokhara..." required value="{{old('city')}}">
                </div>

                <div class="md:col-span-2">
                    <label for="inputAddress" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Street Address <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputAddress" name="address" placeholder="634 Main Street" required value="{{old('address')}}">
                </div>

                <div>
                    <label for="inputZip" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Zip / Postal Code <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputZip" name="zip" placeholder="44600" required value="{{old('zip')}}">
                </div>

                <div class="md:col-span-3">
                    <label for="inputAddress2" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Address Line 2 <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputAddress2" name="address2" placeholder="Apartment, suite, or floor" value="{{old('address2')}}">
                </div>
            </div>
        </div>

        {{-- Section 3: Parents Information --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-amber-500 rounded-full"></span>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">3. Parents' & Guardian Information</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="inputFatherName" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Father's Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputFatherName" name="father_name" placeholder="Father's Full Name" required value="{{old('father_name')}}">
                </div>

                <div>
                    <label for="inputFatherPhone" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Father's Phone <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputFatherPhone" name="father_phone" placeholder="+977 9800000000" required value="{{old('father_phone')}}">
                </div>

                <div>
                    <label for="inputMotherName" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Mother's Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputMotherName" name="mother_name" placeholder="Mother's Full Name" required value="{{old('mother_name')}}">
                </div>

                <div>
                    <label for="inputMotherPhone" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Mother's Phone <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputMotherPhone" name="mother_phone" placeholder="+977 9800000000" required value="{{old('mother_phone')}}">
                </div>

                <div class="md:col-span-2">
                    <label for="inputParentAddress" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Parent Address <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputParentAddress" name="parent_address" placeholder="Guardian / Parent Full Address" required value="{{old('parent_address')}}">
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4"><i class="bi bi-link-45deg me-1"></i> Direct Parent Account Link</h3>
                <p class="text-xs text-slate-500 mb-4">Fill these fields to automatically create a parent portal account and link it to this student.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="inputParentEmail" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Parent Email <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <input type="email" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputParentEmail" name="parent_email" placeholder="Parent's login email" value="{{old('parent_email')}}">
                    </div>
                    <div>
                        <label for="inputParentPassword" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Parent Password <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <input type="password" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputParentPassword" name="parent_password" placeholder="Min 6 characters">
                    </div>
                    <div>
                        <label for="inputParentRelationship" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Link As <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <select id="inputParentRelationship" name="parent_relationship" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                            <option value="">— Select —</option>
                            <option value="father" {{old('parent_relationship') == 'father' ? 'selected' : ''}}>Father</option>
                            <option value="mother" {{old('parent_relationship') == 'mother' ? 'selected' : ''}}>Mother</option>
                            <option value="guardian" {{old('parent_relationship') == 'guardian' ? 'selected' : ''}}>Guardian</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Academic Information --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-blue-500 rounded-full"></span>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">4. Academic Assignment</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="inputAssignToClass" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Assign to Class <span class="text-rose-500">*</span>
                    </label>
                    <select onchange="getSections(this);" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputAssignToClass" name="class_id" required>
                        @isset($school_classes)
                            <option selected disabled value="">Please select a class</option>
                            @foreach ($school_classes as $school_class)
                                <option value="{{$school_class->id}}">{{$school_class->class_name}}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <div>
                    <label for="inputAssignToSection" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Assign to Section <span class="text-rose-500">*</span>
                    </label>
                    <select class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputAssignToSection" name="section_id" required>
                        <option value="" disabled selected>Select class first</option>
                    </select>
                </div>

                <div>
                    <label for="inputBoardRegistrationNumber" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Board Registration No. <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <input type="text" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none" id="inputBoardRegistrationNumber" name="board_reg_no" placeholder="Board Reg No." value="{{old('board_reg_no')}}">
                </div>

                <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
            </div>
        </div>

        {{-- Submit Action Bar --}}
        <div class="flex items-center justify-between pt-2 pb-12">
            <a href="{{route('home')}}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold transition">
                Cancel
            </a>
            <button type="submit" class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 hover:shadow-indigo-600/35 transition-all flex items-center gap-2">
                <i class="bi bi-person-check text-sm"></i> Save Student
            </button>
        </div>
    </form>
</div>

<script>
    function getSections(obj) {
        var class_id = obj.options[obj.selectedIndex].value;
        var sectionSelect = document.getElementById('inputAssignToSection');
        sectionSelect.innerHTML = '<option disabled selected value="">Loading sections...</option>';

        var url = "{{route('get.sections.courses.by.classId')}}?class_id=" + class_id;

        fetch(url)
        .then((resp) => resp.json())
        .then(function(data) {
            sectionSelect.options.length = 0;
            sectionSelect.options[0] = new Option('Please select a section', '');
            sectionSelect.options[0].disabled = true;
            sectionSelect.options[0].selected = true;

            if (data.sections && data.sections.length > 0) {
                data.sections.forEach(function(section) {
                    sectionSelect.add(new Option(section.section_name, section.id));
                });
            }
        })
        .catch(function(error) {
            console.error('Error fetching sections:', error);
            sectionSelect.innerHTML = '<option disabled selected value="">Failed to load sections</option>';
        });
    }
</script>

@include('components.photos.photo-input')
@endsection
