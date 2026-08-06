@extends('layouts.app')
@section('title', 'Contact Message — ' . $contactMessage->name)

@section('content')
<div class="container-fluid px-4 py-4" style="max-width:900px;">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('super.contact-messages.index') }}"
           class="btn btn-light border border-slate-200 text-slate-600 rounded-xl px-3 py-2 text-sm font-semibold">
            <i class="bi bi-arrow-left me-1"></i> Back to Inbox
        </a>
        <div>
            <h2 class="h4 font-extrabold text-slate-800 mb-0">{{ $contactMessage->subject }}</h2>
            <p class="text-xs text-slate-400 mb-0">Received {{ $contactMessage->created_at->format('l, F j, Y \a\t g:i A') }}</p>
        </div>
    </div>

    @include('session-messages')

    <div class="row g-4">

        {{-- Left: Message content --}}
        <div class="col-12 col-lg-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
                {{-- Sender info --}}
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 font-extrabold d-flex align-items-center justify-content-center flex-shrink-0" style="font-size:1.2rem;">
                        {{ strtoupper(substr($contactMessage->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="font-bold text-slate-800 text-base">{{ $contactMessage->name }}</div>
                        <a href="mailto:{{ $contactMessage->email }}" class="text-indigo-600 text-sm font-medium hover:underline">
                            {{ $contactMessage->email }}
                        </a>
                        @if($contactMessage->school_name)
                        <span class="badge bg-slate-100 text-slate-700 text-xs px-2 py-0.5 rounded-lg font-semibold ms-2">
                            <i class="bi bi-building me-1"></i>{{ $contactMessage->school_name }}
                        </span>
                        @endif
                    </div>
                    <div>{!! $contactMessage->status_badge !!}</div>
                </div>

                {{-- Subject badge --}}
                <div class="mb-3">
                    <span class="badge bg-indigo-50 text-indigo-700 text-xs px-3 py-1.5 rounded-xl font-semibold">
                        <i class="bi bi-tag-fill me-1"></i>{{ $contactMessage->subject }}
                    </span>
                </div>

                {{-- Message body --}}
                <div class="bg-slate-50 rounded-xl p-4 text-slate-700 text-sm leading-relaxed" style="white-space:pre-wrap;">{{ $contactMessage->message }}</div>

                {{-- IP / meta --}}
                <div class="d-flex align-items-center gap-3 mt-3 text-xs text-slate-400">
                    @if($contactMessage->ip_address)
                    <span><i class="bi bi-globe me-1"></i>IP: {{ $contactMessage->ip_address }}</span>
                    @endif
                    <span><i class="bi bi-clock me-1"></i>{{ $contactMessage->created_at->diffForHumans() }}</span>
                </div>
            </div>

            {{-- Quick reply button --}}
            <a href="mailto:{{ $contactMessage->email }}?subject=Re: {{ urlencode($contactMessage->subject) }}"
               class="btn btn-indigo bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-bold px-5 py-2.5 rounded-xl shadow-md transition w-100 d-flex align-items-center justify-content-center gap-2 mb-4">
                <i class="bi bi-reply-fill"></i> Reply via Email
            </a>
        </div>

        {{-- Right: Actions & Notes --}}
        <div class="col-12 col-lg-4">

            {{-- Status control --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
                <h6 class="font-bold text-slate-700 mb-3 text-sm">
                    <i class="bi bi-toggle-on text-indigo-500 me-1.5"></i> Update Status
                </h6>
                <form method="POST" action="{{ route('super.contact-messages.status', $contactMessage) }}">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select form-select-sm rounded-xl border-slate-200 mb-3 text-sm font-medium text-slate-800">
                        <option value="unread"   @selected($contactMessage->status === 'unread')>📬 Unread</option>
                        <option value="read"     @selected($contactMessage->status === 'read')>📖 Read</option>
                        <option value="replied"  @selected($contactMessage->status === 'replied')>✅ Replied</option>
                        <option value="archived" @selected($contactMessage->status === 'archived')>🗃️ Archived</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-indigo rounded-xl w-100 font-semibold text-xs py-2">
                        Save Status
                    </button>
                </form>
            </div>

            {{-- Admin notes --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
                <h6 class="font-bold text-slate-700 mb-3 text-sm">
                    <i class="bi bi-pencil-square text-amber-500 me-1.5"></i> Internal Notes
                </h6>
                <form method="POST" action="{{ route('super.contact-messages.notes', $contactMessage) }}">
                    @csrf @method('PATCH')
                    <textarea name="admin_notes" rows="5"
                              class="form-control form-control-sm rounded-xl border-slate-200 text-sm mb-3"
                              placeholder="Add private notes about this message…">{{ $contactMessage->admin_notes }}</textarea>
                    <button type="submit" class="btn btn-sm btn-amber rounded-xl w-100 font-semibold text-xs py-2">
                        <i class="bi bi-save me-1"></i> Save Notes
                    </button>
                </form>
            </div>

            {{-- Delete --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <h6 class="font-bold text-slate-700 mb-3 text-sm">
                    <i class="bi bi-trash text-rose-500 me-1.5"></i> Delete Message
                </h6>
                <p class="text-xs text-slate-400 mb-3">Permanently removes this message from the inbox. Cannot be undone.</p>
                <form method="POST" action="{{ route('super.contact-messages.destroy', $contactMessage) }}"
                      onsubmit="return confirm('Delete this message permanently?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-xl w-100 font-semibold text-xs py-2">
                        <i class="bi bi-trash me-1"></i> Delete Permanently
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
