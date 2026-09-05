@extends('layouts.app')
@section('title', 'Pay Invoice')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Pay Invoice</h1>
            <p class="text-xs text-slate-500 mt-0.5">{{ $invoice->invoice_number }} · {{ $invoice->title }}</p>
        </div>
        <a href="{{ route('payments.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    @include('session-messages')

    {{-- Amount due --}}
    <div class="mb-5 p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Amount Due</p>
            <p class="text-3xl font-bold text-rose-600 mt-1">NPR {{ number_format($amountDue, 2) }}</p>
        </div>
        <span class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
            <i class="bi bi-receipt"></i>
        </span>
    </div>

    {{-- Already submitted & pending --}}
    @if($pendingProof)
    <div class="mb-5 p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 flex items-start gap-3">
        <i class="bi bi-hourglass-split text-amber-500 text-lg flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Payment proof submitted — awaiting approval</p>
            <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                You submitted a <strong>{{ $pendingProof->gateway_label }}</strong> payment proof on
                {{ $pendingProof->created_at->format('d M Y, h:i A') }}.
                The school admin will confirm within 24 hours.
            </p>
            @if($pendingProof->screenshot_url)
            <a href="{{ $pendingProof->screenshot_url }}" target="_blank"
               class="inline-flex items-center gap-1 mt-2 text-xs text-amber-700 underline">
                <i class="bi bi-image"></i> View submitted screenshot
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- STEP 1: Scan QR --}}
    @if($paySchool && ($paySchool->payment_qr_esewa || $paySchool->payment_qr_khalti || $paySchool->payment_qr_bank || $paySchool->payment_qr_fonepay))
    <div class="mb-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 bg-emerald-50 dark:bg-emerald-900/20 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">1</span>
            <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Scan QR & Pay</p>
        </div>
        <div class="p-5">
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                Open your eSewa, Khalti, banking app → tap <strong>Scan QR</strong> → enter
                <span class="font-bold text-rose-600">NPR {{ number_format($amountDue, 2) }}</span> → confirm payment.
            </p>
            <div class="grid grid-cols-2 gap-4">
                @foreach([
                    ['field' => 'payment_qr_esewa',   'label' => 'eSewa',             'color' => '#60bb46'],
                    ['field' => 'payment_qr_khalti',  'label' => 'Khalti',            'color' => '#5c2d91'],
                    ['field' => 'payment_qr_bank',    'label' => 'Bank / ConnectIPS', 'color' => '#1d4ed8'],
                    ['field' => 'payment_qr_fonepay', 'label' => 'FonePay',           'color' => '#dc2626'],
                ] as $idx => $qr)
                @if($paySchool->{$qr['field']})
                <div class="flex flex-col items-center gap-2 p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 cursor-pointer hover:border-slate-300 transition"
                     onclick="openQrModal('{{ Storage::disk('public')->url($paySchool->{$qr['field']}) }}', '{{ $qr['label'] }}', '{{ $qr['color'] }}')">
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full text-white" style="background:{{ $qr['color'] }}">{{ $qr['label'] }}</span>
                    <img src="{{ Storage::disk('public')->url($paySchool->{$qr['field']}) }}"
                         alt="{{ $qr['label'] }} QR"
                         class="w-32 h-32 object-contain rounded-lg bg-white p-1 border border-slate-200 dark:border-slate-700">
                    <span class="text-[10px] text-indigo-500 font-medium">Tap to enlarge</span>
                </div>
                @endif
                @endforeach
            </div>
            @if($paySchool->payment_instructions)
            <div class="mt-4 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800">
                <p class="text-[11px] text-amber-800 dark:text-amber-300 whitespace-pre-line">{{ $paySchool->payment_instructions }}</p>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="mb-5 p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800">
        <p class="text-sm text-blue-700 dark:text-blue-300">
            <i class="bi bi-info-circle me-1"></i>
            No QR codes configured yet. Please pay at the school office or contact the administration.
        </p>
    </div>
    @endif

    {{-- STEP 2: Submit proof --}}
    @if(!$pendingProof)
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 bg-indigo-50 dark:bg-indigo-900/20 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">2</span>
            <p class="text-sm font-bold text-indigo-800 dark:text-indigo-300">Confirm Your Payment</p>
        </div>
        <div class="p-5">
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">
                After paying, take a screenshot of the payment confirmation and upload it below.
                The school admin will review and mark your invoice as paid.
            </p>
            <form method="POST"
                  action="{{ route('payment-proof.submit', $invoice->id) }}"
                  enctype="multipart/form-data"
                  class="space-y-4">
                @csrf

                {{-- Payment method --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">Payment Method <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach([
                            ['esewa',   'eSewa',   '#60bb46'],
                            ['khalti',  'Khalti',  '#5c2d91'],
                            ['bank',    'Bank',    '#1d4ed8'],
                            ['fonepay', 'FonePay', '#dc2626'],
                        ] as [$val, $label, $color])
                        <label class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 cursor-pointer transition-all duration-150 border-slate-200 dark:border-slate-700 hover:border-slate-400 has-[:checked]:border-2"
                               style="--c: {{ $color }}"
                               id="method-{{ $val }}">
                            <input type="radio" name="gateway" value="{{ $val }}"
                                   class="sr-only" onchange="selectMethod('{{ $val }}', '{{ $color }}')"
                                   {{ old('gateway') === $val ? 'checked' : '' }}>
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-sm font-bold"
                                  style="background:{{ $color }}">
                                <i class="bi bi-phone-fill"></i>
                            </span>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('gateway')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Amount --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Amount Paid (NPR) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" step="0.01" min="1"
                           value="{{ old('amount', $amountDue) }}"
                           class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                           placeholder="Enter amount paid">
                    @error('amount')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Transaction ID --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                        Transaction / Reference ID
                        <span class="text-slate-400 font-normal">(optional but recommended)</span>
                    </label>
                    <input type="text" name="transaction_id" value="{{ old('transaction_id') }}"
                           class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                           placeholder="e.g. eSewa Ref No, Khalti Transaction ID, Bank Voucher No">
                    @error('transaction_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Screenshot upload --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                        Payment Screenshot <span class="text-rose-500">*</span>
                    </label>
                    <label id="screenshotZone"
                           class="flex flex-col items-center justify-center gap-2 w-full border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl py-8 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition">
                        <i class="bi bi-cloud-arrow-up text-3xl text-slate-400" id="uploadIcon"></i>
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300" id="uploadLabel">
                            Click to upload payment confirmation screenshot
                        </span>
                        <span class="text-[10px] text-slate-400">JPEG / PNG / WebP · max 5 MB</span>
                        <input type="file" name="screenshot" accept="image/*" class="hidden"
                               id="screenshotInput" onchange="previewScreenshot(this)">
                    </label>
                    <img id="screenshotPreview" src="" alt="Preview"
                         class="hidden mt-3 w-full max-h-48 object-contain rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    @error('screenshot')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                        Additional Notes <span class="text-slate-400 font-normal">(optional)</span>
                    </label>
                    <textarea name="notes" rows="2"
                              class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none"
                              placeholder="Any notes for the school admin...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2 shadow-md shadow-emerald-500/20 transition">
                    <i class="bi bi-send-check-fill"></i>
                    Submit Payment Proof for Approval
                </button>
            </form>
        </div>
    </div>
    @else
    <div class="mt-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Need to resubmit? <a href="{{ route('payments.index') }}" class="text-indigo-600 underline">Go back</a>
            and wait for admin review first.
        </p>
    </div>
    @endif

</div>

{{-- Full-screen QR modal --}}
<div id="qr-modal"
     class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4"
     onclick="closeQrModal()">
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-7 flex flex-col items-center gap-4 shadow-2xl max-w-sm w-full"
         onclick="event.stopPropagation()">
        <p id="qr-modal-label" class="text-sm font-bold text-slate-700 dark:text-slate-200"></p>
        <img id="qr-modal-img" src="" alt="QR" class="w-64 h-64 object-contain rounded-xl bg-white border border-slate-200 dark:border-slate-700">
        <p class="text-xs text-slate-400 text-center">
            Open your app → Scan → Enter <strong class="text-rose-600">NPR {{ number_format($amountDue, 2) }}</strong> → Pay
        </p>
        <button onclick="closeQrModal()"
                class="px-6 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
            <i class="bi bi-x-circle me-1"></i> Close
        </button>
    </div>
</div>

@push('scripts')
<script>
function openQrModal(src, label, color) {
    document.getElementById('qr-modal-img').src = src;
    document.getElementById('qr-modal-label').textContent = label + ' QR Code';
    document.getElementById('qr-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeQrModal() {
    document.getElementById('qr-modal').classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeQrModal(); });

function selectMethod(val, color) {
    document.querySelectorAll('[id^="method-"]').forEach(el => {
        el.style.borderColor = '';
        el.style.background  = '';
    });
    const el = document.getElementById('method-' + val);
    if (el) {
        el.style.borderColor = color;
        el.style.background  = color + '10';
    }
}

function previewScreenshot(input) {
    const preview = document.getElementById('screenshotPreview');
    const label   = document.getElementById('uploadLabel');
    const icon    = document.getElementById('uploadIcon');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            label.textContent = input.files[0].name;
            icon.className = 'bi bi-check-circle-fill text-3xl text-emerald-500';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
// Restore selection from old() on validation error
@if(old('gateway'))
selectMethod('{{ old('gateway') }}', {
    esewa:'#60bb46', khalti:'#5c2d91', bank:'#1d4ed8', fonepay:'#dc2626'
}['{{ old('gateway') }}'] || '#6366f1');
@endif
</script>
@endpush
@endsection
