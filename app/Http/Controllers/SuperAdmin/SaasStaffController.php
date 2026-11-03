<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SaasStaffController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display a listing of SaaS platform staff.
     */
    public function index(Request $request)
    {
        $query = User::whereNull('school_id')
            ->where(function ($q) {
                $q->whereHas('roles', function ($rq) {
                    $rq->whereIn('name', ['super-admin', 'saas-admin', 'saas-staff', 'saas-billing', 'saas-support']);
                })->orWhere('role', 'super-admin');
            })
            ->with(['roles', 'permissions']);

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);
            });
        }

        $staffMembers = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $stats = [
            'total'     => User::whereNull('school_id')->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'saas-admin', 'saas-staff', 'saas-billing', 'saas-support']))->count(),
            'admins'    => User::whereNull('school_id')->role(['super-admin', 'saas-admin'])->count(),
            'billing'   => User::whereNull('school_id')->role('saas-billing')->count(),
            'support'   => User::whereNull('school_id')->role('saas-support')->count(),
        ];

        return view('super-admin.staff.index', compact('staffMembers', 'stats'));
    }

    /**
     * Show the form for creating a new SaaS staff member.
     */
    public function create()
    {
        $roles = [
            'saas-admin'   => 'SaaS Administrator (पूर्ण व्यवस्थापक)',
            'saas-billing' => 'Billing & Accounts Officer (बिलिङ तथा वित्तीय अधिकृत)',
            'saas-support' => 'School Support & Onboarding (विद्यालय सहयोग तथा दर्ता अधिकृत)',
            'saas-staff'   => 'General SaaS Staff (साधारण कर्मचारी)',
        ];

        $permissionGroups = $this->getSaaSGroupedPermissions();

        return view('super-admin.staff.create', compact('roles', 'permissionGroups'));
    }

    /**
     * Store a newly created SaaS staff member.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'    => 'required|string|max:60',
            'last_name'     => 'required|string|max:60',
            'email'         => 'required|email|max:100|unique:users,email',
            'phone'         => 'nullable|string|max:30',
            'password'      => 'required|string|min:8|confirmed',
            'role'          => 'required|string|in:saas-admin,saas-billing,saas-support,saas-staff',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $user = User::create([
            'first_name'        => $validated['first_name'],
            'last_name'         => $validated['last_name'],
            'email'             => strtolower($validated['email']),
            'phone'             => $validated['phone'] ?? '9800000000',
            'gender'            => $request->gender ?? 'Male',
            'nationality'       => $request->nationality ?? 'Nepali',
            'address'           => $request->address ?? 'Nepal',
            'address2'          => '',
            'city'              => $request->city ?? 'Kathmandu',
            'zip'               => '00000',
            'password'          => Hash::make($validated['password']),
            'school_id'         => null,
            'role'              => $validated['role'],
            'email_verified_at' => now(),
        ]);

        // Assign Spatie Role
        Role::firstOrCreate(['name' => $validated['role'], 'guard_name' => 'web']);
        $user->assignRole($validated['role']);

        // Sync Granular Permissions
        if (!empty($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        return redirect()->route('super.staff.index')
            ->with('success', "SaaS Staff [{$user->full_name}] created successfully with [{$validated['role']}] role.");
    }

    /**
     * Show the form for editing the specified SaaS staff member.
     */
    public function edit(User $staff)
    {
        if ($staff->school_id !== null) {
            abort(404, 'Not a platform staff member.');
        }

        if ($staff->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admin can edit Super Admin accounts.');
        }

        $roles = [
            'saas-admin'   => 'SaaS Administrator (पूर्ण व्यवस्थापक)',
            'saas-billing' => 'Billing & Accounts Officer (बिलिङ तथा वित्तीय अधिकृत)',
            'saas-support' => 'School Support & Onboarding (विद्यालय सहयोग तथा दर्ता अधिकृत)',
            'saas-staff'   => 'General SaaS Staff (साधारण कर्मचारी)',
        ];

        if (auth()->user()->isSuperAdmin()) {
            $roles['super-admin'] = 'Super Admin (पूर्ण पहुँच - Owner)';
        }

        $permissionGroups = $this->getSaaSGroupedPermissions();
        $userPermissions = $staff->getAllPermissions()->pluck('name')->toArray();
        $currentRole = $staff->getRoleNames()->first() ?? $staff->role;

        return view('super-admin.staff.edit', compact('staff', 'roles', 'permissionGroups', 'userPermissions', 'currentRole'));
    }

    /**
     * Update the specified SaaS staff member.
     */
    public function update(Request $request, User $staff)
    {
        if ($staff->school_id !== null) {
            abort(404, 'Not a platform staff member.');
        }

        if ($staff->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admin can update Super Admin accounts.');
        }

        $validated = $request->validate([
            'first_name'    => 'required|string|max:60',
            'last_name'     => 'required|string|max:60',
            'email'         => "required|email|max:100|unique:users,email,{$staff->id}",
            'phone'         => 'nullable|string|max:30',
            'password'      => 'nullable|string|min:8|confirmed',
            'role'          => 'required|string',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $updateData = [
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => strtolower($validated['email']),
            'phone'      => $validated['phone'] ?? null,
            'role'       => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $staff->update($updateData);

        // Update role
        Role::firstOrCreate(['name' => $validated['role'], 'guard_name' => 'web']);
        $staff->syncRoles([$validated['role']]);

        // Sync Permissions
        $permissions = $validated['permissions'] ?? [];
        $staff->syncPermissions($permissions);

        return redirect()->route('super.staff.index')
            ->with('success', "Staff [{$staff->full_name}] updated successfully.");
    }

    /**
     * Remove the specified SaaS staff member.
     */
    public function destroy(User $staff)
    {
        if ($staff->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($staff->isSuperAdmin()) {
            return back()->with('error', 'Super Admin accounts cannot be deleted from staff management.');
        }

        $name = $staff->full_name;
        $staff->delete();

        return redirect()->route('super.staff.index')
            ->with('success', "Staff member [{$name}] removed from SaaS platform.");
    }

    /**
     * Helper to get structured SaaS permissions for checklist grouping.
     */
    private function getSaaSGroupedPermissions(): array
    {
        return [
            'विद्यालय तथा ग्राहक व्यवस्थापन (Schools & Tenants)' => [
                'manage saas schools' => [
                    'label' => 'Manage Schools & Approvals',
                    'desc'  => 'नयाँ विद्यालय स्वीकृत गर्ने, विवरण हेर्ने, निलम्बन तथा सक्रिय गर्ने',
                ],
                'impersonate schools' => [
                    'label' => 'Impersonate School Admin',
                    'desc'  => 'विद्यालयको एडमिनको रूपमा लगइन गरी समस्या समाधान गर्ने (Login as Admin)',
                ],
                'manage saas google' => [
                    'label' => 'Google Workspace Hub',
                    'desc'  => 'विद्यालयहरूको गुगल वर्कस्पेस डोमेन, एकाउन्ट तथा सिंक व्यवस्थापन',
                ],
            ],

            'योजना तथा सदस्यता व्यवस्थापन (Plans & Subscriptions)' => [
                'manage saas plans' => [
                    'label' => 'Manage Plans & Quotas',
                    'desc'  => 'विद्यालयको योजना परिवर्तन गर्ने (Free, Starter, Growth, Enterprise) र म्याद थप गर्ने',
                ],
            ],

            'बिलिङ, बिजक तथा भुक्तानी (Billing & Payments)' => [
                'manage saas billing' => [
                    'label' => 'SaaS Invoicing & Bills',
                    'desc'  => 'विद्यालयका लागि नयाँ बिल/बिजक जारी गर्ने, बिल सम्पादन तथा प्रिन्ट गर्ने',
                ],
                'manage saas payments' => [
                    'label' => 'Record Payments & Receipts',
                    'desc'  => 'विद्यालयबाट प्राप्त रकम (eSewa, Khalti, Bank) प्रविष्टि तथा रसिद जारी गर्ने',
                ],
            ],

            'सेवा सम्झौता तथा सेवा सूची (Agreements & Catalog)' => [
                'manage saas agreements' => [
                    'label' => 'Service Agreements (सेवा सम्झौता)',
                    'desc'  => 'विद्यालयसँगको औपचारिक सफ्टवेयर सम्झौता तयार गर्ने, सम्पादन र प्रिन्ट गर्ने',
                ],
                'manage saas services' => [
                    'label' => 'Hardware & Service Catalog',
                    'desc'  => 'थर्मल प्रिन्टर, बायोमेट्रिक हाजिरी, कम्प्युटर ल्याब सेटअप दर सूची व्यवस्थापन',
                ],
            ],

            'सञ्चार तथा ग्राहक सहायता (Support & Communication)' => [
                'manage saas messages' => [
                    'label' => 'Contact Inbox & Support',
                    'desc'  => 'वेबसाइटबाट आएका सोधपुछ तथा सन्देशहरू हेर्ने, स्थिति परिवर्तन तथा नोट थप्ने',
                ],
            ],

            'साँस टिम तथा सुरक्षा (Platform Staff & Roles)' => [
                'manage saas staff' => [
                    'label' => 'Manage SaaS Staff & Access',
                    'desc'  => 'नयाँ कर्मचारी थप्ने, भूमिका (Role) परिवर्तन तथा अनुमति (Permissions) तोक्ने',
                ],
            ],
        ];
    }
}
