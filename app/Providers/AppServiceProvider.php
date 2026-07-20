<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Attendance;
use App\Models\Exam;
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
        $this->app->bind(\App\Interfaces\PaymentInterface::class, \App\Repositories\PaymentRepository::class);

        // Question Paper module
        $this->app->bind(\App\Interfaces\QuestionPaperInterface::class, \App\Repositories\QuestionPaperRepository::class);
        $this->app->bind(\App\Interfaces\QuestionBankInterface::class, \App\Repositories\QuestionBankRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ----------------------------------------------------------------
        // Register AuditObserver on all key models
        // Add more models here as they are built out.
        // ----------------------------------------------------------------
        $modelsToAudit = [User::class, Attendance::class, Exam::class, Mark::class, Invoice::class, Payment::class];

        foreach ($modelsToAudit as $model) {
            $model::observe(AuditObserver::class);
        }

        // ----------------------------------------------------------------
        // Share pending question-paper count with every view that
        // includes the left-menu (avoids raw @php DB calls in blade).
        // ----------------------------------------------------------------
        \Illuminate\Support\Facades\View::composer('layouts.left-menu', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                try {
                    $pending = \App\Models\QuestionPaper::whereIn('status', ['submitted', 'reviewed'])->count();
                } catch (\Throwable $e) {
                    $pending = 0; // table may not exist yet before migrations run
                }
                $view->with('pendingPapers', $pending);
            }
        });
    }
}
