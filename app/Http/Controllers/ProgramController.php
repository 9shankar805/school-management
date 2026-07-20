<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Department;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;

class ProgramController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->middleware('auth');
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    public function index()
    {
        $this->authorize('view academic settings');

        $programs = Program::with(['department', 'classes'])
            ->withCount('curriculums')
            ->latest()
            ->get();

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('programs.index', compact('programs', 'departments'));
    }

    public function store(Request $request)
    {
        $this->authorize('view academic settings');

        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'code'           => 'nullable|string|max:20|unique:programs,code',
            'description'    => 'nullable|string',
            'level'          => 'required|in:primary,secondary,higher_secondary,undergraduate',
            'duration_years' => 'required|integer|min:1|max:10',
            'department_id'  => 'nullable|exists:departments,id',
            'class_ids'      => 'nullable|array',
            'class_ids.*'    => 'exists:school_classes,id',
        ]);

        $program = Program::create($data);

        if (!empty($data['class_ids'])) {
            $program->classes()->sync($data['class_ids']);
        }

        return back()->with('status', "Program '{$program->name}' created successfully.");
    }

    public function edit(int $id)
    {
        $this->authorize('view academic settings');

        $program     = Program::with('classes')->findOrFail($id);
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $classes     = SchoolClass::orderBy('class_name')->get();

        return view('programs.edit', compact('program', 'departments', 'classes'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('view academic settings');

        $program = Program::findOrFail($id);

        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'code'           => 'nullable|string|max:20|unique:programs,code,' . $id,
            'description'    => 'nullable|string',
            'level'          => 'required|in:primary,secondary,higher_secondary,undergraduate',
            'duration_years' => 'required|integer|min:1|max:10',
            'department_id'  => 'nullable|exists:departments,id',
            'is_active'      => 'boolean',
            'class_ids'      => 'nullable|array',
            'class_ids.*'    => 'exists:school_classes,id',
        ]);

        $program->update($data);
        $program->classes()->sync($data['class_ids'] ?? []);

        return back()->with('status', "Program updated.");
    }

    public function destroy(int $id)
    {
        $this->authorize('view academic settings');

        $program = Program::findOrFail($id);
        $program->delete();

        return back()->with('status', "Program '{$program->name}' deleted.");
    }
}
