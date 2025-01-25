<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AuthController;

Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
    //logout route is protected, as only logged in users can use it
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});

Route::prefix('courses/{course}')->group(function () {
    Route::get('comments', [CommentController::class, 'index'])->name('courses.comments.index');
    Route::post('comments', [CommentController::class, 'store'])->name('courses.comments.store');
    Route::put('comments/{comment}', [CommentController::class, 'update'])->name('courses.comments.update');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('courses.comments.destroy');
});

Route::apiResource('student', UserController::class);
Route::apiResource('courses', CourseController::class);
Route::apiResource('registrations', RegistrationController::class)->middleware('auth:sanctum');