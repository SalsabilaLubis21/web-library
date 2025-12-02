<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'username',
        'email',
        'password',
        'face_embedding'
    ];

    protected $hidden = [
        'password',
        'face_embedding'
    ];
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->longText('face_embedding')->nullable();
    });
}

}

