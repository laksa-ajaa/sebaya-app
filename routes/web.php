<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\GuruDashboardController;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($user->role === 'teacher') {
            return redirect()->route('guru.dashboard');
        }
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'authenticate'])
        ->middleware('throttle:5,1')
        ->name('authenticated');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'logout'])->name('logout');

    // Admin pages
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/statistik', [AdminDashboardController::class, 'statistik'])->name('statistik');
        Route::get('/laporan', [AdminDashboardController::class, 'laporan'])->name('laporan');
    });

    // Guru pages
    Route::prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
        Route::get('/screening', [GuruDashboardController::class, 'screening'])->name('screening');
        Route::get('/siswa', [GuruDashboardController::class, 'siswa'])->name('siswa');
        Route::get('/laporan', [GuruDashboardController::class, 'laporan'])->name('laporan');
    });
});
