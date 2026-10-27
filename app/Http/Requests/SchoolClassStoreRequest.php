<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolClassStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('create classes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'class_name'          => 'required_without_all:classes,bulk_class_names|nullable|string',
            'classes'             => 'required_without_all:class_name,bulk_class_names|nullable|array',
            'classes.*.class_name'=> 'required_with:classes|string|max:100',
            'bulk_class_names'    => 'required_without_all:class_name,classes|nullable|string',
            'session_id'          => 'required|integer|gt:0',
        ];
    }
}
