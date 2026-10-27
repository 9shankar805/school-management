@extends('school-landing.layout')

@section('title', 'About Our Academy & Leadership')

@section('content')

{{-- Breadcrumb / Page Header --}}
<div class="bg-white text-slate-900 py-12 border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">
            <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">About Us</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900">About {{ $school->name }}</h1>
        <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl">
            A sanctuary of learning dedicated to academic excellence, character formation, and global intellectual leadership.
        </p>
    </div>
</div>

{{-- Institutional Heritage & Story --}}
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6 relative">
                <div class="rounded-2xl overflow-hidden shadow-lg border border-slate-200 bg-white aspect-[4/3]">
                    @php
                        $aboutImg = !empty($school->gallery_images[0]) 
                            ? asset('storage/' . $school->gallery_images[0]) 
                            : asset('imgs/hero/hero_1.webp');
                    @endphp
                    <img src="{{ $aboutImg }}" alt="{{ $school->name }} Campus" class="w-full h-full object-cover">
                </div>
            </div>

            <div class="lg:col-span-6">
                <div class="flex items-center gap-2 mb-3 flex-wrap">
                    <span class="text-xs font-bold tracking-widest uppercase text-slate-700 bg-slate-100 px-3.5 py-1 rounded-md border border-slate-200 inline-block">
                        Our Heritage & Values
                    </span>
                    @if($school->iemis_code)
                    <span class="text-xs font-bold text-slate-800 bg-emerald-50 px-3 py-1 rounded-md border border-emerald-200 inline-flex items-center gap-1.5">
                        <i class="bi bi-shield-check text-emerald-600"></i> IEMIS: {{ $school->iemis_code }}
                    </span>
                    @endif
                    @if($school->established_year)
                    <span class="text-xs font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded-md border border-slate-200">
                        ESTD. {{ $school->established_year }}
                    </span>
                    @endif
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-6 leading-tight">
                    Shaping Intellect & Ethical Integrity
                </h2>
                <div class="text-slate-600 text-sm sm:text-base leading-relaxed space-y-4 mb-6">
                    @if($school->about_us)
                        <p>{{ $school->about_us }}</p>
                    @endif
                    @if($school->description && $school->description !== $school->about_us)
                        <p>{{ $school->description }}</p>
                    @endif
                    @if($school->history)
                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200">
                            <h4 class="font-bold text-slate-900 text-base mb-2">Our History & Inception</h4>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $school->history }}</p>
                        </div>
                    @else
                        <p>
                            Founded on principles of holistic human development, <strong>{{ $school->name }}</strong> provides a stimulating academic environment where inquiry, scientific thought, and artistic expression thrive alongside disciplined character building.
                        </p>
                        <p>
                            Our graduates consistently excel in national board examinations (SEE and NEB +2), competitive university entrance tests, and extracurricular arenas spanning debate, sports, and international olympiads.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mission, Vision, Core Values --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16 pt-16 border-t border-slate-200">
            <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm card-lift hover:border-[#f36c21] transition">
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#f36c21] flex items-center justify-center text-2xl mb-4 font-bold border border-orange-200">
                    <i class="bi bi-compass-fill"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-900 mb-2">Our Mission</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    {{ $school->mission ?: 'To impart world-class, value-grounded education that empowers curious young minds to become innovative problem solvers and ethical global citizens.' }}
                </p>
            </div>

            <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm card-lift hover:border-[#f36c21] transition">
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#f36c21] flex items-center justify-center text-2xl mb-4 font-bold border border-orange-200">
                    <i class="bi bi-eye-fill"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-900 mb-2">Our Vision</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    {{ $school->vision ?: 'To stand as a nationally admired academic citadel known for transformative teaching, scientific rigor, inclusive culture, and compassionate leadership.' }}
                </p>
            </div>

            <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm card-lift hover:border-[#f36c21] transition">
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#f36c21] flex items-center justify-center text-2xl mb-4 font-bold border border-orange-200">
                    <i class="bi bi-gem"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-900 mb-2">Core Pillars</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Integrity in thought and action, intellectual curiosity, moral courage, empathy for society, and uncompromising dedication to academic distinction.
                </p>
            </div>
        </div>

        {{-- Visual Campus Showcase (Photo Quad) --}}
        <div class="mt-16 pt-16 border-t border-slate-200">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-bold tracking-widest uppercase text-[#f36c21] bg-orange-50 px-3.5 py-1 rounded-md border border-orange-200 inline-block mb-2">
                    Campus Life
                </span>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                    Our Campus in Pictures
                </h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $quadImages = [
                        !empty($school->gallery_images[0]) ? asset('storage/' . $school->gallery_images[0]) : asset('imgs/hero/hero_1.webp'),
                        !empty($school->gallery_images[1]) ? asset('storage/' . $school->gallery_images[1]) : asset('imgs/hero/hero_2.webp'),
                        !empty($school->gallery_images[2]) ? asset('storage/' . $school->gallery_images[2]) : asset('imgs/hero/hero_3.webp'),
                        !empty($school->gallery_images[3]) ? asset('storage/' . $school->gallery_images[3]) : (file_exists(public_path('imgs/hero/school_hero_graduates.jpg')) ? asset('imgs/hero/school_hero_graduates.jpg') : asset('imgs/schools.webp')),
                    ];
                @endphp
                @foreach($quadImages as $qIdx => $qImg)
                <div class="rounded-2xl overflow-hidden shadow-sm border border-slate-200 aspect-square group">
                    <img src="{{ $qImg }}" alt="{{ $school->name }} Campus View {{ $qIdx + 1 }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- School Management Committee (SMC) Section --}}
@if(isset($committeeMembers) && $committeeMembers->count() > 0)
<section class="py-16 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold tracking-widest uppercase text-[#f36c21] bg-orange-50 px-3.5 py-1 rounded-md border border-orange-200 inline-block mb-2">
                Governance & Leadership
            </span>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                School Management Committee (SMC)
            </h3>
            <p class="text-slate-600 text-xs sm:text-sm mt-1">
                Distinguished guardians and community leaders ensuring academic excellence and governance integrity.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($committeeMembers as $cm)
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm card-lift flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#f36c21] flex items-center justify-center text-xl font-bold border border-orange-200 shrink-0">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-base text-slate-900 leading-snug">{{ $cm->full_name }}</h4>
                            <span class="text-xs font-semibold text-[#f36c21] uppercase tracking-wide block">
                                {{ \App\Models\SchoolCommitteeMember::POSITIONS[$cm->position] ?? $cm->position }}
                            </span>
                        </div>
                    </div>
                    @if($cm->appointed_at)
                    <p class="text-[11px] text-slate-500 mb-2">
                        <i class="bi bi-calendar-check mr-1"></i> Appointed: {{ $cm->appointed_at->format('M Y') }}
                    </p>
                    @endif
                    @if($cm->no_political_party_member)
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 mb-2">
                        <i class="bi bi-check-circle-fill text-emerald-600"></i> Non-Political Code Compliant
                    </span>
                    @endif
                </div>
                @if($cm->phone)
                <div class="pt-3 border-t border-slate-100 text-xs text-slate-600">
                    <i class="bi bi-telephone text-slate-400 mr-1"></i> {{ $cm->phone }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Leadership / Principal Address --}}
<section class="py-20 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-slate-50 rounded-2xl p-8 sm:p-12 border border-slate-200 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-4 flex flex-col items-center text-center">
                    <div class="relative">
                        <div class="w-56 h-56 rounded-2xl overflow-hidden shadow-lg border border-slate-200 p-1 bg-white">
                            <img src="{{ $school->principal_photo_url }}" 
                                 onerror="this.src='{{ asset('imgs/profile.webp') }}'" 
                                 alt="{{ $school->principal_name_resolved }}" 
                                 class="w-full h-full object-cover rounded-xl">
                        </div>
                        <div class="absolute -bottom-4 bg-slate-900 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-md">
                            Office of the Principal
                        </div>
                    </div>
                    <div class="mt-8">
                        <h3 class="font-bold text-xl text-slate-900">{{ $school->principal_name_resolved }}</h3>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mt-0.5">{{ $school->settings['principal_title'] ?? 'Head of Institution / Principal' }}</p>
                        <p class="text-xs text-[#f36c21] font-bold mt-1">{{ $school->name }}</p>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-600 mb-2">
                        <i class="bi bi-quote text-2xl leading-none text-[#f36c21]"></i> Leadership Welcome Address
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-5 leading-tight">
                        From the Principal's Desk
                    </h2>
                    <div class="text-slate-700 leading-relaxed text-sm sm:text-base space-y-4">
                        @if($school->principal_message)
                            <p class="italic text-slate-800 font-medium border-l-4 border-[#f36c21] pl-4 py-2 bg-white rounded-r-lg border border-slate-200">
                                "{{ $school->principal_message }}"
                            </p>
                        @endif
                        <p>
                            Welcome to <strong>{{ $school->name }}</strong>. Education at our institution is an invigorating journey of discovery. We believe that true learning transcends textbook memorization; it is about cultivating agile minds capable of solving real-world challenges with empathy and discernment.
                        </p>
                        <p>
                            We warmly invite prospective parents and students to visit our campus, observe our dynamic classrooms and laboratories, and experience the warmth of our scholarly community.
                        </p>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-200 flex flex-wrap items-center gap-4">
                        <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-primary-dark">
                            <i class="bi bi-mortarboard-fill"></i> Apply for Admission 2026/27
                        </a>
                        <a href="{{ $pageUrl('faculty') }}" class="btn-secondary-white">
                            <i class="bi bi-person-lines-fill"></i> Meet Our Faculty
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
