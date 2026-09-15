<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\CourseApplicationController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\CheckValidToken;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Welcome Moji!';
});

Route::get('/run-migrations-secret-123', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return response()->json([
            'message' => 'Migrations executed successfully!',
            'output'  => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);

Route::middleware(CheckValidToken::class)->group(function () {
    Route::post('logout', [UserController::class, 'logout']);

    Route::resource('courses', CourseController::class)->only(['store', 'show', 'index', 'update', 'destroy']);
    Route::resource('activities', ActivityController::class)->only(['store', 'show', 'index', 'update', 'destroy']);
    Route::resource('questions', QuestionController::class)->only(['store', 'show', 'index', 'update', 'destroy']);

    Route::resource('course-applications', CourseApplicationController::class)->only(['store', 'show', 'index', 'destroy']);
    Route::put('course-applications/{id}/status', [CourseApplicationController::class, 'updateStatus']);
});