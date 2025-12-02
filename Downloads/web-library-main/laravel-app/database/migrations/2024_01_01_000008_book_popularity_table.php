<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookPopularityTable extends Migration
{
    public function up()
    {
        Schema::create('book_popularity', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->primary();
            $table->integer('borrow_count')->default(0);

            $table->foreign('book_id')->references('id')->on('books');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('book_popularity');
    }
}