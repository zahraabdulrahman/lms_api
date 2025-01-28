<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = ['name', 'email', 'password', 'role'];

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }
}
