@extends('school-landing.layout')

@section('title', 'World-Class Campus Infrastructure & Facilities')

@section('content')

{{-- Page Header --}}
<div class="bg-white text-slate-900 py-12 border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">
            <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Campus Life</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Campus Infrastructure & Facilities</h1>
        <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl">
            An inspiring, safe, and technologically advanced learning haven engineered to stimulate scholarship, athletics, and creativity.
        </p>
    </div>
</div>

{{-- Facilities Detailed Showcase --}}
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Facility 1: STEM & Robotics Lab --}}
            <div class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm card-lift flex flex-col justify-between group hover:border-[#f36c21] transition">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100 relative">
                    <img src="{{ asset('imgs/hero/hero_2.webp') }}" alt="STEM & Robotics Lab" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-900 shadow-sm">
                        Tech & Coding
                    </div>
                </div>
                <div class="p-6">
                    <span class="text-xs font-bold text-[#f36c21] uppercase tracking-wider block mb-1">Technological Excellence</span>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">STEM & Computer Science Lab</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4">
                        Outfitted with modern networked computer workstations, modular electronics toolkits, Arduino microcontrollers, and hands-on scientific models for experiential learning.
                    </p>
                    <ul class="text-xs text-slate-500 space-y-1.5 border-t border-slate-100 pt-4">
                        <li class="flex items-center gap-2"><i class="bi bi-check2 text-emerald-600 font-bold"></i> Computer Programming & Web Design</li>
                        <li class="flex items-center gap-2"><i class="bi bi-check2 text-emerald-600 font-bold"></i> Science & Tech Exhibitions</li>
                    </ul>
                </div>
            </div>

            {{-- Facility 2: Science Laboratories --}}
            <div class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm card-lift flex flex-col justify-between group hover:border-[#f36c21] transition">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100 relative">
                    <img src="{{ asset('imgs/hero/hero_1.webp') }}" alt="Modern Science Laboratories" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-900 shadow-sm">
                        Physics, Chem & Bio
                    </div>
                </div>
                <div class="p-6">
                    <span class="text-xs font-bold text-[#f36c21] uppercase tracking-wider block mb-1">Practical Research</span>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">Modern Science Laboratories</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4">
                        Dedicated, ventilated laboratories for Physics, Organic/Inorganic Chemistry, and Molecular Biology equipped with optical microscopes and digital measurement sensors.
                    </p>
                    <ul class="text-xs text-slate-500 space-y-1.5 border-t border-slate-100 pt-4">
                        <li class="flex items-center gap-2"><i class="bi bi-check2 text-emerald-600 font-bold"></i> Compliant with International Lab Safety</li>
                        <li class="flex items-center gap-2"><i class="bi bi-check2 text-emerald-600 font-bold"></i> Individual Experiment Workbenches</li>
                    </ul>
                </div>
            </div>

            {{-- Facility 3: Central Research & E-Library --}}
            <div class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm card-lift flex flex-col justify-between group hover:border-[#f36c21] transition">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100 relative">
                    <img src="{{ asset('imgs/hero/hero_3.webp') }}" alt="Central Research E-Library" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-900 shadow-sm">
                        Knowledge Hub
                    </div>
                </div>
                <div class="p-6">
                    <span class="text-xs font-bold text-[#f36c21] uppercase tracking-wider block mb-1">Scholarly Sanctuary</span>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">Central Research E-Library</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4">
                        Over 15,000 reference volumes, peer-reviewed academic journals, daily newspapers, quiet reading alcoves, and digital tablets connected to academic databases.
                    </p>
                    <ul class="text-xs text-slate-500 space-y-1.5 border-t border-slate-100 pt-4">
                        <li class="flex items-center gap-2"><i class="bi bi-check2 text-emerald-600 font-bold"></i> Digital Catalog & E-Book Lending</li>
                        <li class="flex items-center gap-2"><i class="bi bi-check2 text-emerald-600 font-bold"></i> Collaborative Study Pods</li>
                    </ul>
                </div>
            </div>

            {{-- Facility 4: Sports Arena & Turf --}}
            <div class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm card-lift flex flex-col justify-between group hover:border-[#f36c21] transition">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100 relative">
                    <img src="{{ asset('imgs/schools.webp') }}" alt="Olympic Sports Arena" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-900 shadow-sm">
                        Athletics Arena
                    </div>
                </div>
                <div class="p-6">
                    <span class="text-xs font-bold text-[#f36c21] uppercase tracking-wider block mb-1">Athletics & Fitness</span>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">Olympic Sports Arena</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4">
                        Full-size futsal synthetic turf, standard basketball courts, badminton arena, indoor table tennis, and specialized athletics coaching under national trainers.
                    </p>
                    <ul class="text-xs text-slate-500 space-y-1.5 border-t border-slate-100 pt-4">
                        <li class="flex items-center gap-2"><i class="bi bi-check2 text-emerald-600 font-bold"></i> Inter-School Sports Tournaments</li>
                        <li class="flex items-center gap-2"><i class="bi bi-check2 text-emerald-600 font-bold"></i> Certified Fitness Instructors</li>
                    </ul>
                </div>
            </div>

            {{-- Facility 5: GPS Fleet --}}
            <div class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm card-lift flex flex-col justify-between group hover:border-[#f36c21] transition">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100 relative">
                    <img src="{{ asset('imgs/kathmandu.webp') }}" alt="GPS-Tracked Bus Fleet" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-900 shadow-sm">
                        Safe Transport
                    </div>
                </div>
                <div class="p-6">
                    <span class="text-xs font-bold text-[#f36c21] uppercase tracking-wider block mb-1">Safe Commute</span>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">GPS-Tracked Bus Fleet</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4">
                        Air-conditioned, modern school bus fleet traversing comprehensive city routes with trained caretakers and live mobile GPS tracking for parents.
                    </p>
                    <ul class="text-xs text-slate-500 space-y-1.5 border-t border-slate-100 pt-4">
                        <li class="flex items-center gap-2"><i class="bi bi-check2 text-emerald-600 font-bold"></i> Real-Time Parent App Tracking</li>
                        <li class="flex items-center gap-2"><i class="bi bi-check2 text-emerald-600 font-bold"></i> First-Aid & Speed Governors</li>
                    </ul>
                </div>
            </div>

            {{-- Facility 6: Smart Interactive Classrooms --}}
            <div class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm card-lift flex flex-col justify-between group hover:border-[#f36c21] transition">
                <div class="aspect-[16/10] overflow-hidden bg-slate-100 relative">
                    <img src="{{ file_exists(public_path('imgs/hero/school_hero_graduates.jpg')) ? asset('imgs/hero/school_hero_graduates.jpg') : asset('imgs/hero/hero_2.webp') }}" alt="Smart Interactive Classrooms" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                    <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-900 shadow-sm">
                        Smart AV Tech
                    </div>
                </div>
                <div class="p-6">
                    <span class="text-xs font-bold text-[#f36c21] uppercase tracking-wider block mb-1">Modern Classrooms</span>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">Smart Digital Classrooms</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4">
                        Acoustically designed, multimedia-equipped classrooms with digital interactive flat panels, high-speed Wi-Fi, and ergonomic furniture for deep engagement.
                    </p>
                    <ul class="text-xs text-slate-500 space-y-1.5 border-t border-slate-100 pt-4">
                        <li class="flex items-center gap-2"><i class="bi bi-check2 text-emerald-600 font-bold"></i> High-Resolution Interactive Touchscreens</li>
                        <li class="flex items-center gap-2"><i class="bi bi-check2 text-emerald-600 font-bold"></i> Pure Air Circulation & Natural Light</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</section>

@endsection
