<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->full_name,
            'first_name'      => $this->first_name,
            'last_name'       => $this->last_name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'gender'          => $this->gender,
            'avatar'          => $this->avatar_url,
            'joining_date'    => $this->joining_date?->format('Y-m-d'),
            'basic_salary'    => $this->basic_salary,
            'pan_number'      => $this->when($request->user()?->can('view teachers'), $this->pan_number),
            'bank_account'    => $this->when($request->user()?->can('view teachers'), $this->bank_account),
            'department'      => $this->whenLoaded('department', fn() => [
                'id'   => $this->department->id,
                'name' => $this->department->name,
            ]),
            'qualifications'  => $this->whenLoaded('qualifications', fn() =>
                $this->qualifications->map(fn($q) => [
                    'degree'     => $q->degree,
                    'institution'=> $q->institution,
                    'year'       => $q->passing_year,
                ])
            ),
            'assigned_courses'=> $this->whenLoaded('assignedTeachers', fn() =>
                $this->assignedTeachers->map(fn($at) => [
                    'course'  => $at->course?->course_name,
                    'class'   => $at->schoolClass?->class_name,
                    'section' => $at->section?->section_name,
                ])
            ),
            'school_id'       => $this->school_id,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
