<?php

namespace App\Repositories;

use App\Models\Routine;
use App\Interfaces\RoutineInterface;

class RoutineRepository implements RoutineInterface {

    public function saveRoutine($request)
    {
        try {
            Routine::create([
                'start'      => $request['start'],
                'end'        => $request['end'],
                'weekday'    => $request['weekday'],
                'session_id' => $request['session_id'],
                'class_id'   => $request['class_id'],
                'section_id' => $request['section_id'],
                'course_id'  => $request['course_id'],
                'teacher_id' => $request['teacher_id'] ?? null,
                'room'       => $request['room'] ?? null,
                'color'      => $request['color'] ?? null,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to save routine. ' . $e->getMessage());
        }
    }

    public function getAll($class_id, $section_id, $session_id)
    {
        return Routine::with(['course', 'section'])
            ->where('session_id', $session_id)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->get();
    }

    /** Return all slots for a given teacher in the current session */
    public function getByTeacher(int $teacher_id, int $session_id)
    {
        return Routine::with(['course', 'schoolClass', 'section'])
            ->where('session_id', $session_id)
            ->where('teacher_id', $teacher_id)
            ->orderBy('weekday')
            ->orderBy('start')
            ->get();
    }
}
