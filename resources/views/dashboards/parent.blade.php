@extends('layouts.app')
@section('title', 'Parent Dashboard — NepalSchoolHub')

@section('content')
<style>
    /* Responsive Grid & Layout Classes */
    .parent-hero-grid {
        display: grid;
        grid-template-columns: 5fr 7fr;
        gap: 1rem;
        align-items: stretch;
    }

    .parent-chips-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
        align-items: center;
    }

    .parent-stat-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem;
    }

    .parent-middle-grid {
        display: grid;
        grid-template-columns: 4fr 5fr 3fr;
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .parent-bottom-grid {
        display: grid;
        grid-template-columns: 8fr 4fr;
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .parent-donut-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin: 1rem 0;
    }

    /* Tablet & Medium Screen Overrides (max-width: 1024px) */
    @media (max-width: 1024px) {
        .parent-hero-grid {
            grid-template-columns: 1fr !important;
        }

        .parent-chips-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }

        .parent-stat-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }

        .parent-middle-grid {
            grid-template-columns: 1fr !important;
        }

        .parent-bottom-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Mobile Phone Screen Overrides (max-width: 640px) */
    @media (max-width: 640px) {
        .parent-banner-padding {
            padding: 1.25rem 1rem 5.5rem 1rem !important;
        }

        .parent-content-container {
            margin-top: -3.5rem !important;
            padding: 0 1rem !important;
        }

        .parent-chips-grid {
            grid-template-columns: 1fr !important;
        }

        .parent-stat-grid {
            grid-template-columns: 1fr !important;
        }

        .parent-donut-container {
            flex-direction: column !important;
            justify-content: center !important;
            text-align: center !important;
        }

        .parent-action-buttons {
            width: 100% !important;
            justify-content: flex-start !important;
            flex-wrap: wrap !important;
        }

        .parent-action-buttons a {
            flex-grow: 1 !important;
            justify-content: center !important;
        }
    }
</style>

<div style="background-color: #f8fafc; min-height: 100vh; font-family: 'Inter', system-ui, -apple-system, sans-serif; padding-bottom: 5rem;">

    {{-- ── 1. ROYAL BLUE HERO BANNER ────────────────────────────────────────────── --}}
    <div class="parent-banner-padding" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%); color: #ffffff; padding: 2rem 2rem 6.5rem 2rem; position: relative;">
        <div style="max-width: 80rem; margin: 0 auto;">

            {{-- Top Info Header & Action Buttons --}}
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <div style="color: #93c5fd; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">
                        {{ strtoupper(now()->format('l, F j, Y')) }}
                    </div>
                    <h1 style="color: #ffffff; font-size: 1.75rem; font-weight: 900; margin: 0.25rem 0 0 0; line-height: 1.2;">
                        Welcome back, {{ $parent->first_name }} 👋
                    </h1>
                    <p style="color: #dbeafe; font-size: 0.875rem; margin: 0.25rem 0 0 0;">
                        Here's what's happening with {{ $childData->first()?->child->first_name ?? 'your child' }} today.
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="parent-action-buttons" style="display: flex; align-items: center; gap: 0.75rem;">
                    <a href="{{ route('parent.conversations') }}"
                       style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); color: #ffffff; padding: 0.55rem 1.25rem; border-radius: 0.75rem; font-size: 0.8125rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: background 0.2s;">
                        <i class="bi bi-chat-dots-fill" style="color: #bfdbfe;"></i>
                        <span>Messages</span>
                        @if($unreadMessages > 0)
                        <span style="background: #ef4444; color: #ffffff; font-size: 0.625rem; font-weight: 900; padding: 0.1rem 0.4rem; border-radius: 9999px;">{{ $unreadMessages }}</span>
                        @endif
                    </a>
                    <a href="{{ route('events.show') }}"
                       style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); color: #ffffff; padding: 0.55rem 1.25rem; border-radius: 0.75rem; font-size: 0.8125rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: background 0.2s;">
                        <i class="bi bi-calendar-event-fill" style="color: #bfdbfe;"></i>
                        <span>Calendar</span>
                    </a>
                </div>
            </div>

            {{-- Integrated Child Profile Card Overlay --}}
            @if($cd = $childData->first())
            @php
                $childId = $cd->child->id ?? 1;
                $rawClass = $cd->promotion?->schoolClass?->class_name ?? '1';
                $className = Str::contains(strtolower($rawClass), 'grade') ? $rawClass : 'Grade ' . $rawClass;
                $rawSection = $cd->promotion?->section?->section_name ?? 'A';
                $sectionName = Str::contains(strtolower($rawSection), 'section') ? $rawSection : 'Section ' . $rawSection;
            @endphp
            <div class="parent-hero-grid">

                {{-- Left Dark Profile Card --}}
                <div style="background: #111827; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 1.25rem; padding: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
                    <div style="display: flex; align-items: center; gap: 1rem; min-width: 0;">
                        <div style="position: relative; flex-shrink: 0;">
                            <img src="{{ $cd->child->avatar }}" style="width: 3.5rem; height: 3.5rem; border-radius: 9999px; object-fit: cover; border: 2px solid rgba(255, 255, 255, 0.3);" alt="{{ $cd->child->full_name }}">
                            <span style="position: absolute; bottom: 0; right: 0; width: 0.875rem; height: 0.875rem; background: #34d399; border: 2px solid #111827; border-radius: 9999px;"></span>
                        </div>
                        <div style="min-width: 0;">
                            @if(isset($children) && $children->count() > 1)
                            <div style="position: relative; display: inline-block;">
                                <select onchange="window.location.href='{{ route('home') }}?child_id=' + this.value"
                                        style="background: rgba(255, 255, 255, 0.15); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3); font-size: 0.9375rem; font-weight: 800; border-radius: 0.5rem; padding: 0.2rem 1.75rem 0.2rem 0.5rem; cursor: pointer; appearance: none;">
                                    @foreach($children as $c)
                                        <option value="{{ $c->id }}" {{ $cd->child->id == $c->id ? 'selected' : '' }} style="color: #0f172a; font-weight: 700;">
                                            {{ $c->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="bi bi-chevron-down" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #ffffff; pointer-events: none;"></i>
                            </div>
                            @else
                            <h2 style="color: #ffffff; font-size: 1.0625rem; font-weight: 800; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $cd->child->full_name }}</h2>
                            @endif
                            <p style="color: #9ca3af; font-size: 0.8125rem; margin: 0.2rem 0 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $className }} • {{ $sectionName }}
                            </p>
                            <p style="color: #6b7280; font-size: 0.75rem; margin: 0.1rem 0 0 0;">Roll: {{ $cd->child->roll_no ?? 'N/A' }}</p>
                        </div>
                    </div>

                    {{-- Today's Status --}}
                    <div style="text-align: right; flex-shrink: 0;">
                        <span style="color: #9ca3af; font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.25rem;">TODAY'S STATUS</span>
                        <span style="background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 0.25rem 0.875rem; border-radius: 9999px; font-weight: 800; font-size: 0.75rem; display: inline-block;">
                            {{ $cd->todayStatus }}
                        </span>
                        <span style="color: #6b7280; font-size: 0.6875rem; display: block; margin-top: 0.25rem;">Last updated: {{ $cd->todayCheckIn }}</span>
                    </div>
                </div>

                {{-- Right Translucent Info Chips --}}
                <div class="parent-chips-grid" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 1.25rem; padding: 1rem;">

                    {{-- Class Teacher --}}
                    <div style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 0.875rem; padding: 0.75rem; display: flex; align-items: center; gap: 0.625rem;">
                        <div style="width: 2rem; height: 2rem; border-radius: 0.5rem; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; color: #ffffff; flex-shrink: 0;">
                            <i class="bi bi-person-badge" style="font-size: 0.875rem;"></i>
                        </div>
                        <div style="min-width: 0;">
                            <p style="color: #bfdbfe; font-size: 0.625rem; font-weight: 700; text-transform: uppercase; margin: 0; letter-spacing: 0.03em;">CLASS TEACHER</p>
                            <p style="color: #ffffff; font-size: 0.8125rem; font-weight: 800; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $cd->classTeacher }}</p>
                        </div>
                    </div>

                    {{-- School --}}
                    <div style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 0.875rem; padding: 0.75rem; display: flex; align-items: center; gap: 0.625rem;">
                        <div style="width: 2rem; height: 2rem; border-radius: 0.5rem; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; color: #ffffff; flex-shrink: 0;">
                            <i class="bi bi-building" style="font-size: 0.875rem;"></i>
                        </div>
                        <div style="min-width: 0;">
                            <p style="color: #bfdbfe; font-size: 0.625rem; font-weight: 700; text-transform: uppercase; margin: 0; letter-spacing: 0.03em;">SCHOOL</p>
                            <p style="color: #ffffff; font-size: 0.8125rem; font-weight: 800; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $cd->schoolName }}</p>
                        </div>
                    </div>

                    {{-- Academic Year --}}
                    <div style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 0.875rem; padding: 0.75rem; display: flex; align-items: center; gap: 0.625rem;">
                        <div style="width: 2rem; height: 2rem; border-radius: 0.5rem; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; color: #ffffff; flex-shrink: 0;">
                            <i class="bi bi-calendar-range" style="font-size: 0.875rem;"></i>
                        </div>
                        <div style="min-width: 0;">
                            <p style="color: #bfdbfe; font-size: 0.625rem; font-weight: 700; text-transform: uppercase; margin: 0; letter-spacing: 0.03em;">ACADEMIC YEAR</p>
                            <p style="color: #ffffff; font-size: 0.8125rem; font-weight: 800; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $cd->sessionName }}</p>
                        </div>
                    </div>

                    {{-- Student ID --}}
                    <div style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 0.875rem; padding: 0.75rem; display: flex; align-items: center; gap: 0.625rem;">
                        <div style="width: 2rem; height: 2rem; border-radius: 0.5rem; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; color: #ffffff; flex-shrink: 0;">
                            <i class="bi bi-card-heading" style="font-size: 0.875rem;"></i>
                        </div>
                        <div style="min-width: 0;">
                            <p style="color: #bfdbfe; font-size: 0.625rem; font-weight: 700; text-transform: uppercase; margin: 0; letter-spacing: 0.03em;">STUDENT ID</p>
                            <p style="color: #ffffff; font-size: 0.8125rem; font-weight: 800; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $cd->studentIdCode }}</p>
                        </div>
                    </div>

                </div>

            </div>
            @endif

        </div>
    </div>

    {{-- ── MAIN CONTENT CONTAINER ───────────────────────────────────────────── --}}
    <div class="parent-content-container" style="max-width: 80rem; margin: -4.5rem auto 0 auto; padding: 0 2rem; position: relative; z-index: 10;">

        @if($cd = $childData->first())
        @php $childId = $cd->child->id ?? 1; @endphp

        {{-- ── 2. FIVE METRIC CARDS ROW ───────────────────────────────────────── --}}
        <div class="parent-stat-grid">

            {{-- Card 1: Attendance --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <span style="color: #64748b; font-size: 0.8125rem; font-weight: 700;">Attendance</span>
                        <div style="color: #0f172a; font-size: 1.75rem; font-weight: 900; margin-top: 0.25rem; line-height: 1;">{{ $cd->pct }}%</div>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.125rem; flex-shrink: 0;">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                </div>
                <div style="margin-top: 1rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; color: #64748b;">
                        <span>{{ $cd->present }} / {{ $cd->total > 0 ? $cd->total : 22 }} days</span>
                        <span style="color: #059669; font-weight: 800;">↑ 5% this month</span>
                    </div>
                    <div style="height: 0.375rem; width: 100%; background: #e2e8f0; border-radius: 9999px; overflow: hidden; margin-top: 0.5rem;">
                        <div style="height: 100%; width: {{ $cd->pct }}%; background: #10b981; border-radius: 9999px;"></div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Unpaid Fees --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <span style="color: #64748b; font-size: 0.8125rem; font-weight: 700;">Unpaid Fees</span>
                        <div style="color: #0f172a; font-size: 1.75rem; font-weight: 900; margin-top: 0.25rem; line-height: 1;">₹{{ number_format($cd->unpaidAmount) }}</div>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #ffe4e6; color: #f43f5e; display: flex; align-items: center; justify-content: center; font-size: 1.125rem; flex-shrink: 0;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; margin-top: 1rem;">
                    <span style="color: #64748b;">{{ $cd->invoices }} invoice pending</span>
                    <a href="{{ route('parent.fees', ['studentId' => $childId]) }}" style="color: #f43f5e; font-weight: 800; text-decoration: none;">
                        Pay now →
                    </a>
                </div>
            </div>

            {{-- Card 3: Recent Results --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <span style="color: #64748b; font-size: 0.8125rem; font-weight: 700;">Recent Results</span>
                        <div style="color: #0f172a; font-size: 1.75rem; font-weight: 900; margin-top: 0.25rem; line-height: 1;">5</div>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #dbeafe; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.125rem; flex-shrink: 0;">
                        <i class="bi bi-award-fill"></i>
                    </div>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; margin-top: 1rem;">
                    <span style="color: #64748b;">Tests Completed</span>
                    <a href="{{ route('parent.results', ['studentId' => $childId]) }}" style="color: #2563eb; font-weight: 800; text-decoration: none;">
                        View marks →
                    </a>
                </div>
            </div>

            {{-- Card 4: Homework Due --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <span style="color: #64748b; font-size: 0.8125rem; font-weight: 700;">Homework Due</span>
                        <div style="color: #0f172a; font-size: 1.75rem; font-weight: 900; margin-top: 0.25rem; line-height: 1;">{{ $cd->homeworkDue }}</div>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.125rem; flex-shrink: 0;">
                        <i class="bi bi-pencil-fill"></i>
                    </div>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; margin-top: 1rem;">
                    <span style="color: #64748b;">All submitted</span>
                    <a href="{{ route('parent.homework', ['studentId' => $childId]) }}" style="color: #059669; font-weight: 800; text-decoration: none;">
                        Great job! 🎉
                    </a>
                </div>
            </div>

            {{-- Card 5: Exams Soon --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <span style="color: #64748b; font-size: 0.8125rem; font-weight: 700;">Exams Soon</span>
                        <div style="color: #0f172a; font-size: 1.75rem; font-weight: 900; margin-top: 0.25rem; line-height: 1;">{{ $cd->upcomingExams->count() }}</div>
                    </div>
                    <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #f3e8ff; color: #9333ea; display: flex; align-items: center; justify-content: center; font-size: 1.125rem; flex-shrink: 0;">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; margin-top: 1rem;">
                    <span style="color: #94a3b8;">No upcoming exams</span>
                    <a href="{{ route('parent.timetable', ['studentId' => $childId]) }}" style="color: #9333ea; font-weight: 800; text-decoration: none;">
                        View timetable →
                    </a>
                </div>
            </div>

        </div>

        {{-- ── 3. MIDDLE ROW: ATTENDANCE DONUT + PERFORMANCE TREND + TODAY'S TIMETABLE ── --}}
        <div class="parent-middle-grid">

            {{-- 3.1 Attendance Overview (Donut Chart Left, Legend Right) --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <h3 style="color: #0f172a; font-size: 0.9375rem; font-weight: 800; margin: 0;">Attendance Overview</h3>
                    <span style="background: #f1f5f9; color: #64748b; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.625rem; border-radius: 0.5rem;">This Month ▾</span>
                </div>

                {{-- Side-by-Side Donut & Legend Container --}}
                <div class="parent-donut-container">
                    {{-- Left Donut Chart --}}
                    <div style="position: relative; width: 8.5rem; height: 8.5rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                            <path stroke="#e2e8f0" stroke-width="3.8" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path stroke="#10b981" stroke-width="3.8" stroke-dasharray="92, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path stroke="#ef4444" stroke-width="3.8" stroke-dasharray="4, 100" stroke-dashoffset="-92" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path stroke="#f59e0b" stroke-width="3.8" stroke-dasharray="4, 100" stroke-dashoffset="-96" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <div style="position: absolute; text-align: center;">
                            <span style="color: #0f172a; font-size: 1.5rem; font-weight: 900; display: block; line-height: 1;">92%</span>
                            <span style="color: #94a3b8; font-size: 0.625rem; font-weight: 800; text-transform: uppercase; display: block; margin-top: 0.1rem;">Present</span>
                        </div>
                    </div>

                    {{-- Right Legend List --}}
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; flex-grow: 1;">
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem;">
                            <span style="display: flex; align-items: center; gap: 0.4rem; color: #475569;">
                                <span style="width: 0.625rem; height: 0.625rem; border-radius: 9999px; background: #10b981;"></span> Present
                            </span>
                            <span style="font-weight: 800; color: #0f172a;">22 days (92%)</span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem;">
                            <span style="display: flex; align-items: center; gap: 0.4rem; color: #475569;">
                                <span style="width: 0.625rem; height: 0.625rem; border-radius: 9999px; background: #ef4444;"></span> Absent
                            </span>
                            <span style="font-weight: 800; color: #0f172a;">{{ $cd->absent }} day{{ $cd->absent != 1 ? 's' : '' }} ({{ $cd->total > 0 ? round(($cd->absent/$cd->total)*100) : 0 }}%)</span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem;">
                            <span style="display: flex; align-items: center; gap: 0.4rem; color: #475569;">
                                <span style="width: 0.625rem; height: 0.625rem; border-radius: 9999px; background: #f59e0b;"></span> Leave
                            </span>
                            <span style="font-weight: 800; color: #0f172a;">{{ $cd->leave }} day{{ $cd->leave != 1 ? 's' : '' }} ({{ $cd->total > 0 ? round(($cd->leave/$cd->total)*100) : 0 }}%)</span>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; border-top: 1px solid #f1f5f9; padding-top: 0.75rem;">
                    <a href="{{ route('parent.attendance', ['studentId' => $childId]) }}" style="color: #2563eb; font-size: 0.8125rem; font-weight: 800; text-decoration: none;">
                        View full attendance →
                    </a>
                </div>
            </div>

            {{-- 3.2 Performance Trend (Spline Line Chart) --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                @php
                    $marksList = $cd->marks;
                    $avgScore  = $marksList->count() > 0 ? round($marksList->avg('marks'), 1) : null;
                @endphp
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <h3 style="color: #0f172a; font-size: 0.9375rem; font-weight: 800; margin: 0;">Performance Trend</h3>
                    @if($avgScore !== null)
                    <span style="background: #4338ca; color: #ffffff; font-size: 0.75rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 0.5rem;">
                        Avg: {{ $avgScore }} marks
                    </span>
                    @endif
                </div>

                <div style="margin-top: 1rem;">
                    @if($marksList->count())
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        @foreach($marksList->take(5) as $mark)
                        @php
                            $examMax  = optional($mark->exam)->total_marks ?? 100;
                            $scorePct = $examMax > 0 ? min(100, round(($mark->marks / $examMax) * 100)) : 0;
                            $barColor = $scorePct >= 80 ? '#10b981' : ($scorePct >= 60 ? '#6366f1' : ($scorePct >= 40 ? '#f59e0b' : '#ef4444'));
                        @endphp
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #475569; margin-bottom: 0.25rem;">
                                <span style="truncate; max-width: 70%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $mark->exam?->exam_name ?? 'Exam' }}
                                    @if($mark->course) <span style="color:#94a3b8;">&middot; {{ $mark->course->course_name }}</span> @endif
                                </span>
                                <span style="font-weight: 800; color: #0f172a;">{{ $mark->marks }}/{{ $examMax }}</span>
                            </div>
                            <div style="height: 0.375rem; background: #f1f5f9; border-radius: 9999px; overflow: hidden;">
                                <div style="height: 100%; width: {{ $scorePct }}%; background: {{ $barColor }}; border-radius: 9999px; transition: width 0.6s ease;"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p style="color: #94a3b8; font-size: 0.8125rem; text-align: center; padding: 1.5rem 0;">No marks recorded yet.</p>
                    @endif
                </div>

                <div style="text-align: center; border-top: 1px solid #f1f5f9; padding-top: 0.75rem; margin-top: 0.75rem;">
                    <a href="{{ route('parent.results', ['studentId' => $childId]) }}" style="color: #2563eb; font-size: 0.8125rem; font-weight: 800; text-decoration: none;">
                        View performance details →
                    </a>
                </div>
            </div>

            {{-- 3.3 Today's Timetable --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <h3 style="color: #0f172a; font-size: 0.9375rem; font-weight: 800; margin: 0;">Today's Timetable</h3>
                    <a href="{{ route('parent.timetable', ['studentId' => $childId]) }}" style="color: #2563eb; font-size: 0.6875rem; font-weight: 800; text-decoration: none;">View full timetable →</a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem;">
                    @foreach([
                        ['period' => '1', 'subject' => 'English',      'time' => '8:00 AM – 8:45 AM'],
                        ['period' => '2', 'subject' => 'Mathematics',  'time' => '8:45 AM – 9:30 AM'],
                        ['period' => '3', 'subject' => 'EVS',          'time' => '9:45 AM – 10:30 AM'],
                        ['period' => '4', 'subject' => 'Art & Craft',  'time' => '11:15 AM – 12:00 PM'],
                        ['period' => '5', 'subject' => 'Story Time',   'time' => '1:00 PM – 1:45 PM'],
                    ] as $item)
                    <div style="background: #f8fafc; border-radius: 0.75rem; padding: 0.625rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.625rem; min-width: 0;">
                            <span style="width: 1.25rem; height: 1.25rem; border-radius: 0.375rem; background: #e2e8f0; color: #475569; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 0.6875rem; flex-shrink: 0;">
                                {{ $item['period'] }}
                            </span>
                            <span style="font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item['subject'] }}</span>
                        </div>
                        <span style="color: #94a3b8; font-size: 0.6875rem; font-weight: 500; flex-shrink: 0;">{{ $item['time'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ── 4. BOTTOM ROW: RECENT RESULTS TABLE + IMPORTANT REMINDERS ──────────── --}}
        <div class="parent-bottom-grid">

            {{-- Recent Results Table --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <h3 style="color: #0f172a; font-size: 0.9375rem; font-weight: 800; margin: 0;">Recent Results</h3>
                    <a href="{{ route('parent.results', ['studentId' => $childId]) }}" style="color: #2563eb; font-size: 0.8125rem; font-weight: 800; text-decoration: none;">View all results →</a>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.75rem; min-width: 550px;">
                        <thead>
                            <tr style="border-bottom: 1px solid #f1f5f9; color: #94a3b8; text-transform: uppercase; font-weight: 800; font-size: 0.625rem; letter-spacing: 0.05em;">
                                <th style="padding: 0.75rem;">Exam</th>
                                <th style="padding: 0.75rem;">Subject</th>
                                <th style="padding: 0.75rem;">Marks Obtained</th>
                                <th style="padding: 0.75rem;">Date</th>
                            </tr>
                        </thead>
                        <tbody style="color: #334155; font-weight: 500;">
                            @forelse($cd->marks->take(5) as $mark)
                            @php
                                $examMax = optional($mark->exam)->total_marks ?? 100;
                                $pct     = $examMax > 0 ? round(($mark->marks / $examMax) * 100) : 0;
                                $badge   = $pct >= 80
                                    ? 'background:#d1fae5;color:#047857'
                                    : ($pct >= 60 ? 'background:#fef3c7;color:#b45309' : 'background:#fee2e2;color:#b91c1c');
                            @endphp
                            <tr style="border-bottom: 1px solid #f8fafc;">
                                <td style="padding: 0.875rem 0.75rem; font-weight: 700; color: #0f172a;">{{ $mark->exam?->exam_name ?? '—' }}</td>
                                <td style="padding: 0.875rem 0.75rem;">{{ $mark->course?->course_name ?? '—' }}</td>
                                <td style="padding: 0.875rem 0.75rem; font-weight: 800;">
                                    {{ $mark->marks }}
                                    <span style="font-size:0.6875rem;{{ $badge }};padding:0.15rem 0.5rem;border-radius:9999px;font-weight:800;margin-left:0.25rem;">{{ $pct }}%</span>
                                </td>
                                <td style="padding: 0.875rem 0.75rem; color: #94a3b8;">
                                    {{ $mark->created_at ? $mark->created_at->format('M d, Y') : '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="padding: 2rem; text-align: center; color: #94a3b8; font-size: 0.8125rem;">
                                    No results recorded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Important Reminders Card --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 1rem;">
                <h3 style="color: #0f172a; font-size: 0.9375rem; font-weight: 800; margin: 0;">Important Reminders</h3>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">

                    {{-- Fee Payment Due (real data) --}}
                    @if($cd->invoices > 0)
                    <div style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 1rem; padding: 1rem; display: flex; align-items: flex-start; gap: 0.875rem;">
                        <div style="width: 2.25rem; height: 2.25rem; border-radius: 0.75rem; background: #fecdd3; color: #e11d48; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                            <i class="bi bi-receipt-cutoff"></i>
                        </div>
                        <div style="flex-grow: 1;">
                            <h4 style="color: #0f172a; font-size: 0.8125rem; font-weight: 800; margin: 0;">Fee Payment Due</h4>
                            <p style="color: #64748b; font-size: 0.75rem; margin: 0.2rem 0 0 0;">
                                NPR {{ number_format($cd->unpaidAmount, 2) }} outstanding
                                ({{ $cd->invoices }} {{ Str::plural('invoice', $cd->invoices) }})
                            </p>
                            <a href="{{ route('parent.fees', ['studentId' => $childId]) }}" style="color: #e11d48; font-size: 0.75rem; font-weight: 800; text-decoration: none; display: inline-block; margin-top: 0.25rem;">
                                Pay now →
                            </a>
                        </div>
                    </div>
                    @else
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 1rem; padding: 1rem; display: flex; align-items: flex-start; gap: 0.875rem;">
                        <div style="width: 2.25rem; height: 2.25rem; border-radius: 0.75rem; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div style="flex-grow: 1;">
                            <h4 style="color: #0f172a; font-size: 0.8125rem; font-weight: 800; margin: 0;">Fees Up to Date</h4>
                            <p style="color: #64748b; font-size: 0.75rem; margin: 0.2rem 0 0 0;">No outstanding fee dues. Great!</p>
                        </div>
                    </div>
                    @endif

                    {{-- Upcoming Exam Reminder (real data) --}}
                    @if($cd->upcomingExams->count())
                    @php $nextExam = $cd->upcomingExams->first(); @endphp
                    <div style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 1rem; padding: 1rem; display: flex; align-items: flex-start; gap: 0.875rem;">
                        <div style="width: 2.25rem; height: 2.25rem; border-radius: 0.75rem; background: #ede9fe; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <div style="flex-grow: 1;">
                            <h4 style="color: #0f172a; font-size: 0.8125rem; font-weight: 800; margin: 0;">Upcoming Exam</h4>
                            <p style="color: #64748b; font-size: 0.75rem; margin: 0.2rem 0 0 0;">
                                {{ $nextExam->exam_name }} on {{ \Carbon\Carbon::parse($nextExam->start_date)->format('M d, Y') }}
                            </p>
                            <a href="{{ route('parent.timetable', ['studentId' => $childId]) }}" style="color: #7c3aed; font-size: 0.75rem; font-weight: 800; text-decoration: none; display: inline-block; margin-top: 0.25rem;">
                                View timetable →
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Homework due reminder (real data) --}}
                    @if($cd->homeworkDue > 0)
                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 1rem; padding: 1rem; display: flex; align-items: flex-start; gap: 0.875rem;">
                        <div style="width: 2.25rem; height: 2.25rem; border-radius: 0.75rem; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                            <i class="bi bi-pencil-fill"></i>
                        </div>
                        <div style="flex-grow: 1;">
                            <h4 style="color: #0f172a; font-size: 0.8125rem; font-weight: 800; margin: 0;">Homework Pending</h4>
                            <p style="color: #64748b; font-size: 0.75rem; margin: 0.2rem 0 0 0;">
                                {{ $cd->homeworkDue }} {{ Str::plural('assignment', $cd->homeworkDue) }} still due.
                            </p>
                            <a href="{{ route('parent.homework', ['studentId' => $childId]) }}" style="color: #d97706; font-size: 0.75rem; font-weight: 800; text-decoration: none; display: inline-block; margin-top: 0.25rem;">
                                View homework →
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Notices from school --}}
                    @php try { $latestNotice = $notices->first(); } catch(\Throwable $e) { $latestNotice = null; } @endphp
                    @if($latestNotice)
                    <div style="background: #eff6ff; border: 1px solid #dbeafe; border-radius: 1rem; padding: 1rem; display: flex; align-items: flex-start; gap: 0.875rem;">
                        <div style="width: 2.25rem; height: 2.25rem; border-radius: 0.75rem; background: #bfdbfe; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                            <i class="bi bi-megaphone-fill"></i>
                        </div>
                        <div style="flex-grow: 1; min-width: 0;">
                            <h4 style="color: #0f172a; font-size: 0.8125rem; font-weight: 800; margin: 0;">School Notice</h4>
                            <p style="color: #64748b; font-size: 0.75rem; margin: 0.2rem 0 0 0; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                {!! \Stevebauman\Purify\Facades\Purify::clean(strip_tags($latestNotice->notice)) !!}
                            </p>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

        </div>
        @endif

    </div>

    {{-- Floating Purple Action Plus Button --}}
    <a href="{{ route('parent.conversation.new') }}"
       style="position: fixed; bottom: 1.5rem; right: 1.5rem; width: 3.25rem; height: 3.25rem; background: #6366f1; color: #ffffff; border-radius: 9999px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.5); z-index: 9999; text-decoration: none;"
       title="Start Conversation">
        <i class="bi bi-plus-lg"></i>
    </a>

</div>
@endsection
