<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('take attendances');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'course_id'             => 'nullable|integer',
            'class_id'              => 'required|integer',
            'section_id'            => 'nullable|integer',
            'student_ids'           => 'required|array|min:1',
            'student_ids.*'         => 'integer',
            'status'                => 'required|array|min:1',
            'session_id'            => 'required',
            'date'                  => 'nullable|date',
        ];
    }
}
