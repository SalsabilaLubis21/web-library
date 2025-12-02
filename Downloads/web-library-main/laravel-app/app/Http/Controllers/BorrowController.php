<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BorrowHistory;
use App\Models\BookPopularity;
use Illuminate\Http\Request;

class BorrowController extends Controller
{
    public function borrow(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id'
        ]);

        $book = Book::findOrFail($request->book_id);

        if ($book->stock < 1) {
            return response()->json(['message' => 'Book is out of stock'], 422);
        }

        $book->decrement('stock');

        BorrowHistory::create($request->all());

        BookPopularity::firstOrCreate(['book_id' => $request->book_id])
            ->increment('borrow_count');

        return response()->json(['message' => 'Book borrowed']);
    }

    public function returnBook(BorrowHistory $borrow)
    {
        if ($borrow->return_date) {
            return response()->json(['message' => 'Book already returned'], 422);
        }

        $borrow->update(['return_date' => now()]);

        $borrow->book->increment('stock');

        return response()->json(['message' => 'Book returned']);
    }
}