<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validates password change / reset requests.
 * Enforces the school's password policy:
 *   - Minimum 8 characters
 *   - At least 1 uppercase letter
 *   - At least 1 number
 *   - At least 1 symbol
 *   - Cannot reuse current password
 */
class PasswordChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password'         => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),   // checks HaveIBeenPwned — optional, safe to remove if no internet
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min'      => 'Password must be at least 8 characters.',
            'password.mixed'    => 'Password must contain uppercase and lowercase letters.',
            'password.numbers'  => 'Password must contain at least one number.',
            'password.symbols'  => 'Password must contain at least one symbol.',
        ];
    }
}
