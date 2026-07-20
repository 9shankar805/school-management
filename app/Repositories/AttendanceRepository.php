<?php

namespace App\Repositories;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Interfaces\AttendanceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceRepository implements AttendanceInterface
{
    // ──────────────────────────────────────────────────────────────────────────
    // EXISTING METHODS (unchanged — kept for backward compatibility)
    // ──────────────────────────────────────────────────────────────────────────

    public function saveAttendance($request): void
    {
        try {
            $input = $this->prepareInput($request);
            Attendance::insert($input);
        } catch (\Exception $e) {
            throw new \Exception('Failed to save attendance. ' . $e->getMessage());
        }
    }

    public function prepareInput($request): array
    {
        $input = [];
        $now   = Carbon::now()->toDateTimeString();
        $date  = Carbon::today()->toDateString();

        foreach ($request['student_ids'] as $student_id) {
            $input[] = [
                'status'        => isset($request['status'][$student_id]) ? $request['status'][$student_id] : 'off',
                'class_id'      => $request['class_id'],
                'student_id'    => $student_id,
                'section_id'    => $request['section_id'],
                'course_id'     => $request['course_id'],
                'session_id'    => $request['session_id'],
                'date'          => $date,
                'late_minutes'  => 0,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        return $input;
    }

    public function getSectionAttendance($class_id, $section_id, $session_id)
    {
        return Attendance::with('student')
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->whereDate('date', Carbon::today())
            ->get();
    }

    public function getCourseAttendance($class_id, $course_id, $session_id)
    {
        return Attendance::with('student')
            ->where('class_id', $class_id)
            ->where('course_id', $course_id)
            ->where('session_id', $session_id)
            ->whereDate('date', Carbon::today())
            ->get();
    }

    public function getStudentAttendance($session_id, $student_id)
    {
        return Attendance::with(['section', 'course'])
            ->where('student_id', $student_id)
            ->where('session_id', $session_id)
            ->orderByDesc('date')
            ->get();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DATE-AWARE QUERIES
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * All attendance for a given date, optionally filtered by class & session.
     */
    public function getAttendanceByDate(string $date, ?int $classId = null, ?int $sessionId = null)
    {
        $query = Attendance::with(['student', 'course', 'schoolClass', 'section'])
            ->whereDate('date', $date);

        if ($classId)   $query->where('class_id', $classId);
        if ($sessionId) $query->where('session_id', $sessionId);

        return $query->orderBy('student_id')->get();
    }

    /**
     * Per-student monthly summary.
     * Returns an array of rows:
     *   ['student', 'total', 'present', 'absent', 'late', 'percentage']
     */
    public function getMonthlyStudentSummary(int $sessionId, int $classId, int $year, int $month): array
    {
        $records = Attendance::with('student')
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        // Group by student
        return $records->groupBy('student_id')->map(function (Collection $rows) {
            $total   = $rows->count();
            $present = $rows->where('status', 'on')->count();
            $absent  = $total - $present;
            $late    = $rows->where('status', 'on')->where('late_minutes', '>', 0)->count();

            return [
                'student'    => $rows->first()->student,
                'total'      => $total,
                'present'    => $present,
                'absent'     => $absent,
                'late'       => $late,
                'percentage' => $total > 0 ? round($present / $total * 100, 1) : 0,
            ];
        })->values()->toArray();
    }

    /**
     * Students below the attendance threshold for the current session.
     * Returns rows: ['student_id', 'student', 'class', 'present', 'total', 'percentage'].
     */
    public function getShortageStudents(int $sessionId, float $threshold = 75.0): array
    {
        // Aggregate totals per student
        $rows = DB::table('attendances')
            ->select(
                'student_id',
                'class_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'on' THEN 1 ELSE 0 END) as present")
            )
            ->where('session_id', $sessionId)
            ->groupBy('student_id', 'class_id')
            ->havingRaw('(SUM(CASE WHEN status = \'on\' THEN 1 ELSE 0 END) / COUNT(*)) * 100 < ?', [$threshold])
            ->get();

        return $rows->map(function ($row) {
            $row->percentage = $row->total > 0 ? round($row->present / $row->total * 100, 1) : 0;
            $row->student    = \App\Models\User::find($row->student_id);
            $row->schoolClass = \App\Models\SchoolClass::find($row->class_id);
            return (array) $row;
        })->toArray();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ANALYTICS
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Daily present/absent counts for the last N days.
     * Returns array of ['date', 'present', 'absent'] sorted ascending by date.
     */
    public function getDailyTrend(int $sessionId, int $classId, int $days = 7): array
    {
        $from = Carbon::today()->subDays($days - 1)->toDateString();
        $to   = Carbon::today()->toDateString();

        $rows = DB::table('attendances')
            ->select(
                DB::raw('DATE(date) as day'),
                DB::raw("SUM(CASE WHEN status = 'on' THEN 1 ELSE 0 END) as present"),
                DB::raw("SUM(CASE WHEN status = 'off' THEN 1 ELSE 0 END) as absent")
            )
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->whereBetween('date', [$from, $to])
            ->groupByRaw('DATE(date)')
            ->orderByRaw('DATE(date)')
            ->get()
            ->keyBy('day');

        // Fill in zeros for days with no records
        $result = [];
        foreach (CarbonPeriod::create($from, $to) as $day) {
            $d          = $day->toDateString();
            $result[]   = [
                'date'    => $d,
                'present' => (int) ($rows[$d]->present ?? 0),
                'absent'  => (int) ($rows[$d]->absent  ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Today's overall summary across all classes in a session.
     * Returns ['present', 'absent', 'late', 'total'].
     */
    public function getTodaySummary(int $sessionId): array
    {
        $row = DB::table('attendances')
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'on' THEN 1 ELSE 0 END) as present"),
                DB::raw("SUM(CASE WHEN status = 'off' THEN 1 ELSE 0 END) as absent"),
                DB::raw("SUM(CASE WHEN status = 'on' AND late_minutes > 0 THEN 1 ELSE 0 END) as late")
            )
            ->where('session_id', $sessionId)
            ->whereDate('date', Carbon::today())
            ->first();

        return [
            'total'   => (int) ($row->total   ?? 0),
            'present' => (int) ($row->present  ?? 0),
            'absent'  => (int) ($row->absent   ?? 0),
            'late'    => (int) ($row->late      ?? 0),
        ];
    }

    /**
     * Monthly heatmap data: for each day of the month, the attendance rate (0–100).
     * Returns ['day' => percentage, ...].
     */
    public function getMonthlyHeatmap(int $sessionId, int $classId, int $year, int $month): array
    {
        $rows = DB::table('attendances')
            ->select(
                DB::raw('DAY(date) as day'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'on' THEN 1 ELSE 0 END) as present")
            )
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->groupByRaw('DAY(date)')
            ->get()
            ->keyBy('day');

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $result      = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $row        = $rows[$d] ?? null;
            $result[$d] = $row && $row->total > 0
                ? round($row->present / $row->total * 100, 1)
                : null; // null = no class that day
        }

        return $result;
    }

    /**
     * Per-class summary for a given date (used in analytics dashboard table).
     * Returns Collection of objects with class_name, total, present, absent, late, rate.
     */
    public function getClassSummaryForDate(int $sessionId, string $date): Collection
    {
        return DB::table('attendances')
            ->join('school_classes', 'attendances.class_id', '=', 'school_classes.id')
            ->select(
                'school_classes.class_name',
                'attendances.class_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN attendances.status = 'on' THEN 1 ELSE 0 END) as present"),
                DB::raw("SUM(CASE WHEN attendances.status = 'off' THEN 1 ELSE 0 END) as absent"),
                DB::raw("SUM(CASE WHEN attendances.status = 'on' AND attendances.late_minutes > 0 THEN 1 ELSE 0 END) as late")
            )
            ->where('attendances.session_id', $sessionId)
            ->whereDate('attendances.date', $date)
            ->groupBy('attendances.class_id', 'school_classes.class_name')
            ->orderBy('school_classes.class_name')
            ->get()
            ->map(function ($row) {
                $row->rate = $row->total > 0 ? round($row->present / $row->total * 100, 1) : 0;
                return $row;
            });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // BULK CSV IMPORT
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Upsert attendance rows from a parsed CSV.
     * Each $row must have: student_id, date, status ('on'|'off'|'present'|'absent'), late_minutes.
     *
     * Returns ['imported' => int, 'skipped' => int, 'errors' => string[]].
     */
    public function bulkImportFromCsv(array $rows, int $classId, int $sessionId): array
    {
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $i => $row) {
            $line = $i + 2; // header is line 1

            try {
                // Normalise status
                $rawStatus = strtolower(trim($row['status'] ?? ''));
                $status    = match ($rawStatus) {
                    'present', 'on', '1', 'yes', 'p' => 'on',
                    'absent',  'off','0', 'no',  'a' => 'off',
                    default                           => null,
                };

                if ($status === null) {
                    $errors[] = "Line {$line}: unrecognised status '{$rawStatus}'.";
                    $skipped++;
                    continue;
                }

                $studentId   = (int) ($row['student_id'] ?? 0);
                $date        = trim($row['date']         ?? '');
                $lateMinutes = (int) ($row['late_minutes'] ?? 0);

                if (! $studentId) {
                    $errors[] = "Line {$line}: missing or invalid student_id.";
                    $skipped++;
                    continue;
                }

                if (! $date || ! strtotime($date)) {
                    $errors[] = "Line {$line}: invalid date '{$date}'.";
                    $skipped++;
                    continue;
                }

                Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_id'   => $classId,
                        'session_id' => $sessionId,
                        'date'       => Carbon::parse($date)->toDateString(),
                    ],
                    [
                        'status'       => $status,
                        'late_minutes' => max(0, $lateMinutes),
                        'section_id'   => 0,
                        'course_id'    => 0,
                    ]
                );

                $imported++;

            } catch (\Throwable $e) {
                $errors[] = "Line {$line}: " . $e->getMessage();
                $skipped++;
            }
        }

        return compact('imported', 'skipped', 'errors');
    }
}
