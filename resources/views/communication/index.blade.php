@extends('layouts.app')
@section('title', 'Communication Hub')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-1">
                <div class="w-12 h-12 rounded-2xl text-white d-inline-flex align-items-center justify-content-center flex-shrink-0 shadow-md fs-4" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #ffffff;">
                    <i class="bi bi-broadcast"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Communication Hub</h2>
                    <p class="text-slate-500 text-sm mb-0">Manage multi-channel broadcasts, WhatsApp alerts, live chat, and gateway settings</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('communication.settings') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-sliders me-1.5 text-slate-500"></i> Gateway Settings
            </a>
            <a href="{{ route('communication.bulk') }}" class="btn btn-indigo bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold px-4 py-2.5 rounded-xl shadow-md shadow-emerald-500/20 transition">
                <i class="bi bi-whatsapp me-1.5"></i> Send WhatsApp Alert
            </a>
        </div>
    </div>

    @php
        $school = auth()->user()->school;
        $schoolId = $school?->id;
        
        $waSentCount = $schoolId ? \App\Models\WhatsAppMessage::where('school_id', $schoolId)->where('status', 'sent')->count() : 0;
        $waQuota = $school?->whatsapp_quota ?? 5000;
        $waFailedCount = $schoolId ? \App\Models\WhatsAppMessage::where('school_id', $schoolId)->where('status', 'failed')->count() : 0;
        $recentMessages = $schoolId ? \App\Models\WhatsAppMessage::where('school_id', $schoolId)->latest()->take(5)->get() : collect();
    @endphp

    <!-- Metrics Overview Cards -->
    <div class="row g-4 mb-4">
        <!-- WhatsApp Sent Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">WhatsApp Sent</span>
                    <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 d-flex align-items-center justify-content-center fs-5">
                        <i class="bi bi-whatsapp"></i>
                    </span>
                </div>
                <div class="h3 font-extrabold text-slate-800 mb-1">{{ number_format($waSentCount) }}</div>
                <div class="text-xs text-emerald-600 font-medium"><i class="bi bi-check-all me-1"></i> Deliveries logged this session</div>
            </div>
        </div>

        <!-- WhatsApp Quota Remaining Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Quota Remaining</span>
                    <span class="w-10 h-10 rounded-xl bg-teal-100 text-teal-600 d-flex align-items-center justify-content-center fs-5">
                        <i class="bi bi-chat-quote-fill"></i>
                    </span>
                </div>
                <div class="h3 font-extrabold text-slate-800 mb-1">{{ number_format($waQuota) }}</div>
                <div class="text-xs text-slate-500 font-medium">Monthly message balance</div>
            </div>
        </div>

        <!-- In-App / SMS Alerts Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Failed Attempts</span>
                    <span class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 d-flex align-items-center justify-content-center fs-5">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </span>
                </div>
                <div class="h3 font-extrabold text-slate-800 mb-1">{{ number_format($waFailedCount) }}</div>
                <div class="text-xs text-rose-600 font-medium"><i class="bi bi-x-circle me-1"></i> Delivery errors or quota limits</div>
            </div>
        </div>

        <!-- Active Chat Threads -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Messenger Chats</span>
                    <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 d-flex align-items-center justify-content-center fs-5">
                        <i class="bi bi-chat-left-dots-fill"></i>
                    </span>
                </div>
                <div class="h3 font-extrabold text-slate-800 mb-1">Active</div>
                <div class="text-xs text-indigo-600 font-medium">Real-time parent messaging</div>
            </div>
        </div>
    </div>

    <!-- Main Module Action Cards -->
    <div class="row g-4 mb-4">
        <!-- 1. Bulk & WhatsApp Alerts Card -->
        <div class="col-12 col-lg-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm h-100 d-flex flex-column justify-content-between relative overflow-hidden hover:border-emerald-300 transition">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white d-flex align-items-center justify-content-center fs-4 shadow-md shadow-emerald-500/20 mb-4">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 mb-2">WhatsApp & Bulk Alerts</h4>
                    <p class="text-slate-500 text-sm leading-relaxed mb-4">
                        Blast instant notifications to parents, students, and staff across WhatsApp, SMS, In-App notices, and Mobile Push.
                    </p>
                    <ul class="text-xs text-slate-600 space-y-2 mb-4">
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-emerald-500"></i> Automatic school name header prefix</li>
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-emerald-500"></i> Class & section filtering</li>
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-emerald-500"></i> Meta Official Cloud API integration</li>
                    </ul>
                </div>
                <a href="{{ route('communication.bulk') }}" class="btn btn-emerald bg-emerald-600 hover:bg-emerald-700 text-white font-bold w-100 py-3 rounded-xl shadow-md shadow-emerald-600/20 transition">
                    <i class="bi bi-send-fill me-1.5"></i> Open Broadcaster
                </a>
            </div>
        </div>

        <!-- 2. Live Chat / Discussions -->
        <div class="col-12 col-lg-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm h-100 d-flex flex-column justify-content-between relative overflow-hidden hover:border-indigo-300 transition">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white d-flex align-items-center justify-content-center fs-4 shadow-md shadow-indigo-600/20 mb-4">
                        <i class="bi bi-chat-quote-fill"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 mb-2">Parent & Staff Messenger</h4>
                    <p class="text-slate-500 text-sm leading-relaxed mb-4">
                        Engage in 1-on-1 conversations or group discussions with parents, students, and subject teachers.
                    </p>
                    <ul class="text-xs text-slate-600 space-y-2 mb-4">
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-indigo-500"></i> Real-time socket chat</li>
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-indigo-500"></i> Unread message badges</li>
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-indigo-500"></i> Document attachment support</li>
                    </ul>
                </div>
                <a href="{{ route('communication.chat') }}" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold w-100 py-3 rounded-xl shadow-md shadow-indigo-600/20 transition">
                    <i class="bi bi-chat-fill me-1.5"></i> Launch Messenger
                </a>
            </div>
        </div>

        <!-- 3. Notice Board -->
        <div class="col-12 col-lg-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm h-100 d-flex flex-column justify-content-between relative overflow-hidden hover:border-blue-300 transition">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white d-flex align-items-center justify-content-center fs-4 shadow-md shadow-blue-600/20 mb-4">
                        <i class="bi bi-pin-angle-fill"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 mb-2">School Notice Board</h4>
                    <p class="text-slate-500 text-sm leading-relaxed mb-4">
                        Post official notices, circulars, and event flyers directly onto the student and parent dashboard bulletin boards.
                    </p>
                    <ul class="text-xs text-slate-600 space-y-2 mb-4">
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-blue-500"></i> Public circular board</li>
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-blue-500"></i> PDF attachment uploads</li>
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-blue-500"></i> Expiry date scheduling</li>
                    </ul>
                </div>
                <a href="{{ route('notice.create') }}" class="btn btn-blue bg-blue-600 hover:bg-blue-700 text-white font-bold w-100 py-3 rounded-xl shadow-md shadow-blue-600/20 transition">
                    <i class="bi bi-plus-circle-fill me-1.5"></i> Post New Notice
                </a>
            </div>
        </div>
    </div>

    <!-- Recent WhatsApp Delivery Audit Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-1"><i class="bi bi-whatsapp text-emerald-600 me-2"></i> Recent WhatsApp Deliveries</h5>
                <p class="text-xs text-slate-400 mb-0">Audit log of automated and manual WhatsApp alerts sent by your school</p>
            </div>
            <a href="{{ route('communication.bulk') }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 font-semibold px-3 py-1.5 rounded-lg text-xs">
                View All
            </a>
        </div>

        @if($recentMessages->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Recipient Phone</th>
                        <th class="py-3 px-4">Trigger Module</th>
                        <th class="py-3 px-4">Delivery Status</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentMessages as $msg)
                    <tr>
                        <td class="py-3 px-4 font-semibold text-slate-800">
                            <i class="bi bi-telephone text-slate-400 me-1.5"></i> {{ $msg->recipient_phone }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-semibold uppercase">
                                {{ $msg->module ?? 'general' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($msg->status === 'sent')
                            <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Sent Successfully
                            </span>
                            @else
                            <span class="badge bg-rose-100 text-rose-700 text-xs px-2.5 py-1 rounded-lg font-semibold" title="{{ $msg->error_message }}">
                                <i class="bi bi-x-circle-fill me-1"></i> Failed
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-end text-xs text-slate-400">
                            {{ $msg->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-chat-left-dots fs-1 text-slate-300 mb-2 block"></i>
            <p class="mb-0 text-sm">No WhatsApp messages sent yet. Send your first alert using the Broadcaster above!</p>
        </div>
        @endif
    </div>

</div>
@endsection
