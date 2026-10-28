<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\FinalMark;
use App\Interfaces\MarkInterface;

class MarkRepository implements MarkInterface {
    public function create($rows) {
        try {
            foreach($rows as $row){
                Mark::updateOrCreate([
                    'session_id' => $row['session_id'],
                    'class_id'   => $row['class_id'],
                    'student_id' => $row['student_id'],
                    'course_id'  => $row['course_id'],
                    'exam_id'    => $row['exam_id'],
                ], [
                    'section_id' => $row['section_id'] ?? 0,
                    'marks'      => $row['marks'],
                ]);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to update students marks. '.$e->getMessage());
        }
    }

    public function getAll($session_id, $semester_id, $class_id, $section_id, $course_id) {
        $exam_ids = Exam::where('session_id', $session_id)
            ->when($semester_id > 0, fn($q) => $q->where('semester_id', (int)$semester_id))
            ->when($class_id > 0, fn($q) => $q->where('class_id', (int)$class_id))
            ->when($course_id > 0, fn($q) => $q->where('course_id', (int)$course_id))
            ->pluck('id')
            ->toArray();

        $query = Mark::with('student', 'exam')
            ->where('session_id', $session_id);

        if (!empty($exam_ids)) {
            $query->whereIn('exam_id', $exam_ids);
        }

        if (!empty($class_id) && (int)$class_id > 0) {
            $query->where('class_id', (int)$class_id);
        }

        if (!empty($course_id) && (int)$course_id > 0) {
            $query->where('course_id', (int)$course_id);
        }

        if (!empty($section_id) && (int)$section_id > 0) {
            $query->where('section_id', (int)$section_id);
        }

        return $query->get();
    }

    public function getAllByStudentId($session_id, $semester_id, $class_id, $section_id, $course_id, $student_id) {
        $exam_ids = Exam::where('session_id', $session_id)
            ->when($semester_id > 0, fn($q) => $q->where('semester_id', (int)$semester_id))
            ->pluck('id')
            ->toArray();

        $query = Mark::with('student', 'exam')
            ->where('session_id', $session_id)
            ->where('student_id', $student_id);

        if (!empty($exam_ids)) {
            $query->whereIn('exam_id', $exam_ids);
        }

        if (!empty($class_id) && (int)$class_id > 0) {
            $query->where('class_id', (int)$class_id);
        }

        if (!empty($course_id) && (int)$course_id > 0) {
            $query->where('course_id', (int)$course_id);
        }

        if (!empty($section_id) && (int)$section_id > 0) {
            $query->where('section_id', (int)$section_id);
        }

        return $query->get();
    }

    public function getFinalMarksCount($session_id, $semester_id, $class_id, $section_id, $course_id) {
        $query = FinalMark::where('session_id', $session_id)
            ->where('semester_id', $semester_id)
            ->where('class_id', $class_id);

        if (!empty($course_id) && (int)$course_id > 0) {
            $query->where('course_id', (int)$course_id);
        }

        if (!empty($section_id) && (int)$section_id > 0) {
            $query->where('section_id', (int)$section_id);
        }

        return $query->count();
    }

    public function getAllFinalMarks($session_id, $semester_id, $class_id, $section_id, $course_id) {
        $query = FinalMark::with('student')
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

        if (!empty($section_id) && (int)$section_id > 0) {
            $query->where('section_id', (int)$section_id);
        }

        return $query->get();
    }

    public function getAllFinalMarksByStudentId($session_id, $student_id, $semester_id, $class_id, $section_id, $course_id) {
        $query = FinalMark::with('student')
            ->where('session_id', $session_id)
            ->where('student_id', $student_id);

        if (!empty($semester_id) && (int)$semester_id > 0) {
            $query->where('semester_id', (int)$semester_id);
        }

        if (!empty($class_id) && (int)$class_id > 0) {
            $query->where('class_id', (int)$class_id);
        }

        if (!empty($course_id) && (int)$course_id > 0) {
            $query->where('course_id', (int)$course_id);
        }

        if (!empty($section_id) && (int)$section_id > 0) {
            $query->where('section_id', (int)$section_id);
        }

        return $query->get();
    }

    public function storeFinalMarks($rows) {
        try {
            foreach($rows as $row){
                FinalMark::updateOrCreate([
                    'session_id'  => $row['session_id'],
                    'semester_id' => $row['semester_id'],
                    'class_id'    => $row['class_id'],
                    'student_id'  => $row['student_id'],
                    'course_id'   => $row['course_id'],
                ], [
                    'section_id'       => $row['section_id'] ?? 0,
                    'calculated_marks' => $row['calculated_marks'],
                    'final_marks'      => $row['final_marks'],
                    'note'             => $row['note'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to update students final marks. '.$e->getMessage());
        }
    }
}