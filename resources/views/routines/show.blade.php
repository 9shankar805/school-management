@extends('layouts.app')

@section('content')
<style>
    /* ── Schedule List Mode Styling ── */
    .nsh-tt-wrapper {
        background: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
        border-radius: 16px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        overflow: hidden !important;
    }
    .nsh-day-group-cell {
        background-color: #FFFFFF !important;
        border-left: 4px solid #6366F1 !important;
        border-right: 1px solid #E2E8F0 !important;
        border-bottom: 1px solid #E2E8F0 !important;
        vertical-align: top !important;
        padding: 20px 16px !important;
        width: 140px !important;
        min-width: 140px !important;
    }
    .nsh-day-pill {
        background-color: #EDE9FE !important;
        color: #6366F1 !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        padding: 4px 12px !important;
        border-radius: 9999px !important;
        display: inline-block !important;
        line-height: 1.2 !important;
    }
    .nsh-day-count {
        color: #94A3B8 !important;
        font-size: 12px !important;
        margin-top: 6px !important;
        font-weight: 500 !important;
    }
    .nsh-tt-th {
        background-color: #F8FAFC !important;
        color: #334155 !important;
        font-weight: 700 !important;
        font-size: 12.5px !important;
        padding: 14px 18px !important;
        border-bottom: 1px solid #E2E8F0 !important;
        text-align: left !important;
    }
    .nsh-tt-row {
        border-bottom: 1px solid #E2E8F0 !important;
        transition: background-color 0.15s ease !important;
    }
    .nsh-tt-row:hover {
        background-color: #F8FAFC !important;
    }
    .nsh-tt-row-start-day {
        border-top: 1.5px solid #CBD5E1 !important;
    }
    .nsh-tt-td {
        padding: 13px 18px !important;
        vertical-align: middle !important;
        font-size: 13px !important;
    }
    .nsh-time-text {
        color: #475569 !important;
        font-weight: 500 !important;
        font-size: 13px !important;
        white-space: nowrap !important;
    }
    .nsh-subject-title {
        color: #1E293B !important;
        font-weight: 600 !important;
        font-size: 13.5px !important;
    }
    .nsh-teacher-text {
        color: #334155 !important;
        font-weight: 500 !important;
        font-size: 13px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    /* ── Grid Matrix Mode Styling ── */
    .nsh-grid-card {
        background: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
        overflow: hidden !important;
    }
    .nsh-grid-th {
        background-color: #374B5E !important;
        color: #FFFFFF !important;
        border-right: 1px solid #485D71 !important;
        border-bottom: 1px solid #485D71 !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        text-transform: capitalize !important;
        letter-spacing: 0.02em !important;
        padding: 12px 10px !important;
        text-align: center !important;
        vertical-align: middle !important;
        -webkit-print-color-adjust: exact !important;
    }
    .nsh-grid-th:first-child {
        width: 130px !important;
        min-width: 130px !important;
    }
    .nsh-grid-row-header {
        background-color: #374B5E !important;
        color: #FFFFFF !important;
        border-right: 1px solid #485D71 !important;
        border-bottom: 1px solid #485D71 !important;
        padding: 12px 8px !important;
        text-align: center !important;
        vertical-align: middle !important;
        width: 130px !important;
        min-width: 130px !important;
        user-select: none !important;
        -webkit-print-color-adjust: exact !important;
    }
    .nsh-grid-grade-badge {
        font-size: 14px !important;
        font-weight: 800 !important;
        color: #FFFFFF !important;
        line-height: 1.2 !important;
        letter-spacing: 0.03em !important;
        margin-bottom: 2px !important;
    }
    .nsh-grid-time-badge {
        font-size: 11px !important;
        font-weight: 400 !important;
        color: #CBD5E1 !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        letter-spacing: -0.02em !important;
    }
    .nsh-grid-cell {
        background-color: #FFFFFF !important;
        border-right: 1px solid #E2E8F0 !important;
        border-bottom: 1px solid #E2E8F0 !important;
        padding: 12px 8px !important;
        text-align: center !important;
        vertical-align: middle !important;
        min-width: 130px !important;
        transition: background-color 0.15s ease !important;
        -webkit-print-color-adjust: exact !important;
    }
    .nsh-grid-cell:hover {
        background-color: #F8FAFC !important;
    }
    .nsh-grid-cell:last-child {
        border-right: none !important;
    }
    .nsh-grid-subject-name {
        font-size: 11px !important;
        font-weight: 800 !important;
        color: #1E293B !important;
        text-transform: uppercase !important;
        letter-spacing: -0.01em !important;
        line-height: 1.25 !important;
        margin-top: 6px !important;
        margin-bottom: 3px !important;
        display: block !important;
    }
    .nsh-grid-teacher-name {
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #047857 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        line-height: 1.2 !important;
    }
    .nsh-grid-teacher-name i {
        font-size: 10px !important;
        color: #059669 !important;
    }

    /* ── Rapid Matrix Editor Styling ── */
    .nsh-matrix-card {
        background: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;
        overflow: hidden !important;
    }
    .nsh-matrix-th {
        background-color: #1E293B !important;
        color: #F8FAFC !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        letter-spacing: 0.02em !important;
        padding: 12px 10px !important;
        text-align: center !important;
        border-right: 1px solid #334155 !important;
        border-bottom: 2px solid #0F172A !important;
        user-select: none !important;
    }
    .nsh-matrix-th-period {
        background-color: #0F172A !important;
        width: 155px !important;
        min-width: 155px !important;
        text-align: left !important;
    }
    .nsh-matrix-row-head {
        background-color: #F8FAFC !important;
        border-right: 1px solid #E2E8F0 !important;
        border-bottom: 1px solid #E2E8F0 !important;
        padding: 10px 12px !important;
        vertical-align: middle !important;
        width: 155px !important;
        min-width: 155px !important;
    }
    .nsh-matrix-cell {
        background-color: #FFFFFF !important;
        border-right: 1px solid #E2E8F0 !important;
        border-bottom: 1px solid #E2E8F0 !important;
        padding: 8px !important;
        vertical-align: top !important;
        min-width: 165px !important;
        transition: all 0.15s ease !important;
        position: relative !important;
    }
    .nsh-matrix-cell:hover {
        background-color: #F8FAFC !important;
    }
    .nsh-matrix-cell.has-subject {
        background-color: #FAFAFE !important;
    }
    .nsh-matrix-cell.has-conflict {
        background-color: #FEF2F2 !important;
        border-color: #FCA5A5 !important;
    }
    .nsh-break-row {
        background: linear-gradient(90deg, #FFFBEB 0%, #FEF3C7 50%, #FFFBEB 100%) !important;
        border-top: 1.5px dashed #F59E0B !important;
        border-bottom: 1.5px dashed #F59E0B !important;
    }
    .nsh-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        transition: all 0.25s ease;
        transform: translateY(100px);
        opacity: 0;
    }
    .nsh-toast.show {
        transform: translateY(0);
        opacity: 1;
    }

    /* ── High-Quality Print Stylesheet ── */
    @media print {
        @page {
            size: A4 landscape;
            margin: 5mm 6mm !important;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
            box-sizing: border-box !important;
        }
        body, html, main, .flex-1, .app-layout, .container, .content, .min-h-screen {
            background: #FFFFFF !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            overflow: visible !important;
        }
        header, nav, aside, footer, 
        .app-topbar, .left-menu, .sidebar, #left-sidebar,
        #mobile-bottom-nav, .mobile-bottom-nav, #mobile-bottom-sheet, 
        #fab-group, #fab-main, #fab-actions, 
        button, .btn, .print-hide, #filterForm, 
        .nsh-tt-top-bar, .nsh-tt-actions, #addSlotModal,
        #bottom-nav-customizer-modal, #erp-loader, #quick-search-modal, #command-palette-modal {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            opacity: 0 !important;
        }

        .print-only {
            display: block !important;
            padding: 6px 10px !important;
            border-bottom: 2px solid #374B5E !important;
            margin-bottom: 6px !important;
        }

        .nsh-tt-wrapper, .nsh-grid-card {
            border: 1px solid #E2E8F0 !important;
            box-shadow: none !important;
            border-radius: 6px !important;
            width: 100% !important;
            page-break-inside: avoid !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
        }

        .nsh-grid-th, .nsh-grid-row-header {
            background-color: #374B5E !important;
            color: #FFFFFF !important;
            -webkit-print-color-adjust: exact !important;
        }

        .actions-col {
            display: none !important;
        }
    }
</style>

<div class="flex-1 p-4 lg:p-7 overflow-auto bg-slate-50/50 min-h-screen">

    {{-- Main Header matching screenshot --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        
        {{-- Title + Icon + Subtitle --}}
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="bi bi-calendar3 text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight leading-none">
                    Class Timetable
                </h1>
                <p class="text-xs text-slate-400 mt-1.5">
                    Manage and view class schedules for all grades and sections.
                </p>
            </div>
        </div>

        {{-- Action Buttons & Mode Switcher --}}
        <div class="flex flex-wrap items-center gap-2.5 print-hide">
            
            {{-- Mode Switcher (Rapid Matrix vs Grid vs Schedule) --}}
            <div class="inline-flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200 shadow-2xs">
                <button type="button" id="btnModeMatrix" onclick="switchTimetableMode('matrix')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition bg-white text-indigo-600 shadow-2xs">
                    <i class="bi bi-lightning-charge-fill text-amber-500 text-sm"></i> Rapid Matrix
                </button>
                <button type="button" id="btnModeGrid" onclick="switchTimetableMode('grid')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900">
                    <i class="bi bi-grid-3x3-gap-fill text-sm"></i> Grid View
                </button>
                <button type="button" id="btnModeSchedule" onclick="switchTimetableMode('schedule')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900">
                    <i class="bi bi-view-list text-sm"></i> Day List
                </button>
            </div>

            {{-- Print Button --}}
            <button onclick="window.print()" type="button" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition border border-slate-200 shadow-2xs">
                <i class="bi bi-printer"></i> Print
            </button>

            {{-- Add Slot Button --}}
            @can('create routines')
            <button type="button" onclick="openAddSlotModal()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                <i class="bi bi-plus-lg"></i> Add Slot
            </button>
            @endcan

        </div>

    </div>

    @include('session-messages')

    {{-- Filter Bar matching screenshot --}}
    <form method="GET" action="{{ route('section.routine.show') }}" id="filterForm" 
          class="flex flex-wrap items-center gap-3 mb-6 print-hide">
        
        {{-- Grade / Class Dropdown with Graduation Cap Icon --}}
        <div class="relative min-w-[170px]">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="bi bi-mortarboard text-sm"></i>
            </div>
            <select name="class_id" id="class_select" required onchange="loadSections(this.value)"
                    class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-8 py-2.5 text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition appearance-none shadow-2xs">
                <option value="">Select Grade…</option>
                @foreach($classes as $cls)
                <option value="{{ $cls->id }}" {{ ($class_id ?? 0) == $cls->id ? 'selected' : '' }}>
                    {{ $cls->class_name }}
                </option>
                @endforeach
            </select>
            <i class="bi bi-chevron-down absolute right-3 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
        </div>

        {{-- Section Dropdown with Users Icon --}}
        <div class="relative min-w-[170px]">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="bi bi-people text-sm"></i>
            </div>
            <select name="section_id" id="section_select" onchange="document.getElementById('filterForm').submit()"
                    class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-8 py-2.5 text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition appearance-none shadow-2xs">
                <option value="">Select Section…</option>
            </select>
            <i class="bi bi-chevron-down absolute right-3 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
        </div>

        {{-- View Button --}}
        <button type="submit" 
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition flex items-center justify-center gap-2 shadow-xs">
            <i class="bi bi-search"></i> View
        </button>

    </form>

    @php
        // Prepare Day Schedule data matching the reference image exactly
        $dayNames = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ];

        // Reference dataset from screenshot
        $referenceDays = [
            1 => [
                'name' => 'Monday',
                'slots' => [
                    ['time' => '08:30 – 09:15', 'subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                    ['time' => '09:20 – 10:05', 'subject' => 'English Literature', 'teacher' => 'Ananya Verma'],
                    ['time' => '10:10 – 10:55', 'subject' => 'Science', 'teacher' => 'Bikram Thapa'],
                    ['time' => '11:15 – 12:00', 'subject' => 'Nepali', 'teacher' => 'Sunita Adhikari'],
                    ['time' => '12:05 – 12:50', 'subject' => 'Social Studies', 'teacher' => 'David Miller'],
                    ['time' => '13:30 – 14:15', 'subject' => 'Computer Science', 'teacher' => 'Priyanka Gupta'],
                    ['time' => '14:20 – 15:05', 'subject' => 'Physical Education', 'teacher' => 'Sanjay Karki'],
                ]
            ],
            2 => [
                'name' => 'Tuesday',
                'slots' => [
                    ['time' => '08:30 – 09:15', 'subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                    ['time' => '09:20 – 10:05', 'subject' => 'English Literature', 'teacher' => 'Ananya Verma'],
                    ['time' => '10:10 – 10:55', 'subject' => 'Science', 'teacher' => 'Bikram Thapa'],
                ]
            ]
        ];

        // 7-Period standard definitions for Grid Matrix
        $defaultPeriods = [
            ['start' => '08:30', 'end' => '09:15', 'label' => '08:30 - 09:15'],
            ['start' => '09:20', 'end' => '10:05', 'label' => '09:20 - 10:05'],
            ['start' => '10:10', 'end' => '10:55', 'label' => '10:10 - 10:55'],
            ['start' => '11:15', 'end' => '12:00', 'label' => '11:15 - 12:00'],
            ['start' => '12:05', 'end' => '12:50', 'label' => '12:05 - 12:50'],
            ['start' => '13:30', 'end' => '14:15', 'label' => '13:30 - 14:15'],
            ['start' => '14:20', 'end' => '15:05', 'label' => '14:20 - 15:05'],
        ];

        // Format actual database slots if available
        $displayGroups = [];
        $totalSlotCount = 0;
        $dbSlots = $rawRoutines ?? collect();

        if (isset($routines) && $routines->isNotEmpty()) {
            foreach ($routines as $dayNum => $daySlots) {
                $dName = $dayNames[$dayNum] ?? ('Day ' . $dayNum);
                $slotList = [];
                foreach ($daySlots->sortBy('start') as $slot) {
                    $slotList[] = [
                        'id'      => $slot->id,
                        'time'    => $slot->start . ' – ' . $slot->end,
                        'subject' => $slot->course->course_name ?? 'Subject',
                        'teacher' => $slot->teacher->full_name ?? ($slot->teacher->name ?? 'Faculty'),
                        'room'    => $slot->room,
                    ];
                    $totalSlotCount++;
                }
                $displayGroups[$dayNum] = [
                    'name'  => $dName,
                    'slots' => $slotList
                ];
            }
        }

        if (empty($displayGroups)) {
            $displayGroups = $referenceDays;
            $totalSlotCount = 10;
        }

        // Build Periods list for Grid Mode
        $periods = [];
        if ($dbSlots->isNotEmpty()) {
            $uniqueTimes = $dbSlots->groupBy(function($item) {
                return $item->start . ' - ' . $item->end;
            })->sortKeys();

            foreach ($uniqueTimes as $timeKey => $items) {
                $first = $items->first();
                $periods[] = [
                    'start' => $first->start,
                    'end'   => $first->end,
                    'label' => $timeKey,
                ];
            }
        }
        if (empty($periods)) {
            $periods = $defaultPeriods;
        }

        $columnHeaders = [
            1 => 'Monday 1',
            2 => 'Monday 2',
            3 => 'Monday 3',
            4 => 'Monday 4',
            5 => 'Monday 5',
            6 => 'Monday 6',
            7 => 'Monday 7',
        ];

        $sampleSchedule = [
            0 => [
                1 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                2 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                3 => ['subject' => 'English Literature', 'teacher' => 'Ananya Verma'],
                4 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                5 => ['subject' => 'English Literature', 'teacher' => 'Ananya Verma'],
                6 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                7 => ['subject' => 'English Literature', 'teacher' => 'Ananya Verma'],
            ],
            1 => [
                1 => ['subject' => 'English Literature', 'teacher' => 'Ananya Verma'],
                2 => ['subject' => 'Science', 'teacher' => 'Bikram Thapa'],
                3 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                4 => ['subject' => 'English Literature', 'teacher' => 'Ananya Verma'],
                5 => ['subject' => 'Science', 'teacher' => 'Bikram Thapa'],
                6 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                7 => ['subject' => 'Science', 'teacher' => 'Bikram Thapa'],
            ],
            2 => [
                1 => ['subject' => 'Nepali', 'teacher' => 'Sunita Adhikari'],
                2 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                3 => ['subject' => 'Social Studies', 'teacher' => 'David Miller'],
                4 => ['subject' => 'Nepali', 'teacher' => 'Sunita Adhikari'],
                5 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                6 => ['subject' => 'Social Studies', 'teacher' => 'David Miller'],
                7 => ['subject' => 'Nepali', 'teacher' => 'Sunita Adhikari'],
            ],
            3 => [
                1 => ['subject' => 'Social Studies', 'teacher' => 'David Miller'],
                2 => ['subject' => 'Nepali', 'teacher' => 'Sunita Adhikari'],
                3 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                4 => ['subject' => 'Social Studies', 'teacher' => 'David Miller'],
                5 => ['subject' => 'Nepali', 'teacher' => 'Sunita Adhikari'],
                6 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                7 => ['subject' => 'Social Studies', 'teacher' => 'David Miller'],
            ],
            4 => [
                1 => ['subject' => 'Computer Science', 'teacher' => 'Priyanka Gupta'],
                2 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                3 => ['subject' => 'Computer Science', 'teacher' => 'Priyanka Gupta'],
                4 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                5 => ['subject' => 'Computer Science', 'teacher' => 'Priyanka Gupta'],
                6 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                7 => ['subject' => 'Computer Science', 'teacher' => 'Priyanka Gupta'],
            ],
            5 => [
                1 => ['subject' => 'Physical Education', 'teacher' => 'Sanjay Karki'],
                2 => ['subject' => 'Physical Education', 'teacher' => 'Sanjay Karki'],
                3 => ['subject' => 'Physical Education', 'teacher' => 'Sanjay Karki'],
                4 => ['subject' => 'Physical Education', 'teacher' => 'Sanjay Karki'],
                5 => ['subject' => 'Physical Education', 'teacher' => 'Sanjay Karki'],
                6 => ['subject' => 'Physical Education', 'teacher' => 'Sanjay Karki'],
                7 => ['subject' => 'Physical Education', 'teacher' => 'Sanjay Karki'],
            ],
            6 => [
                1 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                2 => ['subject' => 'Science', 'teacher' => 'Bikram Thapa'],
                3 => ['subject' => 'Social Studies', 'teacher' => 'David Miller'],
                4 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
                5 => ['subject' => 'Science', 'teacher' => 'Bikram Thapa'],
                6 => ['subject' => 'Social Studies', 'teacher' => 'David Miller'],
                7 => ['subject' => 'Mathematics', 'teacher' => 'Rajesh Sharma'],
            ],
        ];

        $gradeLabel = $selectedClass ? $selectedClass->class_name : 'Grade 1';
        $sectionLabel = $selectedSection ? $selectedSection->section_name : 'Section A';
        
        $cNum = preg_replace('/[^0-9]/', '', $gradeLabel);
        if (!$cNum) $cNum = trim(str_ireplace(['grade', 'class'], '', $gradeLabel));
        $sChar = $selectedSection ? trim(str_ireplace(['section', 'sec'], '', $sectionLabel)) : 'A';
        $gradeSecLabel = ($cNum ?: '1') . '-' . ($sChar ?: 'A');
    @endphp

    {{-- Top indicator badges --}}
    <div class="flex items-center justify-end gap-3 mb-3 text-xs text-slate-500 font-medium print-hide">
        <span class="inline-flex items-center gap-1.5 text-emerald-700">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            @php
                $indicatorParts = [];
                foreach ($displayGroups as $grp) {
                    $indicatorParts[] = count($grp['slots']) . ' slots (' . $grp['name'] . ')';
                }
            @endphp
            {{ implode('  •  ', $indicatorParts) }}
        </span>
    </div>

    {{-- Print Only Header --}}
    <div class="print-only hidden">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-800">
                    {{ $currentSchool->school_name ?? $currentSchool->name ?? 'Genesis Global School' }}
                </h2>
                <p class="text-xs text-slate-500">
                    Class Timetable &middot; {{ $gradeLabel }} ({{ $sectionLabel }})
                </p>
            </div>
            <div class="text-right text-xs text-slate-500">
                <p class="font-semibold text-slate-700">Official Schedule</p>
                <p class="text-[11px] text-slate-400">{{ date('F Y') }}</p>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- VIEW 0: Rapid Matrix Editor (Fastest Matrix System for School Admins)    --}}
    {{-- ========================================================================= --}}
    <div id="viewRapidMatrix" class="nsh-matrix-card mb-6">
        
        {{-- Matrix Control Header Bar --}}
        <div class="p-4 bg-slate-900 text-white flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-800">
            
            {{-- Left: Period presets & Days Selector --}}
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5 mr-1">
                    <i class="bi bi-clock-history"></i> Bell Presets:
                </span>
                <button type="button" onclick="applyPeriodPreset('standard')"
                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-lg border border-slate-700 transition flex items-center gap-1.5 shadow-2xs">
                    <i class="bi bi-sun text-amber-400"></i> Standard (10:00–15:40)
                </button>
                <button type="button" onclick="applyPeriodPreset('morning')"
                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-lg border border-slate-700 transition flex items-center gap-1.5 shadow-2xs">
                    <i class="bi bi-sunrise text-sky-400"></i> Morning (08:30–14:00)
                </button>
                <button type="button" onclick="openPeriodModal()"
                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium rounded-lg border border-slate-700 transition flex items-center gap-1">
                    <i class="bi bi-sliders"></i> Edit Times
                </button>

                <div class="h-4 w-px bg-slate-700 mx-1 hidden sm:block"></div>

                {{-- Weekday Filter --}}
                <div class="inline-flex items-center gap-1 bg-slate-800 p-1 rounded-lg border border-slate-700 text-xs">
                    <button type="button" onclick="setWorkingDays('sun-fri')" id="btnDaysSunFri"
                            class="px-2 py-1 rounded font-semibold text-slate-200 bg-slate-700 transition">Sun–Fri (6D)</button>
                    <button type="button" onclick="setWorkingDays('mon-fri')" id="btnDaysMonFri"
                            class="px-2 py-1 rounded font-medium text-slate-400 hover:text-slate-200 transition">Mon–Fri (5D)</button>
                    <button type="button" onclick="setWorkingDays('mon-sat')" id="btnDaysMonSat"
                            class="px-2 py-1 rounded font-medium text-slate-400 hover:text-slate-200 transition">Mon–Sat (6D)</button>
                    <button type="button" onclick="setWorkingDays('all')" id="btnDaysAll"
                            class="px-2 py-1 rounded font-medium text-slate-400 hover:text-slate-200 transition">All 7D</button>
                </div>
            </div>

            {{-- Right: 1-Click Accelerators --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Quick Copy Day to All --}}
                <div class="flex items-center gap-1 bg-slate-800/90 border border-slate-700 rounded-lg p-1 text-xs">
                    <span class="text-slate-400 pl-1 text-[11px] hidden sm:inline">Copy:</span>
                    <select id="quickCopySourceDay" class="bg-slate-900 border-none text-xs text-slate-200 font-semibold rounded px-1.5 py-0.5 focus:ring-0">
                        <option value="7">Sunday</option>
                        <option value="1" selected>Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                    </select>
                    <button type="button" onclick="quickApplyDayToAll()" title="Copy this day's routine to all other days"
                            class="px-2 py-1 bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold rounded text-xs transition flex items-center gap-1 shadow-xs">
                        <i class="bi bi-lightning-charge-fill"></i> Fill Week
                    </button>
                </div>

                {{-- Clone from Another Section --}}
                <button type="button" onclick="openCloneModal()"
                        class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-lg border border-slate-700 transition flex items-center gap-1.5">
                    <i class="bi bi-copy text-indigo-400"></i> Clone Section…
                </button>

                {{-- Clear Grid --}}
                <button type="button" onclick="clearMatrixGrid()"
                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-rose-950/40 text-slate-400 hover:text-rose-300 text-xs font-medium rounded-lg border border-slate-700 hover:border-rose-900 transition flex items-center gap-1"
                        title="Clear all slots in matrix">
                    <i class="bi bi-trash"></i>
                </button>

                {{-- Bulk Save Timetable Button --}}
                <button type="button" id="btnSaveMatrix" onclick="saveMatrixTimetable()"
                        class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-xs font-bold rounded-lg transition flex items-center gap-2 shadow-md shadow-indigo-900/40">
                    <i class="bi bi-check2-circle text-sm"></i>
                    <span>Save Timetable</span>
                    <span id="matrixSaveCountBadge" class="bg-indigo-800/80 text-[10px] px-1.5 py-0.5 rounded-full font-mono">0</span>
                </button>

            </div>

        </div>

        {{-- Live Status Sub-Bar --}}
        <div class="px-5 py-2.5 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3">
                <span id="matrixStatusIndicator" class="inline-flex items-center gap-1.5 font-semibold text-emerald-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="matrixStatusText">Ready • Loading matrix</span>
                </span>
                <span class="text-slate-300">|</span>
                <span class="text-slate-500 font-medium hidden sm:inline">
                    Target: <strong class="text-slate-700">{{ $gradeLabel }} ({{ $sectionLabel }})</strong>
                </span>
            </div>
            
            <div id="matrixConflictWarningBar" class="hidden items-center gap-2 text-rose-600 font-semibold bg-rose-50 px-2.5 py-1 rounded-md border border-rose-200">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span id="matrixConflictWarningText">Teacher double-booking detected</span>
            </div>
        </div>

        {{-- The Interactive Matrix Grid Table --}}
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse" id="rapidMatrixTable" style="min-width: 950px;">
                <thead id="rapidMatrixThead">
                    {{-- Generated via JS --}}
                </thead>
                <tbody id="rapidMatrixTbody">
                    {{-- Generated via JS --}}
                </tbody>
            </table>
        </div>

        {{-- Matrix Footer Helper --}}
        <div class="px-5 py-3 bg-white border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-sm bg-indigo-50 border border-indigo-200"></span> Active Subject
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-sm bg-rose-50 border border-rose-300"></span> Teacher Conflict
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-sm bg-amber-50 border border-amber-300"></span> Break / Recess
                </span>
            </div>
            <div>
                <span class="text-slate-500 font-medium">⚡ Tip:</span> Select a subject to automatically assign the mapped teacher.
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- VIEW 1: Day-Wise Schedule List Mode (Screenshot 2 layout)                 --}}
    {{-- ========================================================================= --}}
    <div id="viewScheduleList" class="nsh-tt-wrapper mb-6 hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse" style="min-width: 780px;">
                
                {{-- Table Header --}}
                <thead>
                    <tr>
                        <th class="nsh-tt-th w-[140px]">Day</th>
                        <th class="nsh-tt-th w-[180px]">Time</th>
                        <th class="nsh-tt-th">Subject</th>
                        <th class="nsh-tt-th w-[220px]">Teacher</th>
                        <th class="nsh-tt-th w-[100px] text-center actions-col print-hide">Actions</th>
                    </tr>
                </thead>

                {{-- Table Body --}}
                <tbody>
                    @php $isFirstGroup = true; @endphp
                    @foreach($displayGroups as $groupKey => $group)
                        @php $slotCount = count($group['slots']); @endphp
                        
                        @foreach($group['slots'] as $sIndex => $slot)
                        <tr class="nsh-tt-row {{ $sIndex === 0 && !$isFirstGroup ? 'nsh-tt-row-start-day' : '' }}">
                            
                            {{-- Day Column with rowspan --}}
                            @if($sIndex === 0)
                            <td rowspan="{{ $slotCount }}" class="nsh-day-group-cell">
                                <span class="nsh-day-pill">
                                    {{ $group['name'] }}
                                </span>
                                <div class="nsh-day-count">
                                    {{ $slotCount }} {{ $slotCount === 1 ? 'class' : 'classes' }}
                                </div>
                            </td>
                            @endif

                            {{-- Time Slot --}}
                            <td class="nsh-tt-td nsh-time-text">
                                {{ $slot['time'] }}
                            </td>

                            {{-- Subject with Icon Badge --}}
                            <td class="nsh-tt-td">
                                <div class="flex items-center gap-3">
                                    <x-timetable-badge :subject="$slot['subject']" />
                                    <span class="nsh-subject-title">
                                        {{ $slot['subject'] }}
                                    </span>
                                </div>
                            </td>

                            {{-- Teacher with Person Icon --}}
                            <td class="nsh-tt-td">
                                <div class="nsh-teacher-text">
                                    <i class="bi bi-person text-slate-400 text-sm"></i>
                                    <span>{{ $slot['teacher'] }}</span>
                                </div>
                            </td>

                            {{-- Actions Column --}}
                            <td class="nsh-tt-td text-center actions-col print-hide">
                                <div class="inline-flex items-center justify-center gap-2">
                                    @if(isset($slot['id']))
                                    <a href="{{ route('routine.edit', $slot['id']) }}" 
                                       class="p-1 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition" 
                                       title="Edit Slot">
                                        <i class="bi bi-pencil text-sm"></i>
                                    </a>
                                    <form action="{{ route('routine.destroy', $slot['id']) }}" method="POST" onsubmit="return confirm('Remove slot?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Delete">
                                            <i class="bi bi-trash text-sm"></i>
                                        </button>
                                    </form>
                                    @else
                                    <button type="button" onclick="openAddSlotModal({{ $groupKey }})" class="p-1 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition" title="Edit">
                                        <i class="bi bi-pencil text-sm"></i>
                                    </button>
                                    <button type="button" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Delete">
                                        <i class="bi bi-trash text-sm"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>

                        </tr>
                        @endforeach
                        @php $isFirstGroup = false; @endphp
                    @endforeach
                </tbody>

            </table>
        </div>

        {{-- Table Footer with Showing count & Pagination --}}
        <div class="px-5 py-3.5 bg-white border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400 print-hide">
            <div>
                Showing {{ $totalSlotCount }} of {{ $totalSlotCount }} slots
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" class="w-7 h-7 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-400 flex items-center justify-center text-xs">
                    <i class="bi bi-chevron-left text-[10px]"></i>
                </button>
                <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-semibold flex items-center justify-center text-xs shadow-2xs">
                    1
                </span>
                <button type="button" class="w-7 h-7 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-400 flex items-center justify-center text-xs">
                    <i class="bi bi-chevron-right text-[10px]"></i>
                </button>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- VIEW 2: Full 7x7 Weekly Grid Matrix Mode (Screenshot 1 layout)            --}}
    {{-- ========================================================================= --}}
    <div id="viewGridMatrix" class="nsh-grid-card mb-6 hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse" style="min-width: 900px;">
                
                {{-- Header Row --}}
                <thead>
                    <tr>
                        <th class="nsh-grid-th">
                            Grade / Sec
                        </th>
                        @foreach($columnHeaders as $colIndex => $colTitle)
                        <th class="nsh-grid-th">
                            {{ $colTitle }}
                        </th>
                        @endforeach
                    </tr>
                </thead>

                {{-- Body Rows --}}
                <tbody>
                    @foreach($periods as $pIndex => $period)
                    <tr>
                        
                        {{-- Row Header: Dark slate Grade/Sec badge and Time Range --}}
                        <th class="nsh-grid-row-header">
                            <div class="nsh-grid-grade-badge">
                                {{ $gradeSecLabel }}
                            </div>
                            <div class="nsh-grid-time-badge">
                                {{ $period['label'] }}
                            </div>
                        </th>

                        {{-- Period Cells --}}
                        @foreach($columnHeaders as $colNum => $colName)
                            @php
                                $slotMatch = null;
                                if ($dbSlots->isNotEmpty()) {
                                    $slotMatch = $dbSlots->first(function($item) use ($colNum, $period) {
                                        return $item->weekday == $colNum && (
                                            $item->start == $period['start'] || 
                                            ($item->start . ' - ' . $item->end) == $period['label']
                                        );
                                    });
                                }

                                $cellData = null;
                                if ($slotMatch) {
                                    $cellData = [
                                        'id'      => $slotMatch->id,
                                        'subject' => $slotMatch->course?->course_name ?? 'Subject',
                                        'teacher' => $slotMatch->teacher?->full_name ?? ($slotMatch->teacher?->name ?? 'Faculty'),
                                        'room'    => $slotMatch->room,
                                        'color'   => $slotMatch->color,
                                    ];
                                } elseif (isset($sampleSchedule[$pIndex][$colNum])) {
                                    $cellData = $sampleSchedule[$pIndex][$colNum];
                                }
                            @endphp

                            <td class="nsh-grid-cell relative group">
                                @if($cellData)
                                    <div class="flex flex-col items-center justify-center">
                                        
                                        {{-- Subject Icon Badge --}}
                                        <x-timetable-badge :subject="$cellData['subject']" :color="$cellData['color'] ?? null" />

                                        {{-- Subject Name --}}
                                        <span class="nsh-grid-subject-name" title="{{ $cellData['subject'] }}">
                                            {{ $cellData['subject'] }}
                                        </span>

                                        {{-- Teacher Name with Icon --}}
                                        @if(!empty($cellData['teacher']))
                                        <div class="nsh-grid-teacher-name">
                                            <i class="bi bi-person-fill"></i>
                                            <span class="truncate" title="{{ $cellData['teacher'] }}">{{ $cellData['teacher'] }}</span>
                                        </div>
                                        @endif

                                        {{-- Hover Action Buttons --}}
                                        @if(isset($cellData['id']))
                                        <div class="absolute top-1 right-1 hidden group-hover:flex items-center gap-0.5 bg-white shadow-sm rounded-md p-0.5 border border-slate-200 z-10 print-hide">
                                            <a href="{{ route('routine.edit', $cellData['id']) }}" class="p-1 text-slate-500 hover:text-indigo-600 rounded text-[10px]" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('routine.destroy', $cellData['id']) }}" method="POST" onsubmit="return confirm('Remove slot?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1 text-slate-500 hover:text-rose-600 rounded text-[10px]" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                        @endif

                                    </div>
                                @else
                                    <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                        @endforeach

                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

</div>

{{-- Add Slot Modal --}}
<div id="addSlotModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4 print-hide">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden">
        
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="bi bi-clock-history text-indigo-600"></i> Add Timetable Slot
            </h3>
            <button type="button" onclick="closeAddSlotModal()" class="text-slate-400 hover:text-slate-600 text-base">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form action="{{ route('section.routine.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="session_id" value="{{ $session_id }}">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Class *</label>
                    <select name="class_id" id="modal_class" required onchange="loadModalSections(this.value)"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 bg-white focus:ring-2 focus:ring-indigo-500">
                        @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ ($class_id ?? 0) == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Section *</label>
                    <select name="section_id" id="modal_section" required
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Section</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Subject / Course *</label>
                    <select name="course_id" id="modal_course" required
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Course</option>
                        @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->course_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Assigned Teacher</label>
                    <select name="teacher_id" id="modal_teacher"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Teacher</option>
                        @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Day *</label>
                    <select name="weekday" id="modal_weekday" required
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                        <option value="7">Sunday</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Start Time *</label>
                    <input type="time" name="start" id="modal_start" value="08:30" required
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 bg-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">End Time *</label>
                    <input type="time" name="end" id="modal_end" value="09:15" required
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 bg-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Room No.</label>
                    <input type="text" name="room" placeholder="e.g. 101, Lab 2"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 bg-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Color Theme</label>
                    <input type="color" name="color" value="#6366f1"
                           class="w-full h-9 border border-slate-200 rounded-xl p-1 bg-white cursor-pointer">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeAddSlotModal()"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                    Save Slot
                </button>
            </div>
        </form>

    </div>
</div>

{{-- Clone Section Modal --}}
<div id="cloneSectionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4 print-hide">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="bi bi-copy text-indigo-400"></i> Clone Timetable from Section
            </h3>
            <button type="button" onclick="closeCloneModal()" class="text-slate-400 hover:text-white text-base">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-xs text-slate-500 leading-relaxed">
                Copy the complete weekly period schedule, courses, and mapped teachers from another section into <strong class="text-slate-800">{{ $gradeLabel }} ({{ $sectionLabel }})</strong> in 1 click.
            </p>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Source Section to Copy From *</label>
                <select id="cloneSourceSectionSelect" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-700 bg-white focus:ring-2 focus:ring-indigo-500 font-medium">
                    <option value="">— Select Source Section —</option>
                    @foreach($cloneableSections as $cs)
                    <option value="{{ $cs['class_id'] }}_{{ $cs['section_id'] }}">
                        {{ $cs['label'] }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 flex items-start gap-2">
                <i class="bi bi-info-circle-fill text-amber-600 mt-0.5 flex-shrink-0"></i>
                <span>This will populate the matrix for your review. You can make adjustments and click "Save Timetable" to commit.</span>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeCloneModal()"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition">
                    Cancel
                </button>
                <button type="button" id="btnExecuteClone" onclick="executeCloneSection()"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition shadow-xs flex items-center gap-1.5">
                    <i class="bi bi-copy"></i> Clone Now
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Periods / Bell Schedule Modal --}}
<div id="periodModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4 print-hide">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="bi bi-clock-history text-amber-400"></i> Bell Schedule &amp; Periods
            </h3>
            <button type="button" onclick="closePeriodModal()" class="text-slate-400 hover:text-white text-base">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-xs text-slate-500 mb-3">
                Customize bell period timings and recess breaks for the matrix grid.
            </p>

            <div id="periodEditorList" class="space-y-2 max-h-[350px] overflow-y-auto pr-1 mb-4">
                {{-- Injected dynamically --}}
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <button type="button" onclick="addNewPeriodRow()"
                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition flex items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Add Period
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closePeriodModal()"
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition">
                        Cancel
                    </button>
                    <button type="button" onclick="savePeriodSchedule()"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                        Apply Periods
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Notification Toast --}}
<div id="nshToast" class="nsh-toast bg-slate-900 text-white border border-slate-800">
    <i id="nshToastIcon" class="bi bi-check-circle-fill text-emerald-400 text-base"></i>
    <span id="nshToastMsg">Action completed</span>
</div>

<script>
// Server Data Payload
const SERVER_DATA = {
    classId: {{ (int) ($class_id ?? 0) }},
    sectionId: {{ (int) ($section_id ?? 0) }},
    sessionId: {{ (int) ($session_id ?? 0) }},
    courses: @json($courses ?? []),
    teachers: @json($teachers ?? []),
    teacherMappings: @json($teacherMappings ?? (object)[]),
    existingSlots: @json($rawRoutines ?? []),
    cloneableSections: @json($cloneableSections ?? []),
    csrfToken: '{{ csrf_token() }}',
    urls: {
        matrixData: '{{ route("routine.matrix.data") }}',
        matrixSave: '{{ route("routine.matrix.save") }}',
        cloneSection: '{{ route("routine.clone.section") }}',
    }
};

// Weekday Definitions
const ALL_WEEKDAYS = {
    7: { id: 7, name: 'Sunday', short: 'Sun' },
    1: { id: 1, name: 'Monday', short: 'Mon' },
    2: { id: 2, name: 'Tuesday', short: 'Tue' },
    3: { id: 3, name: 'Wednesday', short: 'Wed' },
    4: { id: 4, name: 'Thursday', short: 'Thu' },
    5: { id: 5, name: 'Friday', short: 'Fri' },
    6: { id: 6, name: 'Saturday', short: 'Sat' },
};

// Period Presets
const PRESETS = {
    standard: [
        { name: 'Period 1', start: '10:00', end: '10:45', is_break: false },
        { name: 'Period 2', start: '10:45', end: '11:30', is_break: false },
        { name: 'Period 3', start: '11:30', end: '12:15', is_break: false },
        { name: 'Tiffin / Lunch Break', start: '12:15', end: '12:45', is_break: true },
        { name: 'Period 4', start: '12:45', end: '13:30', is_break: false },
        { name: 'Period 5', start: '13:30', end: '14:15', is_break: false },
        { name: 'Period 6', start: '14:15', end: '15:00', is_break: false },
        { name: 'Period 7', start: '15:00', end: '15:40', is_break: false },
    ],
    morning: [
        { name: 'Period 1', start: '08:30', end: '09:15', is_break: false },
        { name: 'Period 2', start: '09:15', end: '10:00', is_break: false },
        { name: 'Period 3', start: '10:00', end: '10:45', is_break: false },
        { name: 'Tiffin Break', start: '10:45', end: '11:15', is_break: true },
        { name: 'Period 4', start: '11:15', end: '12:00', is_break: false },
        { name: 'Period 5', start: '12:00', end: '12:45', is_break: false },
        { name: 'Period 6', start: '12:45', end: '13:30', is_break: false },
        { name: 'Period 7', start: '13:30', end: '14:00', is_break: false },
    ]
};

// State Variables
let currentPeriods = [];
let activeDayKeys = [7, 1, 2, 3, 4, 5]; // Default Sunday to Friday (Nepal 6-Day)
let matrixGrid = {}; // matrixGrid[pIndex][weekday] = { course_id, teacher_id, room, color }
let otherBookings = [];
let hasUnsavedChanges = false;

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    initPeriodsAndMatrix();
    fetchOtherBookings();

    // Mode check
    const savedMode = localStorage.getItem('nsh_timetable_mode') || 'matrix';
    const urlParams = new URLSearchParams(window.location.search);
    const viewParam = urlParams.get('view') || savedMode;
    switchTimetableMode(viewParam);
});

// Toast Helper
function showToast(msg, isError = false) {
    const toast = document.getElementById('nshToast');
    const msgEl = document.getElementById('nshToastMsg');
    const iconEl = document.getElementById('nshToastIcon');

    msgEl.innerHTML = msg;
    if (isError) {
        iconEl.className = 'bi bi-exclamation-circle-fill text-rose-400 text-base';
        toast.className = 'nsh-toast bg-rose-950 text-rose-100 border border-rose-800 show';
    } else {
        iconEl.className = 'bi bi-check-circle-fill text-emerald-400 text-base';
        toast.className = 'nsh-toast bg-slate-900 text-white border border-slate-800 show';
    }

    setTimeout(() => {
        toast.classList.remove('show');
    }, 3500);
}

// Fetch other bookings in background for real-time conflict checking
function fetchOtherBookings() {
    if (!SERVER_DATA.classId || !SERVER_DATA.sectionId) return;
    const url = `${SERVER_DATA.urls.matrixData}?class_id=${SERVER_DATA.classId}&section_id=${SERVER_DATA.sectionId}`;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                otherBookings = data.other_bookings || [];
                // Update teacher mappings if server provided more recent
                if (data.teacher_mappings && Object.keys(data.teacher_mappings).length > 0) {
                    SERVER_DATA.teacherMappings = data.teacher_mappings;
                }
                renderMatrix();
            }
        })
        .catch(err => console.error('Error fetching matrix bookings:', err));
}

// Initialize Periods from Existing Slots or Default to Standard Preset
function initPeriodsAndMatrix() {
    matrixGrid = {};

    // 1. Check if existing slots have specific timings
    const existing = SERVER_DATA.existingSlots || [];
    if (existing.length > 0) {
        // Extract unique timings
        const map = new Map();
        existing.forEach(s => {
            const st = (s.start || '').substring(0, 5);
            const en = (s.end || '').substring(0, 5);
            const key = `${st}-${en}`;
            if (!map.has(key) && st && en) {
                map.set(key, { start: st, end: en });
            }
        });

        const sorted = Array.from(map.values()).sort((a, b) => a.start.localeCompare(b.start));
        if (sorted.length >= 3) {
            currentPeriods = sorted.map((item, idx) => ({
                name: `Period ${idx + 1}`,
                start: item.start,
                end: item.end,
                is_break: false
            }));
        } else {
            currentPeriods = JSON.parse(JSON.stringify(PRESETS.standard));
        }
    } else {
        currentPeriods = JSON.parse(JSON.stringify(PRESETS.standard));
    }

    // 2. Populate matrixGrid with existing slots
    currentPeriods.forEach((p, pIdx) => {
        matrixGrid[pIdx] = {};
        if (p.is_break) return;

        ALL_DAYS_KEYS = [1, 2, 3, 4, 5, 6, 7];
        ALL_DAYS_KEYS.forEach(day => {
            const match = existing.find(s => {
                const sStart = (s.start || '').substring(0, 5);
                const sEnd = (s.end || '').substring(0, 5);
                return parseInt(s.weekday) === day && (sStart === p.start || sEnd === p.end);
            });

            if (match) {
                matrixGrid[pIdx][day] = {
                    id: match.id,
                    course_id: match.course_id,
                    teacher_id: match.teacher_id,
                    room: match.room || '',
                    color: match.color || ''
                };
            } else {
                matrixGrid[pIdx][day] = null;
            }
        });
    });

    renderMatrix();
}

// Working Days Selector
function setWorkingDays(type) {
    const btnSunFri = document.getElementById('btnDaysSunFri');
    const btnMonFri = document.getElementById('btnDaysMonFri');
    const btnMonSat = document.getElementById('btnDaysMonSat');
    const btnAll = document.getElementById('btnDaysAll');

    [btnSunFri, btnMonFri, btnMonSat, btnAll].forEach(b => {
        b.className = 'px-2 py-1 rounded font-medium text-slate-400 hover:text-slate-200 transition';
    });

    if (type === 'sun-fri') {
        activeDayKeys = [7, 1, 2, 3, 4, 5];
        btnSunFri.className = 'px-2 py-1 rounded font-semibold text-slate-200 bg-slate-700 transition';
    } else if (type === 'mon-fri') {
        activeDayKeys = [1, 2, 3, 4, 5];
        btnMonFri.className = 'px-2 py-1 rounded font-semibold text-slate-200 bg-slate-700 transition';
    } else if (type === 'mon-sat') {
        activeDayKeys = [1, 2, 3, 4, 5, 6];
        btnMonSat.className = 'px-2 py-1 rounded font-semibold text-slate-200 bg-slate-700 transition';
    } else if (type === 'all') {
        activeDayKeys = [7, 1, 2, 3, 4, 5, 6];
        btnAll.className = 'px-2 py-1 rounded font-semibold text-slate-200 bg-slate-700 transition';
    }

    renderMatrix();
}

// Preset Applier
function applyPeriodPreset(type) {
    if (PRESETS[type]) {
        currentPeriods = JSON.parse(JSON.stringify(PRESETS[type]));
        // re-initialize grid keys
        const newGrid = {};
        currentPeriods.forEach((p, idx) => {
            newGrid[idx] = matrixGrid[idx] || {};
        });
        matrixGrid = newGrid;
        renderMatrix();
        markUnsaved();
        showToast(`⚡ Applied ${type === 'standard' ? 'Standard (10:00–15:40)' : 'Morning (08:30–14:00)'} bell preset!`);
    }
}

// Render the entire Matrix Table
function renderMatrix() {
    const thead = document.getElementById('rapidMatrixThead');
    const tbody = document.getElementById('rapidMatrixTbody');
    if (!thead || !tbody) return;

    // 1. Build Thead
    let thHtml = '<tr><th class="nsh-matrix-th nsh-matrix-th-period">Period / Bell Time</th>';
    activeDayKeys.forEach(dayKey => {
        const d = ALL_WEEKDAYS[dayKey];
        thHtml += `
            <th class="nsh-matrix-th group">
                <div class="flex items-center justify-center gap-1.5">
                    <span>${d.name}</span>
                    <button type="button" onclick="copySingleDayToAll(${dayKey})" title="Copy ${d.name} schedule to all weekdays"
                            class="opacity-0 group-hover:opacity-100 p-1 hover:bg-slate-700 text-amber-400 rounded transition text-[10px]" style="line-height:1;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </button>
                </div>
            </th>
        `;
    });
    thHtml += '</tr>';
    thead.innerHTML = thHtml;

    // 2. Build Tbody
    let tbHtml = '';
    let totalSlots = 0;
    let conflictCount = 0;

    currentPeriods.forEach((period, pIdx) => {
        if (!matrixGrid[pIdx]) matrixGrid[pIdx] = {};

        if (period.is_break) {
            tbHtml += `
                <tr class="nsh-break-row">
                    <td colspan="${activeDayKeys.length + 1}" class="py-2.5 px-4 text-center text-amber-800 font-bold text-xs tracking-wide">
                        <i class="bi bi-cup-hot-fill text-amber-600 mr-1.5"></i>
                        ${period.name} &nbsp;•&nbsp; <span class="font-mono text-[11px]">${period.start} – ${period.end}</span>
                    </td>
                </tr>
            `;
            return;
        }

        // Calculate period duration in minutes
        let durationMin = 45;
        try {
            const [sh, sm] = period.start.split(':').map(Number);
            const [eh, em] = period.end.split(':').map(Number);
            durationMin = (eh * 60 + em) - (sh * 60 + sm);
        } catch(e) {}

        tbHtml += `<tr>`;
        
        // Row Header
        tbHtml += `
            <th class="nsh-matrix-row-head">
                <div class="font-bold text-slate-800 text-xs">${period.name}</div>
                <div class="text-[11px] font-mono text-slate-500 mt-0.5">${period.start} – ${period.end}</div>
                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-200 text-slate-600">${durationMin} min</span>
            </th>
        `;

        // Cells for each Day
        activeDayKeys.forEach(dayKey => {
            const slot = matrixGrid[pIdx][dayKey] || null;
            const courseId = slot ? slot.course_id : '';
            const teacherId = slot ? slot.teacher_id : '';
            const room = slot ? (slot.room || '') : '';

            if (courseId) totalSlots++;

            // Check conflict
            let conflictMsg = null;
            if (teacherId && otherBookings.length > 0) {
                const clash = otherBookings.find(ob => {
                    return ob.weekday === dayKey && 
                           ob.teacher_id === parseInt(teacherId) &&
                           ob.start < period.end && ob.end > period.start;
                });
                if (clash) {
                    conflictMsg = `Conflict: ${clash.teacher_name} is in ${clash.class_name} (${clash.section_name})`;
                    conflictCount++;
                }
            }

            const cellClass = conflictMsg ? 'nsh-matrix-cell has-conflict' : (courseId ? 'nsh-matrix-cell has-subject' : 'nsh-matrix-cell');

            tbHtml += `
                <td class="${cellClass}" id="cell_${pIdx}_${dayKey}">
                    
                    {{-- Subject Dropdown --}}
                    <div class="relative mb-1">
                        <select onchange="onCellCourseChange(${pIdx}, ${dayKey}, this.value)"
                                class="w-full bg-white border border-slate-200 hover:border-slate-300 rounded-lg px-2 py-1.5 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-2xs transition">
                            <option value="">— Empty —</option>
                            ${SERVER_DATA.courses.map(c => `
                                <option value="${c.id}" ${courseId == c.id ? 'selected' : ''}>${c.course_name}</option>
                            `).join('')}
                        </select>
                    </div>

                    {{-- Teacher & Room Bar --}}
                    <div class="flex items-center justify-between gap-1 text-[11px] min-h-[22px]">
                        
                        {{-- Teacher info --}}
                        <div class="flex-1 truncate">
                            ${courseId ? `
                                <div class="inline-flex items-center gap-1 text-slate-700 font-medium">
                                    <i class="bi bi-person-fill text-indigo-500 text-[10px]"></i>
                                    <select onchange="onCellTeacherChange(${pIdx}, ${dayKey}, this.value)"
                                            class="bg-transparent border-0 p-0 text-[11px] font-medium text-slate-600 hover:text-indigo-600 focus:ring-0 cursor-pointer truncate max-w-[95px]">
                                        <option value="">No Teacher</option>
                                        ${SERVER_DATA.teachers.map(t => `
                                            <option value="${t.id}" ${teacherId == t.id ? 'selected' : ''}>${t.first_name || t.name}</option>
                                        `).join('')}
                                    </select>
                                </div>
                            ` : `<span class="text-slate-300 text-[10px]">—</span>`}
                        </div>

                        {{-- Room Input --}}
                        ${courseId ? `
                            <div class="w-12">
                                <input type="text" placeholder="Room" value="${room}"
                                       onchange="onCellRoomChange(${pIdx}, ${dayKey}, this.value)"
                                       class="w-full bg-slate-50 border border-slate-200 rounded px-1 py-0.5 text-[10px] text-slate-700 text-center font-mono focus:bg-white focus:outline-none">
                            </div>
                            <button type="button" onclick="clearCellSlot(${pIdx}, ${dayKey})" title="Clear slot"
                                    class="text-slate-300 hover:text-rose-500 text-xs px-0.5">
                                <i class="bi bi-x"></i>
                            </button>
                        ` : ''}

                    </div>

                    {{-- Conflict Warning --}}
                    ${conflictMsg ? `
                        <div class="mt-1 px-1.5 py-0.5 bg-rose-100/90 text-rose-700 rounded text-[10px] font-bold flex items-center gap-1 leading-tight" title="${conflictMsg}">
                            <i class="bi bi-exclamation-triangle-fill text-rose-500"></i>
                            <span class="truncate">${conflictMsg}</span>
                        </div>
                    ` : ''}

                </td>
            `;
        });

        tbHtml += `</tr>`;
    });

    tbody.innerHTML = tbHtml;

    // Update Counter & Conflict badge
    const countBadge = document.getElementById('matrixSaveCountBadge');
    if (countBadge) countBadge.textContent = totalSlots;

    const conflictBar = document.getElementById('matrixConflictWarningBar');
    const conflictText = document.getElementById('matrixConflictWarningText');
    if (conflictCount > 0) {
        conflictBar.classList.remove('hidden');
        conflictBar.classList.add('flex');
        conflictText.textContent = `${conflictCount} teacher conflict${conflictCount > 1 ? 's' : ''} detected`;
    } else {
        conflictBar.classList.add('hidden');
        conflictBar.classList.remove('flex');
    }

    updateStatusText(totalSlots);
}

// Update Status Indicator
function updateStatusText(count) {
    const statusText = document.getElementById('matrixStatusText');
    if (!statusText) return;

    if (hasUnsavedChanges) {
        statusText.innerHTML = `<span class="text-amber-600 font-bold">Unsaved changes (${count} slots)</span> &bull; Click "Save Timetable"`;
    } else {
        statusText.innerHTML = `Active Timetable &bull; <strong class="text-slate-700">${count} slots saved</strong>`;
    }
}

function markUnsaved() {
    hasUnsavedChanges = true;
    const btn = document.getElementById('btnSaveMatrix');
    if (btn) {
        btn.classList.add('animate-pulse');
    }
    const count = Object.values(matrixGrid).reduce((acc, row) => {
        return acc + Object.values(row).filter(s => s && s.course_id).length;
    }, 0);
    updateStatusText(count);
}

function markSaved() {
    hasUnsavedChanges = false;
    const btn = document.getElementById('btnSaveMatrix');
    if (btn) {
        btn.classList.remove('animate-pulse');
    }
    const count = Object.values(matrixGrid).reduce((acc, row) => {
        return acc + Object.values(row).filter(s => s && s.course_id).length;
    }, 0);
    updateStatusText(count);
}

// Cell Event Handlers
function onCellCourseChange(pIdx, dayKey, courseId) {
    if (!matrixGrid[pIdx]) matrixGrid[pIdx] = {};

    if (!courseId) {
        matrixGrid[pIdx][dayKey] = null;
    } else {
        // Auto-assign teacher from teacherMappings
        const mappedTeacherId = SERVER_DATA.teacherMappings[courseId] || '';
        const existing = matrixGrid[pIdx][dayKey] || {};
        matrixGrid[pIdx][dayKey] = {
            id: existing.id || null,
            course_id: parseInt(courseId),
            teacher_id: mappedTeacherId ? parseInt(mappedTeacherId) : (existing.teacher_id || null),
            room: existing.room || '',
            color: existing.color || ''
        };
    }

    markUnsaved();
    renderMatrix();
}

function onCellTeacherChange(pIdx, dayKey, teacherId) {
    if (matrixGrid[pIdx] && matrixGrid[pIdx][dayKey]) {
        matrixGrid[pIdx][dayKey].teacher_id = teacherId ? parseInt(teacherId) : null;
        markUnsaved();
        renderMatrix();
    }
}

function onCellRoomChange(pIdx, dayKey, roomVal) {
    if (matrixGrid[pIdx] && matrixGrid[pIdx][dayKey]) {
        matrixGrid[pIdx][dayKey].room = roomVal;
        markUnsaved();
    }
}

function clearCellSlot(pIdx, dayKey) {
    if (matrixGrid[pIdx]) {
        matrixGrid[pIdx][dayKey] = null;
        markUnsaved();
        renderMatrix();
    }
}

// 1-Click Accelerator: Copy Day Schedule to All Days
function quickApplyDayToAll() {
    const sel = document.getElementById('quickCopySourceDay');
    if (!sel) return;
    copySingleDayToAll(parseInt(sel.value));
}

function copySingleDayToAll(sourceDay) {
    const dayName = ALL_WEEKDAYS[sourceDay]?.name || 'Day ' + sourceDay;

    // Collect source slots
    let copiedCount = 0;
    currentPeriods.forEach((p, pIdx) => {
        if (p.is_break) return;
        const srcSlot = matrixGrid[pIdx] ? matrixGrid[pIdx][sourceDay] : null;

        activeDayKeys.forEach(targetDay => {
            if (targetDay !== sourceDay) {
                if (!matrixGrid[pIdx]) matrixGrid[pIdx] = {};
                if (srcSlot && srcSlot.course_id) {
                    matrixGrid[pIdx][targetDay] = {
                        course_id: srcSlot.course_id,
                        teacher_id: srcSlot.teacher_id,
                        room: srcSlot.room,
                        color: srcSlot.color
                    };
                    copiedCount++;
                } else {
                    matrixGrid[pIdx][targetDay] = null;
                }
            }
        });
    });

    markUnsaved();
    renderMatrix();
    showToast(`⚡ Copied ${dayName} timetable to all active weekdays!`);
}

// Clear all slots
function clearMatrixGrid() {
    if (!confirm('Clear all timetable slots in the matrix?')) return;
    currentPeriods.forEach((p, pIdx) => {
        matrixGrid[pIdx] = {};
    });
    markUnsaved();
    renderMatrix();
    showToast('🧹 Matrix cleared.');
}

// Bulk Save
function saveMatrixTimetable() {
    if (!SERVER_DATA.classId || !SERVER_DATA.sectionId) {
        showToast('Please select a Class and Section first.', true);
        return;
    }

    const slotsToSave = [];
    currentPeriods.forEach((p, pIdx) => {
        if (p.is_break) return;
        if (!matrixGrid[pIdx]) return;

        Object.keys(matrixGrid[pIdx]).forEach(dayKey => {
            const slot = matrixGrid[pIdx][dayKey];
            if (slot && slot.course_id) {
                slotsToSave.push({
                    weekday: parseInt(dayKey),
                    start: p.start,
                    end: p.end,
                    course_id: slot.course_id,
                    teacher_id: slot.teacher_id || null,
                    room: slot.room || null,
                    color: slot.color || null,
                });
            }
        });
    });

    const btn = document.getElementById('btnSaveMatrix');
    const origHtml = btn.innerHTML;
    btn.innerHTML = `<i class="bi bi-arrow-repeat animate-spin text-sm"></i> Saving…`;
    btn.disabled = true;

    fetch(SERVER_DATA.urls.matrixSave, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': SERVER_DATA.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            class_id: SERVER_DATA.classId,
            section_id: SERVER_DATA.sectionId,
            slots: slotsToSave
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.innerHTML = origHtml;
        btn.disabled = false;

        if (data.success) {
            markSaved();
            showToast(`✅ ${data.message || 'Timetable saved successfully!'}`);
            // refresh background bookings
            fetchOtherBookings();
        } else {
            showToast(data.message || 'Error saving timetable', true);
            if (data.conflict) {
                const conflictBar = document.getElementById('matrixConflictWarningBar');
                const conflictText = document.getElementById('matrixConflictWarningText');
                conflictBar.classList.remove('hidden');
                conflictBar.classList.add('flex');
                conflictText.innerHTML = data.message;
            }
        }
    })
    .catch(err => {
        btn.innerHTML = origHtml;
        btn.disabled = false;
        showToast('Server error while saving timetable.', true);
        console.error(err);
    });
}

// Clone Section Modal
function openCloneModal() {
    const modal = document.getElementById('cloneSectionModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeCloneModal() {
    const modal = document.getElementById('cloneSectionModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function executeCloneSection() {
    const sel = document.getElementById('cloneSourceSectionSelect');
    if (!sel || !sel.value) {
        showToast('Please select a source section.', true);
        return;
    }

    const [srcClassId, srcSecId] = sel.value.split('_').map(Number);
    const btn = document.getElementById('btnExecuteClone');
    btn.disabled = true;
    btn.innerHTML = `<i class="bi bi-arrow-repeat animate-spin"></i> Cloning…`;

    fetch(SERVER_DATA.urls.cloneSection, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': SERVER_DATA.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            source_class_id: srcClassId,
            source_section_id: srcSecId,
            target_class_id: SERVER_DATA.classId,
            target_section_id: SERVER_DATA.sectionId
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-copy"></i> Clone Now`;

        if (data.success) {
            closeCloneModal();
            // Populate matrix
            const slots = data.slots || [];
            slots.forEach(s => {
                // Find matching period index or create
                let pIdx = currentPeriods.findIndex(p => p.start === s.start && p.end === s.end);
                if (pIdx === -1) {
                    pIdx = currentPeriods.length;
                    currentPeriods.push({
                        name: `Period ${pIdx + 1}`,
                        start: s.start,
                        end: s.end,
                        is_break: false
                    });
                }
                if (!matrixGrid[pIdx]) matrixGrid[pIdx] = {};
                matrixGrid[pIdx][s.weekday] = {
                    course_id: s.course_id,
                    teacher_id: s.teacher_id,
                    room: s.room,
                    color: s.color
                };
            });

            markUnsaved();
            renderMatrix();
            showToast(`🔄 Cloned ${slots.length} periods from section! Click Save Timetable to commit.`);
        } else {
            showToast(data.message || 'Clone failed', true);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-copy"></i> Clone Now`;
        showToast('Clone failed due to a server error.', true);
    });
}

// Period Schedule Modal
function openPeriodModal() {
    const list = document.getElementById('periodEditorList');
    list.innerHTML = currentPeriods.map((p, idx) => `
        <div class="flex items-center gap-2 p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" id="periodRow_${idx}">
            <input type="text" value="${p.name}" placeholder="Period Name" id="pName_${idx}"
                   class="w-32 bg-white border border-slate-200 rounded px-2 py-1 text-slate-700 font-semibold">
            <input type="time" value="${p.start}" id="pStart_${idx}"
                   class="bg-white border border-slate-200 rounded px-2 py-1 text-slate-700 font-mono">
            <span class="text-slate-400">–</span>
            <input type="time" value="${p.end}" id="pEnd_${idx}"
                   class="bg-white border border-slate-200 rounded px-2 py-1 text-slate-700 font-mono">
            <label class="inline-flex items-center gap-1 text-[11px] text-slate-600 ml-1">
                <input type="checkbox" id="pBreak_${idx}" ${p.is_break ? 'checked' : ''} class="rounded text-amber-500"> Break
            </label>
            <button type="button" onclick="document.getElementById('periodRow_${idx}').remove()" class="text-slate-400 hover:text-rose-500 ml-auto p-1">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `).join('');

    const modal = document.getElementById('periodModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePeriodModal() {
    const modal = document.getElementById('periodModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function addNewPeriodRow() {
    const list = document.getElementById('periodEditorList');
    const idx = Date.now();
    const div = document.createElement('div');
    div.className = "flex items-center gap-2 p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs";
    div.id = `periodRow_${idx}`;
    div.innerHTML = `
        <input type="text" value="Period ${list.children.length + 1}" placeholder="Period Name" id="pName_${idx}"
               class="w-32 bg-white border border-slate-200 rounded px-2 py-1 text-slate-700 font-semibold">
        <input type="time" value="15:45" id="pStart_${idx}"
               class="bg-white border border-slate-200 rounded px-2 py-1 text-slate-700 font-mono">
        <span class="text-slate-400">–</span>
        <input type="time" value="16:30" id="pEnd_${idx}"
               class="bg-white border border-slate-200 rounded px-2 py-1 text-slate-700 font-mono">
        <label class="inline-flex items-center gap-1 text-[11px] text-slate-600 ml-1">
            <input type="checkbox" id="pBreak_${idx}" class="rounded text-amber-500"> Break
        </label>
        <button type="button" onclick="document.getElementById('periodRow_${idx}').remove()" class="text-slate-400 hover:text-rose-500 ml-auto p-1">
            <i class="bi bi-trash"></i>
        </button>
    `;
    list.appendChild(div);
}

function savePeriodSchedule() {
    const list = document.getElementById('periodEditorList');
    const rows = list.children;
    const newPeriods = [];

    for (let r of rows) {
        const id = r.id.replace('periodRow_', '');
        const name = document.getElementById(`pName_${id}`)?.value || 'Period';
        const start = document.getElementById(`pStart_${id}`)?.value || '10:00';
        const end = document.getElementById(`pEnd_${id}`)?.value || '10:45';
        const is_break = document.getElementById(`pBreak_${id}`)?.checked || false;

        newPeriods.push({ name, start, end, is_break });
    }

    if (newPeriods.length === 0) {
        showToast('Please have at least 1 period.', true);
        return;
    }

    currentPeriods = newPeriods;
    closePeriodModal();
    markUnsaved();
    renderMatrix();
    showToast('Applied updated period schedule!');
}

// Mode Switcher (Rapid Matrix vs Grid Mode vs Schedule)
function switchTimetableMode(mode) {
    const viewMatrix = document.getElementById('viewRapidMatrix');
    const viewSchedule = document.getElementById('viewScheduleList');
    const viewGrid = document.getElementById('viewGridMatrix');
    const btnMatrix = document.getElementById('btnModeMatrix');
    const btnSchedule = document.getElementById('btnModeSchedule');
    const btnGrid = document.getElementById('btnModeGrid');

    const activeClass = "inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold rounded-lg transition bg-white text-indigo-600 shadow-2xs";
    const inactiveClass = "inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900";

    if (mode === 'grid') {
        if (viewMatrix) viewMatrix.classList.add('hidden');
        if (viewSchedule) viewSchedule.classList.add('hidden');
        if (viewGrid) viewGrid.classList.remove('hidden');

        if (btnGrid) btnGrid.className = activeClass;
        if (btnMatrix) btnMatrix.className = inactiveClass;
        if (btnSchedule) btnSchedule.className = inactiveClass;

        localStorage.setItem('nsh_timetable_mode', 'grid');
    } else if (mode === 'schedule') {
        if (viewMatrix) viewMatrix.classList.add('hidden');
        if (viewGrid) viewGrid.classList.add('hidden');
        if (viewSchedule) viewSchedule.classList.remove('hidden');

        if (btnSchedule) btnSchedule.className = activeClass;
        if (btnMatrix) btnMatrix.className = inactiveClass;
        if (btnGrid) btnGrid.className = inactiveClass;

        localStorage.setItem('nsh_timetable_mode', 'schedule');
    } else {
        // Default to Rapid Matrix
        if (viewGrid) viewGrid.classList.add('hidden');
        if (viewSchedule) viewSchedule.classList.add('hidden');
        if (viewMatrix) viewMatrix.classList.remove('hidden');

        if (btnMatrix) btnMatrix.className = activeClass;
        if (btnGrid) btnGrid.className = inactiveClass;
        if (btnSchedule) btnSchedule.className = inactiveClass;

        localStorage.setItem('nsh_timetable_mode', 'matrix');
    }
}

function loadSections(classId, preselect) {
    if (!classId) return;
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('section_select');
            if (sel) {
                sel.innerHTML = '<option value="">Select Section…</option>';
                (data.sections || []).forEach(s => {
                    const opt = new Option(s.section_name, s.id);
                    if (preselect && s.id == preselect) opt.selected = true;
                    sel.add(opt);
                });
            }
        });
}

function loadModalSections(classId, preselect) {
    if (!classId) return;
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('modal_section');
            if (sel) {
                sel.innerHTML = '<option value="">Select Section</option>';
                (data.sections || []).forEach(s => {
                    const opt = new Option(s.section_name, s.id);
                    if (preselect && s.id == preselect) opt.selected = true;
                    sel.add(opt);
                });
            }
        });

    fetch(`/ajax/courses-by-class?class_id=${classId}`)
        .then(r => r.json())
        .then(courses => {
            const sel = document.getElementById('modal_course');
            if (sel) {
                sel.innerHTML = '<option value="">Select Course</option>';
                (courses || []).forEach(c => {
                    sel.innerHTML += `<option value="${c.id}">${c.course_name}</option>`;
                });
            }
        });
}

function openAddSlotModal(weekday = 1, start = '08:30', end = '09:15') {
    const modal = document.getElementById('addSlotModal');
    if (document.getElementById('modal_weekday')) document.getElementById('modal_weekday').value = weekday;
    if (document.getElementById('modal_start')) document.getElementById('modal_start').value = start;
    if (document.getElementById('modal_end')) document.getElementById('modal_end').value = end;
    
    const curClass = document.getElementById('class_select')?.value;
    if (curClass) {
        document.getElementById('modal_class').value = curClass;
        loadModalSections(curClass, document.getElementById('section_select')?.value);
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeAddSlotModal() {
    const modal = document.getElementById('addSlotModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Auto-load sections
const currentClassId = {{ $class_id ?? 0 }};
const currentSectionId = {{ $section_id ?? 0 }};
if (currentClassId) {
    loadSections(currentClassId, currentSectionId);
}
</script>
@endsection
