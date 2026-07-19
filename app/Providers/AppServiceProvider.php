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
    }
}
