@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-4">
            <a href="{{ route('parent.conversations') }}" class="text-xs text-indigo-600 hover:underline">
                <i class="bi bi-arrow-left me-1"></i>Back to Messages
            </a>
        </div>

        {{-- Conversation header --}}
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 mb-4 flex items-center gap-3">
            <img src="{{ $conversation->teacher->avatar }}" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow" alt="">
            <div class="flex-1 min-w-0">
                <p class="font-bold text-slate-800 text-sm truncate">{{ $conversation->subject }}</p>
                <p class="text-xs text-slate-400">
                    With: <span class="text-slate-600">{{ $conversation->teacher->full_name }}</span>
                    &middot; Re: <span class="text-slate-600">{{ $conversation->student->full_name }}</span>
                </p>
            </div>
        </div>

        @include('session-messages')

        {{-- Message thread --}}
        <div class="space-y-3 mb-5" id="messageThread">
            @forelse($conversation->messages as $msg)
            @php $isMe = $msg->sender_id === auth()->id(); @endphp
            <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} gap-2">
                @if(!$isMe)
                <img src="{{ $msg->sender->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0 mt-1" alt="">
                @endif
                <div class="max-w-[70%]">
                    <div class="rounded-2xl px-4 py-2.5 {{ $isMe ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-white border border-slate-100 shadow-sm text-slate-800 rounded-bl-sm' }}">
                        <p class="text-sm leading-relaxed">{{ $msg->body }}</p>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1 {{ $isMe ? 'text-right' : 'text-left' }}">
                        {{ $msg->sender->first_name }} &middot; {{ $msg->created_at->format('d M, h:i A') }}
                        @if($isMe && $msg->read_at)
                        &middot; <i class="bi bi-check2-all text-indigo-400"></i>
                        @endif
                    </p>
                </div>
                @if($isMe)
                <img src="{{ $msg->sender->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0 mt-1" alt="">
                @endif
            </div>
            @empty
            <p class="text-center text-slate-400 text-sm py-6">No messages yet.</p>
            @endforelse
        </div>

        {{-- Reply form --}}
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 sticky bottom-0">
            <form method="POST" action="{{ route('parent.conversation.reply', $conversation->id) }}" class="flex gap-3 items-end">
                @csrf
                <div class="flex-1">
                    <textarea name="body" rows="2"
                              placeholder="Write a reply…"
                              maxlength="2000"
                              class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none @error('body') border-rose-400 @enderror">{{ old('body') }}</textarea>
                    @error('body')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit"
                        class="px-4 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition flex-shrink-0">
                    <i class="bi bi-send"></i>
                </button>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Scroll thread to bottom on load
    document.addEventListener('DOMContentLoaded', function () {
        const thread = document.getElementById('messageThread');
        if (thread) thread.scrollIntoView({ behavior: 'smooth', block: 'end' });
    });
</script>
@endpush
