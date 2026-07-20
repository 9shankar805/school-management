@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Messages</h1>
            <a href="{{ route('parent.conversation.new') }}"
               class="text-sm px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                <i class="bi bi-pencil-square me-1"></i>New Message
            </a>
        </div>

        @include('session-messages')

        @if($conversations->isEmpty())
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-12 text-center">
            <i class="bi bi-chat-dots text-slate-200 text-5xl"></i>
            <p class="text-slate-500 mt-3 text-sm">No conversations yet.</p>
            <a href="{{ route('parent.conversation.new') }}" class="mt-3 inline-block text-sm text-indigo-600 hover:underline">Start a conversation with a teacher</a>
        </div>
        @else
        <div class="space-y-2">
            @foreach($conversations as $conv)
            <a href="{{ route('parent.conversation.show', $conv->id) }}"
               class="block bg-white rounded-xl border border-slate-100 shadow-sm p-4 hover:border-indigo-200 transition">
                <div class="flex items-start gap-3">
                    <img src="{{ $conv->teacher->avatar }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0 mt-0.5" alt="">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-slate-800 text-sm truncate">{{ $conv->subject }}</p>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($conv->unread_count > 0)
                                <span class="text-[10px] bg-rose-500 text-white rounded-full px-1.5 py-0.5 font-bold">
                                    {{ $conv->unread_count }}
                                </span>
                                @endif
                                <span class="text-[11px] text-slate-400">{{ $conv->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Teacher: <span class="text-slate-600">{{ $conv->teacher->full_name }}</span>
                            &middot; Re: <span class="text-slate-600">{{ $conv->student->full_name }}</span>
                        </p>
                        @if($conv->latestMessage)
                        <p class="text-xs text-slate-500 mt-1 truncate">{{ $conv->latestMessage->body }}</p>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif

    </div>
</div>
@endsection
