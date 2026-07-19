@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        {{-- Breadcrumb --}}
        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a>
            <span class="mx-1">/</span>
            <a href="{{ route('student.list.show') }}" class="hover:text-indigo-600">Students</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">{{ $student->full_name }}</span>
        </nav>

        {{-- Status alerts --}}
        @if(session('status'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
            <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        {{-- Profile header card --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6 flex flex-wrap items-start gap-6">
            <img src="{{ $student->avatar }}" class="w-20 h-20 rounded-2xl object-cover flex-shrink-0 border-2 border-indigo-100" alt="">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-3 mb-1">
                    <h1 class="text-xl font-bold text-slate-800">{{ $student->full_name }}</h1>
                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2.5 py-0.5 rounded-full font-medium">Student</span>
                    @if($currentHouse)
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-medium bg-amber-100 text-amber-700">
                        <i class="bi bi-shield-fill"></i> {{ $currentHouse->house_name }}
                    </span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-4 text-xs text-slate-500 mt-1">
                    @if($promotion_info)
                    <span><i class="bi bi-diagram-3 me-1"></i>{{ $promotion_info->schoolClass?->class_name ?? '—' }} · {{ $promotion_info->section?->section_name ?? '—' }}</span>
                    <span><i class="bi bi-card-text me-1"></i>ID: {{ $promotion_info->id_card_number ?? '—' }}</span>
                    @endif
                    <span><i class="bi bi-envelope me-1"></i>{{ $student->email }}</span>
                    @if($student->phone)<span><i class="bi bi-telephone me-1"></i>{{ $student->phone }}</span>@endif
                </div>
                <div class="flex flex-wrap gap-3 mt-3">
                    <div class="text-center">
                        <p class="text-lg font-bold {{ $attendanceStats['pct'] >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $attendanceStats['pct'] }}%</p>
                        <p class="text-[10px] text-slate-400">Attendance</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-slate-800">{{ $student->marks->count() }}</p>
                        <p class="text-[10px] text-slate-400">Marks</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-indigo-600">{{ $student->scholarships->where('status','active')->count() }}</p>
                        <p class="text-[10px] text-slate-400">Scholarships</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold {{ $student->disciplinaryRecords->where('resolved',false)->count() > 0 ? 'text-rose-600' : 'text-slate-500' }}">{{ $student->disciplinaryRecords->count() }}</p>
                        <p class="text-[10px] text-slate-400">Incidents</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 flex-shrink-0">
                @can('create students')
                <a href="{{ route('student.edit.show', $student->id) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium transition">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('student.id-card', $student->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium transition">
                    <i class="bi bi-credit-card-2-front"></i> ID Card PDF
                </a>
                @endcan
            </div>
        </div>

        {{-- Tabs navigation --}}
        <div class="flex gap-1 overflow-x-auto mb-6 bg-white rounded-xl border border-slate-100 shadow-sm p-1">
            @foreach([
                ['overview',    'bi-person-lines-fill', 'Overview'],
                ['documents',   'bi-folder2-open',      'Documents'],
                ['medical',     'bi-heart-pulse',       'Medical'],
                ['contacts',    'bi-telephone',         'Emergency'],
                ['discipline',  'bi-exclamation-triangle', 'Discipline'],
                ['extras',      'bi-award',             'House & Awards'],
                ['achievements','bi-star-fill',         'Achievements'],
                ['status',      'bi-mortarboard',       'Graduation'],
                ['certificates','bi-file-earmark-text', 'Certificates'],
                ['timeline',    'bi-clock-history',     'Timeline'],
                ['transfers',   'bi-arrow-left-right',  'Transfers'],
            ] as [$id, $icon, $label])
            <button onclick="showTab('{{ $id }}')" id="tab-btn-{{ $id }}"
                class="tab-btn flex-shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition whitespace-nowrap text-slate-500 hover:text-slate-700 hover:bg-slate-50">
                <i class="bi {{ $icon }}"></i> {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- ═══════════════ TAB: OVERVIEW ═══════════════ --}}
        <div id="tab-overview" class="tab-panel">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Personal info --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700">
                        <i class="bi bi-person me-1 text-indigo-500"></i> Personal Information
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-50">
                            @foreach([
                                ['Birthday', $student->birthday?->format('d M Y') ?? '—'],
                                ['Gender', $student->gender ?? '—'],
                                ['Blood Type', $student->blood_type ?? '—'],
                                ['Nationality', $student->nationality ?? '—'],
                                ['Religion', $student->religion ?? '—'],
                                ['Address', ($student->address ?? '') . ' ' . ($student->city ?? '')],
                            ] as [$label, $val])
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-2.5 text-slate-400 font-medium w-1/3">{{ $label }}</td>
                                <td class="px-5 py-2.5 text-slate-700">{{ $val }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Academic + Parent info --}}
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700">
                            <i class="bi bi-mortarboard me-1 text-blue-500"></i> Academic
                        </div>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-slate-50">
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-2.5 text-slate-400 font-medium w-1/3">Class</td>
                                    <td class="px-5 py-2.5 text-slate-700">{{ $promotion_info?->schoolClass?->class_name ?? '—' }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-2.5 text-slate-400 font-medium">Section</td>
                                    <td class="px-5 py-2.5 text-slate-700">{{ $promotion_info?->section?->section_name ?? '—' }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-2.5 text-slate-400 font-medium">Board Reg.</td>
                                    <td class="px-5 py-2.5 text-slate-700">{{ $student->academic_info?->board_reg_no ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @if($student->parent_info)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700">
                            <i class="bi bi-people me-1 text-violet-500"></i> Parents
                        </div>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-slate-50">
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-2.5 text-slate-400 font-medium w-1/3">Father</td>
                                    <td class="px-5 py-2.5 text-slate-700">{{ $student->parent_info->father_name ?? '—' }} · {{ $student->parent_info->father_phone ?? '' }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-2.5 text-slate-400 font-medium">Mother</td>
                                    <td class="px-5 py-2.5 text-slate-700">{{ $student->parent_info->mother_name ?? '—' }} · {{ $student->parent_info->mother_phone ?? '' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══════════════ TAB: DOCUMENTS ═══════════════ --}}
        <div id="tab-documents" class="tab-panel hidden">
            @can('create students')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-upload me-1 text-indigo-500"></i>Upload Document</p>
                <form method="POST" action="{{ route('student.documents.store', $student->id) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Document Type</label>
                        <select name="document_type" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(\App\Models\StudentDocument::TYPES as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Title</label>
                        <input type="text" name="title" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="e.g. Birth Certificate 2024" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">File (PDF/JPG/PNG, max 10MB)</label>
                        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Notes (optional)</label>
                        <input type="text" name="notes" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Upload Document</button>
                    </div>
                </form>
            </div>
            @endcan

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100"><p class="text-sm font-semibold text-slate-700"><i class="bi bi-folder2-open me-1 text-amber-500"></i>Uploaded Documents</p></div>
                @if($student->studentDocuments->count())
                <div class="divide-y divide-slate-50">
                    @foreach($student->studentDocuments as $doc)
                    <div class="px-5 py-3 flex items-center gap-4">
                        <span class="w-9 h-9 rounded-lg {{ $doc->isPdf() ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-blue-600' }} flex items-center justify-center flex-shrink-0 text-lg">
                            <i class="bi {{ $doc->isPdf() ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image' }}"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $doc->title }}</p>
                            <p class="text-xs text-slate-400">{{ \App\Models\StudentDocument::TYPES[$doc->document_type] ?? $doc->document_type }} · {{ $doc->human_size }} · {{ $doc->created_at->format('d M Y') }}</p>
                        </div>
                        @if($doc->is_verified)
                        <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full flex-shrink-0"><i class="bi bi-patch-check-fill"></i> Verified</span>
                        @endif
                        <div class="flex gap-2 flex-shrink-0">
                            <a href="{{ $doc->url }}" target="_blank" class="text-xs text-indigo-600 hover:underline">View</a>
                            @can('create students')
                            <form method="POST" action="{{ route('student.documents.verify', $doc->id) }}">@csrf
                                <button class="text-xs text-slate-500 hover:text-slate-700">{{ $doc->is_verified ? 'Unverify' : 'Verify' }}</button>
                            </form>
                            <form method="POST" action="{{ route('student.documents.destroy', $doc->id) }}">@csrf @method('DELETE')
                                <button class="text-xs text-rose-500 hover:text-rose-700" onclick="return confirm('Delete this document?')">Delete</button>
                            </form>
                            @endcan
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-10">No documents uploaded yet.</p>
                @endif
            </div>
        </div>

        {{-- ═══════════════ TAB: MEDICAL ═══════════════ --}}
        <div id="tab-medical" class="tab-panel hidden">
            @can('create students')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-heart-pulse me-1 text-rose-500"></i>Medical Record</p>
                @php $med = $student->medicalRecord; @endphp
                <form method="POST" action="{{ route('student.medical.upsert', $student->id) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    @foreach([
                        ['allergies','Allergies'],['chronic_conditions','Chronic Conditions'],
                        ['medications','Current Medications'],['vaccination_history','Vaccination History'],
                        ['special_needs','Special Needs'],['emergency_medical_notes','Emergency Medical Notes'],
                    ] as [$field, $label])
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ $label }}</label>
                        <textarea name="{{ $field }}" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old($field, $med?->$field) }}</textarea>
                    </div>
                    @endforeach
                    @foreach([
                        ['blood_type','Blood Type','text'],['height_cm','Height (cm)','number'],
                        ['weight_kg','Weight (kg)','number'],['eye_condition','Eye Condition','text'],
                        ['hearing_condition','Hearing','text'],['doctor_name','Doctor Name','text'],
                        ['doctor_phone','Doctor Phone','text'],
                    ] as [$field, $label, $type])
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ $label }}</label>
                        <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field, $med?->$field) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" step="0.1">
                    </div>
                    @endforeach
                    <div class="md:col-span-2">
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-medium transition">Save Medical Record</button>
                    </div>
                </form>
            </div>
            @endcan
            @if(!$student->medicalRecord)
            <p class="text-sm text-slate-400 text-center py-6">No medical record yet.</p>
            @endif
        </div>

        {{-- ═══════════════ TAB: EMERGENCY CONTACTS ═══════════════ --}}
        <div id="tab-contacts" class="tab-panel hidden">
            @can('create students')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-telephone me-1 text-blue-500"></i>Add Emergency Contact</p>
                <form method="POST" action="{{ route('student.contacts.store', $student->id) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    @foreach([['name','Full Name'],['relationship','Relationship'],['phone','Phone'],['phone_alt','Alternate Phone'],['email','Email']] as [$f,$l])
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ $l }}</label>
                        <input type="text" name="{{ $f }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" {{ in_array($f,['name','relationship','phone']) ? 'required' : '' }}>
                    </div>
                    @endforeach
                    <div class="flex gap-6 items-center">
                        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                            <input type="checkbox" name="is_primary" value="1" class="rounded"> Primary contact
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                            <input type="checkbox" name="is_authorized_pickup" value="1" class="rounded"> Authorized pickup
                        </label>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">Add Contact</button>
                    </div>
                </form>
            </div>
            @endcan
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100"><p class="text-sm font-semibold text-slate-700">Emergency Contacts</p></div>
                @if($student->emergencyContacts->count())
                <div class="divide-y divide-slate-50">
                    @foreach($student->emergencyContacts as $ec)
                    <div class="px-5 py-3 flex items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-slate-700">{{ $ec->name }}</p>
                                @if($ec->is_primary)<span class="text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-full">Primary</span>@endif
                                @if($ec->is_authorized_pickup)<span class="text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full">Pickup</span>@endif
                            </div>
                            <p class="text-xs text-slate-400">{{ $ec->relationship }} · {{ $ec->phone }}{{ $ec->phone_alt ? ' / '.$ec->phone_alt : '' }}</p>
                            @if($ec->email)<p class="text-xs text-slate-400">{{ $ec->email }}</p>@endif
                        </div>
                        @can('create students')
                        <form method="POST" action="{{ route('student.contacts.destroy', $ec->id) }}">@csrf @method('DELETE')
                            <button class="text-xs text-rose-500 hover:text-rose-700" onclick="return confirm('Remove?')">Remove</button>
                        </form>
                        @endcan
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-10">No emergency contacts added.</p>
                @endif
            </div>
        </div>


        {{-- ═══════════════ TAB: DISCIPLINE ═══════════════ --}}
        <div id="tab-discipline" class="tab-panel hidden">
            @can('create students')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-exclamation-triangle me-1 text-amber-500"></i>Log Incident</p>
                <form method="POST" action="{{ route('student.discipline.store', $student->id) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Incident Date</label>
                        <input type="date" name="incident_date" value="{{ now()->toDateString() }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Severity</label>
                        <select name="severity" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(\App\Models\DisciplinaryRecord::SEVERITIES as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Incident Type</label>
                        <select name="incident_type" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(\App\Models\DisciplinaryRecord::TYPES as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Description</label>
                        <textarea name="description" rows="3" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Action Taken</label>
                        <textarea name="action_taken" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium transition">Log Incident</button>
                    </div>
                </form>
            </div>
            @endcan

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700">Disciplinary History</p>
                </div>
                @if($student->disciplinaryRecords->count())
                <div class="divide-y divide-slate-50">
                    @foreach($student->disciplinaryRecords as $rec)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $rec->severity_badge }}">{{ ucfirst($rec->severity) }}</span>
                                    <span class="text-sm font-semibold text-slate-700">{{ $rec->incident_type }}</span>
                                    <span class="text-xs text-slate-400">{{ $rec->incident_date->format('d M Y') }}</span>
                                    @if($rec->resolved)<span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">Resolved</span>@endif
                                </div>
                                <p class="text-sm text-slate-600 mb-1">{{ $rec->description }}</p>
                                @if($rec->action_taken)<p class="text-xs text-slate-400"><span class="font-medium">Action:</span> {{ $rec->action_taken }}</p>@endif
                                @if($rec->reporter)<p class="text-xs text-slate-400">Reported by {{ $rec->reporter->full_name }}</p>@endif
                            </div>
                            @can('create students')
                            <div class="flex gap-2 flex-shrink-0">
                                <form method="POST" action="{{ route('student.discipline.resolve', $rec->id) }}">@csrf
                                    <button class="text-xs text-indigo-600 hover:underline">{{ $rec->resolved ? 'Unresolve' : 'Resolve' }}</button>
                                </form>
                                <form method="POST" action="{{ route('student.discipline.destroy', $rec->id) }}">@csrf @method('DELETE')
                                    <button class="text-xs text-rose-500 hover:text-rose-700" onclick="return confirm('Delete?')">Delete</button>
                                </form>
                            </div>
                            @endcan
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-10">No disciplinary records.</p>
                @endif
            </div>
        </div>

        {{-- ═══════════════ TAB: HOUSE & AWARDS ═══════════════ --}}
        <div id="tab-extras" class="tab-panel hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- House Assignment --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100">
                        <p class="text-sm font-semibold text-slate-700"><i class="bi bi-shield-fill me-1 text-amber-500"></i>Sports House</p>
                    </div>
                    @if($currentHouse)
                    <div class="px-5 py-4">
                        <p class="text-2xl font-bold text-slate-800">{{ $currentHouse->house_name }}</p>
                        @if($currentHouse->house_color)<p class="text-sm text-slate-400">Colour: {{ $currentHouse->house_color }}</p>@endif
                        @if($currentHouse->captain_name)<p class="text-sm text-slate-400">Captain: {{ $currentHouse->captain_name }}</p>@endif
                    </div>
                    @else
                    <p class="text-sm text-slate-400 text-center py-6">Not assigned to a house.</p>
                    @endif
                    @can('create students')
                    <div class="px-5 pb-4 border-t border-slate-50 pt-3">
                        <form method="POST" action="{{ route('student.house.store', $student->id) }}" class="flex flex-wrap gap-3">
                            @csrf
                            <input type="text" name="house_name" placeholder="House name" value="{{ $currentHouse?->house_name }}" required class="flex-1 min-w-0 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <input type="text" name="house_color" placeholder="Colour (optional)" value="{{ $currentHouse?->house_color }}" class="flex-1 min-w-0 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <button type="submit" class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition whitespace-nowrap">Save</button>
                        </form>
                    </div>
                    @endcan
                </div>

                {{-- Scholarships --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100">
                        <p class="text-sm font-semibold text-slate-700"><i class="bi bi-award me-1 text-violet-500"></i>Scholarships</p>
                    </div>
                    @if($student->scholarships->count())
                    <div class="divide-y divide-slate-50">
                        @foreach($student->scholarships as $sc)
                        <div class="px-5 py-3 flex justify-between items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-700">{{ $sc->name }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ \App\Models\Scholarship::TYPES[$sc->type] ?? $sc->type }}
                                    @if($sc->amount) · ${{ number_format($sc->amount) }}@endif
                                    @if($sc->percentage) · {{ $sc->percentage }}%@endif
                                    · Awarded {{ $sc->awarded_date->format('d M Y') }}
                                </p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium flex-shrink-0 {{ $sc->status_badge }}">{{ ucfirst($sc->status) }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-slate-400 text-center py-6">No scholarships.</p>
                    @endif
                    @can('create students')
                    <div class="px-5 py-4 border-t border-slate-50">
                        <form method="POST" action="{{ route('student.scholarships.store', $student->id) }}" class="grid grid-cols-2 gap-3">
                            @csrf
                            <input type="text" name="name" placeholder="Scholarship name" required class="col-span-2 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <select name="type" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                @foreach(\App\Models\Scholarship::TYPES as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                            </select>
                            <input type="date" name="awarded_date" value="{{ now()->toDateString() }}" required class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <input type="number" name="amount" placeholder="Amount ($)" step="0.01" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <input type="text" name="percentage" placeholder="Discount %" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <div class="col-span-2">
                                <button type="submit" class="px-3 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg text-sm font-medium transition">Add Scholarship</button>
                            </div>
                        </form>
                    </div>
                    @endcan
                </div>
            </div>
        </div>

        {{-- ═══════════════ TAB: TIMELINE ═══════════════ --}}
        <div id="tab-timeline" class="tab-panel hidden">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-6"><i class="bi bi-clock-history me-1 text-indigo-500"></i>Student Activity Timeline</p>
                @php
                    $events = collect();
                    // Enrollment
                    $events->push(['date' => $student->created_at, 'icon' => 'bi-person-check-fill', 'color' => 'indigo', 'title' => 'Enrolled', 'desc' => 'Student account created']);
                    // Marks
                    foreach ($student->marks->take(5) as $mark) {
                        $events->push(['date' => $mark->created_at, 'icon' => 'bi-clipboard-data', 'color' => 'blue', 'title' => 'Mark recorded', 'desc' => ($mark->exam?->exam_name ?? 'Exam') . ' · ' . ($mark->course?->name ?? '') . ' · ' . $mark->marks . ' marks']);
                    }
                    // Disciplinary
                    foreach ($student->disciplinaryRecords as $rec) {
                        $events->push(['date' => $rec->created_at, 'icon' => 'bi-exclamation-triangle-fill', 'color' => 'amber', 'title' => 'Incident: ' . $rec->incident_type, 'desc' => ucfirst($rec->severity) . ' · ' . \Illuminate\Support\Str::limit($rec->description, 80)]);
                    }
                    // Scholarships
                    foreach ($student->scholarships as $sc) {
                        $events->push(['date' => $sc->created_at, 'icon' => 'bi-award-fill', 'color' => 'violet', 'title' => 'Scholarship: ' . $sc->name, 'desc' => \App\Models\Scholarship::TYPES[$sc->type] ?? $sc->type]);
                    }
                    // Documents
                    foreach ($student->studentDocuments as $doc) {
                        $events->push(['date' => $doc->created_at, 'icon' => 'bi-file-earmark-check', 'color' => 'emerald', 'title' => 'Document uploaded', 'desc' => $doc->title . ' (' . (\App\Models\StudentDocument::TYPES[$doc->document_type] ?? $doc->document_type) . ')']);
                    }
                    // Transfers
                    foreach ($student->transfers as $tr) {
                        $events->push(['date' => $tr->created_at, 'icon' => 'bi-arrow-left-right', 'color' => 'rose', 'title' => 'Transfer request', 'desc' => ucwords(str_replace('_',' ',$tr->transfer_type)) . ' · ' . ucfirst($tr->status)]);
                    }
                    $events = $events->sortByDesc('date')->values();
                @endphp
                @if($events->count())
                <div class="relative">
                    <div class="absolute left-4 top-0 bottom-0 w-px bg-slate-100"></div>
                    <div class="space-y-5 pl-10">
                        @foreach($events as $ev)
                        @php $colorMap = ['indigo'=>'bg-indigo-500','blue'=>'bg-blue-500','amber'=>'bg-amber-400','violet'=>'bg-violet-500','emerald'=>'bg-emerald-500','rose'=>'bg-rose-500']; @endphp
                        <div class="relative">
                            <div class="absolute -left-10 w-6 h-6 rounded-full flex items-center justify-center text-white text-[10px] shadow {{ $colorMap[$ev['color']] ?? 'bg-slate-400' }}">
                                <i class="bi {{ $ev['icon'] }}"></i>
                            </div>
                            <div class="bg-slate-50 rounded-xl px-4 py-3 hover:bg-slate-100 transition">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-700">{{ $ev['title'] }}</p>
                                    <span class="text-xs text-slate-400 flex-shrink-0">{{ $ev['date']->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $ev['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No activity recorded yet.</p>
                @endif
            </div>
        </div>

        {{-- ═══════════════ TAB: TRANSFERS ═══════════════ --}}
        <div id="tab-transfers" class="tab-panel hidden">
            @can('create students')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-arrow-left-right me-1 text-rose-500"></i>Request Transfer</p>
                <form method="POST" action="{{ route('student.transfer.store', $student->id) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Transfer Type</label>
                        <select name="transfer_type" id="transfer_type_sel" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="inter_class">Inter-Class</option>
                            <option value="inter_section">Inter-Section</option>
                            <option value="inter_school">Inter-School</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Transfer Date</label>
                        <input type="date" name="transfer_date" value="{{ now()->toDateString() }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div id="to_class_wrap">
                        <label class="block text-xs font-medium text-slate-500 mb-1">To Class</label>
                        <select name="to_class_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— Select —</option>
                            @foreach(\App\Models\SchoolClass::all() as $c)
                            <option value="{{ $c->id }}" {{ $promotion_info?->class_id == $c->id ? '' : '' }}>{{ $c->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="to_section_wrap">
                        <label class="block text-xs font-medium text-slate-500 mb-1">To Section</label>
                        <select name="to_section_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— Select —</option>
                            @foreach(\App\Models\Section::all() as $s)
                            <option value="{{ $s->id }}">{{ $s->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="to_school_wrap" class="md:col-span-2 hidden">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Destination School</label>
                        <input type="text" name="to_school" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Reason</label>
                        <textarea name="reason" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-medium transition">Submit Transfer Request</button>
                    </div>
                </form>
            </div>
            @endcan

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100"><p class="text-sm font-semibold text-slate-700">Transfer History</p></div>
                @if($student->transfers->count())
                <div class="divide-y divide-slate-50">
                    @foreach($student->transfers as $tr)
                    <div class="px-5 py-3 flex justify-between items-center gap-3">
                        <div>
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $tr->status_badge }}">{{ ucfirst($tr->status) }}</span>
                                <span class="text-sm font-medium text-slate-700">{{ ucwords(str_replace('_',' ',$tr->transfer_type)) }}</span>
                            </div>
                            <p class="text-xs text-slate-400">
                                {{ $tr->fromClass?->class_name ?? $tr->from_school ?? '—' }}
                                → {{ $tr->toClass?->class_name ?? $tr->to_school ?? '—' }}
                                @if($tr->toSection) / {{ $tr->toSection->section_name }}@endif
                                · {{ $tr->transfer_date->format('d M Y') }}
                            </p>
                            @if($tr->reason)<p class="text-xs text-slate-400 mt-0.5">{{ $tr->reason }}</p>@endif
                        </div>
                        @if($tr->status === 'pending')
                        @can('create students')
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('student.transfer.approve', $tr->id) }}">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <button class="text-xs px-2 py-1 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded-lg transition">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('student.transfer.approve', $tr->id) }}">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <button class="text-xs px-2 py-1 bg-rose-100 text-rose-700 hover:bg-rose-200 rounded-lg transition">Reject</button>
                            </form>
                        </div>
                        @endcan
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No transfer history.</p>
                @endif
            </div>
        </div>

        {{-- ═══════════════ TAB: ACHIEVEMENTS ═══════════════ --}}
        <div id="tab-achievements" class="tab-panel hidden">
            @can('create students')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-star-fill me-1 text-amber-500"></i>Record Achievement</p>
                <form method="POST" action="{{ route('student.achievements.store', $student->id) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Category</label>
                        <select name="category" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(\App\Models\Achievement::CATEGORIES as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Level</label>
                        <select name="level" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(\App\Models\Achievement::LEVELS as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Title <span class="text-rose-400">*</span></label>
                        <input type="text" name="title" required placeholder="e.g. First Place — Science Olympiad" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Award Type</label>
                        <input type="text" name="award_type" placeholder="e.g. Gold Medal" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Issuing Body</label>
                        <input type="text" name="issuing_body" placeholder="e.g. National Science Council" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Date Awarded <span class="text-rose-400">*</span></label>
                        <input type="date" name="awarded_date" value="{{ now()->toDateString() }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Attachment (optional)</label>
                        <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition">Record Achievement</button>
                    </div>
                </form>
            </div>
            @endcan
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-trophy me-1 text-amber-500"></i>Awards ({{ $student->achievements->count() }})</p>
                    <a href="{{ route('achievements.leaderboard') }}" class="text-xs text-indigo-600 hover:underline">Leaderboard</a>
                </div>
                @if($student->achievements->count())
                <div class="divide-y divide-slate-50">
                    @foreach($student->achievements as $ach)
                    <div class="px-5 py-3 flex items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-0.5">
                                <p class="text-sm font-semibold text-slate-700">{{ $ach->title }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $ach->level_badge }}">{{ \App\Models\Achievement::LEVELS[$ach->level] ?? $ach->level }}</span>
                                <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">{{ \App\Models\Achievement::CATEGORIES[$ach->category] ?? $ach->category }}</span>
                            </div>
                            @if($ach->award_type)<p class="text-xs text-slate-500">{{ $ach->award_type }}@if($ach->issuing_body) · {{ $ach->issuing_body }}@endif</p>@endif
                            <p class="text-xs text-slate-400 mt-0.5">{{ $ach->awarded_date->format('d M Y') }}</p>
                            @if($ach->attachment_path)<a href="{{ $ach->attachment_url }}" target="_blank" class="text-xs text-indigo-600 hover:underline mt-0.5 inline-block"><i class="bi bi-paperclip me-1"></i>Attachment</a>@endif
                        </div>
                        @can('create students')
                        <form method="POST" action="{{ route('student.achievements.destroy', $ach->id) }}">@csrf @method('DELETE')
                            <button class="text-xs text-rose-400 hover:text-rose-600" onclick="return confirm('Remove?')"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No achievements yet.</p>
                @endif
            </div>
        </div>

        {{-- ═══════════════ TAB: STATUS / GRADUATION ═══════════════ --}}
        <div id="tab-status" class="tab-panel hidden">
            @php $currentStatus = $student->currentStatus; @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-3">Current Status</p>
                @if($currentStatus)
                <div class="flex items-center gap-3">
                    <span class="text-base font-bold px-4 py-1.5 rounded-full {{ $currentStatus->status_badge }}">{{ \App\Models\StudentStatus::STATUSES[$currentStatus->status] ?? $currentStatus->status }}</span>
                    <p class="text-sm text-slate-500">Since {{ $currentStatus->effective_date->format('d M Y') }}@if($currentStatus->alumni_batch) · {{ $currentStatus->alumni_batch }}@endif</p>
                </div>
                @else
                <span class="text-sm px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 font-medium">Active</span>
                @endif
            </div>
            @can('create students')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-arrow-repeat me-1 text-indigo-500"></i>Change Status</p>
                <form method="POST" action="{{ route('students.graduation.process', $student->id) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">New Status</label>
                    <select name="status" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\StudentStatus::STATUSES as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                    </select></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Effective Date</label>
                    <input type="date" name="effective_date" value="{{ now()->toDateString() }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Alumni Batch</label><input type="text" name="alumni_batch" placeholder="Class of 2025" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Destination School</label><input type="text" name="destination_school" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Certificate No.</label><input type="text" name="graduation_certificate_no" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div><label class="block text-xs font-medium text-slate-500 mb-1">Reason / Notes</label><input type="text" name="reason" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                    <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Update Status</button></div>
                </form>
            </div>
            @endcan
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100"><p class="text-sm font-semibold text-slate-700">Status History</p></div>
                @php $history = $student->statusHistory; @endphp
                @if($history->count())
                <div class="divide-y divide-slate-50">
                    @foreach($history as $sh)
                    <div class="px-5 py-3 flex justify-between items-center gap-3">
                        <div>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $sh->status_badge }}">{{ \App\Models\StudentStatus::STATUSES[$sh->status] ?? $sh->status }}</span>
                            @if($sh->alumni_batch)<span class="text-xs text-slate-500 ml-2">{{ $sh->alumni_batch }}</span>@endif
                            @if($sh->reason)<p class="text-xs text-slate-400 mt-0.5">{{ $sh->reason }}</p>@endif
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs font-medium text-slate-600">{{ $sh->effective_date->format('d M Y') }}</p>
                            <p class="text-xs text-slate-400">{{ $sh->processor?->full_name ?? 'System' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No status history.</p>
                @endif
            </div>
        </div>

        {{-- ═══════════════ TAB: CERTIFICATES ═══════════════ --}}
        <div id="tab-certificates" class="tab-panel hidden">
            @can('create students')
            @php $certTemplates = \App\Models\CertificateTemplate::where('is_active', true)->get(); @endphp
            @if($certTemplates->count())
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-file-earmark-pdf me-1 text-rose-500"></i>Generate Certificate PDF</p>
                <form method="GET" action="{{ route('student.certificate.generate', $student->id) }}" target="_blank" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-0">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Template</label>
                        <select name="template_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach($certTemplates as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-0">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Extra Notes</label>
                        <input type="text" name="extra_notes" placeholder="e.g. with distinction" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <button type="submit" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-medium transition flex-shrink-0">
                        <i class="bi bi-download me-1"></i>Generate PDF
                    </button>
                </form>
            </div>
            @else
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-5 flex gap-3 items-start">
                <i class="bi bi-exclamation-triangle text-amber-500 mt-0.5"></i>
                <div>
                    <p class="text-sm font-medium text-amber-800">No active certificate templates.</p>
                    <a href="{{ route('certificates.create') }}" class="text-xs text-amber-700 hover:underline">Create one →</a>
                </div>
            </div>
            @endif
            @endcan
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-2">About Certificates</p>
                <p class="text-sm text-slate-500 mb-3">Generated on-demand as PDF. Tokens like <code class="bg-slate-100 px-1 rounded text-xs">{{student_name}}</code>, <code class="bg-slate-100 px-1 rounded text-xs">{{class}}</code>, <code class="bg-slate-100 px-1 rounded text-xs">{{date}}</code> are filled automatically.</p>
                <a href="{{ route('certificates.index') }}" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:underline"><i class="bi bi-arrow-right"></i> Manage templates</a>
            </div>
        </div>

    </div>{{-- /content --}}
</div>{{-- /flex wrapper --}}

@endsection

@push('scripts')
<script>
function showTab(id) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('bg-indigo-50','text-indigo-700','font-semibold');
        b.classList.add('text-slate-500');
    });
    var panel = document.getElementById('tab-' + id);
    var btn   = document.getElementById('tab-btn-' + id);
    if (panel) panel.classList.remove('hidden');
    if (btn) { btn.classList.add('bg-indigo-50','text-indigo-700','font-semibold'); btn.classList.remove('text-slate-500'); }
    history.replaceState(null,'','#' + id);
}

// Show correct tab on load (from URL hash or default)
(function() {
    var hash = location.hash.replace('#','') || 'overview';
    showTab(hash);
})();

// Transfer type toggle
var sel = document.getElementById('transfer_type_sel');
if (sel) {
    sel.addEventListener('change', function() {
        var isSchool = this.value === 'inter_school';
        document.getElementById('to_class_wrap').classList.toggle('hidden', isSchool);
        document.getElementById('to_section_wrap').classList.toggle('hidden', isSchool);
        document.getElementById('to_school_wrap').classList.toggle('hidden', !isSchool);
    });
}
</script>
@endpush
