@extends('layouts.app')

@section('content')
<style>
    /* ── Hide floating + button (FAB) on Attendance page to prevent card obstruction ── */
    #fab-group {
        display: none !important;
    }

    /* ── Core Attendance Theme & Spacing ── */
    .nsh-page-wrap {
        background-color: #F8FAFC;
        min-height: 100vh;
        padding-bottom: 140px !important;
    }
    .dark .nsh-page-wrap {
        background-color: #0F172A;
    }

    /* ── Filter Dropdowns ── */
    .nsh-select-input {
        background-color: #FFFFFF !important;
        border: 1.5px solid #CBD5E1 !important;
        color: #0F172A !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        border-radius: 12px !important;
        padding: 10px 36px 10px 14px !important;
        outline: none !important;
        width: 100% !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        transition: all 0.15s ease !important;
    }
    .dark .nsh-select-input {
        background-color: #1E293B !important;
        border-color: #475569 !important;
        color: #F8FAFC !important;
    }
    .nsh-select-input:focus {
        border-color: #4F46E5 !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15) !important;
    }

    /* ── Primary Filter Action Button ── */
    .nsh-btn-take {
        background-color: #1E1B4B !important;
        background: #1E1B4B !important;
        color: #FFFFFF !important;
        border: 1.5px solid #1E1B4B !important;
        font-weight: 800 !important;
        font-size: 13px !important;
        border-radius: 12px !important;
        padding: 10px 20px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        cursor: pointer !important;
        box-shadow: 0 2px 6px rgba(30, 27, 75, 0.25) !important;
        transition: all 0.15s ease !important;
    }
    .nsh-btn-take:hover {
        background-color: #0F172A !important;
        border-color: #0F172A !important;
        transform: translateY(-1px);
    }
    .nsh-btn-take:active {
        transform: scale(0.97);
    }

    /* ── ⚡ Speed Call Button ── */
    .nsh-btn-speed {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%) !important;
        background-color: #D97706 !important;
        color: #FFFFFF !important;
        border: 1.5px solid #B45309 !important;
        font-weight: 800 !important;
        font-size: 12.5px !important;
        border-radius: 12px !important;
        padding: 8px 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        cursor: pointer !important;
        box-shadow: 0 2px 6px rgba(217, 119, 6, 0.3) !important;
        white-space: nowrap !important;
        transition: all 0.15s ease !important;
    }
    .nsh-btn-speed:hover {
        filter: brightness(1.05);
        transform: translateY(-1px);
    }
    .nsh-btn-speed:active {
        transform: scale(0.96);
    }

    /* ── Toggle Switchers (Roll No / A-Z / List / Grid) ── */
    .nsh-toggle-btn {
        height: 36px !important;
        padding: 0 12px !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        cursor: pointer !important;
        border: 1.5px solid #CBD5E1 !important;
        background-color: #FFFFFF !important;
        color: #1E293B !important;
        transition: all 0.15s ease !important;
        gap: 5px !important;
        outline: none !important;
    }
    .dark .nsh-toggle-btn {
        background-color: #1E293B !important;
        border-color: #475569 !important;
        color: #E2E8F0 !important;
    }
    .nsh-toggle-btn:hover {
        background-color: #F1F5F9 !important;
        border-color: #94A3B8 !important;
    }
    .nsh-toggle-btn.active {
        background-color: #1E1B4B !important;
        background: #1E1B4B !important;
        border-color: #1E1B4B !important;
        color: #FFFFFF !important;
        box-shadow: 0 2px 6px rgba(30, 27, 75, 0.25) !important;
    }

    /* ── Quick Mark Action Buttons ── */
    .nsh-qm-present {
        background-color: #10B981 !important;
        color: #FFFFFF !important;
        border: 1.5px solid #059669 !important;
        font-weight: 800 !important;
        font-size: 12px !important;
        border-radius: 12px !important;
        padding: 7px 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        cursor: pointer !important;
        box-shadow: 0 2px 5px rgba(16, 185, 129, 0.25) !important;
        transition: all 0.12s ease !important;
    }
    .nsh-qm-present:active { transform: scale(0.96); }

    .nsh-qm-absent {
        background-color: #EF4444 !important;
        color: #FFFFFF !important;
        border: 1.5px solid #DC2626 !important;
        font-weight: 800 !important;
        font-size: 12px !important;
        border-radius: 12px !important;
        padding: 7px 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        cursor: pointer !important;
        box-shadow: 0 2px 5px rgba(239, 68, 68, 0.25) !important;
        transition: all 0.12s ease !important;
    }
    .nsh-qm-absent:active { transform: scale(0.96); }

    .nsh-qm-late {
        background-color: #F59E0B !important;
        color: #FFFFFF !important;
        border: 1.5px solid #D97706 !important;
        font-weight: 800 !important;
        font-size: 12px !important;
        border-radius: 12px !important;
        padding: 7px 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        cursor: pointer !important;
        box-shadow: 0 2px 5px rgba(245, 158, 11, 0.25) !important;
        transition: all 0.12s ease !important;
    }
    .nsh-qm-late:active { transform: scale(0.96); }

    /* ── Live Counter Badges ── */
    .nsh-stat-p {
        background-color: #ECFDF5 !important;
        color: #065F46 !important;
        border: 1.5px solid #10B981 !important;
        font-weight: 900 !important;
        font-size: 12px !important;
        padding: 4px 10px !important;
        border-radius: 8px !important;
    }
    .nsh-stat-a {
        background-color: #FFF1F2 !important;
        color: #9F1239 !important;
        border: 1.5px solid #EF4444 !important;
        font-weight: 900 !important;
        font-size: 12px !important;
        padding: 4px 10px !important;
        border-radius: 8px !important;
    }
    .nsh-stat-l {
        background-color: #FFFBEB !important;
        color: #92400E !important;
        border: 1.5px solid #F59E0B !important;
        font-weight: 900 !important;
        font-size: 12px !important;
        padding: 4px 10px !important;
        border-radius: 8px !important;
    }

    /* ── Student Grid Card ── */
    .nsh-student-card {
        background: #FFFFFF;
        border: 2px solid #E2E8F0;
        border-radius: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
        padding: 14px 10px 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        text-align: center;
        transition: all 0.18s ease;
        min-height: 190px;
        position: relative;
    }
    .dark .nsh-student-card {
        background: #1E293B;
        border-color: #334155;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    .nsh-student-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    /* Card Status Border Colors */
    .nsh-student-card.card-p {
        border-color: #10B981 !important;
        background: #F0FDF4 !important;
    }
    .dark .nsh-student-card.card-p {
        border-color: #059669 !important;
        background: #064E3B30 !important;
    }
    .nsh-student-card.card-a {
        border-color: #EF4444 !important;
        background: #FEF2F2 !important;
    }
    .dark .nsh-student-card.card-a {
        border-color: #DC2626 !important;
        background: #88133730 !important;
    }
    .nsh-student-card.card-l {
        border-color: #F59E0B !important;
        background: #FFFBEB !important;
    }
    .dark .nsh-student-card.card-l {
        border-color: #D97706 !important;
        background: #78350F30 !important;
    }

    /* Roll Badge */
    .nsh-roll-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 3px 10px;
        border-radius: 8px;
        font-size: 11.5px;
        font-weight: 800;
        background-color: #EEF2FF;
        color: #4338CA;
        border: 1.5px solid #C7D2FE;
        margin-bottom: 8px;
    }
    .dark .nsh-roll-pill {
        background-color: #312E81;
        color: #E0E7FF;
        border-color: #4338CA;
    }

    /* Avatar Disc */
    .nsh-avatar-disc {
        width: 54px;
        height: 54px;
        min-width: 54px;
        min-height: 54px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 900;
        color: #FFFFFF !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        border: 2.5px solid #FFFFFF;
        margin: 0 auto;
        user-select: none;
    }

    /* Student Name */
    .nsh-student-name {
        font-size: 13.5px;
        font-weight: 800;
        color: #0F172A;
        margin-top: 8px;
        margin-bottom: 12px;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        padding: 0 4px;
    }
    .dark .nsh-student-name {
        color: #F8FAFC;
    }

    /* ── High Contrast Circular Action Buttons (P / A / L) ── */
    .nsh-att-btn-group {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
    }

    .nsh-circle-btn {
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        min-height: 38px !important;
        border-radius: 50% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 14px !important;
        font-weight: 900 !important;
        cursor: pointer !important;
        user-select: none !important;
        transition: all 0.12s ease !important;
        padding: 0 !important;
        line-height: 1 !important;
        outline: none !important;
        border: 2px solid #94A3B8 !important;
        background-color: #FFFFFF !important;
        color: #1E293B !important;
        touch-action: manipulation !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
    }
    .dark .nsh-circle-btn {
        background-color: #1E293B !important;
        border-color: #64748B !important;
        color: #F8FAFC !important;
    }
    .nsh-circle-btn:hover {
        border-color: #475569 !important;
        background-color: #F8FAFC !important;
    }
    .nsh-circle-btn:active {
        transform: scale(0.9);
    }

    /* Active P (Present) - Emerald Green */
    .nsh-circle-btn.is-active-p {
        background-color: #10B981 !important;
        background: #10B981 !important;
        border-color: #059669 !important;
        color: #FFFFFF !important;
        font-weight: 900 !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.55) !important;
    }

    /* Active A (Absent) - Rose Red */
    .nsh-circle-btn.is-active-a {
        background-color: #EF4444 !important;
        background: #EF4444 !important;
        border-color: #DC2626 !important;
        color: #FFFFFF !important;
        font-weight: 900 !important;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.55) !important;
    }

    /* Active L (Late) - Amber Orange */
    .nsh-circle-btn.is-active-l {
        background-color: #F59E0B !important;
        background: #F59E0B !important;
        border-color: #D97706 !important;
        color: #FFFFFF !important;
        font-weight: 900 !important;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.55) !important;
    }

    /* Alphabet Divider */
    .nsh-letter-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 20px 0 12px;
    }
    .nsh-letter-header span {
        width: 34px;
        height: 34px;
        background-color: #1E1B4B;
        color: #FFFFFF;
        font-size: 15px;
        font-weight: 900;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(30, 27, 75, 0.25);
    }
    .nsh-letter-header::after {
        content: '';
        flex: 1;
        height: 1.5px;
        background-color: #E2E8F0;
    }
    .dark .nsh-letter-header::after {
        background-color: #334155;
    }
    /* ── Solid Pure White Speed Call Modal ── */
    #speedCallModal {
        background-color: rgba(15, 23, 42, 0.72) !important;
        backdrop-filter: blur(6px) !important;
        -webkit-backdrop-filter: blur(6px) !important;
    }
    #speedCallModal .nsh-modal-card {
        background-color: #FFFFFF !important;
        background: #FFFFFF !important;
        opacity: 1 !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        border: 2px solid #E2E8F0 !important;
        border-radius: 28px !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.05) !important;
    }
    .dark #speedCallModal .nsh-modal-card {
        background-color: #1E293B !important;
        background: #1E293B !important;
        border-color: #334155 !important;
    }

    /* Modal Navigation Buttons */
    .nsh-modal-nav-btn {
        background-color: #F8FAFC !important;
        border: 1.5px solid #CBD5E1 !important;
        color: #334155 !important;
        font-weight: 800 !important;
        font-size: 12px !important;
        border-radius: 12px !important;
        padding: 8px 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        outline: none !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        transition: all 0.15s ease !important;
    }
    .nsh-modal-nav-btn:hover {
        background-color: #E2E8F0 !important;
        border-color: #94A3B8 !important;
    }
    .nsh-modal-nav-btn:active {
        transform: scale(0.95);
    }
    .nsh-modal-nav-primary {
        background-color: #EEF2FF !important;
        border: 1.5px solid #C7D2FE !important;
        color: #4338CA !important;
        font-weight: 900 !important;
        box-shadow: 0 2px 6px rgba(67, 56, 202, 0.15) !important;
    }
    .nsh-modal-nav-primary:hover {
        background-color: #E0E7FF !important;
        border-color: #A5B4FC !important;
    }

    .nsh-modal-close-btn {
        background: transparent !important;
        border: none !important;
        outline: none !important;
        color: #94A3B8 !important;
        font-size: 22px !important;
        line-height: 1 !important;
        cursor: pointer !important;
        padding: 4px !important;
        border-radius: 50% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.15s ease !important;
    }
    .nsh-modal-close-btn:hover {
        color: #0F172A !important;
        background-color: #F1F5F9 !important;
    }
</style>

<div class="nsh-page-wrap p-3 sm:p-5 lg:p-7">

    @php
        $selectedDate = request()->query('date', $selected_date ?? date('Y-m-d'));
        $carbonDate = \Carbon\Carbon::parse($selectedDate);
        $prevDate = $carbonDate->copy()->subDay()->toDateString();
        $nextDate = $carbonDate->copy()->addDay()->toDateString();
        $formattedDate = $carbonDate->isToday() ? 'Today ' . $carbonDate->format('d M Y') : $carbonDate->format('D, d M Y');

        // Palette for student avatar circles
        $colorPalettes = [
            'linear-gradient(135deg, #6366F1 0%, #4F46E5 100%)',
            'linear-gradient(135deg, #EC4899 0%, #DB2777 100%)',
            'linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%)',
            'linear-gradient(135deg, #3B82F6 0%, #2563EB 100%)',
            'linear-gradient(135deg, #10B981 0%, #059669 100%)',
            'linear-gradient(135deg, #F59E0B 0%, #D97706 100%)',
            'linear-gradient(135deg, #06B6D4 0%, #0891B2 100%)',
            'linear-gradient(135deg, #14B8A6 0%, #0D9488 100%)',
            'linear-gradient(135deg, #F97316 0%, #EA580C 100%)',
            'linear-gradient(135deg, #64748B 0%, #475569 100%)',
        ];

        // Demo student roster matching reference mockup
        $demoRoster = [
            ['id' => 101, 'roll_no' => 1, 'name' => 'Amanda Kherr', 'status' => 'P'],
            ['id' => 102, 'roll_no' => 2, 'name' => 'Angel Johnson', 'status' => 'A'],
            ['id' => 103, 'roll_no' => 3, 'name' => 'Alexander Kherr', 'status' => 'L'],
            ['id' => 104, 'roll_no' => 4, 'name' => 'Austin Kherr', 'status' => 'P'],
            ['id' => 105, 'roll_no' => 5, 'name' => 'Aada Kherr', 'status' => 'P'],
            ['id' => 106, 'roll_no' => 6, 'name' => 'Amelia Kherr', 'status' => 'P'],
            ['id' => 107, 'roll_no' => 7, 'name' => 'Anthony Davis', 'status' => 'P'],
            ['id' => 108, 'roll_no' => 8, 'name' => 'Arthur Miller', 'status' => 'P'],
            ['id' => 109, 'roll_no' => 9, 'name' => 'Ava Wilson', 'status' => 'A'],
            ['id' => 110, 'roll_no' => 10, 'name' => 'Ayush Sharma', 'status' => 'P'],
            ['id' => 201, 'roll_no' => 11, 'name' => 'Babak Kherr', 'status' => 'P'],
            ['id' => 202, 'roll_no' => 12, 'name' => 'Baha Johnson', 'status' => 'L'],
            ['id' => 203, 'roll_no' => 13, 'name' => 'Babette Kherr', 'status' => 'P'],
            ['id' => 204, 'roll_no' => 14, 'name' => 'Badan Kherr', 'status' => 'P'],
            ['id' => 205, 'roll_no' => 15, 'name' => 'Bailee Kherr', 'status' => 'P'],
            ['id' => 206, 'roll_no' => 16, 'name' => 'Bikash Thapa', 'status' => 'P'],
            ['id' => 207, 'roll_no' => 17, 'name' => 'Binod Rai', 'status' => 'P'],
            ['id' => 208, 'roll_no' => 18, 'name' => 'Balbina Kherr', 'status' => 'P'],
            ['id' => 209, 'roll_no' => 19, 'name' => 'Bipin Shrestha', 'status' => 'P'],
            ['id' => 210, 'roll_no' => 20, 'name' => 'Barsha Gurung', 'status' => 'P'],
        ];

        $allStudents = [];
        $totalCount = 0;

        if (isset($student_list) && count($student_list) > 0) {
            $currRoll = 1;
            foreach ($student_list as $st) {
                $fName = $st->student->first_name ?? ($st->first_name ?? 'Student');
                $lName = $st->student->last_name ?? ($st->last_name ?? '');
                $fullName = trim($fName . ' ' . $lName);
                $initial = strtoupper(substr($fName, 0, 1)) ?: 'A';
                $stId = $st->student_id ?? $st->id;
                $roll = $st->roll_no ?? ($st->student->roll_no ?? ($st->academic_info?->roll_no ?? $currRoll++));
                
                // Get 2-letter initials
                $nameParts = explode(' ', $fullName);
                $initials = strtoupper(substr($nameParts[0] ?? 'S', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
                if (strlen($initials) < 2) {
                    $initials = strtoupper(substr($fullName, 0, 2));
                }

                $status = 'P';
                if (isset($existing_attendances) && $existing_attendances->has($stId)) {
                    $attRecord = $existing_attendances->get($stId);
                    if ($attRecord->late_minutes > 0) {
                        $status = 'L';
                    } elseif ($attRecord->status === 'on') {
                        $status = 'P';
                    } else {
                        $status = 'A';
                    }
                }

                $allStudents[] = [
                    'id'       => $stId,
                    'roll_no'  => $roll,
                    'name'     => $fullName,
                    'initial'  => $initial,
                    'initials' => $initials,
                    'status'   => $status,
                ];
                $totalCount++;
            }
        }

        if (empty($allStudents)) {
            foreach ($demoRoster as $dr) {
                $nameParts = explode(' ', $dr['name']);
                $initials = strtoupper(substr($nameParts[0] ?? 'S', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
                if (strlen($initials) < 2) {
                    $initials = strtoupper(substr($dr['name'], 0, 2));
                }
                $allStudents[] = array_merge($dr, [
                    'initial'  => strtoupper(substr($dr['name'], 0, 1)) ?: 'A',
                    'initials' => $initials,
                ]);
            }
            $totalCount = count($allStudents);
        }

        // Group by Alphabet
        $alphaGroups = [];
        foreach ($allStudents as $st) {
            $alphaGroups[$st['initial']][] = $st;
        }
        ksort($alphaGroups);

        // Sort by Roll Number
        $rollSorted = $allStudents;
        usort($rollSorted, function($a, $b) {
            return ($a['roll_no'] ?? 0) <=> ($b['roll_no'] ?? 0);
        });
    @endphp

    {{-- Header Row --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight leading-none">
                    Attendance
                </h1>
                <span class="bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-xs font-black px-2.5 py-1 rounded-lg border border-indigo-200 dark:border-indigo-800">
                    {{ $carbonDate->format('Y') }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-semibold">
                Mark and track student attendance records
            </p>
        </div>

        {{-- Date Navigation Picker (< 📅 Today 10 Oct 2026 >) --}}
        <div class="inline-flex items-center justify-between sm:justify-start gap-1 bg-white dark:bg-slate-800 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <a href="?{{ http_build_query(array_merge(request()->query(), ['date' => $prevDate])) }}" 
               class="text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 text-sm p-1 transition cursor-pointer" title="Previous Day">
                <i class="bi bi-chevron-left font-bold"></i>
            </a>
            
            <label for="att_custom_date" class="inline-flex items-center gap-2 cursor-pointer select-none text-xs font-extrabold text-slate-800 dark:text-slate-100 px-2">
                <i class="bi bi-calendar3 text-indigo-600 dark:text-indigo-400 text-sm"></i>
                <span>{{ $formattedDate }}</span>
                <input type="date" id="att_custom_date" value="{{ $selectedDate }}" onchange="changeAttendanceDate(this.value)" class="sr-only">
            </label>

            <a href="?{{ http_build_query(array_merge(request()->query(), ['date' => $nextDate])) }}" 
               class="text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 text-sm p-1 transition cursor-pointer" title="Next Day">
                <i class="bi bi-chevron-right font-bold"></i>
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('attendance.create.show') }}" id="attendanceFilterForm" class="bg-white dark:bg-slate-800 p-3.5 sm:p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm mb-4">
        <input type="hidden" name="date" value="{{ $selectedDate }}">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            
            {{-- Dropdown Filters & Take Attendance Button --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:flex lg:flex-wrap items-center gap-2.5 flex-1">
                
                {{-- Select Class --}}
                <div class="relative min-w-[140px]">
                    <select name="class_id" id="filter_class_id" onchange="loadSectionsForClass(this.value)" class="nsh-select-input cursor-pointer">
                        <option value="">Select Class</option>
                        @if(isset($school_classes))
                            @foreach ($school_classes as $sc)
                                <option value="{{ $sc->id }}" {{ (request()->query('class_id') == $sc->id || (isset($school_class) && $school_class && $school_class->id == $sc->id)) ? 'selected' : '' }}>
                                    {{ $sc->class_name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <i class="bi bi-chevron-down absolute right-3.5 top-3.5 text-xs text-slate-500 pointer-events-none font-bold"></i>
                </div>

                {{-- Select Subject / Course --}}
                <div class="relative min-w-[140px]">
                    <select name="course_id" id="filter_course_id" class="nsh-select-input cursor-pointer">
                        <option value="0">Select Subject</option>
                        @if(isset($school_class) && $school_class->courses)
                            @foreach($school_class->courses as $c)
                                <option value="{{ $c->id }}" {{ request()->query('course_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->course_name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <i class="bi bi-chevron-down absolute right-3.5 top-3.5 text-xs text-slate-500 pointer-events-none font-bold"></i>
                </div>

                {{-- Select Section --}}
                <div class="relative min-w-[140px]">
                    <select name="section_id" id="filter_section_id" class="nsh-select-input cursor-pointer">
                        <option value="0">Select Section</option>
                        @if(isset($school_class) && $school_class->sections)
                            @foreach($school_class->sections as $sec)
                                <option value="{{ $sec->id }}" {{ (request()->query('section_id') == $sec->id || (isset($school_section) && $school_section && $school_section->id == $sec->id)) ? 'selected' : '' }}>
                                    Section {{ $sec->section_name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <i class="bi bi-chevron-down absolute right-3.5 top-3.5 text-xs text-slate-500 pointer-events-none font-bold"></i>
                </div>

                {{-- Take Attendance Action Button (Solid Deep Indigo / Navy) --}}
                <button type="submit" class="nsh-btn-take">
                    <i class="bi bi-arrow-repeat text-indigo-300 text-sm"></i>
                    <span>Take Attendance</span>
                </button>

            </div>

            {{-- Toolbar: Speed Call, Sort, View Switcher --}}
            <div class="flex items-center justify-between sm:justify-end gap-2 overflow-x-auto pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100 dark:border-slate-700">
                
                {{-- ⚡ Speed Call Button --}}
                <button type="button" onclick="startRapidCallingMode()" class="nsh-btn-speed" title="Start Ultra-Fast 1-by-1 Roll Call Mode">
                    <i class="bi bi-lightning-charge-fill text-amber-200"></i>
                    <span>Speed Call</span>
                </button>

                {{-- Order Toggle (Roll No vs A-Z) --}}
                <div class="inline-flex items-center p-1 bg-slate-100 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 gap-1">
                    <button type="button" id="btnSortRoll" onclick="toggleSortMode('roll')" class="nsh-toggle-btn active" title="Order by Roll Number">
                        <i class="bi bi-sort-numeric-down"></i> Roll No.
                    </button>
                    <button type="button" id="btnSortAlpha" onclick="toggleSortMode('alpha')" class="nsh-toggle-btn" title="Group by Alphabet">
                        <i class="bi bi-sort-alpha-down"></i> A-Z
                    </button>
                </div>

                {{-- View Switcher (List vs Grid) --}}
                <div class="inline-flex items-center p-1 bg-slate-100 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 gap-1">
                    <button type="button" id="btnAttList" onclick="toggleAttendanceView('list')" class="nsh-toggle-btn" title="List View">
                        <i class="bi bi-list-task text-base"></i>
                    </button>
                    <button type="button" id="btnAttGrid" onclick="toggleAttendanceView('grid')" class="nsh-toggle-btn active" title="Grid Card View">
                        <i class="bi bi-grid-3x3-gap-fill text-base"></i>
                    </button>
                </div>
            </div>

        </div>
    </form>

    {{-- Main Attendance Form --}}
    <form action="{{ route('attendances.store') }}" method="POST" id="mainAttendanceForm">
        @csrf
        <input type="hidden" name="session_id" value="{{ $current_school_session_id ?? 1 }}">
        <input type="hidden" name="class_id" value="{{ request()->query('class_id', 1) }}">
        <input type="hidden" name="section_id" value="{{ request()->query('section_id', 0) }}">
        <input type="hidden" name="course_id" value="{{ request()->query('course_id', 0) }}">
        <input type="hidden" name="date" value="{{ $selectedDate }}">

        {{-- Quick Mark & Live Stats Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5 bg-white dark:bg-slate-800 p-3.5 sm:p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
            
            {{-- Quick Mark Buttons --}}
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                <span class="text-slate-600 dark:text-slate-300 font-extrabold mr-1">Quick Mark:</span>
                
                <button type="button" onclick="markAllStatus('P')" class="nsh-qm-present">
                    <i class="bi bi-check-all text-base"></i> All Present (P)
                </button>
                
                <button type="button" onclick="markAllStatus('A')" class="nsh-qm-absent">
                    <i class="bi bi-x-circle text-sm"></i> All Absent (A)
                </button>
                
                <button type="button" onclick="markAllStatus('L')" class="nsh-qm-late">
                    <i class="bi bi-clock text-sm"></i> All Late (L)
                </button>
            </div>

            {{-- Live Badges --}}
            <div class="flex items-center gap-2">
                <span id="attStatsPresent" class="nsh-stat-p">
                    P: 0
                </span>
                <span id="attStatsAbsent" class="nsh-stat-a">
                    A: 0
                </span>
                <span id="attStatsLate" class="nsh-stat-l">
                    L: 0
                </span>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- VIEW 1: Grid Mode (Responsive 2 cols on mobile, 5 cols on desktop)        --}}
        {{-- ========================================================================= --}}
        <div id="attendanceGridView">
            
            {{-- MODE A: ROLL NUMBER ORDER --}}
            <div id="gridRollOrderContainer">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 mb-6">
                    @foreach($rollSorted as $idx => $st)
                        <input type="hidden" name="student_ids[]" value="{{ $st['id'] }}">
                        <input type="hidden" id="status_input_{{ $st['id'] }}" name="status[{{ $st['id'] }}]" value="{{ $st['status'] }}">

                        <div class="nsh-student-card card-{{ strtolower($st['status']) }}" id="card_{{ $st['id'] }}">
                            
                            {{-- Roll Number Badge --}}
                            <span class="nsh-roll-pill">
                                Roll #{{ $st['roll_no'] }}
                            </span>

                            {{-- Student Avatar (Clean Gradient Initials Disc) --}}
                            <div class="nsh-avatar-disc" style="background: {{ $colorPalettes[$idx % count($colorPalettes)] }};">
                                {{ $st['initials'] }}
                            </div>

                            {{-- Student Name --}}
                            <p class="nsh-student-name" title="{{ $st['name'] }}">
                                {{ $st['name'] }}
                            </p>

                            {{-- P / A / L Action Buttons --}}
                            <div class="nsh-att-btn-group">
                                <button type="button" 
                                        id="btn_p_{{ $st['id'] }}"
                                        onclick="setStudentStatus({{ $st['id'] }}, 'P')"
                                        class="nsh-circle-btn {{ $st['status'] === 'P' ? 'is-active-p' : '' }}"
                                        title="Present">
                                    P
                                </button>
                                
                                <button type="button" 
                                        id="btn_a_{{ $st['id'] }}"
                                        onclick="setStudentStatus({{ $st['id'] }}, 'A')"
                                        class="nsh-circle-btn {{ $st['status'] === 'A' ? 'is-active-a' : '' }}"
                                        title="Absent">
                                    A
                                </button>

                                <button type="button" 
                                        id="btn_l_{{ $st['id'] }}"
                                        onclick="setStudentStatus({{ $st['id'] }}, 'L')"
                                        class="nsh-circle-btn {{ $st['status'] === 'L' ? 'is-active-l' : '' }}"
                                        title="Late">
                                    L
                                </button>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>

            {{-- MODE B: ALPHABETICAL ORDER (Grouped by A, B, C...) --}}
            <div id="gridAlphaOrderContainer" class="hidden">
                @foreach($alphaGroups as $letter => $students)
                    <div class="nsh-letter-header">
                        <span>{{ $letter }}</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 mb-6">
                        @foreach($students as $idx => $st)
                            <div class="nsh-student-card card-{{ strtolower($st['status']) }}" id="alpha_card_{{ $st['id'] }}">
                                
                                {{-- Roll Number Badge --}}
                                <span class="nsh-roll-pill">
                                    Roll #{{ $st['roll_no'] }}
                                </span>

                                {{-- Student Avatar --}}
                                <div class="nsh-avatar-disc" style="background: {{ $colorPalettes[$idx % count($colorPalettes)] }};">
                                    {{ $st['initials'] }}
                                </div>

                                {{-- Student Name --}}
                                <p class="nsh-student-name" title="{{ $st['name'] }}">
                                    {{ $st['name'] }}
                                </p>

                                {{-- P / A / L Action Buttons --}}
                                <div class="nsh-att-btn-group">
                                    <button type="button" 
                                            id="alpha_btn_p_{{ $st['id'] }}"
                                            onclick="setStudentStatus({{ $st['id'] }}, 'P')"
                                            class="nsh-circle-btn {{ $st['status'] === 'P' ? 'is-active-p' : '' }}"
                                            title="Present">
                                        P
                                    </button>
                                    
                                    <button type="button" 
                                            id="alpha_btn_a_{{ $st['id'] }}"
                                            onclick="setStudentStatus({{ $st['id'] }}, 'A')"
                                            class="nsh-circle-btn {{ $st['status'] === 'A' ? 'is-active-a' : '' }}"
                                            title="Absent">
                                        A
                                    </button>

                                    <button type="button" 
                                            id="alpha_btn_l_{{ $st['id'] }}"
                                            onclick="setStudentStatus({{ $st['id'] }}, 'L')"
                                            class="nsh-circle-btn {{ $st['status'] === 'L' ? 'is-active-l' : '' }}"
                                            title="Late">
                                        L
                                    </button>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- VIEW 2: Table List Mode Alternative                                       --}}
        {{-- ========================================================================= --}}
        <div id="attendanceListView" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden mb-6 hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-extrabold">
                            <th class="py-3 px-3 w-16 text-center">Roll #</th>
                            <th class="py-3 px-3">Student</th>
                            <th class="py-3 px-3 text-center w-48">Attendance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($rollSorted as $idx => $st)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition">
                            <td class="py-3 px-3 text-center font-bold text-indigo-600 dark:text-indigo-400">
                                <span class="bg-indigo-50 dark:bg-indigo-950 px-2 py-0.5 rounded-md font-mono text-xs font-bold border border-indigo-200 dark:border-indigo-800">
                                    {{ $st['roll_no'] }}
                                </span>
                            </td>
                            <td class="py-3 px-3 flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs text-white shadow-2xs" style="background: {{ $colorPalettes[$idx % count($colorPalettes)] }};">
                                    {{ $st['initials'] }}
                                </div>
                                <span class="font-extrabold text-slate-800 dark:text-slate-100">{{ $st['name'] }}</span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" onclick="setStudentStatus({{ $st['id'] }}, 'P')"
                                            id="list_btn_p_{{ $st['id'] }}"
                                            style="{{ $st['status'] === 'P' ? 'background-color:#10B981!important;color:#fff!important;' : 'background-color:#F1F5F9;color:#334155;border:1.5px solid #CBD5E1;' }}"
                                            class="px-3 py-1 rounded-lg text-xs font-extrabold cursor-pointer transition">
                                        P
                                    </button>
                                    <button type="button" onclick="setStudentStatus({{ $st['id'] }}, 'A')"
                                            id="list_btn_a_{{ $st['id'] }}"
                                            style="{{ $st['status'] === 'A' ? 'background-color:#EF4444!important;color:#fff!important;' : 'background-color:#F1F5F9;color:#334155;border:1.5px solid #CBD5E1;' }}"
                                            class="px-3 py-1 rounded-lg text-xs font-extrabold cursor-pointer transition">
                                        A
                                    </button>
                                    <button type="button" onclick="setStudentStatus({{ $st['id'] }}, 'L')"
                                            id="list_btn_l_{{ $st['id'] }}"
                                            style="{{ $st['status'] === 'L' ? 'background-color:#F59E0B!important;color:#fff!important;' : 'background-color:#F1F5F9;color:#334155;border:1.5px solid #CBD5E1;' }}"
                                            class="px-3 py-1 rounded-lg text-xs font-extrabold cursor-pointer transition">
                                        L
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Save Button Bar in standard document flow --}}
        <div class="mt-6 mb-8 bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-600 dark:text-slate-300 font-medium">
                <span class="font-extrabold text-slate-900 dark:text-slate-100 text-sm">{{ $totalCount }}</span> Students on record for <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $formattedDate }}</span>
            </div>

            <button type="submit" 
                    style="background-color: #1E1B4B !important; background: #1E1B4B !important; color: #FFFFFF !important; font-weight: 800 !important;"
                    class="w-full sm:w-auto px-8 py-3 rounded-xl text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer active:scale-98">
                <i class="bi bi-check2-circle text-lg text-emerald-400"></i>
                <span style="color: #FFFFFF !important;">Save Attendance Records</span>
            </button>
        </div>

    </form>

</div>

{{-- ⚡ Speed Call Modal --}}
<div id="speedCallModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="nsh-modal-card max-w-sm w-full p-6 text-center">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
            <span class="text-xs font-black text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                <i class="bi bi-lightning-charge-fill text-amber-500"></i> Speed Roll Call
            </span>
            <button type="button" onclick="closeRapidCallingMode()" class="nsh-modal-close-btn" title="Close Speed Call">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>

        <div id="speedStudentContainer">
            <span id="speedRollBadge" class="nsh-roll-pill text-sm px-3 py-1 mb-2 font-mono">
                Roll #1
            </span>
            
            <div id="speedAvatar" class="w-20 h-20 rounded-full flex items-center justify-center font-black text-2xl text-white mx-auto my-3 border-4 border-white shadow-lg user-select-none">
                AS
            </div>

            <h3 id="speedName" class="text-xl font-black text-slate-900 dark:text-slate-100 mb-1 tracking-tight">
                Student Name
            </h3>
            <p id="speedCounter" class="text-xs text-slate-400 font-bold mb-6">Student 1 of 20</p>

            {{-- Big Tap Buttons --}}
            <div class="grid grid-cols-3 gap-3">
                <button type="button" onclick="speedCallMark('P')" 
                        style="background-color: #10B981 !important; color: #FFFFFF !important; font-weight: 900 !important; border: 1.5px solid #059669 !important;"
                        class="py-3.5 rounded-2xl text-sm shadow-md active:scale-95 flex flex-col items-center gap-1 cursor-pointer transition">
                    <i class="bi bi-check-circle-fill text-xl"></i> Present
                </button>
                <button type="button" onclick="speedCallMark('A')" 
                        style="background-color: #EF4444 !important; color: #FFFFFF !important; font-weight: 900 !important; border: 1.5px solid #DC2626 !important;"
                        class="py-3.5 rounded-2xl text-sm shadow-md active:scale-95 flex flex-col items-center gap-1 cursor-pointer transition">
                    <i class="bi bi-x-circle-fill text-xl"></i> Absent
                </button>
                <button type="button" onclick="speedCallMark('L')" 
                        style="background-color: #F59E0B !important; color: #FFFFFF !important; font-weight: 900 !important; border: 1.5px solid #D97706 !important;"
                        class="py-3.5 rounded-2xl text-sm shadow-md active:scale-95 flex flex-col items-center gap-1 cursor-pointer transition">
                    <i class="bi bi-clock-fill text-xl"></i> Late
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 gap-2">
            <button type="button" onclick="speedCallNav(-1)" class="nsh-modal-nav-btn">
                <i class="bi bi-arrow-left me-1"></i> Previous
            </button>
            <button type="button" onclick="closeRapidCallingMode()" class="nsh-modal-nav-btn nsh-modal-nav-primary">
                Finish & View Grid
            </button>
            <button type="button" onclick="speedCallNav(1)" class="nsh-modal-nav-btn">
                Skip <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>

    </div>
</div>

<script>
const studentsData = @json($rollSorted);
const avatarColors = @json($colorPalettes);
let currentSpeedIndex = 0;

// Toggle student attendance P/A/L
function setStudentStatus(studentId, status) {
    if (navigator.vibrate) {
        navigator.vibrate(15);
    }

    const input = document.getElementById(`status_input_${studentId}`);
    if (input) input.value = status;

    // Update Roll Order Grid buttons
    const btnP = document.getElementById(`btn_p_${studentId}`);
    const btnA = document.getElementById(`btn_a_${studentId}`);
    const btnL = document.getElementById(`btn_l_${studentId}`);

    if (btnP) btnP.className = 'nsh-circle-btn' + (status === 'P' ? ' is-active-p' : '');
    if (btnA) btnA.className = 'nsh-circle-btn' + (status === 'A' ? ' is-active-a' : '');
    if (btnL) btnL.className = 'nsh-circle-btn' + (status === 'L' ? ' is-active-l' : '');

    // Card background highlight
    const card = document.getElementById(`card_${studentId}`);
    if (card) {
        card.className = 'nsh-student-card card-' + status.toLowerCase();
    }
    const alphaCard = document.getElementById(`alpha_card_${studentId}`);
    if (alphaCard) {
        alphaCard.className = 'nsh-student-card card-' + status.toLowerCase();
    }

    // Update Alpha Order Grid buttons
    const alphaP = document.getElementById(`alpha_btn_p_${studentId}`);
    const alphaA = document.getElementById(`alpha_btn_a_${studentId}`);
    const alphaL = document.getElementById(`alpha_btn_l_${studentId}`);

    if (alphaP) alphaP.className = 'nsh-circle-btn' + (status === 'P' ? ' is-active-p' : '');
    if (alphaA) alphaA.className = 'nsh-circle-btn' + (status === 'A' ? ' is-active-a' : '');
    if (alphaL) alphaL.className = 'nsh-circle-btn' + (status === 'L' ? ' is-active-l' : '');

    // Update List View buttons
    const listP = document.getElementById(`list_btn_p_${studentId}`);
    const listA = document.getElementById(`list_btn_a_${studentId}`);
    const listL = document.getElementById(`list_btn_l_${studentId}`);

    if (listP) listP.style.cssText = status === 'P' ? 'background-color:#10B981!important;color:#fff!important;' : 'background-color:#F1F5F9;color:#334155;border:1.5px solid #CBD5E1;';
    if (listA) listA.style.cssText = status === 'A' ? 'background-color:#EF4444!important;color:#fff!important;' : 'background-color:#F1F5F9;color:#334155;border:1.5px solid #CBD5E1;';
    if (listL) listL.style.cssText = status === 'L' ? 'background-color:#F59E0B!important;color:#fff!important;' : 'background-color:#F1F5F9;color:#334155;border:1.5px solid #CBD5E1;';

    updateStatsSummary();
}

// Quick Mark All P/A/L
function markAllStatus(status) {
    if (navigator.vibrate) navigator.vibrate(20);
    const inputs = document.querySelectorAll('input[name^="status["]');
    inputs.forEach(input => {
        const studentId = input.id.replace('status_input_', '');
        setStudentStatus(studentId, status);
    });
}

// Update summary counts
function updateStatsSummary() {
    let pCount = 0;
    let aCount = 0;
    let lCount = 0;

    const inputs = document.querySelectorAll('input[name^="status["]');
    inputs.forEach(input => {
        if (input.value === 'P' || input.value === 'on' || input.value === 'present') pCount++;
        else if (input.value === 'A' || input.value === 'off' || input.value === 'absent') aCount++;
        else if (input.value === 'L' || input.value === 'late') lCount++;
    });

    const statP = document.getElementById('attStatsPresent');
    const statA = document.getElementById('attStatsAbsent');
    const statL = document.getElementById('attStatsLate');

    if (statP) statP.textContent = `P: ${pCount}`;
    if (statA) statA.textContent = `A: ${aCount}`;
    if (statL) statL.textContent = `L: ${lCount}`;
}

// Toggle Sort Mode (Roll No vs Alpha A-Z)
function toggleSortMode(mode) {
    const rollContainer = document.getElementById('gridRollOrderContainer');
    const alphaContainer = document.getElementById('gridAlphaOrderContainer');
    const btnRoll = document.getElementById('btnSortRoll');
    const btnAlpha = document.getElementById('btnSortAlpha');

    if (mode === 'alpha') {
        rollContainer.classList.add('hidden');
        alphaContainer.classList.remove('hidden');

        btnAlpha.className = "nsh-toggle-btn active";
        btnRoll.className = "nsh-toggle-btn";
    } else {
        alphaContainer.classList.add('hidden');
        rollContainer.classList.remove('hidden');

        btnRoll.className = "nsh-toggle-btn active";
        btnAlpha.className = "nsh-toggle-btn";
    }
}

// View toggle (Grid vs List)
function toggleAttendanceView(mode) {
    const gridView = document.getElementById('attendanceGridView');
    const listView = document.getElementById('attendanceListView');
    const btnGrid = document.getElementById('btnAttGrid');
    const btnList = document.getElementById('btnAttList');

    if (mode === 'list') {
        gridView.classList.add('hidden');
        listView.classList.remove('hidden');

        btnList.className = "nsh-toggle-btn active";
        btnGrid.className = "nsh-toggle-btn";
    } else {
        listView.classList.add('hidden');
        gridView.classList.remove('hidden');

        btnGrid.className = "nsh-toggle-btn active";
        btnList.className = "nsh-toggle-btn";
    }
}

// ⚡ SPEED CALL MODE
function startRapidCallingMode() {
    if (!studentsData || studentsData.length === 0) return;
    currentSpeedIndex = 0;
    renderSpeedStudent();
    document.getElementById('speedCallModal').classList.remove('hidden');
}

function closeRapidCallingMode() {
    document.getElementById('speedCallModal').classList.add('hidden');
}

function renderSpeedStudent() {
    const st = studentsData[currentSpeedIndex];
    if (!st) return;

    document.getElementById('speedRollBadge').textContent = `Roll #${st.roll_no}`;
    const avatarEl = document.getElementById('speedAvatar');
    avatarEl.textContent = st.initials;
    avatarEl.style.background = avatarColors[currentSpeedIndex % avatarColors.length];
    document.getElementById('speedName').textContent = st.name;
    document.getElementById('speedCounter').textContent = `Student ${currentSpeedIndex + 1} of ${studentsData.length}`;
}

function speedCallMark(status) {
    const st = studentsData[currentSpeedIndex];
    if (st) {
        setStudentStatus(st.id, status);
    }

    if (currentSpeedIndex < studentsData.length - 1) {
        currentSpeedIndex++;
        renderSpeedStudent();
    } else {
        closeRapidCallingMode();
    }
}

function speedCallNav(dir) {
    const newIdx = currentSpeedIndex + dir;
    if (newIdx >= 0 && newIdx < studentsData.length) {
        currentSpeedIndex = newIdx;
        renderSpeedStudent();
    }
}

function changeAttendanceDate(newDate) {
    if (!newDate) return;
    const url = new URL(window.location.href);
    url.searchParams.set('date', newDate);
    window.location.href = url.toString();
}

function loadSectionsForClass(classId) {
    if (!classId) return;
    fetch(`/sections?class_id=${classId}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('filter_section_id');
            sel.innerHTML = '<option value="0">Select Section</option>';
            (data.sections || []).forEach(s => {
                sel.innerHTML += `<option value="${s.id}">Section ${s.section_name}</option>`;
            });
        });

    fetch(`/ajax/courses-by-class?class_id=${classId}`)
        .then(r => r.json())
        .then(courses => {
            const sel = document.getElementById('filter_course_id');
            sel.innerHTML = '<option value="0">Select Subject</option>';
            (courses || []).forEach(c => {
                sel.innerHTML += `<option value="${c.id}">${c.course_name}</option>`;
            });
        });
}

document.addEventListener('DOMContentLoaded', updateStatsSummary);
</script>
@endsection
