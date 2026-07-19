<?php

namespace App\Repositories;

use App\Models\Promotion;
use App\Models\User;

class PromotionRepository
{
    public function assignClassSection($request, $student_id)
    {
        Promotion::create([
            'student_id'     => $student_id,
            'session_id'     => $request['session_id'],
            'class_id'       => $request['class_id'],
            'section_id'     => $request['section_id'],
            'id_card_number' => $request['id_card_number'],
        ]);
    }

    public function update($request, $student_id)
    {
        Promotion::where('student_id', $student_id)->update([
            'id_card_number' => $request['id_card_number'],
        ]);
    }

    public function massPromotion($rows)
    {
        foreach ($rows as $row) {
            Promotion::updateOrCreate(
                ['student_id' => $row['student_id'], 'session_id' => $row['session_id'],
                 'class_id'   => $row['class_id'],   'section_id' => $row['section_id']],
                ['id_card_number' => $row['id_card_number']]
            );
        }
    }

    public function getAll($session_id, $class_id, $section_id)
    {
        return Promotion::with(['student', 'section'])
            ->where('session_id', $session_id)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->get();
    }

    public function getAllStudentsBySessionCount($session_id)
    {
        return Promotion::where('session_id', $session_id)->count();
    }

    public function getMaleStudentsBySessionCount($session_id)
    {
        $allStudents = Promotion::where('session_id', $session_id)->pluck('student_id')->toArray();

        return User::where('gender', 'Male')
            ->where('role', 'student')
            ->whereIn('id', $allStudents)
            ->count();
    }

    public function getAllStudentsBySession($session_id)
    {
        return Promotion::with(['student', 'section'])
            ->where('session_id', $session_id)
            ->get();
    }

    public function getPromotionInfoById($session_id, $student_id)
    {
        return Promotion::with(['student', 'section'])
            ->where('session_id', $session_id)
            ->where('student_id', $student_id)
            ->first();
    }

    /** Get the most recent class/section record for a student in a given session */
    public function getStudentCurrentClass($student_id, $session_id)
    {
        return Promotion::with(['schoolClass', 'section'])
            ->where('student_id', $student_id)
            ->where('session_id', $session_id)
            ->first();
    }

    public function getClasses($session_id)
    {
        return Promotion::with('schoolClass')
            ->select('class_id')
            ->where('session_id', $session_id)
            ->distinct('class_id')
            ->get();
    }

    public function getSections($session_id, $class_id)
    {
        return Promotion::with('section')
            ->select('section_id')
            ->where('session_id', $session_id)
            ->where('class_id', $class_id)
            ->distinct('section_id')
            ->get();
    }

    public function getSectionsBySession($session_id)
    {
        return Promotion::with('section')
            ->select('section_id')
            ->where('session_id', $session_id)
            ->distinct('section_id')
            ->get();
    }
}
