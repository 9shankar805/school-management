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
        try {
            $roles = Role::withCount('permissions', 'users')->orderBy('name')->get();
            return view('roles.index', compact('roles'));
        } catch (\Exception $e) {
            return view('roles.index', ['roles' => collect(), 'error' => 'Unable to load roles: ' . $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------------------
    // Create role form
    // -----------------------------------------------------------------------
    public function create(): View
    {
        try {
            $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
                // Group by first word of permission name for display
                return ucwords(explode(' ', $p->name, 2)[1] ?? 'general');
            });

            return view('roles.create', compact('permissions'));
        } catch (\Exception $e) {
            return view('roles.create', ['permissions' => collect(), 'error' => 'Unable to load permissions: ' . $e->getMessage()]);
        }
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
        try {
            $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
                return ucwords(explode(' ', $p->name, 2)[1] ?? 'general');
            });
            $rolePermissions = $role->permissions->pluck('id')->toArray();

            return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
        } catch (\Exception $e) {
            return view('roles.edit', [
                'role' => $role,
                'permissions' => collect(),
                'rolePermissions' => [],
                'error' => 'Unable to load role permissions: ' . $e->getMessage()
            ]);
        }
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
        try {
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
        } catch (\Exception $e) {
            return view('roles.matrix', [
                'roles' => collect(),
                'permissions' => collect(),
                'matrix' => [],
                'error' => 'Unable to load permission matrix: ' . $e->getMessage()
            ]);
        }
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
    // List of assignable users (with filters) — school-scoped
    // -----------------------------------------------------------------------
    public function assignableUsers(): View
    {
        try {
            $query = User::with('roles');

            if (!auth()->user()->isSuperAdmin()) {
                $query->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super-admin'));
            }

            if ($search = request('search')) {
                $like = '%' . $search . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('first_name', 'like', $like)
                      ->orWhere('last_name', 'like', $like)
                      ->orWhere('email', 'like', $like)
                      ->orWhere('phone', 'like', $like)
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", [$like]);
                });
            }

            if ($roleFilter = request('role')) {
                $query->role($roleFilter);
            }

            $users = $query->orderByDesc('id')->paginate(25)->withQueryString();

            return view('roles.assignable-users', compact('users'));
        } catch (\Exception $e) {
            return view('roles.assignable-users', ['users' => null, 'error' => 'Unable to load users: ' . $e->getMessage()]);
        }
    }

    // -----------------------------------------------------------------------
    // Assign / remove roles from a specific user
    // -----------------------------------------------------------------------
    public function userRoles(User $user): View
    {
        try {
            if (!auth()->user()->isSuperAdmin() && $user->hasRole('super-admin')) {
                abort(403, 'You cannot modify super-admin users.');
            }

            $roles = Role::when(!auth()->user()->isSuperAdmin(), fn ($q) => $q->where('name', '!=', 'super-admin'))
                         ->orderBy('name')
                         ->get();
            $userRoles = $user->getRoleNames()->toArray();

            return view('roles.user-roles', compact('user', 'roles', 'userRoles'));
        } catch (\Exception $e) {
            return view('roles.user-roles', [
                'user' => $user,
                'roles' => collect(),
                'userRoles' => [],
                'error' => 'Unable to load user roles: ' . $e->getMessage()
            ]);
        }
    }

    public function updateUserRoles(Request $request, User $user): RedirectResponse
    {
        try {
            if (!auth()->user()->isSuperAdmin() && $user->hasRole('super-admin')) {
                abort(403, 'You cannot modify super-admin users.');
            }

            $request->validate([
                'roles'   => ['nullable', 'array'],
                'roles.*' => ['string', 'exists:roles,name'],
            ]);

            $roles = $request->roles ?? [];
            if (!auth()->user()->isSuperAdmin()) {
                $roles = array_values(array_filter($roles, fn ($r) => $r !== 'super-admin'));
            }

            $user->syncRoles($roles);
            $primary = $user->getRoleNames()->first();
            if ($primary) {
                $user->update(['role' => $primary]);
            } elseif ($user->role !== null) {
                $user->update(['role' => null]);
            }

            return redirect()->back()
                ->with('success', "Roles updated for {$user->full_name}.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Unable to update user roles: ' . $e->getMessage());
        }
    }
}
