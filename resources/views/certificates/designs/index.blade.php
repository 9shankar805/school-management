@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Page header --}}
    <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Certificate Designs</h1>
            <p class="text-slate-400 text-sm mt-0.5">Four ready-to-use certificate templates — pick one and apply it to any template</p>
        </div>
        <a href="{{ route('certificates.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-700 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition">
            <i class="bi bi-arrow-left"></i> Back to Templates
        </a>
    </div>

    {{-- Design cards grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

        {{-- Design 1 — Royal Classic --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition group">
            {{-- Thumbnail preview --}}
            <div class="relative overflow-hidden" style="height:180px; background: linear-gradient(135deg,#e8e0cc 0%,#fffdf7 100%);">
                {{-- Mini decorative preview --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center p-3">
                    <div class="w-full h-full border-2 border-[#1a3a6b] relative flex flex-col items-center justify-center p-3 gap-1">
                        <div class="absolute inset-[4px] border border-[#b8953e] pointer-events-none"></div>
                        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-[#1a3a6b] via-[#b8953e] to-[#1a3a6b]"></div>
                        <div class="absolute bottom-0 left-0 right-0 h-2 bg-gradient-to-r from-[#1a3a6b] via-[#b8953e] to-[#1a3a6b]"></div>
                        <div class="text-[7px] font-bold text-[#b8953e] tracking-widest uppercase">SCHOOL NAME</div>
                        <div class="text-[11px] font-bold text-[#1a3a6b] tracking-wider uppercase leading-tight">Certificate of<br>Achievement</div>
                        <div class="w-16 border-b border-[#b8953e] my-1"></div>
                        <div class="text-[8px] font-bold text-[#1a3a6b]">Student Name</div>
                        <div class="flex gap-2 mt-1">
                            <div class="text-[5px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">Class X</div>
                            <div class="text-[5px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">2081–82</div>
                        </div>
                    </div>
                </div>
                <div class="absolute top-2 left-2">
                    <span class="bg-[#1a3a6b] text-white text-[9px] font-bold px-2 py-0.5 rounded-full">Design 1</span>
                </div>
            </div>

            <div class="p-4 flex flex-col flex-1 gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Royal Classic</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Deep blue & gold · Double border · Ornate corners · Serif typography</p>
                </div>
                <div class="flex flex-wrap gap-1.5 mt-auto">
                    <span class="text-[10px] bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">Formal</span>
                    <span class="text-[10px] bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full">Gold Accent</span>
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-full">Serif</span>
                </div>
                <div class="flex gap-2 pt-2 border-t border-slate-50 dark:border-slate-700">
                    <a href="{{ route('certificates.design.preview', 1) }}" target="_blank"
                       class="flex-1 text-center py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-eye-fill me-1"></i> Preview
                    </a>
                    <a href="{{ route('certificates.design.apply', 1) }}"
                       class="flex-1 text-center py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-check-lg me-1"></i> Use This
                    </a>
                </div>
            </div>
        </div>

        {{-- Design 2 — Modern Gradient --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition group">
            <div class="relative overflow-hidden" style="height:180px; background:#f5f3ff;">
                <div class="absolute inset-0 flex flex-col">
                    {{-- Header band --}}
                    <div class="h-[60px] flex-shrink-0 relative overflow-hidden"
                         style="background: linear-gradient(135deg,#6c2eb9 0%,#8b5cf6 40%,#0ea5e9 70%,#06b6d4 100%);">
                        <div class="absolute inset-0 flex items-center px-3 gap-2">
                            <div class="w-6 h-6 rounded-full border border-white/50 bg-white/20 flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-[6px] font-bold">LOGO</span>
                            </div>
                            <div class="text-white text-[7px] font-bold tracking-wider">SCHOOL NAME</div>
                        </div>
                        <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 300 8" preserveAspectRatio="none">
                            <path d="M0,0 C50,8 100,0 150,5 C200,10 250,3 300,5 L300,8 L0,8 Z" fill="#f5f3ff"/>
                        </svg>
                    </div>
                    {{-- Body preview --}}
                    <div class="flex-1 flex flex-col items-center justify-center px-3 gap-1">
                        <span class="text-[6px] font-bold tracking-widest uppercase"
                              style="background:linear-gradient(90deg,#6c2eb9,#0ea5e9); -webkit-background-clip:text; color:transparent; background-clip:text;">Academic Certificate</span>
                        <div class="text-[10px] font-extrabold text-[#1e1b4b] tracking-wide uppercase">Certificate of Achievement</div>
                        <div class="w-10 h-[2px] rounded-full my-0.5"
                             style="background:linear-gradient(90deg,#6c2eb9,#06b6d4)"></div>
                        <div class="text-[7px] text-purple-700 font-bold">Student Name</div>
                        <div class="flex gap-1 mt-1 flex-wrap justify-center">
                            <span class="text-[5px] bg-slate-100 px-1.5 py-0.5 rounded-full text-slate-500">Grade X</span>
                            <span class="text-[5px] bg-slate-100 px-1.5 py-0.5 rounded-full text-slate-500">2081–82</span>
                        </div>
                    </div>
                </div>
                <div class="absolute top-2 left-2">
                    <span class="bg-purple-600 text-white text-[9px] font-bold px-2 py-0.5 rounded-full">Design 2</span>
                </div>
            </div>

            <div class="p-4 flex flex-col flex-1 gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Modern Gradient</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Purple-to-teal header · Wave ribbon · Pill badges · Sans-serif</p>
                </div>
                <div class="flex flex-wrap gap-1.5 mt-auto">
                    <span class="text-[10px] bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-full">Modern</span>
                    <span class="text-[10px] bg-cyan-50 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-300 px-2 py-0.5 rounded-full">Gradient</span>
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-full">Sans-serif</span>
                </div>
                <div class="flex gap-2 pt-2 border-t border-slate-50 dark:border-slate-700">
                    <a href="{{ route('certificates.design.preview', 2) }}" target="_blank"
                       class="flex-1 text-center py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-eye-fill me-1"></i> Preview
                    </a>
                    <a href="{{ route('certificates.design.apply', 2) }}"
                       class="flex-1 text-center py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-check-lg me-1"></i> Use This
                    </a>
                </div>
            </div>
        </div>

        {{-- Design 3 — Achievement Gold --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition group">
            <div class="relative overflow-hidden" style="height:180px; background:#1c0a00;">
                <div class="absolute inset-0 flex flex-col items-center justify-center p-3">
                    <div class="w-full h-full relative flex flex-col items-center justify-center gap-1"
                         style="border:2px solid #7b1e1e;">
                        <div class="absolute inset-[3px] border border-[#b8953e] pointer-events-none"></div>
                        {{-- Top/bottom stripe --}}
                        <div class="absolute top-0 left-0 right-0 h-1.5"
                             style="background:repeating-linear-gradient(90deg,#7b1e1e 0,#7b1e1e 6px,#b8953e 6px,#b8953e 8px,#7b1e1e 8px,#7b1e1e 14px)"></div>
                        <div class="absolute bottom-0 left-0 right-0 h-1.5"
                             style="background:repeating-linear-gradient(90deg,#7b1e1e 0,#7b1e1e 6px,#b8953e 6px,#b8953e 8px,#7b1e1e 8px,#7b1e1e 14px)"></div>
                        {{-- Medallion --}}
                        <div class="w-8 h-8 rounded-full border-2 border-[#b8953e] flex items-center justify-center mb-1"
                             style="background:radial-gradient(circle,#2d1200 40%,#1c0a00);">
                            <span class="text-[#d4a84b] text-sm">★</span>
                        </div>
                        <div class="text-[6px] font-bold text-[#b8953e] tracking-widest uppercase">SCHOOL NAME</div>
                        <div class="text-[11px] font-bold tracking-wider uppercase leading-tight text-center"
                             style="color:#d4a84b;">Certificate of<br>Excellence</div>
                        <div class="flex items-center gap-1 my-0.5">
                            <div class="w-8 border-t border-[#b8953e]"></div>
                            <span class="text-[#d4a84b] text-[8px]">★</span>
                            <div class="w-8 border-t border-[#b8953e]"></div>
                        </div>
                        <div class="text-[8px] font-bold" style="color:#d4a84b;">Student Name</div>
                    </div>
                </div>
                <div class="absolute top-2 left-2">
                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-full" style="background:#b8953e;color:#1c0a00;">Design 3</span>
                </div>
            </div>

            <div class="p-4 flex flex-col flex-1 gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Achievement Gold</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Dark maroon & gold · Central medallion · Star accents · Premium look</p>
                </div>
                <div class="flex flex-wrap gap-1.5 mt-auto">
                    <span class="text-[10px] bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full">Premium</span>
                    <span class="text-[10px] bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 px-2 py-0.5 rounded-full">Dark Theme</span>
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-full">Serif</span>
                </div>
                <div class="flex gap-2 pt-2 border-t border-slate-50 dark:border-slate-700">
                    <a href="{{ route('certificates.design.preview', 3) }}" target="_blank"
                       class="flex-1 text-center py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-eye-fill me-1"></i> Preview
                    </a>
                    <a href="{{ route('certificates.design.apply', 3) }}"
                       class="flex-1 text-center py-2 text-white rounded-lg text-xs font-semibold transition"
                       style="background:#b8953e;">
                        <i class="bi bi-check-lg me-1"></i> Use This
                    </a>
                </div>
            </div>
        </div>

        {{-- Design 4 — Minimalist Professional --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition group">
            <div class="relative overflow-hidden" style="height:180px; background:#f8fafc;">
                <div class="absolute inset-0 flex">
                    {{-- Left accent bar --}}
                    <div class="w-4 h-full flex-shrink-0 relative" style="background:#1e293b;">
                        <div class="absolute right-0 top-0 bottom-0 w-1"
                             style="background:linear-gradient(180deg,#3b82f6,#1d4ed8,#1e293b)"></div>
                    </div>
                    {{-- Content --}}
                    <div class="flex-1 flex flex-col justify-center px-3 py-3 gap-1.5">
                        {{-- Header --}}
                        <div class="flex items-center gap-2 pb-1.5 border-b border-slate-100">
                            <div class="w-5 h-5 bg-slate-800 rounded flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-[5px] font-bold">LOGO</span>
                            </div>
                            <span class="text-[7px] font-extrabold text-slate-800 tracking-wider uppercase">School Name</span>
                        </div>
                        {{-- Blue rule --}}
                        <div class="h-[2px] rounded-full w-full"
                             style="background:linear-gradient(90deg,#1d4ed8,#3b82f6,#e0f2fe)"></div>
                        {{-- Type tag --}}
                        <span class="text-[6px] font-bold tracking-widest text-blue-600 uppercase">Academic Certificate</span>
                        {{-- Title --}}
                        <div>
                            <span class="text-[11px] font-extrabold text-slate-900 tracking-wide">Certificate</span>
                            <span class="text-[11px] font-light text-slate-400 tracking-wide"> of Achievement</span>
                        </div>
                        {{-- Name block --}}
                        <div class="border-b-2 border-slate-800 pb-1">
                            <span class="text-[10px] font-extrabold text-blue-700">Student Full Name</span>
                        </div>
                        {{-- Meta --}}
                        <div class="grid grid-cols-2 gap-x-2 gap-y-0.5 bg-slate-50 rounded p-1.5">
                            <div><span class="text-[5px] text-slate-400 uppercase block">Class</span><span class="text-[6px] font-bold text-slate-700">Grade X</span></div>
                            <div><span class="text-[5px] text-slate-400 uppercase block">Year</span><span class="text-[6px] font-bold text-slate-700">2081–82</span></div>
                        </div>
                    </div>
                </div>
                <div class="absolute top-2 left-6">
                    <span class="bg-slate-800 text-white text-[9px] font-bold px-2 py-0.5 rounded-full">Design 4</span>
                </div>
            </div>

            <div class="p-4 flex flex-col flex-1 gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Minimalist Professional</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Charcoal sidebar · Blue rule · Dot grid · Clean corporate style</p>
                </div>
                <div class="flex flex-wrap gap-1.5 mt-auto">
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-full">Minimal</span>
                    <span class="text-[10px] bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">Corporate</span>
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-full">Sans-serif</span>
                </div>
                <div class="flex gap-2 pt-2 border-t border-slate-50 dark:border-slate-700">
                    <a href="{{ route('certificates.design.preview', 4) }}" target="_blank"
                       class="flex-1 text-center py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-eye-fill me-1"></i> Preview
                    </a>
                    <a href="{{ route('certificates.design.apply', 4) }}"
                       class="flex-1 text-center py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-check-lg me-1"></i> Use This
                    </a>
                </div>
            </div>
        </div>

        {{-- Design 5 — Emerald Honor --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition group mt-6 sm:mt-0">
            <div class="relative overflow-hidden" style="height:180px; background: linear-gradient(135deg,#064e3b 0%,#047857 100%);">
                <div class="absolute inset-2 border-2 border-[#d97706] p-2 flex flex-col items-center justify-center text-center">
                    <div class="text-[7px] font-bold text-amber-300 tracking-widest uppercase">GENESIS GLOBAL SCHOOL</div>
                    <div class="text-[10px] font-black text-white tracking-wider uppercase mt-1">Certificate of Excellence</div>
                    <div class="w-12 border-b border-amber-400 my-1"></div>
                    <div class="text-[8px] font-bold text-amber-200">Aarav Shrestha</div>
                </div>
                <div class="absolute top-2 left-2">
                    <span class="bg-emerald-800 text-amber-300 border border-amber-400 text-[9px] font-bold px-2 py-0.5 rounded-full">Design 5</span>
                </div>
            </div>
            <div class="p-4 flex flex-col flex-1 gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Emerald Honor & Gold Foil</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Deep emerald green · Gold filigree · Luxury honors style</p>
                </div>
                <div class="flex flex-wrap gap-1.5 mt-auto">
                    <span class="text-[10px] bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded-full">Emerald</span>
                    <span class="text-[10px] bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full">Gold Foil</span>
                </div>
                <div class="flex gap-2 pt-2 border-t border-slate-50 dark:border-slate-700">
                    <a href="{{ route('certificates.design.preview', 5) }}" target="_blank"
                       class="flex-1 text-center py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-eye-fill me-1"></i> Preview
                    </a>
                    <a href="{{ route('certificates.design.apply', 5) }}"
                       class="flex-1 text-center py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-check-lg me-1"></i> Use This
                    </a>
                </div>
            </div>
        </div>

        {{-- Design 6 — Vibrant Achievement --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition group">
            <div class="relative overflow-hidden" style="height:180px; background:#faf5ff;">
                <div class="h-10 bg-gradient-to-r from-purple-800 via-indigo-600 to-cyan-500 p-2 text-white flex items-center">
                    <span class="text-[7px] font-bold uppercase tracking-wider">GENESIS GLOBAL SCHOOL</span>
                </div>
                <div class="p-3 text-center flex flex-col items-center justify-center gap-1">
                    <span class="text-[6px] font-bold text-purple-700 uppercase tracking-widest">OUTSTANDING AWARD</span>
                    <div class="text-[10px] font-extrabold text-slate-900">Certificate of Achievement</div>
                    <span class="text-[7px] bg-purple-100 text-purple-800 font-bold px-2 py-0.5 rounded-full">Aarav Shrestha</span>
                </div>
                <div class="absolute top-2 left-2">
                    <span class="bg-purple-700 text-white text-[9px] font-bold px-2 py-0.5 rounded-full">Design 6</span>
                </div>
            </div>
            <div class="p-4 flex flex-col flex-1 gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Vibrant Achievement</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Flowing gradient header wave · Dynamic badges · Modern UI</p>
                </div>
                <div class="flex flex-wrap gap-1.5 mt-auto">
                    <span class="text-[10px] bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-full">Gradient Wave</span>
                    <span class="text-[10px] bg-cyan-50 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-300 px-2 py-0.5 rounded-full">Modern</span>
                </div>
                <div class="flex gap-2 pt-2 border-t border-slate-50 dark:border-slate-700">
                    <a href="{{ route('certificates.design.preview', 6) }}" target="_blank"
                       class="flex-1 text-center py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-eye-fill me-1"></i> Preview
                    </a>
                    <a href="{{ route('certificates.design.apply', 6) }}"
                       class="flex-1 text-center py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-check-lg me-1"></i> Use This
                    </a>
                </div>
            </div>
        </div>

        {{-- Design 7 — Imperial Crimson --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition group">
            <div class="relative overflow-hidden" style="height:180px; background: linear-gradient(135deg,#881337 0%,#4c0519 100%);">
                <div class="absolute inset-2 border border-amber-400 p-2 flex flex-col items-center justify-center text-center">
                    <div class="text-[7px] font-bold text-amber-300 uppercase tracking-widest">GENESIS GLOBAL SCHOOL</div>
                    <div class="text-[10px] font-black text-white uppercase mt-1">Certificate of Appreciation</div>
                    <div class="w-12 border-b border-amber-400 my-1"></div>
                    <div class="text-[8px] font-bold text-amber-200">Aarav Shrestha</div>
                </div>
                <div class="absolute top-2 left-2">
                    <span class="bg-rose-900 text-amber-300 border border-amber-400 text-[9px] font-bold px-2 py-0.5 rounded-full">Design 7</span>
                </div>
            </div>
            <div class="p-4 flex flex-col flex-1 gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Imperial Crimson</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Regal maroon & gold · Double border frame · Honor award style</p>
                </div>
                <div class="flex flex-wrap gap-1.5 mt-auto">
                    <span class="text-[10px] bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-300 px-2 py-0.5 rounded-full">Imperial</span>
                    <span class="text-[10px] bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full">Regal</span>
                </div>
                <div class="flex gap-2 pt-2 border-t border-slate-50 dark:border-slate-700">
                    <a href="{{ route('certificates.design.preview', 7) }}" target="_blank"
                       class="flex-1 text-center py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-eye-fill me-1"></i> Preview
                    </a>
                    <a href="{{ route('certificates.design.apply', 7) }}"
                       class="flex-1 text-center py-2 bg-rose-700 hover:bg-rose-800 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-check-lg me-1"></i> Use This
                    </a>
                </div>
            </div>
        </div>

        {{-- Design 8 — Traditional Parchment --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition group">
            <div class="relative overflow-hidden" style="height:180px; background:#fffbe6;">
                <div class="absolute inset-2 border-2 border-[#78350f] p-2 flex flex-col items-center justify-center text-center">
                    <div class="text-[7px] font-bold text-[#78350f] uppercase">GENESIS GLOBAL SCHOOL</div>
                    <div class="text-[6px] text-amber-800 font-bold">जेनेसेसि ग्लोबल स्कूल</div>
                    <div class="text-[10px] font-black text-[#78350f] uppercase mt-0.5">Character Certificate</div>
                    <div class="w-10 border-b border-[#78350f] my-1"></div>
                    <div class="text-[8px] font-bold text-[#92400e]">Aarav Shrestha</div>
                </div>
                <div class="absolute top-2 left-2">
                    <span class="bg-amber-900 text-white text-[9px] font-bold px-2 py-0.5 rounded-full">Design 8</span>
                </div>
            </div>
            <div class="p-4 flex flex-col flex-1 gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Traditional Parchment</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Vintage cream parchment paper · Bilingual English & Nepali</p>
                </div>
                <div class="flex flex-wrap gap-1.5 mt-auto">
                    <span class="text-[10px] bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-300 px-2 py-0.5 rounded-full">Parchment</span>
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-full">Bilingual</span>
                </div>
                <div class="flex gap-2 pt-2 border-t border-slate-50 dark:border-slate-700">
                    <a href="{{ route('certificates.design.preview', 8) }}" target="_blank"
                       class="flex-1 text-center py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-eye-fill me-1"></i> Preview
                    </a>
                    <a href="{{ route('certificates.design.apply', 8) }}"
                       class="flex-1 text-center py-2 bg-amber-700 hover:bg-amber-800 text-white rounded-lg text-xs font-semibold transition">
                        <i class="bi bi-check-lg me-1"></i> Use This
                    </a>
                </div>
            </div>
        </div>

    </div>{{-- /grid --}}

    {{-- Usage note --}}
    <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-5">
        <p class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="bi bi-info-circle-fill me-1.5"></i>How to use these designs
        </p>
        <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed">
            Each design is a standalone Blade template located in
            <code class="bg-blue-100 dark:bg-blue-800/40 px-1.5 py-0.5 rounded text-[11px]">resources/views/certificates/designs/</code>.
            They accept the same variables as the main <code class="bg-blue-100 dark:bg-blue-800/40 px-1.5 py-0.5 rounded text-[11px]">pdf.blade.php</code>
            (<code class="bg-blue-100 dark:bg-blue-800/40 px-1.5 py-0.5 rounded text-[11px]">$template</code>,
            <code class="bg-blue-100 dark:bg-blue-800/40 px-1.5 py-0.5 rounded text-[11px]">$student</code>,
            <code class="bg-blue-100 dark:bg-blue-800/40 px-1.5 py-0.5 rounded text-[11px]">$issued</code>, etc.)
            and can be rendered directly via DomPDF by pointing your controller to the chosen design file.
        </p>
    </div>

</div>
@endsection
