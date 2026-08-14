<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class SchoolRegistrationController extends Controller
{
    /** GET /register/school — show the sign-up form */
    public function create()
    {
        return view('saas.register');
    }

    /** POST /register/school — process sign-up */
    public function store(Request $request)
    {
        $data = $request->validate([
            'school_name'    => 'required|string|max:120',
            'iemis_code'     => 'nullable|string|max:20',
            'district'       => 'required|string|max:80',
            'province'       => 'required|string|max:80',
            'school_email'   => 'required|email|unique:schools,email',
            'school_phone'   => 'nullable|string|max:20',
            'plan'           => 'required|in:free,starter,growth,enterprise',
            'contact_person' => 'required|string|max:100',
            'admin_email'    => 'required|email|unique:users,email',
            'admin_password' => 'required|min:8|confirmed',
        ]);

        DB::transaction(function () use ($data) {
            $limits = School::planLimits()[$data['plan']];

            // 1. Create the school record — always pending.
            //    NO auto-approval regardless of plan. Super-admin must approve every
            //    school before its admin can log in. This guarantees no school can
            //    access the platform without manual review.
            $school = School::create([
                'name'            => $data['school_name'],
                'slug'            => School::makeSlug($data['school_name']),
                'iemis_code'      => $data['iemis_code'] ?? null,
                'email'           => $data['school_email'],
                'phone'           => $data['school_phone'] ?? null,
                'district'        => $data['district'],
                'province'        => $data['province'],
                'country'         => 'Nepal',
                'plan'            => $data['plan'],
                'max_students'    => $limits['students'],
                'max_teachers'    => $limits['teachers'],
                'max_staff'       => $limits['staff'],
                'ai_features_enabled' => $limits['ai'],
                'contact_person'  => $data['contact_person'],
                'contact_email'   => $data['admin_email'],
                'status'          => 'pending',
                'approved_at'     => null,
            ]);

            // 2. Create the school admin user.
            //    email_verified_at is left NULL until the super-admin approves the
            //    school, at which point the "approve" action marks it as verified.
            $nameParts = explode(' ', trim($data['contact_person']));
            $adminUser = User::create([
                'school_id'         => $school->id,
                'first_name'        => $nameParts[0],
                'last_name'         => implode(' ', array_slice($nameParts, 1)) ?: '-',
                'email'             => $data['admin_email'],
                'password'          => Hash::make($data['admin_password']),
                'role'              => 'admin',
                'gender'            => 'Male',
                'nationality'       => 'Nepali',
                'phone'             => $data['school_phone'] ?? '0000000000',
                'address'           => $data['district'] . ', ' . $data['province'],
                'address2'          => '',
                'city'              => $data['district'],
                'zip'               => '00000',
                'email_verified_at' => null,
            ]);

            // 3. Assign admin role
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            $adminUser->assignRole($adminRole);

            // 4. Set owner
            $school->update(['owner_id' => $adminUser->id]);
        });

        return redirect()->route('saas.registered')
                         ->with('success', 'Your school registration has been submitted. A Super Admin will review and approve your school — you will receive a confirmation email once it is active.');
    }

    /** GET /register/school/success */
    public function registered()
    {
        return view('saas.registered');
    }
}
