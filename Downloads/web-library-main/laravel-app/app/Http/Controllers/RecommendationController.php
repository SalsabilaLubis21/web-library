<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BorrowHistory;
use App\Models\BookPopularity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RecommendationController extends Controller
{
    public function recommend(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $userId = $request->user_id;

        // 1. USER BORROWING HISTORY → favorite category
        $favoriteCategory = BorrowHistory::where('user_id', $userId)
            ->join('books', 'borrow_history.book_id', '=', 'books.id')
            ->select('books.category_id')
            ->groupBy('books.category_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(1)
            ->value('category_id');

        // 2. NEW BOOKS
        $newBooks = Book::orderBy('publication_date', 'desc')->take(5)->get();

        // 3. POPULAR BOOKS
        $popularBooks = Book::orderByPopularity()->take(5)->get();

        // 4. CATEGORY-BASED RECOMMENDATION
        $categoryBooks = collect();
        if ($favoriteCategory) {
            $categoryBooks = Book::where('category_id', $favoriteCategory)->take(5)->get();
        }

        // 5. RECOMMEND FROM PYTHON API
        $faceRecommendations = [];
        try {
            $response = Http::post("http://localhost:5001/recommend_by_face", [
                "user_id" => $userId
            ]);
            if ($response->successful()) {
                $faceRecommendations = $response->json()["books"] ?? [];
            }
        } catch (\Exception $e) {
            // Log the error or handle it gracefully
        }

        return response()->json([
            "new_books" => $newBooks,
            "popular_books" => $popularBooks,
            "category_recommendations" => $categoryBooks,
            "face_recommendations" => $faceRecommendations
        ]);
    }
}