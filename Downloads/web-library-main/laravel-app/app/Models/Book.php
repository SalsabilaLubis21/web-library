<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'category_id',
        'stock',
        'image_url',
        'publication_date',
        'description'
    ];

    public function scopeOrderByPopularity($query)
    {
        return $query->leftJoin('book_popularity', 'books.id', '=', 'book_popularity.book_id')
            ->orderByDesc('book_popularity.borrow_count');
    }
}