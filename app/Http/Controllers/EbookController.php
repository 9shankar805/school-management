<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use App\Models\BookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EbookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view books')->only(['index', 'show', 'download']);
        $this->middleware('can:create books')->only(['create', 'store']);
        $this->middleware('can:edit books')->only(['edit', 'update', 'toggleActive']);
        $this->middleware('can:delete books')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Ebook::with('category')->active();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($cat = $request->input('category_id')) {
            $query->where('category_id', $cat);
        }

        if ($type = $request->input('file_type')) {
            $query->where('file_type', $type);
        }

        $ebooks     = $query->orderBy('title')->paginate(20)->withQueryString();
        $categories = BookCategory::orderBy('name')->get();

        return view('library.ebooks.index', compact('ebooks', 'categories'));
    }

    public function create()
    {
        $categories = BookCategory::orderBy('name')->get();
        return view('library.ebooks.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'author'           => 'nullable|string|max:255',
            'category_id'      => 'nullable|integer|exists:book_categories,id',
            'isbn'             => 'nullable|string|max:50',
            'description'      => 'nullable|string',
            'file'             => 'required|file|mimes:pdf,epub|max:51200', // 50 MB
            'cover'            => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'file_type'        => 'required|in:pdf,epub,mobi',
            'pages'            => 'nullable|integer|min:1',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'publisher'        => 'nullable|string|max:255',
            'access_level'     => 'required|in:public,members_only,restricted',
        ]);

        // Store the file
        $filePath = $request->file('file')->store('ebooks', 'local');
        $fileSize = $request->file('file')->getSize();

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('ebooks/covers', 'public');
        }

        Ebook::create(array_merge($data, [
            'file_path'  => $filePath,
            'cover_image'=> $coverPath,
            'file_size'  => $fileSize,
            'is_active'  => true,
        ]));

        return redirect()->route('library.ebooks.index')
                         ->with('status', 'E-book uploaded successfully.');
    }

    public function show(int $id)
    {
        $ebook = Ebook::with('category')->findOrFail($id);
        return view('library.ebooks.show', compact('ebook'));
    }

    public function edit(int $id)
    {
        $ebook      = Ebook::findOrFail($id);
        $categories = BookCategory::orderBy('name')->get();
        return view('library.ebooks.edit', compact('ebook', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $ebook = Ebook::findOrFail($id);

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'author'           => 'nullable|string|max:255',
            'category_id'      => 'nullable|integer|exists:book_categories,id',
            'isbn'             => 'nullable|string|max:50',
            'description'      => 'nullable|string',
            'cover'            => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'pages'            => 'nullable|integer|min:1',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'publisher'        => 'nullable|string|max:255',
            'access_level'     => 'required|in:public,members_only,restricted',
        ]);

        if ($request->hasFile('cover')) {
            // Delete old cover
            if ($ebook->cover_image) {
                Storage::disk('public')->delete($ebook->cover_image);
            }
            $data['cover_image'] = $request->file('cover')->store('ebooks/covers', 'public');
        }

        $ebook->update($data);

        return redirect()->route('library.ebooks.index')
                         ->with('status', 'E-book updated successfully.');
    }

    public function destroy(int $id)
    {
        $ebook = Ebook::findOrFail($id);

        Storage::disk('local')->delete($ebook->file_path);
        if ($ebook->cover_image) {
            Storage::disk('public')->delete($ebook->cover_image);
        }

        $ebook->delete();

        return redirect()->route('library.ebooks.index')
                         ->with('status', 'E-book deleted successfully.');
    }

    public function download(int $id)
    {
        $ebook = Ebook::findOrFail($id);

        // Access check
        if ($ebook->access_level === 'restricted' && !auth()->user()->hasAnyRole(['admin', 'librarian', 'super-admin'])) {
            abort(403, 'You do not have permission to download this e-book.');
        }

        if (!Storage::disk('local')->exists($ebook->file_path)) {
            abort(404, 'File not found.');
        }

        $ebook->incrementDownloads();

        $filename = Str::slug($ebook->title) . '.' . $ebook->file_type;

        return Storage::disk('local')->download($ebook->file_path, $filename);
    }

    public function toggleActive(int $id)
    {
        $ebook = Ebook::findOrFail($id);
        $ebook->update(['is_active' => !$ebook->is_active]);

        $msg = $ebook->is_active ? 'E-book published.' : 'E-book unpublished.';
        return redirect()->back()->with('status', $msg);
    }
}
