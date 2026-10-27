<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $date   = $request->input('date', today()->toDateString());
        $search = $request->input('search');

        $query = Visitor::with('creator')->latest('check_in_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($date) {
            $query->whereDate('check_in_at', $date);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('badge_number', 'like', "%{$search}%")
                  ->orWhere('person_to_meet', 'like', "%{$search}%")
                  ->orWhere('organization', 'like', "%{$search}%");
            });
        }

        $visitors = $query->paginate(20)->withQueryString();

        $stats = [
            'today_total' => Visitor::whereDate('check_in_at', today())->count(),
            'active_now'  => Visitor::where('status', 'active')->count(),
            'checked_out' => Visitor::whereDate('check_in_at', today())->where('status', 'checked_out')->count(),
        ];

        return view('visitors.index', compact('visitors', 'stats', 'status', 'date', 'search'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'required|string|max:50',
            'email'          => 'nullable|email|max:255',
            'id_type'        => 'nullable|string|in:citizenship,national_id,license,passport,other',
            'id_number'      => 'nullable|string|max:100',
            'organization'   => 'nullable|string|max:255',
            'purpose'        => 'required|string|max:255',
            'person_to_meet' => 'required|string|max:255',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $data['badge_number'] = Visitor::generateBadgeNumber();
        $data['check_in_at']  = now();
        $data['status']       = 'active';
        $data['created_by']   = auth()->id();
        $data['school_id']    = auth()->user()->school_id ?? 1;

        $visitor = Visitor::create($data);

        return redirect()->route('visitors.index')->with('status', "Visitor {$visitor->name} checked in successfully. Badge: {$visitor->badge_number}");
    }

    public function checkOut(Request $request, int $id)
    {
        $visitor = Visitor::findOrFail($id);
        $visitor->update([
            'check_out_at' => now(),
            'status'       => 'checked_out',
        ]);

        return back()->with('status', "Visitor {$visitor->name} checked out at " . now()->format('h:i A') . ".");
    }

    public function printBadge(int $id)
    {
        $visitor = Visitor::findOrFail($id);
        $school  = auth()->user()->school ?? \App\Models\School::first();

        $pdf = Pdf::loadView('visitors.pdf.badge', compact('visitor', 'school'))
            ->setPaper([0, 0, 240, 360], 'portrait'); // Standard Visitor ID badge card size

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="visitor-pass-'.$visitor->badge_number.'.pdf"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    public function destroy(int $id)
    {
        $visitor = Visitor::findOrFail($id);
        $visitor->delete();

        return back()->with('status', 'Visitor record removed.');
    }
}
