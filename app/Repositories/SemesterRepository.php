<?php

namespace App\Repositories;

use App\Interfaces\SemesterInterface;
use App\Models\Semester;
use App\Models\SchoolSession;
use App\Models\Course;

class SemesterRepository implements SemesterInterface {
    public function create($request) {
        try {
            $semester = Semester::create($request);

            if ($semester && !empty($semester->session_id)) {
                $this->propagateExistingCoursesToSemester($semester);
            }

            return $semester;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create School Semester. '.$e->getMessage());
        }
    }

    /**
     * Auto-replicate all existing subjects in the session into the newly created semester.
     * In K-12/secondary schools, all semesters/terms share the exact same curriculum subjects.
     */
    public function propagateExistingCoursesToSemester(Semester $semester): int
    {
        $existingCourses = Course::where('session_id', $semester->session_id)
            ->where('semester_id', '!=', $semester->id)
            ->orderBy('position', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->unique(fn($c) => $c->class_id . '___' . $c->course_name);

        $created = 0;
        foreach ($existingCourses as $c) {
            $newCourse = Course::firstOrCreate([
                'course_name' => $c->course_name,
                'class_id'    => $c->class_id,
                'semester_id' => $semester->id,
                'session_id'  => $semester->session_id,
            ], [
                'course_type' => $c->course_type,
                'position'    => $c->position ?? 0,
                'school_id'   => $c->school_id ?? null,
            ]);

            if ($newCourse->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    public function getAll($session_id)
    {
        return Semester::where('session_id', $session_id)->orderBy('id', 'desc')->get();
    }
}