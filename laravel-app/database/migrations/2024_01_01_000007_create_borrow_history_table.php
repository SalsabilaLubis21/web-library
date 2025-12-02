<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBorrowHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('borrow_history', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('book_id');

            $table->timestamp('borrow_date')->useCurrent();
            $table->timestamp('return_date')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('book_id')->references('id')->on('books');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('borrow_history');
    }
}