<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * StudentResource
 *
 * Transforms a User (student role) into a consistent API JSON shape.
 * Used by /api/v1/students endpoints.
 */
class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'admission_no'   => $this->admission_no,
            'name'           => $this->full_name,
            'first_name'     => $this->first_name,
            'last_name'      => $this->last_name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'date_of_birth'  => $this->dob?->format('Y-m-d'),
            'gender'         => $this->gender,
            'avatar'         => $this->avatar_url,
            'status'         => $this->student_status ?? 'active',
            'roll_no'        => $this->roll_no,
            'class'          => $this->whenLoaded('schoolClass', fn() => [
                'id'   => $this->schoolClass->id,
                'name' => $this->schoolClass->class_name,
            ]),
            'section'        => $this->whenLoaded('section', fn() => [
                'id'   => $this->section->id,
                'name' => $this->section->section_name,
            ]),
            'guardian'       => $this->whenLoaded('guardian', fn() => [
                'id'    => $this->guardian->id,
                'name'  => $this->guardian->full_name,
                'phone' => $this->guardian->phone,
            ]),
            'admission_date' => $this->admission_date?->format('Y-m-d'),
            'school_id'      => $this->school_id,
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
