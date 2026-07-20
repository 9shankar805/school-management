<?php

namespace App\Console\Commands;

use App\Jobs\SendAbsentParentNotification;
use App\Models\SchoolSession;
use App\Models\User;
use App\Notifications\AttendanceShortageAlert;
use App\Repositories\AttendanceRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * attendance:check-shortage
 * ─────────────────────────────────────────────────────────────────────────────
 * Scheduled to run once daily (after school hours, e.g. 18:00).
 * Two responsibilities:
 *
 *  1. Dispatch absent-parent notifications for today's absences.
 *  2. Find students below the attendance threshold and send shortage alerts
 *     to them (in-app + mail) and a digest to all attendance officers / admins.
 *
 * Usage:
 *   php artisan attendance:check-shortage
 *   php artisan attendance:check-shortage --threshold=80 --date=2026-07-20
 */
class CheckAttendanceShortage extends Command
{
    protected $signature = 'attendance:check-shortage
                            {--threshold=75 : Minimum attendance % before alert is sent}
                            {--date=        : Date to process (default: today, YYYY-MM-DD)}
                            {--skip-absent  : Skip dispatching absent-parent notifications}';

    protected $description = 'Check attendance shortages and dispatch parent/admin alerts.';

    public function handle(): int
    {
        $threshold = (float) $this->option('threshold');
        $date      = $this->option('date') ?: now()->toDateString();

        $this->info("Running attendance shortage check for {$date} (threshold: {$threshold}%)");

        // Resolve the current/latest school session
        $session = SchoolSession::latest()->first();

        if (! $session) {
            $this->error('No school session found. Aborting.');
            return self::FAILURE;
        }

        $sessionId = $session->id;

        // ── 1. Dispatch absent-parent notifications ────────────────────────
        if (! $this->option('skip-absent')) {
            $this->line('Dispatching absent-parent notifications...');
            $dispatched = SendAbsentParentNotification::dispatchForDate($sessionId, $date);
            $this->info("  → {$dispatched} notification job(s) queued.");
            Log::info("attendance:check-shortage: {$dispatched} absent notifications dispatched for {$date}.");
        }

        // ── 2. Shortage alerts ─────────────────────────────────────────────
        $this->line("Checking attendance below {$threshold}%...");

        $repo    = new AttendanceRepository();
        $shorts  = $repo->getShortageStudents($sessionId, $threshold);
        $count   = count($shorts);

        $this->info("  → {$count} student(s) below threshold.");

        if ($count > 0) {
            // Notify each student individually
            foreach ($shorts as $row) {
                $student = $row['student'] ?? null;
                if (! $student) continue;

                try {
                    $student->notify(new AttendanceShortageAlert(
                        studentName: $student->full_name,
                        percentage:  $row['percentage'],
                        threshold:   $threshold,
                        className:   $row['schoolClass']?->class_name ?? 'Unknown Class',
                    ));
                } catch (\Throwable $e) {
                    $this->warn("  Failed to notify student {$student->id}: " . $e->getMessage());
                    Log::warning("attendance:check-shortage student notify failed: " . $e->getMessage());
                }
            }

            // Send digest to attendance officers and admins
            $admins = User::role(['admin', 'principal', 'attendance-officer'])->get();

            foreach ($admins as $admin) {
                try {
                    $admin->notify(new AttendanceShortageAlert(
                        threshold:   $threshold,
                        digestCount: $count,
                    ));
                } catch (\Throwable $e) {
                    $this->warn("  Failed to notify admin {$admin->id}: " . $e->getMessage());
                }
            }

            $this->info("  → Shortage digest sent to {$admins->count()} admin(s).");
        }

        $this->info('Done.');
        Log::info("attendance:check-shortage completed: {$count} shortage(s) on {$date}.");

        return self::SUCCESS;
    }
}
