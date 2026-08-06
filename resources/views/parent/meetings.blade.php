@extends('layouts.app')

@section('content')

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')
        @include('parent.partials.page-header', ['title' => 'Parent-Teacher Meetings'])

        @include('session-messages')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 space-y-6">
                {{-- Upcoming Meetings --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="bg-slate-50 border-b border-slate-100 px-6 py-4">
                        <h3 class="font-semibold text-slate-800">Upcoming Meetings</h3>
                    </div>
                    <div class="p-0">
                        @forelse($upcoming as $meeting)
                            <div class="flex items-center justify-between p-6 border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                        {{ \Carbon\Carbon::parse($meeting['date'])->format('d') }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800">{{ $meeting['subject'] }}</h4>
                                        <p class="text-sm text-slate-500">With {{ $meeting['teacher'] }} • {{ \Carbon\Carbon::parse($meeting['date'])->format('M d, Y g:i A') }}</p>
                                    </div>
                                </div>
                                <span class="bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-md uppercase font-semibold">
                                    {{ $meeting['status'] }}
                                </span>
                            </div>
                        @empty
                            <div class="p-8 text-center">
                                <p class="text-slate-500">No upcoming meetings scheduled.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Past Meetings --}}
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="bg-slate-50 border-b border-slate-100 px-6 py-4">
                        <h3 class="font-semibold text-slate-800">Past Meetings</h3>
                    </div>
                    <div class="p-0">
                        @forelse($past as $meeting)
                            <div class="flex items-center justify-between p-6 border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold">
                                        {{ \Carbon\Carbon::parse($meeting['date'])->format('d') }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-700">{{ $meeting['subject'] }}</h4>
                                        <p class="text-sm text-slate-500">With {{ $meeting['teacher'] }} • {{ \Carbon\Carbon::parse($meeting['date'])->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <span class="bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-md uppercase font-semibold">
                                    {{ $meeting['status'] }}
                                </span>
                            </div>
                        @empty
                            <div class="p-8 text-center">
                                <p class="text-slate-500">No past meetings.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Request Meeting Form --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 sticky top-6">
                    <h3 class="font-bold text-slate-800 mb-1">Request a Meeting</h3>
                    <p class="text-sm text-slate-500 mb-6">Schedule a 1-on-1 meeting with your child's teachers.</p>

                    <form action="{{ route('parent.meetings.store', $child->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Teacher</label>
                            <select name="teacher_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="">Select a teacher...</option>
                                <option value="1">Mr. Smith (Math)</option>
                                <option value="2">Mrs. Doe (Science)</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Subject / Reason</label>
                            <input type="text" name="subject" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Discuss math grades" required>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Preferred Date & Time</label>
                            <input type="datetime-local" name="date" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                            Request Meeting
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
