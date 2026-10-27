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

    public function getAll($session_id, $semester_id = 0, $class_id = 0, $course_id = 0)
    {
        $query = Exam::with(['course', 'schoolClass', 'semester'])
            ->where('session_id', $session_id);

        if (!empty($semester_id) && (int)$semester_id > 0) {
            $query->where('semester_id', (int)$semester_id);
        }

        if (!empty($class_id) && (int)$class_id > 0) {
            $query->where('class_id', (int)$class_id);
        }

        if (!empty($course_id) && (int)$course_id > 0) {
            $query->where('course_id', (int)$course_id);
        }

        return $query->latest('id')->get();
    }
}