<?php

namespace App\Http\Controllers;

use App\Models\StaffDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view staff');
    }

    public function index(Request $request)
    {
        $query = StaffDocument::with('user');

        if ($search = $request->input('search')) {
            $query->whereHas('user', fn($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%"));
        }
        if ($type = $request->input('document_type')) {
            $query->where('document_type', $type);
        }

        $documents = $query->latest()->paginate(25)->withQueryString();

        // Expiry alerts
        $expiryAlerts = StaffDocument::with('user')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->get();

        return view('staff.documents.index', compact('documents', 'expiryAlerts'));
    }

    public function store(Request $request)
    {
        $this->authorize('create staff');
        $data = $request->validate([
            'user_id'       => 'required|integer|exists:users,id',
            'title'         => 'required|string|max:200',
            'document_type' => 'required|string|max:100',
            'file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'expiry_date'   => 'nullable|date',
            'notes'         => 'nullable|string|max:500',
        ]);

        $file              = $request->file('file');
        $data['file_path'] = $file->store('staff/documents', 'public');
        $data['file_name'] = $file->getClientOriginalName();

        StaffDocument::create($data);
        return back()->with('status', 'Document uploaded.');
    }

    public function verify(int $id)
    {
        $this->authorize('create staff');
        StaffDocument::findOrFail($id)->update([
            'is_verified' => true,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);
        return back()->with('status', 'Document verified.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create staff');
        $doc = StaffDocument::findOrFail($id);
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();
        return back()->with('status', 'Document deleted.');
    }
}
