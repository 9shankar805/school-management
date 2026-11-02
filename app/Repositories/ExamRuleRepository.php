<?php

namespace App\Repositories;

use App\Interfaces\ExamRuleInterface;
use App\Models\ExamRule;

class ExamRuleRepository implements ExamRuleInterface {
    public function create($request) {
        try {
            ExamRule::create($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create exam rule. '.$e->getMessage());
        }
    }

    public function update($request) {
        try {
            $existing = ExamRule::where('id', $request->exam_rule_id)->first();
            $newTotal = (float) $request->total_marks;

            ExamRule::where('id', $request->exam_rule_id)->update([
                'total_marks'              => $request->total_marks,
                'pass_marks'               => $request->pass_marks,
                'marks_distribution_note'  => $request->marks_distribution_note
                    ?? $existing?->marks_distribution_note
                    ?? '{}',
            ]);

            // When full marks are reduced, clamp any existing marks that now exceed
            // the new maximum — otherwise they become impossible values (e.g. 60/50).
            if ($existing && $newTotal > 0) {
                ExamRule::clampMarksToFullMarks((int) $existing->exam_id, $newTotal);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to update exam rule. '.$e->getMessage());
        }
    }

    public function getAll($session_id, $exam_id) {
        return ExamRule::where('session_id', $session_id)
                        ->where('exam_id', $exam_id)
                        ->get();
    }

    public function getById($exam_rule_id) {
        return ExamRule::where('id', $exam_rule_id)
                        ->first();
    }
}