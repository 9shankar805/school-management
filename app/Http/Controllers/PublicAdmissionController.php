<?php

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use Illuminate\Http\Request;

class PublicAdmissionController extends Controller
{
    /**
     * Show public admission application form for a school.
     */
    public function create(Request $request)
    {
        $schoolSlug = $request->query('school');
        $school     = null;

        if ($schoolSlug) {
            $school = School::where('slug', $schoolSlug)->where('status', 'active')->first();
        }

        if (!$school) {
            $school = School::where('status', 'active')->first();
        }

        if (!$school) {
            abort(404, 'School not found.');
        }

        $classes        = SchoolClass::where('school_id', $school->id)->get();
        $sessions       = SchoolSession::where('school_id', $school->id)->latest()->get();
        $currentSession = $sessions->first();

        return view('admissions.public_apply', compact('school', 'classes', 'sessions', 'currentSession'));
    }

    /**
     * Store public admission application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id'              => 'required|exists:schools,id',
            'first_name'             => 'required|string|max:100',
            'last_name'              => 'required|string|max:100',
            'iemis_id'               => 'nullable|string|max:50',
            'national_id'            => 'nullable|string|max:100',
            'email'                  => 'nullable|email|max:255',
            'phone'                  => 'nullable|string|max:30',
            'birthday'               => 'nullable|date',
            'gender'                 => 'nullable|in:Male,Female,Other',
            'nationality'            => 'nullable|string|max:100',
            'religion'               => 'nullable|string|max:100',
            'ethnicity'              => 'nullable|string|max:50',
            'mother_tongue'          => 'nullable|string|max:50',
            'blood_type'             => 'nullable|string|max:10',
            'address'                => 'nullable|string|max:500',
            'session_id'             => 'nullable|exists:school_sessions,id',
            'class_id'               => 'nullable|exists:school_classes,id',
            'guardian_name'          => 'nullable|string|max:255',
            'guardian_phone'         => 'nullable|string|max:30',
            'guardian_email'         => 'nullable|email|max:255',
            'guardian_relation'      => 'nullable|string|max:100',
            'previous_school'        => 'nullable|string|max:255',
            'previous_class'         => 'nullable|string|max:100',
            'student_photo'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'birth_certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ]);

        $school = School::findOrFail($validated['school_id']);

        $photoPath = null;
        if ($request->hasFile('student_photo')) {
            $photoPath = \App\Services\ImageWebpService::storeAsWebp($request->file('student_photo'), 'admissions/photos', 'public');
        }

        $docPath = null;
        if ($request->hasFile('birth_certificate_file')) {
            $docPath = \App\Services\ImageWebpService::storeAsWebp($request->file('birth_certificate_file'), 'admissions/documents', 'public');
        }

        $application = AdmissionApplication::create([
            'school_id'             => $school->id,
            'application_number'    => AdmissionApplication::generateNumber(),
            'iemis_id'              => $validated['iemis_id'] ?? null,
            'national_id'           => $validated['national_id'] ?? null,
            'first_name'            => $validated['first_name'],
            'last_name'             => $validated['last_name'],
            'photo'                 => $photoPath,
            'email'                 => $validated['email'] ?? null,
            'phone'                 => $validated['phone'] ?? null,
            'birthday'              => $validated['birthday'] ?? null,
            'gender'                => $validated['gender'] ?? null,
            'nationality'           => $validated['nationality'] ?? null,
            'religion'              => $validated['religion'] ?? null,
            'ethnicity'             => $validated['ethnicity'] ?? null,
            'mother_tongue'         => $validated['mother_tongue'] ?? null,
            'blood_type'            => $validated['blood_type'] ?? null,
            'address'               => $validated['address'] ?? null,
            'session_id'            => $validated['session_id'] ?? null,
            'class_id'              => $validated['class_id'] ?? null,
            'guardian_name'         => $validated['guardian_name'] ?? null,
            'guardian_phone'        => $validated['guardian_phone'] ?? null,
            'guardian_email'        => $validated['guardian_email'] ?? null,
            'guardian_relation'     => $validated['guardian_relation'] ?? null,
            'previous_school'       => $validated['previous_school'] ?? null,
            'previous_class'        => $validated['previous_class'] ?? null,
            'birth_certificate_doc' => $docPath,
            'status'                => 'pending',
        ]);

        return view('admissions.public_success', compact('school', 'application'));
    }
}
