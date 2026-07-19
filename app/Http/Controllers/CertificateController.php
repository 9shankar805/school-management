<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use App\Models\User;
use App\Models\Promotion;
use App\Traits\SchoolSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    use SchoolSession;

    public function __construct()
    {
        $this->middleware(['auth', 'can:view students']);
    }

    // ── Template CRUD ─────────────────────────────────────────────────────
    public function index()
    {
        $this->authorize('create students');
        $templates = CertificateTemplate::latest()->get();
        return view('students.certificates.index', compact('templates'));
    }

    public function create()
    {
        $this->authorize('create students');
        return view('students.certificates.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create students');

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:' . implode(',', array_keys(CertificateTemplate::TYPES)),
            'header_text'     => 'nullable|string|max:500',
            'body_text'       => 'required|string',
            'footer_text'     => 'nullable|string|max:500',
            'signature_name'  => 'nullable|string|max:255',
            'signature_title' => 'nullable|string|max:255',
        ]);

        CertificateTemplate::create(array_merge($data, ['is_active' => true]));
        return redirect()->route('certificates.index')->with('status', 'Template created.');
    }

    public function edit(int $id)
    {
        $this->authorize('create students');
        $template = CertificateTemplate::findOrFail($id);
        return view('students.certificates.edit', compact('template'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create students');
        $template = CertificateTemplate::findOrFail($id);

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:' . implode(',', array_keys(CertificateTemplate::TYPES)),
            'header_text'     => 'nullable|string|max:500',
            'body_text'       => 'required|string',
            'footer_text'     => 'nullable|string|max:500',
            'signature_name'  => 'nullable|string|max:255',
            'signature_title' => 'nullable|string|max:255',
            'is_active'       => 'boolean',
        ]);

        $template->update($data);
        return redirect()->route('certificates.index')->with('status', 'Template updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create students');
        CertificateTemplate::findOrFail($id)->delete();
        return back()->with('status', 'Template deleted.');
    }

    // ── Generate certificate PDF for a student ────────────────────────────
    public function generate(Request $request, int $studentId)
    {
        $request->validate([
            'template_id' => 'required|exists:certificate_templates,id',
            'extra_notes' => 'nullable|string|max:500',
        ]);

        $student  = User::with('promotions.schoolClass', 'promotions.section')->findOrFail($studentId);
        $template = CertificateTemplate::findOrFail($request->template_id);

        $bodyText = $template->render($student, [
            '{{extra_notes}}' => $request->extra_notes ?? '',
            '{{issued_by}}'   => auth()->user()->full_name,
        ]);

        $pdf = Pdf::loadView('students.certificates.pdf', compact('student', 'template', 'bodyText'))
            ->setPaper('A4', 'landscape')
            ->setOptions([
                'dpi'           => 150,
                'defaultFont'   => 'sans-serif',
                'isRemoteEnabled' => true,
            ]);

        return $pdf->stream("certificate-{$student->id}.pdf");
    }

    // ── Bulk: generate one PDF with all students in a class ───────────────
    public function bulkGenerate(Request $request)
    {
        $this->authorize('create students');

        $request->validate([
            'template_id' => 'required|exists:certificate_templates,id',
            'class_id'    => 'required|exists:school_classes,id',
        ]);

        $sessionId = $this->getSchoolCurrentSession();
        $template  = CertificateTemplate::findOrFail($request->template_id);

        $promotions = Promotion::with('student', 'schoolClass', 'section')
            ->where('session_id', $sessionId)
            ->where('class_id', $request->class_id)
            ->get();

        $items = $promotions->map(fn($p) => [
            'student'   => $p->student,
            'bodyText'  => $template->render($p->student),
        ])->filter(fn($i) => $i['student'] !== null)->values();

        $pdf = Pdf::loadView('students.certificates.pdf-bulk', compact('items', 'template'))
            ->setPaper('A4', 'landscape')
            ->setOptions(['dpi' => 120, 'defaultFont' => 'sans-serif']);

        return $pdf->stream('certificates-bulk.pdf');
    }
}
