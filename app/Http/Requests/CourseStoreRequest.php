<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('create courses');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'course_name'  => 'required|string|max:255',
            'course_type'  => 'required|string|in:Core,General,Elective,Optional',
            'class_ids'    => 'required|array|min:1',
            'class_ids.*'  => 'integer|gt:0',
            'all_terms'    => 'nullable|boolean',
            'semester_id'  => 'required_without:all_terms|nullable|integer|gt:0',
            'session_id'   => 'required|integer|gt:0',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages()
    {
        return [
            'class_ids.required' => 'Please select at least one class.',
            'class_ids.min'      => 'Please select at least one class.',
        ];
    }
}
