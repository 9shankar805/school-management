<?php

namespace App\Http\Controllers;

use App\Models\IdCardTemplate;
use App\Models\User;
use App\Models\Promotion;
use App\Models\SchoolSession;
use App\Traits\SchoolSession as SchoolSessionTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IdCardTemplateController extends Controller
{
    use SchoolSessionTrait;

    public function __construct()
    {
        $this->middleware(['auth']);
    }

    // ── List all templates ───────────────────────────────────────────────
    public function index()
    {
        $this->authorize('manage settings');
        $templates = IdCardTemplate::latest()->get();
        return view('id-card-templates.index', compact('templates'));
    }

    // ── Show the visual designer ─────────────────────────────────────────
    public function designer(?int $id = null)
    {
        $this->authorize('manage settings');

        $template = $id
            ? IdCardTemplate::findOrFail($id)
            : IdCardTemplate::defaults();

        $allFields    = IdCardTemplate::ALL_FIELDS;
        $visibleFields = $template->visible_fields ?? IdCardTemplate::DEFAULT_FIELDS;

        return view('id-card-templates.designer', compact('template', 'allFields', 'visibleFields'));
    }

    // ── Save / create template from designer ────────────────────────────
    public function save(Request $request, ?int $id = null)
    {
        $this->authorize('manage settings');

        $data = $request->validate([
            'name'                    => 'required|string|max:120',
            'header_bg_color'         => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'header_text_color'       => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'show_logo'               => 'nullable|boolean',
            'show_school_name'        => 'nullable|boolean',
            'show_tagline'            => 'nullable|boolean',
            'tagline_text'            => 'required|string|max:120',
            'header_height'           => 'required|integer|min:28|max:60',
            'accent_color'            => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'show_photo'              => 'nullable|boolean',
            'photo_size'              => 'required|integer|min:30|max:70',
            'photo_shape'             => 'required|in:square,circle',
            'show_photo_corner_marks' => 'nullable|boolean',
            'show_blood_type'         => 'nullable|boolean',
            'body_bg_color'           => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'name_text_color'         => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'name_font_size'          => 'required|integer|min:7|max:16',
            'name_uppercase'          => 'nullable|boolean',
            'show_role_badge'         => 'nullable|boolean',
            'visible_fields'          => 'nullable|array',
            'visible_fields.*'        => 'string|in:' . implode(',', array_keys(IdCardTemplate::ALL_FIELDS)),
            'footer_bg_color'         => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'footer_text_color'       => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'show_id_number'          => 'nullable|boolean',
            'show_session'            => 'nullable|boolean',
            'show_qr'                 => 'nullable|boolean',
            'footer_left_text'        => 'nullable|string|max:120',
            'footer_center_text'      => 'nullable|string|max:120',
            'show_signature_line'     => 'nullable|boolean',
            'signature_label'         => 'nullable|string|max:80',
            'background_opacity'      => 'required|integer|min:0|max:80',
        ]);

        // Coerce checkboxes (not sent when unchecked)
        foreach ([
            'show_logo','show_school_name','show_tagline','show_photo',
            'show_photo_corner_marks','show_blood_type','name_uppercase',
            'show_role_badge','show_id_number','show_session','show_qr',
            'show_signature_line',
        ] as $bool) {
            $data[$bool] = $request->boolean($bool);
        }

        $data['visible_fields'] = $request->input('visible_fields', []);

        if ($id) {
            $template = IdCardTemplate::findOrFail($id);

            // Handle background image upload
            if ($request->hasFile('background_image')) {
                if ($template->background_image_path) {
                    Storage::disk('public')->delete($template->background_image_path);
                }
                $data['background_image_path'] = \App\Services\ImageWebpService::storeAsWebp($request->file('background_image'), 'id-card-templates/backgrounds', 'public');
            }
            if ($request->boolean('remove_background') && $template->background_image_path) {
                Storage::disk('public')->delete($template->background_image_path);
                $data['background_image_path'] = null;
            }

            $template->update($data);
            $msg = 'Template updated successfully.';
        } else {
            if ($request->hasFile('background_image')) {
                $data['background_image_path'] = \App\Services\ImageWebpService::storeAsWebp($request->file('background_image'), 'id-card-templates/backgrounds', 'public');
            }
            $template = IdCardTemplate::create($data);
            $msg = 'Template created successfully.';
        }

        return redirect()
            ->route('id-card-templates.edit', $template->id)
            ->with('status', $msg);
    }

    // ── Set a template as active ─────────────────────────────────────────
    public function activate(int $id)
    {
        $this->authorize('manage settings');

        // Deactivate all others for this school
        IdCardTemplate::where('is_active', true)->update(['is_active' => false]);

        IdCardTemplate::findOrFail($id)->update(['is_active' => true]);

        return back()->with('status', 'Template set as active.');
    }

    // ── Delete ───────────────────────────────────────────────────────────
    public function destroy(int $id)
    {
        $this->authorize('manage settings');
        $t = IdCardTemplate::findOrFail($id);
        if ($t->background_image_path) {
            Storage::disk('public')->delete($t->background_image_path);
        }
        $t->delete();
        return redirect()->route('id-card-templates.index')->with('status', 'Template deleted.');
    }

    // ── Live preview (AJAX) ───────────────────────────────────────────────
    public function preview(Request $request)
    {
        $this->authorize('manage settings');

        // Build a temporary (unsaved) template object from POST data
        $t = new IdCardTemplate();
        $t->forceFill([
            'name'                    => 'Preview',
            'header_bg_color'         => $request->input('header_bg_color', '#1a56db'),
            'header_text_color'       => $request->input('header_text_color', '#ffffff'),
            'show_logo'               => $request->boolean('show_logo', true),
            'show_school_name'        => $request->boolean('show_school_name', true),
            'show_tagline'            => $request->boolean('show_tagline', true),
            'tagline_text'            => $request->input('tagline_text', 'Student Identification Card'),
            'header_height'           => (int) $request->input('header_height', 38),
            'accent_color'            => $request->input('accent_color', '#f59e0b'),
            'show_photo'              => $request->boolean('show_photo', true),
            'photo_size'              => (int) $request->input('photo_size', 52),
            'photo_shape'             => $request->input('photo_shape', 'square'),
            'show_photo_corner_marks' => $request->boolean('show_photo_corner_marks', true),
            'show_blood_type'         => $request->boolean('show_blood_type', true),
            'body_bg_color'           => $request->input('body_bg_color', '#ffffff'),
            'name_text_color'         => $request->input('name_text_color', '#0f172a'),
            'name_font_size'          => (int) $request->input('name_font_size', 10),
            'name_uppercase'          => $request->boolean('name_uppercase', true),
            'show_role_badge'         => $request->boolean('show_role_badge', true),
            'visible_fields'          => $request->input('visible_fields', IdCardTemplate::DEFAULT_FIELDS),
            'footer_bg_color'         => $request->input('footer_bg_color', '#f8fafc'),
            'footer_text_color'       => $request->input('footer_text_color', '#1e293b'),
            'show_id_number'          => $request->boolean('show_id_number', true),
            'show_session'            => $request->boolean('show_session', true),
            'show_qr'                 => $request->boolean('show_qr', true),
            'footer_left_text'        => $request->input('footer_left_text'),
            'footer_center_text'      => $request->input('footer_center_text'),
            'show_signature_line'     => $request->boolean('show_signature_line', false),
            'signature_label'         => $request->input('signature_label', 'Principal'),
            'background_image_path'   => null,
            'background_opacity'      => (int) $request->input('background_opacity', 10),
        ]);

        // Dummy student data for the preview
        $dummyData = [
            'name'        => 'Aarav Sharma',
            'class'       => 'Grade 10',
            'section'     => 'Science A',
            'gender'      => 'Male',
            'dob'         => 'Jan 15, 2010',
            'phone'       => '+977 98XXXXXXXX',
            'blood_type'  => 'B+',
            'roll_no'     => 'ID-GGS-2026-001',
            'session'     => '2026-2027',
            'photoSrc'    => 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                  <rect width="100" height="100" fill="#dbeafe"/>
                  <circle cx="50" cy="36" r="18" fill="#93c5fd"/>
                  <ellipse cx="50" cy="78" rx="28" ry="18" fill="#93c5fd"/>
                </svg>'
            ),
            'qrSrc'       => 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60">
                  <rect width="60" height="60" fill="#f1f5f9"/>
                  <text x="50%" y="55%" text-anchor="middle" font-size="8" fill="#94a3b8">QR</text>
                </svg>'
            ),
        ];

        $school = config('tenant.school');
        $html   = view('id-card-templates.card-render', compact('t', 'school', 'dummyData'))->render();

        return response($html)->header('Content-Type', 'text/html');
    }
}
