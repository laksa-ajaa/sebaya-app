<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MoodCheckController;
use App\Http\Controllers\Api\JournalController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

// Mood check routes (protected)
Route::middleware('auth:api')->group(function () {
    Route::post('/mood-check', [MoodCheckController::class, 'checkMood']);
    Route::get('/mood-check/today', [MoodCheckController::class, 'getTodayMoodCheck']);
    Route::get('/mood-check/history', [MoodCheckController::class, 'getMoodHistory']);
});

// Journal routes (protected)
Route::middleware('auth:api')->group(function () {
    Route::post('/journal', [JournalController::class, 'store']);
    Route::get('/journal/today', [JournalController::class, 'getTodayJournal']);
    Route::get('/journal/history', [JournalController::class, 'getHistory']);
});

Route::get('/test', function () {
    return 'test';
});
