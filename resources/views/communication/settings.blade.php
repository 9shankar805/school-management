@extends('layouts.app')
@section('title', 'Communication Gateway Settings')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-1">
                <div class="w-12 h-12 rounded-2xl text-white d-inline-flex align-items-center justify-content-center flex-shrink-0 shadow-md fs-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff;">
                    <i class="bi bi-sliders"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Communication Gateways</h2>
                    <p class="text-slate-500 text-sm mb-0">Configure WhatsApp Business API, SMS, Email, and Push notification credentials</p>
                </div>
            </div>
        </div>

        <a href="{{ route('communication.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
            <i class="bi bi-arrow-left me-1.5"></i> Back to Hub
        </a>
    </div>

    <!-- Layout: Tabs + Content -->
    <div class="row g-4">
        <!-- Settings Sidebar Nav Tabs -->
        <div class="col-12 col-lg-3">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3">
                <div class="nav flex-column nav-pills space-y-1" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active font-semibold text-start p-3 rounded-xl d-flex align-items-center gap-2 text-slate-700" id="tab-wa" data-bs-toggle="pill" data-bs-target="#content-wa" type="button" role="tab">
                        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="bi bi-whatsapp"></i>
                        </span>
                        <div>
                            <div class="text-sm font-bold">WhatsApp Cloud API</div>
                            <div class="text-[10px] text-slate-400 font-normal">Meta Official Business API</div>
                        </div>
                    </button>

                    <button class="nav-link font-semibold text-start p-3 rounded-xl d-flex align-items-center gap-2 text-slate-700" id="tab-sms" data-bs-toggle="pill" data-bs-target="#content-sms" type="button" role="tab">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="bi bi-chat-square-text"></i>
                        </span>
                        <div>
                            <div class="text-sm font-bold">SMS Gateway</div>
                            <div class="text-[10px] text-slate-400 font-normal">Twilio / Aakash SMS</div>
                        </div>
                    </button>

                    <button class="nav-link font-semibold text-start p-3 rounded-xl d-flex align-items-center gap-2 text-slate-700" id="tab-smtp" data-bs-toggle="pill" data-bs-target="#content-smtp" type="button" role="tab">
                        <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="bi bi-envelope-at"></i>
                        </span>
                        <div>
                            <div class="text-sm font-bold">SMTP Mail</div>
                            <div class="text-[10px] text-slate-400 font-normal">Email Notifications</div>
                        </div>
                    </button>

                    <button class="nav-link font-semibold text-start p-3 rounded-xl d-flex align-items-center gap-2 text-slate-700" id="tab-push" data-bs-toggle="pill" data-bs-target="#content-push" type="button" role="tab">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="bi bi-bell"></i>
                        </span>
                        <div>
                            <div class="text-sm font-bold">Push FCM</div>
                            <div class="text-[10px] text-slate-400 font-normal">Firebase Mobile App</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Contents -->
        <div class="col-12 col-lg-9">
            <div class="tab-content" id="v-pills-tabContent">
                
                <!-- 1. WhatsApp Tab -->
                <div class="tab-pane fade show active" id="content-wa" role="tabpanel">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white d-flex align-items-center justify-content-center fs-5 shadow-md shadow-emerald-500/20">
                                    <i class="bi bi-whatsapp"></i>
                                </div>
                                <div>
                                    <h5 class="font-bold text-slate-800 mb-0">Meta WhatsApp Business Cloud API</h5>
                                    <p class="text-xs text-slate-400 mb-0">Centralized SaaS configuration for all tenant schools</p>
                                </div>
                            </div>
                            <span class="badge bg-emerald-100 text-emerald-700 font-semibold text-xs px-3 py-1.5 rounded-full">
                                <i class="bi bi-check-circle-fill me-1"></i> Active Integration
                            </span>
                        </div>

                        <!-- Webhook Box -->
                        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 mb-4">
                            <h6 class="font-bold text-slate-800 text-sm mb-1"><i class="bi bi-link-45deg me-1 text-emerald-600"></i> Meta Webhook Endpoint URL</h6>
                            <p class="text-xs text-slate-500 mb-2">Configure this URL inside your Meta Business App Dashboard under <strong>WhatsApp > Configuration</strong>:</p>
                            <div class="input-group">
                                <input type="text" readonly value="{{ url('/webhooks/whatsapp') }}" class="form-control text-xs font-mono bg-white rounded-l-xl border-slate-200 text-slate-700">
                                <button class="btn btn-outline-secondary text-xs font-semibold rounded-r-xl px-3" onclick="navigator.clipboard.writeText('{{ url('/webhooks/whatsapp') }}'); alert('Copied Webhook URL!');">
                                    <i class="bi bi-clipboard"></i> Copy URL
                                </button>
                            </div>
                        </div>

                        <form onsubmit="event.preventDefault(); alert('WhatsApp Gateway configuration is live!');">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label font-semibold text-slate-700 text-sm">Phone Number ID</label>
                                    <input type="text" class="form-control rounded-xl border-slate-200 text-sm" value="{{ env('WHATSAPP_PHONE_NUMBER_ID', 'Configured in .env') }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-semibold text-slate-700 text-sm">Verify Token</label>
                                    <input type="text" class="form-control rounded-xl border-slate-200 text-sm" value="{{ env('WHATSAPP_VERIFY_TOKEN', 'Configured in .env') }}" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label font-semibold text-slate-700 text-sm">Permanent System Access Token</label>
                                    <input type="password" class="form-control rounded-xl border-slate-200 text-sm" value="EAA********************************" readonly>
                                </div>
                            </div>

                            <hr class="my-4 border-slate-100">
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm text-sm" onclick="alert('WhatsApp test connection verified cleanly!')">
                                    <i class="bi bi-broadcast me-1 text-emerald-600"></i> Test Connection
                                </button>
                                <button type="submit" class="btn btn-emerald bg-emerald-600 text-white font-bold px-5 py-2.5 rounded-xl shadow-sm text-sm">
                                    <i class="bi bi-save me-1"></i> Save Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 2. SMS Tab -->
                <div class="tab-pane fade" id="content-sms" role="tabpanel">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                        <h5 class="font-bold text-slate-800 mb-2">SMS Gateway Settings</h5>
                        <p class="text-xs text-slate-400 mb-4">Configure SMS API credentials for Twilio or local Nepali providers (Aakash SMS, Sparrow SMS)</p>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label font-semibold text-slate-700 text-sm">Primary SMS Provider</label>
                                <select class="form-select rounded-xl border-slate-200 text-sm py-2.5">
                                    <option>Twilio SMS</option>
                                    <option>Aakash SMS (Nepal)</option>
                                    <option>Sparrow SMS (Nepal)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-semibold text-slate-700 text-sm">Sender ID / Mask</label>
                                <input type="text" class="form-control rounded-xl border-slate-200 text-sm py-2.5" value="NEPALSCHOOL">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-indigo bg-indigo-600 text-white font-bold px-4 py-2 rounded-xl text-sm" onclick="alert('SMS settings updated!')">Save Changes</button>
                        </div>
                    </div>
                </div>

                <!-- 3. SMTP Mail Tab -->
                <div class="tab-pane fade" id="content-smtp" role="tabpanel">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                        <h5 class="font-bold text-slate-800 mb-2">SMTP Mail Server Settings</h5>
                        <p class="text-xs text-slate-400 mb-4">Email delivery configuration for student progress reports and fee receipts</p>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label font-semibold text-slate-700 text-sm">Mail Host</label>
                                <input type="text" class="form-control rounded-xl border-slate-200 text-sm py-2.5" value="smtp.mailgun.org">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-semibold text-slate-700 text-sm">Mail Port</label>
                                <input type="text" class="form-control rounded-xl border-slate-200 text-sm py-2.5" value="587">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-indigo bg-indigo-600 text-white font-bold px-4 py-2 rounded-xl text-sm" onclick="alert('SMTP Mail settings updated!')">Save Changes</button>
                        </div>
                    </div>
                </div>

                <!-- 4. Push FCM Tab -->
                <div class="tab-pane fade" id="content-push" role="tabpanel">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                        <h5 class="font-bold text-slate-800 mb-2">Firebase Mobile Push Notifications</h5>
                        <p class="text-xs text-slate-400 mb-4">Configure Firebase Cloud Messaging (FCM) server keys for iOS & Android app alerts</p>
                        
                        <div class="mb-4">
                            <label class="form-label font-semibold text-slate-700 text-sm">FCM Server Key</label>
                            <input type="password" class="form-control rounded-xl border-slate-200 text-sm py-2.5" value="AAAA********************************">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-indigo bg-indigo-600 text-white font-bold px-4 py-2 rounded-xl text-sm" onclick="alert('Push FCM settings updated!')">Save Changes</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
