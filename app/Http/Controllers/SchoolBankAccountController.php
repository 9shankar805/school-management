<?php

namespace App\Http\Controllers;

use App\Models\SchoolBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolBankAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:manage finance']);
    }

    /**
     * Display multi-bank master listing and Fonepay configurations.
     */
    public function index()
    {
        $this->ensureDefaultBanksExist();

        $bankAccounts = SchoolBankAccount::withCount('payments')
            ->defaultFirst()
            ->get();

        return view('finance.bank-accounts.index', compact('bankAccounts'));
    }

    /**
     * Store a new school bank account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name'           => 'required|string|max:150',
            'bank_code'           => 'nullable|string|max:30',
            'account_name'        => 'required|string|max:150',
            'account_number'      => 'required|string|max:50',
            'branch_name'         => 'nullable|string|max:150',
            'account_type'        => 'required|string|in:Current,Savings',
            'fonepay_merchant_id' => 'nullable|string|max:50',
            'fonepay_pan'         => 'nullable|string|max:30',
            'qr_payload'          => 'nullable|string',
            'qr_image'            => 'nullable|image|max:3072',
            'is_default'          => 'nullable|boolean',
            'is_active'           => 'nullable|boolean',
            'notes'               => 'nullable|string|max:500',
        ]);

        $schoolId = auth()->user()->school_id ?? null;
        $isDefault = !empty($validated['is_default']);

        if ($isDefault) {
            SchoolBankAccount::where('school_id', $schoolId)->update(['is_default' => false]);
        }

        $qrPath = null;
        if ($request->hasFile('qr_image')) {
            $qrPath = $request->file('qr_image')->store('bank_qrs', 'public');
        }

        SchoolBankAccount::create([
            'school_id'           => $schoolId,
            'bank_name'           => $validated['bank_name'],
            'bank_code'           => $validated['bank_code'] ?? strtoupper(substr($validated['bank_name'], 0, 5)),
            'account_name'        => $validated['account_name'],
            'account_number'      => $validated['account_number'],
            'branch_name'         => $validated['branch_name'] ?? null,
            'account_type'        => $validated['account_type'],
            'fonepay_merchant_id' => $validated['fonepay_merchant_id'] ?? null,
            'fonepay_pan'         => $validated['fonepay_pan'] ?? null,
            'qr_payload'          => $validated['qr_payload'] ?? null,
            'qr_image'            => $qrPath,
            'is_default'          => $isDefault,
            'is_active'           => $request->has('is_active') ? true : true,
            'notes'               => $validated['notes'] ?? null,
        ]);

        return redirect()->route('finance.bank-accounts.index')->with('status', 'बैंक खाता सफलतापूर्वक थप गरियो (Bank account added successfully).');
    }

    /**
     * Update an existing bank account.
     */
    public function update(Request $request, $id)
    {
        $account = SchoolBankAccount::findOrFail($id);

        $validated = $request->validate([
            'bank_name'           => 'required|string|max:150',
            'bank_code'           => 'nullable|string|max:30',
            'account_name'        => 'required|string|max:150',
            'account_number'      => 'required|string|max:50',
            'branch_name'         => 'nullable|string|max:150',
            'account_type'        => 'required|string|in:Current,Savings',
            'fonepay_merchant_id' => 'nullable|string|max:50',
            'fonepay_pan'         => 'nullable|string|max:30',
            'qr_payload'          => 'nullable|string',
            'qr_image'            => 'nullable|image|max:3072',
            'is_default'          => 'nullable|boolean',
            'is_active'           => 'nullable|boolean',
            'notes'               => 'nullable|string|max:500',
        ]);

        $isDefault = !empty($validated['is_default']);

        if ($isDefault) {
            SchoolBankAccount::where('id', '!=', $id)->update(['is_default' => false]);
        }

        if ($request->hasFile('qr_image')) {
            if ($account->qr_image && Storage::disk('public')->exists($account->qr_image)) {
                Storage::disk('public')->delete($account->qr_image);
            }
            $account->qr_image = $request->file('qr_image')->store('bank_qrs', 'public');
        }

        $account->update([
            'bank_name'           => $validated['bank_name'],
            'bank_code'           => $validated['bank_code'] ?? $account->bank_code,
            'account_name'        => $validated['account_name'],
            'account_number'      => $validated['account_number'],
            'branch_name'         => $validated['branch_name'] ?? null,
            'account_type'        => $validated['account_type'],
            'fonepay_merchant_id' => $validated['fonepay_merchant_id'] ?? null,
            'fonepay_pan'         => $validated['fonepay_pan'] ?? null,
            'qr_payload'          => $validated['qr_payload'] ?? null,
            'is_default'          => $isDefault,
            'is_active'           => $request->has('is_active'),
            'notes'               => $validated['notes'] ?? null,
        ]);

        return redirect()->route('finance.bank-accounts.index')->with('status', 'बैंक खाता विवरण अद्यावधिक गरियो (Bank account updated successfully).');
    }

    /**
     * Set a bank account as default.
     */
    public function setDefault($id)
    {
        SchoolBankAccount::query()->update(['is_default' => false]);
        $account = SchoolBankAccount::findOrFail($id);
        $account->update(['is_default' => true, 'is_active' => true]);

        return back()->with('status', "{$account->bank_name} लाई प्राथमिक काउन्टर खाता बनाइयो (Set as primary counter account).");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleActive($id)
    {
        $account = SchoolBankAccount::findOrFail($id);
        $account->update(['is_active' => !$account->is_active]);

        return back()->with('status', 'बैंक खाताको अवस्था परिवर्तन गरियो (Account status updated).');
    }

    /**
     * Delete a bank account.
     */
    public function destroy($id)
    {
        $account = SchoolBankAccount::findOrFail($id);

        if ($account->payments()->count() > 0) {
            $account->update(['is_active' => false]);
            return back()->with('status', 'यो खातामा भुक्तानी कारोबार जोडिएकाले निष्क्रिय बनाइयो (Account deactivated due to existing payment history).');
        }

        if ($account->qr_image && Storage::disk('public')->exists($account->qr_image)) {
            Storage::disk('public')->delete($account->qr_image);
        }

        $account->delete();

        return back()->with('status', 'बैंक खाता हटाइयो (Bank account removed).');
    }

    /**
     * Ensure realistic default Nepali banks exist for instantaneous zero-config experience.
     */
    public static function ensureDefaultBanksExist(): void
    {
        if (SchoolBankAccount::count() === 0) {
            $school = auth()->user()->school ?? \App\Models\School::first();
            $schoolName = $school->name ?? 'Shree Shanti Secondary School';
            $schoolId = $school->id ?? null;

            SchoolBankAccount::create([
                'school_id'           => $schoolId,
                'bank_name'           => 'Nabil Bank Limited',
                'bank_code'           => 'NABIL',
                'account_name'        => $schoolName,
                'account_number'      => '01201017500123',
                'branch_name'         => 'Kantipath Branch, Kathmandu',
                'account_type'        => 'Current',
                'fonepay_merchant_id' => '9801234567',
                'fonepay_pan'         => '301234567',
                'is_default'          => true,
                'is_active'           => true,
                'notes'               => 'Primary collection account for tuition & term fees via Fonepay Counter QR',
            ]);

            SchoolBankAccount::create([
                'school_id'           => $schoolId,
                'bank_name'           => 'Global IME Bank Ltd.',
                'bank_code'           => 'GLOBAL',
                'account_name'        => $schoolName,
                'account_number'      => '00101010045678',
                'branch_name'         => 'Panipokhari Branch, Kathmandu',
                'account_type'        => 'Current',
                'fonepay_merchant_id' => '9812345678',
                'fonepay_pan'         => '301234567',
                'is_default'          => false,
                'is_active'           => true,
                'notes'               => 'Secondary fee deposit and examination charges account',
            ]);

            SchoolBankAccount::create([
                'school_id'           => $schoolId,
                'bank_name'           => 'Nepal Investment Mega Bank (NIMB)',
                'bank_code'           => 'NIMB',
                'account_name'        => $schoolName,
                'account_number'      => '01801020088990',
                'branch_name'         => 'Durbar Marg Branch, Kathmandu',
                'account_type'        => 'Current',
                'fonepay_merchant_id' => '9823456789',
                'fonepay_pan'         => '301234567',
                'is_default'          => false,
                'is_active'           => true,
                'notes'               => 'Hostel and transportation fee deposits',
            ]);

            SchoolBankAccount::create([
                'school_id'           => $schoolId,
                'bank_name'           => 'NIC Asia Bank Limited',
                'bank_code'           => 'NICASIA',
                'account_name'        => $schoolName,
                'account_number'      => '5441050012345001',
                'branch_name'         => 'New Road Branch, Kathmandu',
                'account_type'        => 'Current',
                'fonepay_merchant_id' => '9834567890',
                'fonepay_pan'         => '301234567',
                'is_default'          => false,
                'is_active'           => true,
                'notes'               => 'Instant mobile banking & ConnectIPS transfers',
            ]);
        }
    }
}
