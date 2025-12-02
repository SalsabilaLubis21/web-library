<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BorrowHistory;
use App\Models\BookPopularity;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

// this is DSS system recommendation
class RecommendationService
{
    protected $cacheTtl = 60 * 5; // cache 5 menit (sesuaikan)

    /**
     * Get recommendations for a user.
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function recommendForUser(int $userId, int $limit = 20): array
    {
        // 1) ambil history singkat
        $history = $this->getUserHistory($userId);

        // jika tidak ada history => top populer
        if ($history->isEmpty()) {
            return $this->getTopPopular($limit);
        }

        // hitung frekuensi kategori user
        $categoryCounts = $this->getCategoryCounts($history);

        $recs = collect();

        // RULE 1: jika user pinjam > 5 buku di kategori tertentu -> rekomendasi new releases di kategori itu
        foreach ($categoryCounts as $categoryId => $count) {
            if ($count > 5) {
                $recs = $recs->merge($this->getNewReleasesByCategory($categoryId, 5));
            }
        }

        // RULE 2: jika user pinjam kategori > 3 -> rekomendasi top books di kategori terkait
        foreach ($categoryCounts as $categoryId => $count) {
            if ($count > 3) {
                $recs = $recs->merge($this->getRelatedByCategory($categoryId, 5));
            }
        }

        // RULE 4: selalu tambahkan newest 5 buku dari setiap kategori yang disukai
        foreach (array_keys($categoryCounts) as $categoryId) {
            $recs = $recs->merge($this->getNewestByCategory($categoryId, 5));
        }

        // RULE 3: jika rekomendasi masih kosong, tambahkan top popular
        if ($recs->isEmpty()) {
            $recs = collect($this->getTopPopular($limit));
        }

        // deduplicate & rank by combination (popularity + newest)
        $final = $this->rankAndDeduplicate($recs, $limit);

        return $final->values()->all();
    }

    /**
     * Get user's borrow history (cached)
     */
    protected function getUserHistory(int $userId): Collection
    {
        $key = "user:{$userId}:history";
        return Cache::remember($key, $this->cacheTtl, function () use ($userId) {
            return BorrowHistory::where('user_id', $userId)
                ->join('books', 'borrow_history.book_id', '=', 'books.id')
                ->select('books.*', 'borrow_history.borrow_date')
                ->orderByDesc('borrow_history.borrow_date')
                ->get();
        });
    }

    /**
     * Count categories from history: return [category_id => count]
     */
    protected function getCategoryCounts(Collection $history): array
    {
        $counts = [];
        foreach ($history as $row) {
            $cat = $row->category_id ?? null;
            if (!$cat) continue;
            if (!isset($counts[$cat])) $counts[$cat] = 0;
            $counts[$cat]++;
        }
        // sort desc
        arsort($counts);
        return $counts;
    }

    /**
     * Get new releases by category (order by publication_date or created_at)
     */
    protected function getNewReleasesByCategory(int $categoryId, int $limit = 5)
    {
        $key = "category:{$categoryId}:newreleases";
        return Cache::remember($key, $this->cacheTtl, function () use ($categoryId, $limit) {
            return Book::where('category_id', $categoryId)
                ->orderByDesc('publication_date')
                ->orderByDesc('id')
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get related books by category (could be same as top recent)
     */
    protected function getRelatedByCategory(int $categoryId, int $limit = 5)
    {
        $key = "category:{$categoryId}:related";
        return Cache::remember($key, $this->cacheTtl, function () use ($categoryId, $limit) {
            return Book::where('category_id', $categoryId)
                ->orderByDesc('id')
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get newest books by category
     */
    protected function getNewestByCategory(int $categoryId, int $limit = 5)
    {
        // alias of new releases for now
        return $this->getNewReleasesByCategory($categoryId, $limit);
    }

    /**
     * Top popular books across library
     */
    protected function getTopPopular(int $limit = 10)
    {
        $key = "books:top_popular:{$limit}";
        return Cache::remember($key, $this->cacheTtl, function () use ($limit) {
            // join with book_popularity
            return Book::select('books.*')
                ->join('book_popularity', 'books.id', '=', 'book_popularity.book_id')
                ->orderByDesc('book_popularity.borrow_count')
                ->take($limit)
                ->get();
        });
    }

    /**
     * Rank and deduplicate recommendations
     */
    protected function rankAndDeduplicate($collection, int $limit = 20)
    {
        // Convert to collection of models
        $col = collect($collection)->flatten(1);

        // Map book_id => score (popularity + recency)
        $scores = [];
        foreach ($col as $book) {
            $id = $book->id;
            $pop = BookPopularity::where('book_id', $id)->value('borrow_count') ?? 0;
            $daysSincePub = 0;
            if ($book->publication_date) {
                $daysSincePub = (int) \Carbon\Carbon::parse($book->publication_date)->diffInDays(now());
            } else {
                $daysSincePub = (int) \Carbon\Carbon::parse($book->created_at ?? now())->diffInDays(now());
            }
            // score: more popular + more recent => higher
            $score = ($pop * 2) + max(0, 365 - $daysSincePub) / 365; // simple heuristic
            if (!isset($scores[$id]) || $score > $scores[$id]['score']) {
                $scores[$id] = ['book' => $book, 'score' => $score];
            }
        }

        // Sort by score desc and limit
        $sorted = collect($scores)
            ->sortByDesc(fn($v) => $v['score'])
            ->take($limit)
            ->map(fn($v) => $v['book']);

        return $sorted;
    }
}
