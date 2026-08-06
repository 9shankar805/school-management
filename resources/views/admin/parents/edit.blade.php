@extends('layouts.app')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">
    
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('parents.index') }}" class="w-10 h-10 bg-white border border-slate-200 rounded-lg flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-colors shadow-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-800">Edit Parent: {{ $parent->full_name }}</h1>
            <p class="text-sm text-slate-500 mt-1">Update profile information or reset their credentials.</p>
        </div>
    </div>

    @include('session-messages')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Edit Profile Form --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <form action="{{ route('parents.update', $parent->id) }}" method="POST">
                    @csrf @method('PUT')
                    
                    <div class="p-6 md:p-8">
                        <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-6">Profile Details</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">First Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="first_name" value="{{ old('first_name', $parent->first_name) }}" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-shadow" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Last Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="last_name" value="{{ old('last_name', $parent->last_name) }}" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-shadow" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address <span class="text-rose-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $parent->email) }}" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-shadow" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone', $parent->phone) }}" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-shadow">
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 border-t border-slate-100 p-6 flex justify-end gap-3">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors flex items-center gap-2 shadow-sm">
                            <i class="bi bi-save2"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>

            {{-- Linked Children List --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8 flex items-center justify-between border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Linked Children</h3>
                    <a href="{{ route('admin.parents.link') }}?parent_id={{ $parent->id }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm flex items-center gap-1 transition-colors">
                        <i class="bi bi-link"></i> Link a Student
                    </a>
                </div>
                
                <div class="p-0">
                    @forelse($parent->children as $child)
                        <div class="p-6 border-b border-slate-100 last:border-0 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center font-bold">
                                    <i class="bi bi-person-check-fill"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800">{{ $child->full_name }}</p>
                                    <p class="text-xs text-slate-500">Student ID: {{ $child->id }}</p>
                                </div>
                            </div>
                            
                            <form action="{{ route('admin.parents.unlink', ['parentId' => $parent->id, 'studentId' => $child->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to unlink this student from this parent?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:bg-rose-50 p-2 rounded-lg transition-colors font-semibold text-sm">
                                    <i class="bi bi-x-circle"></i> Unlink
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <p class="text-slate-500 text-sm">No children are currently linked to this parent account.</p>
                            <a href="{{ route('admin.parents.link') }}?parent_id={{ $parent->id }}" class="mt-3 inline-block text-indigo-600 font-semibold text-sm">Link a student now &rarr;</a>
                        </div>
                    @endforelse
                </div>
            </div>
            
        </div>

        {{-- Reset Password Card --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-rose-200 shadow-sm overflow-hidden sticky top-6">
                <div class="bg-rose-50 border-b border-rose-100 p-6">
                    <h3 class="text-lg font-bold text-rose-800 flex items-center gap-2">
                        <i class="bi bi-shield-lock-fill"></i> Configure Credentials
                    </h3>
                </div>
                
                <form action="{{ route('parents.password.update', $parent->id) }}" method="POST" class="p-6">
                    @csrf @method('PUT')
                    <p class="text-sm text-slate-500 mb-6">Reset the password for this parent account. They will need to use this new password to log in.</p>

                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">New Password</label>
                            <input type="password" name="password" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm" required>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-semibold py-2.5 transition-colors">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
