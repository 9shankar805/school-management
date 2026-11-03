<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SaasAgreement;
use App\Models\SaasAgreementItem;
use App\Models\SaasService;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaasAgreementController extends Controller
{
    public function index(Request $request)
    {
        $query = SaasAgreement::with(['school', 'items']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('agreement_number', 'like', "%{$search}%")
                  ->orWhere('school_name', 'like', "%{$search}%")
                  ->orWhere('school_pan_no', 'like', "%{$search}%")
                  ->orWhere('school_representative', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        $agreements = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'       => SaasAgreement::count(),
            'active'      => SaasAgreement::where('status', 'active')->count(),
            'draft'       => SaasAgreement::where('status', 'draft')->count(),
            'total_value' => SaasAgreement::sum('grand_total'),
        ];

        $schools = School::orderBy('name')->select('id', 'name')->get();

        return view('super-admin.agreements.index', compact('agreements', 'stats', 'schools'));
    }

    public function create(Request $request)
    {
        $schools = School::orderBy('name')->get();
        $services = SaasService::where('is_active', true)->orderBy('category')->get();
        $selectedSchool = null;

        if ($request->filled('school_id')) {
            $selectedSchool = School::find($request->school_id);
        }

        $nextNumber = SaasAgreement::generateAgreementNumber();

        return view('super-admin.agreements.create', compact('schools', 'services', 'selectedSchool', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'agreement_number'        => 'nullable|string|max:100|unique:saas_agreements,agreement_number',
            'school_id'               => 'nullable|exists:schools,id',
            'status'                  => 'required|string|in:draft,active,renewed,expired,terminated',
            'agreement_date_bs'       => 'required|string|max:255',
            'bs_year'                 => 'nullable|string|max:20',
            'bs_month'                => 'nullable|string|max:50',
            'bs_day'                  => 'nullable|string|max:20',
            'effective_date'          => 'nullable|date',
            'expiry_date'             => 'nullable|date',
            'duration_years'          => 'required|integer|min:1',
            // Provider
            'provider_name'           => 'required|string|max:255',
            'provider_reg_no'         => 'required|string|max:100',
            'provider_pan_vat'        => 'required|string|max:100',
            'provider_address'        => 'required|string|max:255',
            'provider_phone'          => 'required|string|max:100',
            'provider_email'          => 'required|email|max:255',
            'provider_website'        => 'required|string|max:255',
            'provider_representative' => 'nullable|string|max:255',
            'provider_designation'    => 'nullable|string|max:255',
            // School
            'school_name'             => 'required|string|max:255',
            'school_address'          => 'nullable|string|max:255',
            'school_reg_no'           => 'nullable|string|max:100',
            'school_pan_no'           => 'nullable|string|max:100',
            'school_representative'   => 'nullable|string|max:255',
            'school_designation'      => 'nullable|string|max:255',
            'school_phone'            => 'nullable|string|max:100',
            'school_email'            => 'nullable|email|max:255',
            // Standard fees
            'setup_fee'               => 'required|numeric|min:0',
            'setup_fee_basis'         => 'required|string|max:255',
            'renewal_fee'             => 'required|numeric|min:0',
            'renewal_fee_basis'       => 'required|string|max:255',
            'software_inclusion_text' => 'nullable|string|max:255',
            'notes'                   => 'nullable|string',
            // Witnesses
            'witness_1_name'          => 'nullable|string|max:255',
            'witness_1_date'          => 'nullable|string|max:100',
            'witness_2_name'          => 'nullable|string|max:255',
            'witness_2_date'          => 'nullable|string|max:100',
            // Items
            'items'                   => 'nullable|array',
            'items.*.service_id'      => 'nullable|exists:saas_services,id',
            'items.*.item_name'       => 'required_with:items|string|max:255',
            'items.*.unit_price'      => 'required_with:items|numeric|min:0',
            'items.*.quantity'        => 'required_with:items|integer|min:1',
            'items.*.basis'           => 'nullable|string|max:255',
            'items.*.remarks'         => 'nullable|string|max:255',
        ]);

        if (empty($validated['agreement_number'])) {
            $validated['agreement_number'] = SaasAgreement::generateAgreementNumber();
        }

        $validated['created_by'] = auth()->id();

        DB::beginTransaction();
        try {
            // Compute additional services total
            $additionalTotal = 0;
            $itemsData = $request->input('items', []);

            if (!empty($itemsData)) {
                foreach ($itemsData as $it) {
                    if (!empty($it['item_name'])) {
                        $qty = (int)($it['quantity'] ?? 1);
                        $price = (float)($it['unit_price'] ?? 0);
                        $additionalTotal += ($qty * $price);
                    }
                }
            }

            $validated['additional_services_total'] = $additionalTotal;
            $validated['grand_total'] = (float)$validated['setup_fee'] + (float)$validated['renewal_fee'] + $additionalTotal;

            $agreement = SaasAgreement::create($validated);

            if (!empty($itemsData)) {
                foreach ($itemsData as $it) {
                    if (!empty($it['item_name'])) {
                        $qty = max(1, (int)($it['quantity'] ?? 1));
                        $price = (float)($it['unit_price'] ?? 0);
                        $agreement->items()->create([
                            'service_id'  => !empty($it['service_id']) ? $it['service_id'] : null,
                            'item_name'   => $it['item_name'],
                            'unit_price'  => $price,
                            'quantity'    => $qty,
                            'total_price' => $qty * $price,
                            'basis'       => $it['basis'] ?? 'एकपटक',
                            'remarks'     => $it['remarks'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('super.agreements.show', $agreement->id)
                ->with('success', 'Agreement paper generated successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to generate agreement: ' . $e->getMessage());
        }
    }

    public function show(SaasAgreement $agreement)
    {
        $agreement->load(['school', 'items.service', 'invoices']);
        return view('super-admin.agreements.show', compact('agreement'));
    }

    public function print(SaasAgreement $agreement)
    {
        $agreement->load(['school', 'items.service']);
        return view('super-admin.agreements.print', compact('agreement'));
    }

    public function edit(SaasAgreement $agreement)
    {
        $agreement->load(['school', 'items']);
        $schools = School::orderBy('name')->get();
        $services = SaasService::where('is_active', true)->orderBy('category')->get();

        return view('super-admin.agreements.edit', compact('agreement', 'schools', 'services'));
    }

    public function update(Request $request, SaasAgreement $agreement)
    {
        $validated = $request->validate([
            'agreement_number'        => 'required|string|max:100|unique:saas_agreements,agreement_number,' . $agreement->id,
            'school_id'               => 'nullable|exists:schools,id',
            'status'                  => 'required|string|in:draft,active,renewed,expired,terminated',
            'agreement_date_bs'       => 'required|string|max:255',
            'bs_year'                 => 'nullable|string|max:20',
            'bs_month'                => 'nullable|string|max:50',
            'bs_day'                  => 'nullable|string|max:20',
            'effective_date'          => 'nullable|date',
            'expiry_date'             => 'nullable|date',
            'duration_years'          => 'required|integer|min:1',
            // Provider
            'provider_name'           => 'required|string|max:255',
            'provider_reg_no'         => 'required|string|max:100',
            'provider_pan_vat'        => 'required|string|max:100',
            'provider_address'        => 'required|string|max:255',
            'provider_phone'          => 'required|string|max:100',
            'provider_email'          => 'required|email|max:255',
            'provider_website'        => 'required|string|max:255',
            'provider_representative' => 'nullable|string|max:255',
            'provider_designation'    => 'nullable|string|max:255',
            // School
            'school_name'             => 'required|string|max:255',
            'school_address'          => 'nullable|string|max:255',
            'school_reg_no'           => 'nullable|string|max:100',
            'school_pan_no'           => 'nullable|string|max:100',
            'school_representative'   => 'nullable|string|max:255',
            'school_designation'      => 'nullable|string|max:255',
            'school_phone'            => 'nullable|string|max:100',
            'school_email'            => 'nullable|email|max:255',
            // Standard fees
            'setup_fee'               => 'required|numeric|min:0',
            'setup_fee_basis'         => 'required|string|max:255',
            'renewal_fee'             => 'required|numeric|min:0',
            'renewal_fee_basis'       => 'required|string|max:255',
            'software_inclusion_text' => 'nullable|string|max:255',
            'notes'                   => 'nullable|string',
            // Witnesses
            'witness_1_name'          => 'nullable|string|max:255',
            'witness_1_date'          => 'nullable|string|max:100',
            'witness_2_name'          => 'nullable|string|max:255',
            'witness_2_date'          => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $itemsData = $request->input('items', []);
            $additionalTotal = 0;

            $agreement->items()->delete();

            if (!empty($itemsData)) {
                foreach ($itemsData as $it) {
                    if (!empty($it['item_name'])) {
                        $qty = max(1, (int)($it['quantity'] ?? 1));
                        $price = (float)($it['unit_price'] ?? 0);
                        $tot = $qty * $price;
                        $additionalTotal += $tot;

                        $agreement->items()->create([
                            'service_id'  => !empty($it['service_id']) ? $it['service_id'] : null,
                            'item_name'   => $it['item_name'],
                            'unit_price'  => $price,
                            'quantity'    => $qty,
                            'total_price' => $tot,
                            'basis'       => $it['basis'] ?? 'एकपटक',
                            'remarks'     => $it['remarks'] ?? null,
                        ]);
                    }
                }
            }

            $validated['additional_services_total'] = $additionalTotal;
            $validated['grand_total'] = (float)$validated['setup_fee'] + (float)$validated['renewal_fee'] + $additionalTotal;

            $agreement->update($validated);

            DB::commit();

            return redirect()->route('super.agreements.show', $agreement->id)
                ->with('success', 'Agreement paper updated successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update agreement: ' . $e->getMessage());
        }
    }

    public function destroy(SaasAgreement $agreement)
    {
        $agreement->delete();
        return redirect()->route('super.agreements.index')
            ->with('success', 'Agreement deleted successfully.');
    }
}

