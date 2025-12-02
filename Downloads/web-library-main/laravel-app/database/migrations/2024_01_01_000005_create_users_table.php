<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');

            // Store face embedding
            $table->longText('face_embedding')->nullable();

            $table->timestamps(); // created_at + updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
