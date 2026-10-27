@extends('school-landing.layout')

@section('title', 'Contact, Admissions Helpline & Campus Location')

@section('content')

{{-- Page Header --}}
<div class="bg-white text-slate-900 py-12 border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">
            <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Contact</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Contact & Campus Location</h1>
        <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl">
            We welcome parents, prospective students, and education partners for campus visits and consultations.
        </p>
    </div>
</div>

<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Contact Cards Grid --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
            <div class="bg-slate-50 rounded-2xl p-6 text-center border border-slate-200 shadow-sm card-lift">
                <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-900 mb-1">Campus Location</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    {{ $school->address }}<br>
                    {{ $school->city ?? $school->district ?? 'Kathmandu' }}, Nepal
                </p>
            </div>

            <div class="bg-slate-50 rounded-2xl p-6 text-center border border-slate-200 shadow-sm card-lift">
                <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="bi bi-telephone-fill"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-900 mb-1">Helpline Phone</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    @if($school->phone)
                        <a href="tel:{{ $school->phone }}" class="text-emerald-700 font-semibold hover:underline">{{ $school->phone }}</a>
                    @else
                        +977-1-4412345
                    @endif
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Sun - Fri, 9:00 AM - 4:30 PM</p>
            </div>

            <div class="bg-slate-50 rounded-2xl p-6 text-center border border-slate-200 shadow-sm card-lift">
                <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="bi bi-envelope-fill"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-900 mb-1">Email Queries</h4>
                <p class="text-xs text-slate-600 leading-relaxed break-all">
                    @if($school->email)
                        <a href="mailto:{{ $school->email }}" class="text-amber-700 font-semibold hover:underline">{{ $school->email }}</a>
                    @else
                        info@genesisgs.edu.in
                    @endif
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Official Response Desk</p>
            </div>

            <div class="bg-slate-50 rounded-2xl p-6 text-center border border-slate-200 shadow-sm card-lift">
                <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-900 mb-1">Digital LMS Portal</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Access grades, homework, routines and online attendance.
                </p>
                <a href="{{ url('login') }}" class="inline-block mt-2 text-xs font-bold text-purple-700 hover:underline">
                    Login to Portal &rarr;
                </a>
            </div>
        </div>

        {{-- Send Inquiry Form & Map Directions --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            <div class="lg:col-span-7 bg-slate-50 rounded-3xl p-6 sm:p-10 border border-slate-200 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-widest text-[#f36c21] block mb-1">Direct Consultation</span>
                <h3 class="text-2xl font-extrabold text-slate-900 mb-6">Send an Inquiry to Administration</h3>

                <form onsubmit="handleInquiry(event)" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Parent / Student Name *</label>
                            <input type="text" required placeholder="Full name" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-medium focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Contact Phone Number *</label>
                            <input type="tel" required placeholder="98XXXXXXXX" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-medium focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                            <input type="email" placeholder="name@example.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-medium focus:ring-2 focus:ring-slate-900 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Program of Interest</label>
                            <select class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-medium focus:ring-2 focus:ring-slate-900 focus:outline-none">
                                <option>+2 Science Stream</option>
                                <option>+2 Management Stream</option>
                                <option>+2 Humanities & Law</option>
                                <option>Secondary & Basic (Grades 1-10)</option>
                                <option>General Inquiries</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Your Message or Inquiry</label>
                        <textarea rows="4" required placeholder="Write your question or request a campus tour appointment..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-medium focus:ring-2 focus:ring-slate-900 focus:outline-none"></textarea>
                    </div>

                    <button type="submit" class="btn-brand-orange w-full sm:w-auto text-xs px-8 py-3">
                        <i class="bi bi-send-fill"></i> Submit Inquiry to Admissions
                    </button>
                    <div id="inquirySuccessToast" class="hidden p-3 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-bold">
                        <i class="bi bi-check-circle-fill"></i> Thank you! Your inquiry has been submitted. Our admissions counselor will contact you within 24 hours.
                    </div>
                </form>
            </div>

            <div class="lg:col-span-5 space-y-6">
                {{-- Dynamic School Hours & Real-time Live Status --}}
                <div class="bg-white text-slate-900 rounded-2xl p-8 shadow-sm border border-slate-200 relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-slate-500 text-xs font-bold uppercase tracking-widest block">Institution Timings</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $school->open_status_badge['class'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $school->is_open_now ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }} inline-block mr-1"></span>
                            {{ $school->open_status_badge['badge'] }}
                        </span>
                    </div>
                    <h3 class="text-xl font-bold mb-4 text-slate-900">School & Office Timings</h3>
                    <div class="space-y-3 text-xs text-slate-600">
                        <div class="flex justify-between border-b border-slate-200 pb-2">
                            <span>Regular Classes (Sun – Thu):</span>
                            <strong class="text-slate-900">{{ $school->school_hours_weekdays }}</strong>
                        </div>
                        <div class="flex justify-between border-b border-slate-200 pb-2">
                            <span>Friday (Half Day / Assembly):</span>
                            <strong class="text-slate-900">{{ $school->school_hours_friday }}</strong>
                        </div>
                        <div class="flex justify-between border-b border-slate-200 pb-2">
                            <span>Administration & Admission Desk:</span>
                            <strong class="text-slate-900">{{ $school->office_hours }}</strong>
                        </div>
                        <div class="flex justify-between text-slate-800 font-bold">
                            <span>Weekend (Saturday & Public Holidays):</span>
                            <strong>{{ $school->school_hours_weekend }}</strong>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200 text-[11px] text-slate-500 flex items-center gap-1.5">
                        <i class="bi bi-info-circle-fill text-slate-700"></i>
                        <span>{{ $school->open_status_badge['subtext'] }}</span>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-2xl p-8 border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-2 mb-2 text-slate-800 font-bold text-xs uppercase tracking-wider">
                        <i class="bi bi-mortarboard-fill text-base"></i> Admissions Counseling
                    </div>
                    <h4 class="font-bold text-slate-900 text-sm mb-2">Have questions about 2026 Admissions?</h4>
                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                        Speak directly with our academic counselors for syllabus details, stream selection, scholarship criteria, and campus visits.
                    </p>
                    <div class="flex flex-col gap-2">
                        <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2">
                            <i class="bi bi-pencil-square"></i> Apply for Admission Online
                        </a>
                        <button onclick="openProspectusModal()" class="w-full py-2.5 px-4 bg-white border border-slate-300 hover:bg-slate-50 text-slate-900 font-bold rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2">
                            <i class="bi bi-file-earmark-pdf"></i> Download Prospectus (PDF)
                        </button>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-2 text-slate-900 font-bold text-xs uppercase tracking-wider mb-3">
                        <i class="bi bi-geo-alt-fill text-base text-[#f36c21]"></i> Campus Location Map
                    </div>
                    <div class="rounded-xl overflow-hidden border border-slate-200 aspect-[16/10] bg-slate-200 relative mb-3">
                        @php
                            $mapQuery = urlencode($school->name . ', ' . ($school->address ?? '') . ', ' . ($school->city ?? 'Nepal'));
                        @endphp
                        <iframe 
                            src="https://maps.google.com/maps?q={{ $mapQuery }}&t=&z=14&ie=UTF8&iwloc=&output=embed" 
                            class="w-full h-full border-0" 
                            loading="lazy" 
                            allowfullscreen>
                        </iframe>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-medium">{{ $school->address }}, {{ $school->city ?? $school->district ?? 'Nepal' }}</span>
                        <a href="https://maps.google.com/?q={{ $mapQuery }}" target="_blank" class="font-bold text-[#f36c21] hover:underline flex items-center gap-1">
                            <span>Open in Maps</span> <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

                @if($school->facebook_url || $school->instagram_url || $school->youtube_url || $school->twitter_url)
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-2 text-slate-900 font-bold text-xs uppercase tracking-wider mb-3">
                        <i class="bi bi-share-fill text-base text-[#f36c21]"></i> Connect on Social Media
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($school->facebook_url)
                        <a href="{{ $school->facebook_url }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition flex items-center gap-1.5 shadow-sm">
                            <i class="bi bi-facebook"></i> Facebook
                        </a>
                        @endif
                        @if($school->instagram_url)
                        <a href="{{ $school->instagram_url }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white text-xs font-bold hover:opacity-90 transition flex items-center gap-1.5 shadow-sm">
                            <i class="bi bi-instagram"></i> Instagram
                        </a>
                        @endif
                        @if($school->youtube_url)
                        <a href="{{ $school->youtube_url }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700 transition flex items-center gap-1.5 shadow-sm">
                            <i class="bi bi-youtube"></i> YouTube
                        </a>
                        @endif
                        @if($school->twitter_url)
                        <a href="{{ $school->twitter_url }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition flex items-center gap-1.5 shadow-sm">
                            <i class="bi bi-twitter-x"></i> X / Twitter
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>
</section>

@push('scripts')
<script>
function handleInquiry(e) {
    e.preventDefault();
    const toast = document.getElementById('inquirySuccessToast');
    if (toast) {
        toast.classList.remove('hidden');
        e.target.reset();
    }
}
</script>
@endpush

@endsection
