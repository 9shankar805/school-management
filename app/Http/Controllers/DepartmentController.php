<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:view teachers']);
    }

    public function index()
    {
        $user = auth()->user();
        $departments = Department::with('head', 'teachers')->get();

        $teachersQuery = User::where(function ($q) {
            $q->whereIn('role', ['teacher', 'class-teacher', 'admin', 'principal'])
              ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['teacher', 'class-teacher']));
        });

        if ($user->school_id && !$user->hasRole('super-admin')) {
            $teachersQuery->where('school_id', $user->school_id);
        }

        $teachers = $teachersQuery->get();
        return view('teachers.departments.index', compact('departments', 'teachers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create teachers');
        $user = auth()->user();

        $data = $request->validate([
            'name'        => [
                'required', 'string', 'max:255',
                Rule::unique('departments', 'name')->where('school_id', $user->school_id),
            ],
            'code'        => [
                'nullable', 'string', 'max:20',
                Rule::unique('departments', 'code')->where('school_id', $user->school_id),
            ],
            'description' => 'nullable|string|max:1000',
            'head_id'     => 'nullable|exists:users,id',
        ]);

        Department::create(array_merge($data, [
            'school_id' => $user->school_id,
            'is_active' => true,
        ]));

        return back()->with('status', 'Department created.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create teachers');
        $user = auth()->user();

        $query = Department::query();
        if ($user->school_id && !$user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        }

        $dept = $query->findOrFail($id);

        $data = $request->validate([
            'name'        => [
                'required', 'string', 'max:255',
                Rule::unique('departments', 'name')->where('school_id', $user->school_id)->ignore($id),
            ],
            'code'        => [
                'nullable', 'string', 'max:20',
                Rule::unique('departments', 'code')->where('school_id', $user->school_id)->ignore($id),
            ],
            'description' => 'nullable|string|max:1000',
            'head_id'     => 'nullable|exists:users,id',
            'is_active'   => 'boolean',
        ]);

        $dept->update($data);
        return back()->with('status', 'Department updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create teachers');
        $user = auth()->user();

        $query = Department::query();
        if ($user->school_id && !$user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        }

        $dept = $query->findOrFail($id);
        $dept->delete();
        return back()->with('status', 'Department deleted.');
    }

    /** Assign teacher to department */
    public function assignTeacher(Request $request, int $id)
    {
        $this->authorize('create teachers');
        $user = auth()->user();

        $query = Department::query();
        if ($user->school_id && !$user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        }

        $dept = $query->findOrFail($id);

        $teacherQuery = User::where('id', $request->teacher_id);
        if ($user->school_id && !$user->hasRole('super-admin')) {
            $teacherQuery->where('school_id', $user->school_id);
        }
        $teacher = $teacherQuery->firstOrFail();

        $dept->teachers()->syncWithoutDetaching([$teacher->id]);
        return back()->with('status', 'Teacher assigned to department.');
    }

    /** Remove teacher from department */
    public function removeTeacher(int $id, int $teacherId)
    {
        $this->authorize('create teachers');
        $user = auth()->user();

        $query = Department::query();
        if ($user->school_id && !$user->hasRole('super-admin')) {
            $query->where('school_id', $user->school_id);
        }

        $dept = $query->findOrFail($id);
        $dept->teachers()->detach($teacherId);
        return back()->with('status', 'Teacher removed from department.');
    }
}
