<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);
    // Comments
    Route::prefix('courses/{course}')->group(function () {
        Route::get('comments', [CommentController::class, 'index']);
        Route::post('comments', [CommentController::class, 'store'])->middleware('can:create,App\Models\Comment');
        Route::put('comments/{comment}', [CommentController::class, 'update'])->middleware('can:update,comment');
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->middleware('can:delete,comment');
    });

    // Registrations
    Route::get('/registrations', [RegistrationController::class, 'index'])->middleware('can:viewAny,App\Models\Registration');
    Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->middleware('can:view,registration');
    Route::post('/courses/{course}/registrations', [RegistrationController::class, 'store'])->middleware('can:create,App\Models\Registration,course');
    Route::put('/registrations/{registration}', [RegistrationController::class, 'update'])->middleware('can:update,registration');
    Route::delete('/registrations/{registration}', [RegistrationController::class, 'destroy'])->middleware('can:delete,registration');

    // Courses
    Route::post('/courses', [CourseController::class, 'store'])->middleware('can:create,App\Models\Course');
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);
    Route::put('/courses/{course}', [CourseController::class, 'update'])->middleware('can:update,course');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->middleware('can:delete,course');

    // Students
    Route::post('/students', [UserController::class, 'store']);
    Route::delete('/students/{user}', [UserController::class, 'destroy'])->middleware('can:delete,user');
    Route::get('/students/{user}', [UserController::class, 'show'])->middleware('can:view,user');
    Route::put('/students/{user}', [UserController::class, 'update'])->middleware('can:update,user');
    Route::delete('/students/{user}', [UserController::class, 'destroy'])->middleware('can:delete,user');

    // Email
    Route::get('/send_email', [EmailController::class, 'sendUserEmail']);
});
