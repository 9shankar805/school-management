<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        // Reset cached roles and permissions
        if (app()->bound(PermissionRegistrar::class)) {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        }

        // 1. SaaS Granular Permissions
        $permissions = [
            'manage saas schools',
            'manage saas plans',
            'manage saas billing',
            'manage saas payments',
            'manage saas agreements',
            'manage saas services',
            'manage saas messages',
            'manage saas google',
            'manage saas staff',
            'impersonate schools',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // 2. SaaS Roles
        $saasAdmin = Role::firstOrCreate(['name' => 'saas-admin', 'guard_name' => 'web']);
        $saasAdmin->syncPermissions($permissions);

        $saasBilling = Role::firstOrCreate(['name' => 'saas-billing', 'guard_name' => 'web']);
        $saasBilling->syncPermissions([
            'manage saas billing',
            'manage saas payments',
            'manage saas agreements',
            'manage saas services',
            'manage saas plans',
        ]);

        $saasSupport = Role::firstOrCreate(['name' => 'saas-support', 'guard_name' => 'web']);
        $saasSupport->syncPermissions([
            'manage saas schools',
            'manage saas messages',
            'manage saas google',
        ]);

        $saasStaff = Role::firstOrCreate(['name' => 'saas-staff', 'guard_name' => 'web']);
        $saasStaff->syncPermissions([
            'manage saas schools',
            'manage saas messages',
            'manage saas billing',
        ]);
    }

    public function down(): void
    {
        $permissions = [
            'manage saas schools',
            'manage saas plans',
            'manage saas billing',
            'manage saas payments',
            'manage saas agreements',
            'manage saas services',
            'manage saas messages',
            'manage saas google',
            'manage saas staff',
            'impersonate schools',
        ];

        foreach ($permissions as $permName) {
            Permission::where('name', $permName)->delete();
        }

        Role::whereIn('name', ['saas-admin', 'saas-staff', 'saas-billing', 'saas-support'])->delete();
    }
};
