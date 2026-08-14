<?php

namespace App\Repositories;

use App\Models\StudentParentInfo;
use App\Models\User;

class StudentParentInfoRepository {

    /**
     * Resolve school_id from the student record.
     */
    private function resolveSchoolId($studentId): ?int
    {
        $student = User::find($studentId);
        return $student?->school_id ?? config('tenant.school_id') ?? null;
    }

    public function store($request, $student_id) {
        try {
            StudentParentInfo::create([
                'student_id'     => $student_id,
                'school_id'      => $this->resolveSchoolId($student_id),
                'father_name'    => $request['father_name'],
                'father_phone'   => $request['father_phone'],
                'mother_name'    => $request['mother_name'],
                'mother_phone'   => $request['mother_phone'],
                'parent_address' => $request['parent_address'],
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create Student Parent information. '.$e->getMessage());
        }
    }

    public function getParentInfo($student_id) {
        // withoutGlobalScopes ensures legacy rows with null school_id are
        // still returned until fully backfilled.
        return StudentParentInfo::withoutGlobalScopes()
                ->where('student_id', $student_id)
                ->first();
    }

    public function update($request, $student_id) {
        try {
            // updateOrCreate handles both new students (no row yet) and existing records
            StudentParentInfo::withoutGlobalScopes()
                ->updateOrCreate(
                    ['student_id' => $student_id],
                    [
                        'school_id'      => $this->resolveSchoolId($student_id),
                        'father_name'    => $request['father_name'] ?? null,
                        'father_phone'   => $request['father_phone'] ?? null,
                        'mother_name'    => $request['mother_name'] ?? null,
                        'mother_phone'   => $request['mother_phone'] ?? null,
                        'parent_address' => $request['parent_address'] ?? null,
                    ]
                );
        } catch (\Exception $e) {
            throw new \Exception('Failed to update Student Parent information. '.$e->getMessage());
        }
    }
}
