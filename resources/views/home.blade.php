@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar / Left Menu (Using existing include but wrapping it) -->
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 p-6 lg:p-10 overflow-hidden">
        
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Overview</h1>
                <p class="text-slate-500 mt-1">Welcome back to Unifiedtransform. Here's what's happening today.</p>
            </div>
            
            <div class="flex space-x-3">
                <button class="px-4 py-2 bg-white border border-slate-200 rounded-lg shadow-sm text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    <i class="bi bi-download mr-2"></i> Export Report
                </button>
                <button class="px-4 py-2 bg-gradient-to-r from-brand-500 to-brand-600 rounded-lg shadow-soft text-sm font-medium text-white hover:from-brand-600 hover:to-brand-700 transition-all">
                    <i class="bi bi-plus-lg mr-2"></i> Quick Action
                </button>
            </div>
        </div>

        <!-- Metric Cards (Stripe/Linear Style) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <!-- Total Students -->
            <div class="bg-white rounded-[16px] p-6 shadow-soft border border-slate-100 hover:shadow-floating transition-shadow duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-slate-500 mb-1">Total Students</p>
                        <h3 class="text-3xl font-bold text-slate-800">{{$studentCount}}</h3>
                    </div>
                    <div class="p-3 bg-brand-50 rounded-xl">
                        <i class="bi bi-people text-brand-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-emerald-500 font-medium flex items-center"><i class="bi bi-arrow-up-short text-lg"></i> 12%</span>
                    <span class="text-slate-400 ml-2">vs last month</span>
                </div>
            </div>

            <!-- Total Teachers -->
            <div class="bg-white rounded-[16px] p-6 shadow-soft border border-slate-100 hover:shadow-floating transition-shadow duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-slate-500 mb-1">Total Teachers</p>
                        <h3 class="text-3xl font-bold text-slate-800">{{$teacherCount}}</h3>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-xl">
                        <i class="bi bi-person-badge text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-emerald-500 font-medium flex items-center"><i class="bi bi-arrow-up-short text-lg"></i> 4%</span>
                    <span class="text-slate-400 ml-2">vs last month</span>
                </div>
            </div>

            <!-- Total Classes -->
            <div class="bg-white rounded-[16px] p-6 shadow-soft border border-slate-100 hover:shadow-floating transition-shadow duration-300">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-slate-500 mb-1">Active Classes</p>
                        <h3 class="text-3xl font-bold text-slate-800">{{$classCount}}</h3>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-xl">
                        <i class="bi bi-diagram-3 text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-slate-400">Current Academic Session</span>
                </div>
            </div>
        </div>

        <!-- Demographic Progress Bar -->
        @if($studentCount > 0)
        @php
            $maleStudentPercentage = round(($maleStudentsBySession/$studentCount), 2) * 100;
            $femaleStudentPercentage = round((($studentCount - $maleStudentsBySession)/$studentCount), 2) * 100;
        @endphp
        <div class="bg-white rounded-[16px] p-6 shadow-soft border border-slate-100 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-lg font-semibold text-slate-800">Student Demographics</h4>
                <div class="flex space-x-4 text-sm">
                    <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span> Male ({{$maleStudentPercentage}}%)</div>
                    <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-pink-500 mr-2"></span> Female ({{$femaleStudentPercentage}}%)</div>
                </div>
            </div>
            <div class="w-full h-4 bg-slate-100 rounded-full overflow-hidden flex">
                <div class="h-full bg-blue-500 transition-all duration-1000" style="width: {{$maleStudentPercentage}}%"></div>
                <div class="h-full bg-pink-500 transition-all duration-1000" style="width: {{$femaleStudentPercentage}}%"></div>
            </div>
        </div>
        @endif

        <!-- Banners Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Glassmorphism Banner -->
            <div class="relative overflow-hidden rounded-[16px] p-8 bg-gradient-to-br from-brand-600 to-brand-900 shadow-floating text-white">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
                <div class="relative z-10">
                    <h3 class="text-2xl font-bold mb-2">Welcome to Unifiedtransform!</h3>
                    <p class="text-brand-100 mb-6">Thanks for your love and support. Check out the new dashboard features.</p>
                    <button class="px-5 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-md rounded-lg text-sm font-semibold transition-all border border-white/10">
                        View Updates
                    </button>
                </div>
            </div>
            
            <div class="rounded-[16px] p-8 bg-white border border-slate-200 shadow-soft flex flex-col justify-center items-start">
                <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center mb-4">
                    <i class="bi bi-lightning text-amber-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Manage school better</h3>
                <p class="text-slate-500 mb-4">Experience the next generation ERP built for modern educational institutions.</p>
                <a href="https://github.com/changeweb/Unifiedtransform" target="_blank" class="text-brand-600 font-semibold hover:text-brand-700 inline-flex items-center">
                    Visit GitHub <i class="bi bi-arrow-right-short ml-1 text-xl"></i>
                </a>
            </div>
        </div>

        <!-- Lower Widgets -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-12">
            <!-- Calendar Widget -->
            <div class="bg-white rounded-[16px] border border-slate-100 shadow-soft overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h4 class="font-semibold text-slate-800 flex items-center"><i class="bi bi-calendar-event mr-2 text-brand-500"></i> Events</h4>
                </div>
                <div class="p-6">
                    <!-- Bootstrap Calendar Integration - Wrapped in tailwind prose to isolate styles if needed -->
                    <div class="calendar-wrapper">
                        @include('components.events.event-calendar', ['editable' => 'false', 'selectable' => 'false'])
                    </div>
                </div>
            </div>

            <!-- Notices Widget -->
            <div class="bg-white rounded-[16px] border border-slate-100 shadow-soft overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h4 class="font-semibold text-slate-800 flex items-center"><i class="bi bi-megaphone mr-2 text-amber-500"></i> Notices</h4>
                    <div class="text-sm">
                        {{ $notices->links('pagination::simple-tailwind') }}
                    </div>
                </div>
                <div class="p-0">
                    @if(count($notices) > 0)
                        <div class="divide-y divide-slate-100">
                            @foreach ($notices as $notice)
                                <div x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }" class="bg-white">
                                    <button @click="expanded = !expanded" class="w-full px-6 py-4 flex justify-between items-center hover:bg-slate-50 transition-colors focus:outline-none text-left">
                                        <span class="text-sm font-medium text-slate-700">Published at: {{$notice->created_at->format('M d, Y')}}</span>
                                        <i class="bi bi-chevron-down text-slate-400 transition-transform duration-200" :class="{'rotate-180': expanded}"></i>
                                    </button>
                                    <div x-show="expanded" x-collapse class="px-6 pb-4">
                                        <div class="prose prose-sm prose-slate max-w-none bg-slate-50 p-4 rounded-lg border border-slate-100">
                                            {!!Purify::clean($notice->notice)!!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-12 text-center text-slate-500">
                            <i class="bi bi-inbox text-4xl mb-3 block text-slate-300"></i>
                            <p>No new notices at this time.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
<!-- Include Alpine.js for the accordion functionality -->
<script src="//unpkg.com/alpinejs" defer></script>
@endsection
