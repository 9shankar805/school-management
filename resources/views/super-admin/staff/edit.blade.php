@extends('layouts.app')
@section('title', 'Edit SaaS Staff - NepalSchoolHub')

@section('content')
<div class="px-6 py-6 max-w-5xl mx-auto space-y-6">

    {{-- Breadcrumb & Back Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('super.staff.index') }}" class="hover:text-indigo-600 transition flex items-center gap-1">
                    <i class="bi bi-people"></i> SaaS Staff
                </a>
                <span>&bull;</span>
                <span>Edit Staff Member</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1 flex items-center gap-2.5">
                <i class="bi bi-pencil-square text-indigo-600"></i>
                Edit Staff: {{ $staff->full_name }} (कर्मचारी सम्पादन)
            </h1>
        </div>

        <a href="{{ route('super.staff.index') }}"
            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs transition">
            <i class="bi bi-arrow-left me-1"></i> Back to Staff List
        </a>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm">
            <p class="font-bold mb-1 flex items-center gap-1.5"><i class="bi bi-exclamation-octagon-fill"></i> Please correct the following errors:</p>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('super.staff.update', $staff) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Basic Information Card --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-5">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                <i class="bi bi-person-vcard text-indigo-600"></i>
                Staff Personal &amp; Account Credentials (कर्मचारी व्यक्तिगत विवरण)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        First Name (पहिलो नाम) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="first_name" value="{{ old('first_name', $staff->first_name) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Last Name (थर) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="last_name" value="{{ old('last_name', $staff->last_name) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Email Address (इमेल) <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $staff->email) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Phone Number (फोन नम्बर)
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        New Password (नयाँ पासवर्ड - परिवर्तन गर्न चाहेमा मात्र भर्नुहोस्)
                    </label>
                    <input type="password" name="password" minlength="8"
                        placeholder="Leave blank to keep current password"
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Confirm New Password (पासवर्ड पुष्टि गर्नुहोस्)
                    </label>
                    <input type="password" name="password_confirmation" minlength="8"
                        placeholder="Re-type new password"
                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                </div>
            </div>
        </div>

        {{-- Role & Operational Permissions Card --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-5">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-shield-lock-fill text-indigo-600"></i>
                    Primary SaaS Role &amp; Access Scope (साँस पद तथा मुख्य भूमिका)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Select the staff member's core operational role.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($roles as $roleKey => $roleTitle)
                    <label class="relative flex items-start p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500 cursor-pointer transition bg-slate-50/50 dark:bg-slate-900/30">
                        <input type="radio" name="role" value="{{ $roleKey }}" {{ old('role', $currentRole) === $roleKey ? 'checked' : '' }}
                            class="mt-0.5 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                        <div class="ml-3">
                            <span class="block text-xs font-bold text-slate-900 dark:text-white">{{ $roleTitle }}</span>
                            <span class="block text-[11px] text-slate-400 mt-0.5 font-mono">{{ $roleKey }}</span>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700/60 pt-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                            Granular Permissions Matrix (कर्मचारीले के के व्यवस्थापन गर्न पाउने?)
                        </h4>
                        <p class="text-[11px] text-slate-400">
                            Check or uncheck the specific SaaS modules this team member is authorized to manage.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="selectAllPermissions(true)" class="text-[11px] font-semibold text-indigo-600 hover:underline">Select All</button>
                        <span class="text-slate-300">|</span>
                        <button type="button" onclick="selectAllPermissions(false)" class="text-[11px] font-semibold text-slate-500 hover:underline">Clear All</button>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($permissionGroups as $groupTitle => $groupPermissions)
                        <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-200/70 dark:border-slate-700/60 space-y-3">
                            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <i class="bi bi-folder2-open text-indigo-500"></i> {{ $groupTitle }}
                            </h5>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                                @foreach($groupPermissions as $permName => $permMeta)
                                    @php
                                        $isChecked = is_array(old('permissions'))
                                            ? in_array($permName, old('permissions'))
                                            : in_array($permName, $userPermissions);
                                    @endphp
                                    <label class="flex items-start p-2.5 rounded-lg border border-slate-200/60 dark:border-slate-700/50 hover:bg-white dark:hover:bg-slate-800 cursor-pointer transition">
                                        <input type="checkbox" name="permissions[]" value="{{ $permName }}"
                                            class="perm-checkbox mt-0.5 text-indigo-600 rounded focus:ring-indigo-500 h-4 w-4"
                                            {{ $isChecked ? 'checked' : '' }}>
                                        <div class="ml-2.5">
                                            <span class="block text-xs font-semibold text-slate-800 dark:text-slate-100">{{ $permMeta['label'] }}</span>
                                            <span class="block text-[10px] text-slate-400">{{ $permMeta['desc'] }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('super.staff.index') }}"
                class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold text-xs hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                Cancel
            </a>
            <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-200 dark:shadow-none transition flex items-center gap-2">
                <i class="bi bi-check2-circle text-sm"></i>
                Update Staff Member &amp; Permissions (अपडेट गर्नुहोस्)
            </button>
        </div>
    </form>
</div>

<script>
function selectAllPermissions(check) {
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = check);
}
</script>
@endsection
