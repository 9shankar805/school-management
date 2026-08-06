<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use App\Models\IssuedCertificate;
use App\Models\User;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\SchoolSession;
use App\Services\StorageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    // ── Helper: get current session ID ────────────────────────────────────
    private function currentSessionId(): int
    {
        if (session()->has('browse_session_id')) {
            return (int) session('browse_session_id');
        }
        return (int) SchoolSession::latest('id')->value('id');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // TEMPLATE CRUD
    // ═══════════════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $this->authorize('view students');

        $templates = CertificateTemplate::latest()->get();

        // Issued certificate stats
        $stats = [
            'templates'     => $templates->count(),
            'total_issued'  => IssuedCertificate::count(),
            'this_month'    => IssuedCertificate::whereMonth('issued_date', now()->month)
                                ->whereYear('issued_date', now()->year)->count(),
            'revoked'       => IssuedCertificate::where('status', 'revoked')->count(),
        ];

        // Recent issued
        $recent = IssuedCertificate::with('student', 'template')
            ->latest()->limit(10)->get();

        return view('certificates.index', compact('templates', 'stats', 'recent'));
    }

    public function create()
    {
        $this->authorize('create students');
        $typeGroups = CertificateTemplate::TYPE_GROUPS;
        $types      = CertificateTemplate::TYPES;
        return view('certificates.create', compact('typeGroups', 'types'));
    }

    public function store(Request $request, StorageService $storage)
    {
        $this->authorize('create students');

        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'type'               => 'required|string|in:' . implode(',', array_keys(CertificateTemplate::TYPES)),
            'language'           => 'required|in:en,ne',
            'header_text'        => 'nullable|string|max:500',
            'body_text'          => 'required|string',
            'body_text_ne'       => 'nullable|string',
            'footer_text'        => 'nullable|string|max:500',
            'signature_name'     => 'nullable|string|max:255',
            'signature_title'    => 'nullable|string|max:255',
            'paper_size'         => 'required|in:A4,Letter',
            'orientation'        => 'required|in:portrait,landscape',
            'border_style'       => 'required|in:classic,modern,minimal,ornate',
            'primary_color'      => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'secondary_color'    => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'show_student_photo' => 'boolean',
            'show_qr_code'       => 'boolean',
            'show_serial_number' => 'boolean',
            'verification_url'   => 'nullable|url|max:500',
            'logo'               => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            'signature_image'    => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'background_image'   => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
        ]);

        $data['show_student_photo'] = $request->boolean('show_student_photo');
        $data['show_qr_code']       = $request->boolean('show_qr_code');
        $data['show_serial_number'] = $request->boolean('show_serial_number');
        $data['is_active']          = true;

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $storage->store($request->file('logo'), 'certificates/logos', 'public');
        }
        if ($request->hasFile('signature_image')) {
            $data['signature_image_path'] = $storage->store($request->file('signature_image'), 'certificates/signatures', 'public');
        }
        if ($request->hasFile('background_image')) {
            $data['background_image_path'] = $storage->store($request->file('background_image'), 'certificates/backgrounds', 'public');
        }

        unset($data['logo'], $data['signature_image'], $data['background_image']);
        CertificateTemplate::create($data);

        return redirect()->route('certificates.index')->with('status', 'Certificate template created successfully.');
    }

    public function edit(int $id)
    {
        $this->authorize('create students');
        $template   = CertificateTemplate::findOrFail($id);
        $typeGroups = CertificateTemplate::TYPE_GROUPS;
        $types      = CertificateTemplate::TYPES;
        return view('certificates.edit', compact('template', 'typeGroups', 'types'));
    }

    public function update(Request $request, int $id, StorageService $storage)
    {
        $this->authorize('create students');
        $template = CertificateTemplate::findOrFail($id);

        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'type'               => 'required|string|in:' . implode(',', array_keys(CertificateTemplate::TYPES)),
            'language'           => 'required|in:en,ne',
            'header_text'        => 'nullable|string|max:500',
            'body_text'          => 'required|string',
            'body_text_ne'       => 'nullable|string',
            'footer_text'        => 'nullable|string|max:500',
            'signature_name'     => 'nullable|string|max:255',
            'signature_title'    => 'nullable|string|max:255',
            'paper_size'         => 'required|in:A4,Letter',
            'orientation'        => 'required|in:portrait,landscape',
            'border_style'       => 'required|in:classic,modern,minimal,ornate',
            'primary_color'      => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'secondary_color'    => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'show_student_photo' => 'boolean',
            'show_qr_code'       => 'boolean',
            'show_serial_number' => 'boolean',
            'verification_url'   => 'nullable|url|max:500',
            'is_active'          => 'boolean',
            'logo'               => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            'signature_image'    => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'background_image'   => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
        ]);

        $data['show_student_photo'] = $request->boolean('show_student_photo');
        $data['show_qr_code']       = $request->boolean('show_qr_code');
        $data['show_serial_number'] = $request->boolean('show_serial_number');
        $data['is_active']          = $request->boolean('is_active', true);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($template->logo_path) {
                $storage->delete($template->logo_path, 'public');
            }
            $data['logo_path'] = $storage->store($request->file('logo'), 'certificates/logos', 'public');
        }
        if ($request->hasFile('signature_image')) {
            if ($template->signature_image_path) {
                $storage->delete($template->signature_image_path, 'public');
            }
            $data['signature_image_path'] = $storage->store($request->file('signature_image'), 'certificates/signatures', 'public');
        }
        if ($request->hasFile('background_image')) {
            if ($template->background_image_path) {
                $storage->delete($template->background_image_path, 'public');
            }
            $data['background_image_path'] = $storage->store($request->file('background_image'), 'certificates/backgrounds', 'public');
        }

        // Handle logo removal
        if ($request->boolean('remove_logo') && $template->logo_path) {
            $storage->delete($template->logo_path, 'public');
            $data['logo_path'] = null;
        }
        if ($request->boolean('remove_signature_image') && $template->signature_image_path) {
            $storage->delete($template->signature_image_path, 'public');
            $data['signature_image_path'] = null;
        }
        if ($request->boolean('remove_background_image') && $template->background_image_path) {
            $storage->delete($template->background_image_path, 'public');
            $data['background_image_path'] = null;
        }

        unset($data['logo'], $data['signature_image'], $data['background_image']);
        $template->update($data);

        return redirect()->route('certificates.index')->with('status', 'Template updated successfully.');
    }

    public function destroy(int $id, StorageService $storage)
    {
        $this->authorize('create students');
        $template = CertificateTemplate::findOrFail($id);

        if ($template->logo_path) {
            $storage->delete($template->logo_path, 'public');
        }
        if ($template->signature_image_path) {
            $storage->delete($template->signature_image_path, 'public');
        }
        if ($template->background_image_path) {
            $storage->delete($template->background_image_path, 'public');
        }

        $template->delete();
        return back()->with('status', 'Template deleted.');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // VISUAL DRAG & DROP BUILDER
    // ═══════════════════════════════════════════════════════════════════════

    public function builder(int $id)
    {
        $this->authorize('create students');
        $template = CertificateTemplate::findOrFail($id);

        $layout       = $template->builder_layout ?? [];
        $editable     = $template->editable_fields ?? [];
        $dynamicFields = CertificateTemplate::DYNAMIC_FIELDS();
        $bgUrl        = $template->backgroundImageUrl();

        return view('certificates.builder', compact(
            'template', 'layout', 'editable', 'dynamicFields', 'bgUrl'
        ));
    }

    public function builderSave(Request $request, int $id)
    {
        $this->authorize('create students');
        $template = CertificateTemplate::findOrFail($id);

        $data = $request->validate([
            'builder_layout'   => 'required|array',
            'editable_fields'  => 'nullable|array',
            'paper_size'       => 'nullable|in:A4,Letter',
            'orientation'      => 'nullable|in:portrait,landscape',
        ]);

        // Sanitize each element to avoid garbage in JSON
        $cleanLayout = [];
        foreach ($data['builder_layout'] as $el) {
            $type = $el['type'] ?? 'text';
            if (!in_array($type, ['text','field','image','qr','line','signature','student_photo'])) continue;
            $clean = [
                'id'         => $el['id'] ?? ('el_' . uniqid()),
                'type'       => $type,
                'x'          => (float) ($el['x'] ?? 0),
                'y'          => (float) ($el['y'] ?? 0),
                'w'          => isset($el['w']) ? (float) $el['w'] : null,
                'h'          => isset($el['h']) ? (float) $el['h'] : null,
                'fontSize'   => isset($el['fontSize'])  ? (int) $el['fontSize'] : 12,
                'fontWeight' => in_array($el['fontWeight'] ?? 'normal', ['normal','bold']) ? $el['fontWeight'] : 'normal',
                'fontStyle'  => in_array($el['fontStyle']  ?? 'normal', ['normal','italic']) ? $el['fontStyle'] : 'normal',
                'color'      => (string) ($el['color'] ?? '#1e293b'),
                'align'      => in_array($el['align'] ?? 'left', ['left','center','right']) ? $el['align'] : 'left',
                'content'    => (string) ($el['content'] ?? ''),
                'field'      => (string) ($el['field'] ?? ''),
                'zIndex'     => (int) ($el['zIndex'] ?? 1),
                'border'     => (int) ($el['border'] ?? 0),
                'borderColor'=> (string) ($el['borderColor'] ?? '#000000'),
            ];
            $cleanLayout[] = $clean;
        }

        $template->builder_mode    = true;
        $template->builder_layout  = $cleanLayout;
        $template->editable_fields = $data['editable_fields'] ?? [];
        if (!empty($data['paper_size']))  $template->paper_size  = $data['paper_size'];
        if (!empty($data['orientation'])) $template->orientation = $data['orientation'];
        $template->save();

        return response()->json(['ok' => true, 'count' => count($cleanLayout)]);
    }

    public function builderUploadBackground(Request $request, int $id, StorageService $storage)
    {
        $this->authorize('create students');
        $template = CertificateTemplate::findOrFail($id);

        $request->validate([
            'background' => 'required|image|mimes:png,jpg,jpeg|max:5120',
        ]);

        if ($template->background_image_path) {
            $storage->delete($template->background_image_path, 'public');
        }
        $path = $storage->store($request->file('background'), 'certificates/backgrounds', 'public');
        $template->background_image_path = $path;
        $template->save();

        return response()->json(['ok' => true, 'url' => $template->backgroundImageUrl()]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PREVIEW — render template with dummy data (no student required)
    // ═══════════════════════════════════════════════════════════════════════

    public function preview(int $id)
    {
        $this->authorize('view students');

        $template = CertificateTemplate::findOrFail($id);

        // Build a dummy student object so the PDF view has all required variables
        $student = (object) [
            'id'           => 0,
            'first_name'   => 'Ram',
            'last_name'    => 'Sharma',
            'full_name'    => 'Ram Sharma',
            'gender'       => 'Male',
            'birthday'     => \Carbon\Carbon::parse('2008-05-15'),
            'nationality'  => 'Nepali',
            'religion'     => 'Hindu',
            'blood_type'   => 'B+',
            'address'      => 'Kathmandu, Nepal',
            'city'         => 'Kathmandu',
            'phone'        => '9800000000',
            'photo'        => null,
            'academic_info'=> (object) ['admission_no' => 'ADM-2024-001', 'board_reg_no' => 'BOA-0001', 'date_of_birth' => '2008-05-15'],
            'parent_info'  => (object) ['father_name' => 'Hari Sharma', 'mother_name' => 'Sita Sharma'],
            'promotions'   => collect([(object) [
                'id'          => 0,
                'id_card_number' => 'ROLL-001',
                'roll_no'     => '01',
                'schoolClass' => (object) ['class_name' => 'Class 10'],
                'section'     => (object) ['section_name' => 'A'],
                'session'     => (object) ['name' => '2024-25', 'session_name' => '2024-25'],
            ]]),
        ];

        // Build a dummy issued record
        $issued = new class {
            public int    $id                 = 0;
            public string $certificate_number = '';
            public string $status             = 'issued';
            public string $language           = 'en';
            public string $verification_token = 'preview-token';
            public ?\Carbon\Carbon $issued_date  = null;
            public ?\Carbon\Carbon $valid_until  = null;
            public ?string $purpose           = 'Preview';
            public ?string $extra_notes       = 'This is a preview certificate.';
            public          $issuer           = null;
            public ?array   $field_overrides  = null;

            public function qrCodeUrl(): string {
                return 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=PREVIEW';
            }
            public function verificationUrl(): string {
                return url('/');
            }
        };
        $issued->certificate_number = 'CERT-PRV-' . now()->year . '-PREVIEW';
        $issued->issued_date        = \Carbon\Carbon::today();
        $issued->language           = $template->language ?? 'en';

        $bodyText = str_replace(
            [
                '{{student_name}}', '{{first_name}}', '{{last_name}}',
                '{{class}}', '{{section}}', '{{roll_no}}', '{{admission_no}}',
                '{{board_reg_no}}', '{{academic_year}}', '{{birthday}}', '{{age}}',
                '{{gender}}', '{{nationality}}', '{{religion}}', '{{blood_type}}',
                '{{father_name}}', '{{mother_name}}', '{{address}}', '{{phone}}',
                '{{date}}', '{{school_name}}', '{{issued_by}}',
                '{{extra_notes}}', '{{purpose}}',
            ],
            [
                'Ram Sharma', 'Ram', 'Sharma',
                'Class 10', 'A', 'ROLL-001', 'ADM-2024-001',
                'BOA-0001', '2024-25', 'May 15, 2008', '16',
                'Male', 'Nepali', 'Hindu', 'B+',
                'Hari Sharma', 'Sita Sharma', 'Kathmandu, Nepal', '9800000000',
                now()->format('F j, Y'), config('app.name'), auth()->user()?->full_name ?? 'Administrator',
                'This is a preview certificate.', 'Preview',
            ],
            ($template->language === 'ne' && $template->body_text_ne)
                ? $template->body_text_ne
                : $template->body_text
        );

        $sizes   = ['A4' => [0, 0, 595.28, 841.89], 'Letter' => [0, 0, 612, 792]];
        $paper   = $sizes[$template->paper_size] ?? $sizes['A4'];

        $pdf = Pdf::loadView('certificates.pdf', compact('student', 'template', 'issued', 'bodyText'))
            ->setPaper($paper, $template->orientation ?? 'landscape')
            ->setOptions([
                'dpi'                  => 150,
                'defaultFont'          => 'DejaVu Sans',
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'tempDir'              => storage_path('app/temp'),
                // Zero body margin so our position:fixed chrome and
                // content padding are the only spacing in play
                'margin_top'    => 0,
                'margin_right'  => 0,
                'margin_bottom' => 0,
                'margin_left'   => 0,
            ]);

        return $pdf->stream('preview-' . \Illuminate\Support\Str::slug($template->name) . '.pdf');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ISSUE — single student
    // ═══════════════════════════════════════════════════════════════════════

    /** Show the issue form for a specific student */
    public function issueForm(int $studentId)
    {
        $this->authorize('create students');

        $student   = User::with('promotions.schoolClass', 'promotions.section', 'parent_info', 'academic_info')
                         ->findOrFail($studentId);
        $templates = CertificateTemplate::where('is_active', true)->latest()->get();
        $issued    = IssuedCertificate::with('template')
                         ->where('student_id', $studentId)
                         ->latest()->get();

        return view('certificates.issue', compact('student', 'templates', 'issued'));
    }

    /** Store a new issued certificate record and return the PDF */
    public function issue(Request $request, int $studentId)
    {
        $this->authorize('create students');

        $data = $request->validate([
            'template_id' => 'required|exists:certificate_templates,id',
            'issued_date' => 'required|date',
            'valid_until' => 'nullable|date|after:issued_date',
            'purpose'     => 'nullable|string|max:255',
            'extra_notes' => 'nullable|string|max:1000',
            'language'    => 'required|in:en,ne',
            'paper_size'  => 'required|in:A4,Letter',
            'field_overrides' => 'nullable|array',
        ]);

        $student  = User::findOrFail($studentId);
        $template = CertificateTemplate::findOrFail($data['template_id']);

        $fieldOverrides = [];
        if (!empty($data['field_overrides']) && is_array($data['field_overrides'])) {
            foreach ($data['field_overrides'] as $k => $v) {
                if (is_string($k) && !is_null($v)) $fieldOverrides[$k] = (string) $v;
            }
        }

        $issued = IssuedCertificate::create([
            'school_id'          => $template->school_id,
            'template_id'        => $template->id,
            'student_id'         => $student->id,
            'issued_by'          => auth()->id(),
            'certificate_number' => IssuedCertificate::generateNumber($template->type),
            'type'               => $template->type,
            'issued_date'        => $data['issued_date'],
            'valid_until'        => $data['valid_until'] ?? null,
            'purpose'            => $data['purpose'] ?? null,
            'extra_notes'        => $data['extra_notes'] ?? null,
            'field_overrides'    => $fieldOverrides ?: null,
            'status'             => 'issued',
            'language'           => $data['language'],
            'paper_size'         => $data['paper_size'],
            'verification_token' => IssuedCertificate::generateToken(),
        ]);

        return redirect()->route('certificates.download', $issued->id)
            ->with('status', 'Certificate issued: ' . $issued->certificate_number);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // BULK ISSUE
    // ═══════════════════════════════════════════════════════════════════════

    public function bulkForm()
    {
        try {
            $this->authorize('create students');

            $templates = CertificateTemplate::where('is_active', true)->latest()->get();
            $classes   = SchoolClass::orderBy('class_name')->get();

            return view('certificates.bulk', compact('templates', 'classes'));
        } catch (\Exception $e) {
            return view('certificates.bulk', [
                'templates' => collect(),
                'classes' => collect(),
                'error' => 'Unable to load certificate bulk form: ' . $e->getMessage()
            ]);
        }
    }

    public function bulkIssue(Request $request)
    {
        $this->authorize('create students');

        $data = $request->validate([
            'template_id' => 'required|exists:certificate_templates,id',
            'class_id'    => 'required|exists:school_classes,id',
            'section_id'  => 'nullable|exists:sections,id',
            'issued_date' => 'required|date',
            'language'    => 'required|in:en,ne',
            'paper_size'  => 'required|in:A4,Letter',
            'extra_notes' => 'nullable|string|max:1000',
            'field_overrides_defaults' => 'nullable|array',
        ]);

        $template  = CertificateTemplate::findOrFail($data['template_id']);
        $sessionId = $this->currentSessionId();

        $query = Promotion::with('student.academic_info', 'student.parent_info', 'schoolClass', 'section')
            ->where('session_id', $sessionId)
            ->where('class_id', $data['class_id']);

        if (!empty($data['section_id'])) {
            $query->where('section_id', $data['section_id']);
        }

        $promotions = $query->get();

        if ($promotions->isEmpty()) {
            return back()->with('error', 'No students found for the selected class/section.');
        }

        $defaultOverrides = [];
        if (!empty($data['field_overrides_defaults']) && is_array($data['field_overrides_defaults'])) {
            foreach ($data['field_overrides_defaults'] as $k => $v) {
                if (is_string($k) && !is_null($v)) $defaultOverrides[$k] = (string) $v;
            }
        }

        // Create IssuedCertificate records + build PDF items
        $items = [];
        DB::transaction(function () use ($promotions, $template, $data, $defaultOverrides, &$items) {
            foreach ($promotions as $promotion) {
                $student = $promotion->student;
                if (!$student) continue;

                $issued = IssuedCertificate::create([
                    'school_id'          => $template->school_id,
                    'template_id'        => $template->id,
                    'student_id'         => $student->id,
                    'issued_by'          => auth()->id(),
                    'certificate_number' => IssuedCertificate::generateNumber($template->type),
                    'type'               => $template->type,
                    'issued_date'        => $data['issued_date'],
                    'extra_notes'        => $data['extra_notes'] ?? null,
                    'field_overrides'    => $defaultOverrides ?: null,
                    'status'             => 'issued',
                    'language'           => $data['language'],
                    'paper_size'         => $data['paper_size'],
                    'verification_token' => IssuedCertificate::generateToken(),
                ]);

                $items[] = [
                    'student'  => $student,
                    'issued'   => $issued,
                    'bodyText' => $template->render($student, [
                        '{{extra_notes}}' => $data['extra_notes'] ?? '',
                    ], $data['language']),
                ];
            }
        });

        // Stream bulk PDF
        [$width, $height] = $this->pdfDimensions($data['paper_size'], $template->orientation);

        $pdf = Pdf::loadView('certificates.pdf-bulk', compact('items', 'template'))
            ->setPaper([$width, $height], $template->orientation)
            ->setOptions([
                'dpi'                  => 120,
                'defaultFont'          => 'DejaVu Sans',
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'tempDir'              => storage_path('app/temp'),
                'margin_top'    => 0,
                'margin_right'  => 0,
                'margin_bottom' => 0,
                'margin_left'   => 0,
            ]);

        return $pdf->stream('certificates-bulk-' . now()->format('Ymd') . '.pdf');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // DOWNLOAD / RE-PRINT individual issued certificate
    // ═══════════════════════════════════════════════════════════════════════

    public function download(int $issuedId)
    {
        $this->authorize('view students');

        $issued   = IssuedCertificate::with('student.academic_info', 'student.parent_info',
                        'student.promotions.schoolClass', 'student.promotions.section',
                        'template')->findOrFail($issuedId);
        $student  = $issued->student;
        $template = $issued->template;

        $bodyText = $template->render($student, [
            '{{extra_notes}}' => $issued->extra_notes ?? '',
            '{{purpose}}'     => $issued->purpose ?? '',
        ], $issued->language);

        [$width, $height] = $this->pdfDimensions($issued->paper_size, $template->orientation);

        $pdf = Pdf::loadView('certificates.pdf', compact('student', 'template', 'issued', 'bodyText'))
            ->setPaper([$width, $height], $template->orientation)
            ->setOptions([
                'dpi'                  => 150,
                'defaultFont'          => 'DejaVu Sans',
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'tempDir'              => storage_path('app/temp'),
                'margin_top'    => 0,
                'margin_right'  => 0,
                'margin_bottom' => 0,
                'margin_left'   => 0,
            ]);

        $filename = 'certificate-' . $issued->certificate_number . '.pdf';
        return $pdf->stream($filename);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // REVOKE
    // ═══════════════════════════════════════════════════════════════════════

    public function revoke(Request $request, int $issuedId)
    {
        $this->authorize('create students');
        $issued = IssuedCertificate::findOrFail($issuedId);
        $issued->update(['status' => 'revoked']);
        return back()->with('status', 'Certificate ' . $issued->certificate_number . ' has been revoked.');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PUBLIC VERIFICATION
    // ═══════════════════════════════════════════════════════════════════════

    /** Public endpoint — anyone with the token (via QR) can verify */
    public function verify(string $token)
    {
        $issued = IssuedCertificate::with('student', 'template', 'issuer')
            ->where('verification_token', $token)
            ->first();

        return view('certificates.verify', compact('issued'));
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ISSUED CERTIFICATES LEDGER
    // ═══════════════════════════════════════════════════════════════════════

    public function issued(Request $request)
    {
        $this->authorize('view students');

        $query = IssuedCertificate::with('student', 'template', 'issuer')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('certificate_number', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($s) use ($search) {
                      $s->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhereHas('academic_info', function ($ai) use ($search) {
                            $ai->where('admission_no', 'like', "%{$search}%");
                        });
                  });
            });
        }

        $records = $query->paginate(25)->withQueryString();
        $types   = CertificateTemplate::TYPES;

        return view('certificates.issued', compact('records', 'types'));
    }

    // ═══════════════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Return [width, height] in points for DomPDF setPaper.
     * DomPDF portrait = [0, 0, width, height]; landscape handled by orientation param.
     */
    private function pdfDimensions(string $paperSize, string $orientation): array
    {
        $sizes = [
            'A4'     => [595.28, 841.89],
            'Letter' => [612,    792],
        ];

        [$w, $h] = $sizes[$paperSize] ?? $sizes['A4'];

        // DomPDF handles orientation itself — pass portrait dimensions always
        return [0, 0, $w, $h];
    }
}
