@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')
        @include('parent.partials.page-header', ['title' => 'Leave Applications'])

        @include('session-messages')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Apply leave form --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4">Apply Leave for {{ $child->first_name }}</p>
                <form method="POST" action="{{ route('parent.leave.submit', $child->id) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Leave Type <span class="text-rose-500">*</span></label>
                            <select name="leave_type_id"
                                    class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('leave_type_id') border-rose-400 @enderror">
                                <option value="">— Select leave type —</option>
                                @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('leave_type_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">From Date <span class="text-rose-500">*</span></label>
                                <input type="date" name="from_date" value="{{ old('from_date') }}"
                                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('from_date') border-rose-400 @enderror">
                                @error('from_date')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">To Date <span class="text-rose-500">*</span></label>
                                <input type="date" name="to_date" value="{{ old('to_date') }}"
                                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('to_date') border-rose-400 @enderror">
                                @error('to_date')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Reason <span class="text-rose-500">*</span></label>
                            <textarea name="reason" rows="3" placeholder="Please provide a reason (min. 10 characters)…"
                                      class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('reason') border-rose-400 @enderror">{{ old('reason') }}</textarea>
                            @error('reason')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <button type="submit" class="mt-4 w-full py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                        <i class="bi bi-send me-1"></i>Submit Application
                    </button>
                </form>
            </div>

            {{-- Previous applications --}}
            <div>
                <p class="text-sm font-semibold text-slate-700 mb-3">Previous Applications</p>
                @if($applications->isEmpty())
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-8 text-center">
                    <i class="bi bi-calendar-x text-slate-200 text-4xl"></i>
                    <p class="text-slate-400 text-sm mt-2">No leave applications yet.</p>
                </div>
                @else
                <div class="space-y-3">
                    @foreach($applications as $app)
                    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-800 text-sm">{{ $app->leaveType?->name ?? 'Leave' }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($app->from_date)->format('d M Y') }}
                                    →
                                    {{ \Carbon\Carbon::parse($app->to_date)->format('d M Y') }}
                                    ({{ $app->total_days }} day{{ $app->total_days != 1 ? 's' : '' }})
                                </p>
                                @if($app->reason)
                                <p class="text-xs text-slate-500 mt-1 truncate">{{ $app->reason }}</p>
                                @endif
                                @if($app->reviewer_notes)
                                <p class="text-xs text-slate-600 bg-slate-50 rounded px-2 py-1 mt-1">
                                    <i class="bi bi-chat-square-text me-1"></i>{{ $app->reviewer_notes }}
                                </p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                <span class="text-[11px] px-2.5 py-1 rounded-full font-semibold {{ $app->status_badge }} capitalize">
                                    {{ $app->status }}
                                </span>
                                @if($app->status === 'pending')
                                <form method="POST" action="{{ route('parent.leave.cancel', [$child->id, $app->id]) }}">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Cancel this leave application?')"
                                            class="text-[11px] px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg hover:bg-rose-50 hover:text-rose-600 transition">
                                        Cancel
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
