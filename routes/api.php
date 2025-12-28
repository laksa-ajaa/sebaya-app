<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MoodCheckController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\TeacherRegistrationController;
use App\Http\Controllers\Api\ClassController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

// Teacher registration
Route::post('/teacher/register', [TeacherRegistrationController::class, 'register']);
Route::post('/teacher/resend-otp', [TeacherRegistrationController::class, 'resendOtp']);
Route::post('/teacher/verify-otp', [TeacherRegistrationController::class, 'verifyOtp']);

// User routes (protected)
Route::middleware('auth:api')->group(function () {
    Route::get('/user-data', [AuthController::class, 'getUserData']);
    Route::post('/classes/join', [ClassController::class, 'joinByCode']);
});

// Mood check routes (protected)
Route::middleware('auth:api')->group(function () {
    Route::post('/mood-check', [MoodCheckController::class, 'checkMood']);
    Route::get('/mood-check/today', [MoodCheckController::class, 'getTodayMoodCheck']);
    Route::get('/mood-check/history', [MoodCheckController::class, 'getMoodHistory']);
    Route::delete('/mood-check/reset', [MoodCheckController::class, 'resetMoodCheck']);
});

// Journal routes (protected)
Route::middleware('auth:api')->group(function () {
    Route::post('/journal', [JournalController::class, 'store']);
    Route::get('/journal', [JournalController::class, 'index']);
    Route::get('/journal/{id}', [JournalController::class, 'show']);
    Route::put('/journal/{id}', [JournalController::class, 'update']);
    Route::patch('/journal/{id}', [JournalController::class, 'update']);
});

Route::get('/test', function () {
    return 'test';
});
