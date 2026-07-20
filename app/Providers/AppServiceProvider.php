<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Expense;
use App\Models\IncomeEntry;
use App\Models\Invoice;
use App\Models\Mark;
use App\Models\Payment;
use App\Models\User;
use App\Observers\AuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Interfaces\LibraryInterface::class, \App\Repositories\LibraryRepository::class);
        $this->app->bind(\App\Interfaces\StaffInterface::class, \App\Repositories\StaffRepository::class);
        // Legacy PaymentInterface kept for any code still using it
        $this->app->bind(\App\Interfaces\PaymentInterface::class, \App\Repositories\PaymentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── AuditObserver on all key models ───────────────────────────────────
        $modelsToAudit = [
            User::class, Attendance::class, Exam::class,
            Mark::class, Invoice::class, Payment::class,
            Expense::class, IncomeEntry::class,
        ];

        foreach ($modelsToAudit as $model) {
            $model::observe(AuditObserver::class);
        }

        // ── Share pending question-paper count with left-menu ─────────────────
        \Illuminate\Support\Facades\View::composer('layouts.left-menu', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                try {
                    $pending = \App\Models\QuestionPaper::whereIn('status', ['submitted', 'reviewed'])->count();
                } catch (\Throwable $e) {
                    $pending = 0;
                }
                $view->with('pendingPapers', $pending);
            }
        });
    }
}
