@extends('layouts.app')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">
    
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('parents.index') }}" class="w-10 h-10 bg-white border border-slate-200 rounded-lg flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-colors shadow-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-800">Add Parent</h1>
            <p class="text-sm text-slate-500 mt-1">Register a new parent account and assign credentials.</p>
        </div>
    </div>

    @include('session-messages')

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden max-w-3xl">
        <form action="{{ route('parents.store') }}" method="POST">
            @csrf
            
            <div class="p-6 md:p-8 space-y-6">
                
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2">Parent Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">First Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-shadow" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Last Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-shadow" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-shadow" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-shadow">
                    </div>
                </div>

                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mt-8">Account Credentials</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Password <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-shadow" required>
                        <p class="text-xs text-slate-500 mt-1">Minimum 8 characters</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Confirm Password <span class="text-rose-500">*</span></label>
                        <input type="password" name="password_confirmation" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-shadow" required>
                    </div>
                </div>

            </div>

            <div class="bg-slate-50 border-t border-slate-100 p-6 flex justify-end gap-3">
                <a href="{{ route('parents.index') }}" class="px-6 py-2.5 rounded-lg font-semibold text-slate-600 hover:bg-slate-200 transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors flex items-center gap-2 shadow-sm">
                    <i class="bi bi-person-plus-fill"></i> Create Parent Account
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
