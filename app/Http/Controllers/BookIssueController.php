<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\LibraryMember;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookIssueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:issue books')->only(['create', 'store']);
        $this->middleware('can:return books')->only(['returnForm', 'processReturn']);
    }

    // ── Issue a book ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = BookIssue::with(['book', 'member.user', 'issuedByUser'])
            ->latest('issue_date');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('book', fn($b) => $b->where('title', 'like', "%{$search}%")
                                                   ->orWhere('isbn', 'like', "%{$search}%"))
                  ->orWhereHas('member.user', fn($u) => $u->where('first_name', 'like', "%{$search}%")
                                                           ->orWhere('last_name', 'like', "%{$search}%"))
                  ->orWhereHas('member', fn($m) => $m->where('card_number', 'like', "%{$search}%"));
            });
        }

        // Sync overdue status
        BookIssue::active()
            ->where('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);

        $issues   = $query->paginate(25)->withQueryString();
        $overdueCount = BookIssue::overdue()->count();

        return view('library.issues.index', compact('issues', 'overdueCount'));
    }

    public function create(Request $request)
    {
        $books   = Book::available()->with('category')->orderBy('title')->get();
        $members = LibraryMember::with('user')
                                ->where('status', 'active')
                                ->orderBy('card_number')
                                ->get();

        // Pre-select from QR / search
        $selectedBook   = $request->input('book_id')
            ? Book::find($request->input('book_id'))
            : null;
        $selectedMember = $request->input('member_id')
            ? LibraryMember::with('user')->find($request->input('member_id'))
            : null;

        return view('library.issues.create', compact('books', 'members', 'selectedBook', 'selectedMember'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id'      => 'required|integer|exists:books,id',
            'member_id'    => 'required|integer|exists:library_members,id',
            'issue_date'   => 'required|date',
            'fine_per_day' => 'required|numeric|min:0',
            'notes'        => 'nullable|string|max:500',
        ]);

        $book   = Book::findOrFail($data['book_id']);
        $member = LibraryMember::findOrFail($data['member_id']);

        // Validations
        if ($book->available_qty <= 0) {
            return back()->with('error', 'This book is currently out of stock.')->withInput();
        }
        if (!$member->is_active) {
            return back()->with('error', 'This member\'s library account is not active.')->withInput();
        }
        if ($member->remaining_quota <= 0) {
            return back()->with('error', "Member has reached their borrow limit ({$member->max_books} books).")->withInput();
        }
        // Check for already-issued same book to same member
        $alreadyIssued = BookIssue::where('book_id', $book->id)
                                  ->where('member_id', $member->id)
                                  ->active()
                                  ->exists();
        if ($alreadyIssued) {
            return back()->with('error', 'This member already has this book on loan.')->withInput();
        }

        $issueDate = Carbon::parse($data['issue_date']);
        $dueDate   = $issueDate->copy()->addDays($member->loan_days);

        BookIssue::create([
            'book_id'      => $book->id,
            'member_id'    => $member->id,
            'issued_by'    => auth()->id(),
            'issue_date'   => $issueDate->toDateString(),
            'due_date'     => $dueDate->toDateString(),
            'fine_per_day' => $data['fine_per_day'],
            'status'       => 'issued',
            'notes'        => $data['notes'] ?? null,
        ]);

        // Decrement stock
        $book->decrement('available_qty');

        return redirect()->route('library.issues.index')
                         ->with('status', "Book \"{$book->title}\" issued to {$member->user->first_name} {$member->user->last_name}.");
    }

    // ── Return a book ─────────────────────────────────────────────────────────

    public function returnForm(Request $request)
    {
        $issue = null;

        if ($issueId = $request->input('issue_id')) {
            $issue = BookIssue::with(['book', 'member.user'])->find($issueId);
        }

        // Active issues for the return lookup
        $activeIssues = BookIssue::with(['book', 'member.user'])
            ->active()
            ->latest('issue_date')
            ->get();

        return view('library.issues.return', compact('issue', 'activeIssues'));
    }

    public function processReturn(Request $request, int $id)
    {
        $issue = BookIssue::with(['book', 'member'])->findOrFail($id);

        if ($issue->status === 'returned') {
            return back()->with('error', 'This book has already been returned.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $issue->processReturn(auth()->id(), $request->input('notes'));

        $fine = $issue->fresh()->fine_amount;
        $msg  = "Book \"{$issue->book->title}\" returned successfully.";
        if ($fine > 0) {
            $msg .= " Fine of \${$fine} has been applied.";
        }

        return redirect()->route('library.issues.index')->with('status', $msg);
    }

    // ── Mark lost ─────────────────────────────────────────────────────────────

    public function markLost(Request $request, int $id)
    {
        $this->middleware('can:issue books');

        $issue = BookIssue::with(['book', 'member'])->findOrFail($id);

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $issue->update([
            'status'      => 'lost',
            'fine_status' => 'pending',
            'fine_amount' => $issue->book->price ?? 0,
            'notes'       => $request->input('notes', 'Book reported as lost.'),
        ]);

        // Book is lost — do NOT restore available_qty
        $issue->member->recalculateFine();

        return redirect()->route('library.issues.index')
                         ->with('status', 'Book marked as lost. Fine applied.');
    }

    // ── QR scan lookup ────────────────────────────────────────────────────────

    public function scanLookup(Request $request)
    {
        $barcode = $request->input('barcode');
        $card    = $request->input('card');

        $book   = $barcode ? Book::where('barcode', $barcode)->orWhere('isbn', $barcode)->first() : null;
        $member = $card    ? LibraryMember::with('user')->where('card_number', $card)->first() : null;

        return response()->json([
            'book'   => $book   ? ['id' => $book->id,   'title' => $book->title,   'available_qty' => $book->available_qty] : null,
            'member' => $member ? ['id' => $member->id, 'name'  => $member->user->first_name . ' ' . $member->user->last_name, 'quota' => $member->remaining_quota] : null,
        ]);
    }
}
