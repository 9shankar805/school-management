<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->exam_name,
            'type'        => $this->exam_type ?? null,
            'session_id'  => $this->session_id,
            'class'       => $this->whenLoaded('schoolClass', fn() => [
                'id'   => $this->schoolClass->id,
                'name' => $this->schoolClass->class_name,
            ]),
            'start_date'  => $this->exam_startdate?->format('Y-m-d'),
            'end_date'    => $this->exam_enddate?->format('Y-m-d'),
            'is_published'=> (bool) $this->is_published,
            'rules'       => $this->whenLoaded('examRules', fn() =>
                $this->examRules->map(fn($r) => [
                    'course'     => $r->course?->course_name,
                    'full_marks' => $r->full_marks,
                    'pass_marks' => $r->pass_marks,
                ])
            ),
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
