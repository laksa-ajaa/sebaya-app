<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\MoodCheckController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\TeacherRegistrationController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\ScreeningController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\ChatbotController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [GoogleAuthController::class, 'google']);
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
    Route::delete('/journal/{id}', [JournalController::class, 'destroy']);
});

// Screening routes (protected)
Route::middleware('auth:api')->group(function () {
    // Package listing - public within authenticated users
    Route::get('/screening/packages', [ScreeningController::class, 'listPackages']);
    Route::get('/screening/packages/{id}', [ScreeningController::class, 'showPackage']);

    // Session management
    Route::post('/screening/sessions', [ScreeningController::class, 'startSession']);
    Route::get('/screening/sessions', [ScreeningController::class, 'userSessions']);
    Route::get('/screening/sessions/{id}', [ScreeningController::class, 'getSessionQuestions']);

    // Answer handling
    Route::post('/screening/sessions/{id}/answers', [ScreeningController::class, 'saveAnswer']);
    Route::post('/screening/sessions/{id}/submit', [ScreeningController::class, 'submitSession']);

    // Results
    Route::get('/screening/sessions/{id}/result', [ScreeningController::class, 'getResult']);

    // Schedules (notifications for students)
    Route::get('/schedules', [ScheduleController::class, 'index']);
});

// Chatbot routes (protected)
Route::middleware('auth:api')->group(function () {
    Route::get('/chat', [ChatbotController::class, 'index']);
    Route::post('/chat', [ChatbotController::class, 'sendMessage']);
});
