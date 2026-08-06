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
            $class = SchoolClass::create([
                'class_name' => $request->class_name,
                'session_id' => $request->session_id,
            ]);

            // Auto-create initial sections if provided
            if ($request->has('initial_sections_str') && !empty($request->initial_sections_str)) {
                $sections = explode(',', $request->initial_sections_str);
                foreach ($sections as $secName) {
                    $secName = trim($secName);
                    if (!empty($secName)) {
                        \App\Models\Section::create([
                            'section_name' => $secName,
                            'room_no'      => $request->input('default_room_no', '101'),
                            'class_id'     => $class->id,
                            'session_id'   => $request->session_id,
                        ]);
                    }
                }
            }

            return back()->with('status', 'Class "' . $class->class_name . '" created successfully!');
        } catch (\Exception $e) {
            return back()->withError('Failed to create class. ' . $e->getMessage());
        }
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

        // Class teacher — teacher who teaches the most subjects in this class,
        // or the one explicitly marked (first assigned, as a convention)
        $classTeacher = $assignedTeachers->first()?->teacher;

        // Timetable for this class — all sections, grouped by section → weekday
        $timetableSlots = \App\Models\Routine::with(['course', 'section', 'teacher'])
            ->where('class_id', $id)
            ->where('session_id', $sessionId)
            ->orderBy('section_id')
            ->orderBy('weekday')
            ->orderBy('start')
            ->get()
            ->groupBy('section_id');

        return view('classes.show', compact(
            'schoolClass', 'sections', 'promotions',
            'assignedTeachers', 'classTeacher', 'timetableSlots', 'sessionId'
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
            return back()->withError($e->getMessage());
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
            return back()->withError('Failed to delete class. ' . $e->getMessage());
        }
    }
}
