<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
