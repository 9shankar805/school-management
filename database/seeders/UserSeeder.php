<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------------------------------------------
        // Super Admin
        // ----------------------------------------------------------------
        $superAdmin = User::firstOrCreate(['email' => 'superadmin@ut.com'], [
            'first_name' => 'Super',
            'last_name'  => 'Admin',
            'gender'     => 'Male',
            'nationality'=> 'American',
            'phone'      => '1000000000',
            'address'    => '1 HQ Plaza',
            'address2'   => '',
            'city'       => 'New York',
            'zip'        => '10000',
            'role'       => 'super-admin',
            'password'   => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->syncRoles(['super-admin']);

        // ----------------------------------------------------------------
        // Admin
        // ----------------------------------------------------------------
        $admin = User::firstOrCreate(['email' => 'admin@ut.com'], [
            'first_name' => 'Admin',
            'last_name'  => 'User',
            'gender'     => 'Male',
            'nationality'=> 'American',
            'phone'      => '1234567890',
            'address'    => '123 Main St',
            'address2'   => 'Apt 4B',
            'city'       => 'New York',
            'zip'        => '10001',
            'role'       => 'admin',
            'password'   => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles(['admin']);

        // ----------------------------------------------------------------
        // Principal
        // ----------------------------------------------------------------
        $principal = User::firstOrCreate(['email' => 'principal@ut.com'], [
            'first_name' => 'John',
            'last_name'  => 'Principal',
            'gender'     => 'Male',
            'nationality'=> 'American',
            'phone'      => '1234567891',
            'address'    => '10 School Ave',
            'address2'   => '',
            'city'       => 'New York',
            'zip'        => '10001',
            'role'       => 'principal',
            'password'   => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $principal->syncRoles(['principal']);

        // ----------------------------------------------------------------
        // Teachers
        // ----------------------------------------------------------------
        $teachers = [
            ['first_name' => 'John',   'last_name' => 'Smith',   'email' => 'john.smith@school.com',   'role' => 'teacher'],
            ['first_name' => 'Jane',   'last_name' => 'Doe',     'email' => 'jane.doe@school.com',     'role' => 'class-teacher'],
            ['first_name' => 'Robert', 'last_name' => 'Johnson', 'email' => 'robert.j@school.com',     'role' => 'teacher'],
        ];

        foreach ($teachers as $t) {
            $user = User::firstOrCreate(['email' => $t['email']], [
                'first_name'        => $t['first_name'],
                'last_name'         => $t['last_name'],
                'gender'            => 'Male',
                'nationality'       => 'American',
                'phone'             => '555' . rand(1000000, 9999999),
                'address'           => '456 Oak Ave',
                'city'              => 'New York',
                'zip'               => '10002',
                'role'              => $t['role'],
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->syncRoles([$t['role']]);
        }

        // ----------------------------------------------------------------
        // Students
        // ----------------------------------------------------------------
        $firstNames = ['Michael','Emily','David','Sarah','James','Jessica','Christopher','Amanda','Daniel','Jennifer'];
        $lastNames  = ['Williams','Brown','Jones','Garcia','Miller','Davis','Rodriguez','Martinez','Hernandez','Lopez'];

        for ($i = 0; $i < 10; $i++) {
            $email = strtolower($firstNames[$i]) . '.' . strtolower($lastNames[$i]) . '@school.com';
            $student = User::firstOrCreate(['email' => $email], [
                'first_name'        => $firstNames[$i],
                'last_name'         => $lastNames[$i],
                'gender'            => $i % 2 === 0 ? 'Male' : 'Female',
                'nationality'       => 'American',
                'phone'             => '555' . rand(1000000, 9999999),
                'address'           => '789 Pine Rd',
                'city'              => 'New York',
                'zip'               => '10003',
                'role'              => 'student',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $student->syncRoles(['student']);
        }

        // ----------------------------------------------------------------
        // Parent
        // ----------------------------------------------------------------
        $parent = User::firstOrCreate(['email' => 'parent@ut.com'], [
            'first_name'        => 'Mary',
            'last_name'         => 'Parent',
            'gender'            => 'Female',
            'nationality'       => 'American',
            'phone'             => '5550000001',
            'address'           => '100 Parent Lane',
            'address2'          => '',
            'city'              => 'New York',
            'zip'               => '10005',
            'role'              => 'parent',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $parent->syncRoles(['parent']);

        // ----------------------------------------------------------------
        // Staff (accountant, librarian, hr-manager, receptionist)
        // ----------------------------------------------------------------
        $staff = [
            ['first_name' => 'Alice',  'last_name' => 'Cooper',  'email' => 'alice.c@school.com',    'role' => 'accountant'],
            ['first_name' => 'Bob',    'last_name' => 'Marley',  'email' => 'bob.m@school.com',      'role' => 'librarian'],
            ['first_name' => 'Carol',  'last_name' => 'HR',      'email' => 'carol.hr@school.com',   'role' => 'hr-manager'],
            ['first_name' => 'David',  'last_name' => 'Desk',    'email' => 'david.r@school.com',    'role' => 'receptionist'],
            ['first_name' => 'Emma',   'last_name' => 'Exam',    'email' => 'emma.ex@school.com',    'role' => 'exam-controller'],
            ['first_name' => 'Frank',  'last_name' => 'Attend',  'email' => 'frank.a@school.com',    'role' => 'attendance-officer'],
            ['first_name' => 'Grace',  'last_name' => 'Admit',   'email' => 'grace.ad@school.com',   'role' => 'admission-officer'],
            ['first_name' => 'Henry',  'last_name' => 'Bus',     'email' => 'henry.t@school.com',    'role' => 'transport-manager'],
            ['first_name' => 'Irene',  'last_name' => 'Hostel',  'email' => 'irene.h@school.com',    'role' => 'hostel-manager'],
        ];

        foreach ($staff as $s) {
            $user = User::firstOrCreate(['email' => $s['email']], [
                'first_name'        => $s['first_name'],
                'last_name'         => $s['last_name'],
                'gender'            => 'Female',
                'nationality'       => 'American',
                'phone'             => '555' . rand(1000000, 9999999),
                'address'           => '123 Staff Rd',
                'address2'          => '',
                'city'              => 'New York',
                'zip'               => '10004',
                'role'              => $s['role'],
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->syncRoles([$s['role']]);
        }

        $this->command?->info('✓ All users seeded with Spatie roles assigned.');
    }
}
