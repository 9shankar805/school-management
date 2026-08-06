@extends('layouts.app')
@section('title', 'Live Chat')

@push('head')
<style>
    .chat-container { height: calc(100vh - 200px); }
    .chat-sidebar { border-right: 1px solid #e5e7eb; overflow-y: auto; }
    .chat-messages { overflow-y: auto; flex-grow: 1; background-color: #f8fafc; }
    .message-bubble { max-width: 75%; padding: 12px 16px; border-radius: 12px; margin-bottom: 8px; }
    .message-in { background-color: white; border: 1px solid #e5e7eb; border-bottom-left-radius: 4px; align-self: flex-start; }
    .message-out { background-color: #4f46e5; color: white; border-bottom-right-radius: 4px; align-self: flex-end; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-chat-dots text-green-500"></i> Live Chat</h2>
            <p class="text-muted mb-0">Real-time messaging with staff, students, and parents.</p>
        </div>
        <a href="{{ route('communication.index') }}" class="btn btn-outline-secondary shadow-sm"><i class="bi bi-arrow-left"></i> Back to Hub</a>
    </div>

    <!-- Chat App Layout -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden chat-container d-flex flex-row">
        
        <!-- Sidebar (Contacts/Channels) -->
        <div class="chat-sidebar w-25 bg-white d-flex flex-column">
            <div class="p-3 border-bottom">
                <input type="text" class="form-control" placeholder="Search chats...">
            </div>
            <div class="list-group list-group-flush flex-grow-1 overflow-y-auto">
                <a href="#" class="list-group-item list-group-item-action p-3 active border-start border-4 border-primary">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1 font-bold">Grade 10 Teachers</h6>
                        <small>10:42 AM</small>
                    </div>
                    <p class="mb-1 text-sm text-truncate">Are we still meeting at 3?</p>
                </a>
                <a href="#" class="list-group-item list-group-item-action p-3">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1 font-bold">John Doe (Parent)</h6>
                        <small class="text-muted">Yesterday</small>
                    </div>
                    <p class="mb-1 text-sm text-truncate text-muted">Thank you for the update.</p>
                </a>
                <a href="#" class="list-group-item list-group-item-action p-3">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1 font-bold">School Admin Group</h6>
                        <small class="text-muted">Mon</small>
                    </div>
                    <p class="mb-1 text-sm text-truncate text-muted">System maintenance scheduled.</p>
                </a>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="w-75 d-flex flex-column bg-white">
            <!-- Chat Header -->
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white shadow-sm z-1">
                <div>
                    <h5 class="mb-0 font-bold">Grade 10 Teachers</h5>
                    <small class="text-success"><i class="bi bi-circle-fill" style="font-size: 8px;"></i> 5 online</small>
                </div>
                <div>
                    <button class="btn btn-sm btn-light"><i class="bi bi-search"></i></button>
                    <button class="btn btn-sm btn-light"><i class="bi bi-three-dots-vertical"></i></button>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="chat-messages p-4 d-flex flex-column">
                <div class="text-center mb-4"><span class="badge bg-light text-muted">Today</span></div>
                
                <div class="message-bubble message-in shadow-sm">
                    <div class="text-xs text-muted mb-1 font-semibold text-primary">Sarah Connor</div>
                    Hello everyone, just a reminder about the science fair setup.
                    <div class="text-end text-muted mt-1" style="font-size: 10px;">10:30 AM</div>
                </div>

                <div class="message-bubble message-out shadow-sm">
                    I will be there in 10 minutes.
                    <div class="text-end text-light mt-1" style="font-size: 10px;">10:35 AM <i class="bi bi-check2-all text-info"></i></div>
                </div>
                
                <div class="message-bubble message-in shadow-sm">
                    <div class="text-xs text-muted mb-1 font-semibold text-primary">Jane Roe</div>
                    Are we still meeting at 3?
                    <div class="text-end text-muted mt-1" style="font-size: 10px;">10:42 AM</div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-3 bg-white border-top">
                <form class="d-flex gap-2" onsubmit="event.preventDefault(); this.reset();">
                    <button type="button" class="btn btn-light"><i class="bi bi-paperclip"></i></button>
                    <button type="button" class="btn btn-light"><i class="bi bi-emoji-smile"></i></button>
                    <input type="text" class="form-control rounded-pill px-4" placeholder="Type a message...">
                    <button type="submit" class="btn btn-primary rounded-circle" style="width: 40px; height: 40px; padding:0;"><i class="bi bi-send-fill" style="margin-left:-2px;"></i></button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
