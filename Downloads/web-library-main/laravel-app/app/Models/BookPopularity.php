<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookPopularity extends Model
{
    protected $table = 'book_popularity';

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}