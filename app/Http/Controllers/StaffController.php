<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\StaffInterface;

class StaffController extends Controller
{
    protected $staffRepository;

    public function __construct(StaffInterface $staffRepository) {
        $this->staffRepository = $staffRepository;
    }

    public function index() {
        $staffs = $this->staffRepository->getAllStaff();
        return view('staff.index', compact('staffs'));
    }

    public function create() {
        return view('staff.create');
    }

    public function store(Request $request) {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'required|string',
        ]);

        $this->staffRepository->create($request);
        return redirect()->route('staff.index')->with('status', 'Staff added successfully.');
    }

    public function edit($id) {
        $staff = $this->staffRepository->findById($id);
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
        ]);

        $this->staffRepository->update($request, $id);
        return redirect()->route('staff.index')->with('status', 'Staff updated successfully.');
    }

    public function destroy($id) {
        $this->staffRepository->delete($id);
        return redirect()->route('staff.index')->with('status', 'Staff deleted successfully.');
    }
}
