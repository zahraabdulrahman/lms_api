<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AuthController;

/* //register and login
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']); */


Route::prefix('courses/{course}')->group(function () {
    Route::get('comments', [CommentController::class, 'index'])->name('courses.comments.index');
    Route::post('comments', [CommentController::class, 'store'])->name('courses.comments.store');
    Route::put('comments/{comment}', [CommentController::class, 'update'])->name('courses.comments.update');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('courses.comments.destroy');
});

Route::apiResource('student', UserController::class);
Route::apiResource('courses', CourseController::class);
Route::apiResource('registrations', RegistrationController::class);