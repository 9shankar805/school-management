<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'student_id' => $this->student_id,
            'student'    => $this->whenLoaded('student', fn() => [
                'id'   => $this->student->id,
                'name' => $this->student->full_name,
            ]),
            'course_id'  => $this->course_id,
            'course'     => $this->whenLoaded('course', fn() => [
                'id'   => $this->course->id,
                'name' => $this->course->course_name,
            ]),
            'date'       => $this->date?->format('Y-m-d'),
            'status'     => $this->attendance_type, // present|absent|late|leave
            'notes'      => $this->notes ?? null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
