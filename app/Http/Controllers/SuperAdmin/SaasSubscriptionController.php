<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SaasInvoice;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SaasSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display a listing of schools and their subscription & billing plan status.
     */
    public function index(Request $request)
    {
        $query = School::withTrashed()->withCount('users');

        // Filter by plan
        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        // Filter by subscription type (paid, free, expiring, expired)
        if ($request->filter === 'paid') {
            $query->whereIn('plan', ['starter', 'growth', 'enterprise']);
        } elseif ($request->filter === 'free') {
            $query->where('plan', 'free');
        } elseif ($request->filter === 'expiring') {
            $query->whereNotNull('plan_expires_at')
                  ->where('plan_expires_at', '>=', now())
                  ->where('plan_expires_at', '<=', now()->addDays(30));
        } elseif ($request->filter === 'expired') {
            $query->whereNotNull('plan_expires_at')
                  ->where('plan_expires_at', '<', now());
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $schools = $query->latest('created_at')->paginate(20)->withQueryString();

        // Calculate billing totals per school
        $schoolIds = $schools->pluck('id')->toArray();
        $billingTotals = SaasInvoice::whereIn('school_id', $schoolIds)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('school_id, SUM(total_amount) as total_billed, SUM(paid_amount) as total_paid, SUM(due_amount) as total_due')
            ->groupBy('school_id')
            ->get()
            ->keyBy('school_id');

        $stats = [
            'total_schools'    => School::count(),
            'paid_plans'       => School::whereIn('plan', ['starter', 'growth', 'enterprise'])->count(),
            'free_plans'       => School::where('plan', 'free')->count(),
            'expiring_soon'    => School::whereNotNull('plan_expires_at')
                                        ->where('plan_expires_at', '>=', now())
                                        ->where('plan_expires_at', '<=', now()->addDays(30))
                                        ->count(),
            'expired_plans'    => School::whereNotNull('plan_expires_at')
                                        ->where('plan_expires_at', '<', now())
                                        ->count(),
            'total_saas_collected' => (float) SaasInvoice::where('status', '!=', 'cancelled')->sum('paid_amount'),
        ];

        $planLimits = School::planLimits();

        return view('super-admin.subscriptions.index', compact('schools', 'billingTotals', 'stats', 'planLimits'));
    }

    /**
     * Start / Update subscription plan for a school.
     */
    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'plan'                => 'required|in:free,starter,growth,enterprise',
            'duration_months'     => 'nullable|integer|min:1|max:60',
            'plan_expires_at'     => 'nullable|date',
            'max_students'        => 'nullable|integer|min:1',
            'max_teachers'        => 'nullable|integer|min:1',
            'max_staff'           => 'nullable|integer|min:1',
            'whatsapp_quota'      => 'nullable|integer|min:0',
            'ai_features_enabled' => 'nullable|boolean',
        ]);

        $defaultLimits = School::planLimits()[$request->plan] ?? [
            'students' => 200,
            'teachers' => 20,
            'staff'    => 10,
            'ai'       => false,
        ];

        $updates = [
            'plan'                => $request->plan,
            'max_students'        => $request->filled('max_students') ? (int) $request->max_students : $defaultLimits['students'],
            'max_teachers'        => $request->filled('max_teachers') ? (int) $request->max_teachers : $defaultLimits['teachers'],
            'max_staff'           => $request->filled('max_staff') ? (int) $request->max_staff : $defaultLimits['staff'],
            'ai_features_enabled' => $request->has('ai_features_enabled') ? (bool) $request->ai_features_enabled : (bool) ($defaultLimits['ai'] ?? false),
        ];

        if ($request->filled('whatsapp_quota')) {
            $updates['whatsapp_quota'] = (int) $request->whatsapp_quota;
        }

        // Calculate expiration date if duration specified
        if ($request->filled('duration_months')) {
            $months = (int) $request->duration_months;
            $startDate = ($school->plan_expires_at && $school->plan_expires_at->isFuture())
                ? $school->plan_expires_at
                : now();
            $updates['plan_expires_at'] = Carbon::parse($startDate)->addMonths($months)->toDateString();
        } elseif ($request->filled('plan_expires_at')) {
            $updates['plan_expires_at'] = $request->plan_expires_at;
        }

        $school->update($updates);

        return back()->with('success', "Subscription for \"{$school->name}\" updated successfully to {$school->plan_label} plan.");
    }
}
