<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\StudentStatus;
use App\Models\StudentDocument;
use App\Models\Achievement;
use App\Models\MedicalRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * GET /student-app/profile
     * Full profile for the authenticated student.
     */
    public function show(Request $request): JsonResponse
    {
        $student = $request->user()->load([
            'academic_info',
            'currentStatus.schoolClass',
            'medicalRecord',
            'emergencyContacts',
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'           => $student->id,
                'first_name'   => $student->first_name,
                'last_name'    => $student->last_name,
                'full_name'    => $student->full_name,
                'email'        => $student->email,
                'phone'        => $student->phone,
                'avatar'       => $student->avatar,
                'gender'       => $student->gender,
                'birthday'     => $student->birthday?->format('Y-m-d'),
                'blood_type'   => $student->blood_type,
                'religion'     => $student->religion,
                'nationality'  => $student->nationality,
                'address'      => $student->address,
                'admission_no' => $student->admission_no,
                'roll_no'      => $student->roll_no,
                'admission_date' => $student->admission_date?->format('Y-m-d'),
                'class'        => $student->currentStatus?->schoolClass?->class_name,
                'section'      => null,   // section tracked via Promotion, not StudentStatus
                'academic_info'     => $student->academic_info,
                'medical_record'    => $student->medicalRecord,
                'emergency_contacts'=> $student->emergencyContacts,
            ],
        ]);
    }

    /**
     * PUT /student-app/profile/avatar
     * Upload a new profile photo.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $student = $request->user();

        // Delete old photo
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $path = \App\Services\ImageWebpService::storeAsWebp($request->file('photo'), "student_photos/{$student->school_id}", 'public');
        $student->update(['photo' => $path]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Avatar updated successfully.',
            'avatar'  => $student->avatar,
        ]);
    }

    /**
     * GET /student-app/profile/documents
     * Student's uploaded documents.
     */
    public function documents(Request $request): JsonResponse
    {
        $documents = StudentDocument::where('student_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($d) => [
                'id'          => $d->id,
                'title'       => $d->title,
                'type'        => $d->document_type,
                'file_url'    => $d->file_path ? Storage::url($d->file_path) : null,
                'uploaded_at' => $d->created_at,
            ]);

        return response()->json([
            'status' => 'success',
            'data'   => $documents,
        ]);
    }

    /**
     * GET /student-app/profile/achievements
     * Achievements and awards for the student.
     */
    public function achievements(Request $request): JsonResponse
    {
        $achievements = Achievement::where('student_id', $request->user()->id)
            ->orderByDesc('awarded_date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $achievements,
        ]);
    }
}
