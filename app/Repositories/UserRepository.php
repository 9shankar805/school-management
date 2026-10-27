<?php

namespace App\Repositories;

use App\Models\User;
use App\Traits\Base64ToFile;
use App\Interfaces\UserInterface;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Repositories\PromotionRepository;
use App\Repositories\StudentParentInfoRepository;
use App\Repositories\StudentAcademicInfoRepository;

class UserRepository implements UserInterface {
    use Base64ToFile;

    /**
     * @param mixed $request
     * @return string
    */
    public function createTeacher($request) {
        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'first_name'    => $request['first_name'],
                    'last_name'     => $request['last_name'],
                    'email'         => $request['email'],
                    'gender'        => $request['gender'],
                    'nationality'   => $request['nationality'],
                    'phone'         => $request['phone'],
                    'address'       => $request['address'],
                    'address2'      => $request['address2'] ?? null,
                    'city'          => $request['city'],
                    'zip'           => $request['zip'],
                    'photo'         => (!empty($request['photo']))?$this->convert($request['photo']):null,
                    'role'          => 'teacher',
                    'password'      => Hash::make($request['password']),
                ]);

                // Assign the Spatie "teacher" role so hasRole('teacher'), role('teacher')
                // queries, role-based middleware, and the admin-facing role assignment
                // dropdown all recognize this user as a teacher.
                $user->assignRole('teacher');

                $user->givePermissionTo(
                    'create exams',
                    'view exams',
                    'create exams rule',
                    'view exams rule',
                    'edit exams rule',
                    'delete exams rule',
                    'take attendances',
                    'view attendances',
                    'create assignments',
                    'view assignments',
                    'save marks',
                    'view users',
                    'view routines',
                    'view syllabi',
                    'view events',
                    'view notices',
                );
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to create Teacher. '.$e->getMessage());
        }
    }

    /**
     * @param mixed $request
     * @return string
    */
    public function createStudent($request) {
        try {
            DB::transaction(function () use ($request) {
                $student = User::create([
                    'first_name'    => $request['first_name'],
                    'last_name'     => $request['last_name'],
                    'email'         => $request['email'],
                    'gender'        => $request['gender'],
                    'nationality'   => $request['nationality'],
                    'phone'         => $request['phone'],
                    'address'       => $request['address'],
                    'address2'      => $request['address2'] ?? null,
                    'city'          => $request['city'],
                    'zip'           => $request['zip'],
                    'photo'         => (!empty($request['photo']))?$this->convert($request['photo']):null,
                    'birthday'      => $request['birthday'],
                    'religion'      => $request['religion'],
                    'blood_type'    => $request['blood_type'],
                    'roll_no'       => $request['roll_no'] ?? null,
                    'role'          => 'student',
                    'password'      => Hash::make($request['password']),
                ]);

                // Store Parents' information
                $studentParentInfoRepository = new StudentParentInfoRepository();
                $studentParentInfoRepository->store($request, $student->id);

                // Store Academic information
                $studentAcademicInfoRepository = new StudentAcademicInfoRepository();
                $studentAcademicInfoRepository->store($request, $student->id);

                // Assign student to a Class and a Section
                $promotionRepository = new PromotionRepository();
                $promotionRepository->assignClassSection($request, $student->id);

                $student->assignRole('student');

                // If direct parent link credentials are provided, handle parent user creation & linking
                if (!empty($request['parent_email']) && !empty($request['parent_password'])) {
                    $parent = User::firstOrCreate(
                        ['email' => $request['parent_email']],
                        [
                            'first_name'  => (!empty($request['parent_relationship']) && $request['parent_relationship'] == 'mother') ? $request['mother_name'] : $request['father_name'],
                            'last_name'   => $request['last_name'],
                            'password'    => Hash::make($request['parent_password']),
                            'phone'       => (!empty($request['parent_relationship']) && $request['parent_relationship'] == 'mother') ? $request['mother_phone'] : $request['father_phone'],
                            'gender'      => 'Other',
                            'nationality' => $request['nationality'] ?? 'N/A',
                            'address'     => $request['parent_address'] ?? 'N/A',
                            'city'        => $request['city'] ?? 'N/A',
                            'zip'         => $request['zip'] ?? 'N/A',
                            'role'        => 'parent',
                            'school_id'   => auth()->user()->school_id ?? 1,
                        ]
                    );

                    if (!$parent->hasRole('parent')) {
                        $parent->assignRole('parent');
                    }

                    $rel = !empty($request['parent_relationship']) ? $request['parent_relationship'] : 'guardian';
                    
                    $parent->children()->syncWithoutDetaching([
                        $student->id => [
                            'relationship' => $rel,
                            'is_primary'   => true,
                        ],
                    ]);
                }

                $student->givePermissionTo(
                    'view attendances',
                    'view assignments',
                    'submit assignments',
                    'view exams',
                    'view marks',
                    'view routines',
                    'view syllabi',
                    'view events',
                    'view notices',
                );
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to create Student. '.$e->getMessage());
        }
    }

    public function updateStudent($request) {
        try {
            DB::transaction(function () use ($request) {
                $fields = [
                    'first_name'    => $request['first_name'],
                    'last_name'     => $request['last_name'],
                    'email'         => $request['email'],
                    'gender'        => $request['gender'],
                    'nationality'   => $request['nationality'],
                    'phone'         => $request['phone'],
                    'address'       => $request['address'],
                    'address2'      => $request['address2'] ?? null,
                    'city'          => $request['city'],
                    'zip'           => $request['zip'],
                    'birthday'      => $request['birthday'],
                    'religion'      => $request['religion'],
                    'blood_type'    => $request['blood_type'],
                ];

                if (array_key_exists('roll_no', $request)) {
                    $fields['roll_no'] = $request['roll_no'];
                }

                // Only update photo if a new base64 image was submitted
                if (!empty($request['photo'])) {
                    $fields['photo'] = $this->convert($request['photo']);
                }

                User::where('id', $request['student_id'])->update($fields);

                // Update Parents' information
                $studentParentInfoRepository = new StudentParentInfoRepository();
                $studentParentInfoRepository->update($request, $request['student_id']);

                // Update Student's ID card number
                $promotionRepository = new PromotionRepository();
                $promotionRepository->update($request, $request['student_id']);
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to update Student. '.$e->getMessage());
        }
    }

    public function updateTeacher($request) {
        try {
            DB::transaction(function () use ($request) {
                $fields = [
                    'first_name'    => $request['first_name'],
                    'last_name'     => $request['last_name'],
                    'email'         => $request['email'],
                    'gender'        => $request['gender'],
                    'nationality'   => $request['nationality'],
                    'phone'         => $request['phone'],
                    'address'       => $request['address'],
                    'address2'      => $request['address2'] ?? null,
                    'city'          => $request['city'],
                    'zip'           => $request['zip'],
                ];

                // Only update photo if a new base64 image was submitted
                if (!empty($request['photo'])) {
                    $fields['photo'] = $this->convert($request['photo']);
                }

                User::where('id', $request['teacher_id'])->update($fields);
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to update Teacher. '.$e->getMessage());
        }
    }

    public function getAllStudents($session_id, $class_id, $section_id) {
        if($class_id == 0 || $section_id == 0) {
            $schoolClass = SchoolClass::where('session_id', $session_id)
                                    ->first();
            $section = Section::where('session_id', $session_id)
                                    ->first();
            if($schoolClass == null || $section == null){
                throw new \Exception('There is no class and section');
            } else {
                $class_id = $schoolClass->id;
                $section_id = $section->id;
            }
            
        }
        try {
            $promotionRepository = new PromotionRepository();
            return $promotionRepository->getAll($session_id, $class_id, $section_id);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get all Students. '.$e->getMessage());
        }
    }

    public function getAllStudentsBySession($session_id) {
        $promotionRepository = new PromotionRepository();
        return $promotionRepository->getAllStudentsBySession($session_id);
    }

    public function getAllStudentsBySessionCount($session_id) {
        $promotionRepository = new PromotionRepository();
        return $promotionRepository->getAllStudentsBySessionCount($session_id);
    }

    public function findStudent($id) {
        try {
            return User::with('parent_info', 'academic_info')->where('id', $id)->first();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get Student. '.$e->getMessage());
        }
    }

    public function findTeacher($id) {
        try {
            return User::where('id', $id)
                ->whereNotIn('role', ['student', 'parent'])
                ->first();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get Teacher. '.$e->getMessage());
        }
    }

    public function getAllTeachers() {
        try {
            return User::whereNotIn('role', ['student', 'parent', 'super-admin'])
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get all Teachers. '.$e->getMessage());
        }
    }

    public function changePassword($new_password) {
        try {
            return User::where('id', auth()->user()->id)->update([
                'password'             => Hash::make($new_password),
                'must_change_password' => false,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to change password. '.$e->getMessage());
        }
    }
}