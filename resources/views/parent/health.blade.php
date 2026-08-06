@extends('layouts.app')

@section('content')

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')
        @include('parent.partials.page-header', ['title' => 'Medical & Health Records'])

        @include('session-messages')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden mb-6">
                    <div class="bg-indigo-50 border-b border-indigo-100 px-6 py-4 flex items-center justify-between">
                        <h3 class="font-bold text-indigo-900 flex items-center gap-2">
                            <i class="bi bi-file-medical"></i> Health Profile
                        </h3>
                        <span class="text-xs font-semibold text-indigo-600 bg-white px-2 py-1 rounded shadow-sm border border-indigo-100">Confidential</span>
                    </div>
                    
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Blood Type</p>
                            <p class="font-medium text-slate-800">{{ $record?->blood_type ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Height / Weight</p>
                            <p class="font-medium text-slate-800">
                                {{ $record?->height_cm ? $record->height_cm . ' cm' : '--' }} /
                                {{ $record?->weight_kg ? $record->weight_kg . ' kg' : '--' }}
                                @if($record?->bmi)
                                    <span class="text-slate-400 text-sm ml-2">(BMI: {{ $record->bmi }})</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-span-full border-t border-slate-100 pt-4">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Allergies</p>
                            @if($record?->allergies)
                                <div class="bg-rose-50 text-rose-700 p-3 rounded-lg border border-rose-100 text-sm">
                                    {{ $record->allergies }}
                                </div>
                            @else
                                <p class="text-slate-500 text-sm">None recorded.</p>
                            @endif
                        </div>

                        <div class="col-span-full">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Chronic Conditions</p>
                            <p class="text-slate-700 text-sm">{{ $record?->chronic_conditions ?? 'None recorded.' }}</p>
                        </div>

                        <div class="col-span-full">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Current Medications</p>
                            <p class="text-slate-700 text-sm">{{ $record?->medications ?? 'None recorded.' }}</p>
                        </div>

                        <div class="col-span-full">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Vaccination History</p>
                            <p class="text-slate-700 text-sm">{{ $record?->vaccination_history ?? 'Not provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Emergency Contact Form --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 sticky top-6">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 mb-1">
                        <i class="bi bi-telephone-plus text-rose-500"></i> Emergency Info
                    </h3>
                    <p class="text-sm text-slate-500 mb-6">Update your child's primary emergency medical contact and doctor.</p>

                    <form action="{{ route('parent.health.update', $child->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Doctor's Name</label>
                            <input type="text" name="doctor_name" value="{{ $record->doctor_name ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Doctor's Phone</label>
                            <input type="text" name="doctor_phone" value="{{ $record->doctor_phone ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Emergency Medical Notes</label>
                            <textarea name="emergency_medical_notes" rows="4" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="E.g. In case of allergic reaction...">{{ $record->emergency_medical_notes ?? '' }}</textarea>
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
