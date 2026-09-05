<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RollNumberController extends Controller
{
    use SchoolSession;

    public function __construct()
    {
        $this->middleware(['can:edit users']);
    }

    /**
     * Display Roll Number Management dashboard for class/section.
     */
    public function index(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $classes = SchoolClass::where('session_id', $sessionId)->orderBy('class_name')->get();

        $classId = $request->query('class_id', $classes->first()?->id ?? 0);
        $sections = collect();
        if ($classId) {
            $sections = Section::where('class_id', $classId)->orderBy('section_name')->get();
        }

        $sectionId = $request->query('section_id', $sections->first()?->id ?? 0);

        $promotions = collect();
        if ($classId && $sectionId) {
            $promotions = Promotion::with(['student.academic_info'])
                ->where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->get()
                ->sortBy(function ($p) {
                    // Sort numerically by roll_no if numeric, otherwise fallback to student name
                    if ($p->roll_no !== null && is_numeric($p->roll_no)) {
                        return (int)$p->roll_no;
                    }
                    return $p->student?->first_name ?? '';
                })
                ->values();
        }

        $selectedClass = $classes->firstWhere('id', $classId);
        $selectedSection = $sections->firstWhere('id', $sectionId);

        return view('roll-numbers.index', compact(
            'classes',
            'sections',
            'promotions',
            'classId',
            'sectionId',
            'selectedClass',
            'selectedSection',
            'sessionId'
        ));
    }

    /**
     * Bulk update roll numbers manually.
     */
    public function updateBulk(Request $request)
    {
        $request->validate([
            'class_id'   => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'roll_no'    => 'required|array',
            'roll_no.*'  => 'nullable|string|max:30',
        ]);

        $sessionId = $this->getSchoolCurrentSession();
        $rollNos = $request->input('roll_no', []);

        DB::transaction(function () use ($sessionId, $request, $rollNos) {
            foreach ($rollNos as $studentId => $rollNo) {
                $trimmed = !is_null($rollNo) && trim($rollNo) !== '' ? trim($rollNo) : null;

                Promotion::where('session_id', $sessionId)
                    ->where('class_id', $request->class_id)
                    ->where('section_id', $request->section_id)
                    ->where('student_id', $studentId)
                    ->update(['roll_no' => $trimmed]);

                User::where('id', $studentId)->update(['roll_no' => $trimmed]);
            }
        });

        return back()->with('status', 'Roll numbers updated successfully!');
    }

    /**
     * Auto-assign sequential roll numbers based on sorting rules.
     */
    public function autoAssign(Request $request)
    {
        $request->validate([
            'class_id'   => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'sort_by'    => 'required|in:name_asc,name_desc,admission_no,id_card_number',
            'start_no'   => 'required|integer|min:1',
            'prefix'     => 'nullable|string|max:10',
            'pad_length' => 'nullable|integer|min:0|max:5',
        ]);

        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = $request->class_id;
        $sectionId  = $request->section_id;
        $sortBy     = $request->sort_by;
        $startNo    = (int) $request->start_no;
        $prefix     = $request->prefix ?? '';
        $padLength  = (int) ($request->pad_length ?? 0);

        $promotions = Promotion::with(['student.academic_info'])
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->get();

        if ($promotions->isEmpty()) {
            return back()->with('error', 'No students found in the selected class and section.');
        }

        // Sort promotions
        $sorted = $promotions->sort(function ($a, $b) use ($sortBy) {
            switch ($sortBy) {
                case 'name_desc':
                    return strcasecmp($b->student?->first_name . ' ' . $b->student?->last_name, $a->student?->first_name . ' ' . $a->student?->last_name);
                case 'admission_no':
                    return strcasecmp($a->student?->academic_info?->admission_no ?? '', $b->student?->academic_info?->admission_no ?? '');
                case 'id_card_number':
                    return strcasecmp($a->id_card_number ?? '', $b->id_card_number ?? '');
                case 'name_asc':
                default:
                    return strcasecmp($a->student?->first_name . ' ' . $a->student?->last_name, $b->student?->first_name . ' ' . $b->student?->last_name);
            }
        })->values();

        DB::transaction(function () use ($sorted, $startNo, $prefix, $padLength, $sessionId, $classId, $sectionId) {
            $currentNum = $startNo;
            foreach ($sorted as $promo) {
                $numberPart = $padLength > 0 ? sprintf("%0{$padLength}d", $currentNum) : (string) $currentNum;
                $generatedRoll = $prefix . $numberPart;

                $promo->update(['roll_no' => $generatedRoll]);
                User::where('id', $promo->student_id)->update(['roll_no' => $generatedRoll]);

                $currentNum++;
            }
        });

        return back()->with('status', "Successfully auto-assigned roll numbers to {$sorted->count()} students!");
    }
}
