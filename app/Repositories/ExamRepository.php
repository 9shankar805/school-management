<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Models\Semester;
use App\Models\SchoolClass;
use App\Interfaces\ExamInterface;

class ExamRepository implements ExamInterface {
    public function create($request) {
        try {
            Exam::create($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create exam. '.$e->getMessage());
        }
    }

    public function delete($id) {
        try {
            Exam::destroy($id);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete exam. '.$e->getMessage());
        }
    }

    public function getAll($session_id, $semester_id, $class_id)
    {
        // If no filter provided, try to fall back to the first available values.
        // Guard against the case where the session has no semesters or classes yet
        // (e.g. a brand-new school that hasn't set up terms) — return empty collection
        // rather than crashing with a "Call to a member function on null" error.
        if ($semester_id == 0 || $class_id == 0) {
            $firstSemester = Semester::where('session_id', $session_id)->first();
            $firstClass    = SchoolClass::where('session_id', $session_id)->first();

            if (! $firstSemester || ! $firstClass) {
                return collect();
            }

            $semester_id = $firstSemester->id;
            $class_id    = $firstClass->id;
        }

        return Exam::with('course')
            ->where('session_id',  $session_id)
            ->where('semester_id', $semester_id)
            ->where('class_id',    $class_id)
            ->get();
    }
}