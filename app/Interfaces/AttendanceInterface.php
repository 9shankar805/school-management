<?php

namespace App\Interfaces;

interface AttendanceInterface
{
    // ── Existing ──────────────────────────────────────────────────────────────

    public function saveAttendance($request);

    public function getSectionAttendance($class_id, $section_id, $session_id);

    public function getCourseAttendance($class_id, $course_id, $session_id);

    public function getStudentAttendance($session_id, $student_id);

    // ── Date-aware queries ────────────────────────────────────────────────────

    /** All attendance records for a specific date (today if null). */
    public function getAttendanceByDate(string $date, ?int $classId = null, ?int $sessionId = null);

    /** Per-student summary for a month: total, present, absent, late, percentage. */
    public function getMonthlyStudentSummary(int $sessionId, int $classId, int $year, int $month): array;

    /** Students whose attendance percentage is below $threshold (0–100). */
    public function getShortageStudents(int $sessionId, float $threshold = 75.0): array;

    // ── Analytics ─────────────────────────────────────────────────────────────

    /** 7-day rolling daily present/absent counts for a session+class. */
    public function getDailyTrend(int $sessionId, int $classId, int $days = 7): array;

    /** Class-level summary for today: present, absent, late totals. */
    public function getTodaySummary(int $sessionId): array;

    /** Monthly aggregate per day for a heatmap (year+month). */
    public function getMonthlyHeatmap(int $sessionId, int $classId, int $year, int $month): array;

    // ── Bulk CSV import ───────────────────────────────────────────────────────

    /**
     * Insert/update attendance from a parsed CSV array.
     * Each row: ['student_id', 'date', 'status', 'late_minutes'].
     */
    public function bulkImportFromCsv(array $rows, int $classId, int $sessionId): array;
}
