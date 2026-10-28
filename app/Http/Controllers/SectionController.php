<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\CourseInterface;
use App\Interfaces\SectionInterface;
use App\Http\Requests\SectionStoreRequest;
use App\Interfaces\SchoolSessionInterface;

class SectionController extends Controller
{
    use SchoolSession;
    
    protected $schoolSectionRepository;
    protected $schoolSessionRepository;
    protected $courseRepository;

    /**
    * Create a new Controller instance
    * 
    * @param SectionInterface $schoolSectionRepository
    * @return void
    */
    public function __construct(SchoolSessionInterface $schoolSessionRepository, SectionInterface $schoolSectionRepository, CourseInterface $courseRepository) {
        $this->schoolSectionRepository = $schoolSectionRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->courseRepository = $courseRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  SectionStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(SectionStoreRequest $request)
    {
        try {
            $sessionId = (int) $request->session_id;
            $defaultRoomNo = $request->input('room_no', '101') ?: '101';

            // 1. Determine target classes
            $targetClassIds = [];
            if ($request->boolean('all_classes') || $request->input('class_id') === 'all') {
                $targetClassIds = \App\Models\SchoolClass::where('session_id', $sessionId)->pluck('id')->all();
            } elseif ($request->has('class_ids') && is_array($request->class_ids) && count($request->class_ids) > 0) {
                $targetClassIds = array_filter(array_map('intval', $request->class_ids));
            } elseif ($request->filled('class_id')) {
                $targetClassIds = [(int) $request->class_id];
            }

            if (empty($targetClassIds)) {
                return back()->with('error', 'No valid classes were selected for section creation.');
            }

            // 2. Parse section names (e.g. "A, B" or "Red, Blue")
            $rawSections = preg_split('/[\r\n,]+/', $request->section_name);
            $sectionNames = array_unique(array_filter(array_map('trim', $rawSections)));

            if (empty($sectionNames)) {
                return back()->with('error', 'Please provide at least one section name.');
            }

            // 3. Create sections for each class with auto-incrementing room numbers
            $createdCount = 0;
            $classCount = count($targetClassIds);
            $currentRoomCounter = $defaultRoomNo;

            foreach ($targetClassIds as $classId) {
                foreach ($sectionNames as $secName) {
                    $assignedRoom = $this->incrementRoomNumber($currentRoomCounter);
                    $sec = Section::firstOrCreate([
                        'class_id'     => $classId,
                        'section_name' => $secName,
                        'session_id'   => $sessionId,
                    ], [
                        'room_no'      => $assignedRoom,
                    ]);
                    if ($sec->wasRecentlyCreated) {
                        $createdCount++;
                    }
                }
            }

            $secListStr = implode(', ', $sectionNames);
            $selectedClassNames = \App\Models\SchoolClass::whereIn('id', $targetClassIds)->pluck('class_name')->all();
            $classNamesStr = implode(', ', array_slice($selectedClassNames, 0, 5)) . (count($selectedClassNames) > 5 ? " and " . (count($selectedClassNames) - 5) . " more" : "");

            if ($classCount === 1) {
                return back()->with('status', "Section(s) [{$secListStr}] created successfully for {$classNamesStr} with sequential rooms starting from {$defaultRoomNo}!");
            }

            return back()->with('status', "Successfully created section(s) [{$secListStr}] for {$classCount} classes ({$classNamesStr}) — {$createdCount} new section(s) added with sequential rooms starting from {$defaultRoomNo}!");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create sections. ' . $e->getMessage());
        }
    }

    /**
     * Increment room number string (e.g. "103" -> "104", "Room-103" -> "Room-104")
     */
    private function incrementRoomNumber(string &$currentRoom): string
    {
        $trimmed = trim($currentRoom);
        if (preg_match('/^(.*?)(\d+)(\D*)$/', $trimmed, $matches)) {
            $prefix = $matches[1];
            $numStr = $matches[2];
            $suffix = $matches[3];
            $padLen = strlen($numStr);
            $num = (int)$numStr;

            $assigned = $trimmed;
            $nextNum = $num + 1;
            $nextNumStr = str_pad((string)$nextNum, $padLen, '0', STR_PAD_LEFT);
            $currentRoom = $prefix . $nextNumStr . $suffix;
            return $assigned;
        }

        return $trimmed;
    }

    public function getByClassId(Request $request) {
        $classId = (int) $request->query('class_id', 0);
        $semesterId = (int) $request->query('semester_id', 0);
        $user = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();
        $isRestrictedTeacher = $user && ($user->hasRole('teacher') || $user->hasRole('class-teacher'))
                             && !$user->hasAnyRole(['super-admin', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'attendance-officer']);
        $forAttendance = $request->query('for_attendance') || $request->query('is_attendance');

        $isClassTeacherThisClass = $user ? \App\Models\AssignedTeacher::where('session_id', $sessionId)
            ->where('teacher_id', $user->id)
            ->where('class_id', $classId)
            ->where('is_class_teacher', true)
            ->exists() : false;

        if ($isRestrictedTeacher && !$isClassTeacherThisClass) {
            $query = \App\Models\AssignedTeacher::where('session_id', $sessionId)
                ->where('teacher_id', $user->id)
                ->where('class_id', $classId);

            if ($forAttendance) {
                $query->where('is_class_teacher', true);
            }

            $assignedSectionIds = $query->pluck('section_id')
                ->unique()->filter()->all();

            $sections = \App\Models\Section::where('class_id', $classId)
                ->whereIn('id', $assignedSectionIds)->get();

            $assignedCourseIds = \App\Models\AssignedTeacher::where('session_id', $sessionId)
                ->where('teacher_id', $user->id)
                ->where('class_id', $classId)
                ->pluck('course_id')->unique()->filter()->all();

            $courses = \App\Models\Course::uniqueByName()
                ->where('class_id', $classId)
                ->whereIn('id', $assignedCourseIds)
                ->orderBy('course_name')->get();
        } else {
            $sections = $this->schoolSectionRepository->getAllByClassId($classId);
            // Use uniqueByName to prevent showing same subject multiple times
            // Optionally filter by semester if provided
            $coursesQuery = \App\Models\Course::uniqueByName()
                ->where('class_id', $classId)
                ->orderBy('course_name');
            if ($semesterId > 0) {
                $coursesQuery->forSemester($semesterId);
            }
            $courses = $coursesQuery->get();
        }

        return response()->json([
            'sections'         => $sections,
            'courses'          => $courses,
            'is_class_teacher' => $isClassTeacherThisClass,
        ])->header('Cache-Control', 'no-store, no-cache');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function show(Section $section)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $section_id
     * @return \Illuminate\Http\Response
     */
    public function edit($section_id)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $section = $this->schoolSectionRepository->findById($section_id);

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'section_id'                => $section_id,
            'section'                   => $section,
        ];
        return view('sections.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $this->schoolSectionRepository->update($request);

            return back()->with('status', 'Section edit was successful!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $section = Section::findOrFail($id);
            $secName = $section->section_name;
            $section->delete();

            return back()->with('status', 'Section "' . $secName . '" deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete section. ' . $e->getMessage());
        }
    }

    /**
     * Remove multiple sections from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $sectionIds = $request->input('section_ids', []);
            if (empty($sectionIds) || !is_array($sectionIds)) {
                return back()->with('error', 'No sections were selected for deletion.');
            }

            $count = Section::whereIn('id', $sectionIds)->delete();

            return back()->with('status', "Successfully deleted {$count} selected section(s)!");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete selected sections: ' . $e->getMessage());
        }
    }
}
