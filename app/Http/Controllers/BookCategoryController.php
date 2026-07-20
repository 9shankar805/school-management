<?php

namespace App\Http\Controllers;

use App\Models\BookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:create books')->only(['store']);
        $this->middleware('can:edit books')->only(['update']);
        $this->middleware('can:delete books')->only(['destroy']);
    }

    public function index()
    {
        $categories = BookCategory::withCount('books')->orderBy('name')->get();
        return view('library.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:book_categories,name',
            'description' => 'nullable|string|max:500',
            'color'       => 'nullable|string|max:20',
        ]);

        $data['slug'] = Str::slug($data['name']);

        BookCategory::create($data);

        return redirect()->route('library.categories.index')
                         ->with('status', 'Category created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $category = BookCategory::findOrFail($id);

        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:book_categories,name,' . $id,
            'description' => 'nullable|string|max:500',
            'color'       => 'nullable|string|max:20',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $category->update($data);

        return redirect()->route('library.categories.index')
                         ->with('status', 'Category updated successfully.');
    }

    public function destroy(int $id)
    {
        $category = BookCategory::withCount('books')->findOrFail($id);

        if ($category->books_count > 0) {
            return redirect()->route('library.categories.index')
                             ->with('error', 'Cannot delete category: ' . $category->books_count . ' book(s) are assigned to it.');
        }

        $category->delete();

        return redirect()->route('library.categories.index')
                         ->with('status', 'Category deleted successfully.');
    }
}
