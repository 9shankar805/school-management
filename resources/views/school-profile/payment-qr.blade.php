@extends('layouts.app')
@section('title', 'Payment QR Codes')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-4xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="w-12 h-12 rounded-2xl bg-emerald-600/10 text-emerald-600 flex items-center justify-center text-2xl shadow-sm">
                <i class="bi bi-qr-code-scan"></i>
            </span>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Payment QR Codes</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Upload your school's eSewa, Khalti, Bank, or FonePay QR. Parents scan and pay directly from their phone.
                </p>
            </div>
        </div>
        <a href="{{ route('school.profile.edit') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i> School Profile
        </a>
    </div>

    @include('session-messages')

    {{-- Info banner --}}
    <div class="mb-6 p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 flex items-start gap-3">
        <i class="bi bi-info-circle-fill text-blue-500 text-lg flex-shrink-0 mt-0.5"></i>
        <div class="text-sm text-blue-800 dark:text-blue-300">
            <p class="font-semibold mb-1">How it works</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-blue-700 dark:text-blue-300">
                <li>Upload one or more QR images below — only those you upload will appear on invoices.</li>
                <li>When a parent opens a fee invoice, they see a <strong>"Scan to Pay"</strong> panel with these QR codes.</li>
                <li>Parent opens eSewa / Khalti / bank app → scans QR → enters the invoice amount → pays.</li>
                <li>After payment, staff records it manually via <a href="{{ route('payments.index') }}" class="underline">Payments → Record Payment</a>.</li>
            </ul>
        </div>
    </div>

    <form method="POST" action="{{ route('school.profile.payment-qr.update') }}" enctype="multipart/form-data">
        @csrf

        {{-- QR Upload Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
            @foreach([
                ['field' => 'payment_qr_esewa',   'label' => 'eSewa',              'subtitle' => 'Merchant QR from eSewa app',        'color' => '#60bb46', 'bg' => '#f0fdf4', 'border' => '#bbf7d0', 'icon' => 'bi-phone-fill'],
                ['field' => 'payment_qr_khalti',  'label' => 'Khalti',             'subtitle' => 'QR from Khalti merchant dashboard', 'color' => '#5c2d91', 'bg' => '#faf5ff', 'border' => '#e9d5ff', 'icon' => 'bi-phone-fill'],
                ['field' => 'payment_qr_bank',    'label' => 'Bank / Connect IPS', 'subtitle' => 'Bank account QR or Connect IPS',   'color' => '#1d4ed8', 'bg' => '#eff6ff', 'border' => '#bfdbfe', 'icon' => 'bi-bank2'],
                ['field' => 'payment_qr_fonepay', 'label' => 'FonePay',            'subtitle' => 'FonePay merchant QR code',         'color' => '#dc2626', 'bg' => '#fff1f2', 'border' => '#fecdd3', 'icon' => 'bi-upc-scan'],
            ] as $qr)
            @php $field = $qr['field']; $current = $school->$field ?? null; @endphp
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">

                {{-- Card header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 dark:border-slate-800"
                     style="background: {{ $qr['bg'] }};">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-sm shadow-sm"
                              style="background: {{ $qr['color'] }}">
                            <i class="bi {{ $qr['icon'] }}"></i>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ $qr['label'] }}</p>
                            <p class="text-[10px] text-slate-500">{{ $qr['subtitle'] }}</p>
                        </div>
                    </div>
                    @if($current)
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full text-white shadow-sm"
                              style="background: {{ $qr['color'] }}">
                            <i class="bi bi-check-circle me-1"></i>Active
                        </span>
                    @else
                        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500">
                            Not set
                        </span>
                    @endif
                </div>

                <div class="p-5">
                    @if($current)
                    {{-- Current QR preview --}}
                    <div class="flex items-start gap-4 mb-4 p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                        <img src="{{ Storage::disk('public')->url($current) }}"
                             alt="{{ $qr['label'] }} QR"
                             id="current-{{ $field }}"
                             class="w-28 h-28 object-contain rounded-xl border border-slate-200 dark:border-slate-700 bg-white cursor-pointer shadow-sm"
                             onclick="openQrModal('{{ Storage::disk('public')->url($current) }}', '{{ $qr['label'] }}')"
                             title="Click to enlarge">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Current QR active</p>
                            <p class="text-[11px] text-slate-400 mb-3">Upload a new image to replace it.</p>
                            <label class="flex items-center gap-2 text-xs text-rose-600 cursor-pointer group">
                                <input type="checkbox"
                                       name="remove_{{ $field }}"
                                       value="1"
                                       class="rounded border-rose-300 text-rose-500 accent-rose-500">
                                <span class="group-hover:underline">Remove this QR</span>
                            </label>
                        </div>
                    </div>
                    @endif

                    {{-- Upload zone --}}
                    <label id="zone-{{ $field }}"
                           class="flex flex-col items-center justify-center gap-2 w-full border-2 border-dashed rounded-xl py-6 cursor-pointer transition-all duration-200"
                           style="border-color: {{ $qr['border'] }}; background: {{ $qr['bg'] }};"
                           onmouseover="this.style.borderColor='{{ $qr['color'] }}'"
                           onmouseout="this.style.borderColor='{{ $qr['border'] }}'">
                        <i class="bi bi-cloud-arrow-up text-3xl" style="color: {{ $qr['color'] }}"></i>
                        <span class="text-xs font-bold text-slate-600">
                            {{ $current ? 'Upload new to replace' : 'Upload ' . $qr['label'] . ' QR' }}
                        </span>
                        <span class="text-[10px] text-slate-400">PNG / JPG / WebP · max 2 MB</span>
                        <input type="file"
                               name="{{ $field }}"
                               accept="image/*"
                               class="hidden"
                               onchange="previewNewQr(this, 'preview-{{ $field }}', 'zone-{{ $field }}')">
                    </label>

                    {{-- New file preview --}}
                    <div id="preview-{{ $field }}" class="hidden mt-3 flex flex-col items-center gap-2">
                        <img src="" alt="Preview" class="w-32 h-32 object-contain rounded-xl border border-slate-200 bg-white shadow-sm">
                        <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
                            <i class="bi bi-check-circle-fill"></i> Ready to save
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Payment Instructions --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                <i class="bi bi-chat-text text-indigo-500"></i> Payment Instructions
                <span class="text-slate-400 font-normal text-xs">(optional)</span>
            </h3>
            <p class="text-[11px] text-slate-400 mb-3">
                This text appears below the QR codes on every invoice — add your phone number, account name, or notes for parents.
            </p>
            <textarea name="payment_instructions"
                      rows="4"
                      class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-emerald-400 focus:outline-none resize-y"
                      placeholder="e.g. eSewa / Khalti: 9812345678 (ABC School)&#10;Bank: Nepal Investment Bank, A/C: 012-XXXXXXXX, Kathmandu Branch&#10;After payment, please inform the school office.">{{ old('payment_instructions', $school->payment_instructions) }}</textarea>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center justify-between gap-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 px-6 py-4 shadow-sm">
            <a href="{{ route('payments.index') }}"
               class="text-sm text-slate-500 hover:text-indigo-600 transition flex items-center gap-1.5">
                <i class="bi bi-receipt"></i> View Invoices
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-8 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-md shadow-emerald-500/20">
                <i class="bi bi-floppy-fill"></i> Save QR Codes
            </button>
        </div>
    </form>
</div>

{{-- Full-screen QR modal --}}
<div id="qr-fullscreen-modal"
     class="hidden fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4"
     onclick="closeQrModal()">
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 flex flex-col items-center gap-4 shadow-2xl max-w-sm w-full"
         onclick="event.stopPropagation()">
        <p id="qr-modal-label" class="text-sm font-bold text-slate-700 dark:text-slate-200"></p>
        <img id="qr-modal-img" src="" alt="QR" class="w-64 h-64 object-contain rounded-xl border border-slate-200 dark:border-slate-700 bg-white">
        <p class="text-xs text-slate-400 text-center">Open your payment app → Scan → Pay</p>
        <button onclick="closeQrModal()"
                class="mt-1 px-6 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
            <i class="bi bi-x-circle me-1"></i> Close
        </button>
    </div>
</div>

@push('scripts')
<script>
function previewNewQr(input, previewId, zoneId) {
    const previewWrap = document.getElementById(previewId);
    const img = previewWrap ? previewWrap.querySelector('img') : null;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            if (img) img.src = e.target.result;
            if (previewWrap) previewWrap.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openQrModal(src, label) {
    document.getElementById('qr-modal-img').src = src;
    document.getElementById('qr-modal-label').textContent = label + ' QR Code';
    document.getElementById('qr-fullscreen-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeQrModal() {
    document.getElementById('qr-fullscreen-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Close on Escape key
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeQrModal(); });
</script>
@endpush

@endsection
