<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        return Book::with('category')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required',
            'author'      => 'required',
            'category_id' => 'required|exists:categories,id',
            'stock'       => 'required|integer'
        ]);

        $book = Book::create($request->all());

        return response()->json($book, 201);
    }

    public function show(Book $book)
    {
        return $book->load('category');
    }

    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title'       => 'sometimes|required',
            'author'      => 'sometimes|required',
            'category_id' => 'sometimes|required|exists:categories,id',
            'stock'       => 'sometimes|required|integer'
        ]);

        $book->update($request->all());

        return response()->json($book);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(null, 204);
    }
}