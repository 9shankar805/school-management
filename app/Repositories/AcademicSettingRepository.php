<?php

namespace App\Repositories;

use App\Models\AcademicSetting;
use App\Interfaces\AcademicSettingInterface;

class AcademicSettingRepository implements AcademicSettingInterface {
    public function getAcademicSetting(){
        return AcademicSetting::withoutGlobalScopes()->find(1) ?? AcademicSetting::withoutGlobalScopes()->create([
            'attendance_type' => 'section',
            'marks_submission_status' => 'off'
        ]);
    }

    public function updateAttendanceType($request) {
        try {
            AcademicSetting::where('id', 1)->update($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update attendance type. '.$e->getMessage());
        }
    }

    public function updateFinalMarksSubmissionStatus($request) {
        $status = "off";
        if(isset($request['marks_submission_status'])) {
            $status = "on";
        }
        try {
            AcademicSetting::where('id', 1)->update(['marks_submission_status' => $status]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update final marks submission status. '.$e->getMessage());
        }
    }
}