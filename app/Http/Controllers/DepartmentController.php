<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:view teachers']);
    }

    public function index()
    {
        $departments = Department::with('head', 'teachers')->get();
        $teachers    = User::role(['teacher', 'class-teacher'])->get();
        return view('teachers.departments.index', compact('departments', 'teachers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create teachers');
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:departments,name',
            'code'        => 'nullable|string|max:20|unique:departments,code',
            'description' => 'nullable|string|max:1000',
            'head_id'     => 'nullable|exists:users,id',
        ]);
        Department::create(array_merge($data, ['is_active' => true]));
        return back()->with('status', 'Department created.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create teachers');
        $dept = Department::findOrFail($id);
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:departments,name,' . $id,
            'code'        => 'nullable|string|max:20|unique:departments,code,' . $id,
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
        Department::findOrFail($id)->delete();
        return back()->with('status', 'Department deleted.');
    }

    /** Assign teacher to department */
    public function assignTeacher(Request $request, int $id)
    {
        $this->authorize('create teachers');
        $request->validate(['teacher_id' => 'required|exists:users,id']);
        $dept = Department::findOrFail($id);
        $dept->teachers()->syncWithoutDetaching([$request->teacher_id]);
        return back()->with('status', 'Teacher assigned to department.');
    }

    /** Remove teacher from department */
    public function removeTeacher(int $id, int $teacherId)
    {
        $this->authorize('create teachers');
        Department::findOrFail($id)->teachers()->detach($teacherId);
        return back()->with('status', 'Teacher removed from department.');
    }
}
