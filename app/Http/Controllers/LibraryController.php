<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\LibraryInterface;

class LibraryController extends Controller
{
    protected $libraryRepository;

    public function __construct(LibraryInterface $libraryRepository) {
        // $this->middleware(['can:manage library']);
        $this->libraryRepository = $libraryRepository;
    }

    public function index() {
        $books = $this->libraryRepository->getAll();
        return view('library.index', compact('books'));
    }

    public function create() {
        return view('library.create');
    }

    public function store(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:0',
        ]);

        $this->libraryRepository->create($request);
        return redirect()->route('library.index')->with('status', 'Book created successfully.');
    }

    public function edit($id) {
        $book = $this->libraryRepository->findById($id);
        return view('library.edit', compact('book'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'title' => 'required|string|max:255',
            'qty' => 'required|integer|min:0',
        ]);

        $this->libraryRepository->update($request, $id);
        return redirect()->route('library.index')->with('status', 'Book updated successfully.');
    }

    public function destroy($id) {
        $this->libraryRepository->delete($id);
        return redirect()->route('library.index')->with('status', 'Book deleted successfully.');
    }
}
