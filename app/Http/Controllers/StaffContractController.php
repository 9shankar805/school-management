<?php

namespace App\Http\Controllers;

use App\Models\StaffContract;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffContractController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view staff');
    }

    public function index(Request $request)
    {
        $query = StaffContract::with('user');

        if ($search = $request->input('search')) {
            $query->whereHas('user', fn($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%"));
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Auto-expire contracts past end date
        StaffContract::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<', today())
            ->update(['status' => 'expired']);

        $contracts = $query->latest()->paginate(25)->withQueryString();

        // Expiry alerts (next 30 days)
        $expiryAlerts = StaffContract::with('user')
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays(30))
            ->orderBy('end_date')->get();

        return view('staff.contracts.index', compact('contracts', 'expiryAlerts'));
    }

    public function store(Request $request)
    {
        $this->authorize('create staff');
        $data = $request->validate([
            'user_id'       => 'required|integer|exists:users,id',
            'contract_type' => 'required|string|max:100',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after:start_date',
            'salary'        => 'required|numeric|min:0',
            'allowances'    => 'nullable|numeric|min:0',
            'position'      => 'nullable|string|max:150',
            'department'    => 'nullable|string|max:150',
            'notes'         => 'nullable|string',
            'document'      => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('staff/contracts', 'public');
        }

        $data['status'] = 'active';
        StaffContract::create($data);
        return back()->with('status', 'Contract created.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create staff');
        $contract = StaffContract::findOrFail($id);
        $data = $request->validate([
            'contract_type' => 'required|string|max:100',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after:start_date',
            'salary'        => 'required|numeric|min:0',
            'allowances'    => 'nullable|numeric|min:0',
            'position'      => 'nullable|string|max:150',
            'department'    => 'nullable|string|max:150',
            'status'        => 'required|in:active,expired,terminated',
            'notes'         => 'nullable|string',
        ]);
        $contract->update($data);
        return back()->with('status', 'Contract updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create staff');
        StaffContract::findOrFail($id)->delete();
        return back()->with('status', 'Contract deleted.');
    }
}
