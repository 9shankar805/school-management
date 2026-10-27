<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Validate the email for the given request.
     * Blocks fake, disposable, and non-existent email domains.
     */
    protected function validateEmail(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', new \App\Rules\ValidEmailDomain],
        ]);
    }
}
