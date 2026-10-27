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

    /**
     * Map alternative input names (old_password / new_password) to standard fields
     * so both conventions work seamlessly.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('old_password') && ! $this->filled('current_password')) {
            $this->merge(['current_password' => $this->input('old_password')]);
        }

        if ($this->filled('new_password') && ! $this->filled('password')) {
            $this->merge(['password' => $this->input('new_password')]);
        }

        if ($this->filled('new_password_confirmation') && ! $this->filled('password_confirmation')) {
            $this->merge(['password_confirmation' => $this->input('new_password_confirmation')]);
        }
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
                    ->symbols(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'         => 'The current password field is required.',
            'current_password.current_password' => 'The provided current password does not match your account password.',
            'password.required'                 => 'The new password field is required.',
            'password.confirmed'                => 'The password confirmation does not match.',
            'password.min'                      => 'Password must be at least 8 characters.',
            'password.mixed'                    => 'Password must contain uppercase and lowercase letters.',
            'password.numbers'                  => 'Password must contain at least one number.',
            'password.symbols'                  => 'Password must contain at least one symbol.',
        ];
    }
}
