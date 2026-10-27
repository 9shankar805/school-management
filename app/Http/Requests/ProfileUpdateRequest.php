<?php

namespace App\Http\Requests;

use App\Rules\ValidEmailDomain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'email'       => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
                new ValidEmailDomain(),
            ],
            'phone'       => ['nullable', 'string', 'max:25'],
            'gender'      => ['nullable', 'string', 'in:male,female,other,Male,Female,Other'],
            'birthday'    => ['nullable', 'date', 'before:today'],
            'address'     => ['nullable', 'string', 'max:255'],
            'address2'    => ['nullable', 'string', 'max:255'],
            'city'        => ['nullable', 'string', 'max:100'],
            'zip'         => ['nullable', 'string', 'max:20'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'blood_type'  => ['nullable', 'string', 'max:10'],
            'religion'    => ['nullable', 'string', 'max:50'],
            'photo'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:3072'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required'  => 'Last name is required.',
            'email.required'      => 'Email address is required.',
            'email.email'         => 'Please enter a valid email address.',
            'email.unique'        => 'This email address is already in use by another account.',
            'photo.image'         => 'Profile picture must be an image file.',
            'photo.mimes'         => 'Allowed image formats: JPG, JPEG, PNG, WEBP, GIF.',
            'photo.max'           => 'Image file size may not exceed 3MB.',
            'birthday.before'     => 'Birthday must be a date in the past.',
        ];
    }
}
