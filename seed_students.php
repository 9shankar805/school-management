<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\School;
use App\Models\SchoolSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentAcademicInfo;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$school = School::find(3);
if (!$school) {
    echo "School not found.\n";
    exit;
}

$session = SchoolSession::where('school_id', $school->id)->latest()->first();
if (!$session) {
    echo "No session found.\n";
    exit;
}

$classes = SchoolClass::where('school_id', $school->id)->get();
echo "Found " . $classes->count() . " classes.\n";

$faker = \Faker\Factory::create();
$password = Hash::make('password');

foreach ($classes as $cls) {
    $sections = Section::where('class_id', $cls->id)->get();
    foreach ($sections as $sec) {
        $count = \App\Models\Promotion::where('school_id', $school->id)->where('class_id', $cls->id)->where('section_id', $sec->id)->count();
        $toAdd = 20 - $count;
        if ($toAdd > 0) {
            echo "Adding $toAdd students to Class " . $cls->name . " - " . $sec->name . "\n";
            for ($i = 0; $i < $toAdd; $i++) {
                DB::transaction(function() use ($school, $session, $cls, $sec, $faker, $password) {
                    $user = User::create([
                        'first_name' => $faker->firstName,
                        'last_name' => $faker->lastName,
                        'email' => $faker->unique()->safeEmail,
                        'password' => $password,
                        'gender' => $faker->randomElement(['male', 'female']),
                        'phone' => $faker->phoneNumber,
                        'school_id' => $school->id,
                        'nationality' => 'Indian',
                        'blood_group' => 'A+',
                        'religion' => 'Hindu',
                        'dob' => '2010-01-01',
                        'address' => $faker->address,
                        'city' => $faker->city,
                        'zip' => $faker->postcode,
                        'role' => 'student',
                    ]);
                    $user->assignRole('student');
                    
                    StudentAcademicInfo::create([
                        'student_id' => $user->id,
                        'school_id' => $school->id,
                        'board_reg_no' => 'REG' . rand(10000, 99999),
                    ]);
                    
                    \App\Models\Promotion::create([
                        'student_id' => $user->id,
                        'school_id' => $school->id,
                        'session_id' => $session->id,
                        'class_id' => $cls->id,
                        'section_id' => $sec->id,
                        'id_card_number' => 'STU' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                    ]);
                });
            }
        } else {
            echo "Class " . $cls->name . " - " . $sec->name . " already has $count students.\n";
        }
    }
}
echo "Done.\n";
