<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SectionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('create sections');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'section_name'  => 'required|string',
            'room_no'       => 'nullable|string',
            'class_id'      => 'required_without_all:class_ids,all_classes|nullable',
            'class_ids'     => 'nullable|array',
            'all_classes'   => 'nullable|boolean',
            'session_id'    => 'required|integer|gt:0',
        ];
    }
}
