<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('UNCAUGHT: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        });
    }

    /**
     * Render an exception into an HTTP response.
     * Handles 419 Page Expired (CSRF token mismatch) gracefully.
     */
    public function render($request, Throwable $exception)
    {
        // 419 Page Expired — CSRF token mismatch
        // If user is already authenticated, send them to dashboard
        // Otherwise send them back to login with a friendly message
        if ($exception instanceof TokenMismatchException) {
            if (auth()->check()) {
                return redirect()->route('home')
                    ->with('status', 'Your session was refreshed. You are still logged in.');
            }
            return redirect()->route('login')
                ->with('error', 'Your session expired. Please log in again.');
        }

        return parent::render($request, $exception);
    }
}

