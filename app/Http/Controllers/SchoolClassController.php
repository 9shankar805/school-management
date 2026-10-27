<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Http\Requests\SchoolClassStoreRequest;
use App\Traits\SchoolSession;

class SchoolClassController extends Controller
{
    use SchoolSession;
    protected $schoolClassRepository;
    protected $schoolSessionRepository;

    /**
    * Create a new Controller instance
    * 
    * @param SchoolClassInterface $schoolClassRepository
    * @return void
    */
    public function __construct(SchoolSessionInterface $schoolSessionRepository, SchoolClassInterface $schoolClassRepository) {
        $this->middleware(['can:view classes']);

        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $data = $this->schoolClassRepository->getClassesAndSections($current_school_session_id);
        $data['current_school_session_id'] = $current_school_session_id;

        return view('classes.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        return view('classes.create', compact('current_school_session_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  SchoolClassStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SchoolClassStoreRequest $request)
    {
        try {
            $sessionId = (int) $request->session_id;
            $createdClasses = [];
            $defaultSectionsStr = $request->input('initial_sections_str', 'A, B');
            $defaultRoomNo = $request->input('default_room_no', '101');

            $globalRoomCounter = $defaultRoomNo;

            // 1. Check if structured 'classes' array was submitted (Dynamic Rows Mode)
            if ($request->has('classes') && is_array($request->classes) && count($request->classes) > 0) {
                foreach ($request->classes as $clsData) {
                    $className = trim($clsData['class_name'] ?? '');
                    if (empty($className)) continue;

                    $class = SchoolClass::firstOrCreate([
                        'class_name' => $className,
                        'session_id' => $sessionId,
                    ]);
                    $createdClasses[] = $class->class_name;

                    // Sections for this row
                    $secStr = !empty($clsData['initial_sections']) ? $clsData['initial_sections'] : $defaultSectionsStr;
                    $rowRoomCounter = !empty($clsData['room_no']) ? $clsData['room_no'] : $globalRoomCounter;

                    $this->createSectionsForClass($class->id, $sessionId, $secStr, $rowRoomCounter);
                    if (empty($clsData['room_no'])) {
                        $globalRoomCounter = $rowRoomCounter;
                    }
                }
            }
            // 2. Check if bulk comma/newline separated string was submitted (Bulk Text Mode)
            elseif ($request->filled('bulk_class_names')) {
                // Split by comma or newlines
                $rawNames = preg_split('/[\r\n,]+/', $request->bulk_class_names);
                $bulkRoomCounter = $defaultRoomNo;

                foreach ($rawNames as $rawName) {
                    $className = trim($rawName);
                    if (empty($className)) continue;

                    $class = SchoolClass::firstOrCreate([
                        'class_name' => $className,
                        'session_id' => $sessionId,
                    ]);
                    $createdClasses[] = $class->class_name;

                    $this->createSectionsForClass($class->id, $sessionId, $defaultSectionsStr, $bulkRoomCounter);
                }
            }
            // 3. Fallback: Single Class creation
            elseif ($request->filled('class_name')) {
                $class = SchoolClass::firstOrCreate([
                    'class_name' => trim($request->class_name),
                    'session_id' => $sessionId,
                ]);
                $createdClasses[] = $class->class_name;
                $singleRoomCounter = $defaultRoomNo;

                $this->createSectionsForClass($class->id, $sessionId, $defaultSectionsStr, $singleRoomCounter);
            }

            $count = count($createdClasses);
            if ($count === 0) {
                return back()->with('error', 'No valid class names were provided.');
            }

            if ($count === 1) {
                return back()->with('status', 'Class "' . $createdClasses[0] . '" created successfully with sections and rooms starting at ' . $defaultRoomNo . '!');
            }

            return back()->with('status', "Successfully created {$count} classes: " . implode(', ', array_slice($createdClasses, 0, 5)) . ($count > 5 ? " and " . ($count - 5) . " more." : ".") . " Rooms assigned sequentially starting from {$defaultRoomNo}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create classes. ' . $e->getMessage());
        }
    }

    /**
     * Helper to create sections for a given class with sequential room numbers.
     */
    private function createSectionsForClass(int $classId, int $sessionId, ?string $sectionsStr, string &$roomCounter): void
    {
        if (empty($sectionsStr)) {
            $sectionsStr = 'A, B';
        }

        $sections = explode(',', $sectionsStr);
        foreach ($sections as $secName) {
            $secName = trim($secName);
            if (!empty($secName)) {
                $assignedRoom = $this->incrementRoomNumber($roomCounter);
                \App\Models\Section::firstOrCreate([
                    'class_id'     => $classId,
                    'section_name' => $secName,
                    'session_id'   => $sessionId,
                ], [
                    'room_no'      => $assignedRoom,
                ]);
            }
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SchoolClass  $schoolClass
     * @return \Illuminate\Http\Response
     */
    public function show(SchoolClass $schoolClass)
    {
        //
    }

    /**
     * Full class detail page — students, subject teachers, timetable.
     */
    public function detail(int $id)
    {
        $sessionId   = $this->getSchoolCurrentSession();
        $schoolClass = SchoolClass::with(['sections', 'courses'])->findOrFail($id);

        // All sections for this class
        $sections = $schoolClass->sections;

        // Students enrolled via promotions (current session)
        $promotions = \App\Models\Promotion::with(['student.academic_info', 'student.parent_info', 'section'])
            ->where('class_id', $id)
            ->where('session_id', $sessionId)
            ->orderBy('section_id')
            ->get();

        // Assigned teachers (subject → teacher mapping)
        $assignedTeachers = \App\Models\AssignedTeacher::with(['teacher', 'course', 'section'])
            ->where('class_id', $id)
            ->where('session_id', $sessionId)
            ->get();

        // Class teacher — teacher explicitly designated as Class Teacher (is_class_teacher = 1),
        // or falling back to first assigned subject teacher
        $classTeacher = $assignedTeachers->where('is_class_teacher', true)->first()?->teacher ?? $assignedTeachers->first()?->teacher;


        // Timetable for this class — all sections, grouped by section → weekday
        $timetableSlots = \App\Models\Routine::with(['course', 'section', 'teacher'])
            ->where('class_id', $id)
            ->where('session_id', $sessionId)
            ->orderBy('section_id')
            ->orderBy('weekday')
            ->orderBy('start')
            ->get()
            ->groupBy('section_id');

        // All active teaching staff in the school for assignment dropdown
        $allTeachers = \App\Models\User::role(['teacher', 'class-teacher'])
            ->orderBy('first_name')
            ->get();

        return view('classes.show', compact(
            'schoolClass', 'sections', 'promotions',
            'assignedTeachers', 'classTeacher', 'timetableSlots', 'sessionId', 'allTeachers'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  $class_id
     * @return \Illuminate\Http\Response
     */
    public function edit($class_id)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $schoolClass = $this->schoolClassRepository->findById($class_id);
        $sections = \App\Models\Section::where('class_id', $class_id)->get();

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'class_id'                  => $class_id,
            'schoolClass'               => $schoolClass,
            'sections'                  => $sections,
        ];
        return view('classes.edit', $data);
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
            $this->schoolClassRepository->update($request);

            return back()->with('status', 'Class updated successfully!');
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
            $class = SchoolClass::findOrFail($id);
            $className = $class->class_name;
            \App\Models\Section::where('class_id', $id)->delete();
            $class->delete();

            return back()->with('status', 'Class "' . $className . '" deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete class. ' . $e->getMessage());
        }
    }

    /**
     * Remove multiple classes and their sections from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $classIds = $request->input('class_ids', []);
            if (empty($classIds) || !is_array($classIds)) {
                return back()->with('error', 'No classes were selected for deletion.');
            }

            $classes = SchoolClass::whereIn('id', $classIds)->get();
            $count = $classes->count();
            if ($count === 0) {
                return back()->with('error', 'Selected classes were not found.');
            }

            // Delete related sections first
            \App\Models\Section::whereIn('class_id', $classIds)->delete();
            // Delete classes
            SchoolClass::whereIn('id', $classIds)->delete();

            return back()->with('status', "Successfully deleted {$count} selected class(es) and their sections!");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete selected classes: ' . $e->getMessage());
        }
    }
}
