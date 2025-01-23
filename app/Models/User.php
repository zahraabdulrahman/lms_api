<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    protected $fillable = ['name', 'price', 'start_date', 'end_date', 'details', 'instructor_name'];

    public function registrations(){
        return $this->hasMany(Registration::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }
}

