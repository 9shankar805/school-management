<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apply for Admission — {{ $school->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc !important;
            font-family: 'Inter', sans-serif !important;
            color: #0f172a !important;
        }
        .page-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.08);
            padding: 2rem;
        }
        @media (min-width: 640px) {
            .page-card {
                padding: 2.5rem;
            }
        }
        .section-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 0.875rem;
            margin-bottom: 1.25rem;
            border-bottom: 2px solid #f1f5f9;
        }
        .section-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            background-color: #4f46e5;
            color: #ffffff;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 800;
            flex-shrink: 0;
        }
        .section-title {
            font-size: 0.9375rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            margin: 0 !important;
            line-height: 1.25 !important;
        }
        .form-label-modern {
            display: block !important;
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin-bottom: 0.375rem !important;
        }
        .form-input-modern {
            background-color: #ffffff !important;
            color: #0f172a !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 0.75rem !important;
            padding: 0.6875rem 0.875rem !important;
            width: 100% !important;
            box-sizing: border-box !important;
            transition: all 0.2s ease-in-out !important;
        }
        .form-input-modern:focus {
            border-color: #4f46e5 !important;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15) !important;
            outline: none !important;
        }
    </style>
</head>
<body class="min-h-screen">

    {{-- Top Header / Branding --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-3.5 flex items-center justify-between">
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
                    <p class="text-xs text-slate-500 font-medium">
                        {{ $school->district ? $school->district . ', ' : '' }}{{ $school->province ?? 'Nepal' }}
                    </p>
                </div>
            </div>
            <a href="{{ route('school.landing', ['slug' => $school->slug]) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                <i class="bi bi-arrow-left"></i> Back to School Website
            </a>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="max-w-4xl mx-auto px-4 py-8">

        <div class="page-card">

            {{-- Main Form Title Header --}}
            <div class="mb-8 border-b border-slate-100 pb-6">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 mb-3">
                    <i class="bi bi-file-earmark-text-fill"></i> Online Admission Form
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-1">Student Admission Application</h2>
                <p class="text-sm text-slate-600 font-medium">Please fill out all required fields to submit your admission application to <strong>{{ $school->name }}</strong>.</p>
            </div>

            @if(isset($errors) && $errors->any())
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800">
                <div class="font-bold mb-1 flex items-center gap-1.5 text-rose-700">
                    <i class="bi bi-exclamation-triangle-fill"></i> Please correct the errors below:
                </div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admissions.public.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="school_id" value="{{ $school->id }}">

                {{-- Step 1: Applicant Personal Information --}}
                <div class="section-card">
                    <div class="section-header">
                        <span class="section-number">1</span>
                        <h3 class="section-title">Applicant Personal Information</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label-modern" for="first_name">First Name <span class="text-rose-600">*</span></label>
                                <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" class="form-input-modern" placeholder="e.g. Aarav" required>
                            </div>
                            <div>
                                <label class="form-label-modern" for="last_name">Last Name <span class="text-rose-600">*</span></label>
                                <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" class="form-input-modern" placeholder="e.g. Shrestha" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label-modern" for="iemis_id">Student IEMIS ID <span class="text-slate-400 font-normal">(If already assigned)</span></label>
                                <input id="iemis_id" type="text" name="iemis_id" value="{{ old('iemis_id') }}" class="form-input-modern" placeholder="e.g. 270010001-0012">
                            </div>
                            <div>
                                <label class="form-label-modern" for="national_id">Birth Registration No. / National ID (NIN)</label>
                                <input id="national_id" type="text" name="national_id" value="{{ old('national_id') }}" class="form-input-modern" placeholder="e.g. 2072681023419">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label-modern" for="email">Applicant Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-input-modern" placeholder="aarav@gmail.com">
                            </div>
                            <div>
                                <label class="form-label-modern" for="phone">Applicant Phone</label>
                                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="form-input-modern" placeholder="98XXXXXXXX">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label-modern" for="birthday">Date of Birth</label>
                                <input id="birthday" type="date" name="birthday" value="{{ old('birthday') }}" class="form-input-modern">
                            </div>
                            <div>
                                <label class="form-label-modern" for="gender">Gender</label>
                                <select id="gender" name="gender" class="form-input-modern">
                                    <option value="">Select Gender</option>
                                    @foreach(['Male','Female','Other'] as $g)
                                    <option value="{{ $g }}" {{ old('gender')===$g ? 'selected':'' }}>{{ $g }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label-modern" for="blood_type">Blood Group</label>
                                <select id="blood_type" name="blood_type" class="form-input-modern">
                                    <option value="">Select</option>
                                    @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bt)
                                    <option value="{{ $bt }}" {{ old('blood_type')===$bt ? 'selected':'' }}>{{ $bt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label-modern" for="ethnicity">Caste / Ethnicity <span class="text-slate-400 font-normal">(CEHRD Category)</span></label>
                                <select id="ethnicity" name="ethnicity" class="form-input-modern">
                                    <option value="">Select Category</option>
                                    @foreach(['Brahmin/Chhetri','Dalit','Janajati','Madhesi','Muslim','Other'] as $eth)
                                    <option value="{{ $eth }}" {{ old('ethnicity')===$eth ? 'selected':'' }}>{{ $eth }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label-modern" for="mother_tongue">Mother Tongue</label>
                                <input id="mother_tongue" type="text" name="mother_tongue" value="{{ old('mother_tongue') }}" class="form-input-modern" placeholder="e.g. Nepali, Maithili, Newari">
                            </div>
                        </div>

                        <div>
                            <label class="form-label-modern" for="address">Permanent / Current Address</label>
                            <input id="address" type="text" name="address" value="{{ old('address') }}" class="form-input-modern" placeholder="e.g. Baneshwor-10, Kathmandu">
                        </div>
                    </div>
                </div>

                {{-- Step 2: Academic Program Requested --}}
                <div class="section-card">
                    <div class="section-header">
                        <span class="section-number">2</span>
                        <h3 class="section-title">Academic Program Requested</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label-modern" for="class_id">Applying for Grade / Class</label>
                                <select id="class_id" name="class_id" class="form-input-modern">
                                    <option value="">Select Target Class…</option>
                                    @foreach($classes as $c)
                                    <option value="{{ $c->id }}" {{ old('class_id')==$c->id ? 'selected':'' }}>{{ $c->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label-modern" for="session_id">Academic Session</label>
                                <select id="session_id" name="session_id" class="form-input-modern">
                                    @if($currentSession)
                                    <option value="{{ $currentSession->id }}">{{ $currentSession->session_name }}</option>
                                    @endif
                                    @foreach($sessions as $s)
                                    @if(!$currentSession || $s->id != $currentSession->id)
                                    <option value="{{ $s->id }}">{{ $s->session_name }}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label-modern" for="previous_school">Previous School Name</label>
                                <input id="previous_school" type="text" name="previous_school" value="{{ old('previous_school') }}" class="form-input-modern" placeholder="e.g. Modern Boarding School">
                            </div>
                            <div>
                                <label class="form-label-modern" for="previous_class">Previous Grade / Class Passed</label>
                                <input id="previous_class" type="text" name="previous_class" value="{{ old('previous_class') }}" class="form-input-modern" placeholder="e.g. Grade 7">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Parent / Guardian Information --}}
                <div class="section-card">
                    <div class="section-header">
                        <span class="section-number">3</span>
                        <h3 class="section-title">Parent / Guardian Information</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label-modern" for="guardian_name">Guardian Full Name</label>
                                <input id="guardian_name" type="text" name="guardian_name" value="{{ old('guardian_name') }}" class="form-input-modern" placeholder="e.g. Ramesh Shrestha">
                            </div>
                            <div>
                                <label class="form-label-modern" for="guardian_relation">Relationship to Applicant</label>
                                <select id="guardian_relation" name="guardian_relation" class="form-input-modern">
                                    <option value="">Select Relation</option>
                                    @foreach(['Father','Mother','Guardian','Uncle','Aunt','Grandparent','Other'] as $rel)
                                    <option value="{{ $rel }}" {{ old('guardian_relation')===$rel ? 'selected':'' }}>{{ $rel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label-modern" for="guardian_phone">Guardian Phone Number</label>
                                <input id="guardian_phone" type="text" name="guardian_phone" value="{{ old('guardian_phone') }}" class="form-input-modern" placeholder="98XXXXXXXX">
                            </div>
                            <div>
                                <label class="form-label-modern" for="guardian_email">Guardian Email</label>
                                <input id="guardian_email" type="email" name="guardian_email" value="{{ old('guardian_email') }}" class="form-input-modern" placeholder="guardian@gmail.com">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 4: Photo & Verification Documents --}}
                <div class="section-card">
                    <div class="section-header">
                        <span class="section-number">4</span>
                        <h3 class="section-title">Student Photo & Verification Documents</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label-modern" for="student_photo">Student Passport Photo (PP Size) <span class="text-slate-400 font-normal">(JPG, PNG, WebP — Max 3MB)</span></label>
                            <input id="student_photo" type="file" name="student_photo" accept="image/jpeg,image/png,image/webp" class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-xl p-1 bg-slate-50">
                        </div>
                        <div>
                            <label class="form-label-modern" for="birth_certificate_file">Birth Certificate / National ID Document <span class="text-slate-400 font-normal">(PDF, JPG, PNG, WebP — Max 5MB)</span></label>
                            <input id="birth_certificate_file" type="file" name="birth_certificate_file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-xl p-1 bg-slate-50">
                        </div>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="pt-4 flex flex-col sm:flex-row items-center gap-4">
                    <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-lg transition duration-200 flex items-center justify-center gap-2">
                        <i class="bi bi-send-fill"></i> Submit Admission Application
                    </button>
                    <a href="{{ route('school.landing', ['slug' => $school->slug]) }}" class="text-xs font-bold text-slate-500 hover:text-slate-700 no-underline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

    <footer class="text-center py-6 text-xs text-slate-400">
        &copy; {{ date('Y') }} {{ $school->name }} — Powered by NepalSchoolHub
    </footer>
</body>
</html>
