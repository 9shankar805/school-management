<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoticeStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('create notices');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'       => 'required|string|max:255',
            'priority'    => 'nullable|string|in:normal,urgent,holiday,exam',
            'audience'    => 'nullable|string|in:all,parents,students,teachers',
            'expiry_date' => 'nullable|date',
            'notice'      => 'required|string',
            'session_id'  => 'required',
            'attachment'  => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx|max:10240',
        ];
    }
}
