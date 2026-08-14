<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherVerificationController extends Controller
{
    /**
     * Helper to lookup teacher safely without MySQL string-to-int type coercion bugs.
     */
    private function findTeacherByToken(string $token): ?User
    {
        $token = trim($token);
        if (empty($token)) return null;

        // 1. Search by exact qr_token string match
        $teacher = User::where('qr_token', $token)->where('role', 'teacher')->first();
        if ($teacher) return $teacher;

        // 2. Search by employee_id string match
        $teacher = User::where('employee_id', $token)->where('role', 'teacher')->first();
        if ($teacher) return $teacher;

        // 3. Search by numeric User ID ONLY if token is purely numeric
        if (ctype_digit($token) || is_int($token)) {
            $teacher = User::where('id', (int)$token)->where('role', 'teacher')->first();
            if ($teacher) return $teacher;
        }

        // 4. Fallback search by qr_token regardless of role
        return User::where('qr_token', $token)->first();
    }

    /**
     * Show public digital teacher verification page when QR code is scanned.
     */
    public function show(string $token)
    {
        $teacher = $this->findTeacherByToken($token);

        if (!$teacher) {
            abort(404, 'Teacher / Faculty Verification Record Not Found');
        }

        $schoolSession = SchoolSession::first();
        $school        = config('tenant.school');

        return view('teachers.verify', compact('teacher', 'schoolSession', 'school', 'token'));
    }

    /**
     * One-click QR Quick Login into teacher portal.
     */
    public function qrLogin(string $token)
    {
        $teacher = $this->findTeacherByToken($token);

        if (!$teacher) {
            return redirect()->route('login')->with('error', 'Invalid or expired Teacher QR login token.');
        }

        Auth::login($teacher);

        return redirect()->route('home')->with('status', 'Welcome back, ' . $teacher->full_name . '! Logged in via Teacher QR code.');
    }
}
