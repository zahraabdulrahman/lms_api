<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentProfile extends Model
{
    use HasFactory;
    protected $fillable = ['price', 'start_date', 'end_date', 'details', 'instructor_name', 'role'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
