<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SaasServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = SaasService::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_np', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $services = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'     => SaasService::count(),
            'hardware'  => SaasService::where('category', 'hardware')->count(),
            'software'  => SaasService::where('category', 'software')->count(),
            'services'  => SaasService::whereIn('category', ['service', 'setup'])->count(),
        ];

        $categories = SaasService::categories();
        $billingBases = SaasService::billingBases();

        return view('super-admin.services.index', compact('services', 'stats', 'categories', 'billingBases'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'name_np'        => 'nullable|string|max:255',
            'code'           => 'nullable|string|max:100|unique:saas_services,code',
            'category'       => 'required|string|in:hardware,software,service,setup,package,other',
            'default_price'  => 'required|numeric|min:0',
            'billing_basis'  => 'required|string|in:one_time,annual,monthly,per_unit',
            'unit_name'      => 'nullable|string|max:100',
            'description'    => 'nullable|string',
            'specifications' => 'nullable|string',
            'is_featured'    => 'nullable|boolean',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = 'SRV-' . strtoupper(Str::slug(substr($validated['name'], 0, 20))) . '-' . rand(100, 999);
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['created_by'] = auth()->id();

        SaasService::create($validated);

        return redirect()->route('super.services.index')
            ->with('success', 'Service or Hardware item added successfully!');
    }

    public function update(Request $request, SaasService $service)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'name_np'        => 'nullable|string|max:255',
            'code'           => 'required|string|max:100|unique:saas_services,code,' . $service->id,
            'category'       => 'required|string|in:hardware,software,service,setup,package,other',
            'default_price'  => 'required|numeric|min:0',
            'billing_basis'  => 'required|string|in:one_time,annual,monthly,per_unit',
            'unit_name'      => 'nullable|string|max:100',
            'description'    => 'nullable|string',
            'specifications' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        $service->update($validated);

        return redirect()->route('super.services.index')
            ->with('success', 'Service updated successfully!');
    }

    public function destroy(SaasService $service)
    {
        $service->delete();
        return redirect()->route('super.services.index')
            ->with('success', 'Service deleted successfully.');
    }

    public function toggleStatus(SaasService $service)
    {
        $service->update(['is_active' => !$service->is_active]);
        return response()->json([
            'success'   => true,
            'is_active' => $service->is_active,
            'message'   => 'Status updated successfully.',
        ]);
    }

    public function apiList()
    {
        $services = SaasService::where('is_active', true)->orderBy('name')->get();
        return response()->json($services);
    }
}

