<?php

namespace App\Repositories;

use App\Models\Book;
use App\Interfaces\LibraryInterface;

class LibraryRepository implements LibraryInterface
{
    public function getAll()
    {
        return Book::with('category')->orderBy('title')->get();
    }

    public function findById($id)
    {
        return Book::with('category')->findOrFail($id);
    }

    public function create($request)
    {
        return Book::create($request->all());
    }

    public function update($request, $id)
    {
        $book = Book::findOrFail($id);
        $book->update($request->all());
        return $book;
    }

    public function delete($id)
    {
        $book = Book::findOrFail($id);
        $book->delete();
    }
}
