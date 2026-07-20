<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('view inventory');

        $query = Supplier::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%")
                   ->orWhere('contact_person', 'like', "%{$q}%");
            });
        }

        $suppliers = $query->paginate(25)->withQueryString();

        return view('inventory.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $this->authorize('manage inventory');

        return view('inventory.suppliers.create');
    }

    public function store(Request $request)
    {
        $this->authorize('manage inventory');

        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'contact_person' => 'nullable|string|max:150',
            'email'          => 'nullable|email|max:150',
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:500',
            'tax_number'     => 'nullable|string|max:100',
            'bank_account'   => 'nullable|string|max:100',
            'status'         => 'required|in:active,inactive',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $data['created_by'] = auth()->id();
        Supplier::create($data);

        return redirect()->route('inventory.suppliers.index')
            ->with('status', 'Supplier added successfully.');
    }

    public function edit(int $id)
    {
        $this->authorize('manage inventory');
        $supplier = Supplier::findOrFail($id);

        return view('inventory.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('manage inventory');
        $supplier = Supplier::findOrFail($id);

        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'contact_person' => 'nullable|string|max:150',
            'email'          => 'nullable|email|max:150',
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:500',
            'tax_number'     => 'nullable|string|max:100',
            'bank_account'   => 'nullable|string|max:100',
            'status'         => 'required|in:active,inactive',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $supplier->update($data);

        return redirect()->route('inventory.suppliers.index')
            ->with('status', 'Supplier updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('manage inventory');
        Supplier::findOrFail($id)->delete();

        return back()->with('status', 'Supplier deleted.');
    }
}
