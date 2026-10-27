@extends('school-landing.layout')

@section('title', 'Admissions 2026-2027 & How to Apply')

@section('content')

{{-- Page Header --}}
<div class="bg-white text-slate-900 py-12 border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">
            <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Admissions</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Admissions 2026–2027</h1>
        <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl">
            Join a legacy of distinction. Transparent admission policies, merit scholarships, and straightforward online enrollment.
        </p>
    </div>
</div>

{{-- 4-Step Admission Roadmap --}}
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="text-xs font-bold tracking-widest uppercase text-slate-700 bg-slate-100 px-3.5 py-1 rounded-md border border-slate-200">
                Enrollment Procedure
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3 mb-3">
                How to Apply in 4 Simple Steps
            </h2>
            <p class="text-slate-600 max-w-2xl mx-auto text-sm sm:text-base">
                Follow our streamlined digital roadmap to secure your student placement for the upcoming session.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-16">
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm card-lift hover:border-slate-400 transition">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-900 font-bold text-base flex items-center justify-center mb-4">
                    1
                </div>
                <h4 class="font-bold text-base text-slate-900 mb-2">Online Application</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Fill out our verified online registration form and attach preliminary academic grades.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm card-lift hover:border-slate-400 transition">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-900 font-bold text-base flex items-center justify-center mb-4">
                    2
                </div>
                <h4 class="font-bold text-base text-slate-900 mb-2">Entrance & Assessment</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Participate in the written entrance examination and interactive counseling session.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm card-lift hover:border-slate-400 transition">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-900 font-bold text-base flex items-center justify-center mb-4">
                    3
                </div>
                <h4 class="font-bold text-base text-slate-900 mb-2">Merit List & Awards</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Review published merit list rankings, scholarship awards, and placement confirmation.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm card-lift hover:border-slate-400 transition">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-900 font-bold text-base flex items-center justify-center mb-4">
                    4
                </div>
                <h4 class="font-bold text-base text-slate-900 mb-2">Formal Enrollment</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Complete enrollment verification and collect your official student ID, academic handbook, and uniform kit.
                </p>
            </div>
        </div>

        @if($school->admission_info)
        <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm mb-16">
            <div class="flex items-center gap-2 text-[#f36c21] font-bold text-xs uppercase tracking-wider mb-2">
                <i class="bi bi-info-circle-fill text-base"></i> Institutional Admission Guidelines
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">Notice from Admissions Committee</h3>
            <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-2 whitespace-pre-line">
                {{ $school->admission_info }}
            </div>
        </div>
        @endif

        {{-- Direct CTA --}}
        <div class="bg-slate-50 rounded-2xl p-8 sm:p-12 text-slate-900 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6 border border-slate-200 mb-16">
            <div>
                <span class="text-slate-600 text-xs font-bold uppercase tracking-widest block mb-1">Session 2026-2027</span>
                <h3 class="text-2xl sm:text-3xl font-bold mb-2 text-slate-900">Submit Your Application Online</h3>
                <p class="text-slate-600 text-sm max-w-xl">
                    Fill the digital form in less than 5 minutes. Receive your entrance exam admit card and scholarship eligibility score promptly.
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-primary-dark shadow-sm">
                    <i class="bi bi-pencil-square"></i> Begin Online Application Now
                </a>
                <button onclick="openProspectusModal()" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-800 font-bold text-sm bg-white hover:bg-slate-50 transition flex items-center gap-2 shadow-sm">
                    <i class="bi bi-file-earmark-pdf"></i> Download Prospectus
                </button>
            </div>
        </div>

        {{-- Scholarship Policy & Merit Matrix --}}
        <div class="mb-16">
            <div class="text-center max-w-3xl mx-auto mb-10">
                <span class="text-xs font-bold tracking-widest uppercase text-slate-700 bg-slate-100 px-3.5 py-1 rounded-md border border-slate-200 inline-block mb-2">
                    Scholarship Matrix
                </span>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-2">
                    Merit & Need-Based Scholarship Scheme
                </h3>
                <p class="text-slate-600 text-xs sm:text-sm">
                    We ensure financial limitations never impede high-achieving scholars. Scholarships are granted on annual tuition fees.
                </p>
            </div>

            <div class="overflow-x-auto bg-white rounded-2xl border border-slate-200 shadow-sm mb-6">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-100 text-slate-900 text-[11px] uppercase font-bold border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">Category / Qualification</th>
                            <th class="px-5 py-3.5">Eligibility Benchmark</th>
                            <th class="px-5 py-3.5">Scholarship Quota</th>
                            <th class="px-5 py-3.5">Tuition Concession</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-900">SEE Board Top Tier Distinction</td>
                            <td class="px-5 py-3.5">GPA 3.80 – 4.00</td>
                            <td class="px-5 py-3.5"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 text-[10px] font-bold border border-slate-200">Merit Quota</span></td>
                            <td class="px-5 py-3.5 font-bold text-slate-900">100% Full Tuition Waiver</td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-900">SEE Board High Distinction</td>
                            <td class="px-5 py-3.5">GPA 3.60 – 3.79</td>
                            <td class="px-5 py-3.5"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 text-[10px] font-bold border border-slate-200">Academic Quota</span></td>
                            <td class="px-5 py-3.5 font-bold text-slate-900">75% Tuition Waiver</td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-900">Entrance Examination Topper</td>
                            <td class="px-5 py-3.5">Top 10 Entrance Rank</td>
                            <td class="px-5 py-3.5"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 text-[10px] font-bold border border-slate-200">Competitive</span></td>
                            <td class="px-5 py-3.5 font-bold text-slate-900">100% Tuition Waiver</td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-900">National Athletes & Artists</td>
                            <td class="px-5 py-3.5">National/Provincial Medalist</td>
                            <td class="px-5 py-3.5"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 text-[10px] font-bold border border-slate-200">ECA / Sports</span></td>
                            <td class="px-5 py-3.5 font-bold text-slate-900">50% – 100% Waiver</td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-900">Sibling Concession</td>
                            <td class="px-5 py-3.5">Two or more siblings enrolled</td>
                            <td class="px-5 py-3.5"><span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 text-[10px] font-bold border border-slate-200">Family Benefit</span></td>
                            <td class="px-5 py-3.5 font-bold text-slate-700">25% Younger Sibling Concession</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Document Checklist & Admission Schedule --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base mb-4">
                    <i class="bi bi-folder-check text-xl text-slate-700"></i> Required Documents Checklist
                </div>
                <ul class="space-y-2.5 text-xs text-slate-700">
                    <li class="flex items-start gap-2.5">
                        <i class="bi bi-check2-square text-slate-900 font-bold text-sm mt-0.5"></i>
                        <span>Copy of Secondary Education Examination (SEE) grade-sheet & character certificate.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i class="bi bi-check2-square text-slate-900 font-bold text-sm mt-0.5"></i>
                        <span>Transfer Certificate (TC) from the previous institutional authority.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i class="bi bi-check2-square text-slate-900 font-bold text-sm mt-0.5"></i>
                        <span>Two recent passport-sized color photographs of the student.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i class="bi bi-check2-square text-slate-900 font-bold text-sm mt-0.5"></i>
                        <span>Copy of Birth Registration Certificate or Citizenship Card of parents.</span>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base mb-4">
                    <i class="bi bi-calendar-event text-xl text-slate-700"></i> Admission Key Dates & Timeline
                </div>
                <div class="space-y-3 text-xs text-slate-700">
                    <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                        <div>
                            <strong class="text-slate-900 block font-bold">Online Application Opening</strong>
                            <span class="text-[11px] text-slate-500">Digital Portal Registration Active</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-slate-200 text-slate-800 font-bold text-[10px]">Open Now</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                        <div>
                            <strong class="text-slate-900 block font-bold">Entrance Examination Phase 1</strong>
                            <span class="text-[11px] text-slate-500">Physical & Online Testing</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-slate-200 text-slate-800 font-bold text-[10px]">Weekly Saturdays</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                        <div>
                            <strong class="text-slate-900 block font-bold">Merit List & Scholarship Publication</strong>
                            <span class="text-[11px] text-slate-500">Official Notice Board & SMS Notification</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-slate-200 text-slate-800 font-bold text-[10px]">Within 48 Hours</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- FAQ --}}
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-8">
                <span class="text-xs font-bold tracking-widest uppercase text-slate-700 bg-slate-100 px-3.5 py-1 rounded-md border border-slate-200 inline-block mb-2">
                    Clarifications
                </span>
                <h3 class="text-2xl font-extrabold text-slate-900">
                    Frequently Asked Questions
                </h3>
            </div>

            <div class="space-y-3" id="admissionsFaq">
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                    <button class="w-full px-5 py-3.5 text-left font-bold text-xs sm:text-sm text-slate-800 flex items-center justify-between gap-4 hover:bg-slate-50" onclick="toggleFaq(this)">
                        <span>What documents are required for admission submission?</span>
                        <i class="bi bi-chevron-down text-slate-400"></i>
                    </button>
                    <div class="faq-content px-5 pb-3.5 text-xs text-slate-600 leading-relaxed hidden">
                        Copy of SEE marksheet/grade sheet, character certificate, 2 passport-size photographs, and copy of birth certificate / citizenship.
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                    <button class="w-full px-5 py-3.5 text-left font-bold text-xs sm:text-sm text-slate-800 flex items-center justify-between gap-4 hover:bg-slate-50" onclick="toggleFaq(this)">
                        <span>What is the syllabus for the +2 entrance examination?</span>
                        <i class="bi bi-chevron-down text-slate-400"></i>
                    </button>
                    <div class="faq-content px-5 pb-3.5 text-xs text-slate-600 leading-relaxed hidden">
                        The entrance exam tests Secondary Level (Grade 9 & 10) Compulsory Mathematics, Science, and English for Science stream; and Mathematics, English, and General Aptitude for Management & Law streams.
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                    <button class="w-full px-5 py-3.5 text-left font-bold text-xs sm:text-sm text-slate-800 flex items-center justify-between gap-4 hover:bg-slate-50" onclick="toggleFaq(this)">
                        <span>Are transportation and hostel facilities available for students?</span>
                        <i class="bi bi-chevron-down text-slate-400"></i>
                    </button>
                    <div class="faq-content px-5 pb-3.5 text-xs text-slate-600 leading-relaxed hidden">
                        Yes, our school operates safe GPS-tracked bus routes across key city corridors and offers supervised residential hostel wings for boys and girls.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
