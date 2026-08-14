<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Promotion;
use App\Models\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentVerificationController extends Controller
{
    /**
     * Helper to lookup student safely without MySQL string-to-int type coercion bugs.
     */
    private function findStudentByToken(string $token): ?User
    {
        $token = trim($token);
        if (empty($token)) return null;

        // 1. Search by exact qr_token string match
        $student = User::where('qr_token', $token)->first();
        if ($student) return $student;

        // 2. Search by ID card number (e.g. GGS-2026-001 or ID-GGS-2026-015)
        $promo = Promotion::where('id_card_number', $token)->first();
        if ($promo && $promo->student_id) {
            $student = User::find($promo->student_id);
            if ($student) return $student;
        }

        // 3. Search by numeric User ID ONLY if token is purely numeric (e.g. 431)
        if (ctype_digit($token) || is_int($token)) {
            $student = User::find((int) $token);
            if ($student) return $student;
        }

        return null;
    }

    /**
     * Show public digital student verification page when QR code is scanned.
     */
    public function show(string $token)
    {
        $student = $this->findStudentByToken($token);

        if (!$student) {
            // Check if token belongs to a teacher
            $teacher = User::where('qr_token', $token)->where('role', 'teacher')->first();
            if ($teacher) {
                return redirect()->route('teacher.verify', ['token' => $token]);
            }
            abort(404, 'Student Verification Record Not Found');
        }

        $student->load(['academic_info', 'parent_info', 'parents']);

        $promotion = Promotion::with('schoolClass', 'section')
            ->where('student_id', $student->id)
            ->latest('id')
            ->first();

        $schoolSession = SchoolSession::find($promotion?->session_id) ?? SchoolSession::first();

        // Resolve Father & Mother Name
        $fatherName = null;
        $motherName = null;

        if ($student->parents && $student->parents->isNotEmpty()) {
            foreach ($student->parents as $p) {
                $rel = strtolower($p->pivot->relationship ?? $p->relationship ?? '');
                $gen = strtolower($p->gender ?? '');

                if (!$fatherName && ($rel === 'father' || $gen === 'male')) {
                    $fatherName = $p->full_name;
                }
                if (!$motherName && ($rel === 'mother' || $gen === 'female')) {
                    $motherName = $p->full_name;
                }
            }
        }

        if (!$fatherName) $fatherName = $student->parent_info?->father_name;
        if (!$motherName) $motherName = $student->parent_info?->mother_name;

        if (!$fatherName || !$motherName) {
            $admApp = DB::table('admission_applications')
                ->where('email', $student->email)
                ->orWhere('first_name', $student->first_name)
                ->first();
            if ($admApp) {
                $fatherName = $fatherName ?: ($admApp->father_name ?? null);
                $motherName = $motherName ?: ($admApp->mother_name ?? null);
            }
        }

        $school = config('tenant.school');

        return view('students.verify', compact('student', 'promotion', 'schoolSession', 'school', 'fatherName', 'motherName', 'token'));
    }

    /**
     * One-click QR Quick Login into student portal.
     */
    public function qrLogin(string $token)
    {
        $student = $this->findStudentByToken($token);

        if (!$student) {
            return redirect()->route('login')->with('error', 'Invalid or expired QR login token.');
        }

        Auth::login($student);

        return redirect()->route('home')->with('status', 'Welcome back, ' . $student->full_name . '! Logged in via QR code.');
    }

    /**
     * Universal API / AJAX endpoint for scanning QR code on main login page camera.
     * Supports both Student and Teacher ID cards.
     */
    public function loginFromScan(Request $request)
    {
        $payload = trim($request->input('qr_code', ''));
        if (empty($payload)) {
            return response()->json(['success' => false, 'message' => 'No QR code provided.'], 400);
        }

        // Parse token out of full URL or payload
        $token = $payload;
        if (preg_match('#/verify/(?:student|teacher)/([A-Za-z0-9_-]+)#', $payload, $matches)) {
            $token = $matches[1];
        } elseif (preg_match('#(?:STUDENT|TEACHER):(\d+)#', $payload, $matches)) {
            $token = $matches[1];
        } else {
            $parsedPath = parse_url($payload, PHP_URL_PATH);
            if ($parsedPath) {
                $token = basename($parsedPath);
            }
        }

        $user = User::where('qr_token', $token)->first();

        if (!$user) {
            $promo = Promotion::where('id_card_number', $token)->first();
            if ($promo && $promo->student_id) {
                $user = User::find($promo->student_id);
            }
        }

        if (!$user) {
            $user = User::where('employee_id', $token)->first();
        }

        if (!$user && (ctype_digit($token) || is_int($token))) {
            $user = User::find((int)$token);
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'ID Card record not found for this QR code.'], 404);
        }

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful! Redirecting...',
            'redirect' => route('home'),
            'student' => [
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }
}
