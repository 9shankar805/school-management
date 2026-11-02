<?php

namespace App\Repositories;

use App\Models\AcademicSetting;
use App\Interfaces\AcademicSettingInterface;

class AcademicSettingRepository implements AcademicSettingInterface {
    public function getAcademicSetting(){
        $setting = AcademicSetting::withoutGlobalScopes()->find(1);
        if (!$setting) {
            $setting = AcademicSetting::withoutGlobalScopes()->create([
                'attendance_type' => 'section',
                'marks_submission_status' => 'off',
                'roll_number_mode' => 'yearly',
            ]);
        }
        // Fallback if column exists but null or not set
        if (!isset($setting->roll_number_mode) || empty($setting->roll_number_mode)) {
            $setting->roll_number_mode = \App\Models\Setting::get('roll_number_mode', 'yearly');
        }
        return $setting;
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

    public function updateRollNumberMode($request) {
        $mode = $request['roll_number_mode'] ?? 'yearly';
        if (!in_array($mode, ['yearly', 'terminal'])) {
            $mode = 'yearly';
        }
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('academic_settings', 'roll_number_mode')) {
                AcademicSetting::where('id', 1)->update(['roll_number_mode' => $mode]);
            }
            \App\Models\Setting::set('roll_number_mode', $mode, 'academic');
        } catch (\Exception $e) {
            throw new \Exception('Failed to update roll number assignment mode. '.$e->getMessage());
        }
    }
}