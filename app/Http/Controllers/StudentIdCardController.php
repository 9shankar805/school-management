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

    /**
     * Generate a single student ID card PDF.
     */
    public function generate(int $studentId)
    {
        $student   = User::with('academic_info')->findOrFail($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        $promotion = Promotion::with('schoolClass', 'section')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->first();

        // QR code payload: student ID + name
        $qrPayload = "STUDENT:{$student->id}|{$student->full_name}|{$promotion?->id_card_number}";

        // Generate QR code as base64 SVG-like data-url using a simple URL-based QR
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=' . urlencode($qrPayload);

        $pdf = Pdf::loadView('students.id-card', compact('student', 'promotion', 'qrUrl'))
            ->setPaper([0, 0, 242, 153], 'landscape') // ~85.6mm × 54mm card (CR80)
            ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);

        return $pdf->stream("id-card-{$student->id}.pdf");
    }

    /**
     * Bulk ID cards: all students in a class/section.
     */
    public function bulkGenerate(Request $request)
    {
        $this->authorize('view students');

        $request->validate([
            'class_id'   => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $sessionId  = $this->getSchoolCurrentSession();

        $promotions = Promotion::with('student.academic_info', 'schoolClass', 'section')
            ->where('session_id', $sessionId)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->get();

        $students = $promotions->map(function ($p) {
            $payload = "STUDENT:{$p->student->id}|{$p->student->full_name}|{$p->id_card_number}";
            return [
                'student'   => $p->student,
                'promotion' => $p,
                'qrUrl'     => 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=' . urlencode($payload),
            ];
        });

        $pdf = Pdf::loadView('students.id-card-bulk', compact('students'))
            ->setPaper('A4')
            ->setOptions(['dpi' => 120, 'defaultFont' => 'sans-serif']);

        return $pdf->stream('id-cards-bulk.pdf');
    }
}
