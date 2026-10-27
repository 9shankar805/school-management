<?php

namespace App\Repositories;

use App\Models\User;
use App\Interfaces\StaffInterface;
use Illuminate\Support\Facades\Hash;

class StaffRepository implements StaffInterface {
    public function getAllStaff($search = null, $role = null) {
        $query = User::with('roles')
            ->where(function($q) {
                $q->whereDoesntHave('roles', fn($r) => $r->whereIn('name', ['student', 'parent']))
                  ->where(function($sub) {
                      $sub->whereNull('role')
                          ->orWhereNotIn('role', ['student', 'parent']);
                  });
            });

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where(function($q) use ($role) {
                $q->whereHas('roles', fn($r) => $r->where('name', $role))
                  ->orWhere('role', $role);
            });
        }

        return $query->latest()->paginate(20)->withQueryString();
    }

    public function findById($id) {
        return User::findOrFail($id);
    }

    public function create($request) {
        $data = $request->except(['password_confirmation', 'photo']);
        $data['password'] = Hash::make($request->password);

        // Ensure nullable address fields don't violate NOT NULL constraints
        $data['address']  = $data['address']  ?? '';
        $data['address2'] = $data['address2'] ?? null;
        $data['city']     = $data['city']     ?? '';
        $data['zip']      = $data['zip']      ?? '';

        if (auth()->check() && !isset($data['school_id'])) {
            $data['school_id'] = auth()->user()->school_id;
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('profile_photos', 'public');
        }

        $user = User::create($data);

        if (!empty($data['role'])) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $data['role'], 'guard_name' => 'web']);
            $user->assignRole($data['role']);
            $user->role = $data['role'];
            $user->save();
        }

        return $user;
    }

    public function update($request, $id) {
        $user = User::findOrFail($id);
        $data = $request->except(['password', 'password_confirmation', 'photo']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('profile_photos', 'public');
        }

        $user->update($data);

        if ($request->filled('role')) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $request->role, 'guard_name' => 'web']);
            $user->syncRoles([$request->role]);
            $user->role = $request->role;
            $user->save();
        }

        return $user;
    }

    public function delete($id) {
        $user = User::findOrFail($id);
        $user->delete();
    }
}
