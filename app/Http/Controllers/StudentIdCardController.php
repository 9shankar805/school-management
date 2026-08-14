<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Promotion;
use App\Traits\SchoolSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class StudentIdCardController extends Controller
{
    use SchoolSession;

    public function __construct()
    {
        $this->middleware(['can:view students']);
    }

    private function getBase64Image($url, $isLocal = false)
    {
        try {
            if ($isLocal) {
                if (file_exists($url)) {
                    $type = pathinfo($url, PATHINFO_EXTENSION);
                    $data = file_get_contents($url);
                    return 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            } else {
                // External requests (like api.qrserver.com) are hanging on DNS.
                // Return a transparent placeholder for external URLs to prevent hanging.
                return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
            }
        } catch (\Exception $e) {}
        
        // Return a 1x1 transparent png as fallback
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
    }

    /**
     * Generate a single student ID card PDF.
     */
    public function generate(int $studentId)
    {
        $student   = User::with(['academic_info', 'parent_info', 'parents'])->findOrFail($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        $schoolSession = \App\Models\SchoolSession::find($sessionId);

        $promotion = Promotion::with('schoolClass', 'section')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->first();

        // QR code payload: student ID + name
        $qrPayload = "STUDENT:{$student->id}|{$student->full_name}|{$promotion?->id_card_number}";
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=' . urlencode($qrPayload);
        
        $qrSrc = $this->getBase64Image($qrUrl, false);
        $photoSrc = $this->getBase64Image(public_path('imgs/profile.png'), true);
        if ($student->photo) {
            $photoSrc = $this->getBase64Image(public_path('storage/'.$student->photo), true);
        }

        $pdf = Pdf::loadView('students.id-card', compact('student', 'promotion', 'qrSrc', 'photoSrc', 'schoolSession'))
            ->setPaper([0, 0, 153, 243]) // Vertical CR80 portrait card (54mm x 85.6mm)
            ->setOptions([
                'dpi' => 150, 
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'chroot' => public_path()
            ]);

        return $pdf->stream("id-card-{$student->id}.pdf");
    }

    /**
     * Bulk ID cards: all students in a class/section.
     */
    public function bulkGenerate(Request $request)
    {
        $this->authorize('view students');

        $sessionId     = $this->getSchoolCurrentSession();
        $schoolSession = \App\Models\SchoolSession::find($sessionId);

        $query = Promotion::with('student.academic_info', 'student.parent_info', 'student.parents', 'schoolClass', 'section')
            ->where('session_id', $sessionId);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $promotions = $query->take(50)->get();

        $students = $promotions->map(function ($p) {
            $payload = "STUDENT:{$p->student->id}|{$p->student->full_name}|{$p->id_card_number}";
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=' . urlencode($payload);
            
            $qrSrc = $this->getBase64Image($qrUrl, false);
            $photoSrc = $this->getBase64Image(public_path('imgs/profile.png'), true);
            if ($p->student && $p->student->photo) {
                $photoSrc = $this->getBase64Image(public_path('storage/'.$p->student->photo), true);
            }
            
            return [
                'student'   => $p->student,
                'promotion' => $p,
                'qrSrc'     => $qrSrc,
                'photoSrc'  => $photoSrc
            ];
        });

        $pdf = Pdf::loadView('students.id-card-bulk', compact('students', 'schoolSession'))
            ->setPaper('A4')
            ->setOptions([
                'dpi' => 120, 
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'chroot' => public_path()
            ]);

        return $pdf->stream('id-cards-bulk.pdf');
    }
}
