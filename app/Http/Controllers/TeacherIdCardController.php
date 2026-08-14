<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SchoolSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TeacherIdCardController extends Controller
{
    /**
     * Download or preview a single double-sided Teacher ID Card (No QR code).
     */
    public function single($teacherId)
    {
        $teacher = User::where('id', $teacherId)->firstOrFail();

        $schoolSession = SchoolSession::first();
        $school        = config('tenant.school');

        // Teacher photo base64
        $photoSrc = '';
        if ($teacher->photo) {
            $p = storage_path('app/public/' . ltrim($teacher->photo, '/'));
            if (file_exists($p)) {
                $m = mime_content_type($p) ?: 'image/png';
                $photoSrc = 'data:' . $m . ';base64,' . base64_encode(file_get_contents($p));
            }
        }
        if (!$photoSrc) {
            $photoSrc = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        }

        $pdf = Pdf::loadView('teachers.id-card', compact('teacher', 'photoSrc', 'schoolSession', 'school'))
            ->setPaper([0, 0, 153, 243])
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->stream("Teacher_ID_Card_{$teacher->id}.pdf");
    }

    /**
     * Download or preview bulk printable PDF grid for multiple teachers (No QR code).
     */
    public function bulk(Request $request)
    {
        $teacherIds = $request->input('ids', []);
        
        if (!empty($teacherIds)) {
            $teachers = User::whereIn('id', (array)$teacherIds)->where('role', 'teacher')->get();
        } else {
            $teachers = User::where('role', 'teacher')->take(50)->get();
        }

        $schoolSession = SchoolSession::first();
        $school        = config('tenant.school');

        $teacherCards = [];
        foreach ($teachers as $t) {
            $photoSrc = '';
            if ($t->photo) {
                $p = storage_path('app/public/' . ltrim($t->photo, '/'));
                if (file_exists($p)) {
                    $m = mime_content_type($p) ?: 'image/png';
                    $photoSrc = 'data:' . $m . ';base64,' . base64_encode(file_get_contents($p));
                }
            }
            if (!$photoSrc) {
                $photoSrc = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
            }

            $teacherCards[] = [
                'teacher'  => $t,
                'photoSrc' => $photoSrc,
            ];
        }

        $pdf = Pdf::loadView('teachers.id-card-bulk', [
            'teachers' => $teacherCards,
            'schoolSession' => $schoolSession,
            'school' => $school,
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => true,
        ]);

        return $pdf->stream("Bulk_Teacher_ID_Cards.pdf");
    }
}
