<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Ebook;
use App\Models\LibraryMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    /**
     * GET /student-app/library/issued
     * Books currently issued to the student.
     */
    public function issued(Request $request): JsonResponse
    {
        $member = LibraryMember::where('user_id', $request->user()->id)->first();

        if (! $member) {
            return response()->json([
                'status' => 'success',
                'data'   => [],
                'message' => 'No library membership found.',
            ]);
        }

        $issues = BookIssue::with('book')
            ->where('member_id', $member->id)
            ->whereNull('return_date')
            ->orderByDesc('issue_date')
            ->get()
            ->map(fn($issue) => [
                'issue_id'    => $issue->id,
                'book_title'  => $issue->book?->title,
                'author'      => $issue->book?->author,
                'isbn'        => $issue->book?->isbn,
                'issued_on'   => $issue->issue_date,
                'due_date'    => $issue->due_date,
                'is_overdue'  => $issue->due_date && now()->gt($issue->due_date),
                'fine_amount' => $issue->fine_amount ?? 0,  // actual column: fine_amount
                'status'      => $issue->status,
            ]);

        return response()->json([
            'status' => 'success',
            'data'   => $issues,
        ]);
    }

    /**
     * GET /student-app/library/history
     * Full book borrowing history for the student.
     */
    public function history(Request $request): JsonResponse
    {
        $member = LibraryMember::where('user_id', $request->user()->id)->first();

        if (! $member) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $history = BookIssue::with('book')
            ->where('member_id', $member->id)
            ->orderByDesc('issue_date')
            ->get()
            ->map(fn($issue) => [
                'issue_id'     => $issue->id,
                'book_title'   => $issue->book?->title,
                'author'       => $issue->book?->author,
                'issued_on'    => $issue->issue_date,
                'due_date'     => $issue->due_date,
                'returned_on'  => $issue->return_date,
                'status'       => $issue->status ?? ($issue->return_date ? 'returned' : 'issued'),
                'fine_amount'  => $issue->fine_amount ?? 0,   // actual column
            ]);

        return response()->json([
            'status' => 'success',
            'data'   => $history,
        ]);
    }

    /**
     * GET /student-app/library/catalog
     * Browse available books in the school library.
     */
    public function catalog(Request $request): JsonResponse
    {
        $query = Book::with('category')
            ->where('school_id', $request->user()->school_id);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        $books = $query->orderBy('title')
            ->paginate(20)
            ->through(fn($b) => [
                'id'             => $b->id,
                'title'          => $b->title,
                'author'         => $b->author,
                'isbn'           => $b->isbn,
                'category'       => $b->category?->name,
                'available_qty'  => $b->available_qty,   // actual column: available_qty
                'cover_image'    => $b->cover_image,
            ]);

        return response()->json([
            'status' => 'success',
            'data'   => $books->items(),
            'meta'   => [
                'total'        => $books->total(),
                'current_page' => $books->currentPage(),
                'last_page'    => $books->lastPage(),
            ],
        ]);
    }

    /**
     * GET /student-app/library/ebooks
     * Digital ebooks available for this school.
     */
    public function ebooks(Request $request): JsonResponse
    {
        $ebooks = Ebook::where('school_id', $request->user()->school_id)
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'title', 'author', 'description', 'cover_image', 'file_path',
                   'file_type', 'file_size', 'pages', 'publication_year', 'publisher']);

        return response()->json([
            'status' => 'success',
            'data'   => $ebooks->map(fn($e) => [
                'id'               => $e->id,
                'title'            => $e->title,
                'author'           => $e->author,
                'description'      => $e->description,
                'cover_image'      => $e->cover_image,
                'file_url'         => $e->file_path ? \Storage::url($e->file_path) : null,
                'file_type'        => $e->file_type,
                'pages'            => $e->pages,
                'publication_year' => $e->publication_year,
            ]),
        ]);
    }

    /**
     * GET /student-app/library/ebooks/{id}
     * Single ebook detail with download URL.
     */
    public function ebookShow(Request $request, int $id): JsonResponse
    {
        $ebook = Ebook::where('school_id', $request->user()->school_id)
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'               => $ebook->id,
                'title'            => $ebook->title,
                'author'           => $ebook->author,
                'description'      => $ebook->description,
                'cover_image'      => $ebook->cover_image,
                'file_url'         => $ebook->file_path ? \Storage::url($ebook->file_path) : null,
                'file_type'        => $ebook->file_type,
                'pages'            => $ebook->pages,
                'publication_year' => $ebook->publication_year,
                'publisher'        => $ebook->publisher,
                'download_count'   => $ebook->download_count,
            ],
        ]);
    }
}
