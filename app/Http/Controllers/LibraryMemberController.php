<?php

namespace App\Http\Controllers;

use App\Models\LibraryMember;
use App\Models\User;
use App\Models\BookIssue;
use Illuminate\Http\Request;

class LibraryMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage library members');
    }

    public function index(Request $request)
    {
        $query = LibraryMember::with('user')
            ->withCount(['issues', 'activeIssues', 'overdueIssues']);

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('card_number', 'like', "%{$search}%");
        }

        if ($type = $request->input('member_type')) {
            $query->where('member_type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $members = $query->latest()->paginate(20)->withQueryString();

        return view('library.members.index', compact('members'));
    }

    public function create()
    {
        // Users not already members
        $existingUserIds = LibraryMember::pluck('user_id');
        $users = User::whereNotIn('id', $existingUserIds)
                     ->orderBy('first_name')
                     ->get();

        return view('library.members.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'          => 'required|integer|exists:users,id|unique:library_members,user_id',
            'member_type'      => 'required|in:student,staff,teacher',
            'membership_start' => 'required|date',
            'membership_end'   => 'nullable|date|after:membership_start',
            'max_books'        => 'required|integer|min:1|max:20',
            'loan_days'        => 'required|integer|min:1|max:90',
        ]);

        LibraryMember::create($data);

        return redirect()->route('library.members.index')
                         ->with('status', 'Library member enrolled successfully.');
    }

    public function show(int $id)
    {
        $member = LibraryMember::with([
            'user',
            'issues.book',
            'issues.issuedByUser',
        ])->findOrFail($id);

        $activeIssues  = $member->issues->whereIn('status', ['issued', 'overdue']);
        $historyIssues = $member->issues->whereIn('status', ['returned', 'lost']);

        return view('library.members.show', compact('member', 'activeIssues', 'historyIssues'));
    }

    public function edit(int $id)
    {
        $member = LibraryMember::with('user')->findOrFail($id);
        return view('library.members.edit', compact('member'));
    }

    public function update(Request $request, int $id)
    {
        $member = LibraryMember::findOrFail($id);

        $data = $request->validate([
            'member_type'      => 'required|in:student,staff,teacher',
            'membership_start' => 'required|date',
            'membership_end'   => 'nullable|date|after:membership_start',
            'status'           => 'required|in:active,suspended,expired',
            'max_books'        => 'required|integer|min:1|max:20',
            'loan_days'        => 'required|integer|min:1|max:90',
        ]);

        $member->update($data);

        return redirect()->route('library.members.show', $id)
                         ->with('status', 'Member updated successfully.');
    }

    public function destroy(int $id)
    {
        $member = LibraryMember::withCount('activeIssues')->findOrFail($id);

        if ($member->active_issues_count > 0) {
            return redirect()->route('library.members.index')
                             ->with('error', 'Cannot remove member: they have active book loans.');
        }

        $member->delete();

        return redirect()->route('library.members.index')
                         ->with('status', 'Member removed successfully.');
    }

    /** Pay / waive fine for a member */
    public function settleFine(Request $request, int $id)
    {
        $member = LibraryMember::findOrFail($id);

        $data = $request->validate([
            'action' => 'required|in:paid,waived',
        ]);

        BookIssue::where('member_id', $id)
                 ->where('fine_status', 'pending')
                 ->update(['fine_status' => $data['action']]);

        $member->recalculateFine();

        return redirect()->back()
                         ->with('status', 'Fine marked as ' . $data['action'] . ' successfully.');
    }
}
