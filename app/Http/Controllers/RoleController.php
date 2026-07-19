<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage roles');
    }

    // -----------------------------------------------------------------------
    // Roles list
    // -----------------------------------------------------------------------
    public function index(): View
    {
        $roles = Role::withCount('permissions', 'users')->orderBy('name')->get();

        return view('roles.index', compact('roles'));
    }

    // -----------------------------------------------------------------------
    // Create role form
    // -----------------------------------------------------------------------
    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
            // Group by first word of permission name for display
            return ucwords(explode(' ', $p->name, 2)[1] ?? 'general');
        });

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:60', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')
            ->with('success', "Role [{$request->name}] created with " . count($request->permissions ?? []) . ' permissions.');
    }

    // -----------------------------------------------------------------------
    // Edit role permissions
    // -----------------------------------------------------------------------
    public function edit(Role $role): View
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
            return ucwords(explode(' ', $p->name, 2)[1] ?? 'general');
        });
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->middleware('permission:manage permissions');

        $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')
            ->with('success', "Role [{$role->name}] permissions updated.");
    }

    // -----------------------------------------------------------------------
    // Delete role (protect system roles)
    // -----------------------------------------------------------------------
    public function destroy(Role $role): RedirectResponse
    {
        $protected = ['super-admin', 'admin', 'teacher', 'student', 'parent'];

        if (in_array($role->name, $protected)) {
            return back()->with('error', "Role [{$role->name}] is a system role and cannot be deleted.");
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Role [{$role->name}] deleted.");
    }

    // -----------------------------------------------------------------------
    // Permission matrix — show/update all roles × all permissions
    // -----------------------------------------------------------------------
    public function matrix(): View
    {
        $this->authorize('manage permissions');

        $roles       = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
            return ucwords(explode(' ', $p->name, 2)[1] ?? 'general');
        });
        $matrix      = [];

        foreach ($roles as $role) {
            $matrix[$role->name] = $role->permissions->pluck('name')->toArray();
        }

        return view('roles.matrix', compact('roles', 'permissions', 'matrix'));
    }

    public function matrixUpdate(Request $request): RedirectResponse
    {
        $this->authorize('manage permissions');

        $data = $request->validate([
            'matrix'          => ['nullable', 'array'],
            'matrix.*'        => ['nullable', 'array'],
            'matrix.*.*'      => ['nullable', 'string', 'exists:permissions,name'],
        ]);

        $roles = Role::all();

        foreach ($roles as $role) {
            if (in_array($role->name, ['super-admin'])) {
                continue; // super-admin bypasses via Gate::before
            }
            $perms = $data['matrix'][$role->name] ?? [];
            $role->syncPermissions(array_values(array_filter($perms)));
        }

        return redirect()->route('roles.matrix')
            ->with('success', 'Permission matrix saved.');
    }

    // -----------------------------------------------------------------------
    // Assign / remove roles from a specific user
    // -----------------------------------------------------------------------
    public function userRoles(User $user): View
    {
        $roles     = Role::orderBy('name')->get();
        $userRoles = $user->getRoleNames()->toArray();

        return view('roles.user-roles', compact('user', 'roles', 'userRoles'));
    }

    public function updateUserRoles(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $user->syncRoles($request->roles ?? []);
        // Keep legacy role column in sync with primary Spatie role
        $primary = $user->getRoleNames()->first();
        if ($primary) {
            $user->update(['role' => $primary]);
        }

        return redirect()->back()
            ->with('success', "Roles updated for {$user->full_name}.");
    }
}
