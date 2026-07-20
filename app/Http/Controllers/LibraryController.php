<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookIssue;
use App\Models\LibraryMember;
use App\Models\Ebook;
use App\Interfaces\LibraryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LibraryReportExport;

class LibraryController extends Controller
{
    protected LibraryInterface $libraryRepository;

    public function __construct(LibraryInterface $libraryRepository)
    {
        $this->middleware('auth');
        $this->libraryRepository = $libraryRepository;
    }

    // ── Book Catalog ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('view books');

        $query = Book::with('category');

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        if ($cat = $request->input('category_id')) {
            $query->where('category_id', $cat);
        }

        if ($availability = $request->input('availability')) {
            match ($availability) {
                'available'    => $query->where('available_qty', '>', 0),
                'unavailable'  => $query->where('available_qty', '<=', 0),
                default        => null,
            };
        }

        $books      = $query->orderBy('title')->paginate(20)->withQueryString();
        $categories = BookCategory::orderBy('name')->get();
        $totalBooks = Book::count();
        $available  = Book::where('available_qty', '>', 0)->count();
        $issued     = BookIssue::active()->count();
        $overdue    = BookIssue::overdue()->count();

        return view('library.index', compact(
            'books', 'categories', 'totalBooks', 'available', 'issued', 'overdue'
        ));
    }

    public function create()
    {
        $this->authorize('create books');
        $categories = BookCategory::orderBy('name')->get();
        return view('library.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create books');

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'author'           => 'nullable|string|max:255',
            'publisher'        => 'nullable|string|max:255',
            'edition'          => 'nullable|string|max:50',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'language'         => 'nullable|string|max:50',
            'isbn'             => 'nullable|string|max:50|unique:books,isbn',
            'barcode'          => 'nullable|string|max:100|unique:books,barcode',
            'category_id'      => 'nullable|integer|exists:book_categories,id',
            'qty'              => 'required|integer|min:0',
            'available_qty'    => 'nullable|integer|min:0',
            'price'            => 'nullable|numeric|min:0',
            'shelf_location'   => 'nullable|string|max:100',
            'description'      => 'nullable|string',
            'cover'            => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        // Default available_qty = qty when not supplied
        $data['available_qty'] = $data['available_qty'] ?? $data['qty'];

        if ($request->hasFile('cover')) {
            $data['cover_image'] = $request->file('cover')->store('books/covers', 'public');
        }
        unset($data['cover']);

        Book::create($data);

        return redirect()->route('library.index')
                         ->with('status', 'Book added to catalog successfully.');
    }

    public function edit(int $id)
    {
        $this->authorize('edit books');
        $book       = Book::findOrFail($id);
        $categories = BookCategory::orderBy('name')->get();
        return view('library.edit', compact('book', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('edit books');
        $book = Book::findOrFail($id);

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'author'           => 'nullable|string|max:255',
            'publisher'        => 'nullable|string|max:255',
            'edition'          => 'nullable|string|max:50',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'language'         => 'nullable|string|max:50',
            'isbn'             => 'nullable|string|max:50|unique:books,isbn,' . $id,
            'barcode'          => 'nullable|string|max:100|unique:books,barcode,' . $id,
            'category_id'      => 'nullable|integer|exists:book_categories,id',
            'qty'              => 'required|integer|min:0',
            'available_qty'    => 'required|integer|min:0',
            'price'            => 'nullable|numeric|min:0',
            'shelf_location'   => 'nullable|string|max:100',
            'description'      => 'nullable|string',
            'cover'            => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        if ($request->hasFile('cover')) {
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $data['cover_image'] = $request->file('cover')->store('books/covers', 'public');
        }
        unset($data['cover']);

        $book->update($data);

        return redirect()->route('library.index')
                         ->with('status', 'Book updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->authorize('delete books');
        $book = Book::withCount('activeIssues')->findOrFail($id);

        if ($book->active_issues_count > 0) {
            return redirect()->route('library.index')
                             ->with('error', 'Cannot delete: this book has active loans.');
        }

        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();

        return redirect()->route('library.index')
                         ->with('status', 'Book deleted from catalog.');
    }

    public function show(int $id)
    {
        $this->authorize('view books');
        $book = Book::with([
            'category',
            'issues.member.user',
            'issues.issuedByUser',
        ])->withCount('activeIssues')->findOrFail($id);

        $recentIssues = $book->issues()->with('member.user')
                             ->latest('issue_date')
                             ->take(10)
                             ->get();

        return view('library.show', compact('book', 'recentIssues'));
    }

    // ── Analytics ─────────────────────────────────────────────────────────────

    public function analytics()
    {
        $this->authorize('view library reports');

        $totalBooks   = Book::count();
        $totalCopies  = Book::sum('qty');
        $available    = Book::sum('available_qty');
        $issued       = BookIssue::active()->count();
        $overdue      = BookIssue::overdue()->count();
        $totalMembers = LibraryMember::count();
        $totalEbooks  = Ebook::count();
        $pendingFines = BookIssue::pendingFine()->sum('fine_amount');

        // Most issued books (top 10)
        $mostIssued = Book::withCount('issues')
                          ->orderByDesc('issues_count')
                          ->take(10)
                          ->get();

        // Category distribution
        $categoryStats = BookCategory::withCount('books')
                                     ->orderByDesc('books_count')
                                     ->get();

        // Monthly issues — last 12 months
        $monthlyIssues = BookIssue::selectRaw("DATE_FORMAT(issue_date, '%Y-%m') as month, COUNT(*) as total")
                                  ->where('issue_date', '>=', Carbon::now()->subMonths(12))
                                  ->groupBy('month')
                                  ->orderBy('month')
                                  ->pluck('total', 'month');

        // Overdue list
        $overdueList = BookIssue::with(['book', 'member.user'])
                                ->overdue()
                                ->orderBy('due_date')
                                ->take(20)
                                ->get();

        // Members with fines
        $membersWithFines = LibraryMember::with('user')
                                         ->where('outstanding_fine', '>', 0)
                                         ->orderByDesc('outstanding_fine')
                                         ->take(10)
                                         ->get();

        return view('library.analytics', compact(
            'totalBooks', 'totalCopies', 'available', 'issued', 'overdue',
            'totalMembers', 'totalEbooks', 'pendingFines',
            'mostIssued', 'categoryStats', 'monthlyIssues',
            'overdueList', 'membersWithFines'
        ));
    }

    // ── Reports ───────────────────────────────────────────────────────────────

    public function reportForm()
    {
        $this->authorize('view library reports');
        return view('library.reports');
    }

    public function reportExport(Request $request)
    {
        $this->authorize('view library reports');

        $data = $request->validate([
            'report_type' => 'required|in:catalog,issued,overdue,fines,members',
            'format'      => 'required|in:pdf,excel',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ]);

        $reportType = $data['report_type'];
        $format     = $data['format'];
        $dateFrom   = $data['date_from'] ?? null;
        $dateTo     = $data['date_to']   ?? null;

        // Build dataset
        $records = match ($reportType) {
            'catalog' => Book::with('category')->orderBy('title')->get(),
            'issued'  => BookIssue::with(['book', 'member.user'])
                            ->active()
                            ->when($dateFrom, fn($q) => $q->where('issue_date', '>=', $dateFrom))
                            ->when($dateTo,   fn($q) => $q->where('issue_date', '<=', $dateTo))
                            ->orderBy('due_date')
                            ->get(),
            'overdue' => BookIssue::with(['book', 'member.user'])
                            ->overdue()
                            ->orderBy('due_date')
                            ->get(),
            'fines'   => BookIssue::with(['book', 'member.user'])
                            ->pendingFine()
                            ->orderByDesc('fine_amount')
                            ->get(),
            'members' => LibraryMember::with('user')
                            ->withCount('activeIssues')
                            ->orderBy('card_number')
                            ->get(),
        };

        $title = ucfirst(str_replace('_', ' ', $reportType)) . ' Report';

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('library.reports-pdf', compact('records', 'reportType', 'title', 'dateFrom', 'dateTo'))
                      ->setPaper('a4', 'landscape');
            return $pdf->download("library-{$reportType}-report.pdf");
        }

        // Excel
        return Excel::download(new LibraryReportExport($records, $reportType), "library-{$reportType}-report.xlsx");
    }
}
