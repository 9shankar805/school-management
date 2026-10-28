<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\FinalMark;
use App\Interfaces\MarkInterface;

class MarkRepository implements MarkInterface {
    public function create($rows) {
        if (empty($rows)) {
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($rows) {
                $schoolId = auth()->user()->school_id ?? null;
                $sessionId = $rows[0]['session_id'] ?? null;
                $classId = $rows[0]['class_id'] ?? null;

                $studentIds = array_values(array_unique(array_column($rows, 'student_id')));
                $courseIds = array_values(array_unique(array_column($rows, 'course_id')));
                $examIds = array_values(array_unique(array_column($rows, 'exam_id')));

                // Fetch all existing records in a single query instead of 1 query per row
                $existingQuery = Mark::where('session_id', $sessionId)
                    ->where('class_id', $classId)
                    ->whereIn('student_id', $studentIds)
                    ->whereIn('course_id', $courseIds)
                    ->whereIn('exam_id', $examIds);

                $existingMarks = $existingQuery->get()->keyBy(function ($item) {
                    return $item->student_id . '_' . $item->course_id . '_' . $item->exam_id;
                });

                $toInsert = [];
                $now = now();

                foreach ($rows as $row) {
                    $key = $row['student_id'] . '_' . $row['course_id'] . '_' . $row['exam_id'];

                    if (isset($existingMarks[$key])) {
                        $markRecord = $existingMarks[$key];
                        $markRecord->marks = $row['marks'];
                        $markRecord->section_id = $row['section_id'] ?? 0;
                        if ($schoolId && empty($markRecord->school_id)) {
                            $markRecord->school_id = $schoolId;
                        }
                        $markRecord->save();
                    } else {
                        $toInsert[] = [
                            'school_id'  => $row['school_id'] ?? $schoolId,
                            'session_id' => $row['session_id'],
                            'class_id'   => $row['class_id'],
                            'section_id' => $row['section_id'] ?? 0,
                            'student_id' => $row['student_id'],
                            'course_id'  => $row['course_id'],
                            'exam_id'    => $row['exam_id'],
                            'marks'      => $row['marks'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (!empty($toInsert)) {
                    foreach (array_chunk($toInsert, 250) as $chunk) {
                        Mark::insert($chunk);
                    }
                }
            });
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