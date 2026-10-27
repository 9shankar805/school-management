@extends('layouts.app')
@section('title', 'Multi-Bank Master & Fonepay QR Management')

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-tr from-[#0D7C66] to-teal-500 text-white shadow-lg shadow-teal-500/20">
                    <i class="bi bi-bank2 text-xl"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        Multi-Bank Master &amp; Fonepay QR (बहु-बैंक तथा फोनपे क्युआर)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Manage multiple school commercial bank accounts, merchant QR codes, and POS counter terminals.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('finance.pos') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="bi bi-terminal-dash"></i> Open Fee POS
            </a>
            <button type="button" onclick="openAddBankModal()" class="px-4 py-2.5 bg-gradient-to-r from-[#0D7C66] to-[#00A884] hover:from-[#0B6855] hover:to-[#008F6F] text-white rounded-xl text-xs font-black shadow-md shadow-[#0D7C66]/20 transition flex items-center gap-2">
                <i class="bi bi-plus-circle-fill text-sm"></i> नयाँ बैंक खाता थप्नुहोस् (Add Bank)
            </button>
        </div>
    </div>

    @include('session-messages')

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Bank Accounts</span>
            <div class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ $bankAccounts->count() }} Accounts</div>
            <span class="text-[11px] text-slate-500">Commercial banks in Nepal</span>
        </div>

        <div class="bg-emerald-50/60 dark:bg-emerald-950/20 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 p-4 shadow-sm">
            <span class="text-[11px] font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider block">Primary Counter Account</span>
            @php $primary = $bankAccounts->firstWhere('is_default', true) ?? $bankAccounts->first(); @endphp
            <div class="text-lg font-black text-emerald-700 dark:text-emerald-300 mt-1 truncate">{{ $primary?->bank_name ?? 'None' }}</div>
            <span class="text-[11px] text-emerald-800/80 dark:text-emerald-400">A/C: {{ $primary?->account_number ?? '—' }}</span>
        </div>

        <div class="bg-blue-50/60 dark:bg-blue-950/20 rounded-2xl border border-blue-200 dark:border-blue-800/60 p-4 shadow-sm">
            <span class="text-[11px] font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider block">Fonepay / QR Enabled</span>
            <div class="text-2xl font-black text-blue-700 dark:text-blue-300 mt-1">{{ $bankAccounts->whereNotNull('fonepay_merchant_id')->count() }} Terminals</div>
            <span class="text-[11px] text-blue-800/80 dark:text-blue-400">Instant scanning at counter</span>
        </div>

        <div class="bg-indigo-50/60 dark:bg-indigo-950/20 rounded-2xl border border-indigo-200 dark:border-indigo-800/60 p-4 shadow-sm">
            <span class="text-[11px] font-bold text-indigo-800 dark:text-indigo-300 uppercase tracking-wider block">POS Integrated</span>
            <div class="text-2xl font-black text-indigo-700 dark:text-indigo-300 mt-1">100% Ready</div>
            <span class="text-[11px] text-indigo-800/80 dark:text-indigo-400">Multi-bank dropdown in POS</span>
        </div>
    </div>

    {{-- Bank Accounts Table --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-wallet2 text-emerald-600"></i> Registered Bank Accounts &amp; Merchant QR Payloads
            </h3>
            <span class="text-xs text-slate-400">Used for Fee Invoicing, POS Counter, &amp; Online Receipting</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase font-black tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Bank &amp; Account Details</th>
                        <th class="px-5 py-3.5">Branch &amp; Type</th>
                        <th class="px-5 py-3.5">Fonepay Merchant / PAN</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center">QR Code</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    @forelse($bankAccounts as $acc)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition {{ $acc->is_default ? 'bg-emerald-50/20 dark:bg-emerald-950/10' : '' }}">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-black text-slate-700 dark:text-slate-200 text-xs flex-shrink-0 border border-slate-200 dark:border-slate-700">
                                        {{ $acc->bank_code ? substr($acc->bank_code, 0, 3) : 'BNK' }}
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                                            <span>{{ $acc->bank_name }}</span>
                                            @if($acc->is_default)
                                                <span class="bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 text-[10px] font-extrabold px-2 py-0.2 rounded-md">
                                                    Primary Counter
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-slate-500 dark:text-slate-400 text-[11px] font-mono mt-0.5">
                                            A/C: <span class="font-bold text-slate-800 dark:text-slate-200">{{ $acc->account_number }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            Name: {{ $acc->account_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-700 dark:text-slate-200">{{ $acc->branch_name ?? 'Main Branch' }}</div>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    {{ $acc->account_type }} Account
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                @if($acc->fonepay_merchant_id)
                                    <div class="flex items-center gap-1.5 text-slate-800 dark:text-slate-200 font-bold font-mono">
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                        MID: {{ $acc->fonepay_merchant_id }}
                                    </div>
                                    @if($acc->fonepay_pan)
                                        <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                            PAN: {{ $acc->fonepay_pan }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-[11px] italic">Not Configured</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if($acc->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if($acc->qr_image)
                                    <button type="button" onclick="viewQrModal('{{ asset('storage/' . $acc->qr_image) }}', '{{ $acc->bank_name }}', '{{ $acc->account_number }}')" class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-lg text-[11px] font-bold transition">
                                        <i class="bi bi-qr-code text-sm"></i> View QR
                                    </button>
                                @else
                                    <span class="text-[10px] text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded font-mono">Dynamic Payload</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if(!$acc->is_default)
                                        <form method="POST" action="{{ route('finance.bank-accounts.set-default', $acc->id) }}">
                                            @csrf
                                            <button type="submit" title="Make Primary Counter Account" class="px-2 py-1 text-[11px] font-bold text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/50 rounded-lg transition">
                                                Set Default
                                            </button>
                                        </form>
                                    @endif

                                    <button type="button" onclick="openEditBankModal({{ json_encode($acc) }})" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition" title="Edit Account">
                                        <i class="bi bi-pencil-square text-sm"></i>
                                    </button>

                                    @if(!$acc->is_default)
                                        <form method="POST" action="{{ route('finance.bank-accounts.destroy', $acc->id) }}" onsubmit="return confirm('के तपाईं यो बैंक खाता हटाउन निश्चित हुनुहुन्छ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition" title="Delete Account">
                                                <i class="bi bi-trash3 text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <i class="bi bi-bank text-3xl block mb-2"></i>
                                कुनै बैंक खाता फेला परेन (No bank accounts registered).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Bank Account Modal --}}
<div id="addBankModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-xl w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-bank2 text-[#0D7C66]"></i> नयाँ बैंक खाता थप्नुहोस् (Add New Bank Account)
            </h3>
            <button type="button" onclick="closeAddBankModal()" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('finance.bank-accounts.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Bank Name (बैंकको नाम) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="bank_name" required placeholder="e.g. Nabil Bank Limited"
                           class="w-full text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2 focus:ring-2 focus:ring-[#0D7C66]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Bank Short Code (संकेत)
                    </label>
                    <input type="text" name="bank_code" placeholder="e.g. NABIL, NIMB, GLOBAL"
                           class="w-full text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2 focus:ring-2 focus:ring-[#0D7C66]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Account Holder Name (खातावालाको नाम) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="account_name" required placeholder="e.g. Shree Shanti Secondary School"
                           class="w-full text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2 focus:ring-2 focus:ring-[#0D7C66]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Account Number (खाता नम्बर) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="account_number" required placeholder="e.g. 01201017500123"
                           class="w-full text-xs font-mono font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2 focus:ring-2 focus:ring-[#0D7C66]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Branch Name (शाखा)
                    </label>
                    <input type="text" name="branch_name" placeholder="e.g. Kantipath Branch, Kathmandu"
                           class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2 focus:ring-2 focus:ring-[#0D7C66]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Account Type (खाता प्रकार) <span class="text-rose-500">*</span>
                    </label>
                    <select name="account_type" class="w-full text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2 focus:ring-2 focus:ring-[#0D7C66]">
                        <option value="Current" selected>Current Account (चल्ती खाता)</option>
                        <option value="Savings">Savings Account (बचत खाता)</option>
                    </select>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 rounded-xl space-y-3">
                <p class="text-xs font-black text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                    <i class="bi bi-qr-code-scan"></i> Fonepay / SmartQR Counter Configuration
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                            Fonepay Merchant ID / Terminal Code
                        </label>
                        <input type="text" name="fonepay_merchant_id" placeholder="e.g. 9801234567"
                               class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 px-3 py-1.5 focus:ring-2 focus:ring-[#0D7C66]">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                            Fonepay Merchant PAN No.
                        </label>
                        <input type="text" name="fonepay_pan" placeholder="e.g. 301234567"
                               class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 px-3 py-1.5 focus:ring-2 focus:ring-[#0D7C66]">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                        Upload Official Merchant QR Standee Image (वैकल्पिक)
                    </label>
                    <input type="file" name="qr_image" accept="image/*"
                           class="w-full text-xs file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#0D7C66] file:text-white hover:file:bg-[#00A884]">
                </div>
            </div>

            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_default" value="1" class="w-4 h-4 rounded text-[#0D7C66] focus:ring-[#0D7C66]">
                    <span>Set as Primary POS Counter Account (काउन्टरमा सुरुमै देखिने)</span>
                </label>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                <button type="button" onclick="closeAddBankModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                    रद्द गर्नुहोस् (Cancel)
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-black text-white bg-[#0D7C66] hover:bg-[#00A884] rounded-xl shadow-md transition">
                    सुरक्षित गर्नुहोस् (Save Bank Account)
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Bank Modal --}}
<div id="editBankModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-xl w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-pencil-square text-[#0D7C66]"></i> बैंक खाता विवरण सम्पादन (Edit Bank Account)
            </h3>
            <button type="button" onclick="closeEditBankModal()" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="editBankForm" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" id="edit_bank_name" required class="w-full text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Bank Code</label>
                    <input type="text" name="bank_code" id="edit_bank_code" class="w-full text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Account Name</label>
                    <input type="text" name="account_name" id="edit_account_name" required class="w-full text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Account Number</label>
                    <input type="text" name="account_number" id="edit_account_number" required class="w-full text-xs font-mono font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Branch Name</label>
                    <input type="text" name="branch_name" id="edit_branch_name" class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Account Type</label>
                    <select name="account_type" id="edit_account_type" class="w-full text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2">
                        <option value="Current">Current Account</option>
                        <option value="Savings">Savings Account</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Fonepay MID</label>
                    <input type="text" name="fonepay_merchant_id" id="edit_fonepay_merchant_id" class="w-full text-xs font-mono font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Fonepay PAN</label>
                    <input type="text" name="fonepay_pan" id="edit_fonepay_pan" class="w-full text-xs font-mono font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3.5 py-2">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Upload New QR Standee (Optional)</label>
                <input type="file" name="qr_image" accept="image/*" class="w-full text-xs">
            </div>

            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_default" id="edit_is_default" value="1" class="w-4 h-4 rounded text-[#0D7C66]">
                    <span>Primary Counter Account</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-4 h-4 rounded text-[#0D7C66]">
                    <span>Active Account</span>
                </label>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                <button type="button" onclick="closeEditBankModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400">Cancel</button>
                <button type="submit" class="px-5 py-2 text-xs font-black text-white bg-[#0D7C66] rounded-xl shadow-md">Update Account</button>
            </div>
        </form>
    </div>
</div>

{{-- QR Code Preview Modal --}}
<div id="qrModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-sm w-full border border-slate-200 dark:border-slate-800 shadow-2xl p-6 text-center space-y-4">
        <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
            <h4 class="text-sm font-black text-slate-900 dark:text-white" id="qrModalTitle">Merchant QR Code</h4>
            <button type="button" onclick="closeQrModal()" class="text-slate-400 hover:text-slate-700">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-4 bg-white rounded-xl border border-slate-200 inline-block">
            <img id="qrModalImg" src="" alt="QR Code" class="w-48 h-48 object-contain mx-auto">
        </div>
        <p class="text-xs font-mono font-bold text-slate-600 dark:text-slate-300" id="qrModalAcct"></p>
        <button type="button" onclick="closeQrModal()" class="w-full py-2 bg-slate-900 text-white text-xs font-bold rounded-xl">
            Close
        </button>
    </div>
</div>

<script>
function openAddBankModal() {
    document.getElementById('addBankModal').classList.remove('hidden');
}
function closeAddBankModal() {
    document.getElementById('addBankModal').classList.add('hidden');
}

function openEditBankModal(data) {
    document.getElementById('editBankForm').action = '/finance/bank-accounts/' + data.id;
    document.getElementById('edit_bank_name').value = data.bank_name || '';
    document.getElementById('edit_bank_code').value = data.bank_code || '';
    document.getElementById('edit_account_name').value = data.account_name || '';
    document.getElementById('edit_account_number').value = data.account_number || '';
    document.getElementById('edit_branch_name').value = data.branch_name || '';
    document.getElementById('edit_account_type').value = data.account_type || 'Current';
    document.getElementById('edit_fonepay_merchant_id').value = data.fonepay_merchant_id || '';
    document.getElementById('edit_fonepay_pan').value = data.fonepay_pan || '';
    document.getElementById('edit_is_default').checked = !!data.is_default;
    document.getElementById('edit_is_active').checked = !!data.is_active;

    document.getElementById('editBankModal').classList.remove('hidden');
}
function closeEditBankModal() {
    document.getElementById('editBankModal').classList.add('hidden');
}

function viewQrModal(url, title, acct) {
    document.getElementById('qrModalImg').src = url;
    document.getElementById('qrModalTitle').innerText = title;
    document.getElementById('qrModalAcct').innerText = 'A/C: ' + acct;
    document.getElementById('qrModal').classList.remove('hidden');
}
function closeQrModal() {
    document.getElementById('qrModal').classList.add('hidden');
}
</script>
@endsection
