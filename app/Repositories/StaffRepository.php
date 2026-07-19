<?php

namespace App\Repositories;

use App\Models\User;
use App\Interfaces\StaffInterface;
use Illuminate\Support\Facades\Hash;

class StaffRepository implements StaffInterface {
    public function getAllStaff() {
        return User::whereNotIn('role', ['student', 'teacher', 'parent'])->get();
    }

    public function findById($id) {
        return User::findOrFail($id);
    }

    public function create($request) {
        $data = $request->all();
        $data['password'] = Hash::make($data['password'] ?? 'password');
        
        $user = User::create($data);
        if (isset($data['role']) && !empty($data['role'])) {
            $user->assignRole($data['role']);
        }
        return $user;
    }

    public function update($request, $id) {
        $user = User::findOrFail($id);
        $data = $request->except(['password']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return $user;
    }

    public function delete($id) {
        $user = User::findOrFail($id);
        $user->delete();
    }
}
