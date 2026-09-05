<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use App\Models\School;
use App\Models\SchoolCommitteeMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SchoolManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super-admin']);
    }

    /** GET /super/schools */
    public function index(Request $request)
    {
        $query = School::withTrashed()->withCount('users');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($plan = $request->get('plan')) {
            $query->where('plan', $plan);
        }
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $schools = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'     => School::withTrashed()->count(),
            'active'    => School::where('status', 'active')->count(),
            'pending'   => School::where('status', 'pending')->count(),
            'suspended' => School::where('status', 'suspended')->count(),
        ];

        return view('saas.super.schools.index', compact('schools', 'stats'));
    }

    /** GET /super/schools/{school} */
    public function show(School $school)
    {
        $school->load('owner', 'approvedBy');
        $userStats = [
            'total'    => User::where('school_id', $school->id)->count(),
            'students' => User::where('school_id', $school->id)->role('student')->count(),
            'teachers' => User::where('school_id', $school->id)->role('teacher')->count(),
            'staff'    => User::where('school_id', $school->id)->count(),
        ];
        $limits = School::planLimits()[$school->plan] ?? [];
        return view('saas.super.schools.show', compact('school', 'userStats', 'limits'));
    }

    /** POST /super/schools/{school}/approve */
    public function approve(School $school)
    {
        $school->update([
            'status'      => 'active',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // When a school is approved, also mark its owner/admin as email-verified
        // so they can sign in immediately without a separate verification email.
        $owner = $school->owner;
        if ($owner && empty($owner->email_verified_at)) {
            $owner->update(['email_verified_at' => now()]);
        }
        User::where('school_id', $school->id)
            ->whereNull('email_verified_at')
            ->where('role', 'admin')
            ->update(['email_verified_at' => now()]);

        return back()->with('success', "School \"{$school->name}\" approved and activated. Owner admin can now log in.");
    }

    /** POST /super/schools/{school}/suspend */
    public function suspend(Request $request, School $school)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);

        $settings = $school->settings ?? [];
        $settings['suspension_reason'] = $request->reason;

        $school->update([
            'status'   => 'suspended',
            'settings' => $settings,
        ]);

        return back()->with('success', "School \"{$school->name}\" has been suspended.");
    }

    /** POST /super/schools/{school}/reactivate */
    public function reactivate(School $school)
    {
        $school->update(['status' => 'active']);
        return back()->with('success', "School \"{$school->name}\" reactivated.");
    }

    /** PATCH /super/schools/{school}/plan */
    public function changePlan(Request $request, School $school)
    {
        $request->validate(['plan' => 'required|in:free,starter,growth,enterprise']);

        $limits = School::planLimits()[$request->plan];
        $school->update([
            'plan'                => $request->plan,
            'max_students'        => $limits['students'],
            'max_teachers'        => $limits['teachers'],
            'max_staff'           => $limits['staff'],
            'ai_features_enabled' => $limits['ai'],
        ]);

        return back()->with('success', "Plan updated to {$school->plan_label}.");
    }

    /** DELETE /super/schools/{school} — soft delete */
    public function destroy(School $school)
    {
        $school->update(['status' => 'cancelled']);
        $school->delete();
        return redirect()->route('super.schools.index')->with('success', "School cancelled and archived.");
    }

    /** POST /super/schools/{school}/impersonate — login as school admin */
    public function impersonate(School $school)
    {
        $admin = User::where('school_id', $school->id)
                     ->role('admin')
                     ->first();

        if (!$admin) {
            return back()->with('error', 'No admin user found for this school.');
        }

        // Store super-admin's original ID to allow "stop impersonating"
        session(['impersonating_as' => $admin->id, 'impersonator_id' => Auth::id()]);
        Auth::login($admin);

        return redirect()->route('home')->with('status', "Now viewing as admin of {$school->name}. Click 'Stop Impersonating' to return.");
    }

    /** GET /super/impersonate/stop */
    public function stopImpersonate()
    {
        $originalId = session('impersonator_id');
        if ($originalId) {
            $original = User::find($originalId);
            if ($original) {
                Auth::login($original);
                session()->forget(['impersonating_as', 'impersonator_id']);
                return redirect()->route('super.schools.index')->with('success', 'Returned to super-admin account.');
            }
        }
        return redirect()->route('home');
    }

    // ────────────────────────────────────────────────────────────────────────
    // School Management Committee (SMC) — Governance tab
    // ────────────────────────────────────────────────────────────────────────

    /** GET /super/schools/{school}/committee */
    public function showCommittee(School $school)
    {
        $school->load('committeeMembers.documents', 'owner', 'approvedBy');

        $userStats = [
            'total'    => User::where('school_id', $school->id)->count(),
            'students' => User::where('school_id', $school->id)->role('student')->count(),
            'teachers' => User::where('school_id', $school->id)->role('teacher')->count(),
            'staff'    => User::where('school_id', $school->id)->count(),
        ];
        $limits = School::planLimits()[$school->plan] ?? [];

        $members = $school->committeeMembers()
                          ->with(['documents', 'createdBy'])
                          ->orderByRaw("FIELD(position, 'chairperson','vice_chairperson','secretary','treasurer') DESC, position, full_name")
                          ->get();

        $compliance = [
            'total'     => $members->count(),
            'compliant' => $members->where('no_political_party_member', true)->count(),
            'with_docs' => $members->filter(fn($m) => $m->documents->count() > 0)->count(),
        ];

        return view('saas.super.schools.show', compact(
            'school', 'userStats', 'limits', 'members', 'compliance'
        ));
    }

    /** POST /super/schools/{school}/committee/members */
    public function storeCommitteeMember(Request $request, School $school)
    {
        $validated = $request->validate([
            'full_name'                 => 'required|string|max:200',
            'position'                  => 'required|in:'.implode(',', array_keys(SchoolCommitteeMember::POSITIONS)),
            'phone'                     => 'nullable|string|max:20',
            'email'                     => 'nullable|email|max:200',
            'no_political_party_member' => 'boolean',
            'citizenship_no'            => 'nullable|string|max:50',
            'voter_id_no'               => 'nullable|string|max:50',
            'appointed_at'              => 'nullable|date',
            'term_ends_at'              => 'nullable|date',
            'notes'                     => 'nullable|string|max:1000',
        ]);

        $validated['school_id']   = $school->id;
        $validated['created_by']  = Auth::id();
        $validated['updated_by']  = Auth::id();
        if (!empty($validated['no_political_party_member'])) {
            $validated['declaration_signed_at'] = now();
        }

        $member = SchoolCommitteeMember::create($validated);

        return redirect()
            ->route('super.schools.committee', $school)
            ->with('success', "Added SMC member: {$member->full_name} ({$member->position_label}).");
    }

    /** PATCH /super/schools/{school}/committee/members/{member} */
    public function updateCommitteeMember(Request $request, School $school, SchoolCommitteeMember $member)
    {
        abort_unless($member->school_id === $school->id, 404);

        $validated = $request->validate([
            'full_name'                 => 'required|string|max:200',
            'position'                  => 'required|in:'.implode(',', array_keys(SchoolCommitteeMember::POSITIONS)),
            'phone'                     => 'nullable|string|max:20',
            'email'                     => 'nullable|email|max:200',
            'no_political_party_member' => 'boolean',
            'citizenship_no'            => 'nullable|string|max:50',
            'voter_id_no'               => 'nullable|string|max:50',
            'appointed_at'              => 'nullable|date',
            'term_ends_at'              => 'nullable|date',
            'notes'                     => 'nullable|string|max:1000',
        ]);

        $validated['updated_by'] = Auth::id();
        if (empty($member->declaration_signed_at) && !empty($validated['no_political_party_member'])) {
            $validated['declaration_signed_at'] = now();
        } elseif (empty($validated['no_political_party_member'])) {
            $validated['declaration_signed_at'] = null;
        }

        $member->update($validated);

        return redirect()
            ->route('super.schools.committee', $school)
            ->with('success', "Updated SMC member: {$member->full_name}.");
    }

    /** DELETE /super/schools/{school}/committee/members/{member} */
    public function destroyCommitteeMember(School $school, SchoolCommitteeMember $member)
    {
        abort_unless($member->school_id === $school->id, 404);

        $name = $member->full_name;
        $member->documents->each(function (MediaFile $doc) {
            Storage::disk($doc->disk)->delete($doc->path);
            $doc->forceDelete();
        });
        $member->delete(); // soft-delete

        return redirect()
            ->route('super.schools.committee', $school)
            ->with('success', "Removed SMC member: {$name}.");
    }

    /** POST /super/schools/{school}/committee/members/{member}/document */
    public function uploadCommitteeDocument(Request $request, School $school, SchoolCommitteeMember $member)
    {
        abort_unless($member->school_id === $school->id, 404);

        $request->validate([
            'documents'   => 'required|array|max:5',
            'documents.*' => 'required|file|max:20480',
            'collection'  => 'required|in:smc_id_proof,smc_declaration',
        ]);

        $collection = $request->collection;
        $uploaded   = 0;

        foreach ($request->file('documents') as $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            $fileName  = Str::uuid() . '.' . $extension;
            $directory = 'uploads/' . $collection . '/' . now()->format('Y/m');

            $path = $file->storeAs($directory, $fileName, 'local');

            MediaFile::create([
                'school_id'     => $school->id,
                'uploaded_by'   => Auth::id(),
                'collection'    => $collection,
                'model_type'    => SchoolCommitteeMember::class,
                'model_id'      => $member->id,
                'file_name'     => $fileName,
                'original_name' => basename(str_replace(['..', '/', '\\'], '', $file->getClientOriginalName())),
                'mime_type'     => $file->getMimeType(),
                'disk'          => 'local',
                'path'          => $path,
                'size'          => $file->getSize(),
                'extension'     => $extension,
                'is_public'     => false,
            ]);

            $uploaded++;
        }

        return redirect()
            ->route('super.schools.committee', $school)
            ->with('success', "Uploaded {$uploaded} document(s) for {$member->full_name}.");
    }

    /** DELETE /super/schools/{school}/committee/documents/{mediaFile} */
    public function destroyCommitteeDocument(School $school, MediaFile $mediaFile)
    {
        abort_unless(
            $mediaFile->model_type === SchoolCommitteeMember::class
            && SchoolCommitteeMember::where('id', $mediaFile->model_id)
                                     ->where('school_id', $school->id)
                                     ->exists(),
            404
        );

        Storage::disk($mediaFile->disk)->delete($mediaFile->path);
        $mediaFile->forceDelete();

        return redirect()
            ->route('super.schools.committee', $school)
            ->with('success', 'Document deleted.');
    }
}
