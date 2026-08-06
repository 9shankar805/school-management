<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminParentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereHas('roles', function($q) {
            $q->where('name', 'parent');
        })->with('children');

        if ($request->has('search') && $request->search != '') {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('first_name', 'like', "%$s%")
                  ->orWhere('last_name', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%");
            });
        }

        $parents = $query->orderBy('first_name')->paginate(15);
        return view('admin.parents.index', compact('parents'));
    }

    public function create()
    {
        return view('admin.parents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:20',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        $parent = User::create([
            'first_name'  => $validated['first_name'],
            'last_name'   => $validated['last_name'],
            'email'       => $validated['email'],
            'phone'       => $validated['phone'] ?? 'N/A',
            'password'    => Hash::make($validated['password']),
            'school_id'   => auth()->user()->school_id ?? 1,
            'role'        => 'parent',
            'gender'      => 'Other',
            'nationality' => 'N/A',
            'address'     => 'N/A',
            'city'        => 'N/A',
            'zip'         => 'N/A',
        ]);
        
        $parent->assignRole('parent');

        return redirect()->route('parents.index')->with('status', 'Parent account created successfully.');
    }

    public function edit(User $parent)
    {
        // Ensure this user is actually a parent
        if (!$parent->hasRole('parent')) {
            abort(403, 'User is not a parent.');
        }

        $parent->load('children');
        return view('admin.parents.edit', compact('parent'));
    }

    public function update(Request $request, User $parent)
    {
        if (!$parent->hasRole('parent')) abort(403);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => ['required', 'email', Rule::unique('users')->ignore($parent->id)],
            'phone'      => 'nullable|string|max:20',
        ]);

        $parent->update($validated);

        return redirect()->route('parents.index')->with('status', 'Parent profile updated successfully.');
    }

    public function updatePassword(Request $request, User $parent)
    {
        if (!$parent->hasRole('parent')) abort(403);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $parent->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('status', 'Parent password has been reset successfully.');
    }

    public function destroy(User $parent)
    {
        if (!$parent->hasRole('parent')) abort(403);
        
        $parent->delete();
        return redirect()->route('parents.index')->with('status', 'Parent account deleted successfully.');
    }
}
