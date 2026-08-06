@extends('layouts.app')
@section('title', 'Notification Center')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-bell text-warning"></i> Notification Center</h2>
            <p class="text-muted mb-0">View all your system alerts and announcements.</p>
        </div>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary shadow-sm"><i class="bi bi-check2-all"></i> Mark All as Read</button>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            @php
                $notifications = auth()->user()->notifications;
            @endphp

            @if($notifications->isEmpty())
                <div class="text-center py-5">
                    <div class="bg-gray-100 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-bell-slash text-gray-400 fs-1"></i>
                    </div>
                    <h5 class="font-bold text-gray-600">You're all caught up!</h5>
                    <p class="text-muted">No new notifications to show right now.</p>
                </div>
            @else
                <div class="list-group list-group-flush rounded-4">
                    @foreach($notifications as $notification)
                        <div class="list-group-item p-4 {{ is_null($notification->read_at) ? 'bg-slate-50 border-start border-4 border-primary' : 'bg-white' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="font-bold {{ is_null($notification->read_at) ? 'text-gray-900' : 'text-gray-700' }} mb-1">
                                        {{ $notification->data['title'] ?? 'System Notification' }}
                                    </h6>
                                    <p class="text-muted text-sm mb-2">
                                        {{ $notification->data['message'] ?? 'You have a new message.' }}
                                    </p>
                                    <span class="text-xs text-gray-400"><i class="bi bi-clock"></i> {{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="d-flex gap-2">
                                    @if(is_null($notification->read_at))
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light text-primary" title="Mark as read"><i class="bi bi-check-circle"></i></button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
