<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailController;

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    // Comments (all actions require authentication and student role)
    Route::prefix('courses/{course}')->group(function () {
        Route::get('comments', [CommentController::class, 'index']); // everyone can view comments
        Route::post('comments', [CommentController::class, 'store'])->middleware('can:create,App\Models\Comment');
        Route::put('comments/{comment}', [CommentController::class, 'update'])->middleware('can:update,comment');
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->middleware('can:delete,comment');
    });

    // Registrations
    Route::apiResource('registrations', RegistrationController::class)->only(['index', 'show', 'store', 'update']);
    Route::get('/registrations', [RegistrationController::class, 'index'])->middleware('can:viewAny,App\Models\Registration');
    Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->middleware('can:view,registration');
    Route::post('/courses/{course}/registrations', [RegistrationController::class, 'store'])->middleware('can:create,App\Models\Registration,course');
    Route::put('/registrations/{registration}', [RegistrationController::class, 'update'])->middleware('can:update,registration');

    // Courses
    Route::apiResource('courses', CourseController::class)->except(['index', 'show']);
    Route::post('/courses', [CourseController::class, 'store'])->middleware('can:create,App\Models\Course');
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);

    // Students (delete action only)
    Route::delete('/students/{user}', [UserController::class, 'destroy'])->middleware('can:delete,user');

    // Email
    Route::get('/send_email', [EmailController::class, 'sendUserEmail']);
});
