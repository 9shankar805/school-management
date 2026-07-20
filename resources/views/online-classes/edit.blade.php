@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('online-classes.index') }}" class="hover:text-indigo-600">Online Classes</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Edit</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6">Edit Online Class</h1>

        @include('session-messages')

        <div class="max-w-2xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <form action="{{ route('online-classes.update', $onlineClass->id) }}" method="POST" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title', $onlineClass->title) }}" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Description</label>
                    <textarea name="description" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('description', $onlineClass->description) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Platform</label>
                        <select name="platform" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(['google_meet' => '🟢 Google Meet', 'zoom' => '🔵 Zoom', 'teams' => '🟣 Teams', 'custom' => '⚪ Custom'] as $val => $label)
                            <option value="{{ $val }}" {{ old('platform', $onlineClass->platform) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Status</label>
                        <select name="status" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach(['scheduled' => 'Scheduled', 'live' => 'Live', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                            <option value="{{ $val }}" {{ old('status', $onlineClass->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Meeting URL</label>
                    <input type="url" name="meeting_url" value="{{ old('meeting_url', $onlineClass->meeting_url) }}" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Meeting ID</label>
                        <input type="text" name="meeting_id" value="{{ old('meeting_id', $onlineClass->meeting_id) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Password</label>
                        <input type="text" name="meeting_password" value="{{ old('meeting_password', $onlineClass->meeting_password) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Scheduled At</label>
                        <input type="datetime-local" name="scheduled_at"
                               value="{{ old('scheduled_at', $onlineClass->scheduled_at->format('Y-m-d\TH:i')) }}" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Duration (min)</label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $onlineClass->duration_minutes) }}"
                               min="15" max="300" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Recording URL (after class)</label>
                    <input type="url" name="recording_url" value="{{ old('recording_url', $onlineClass->recording_url) }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="https://drive.google.com/...">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Save Changes</button>
                    <a href="{{ route('online-classes.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
