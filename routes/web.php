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
        Route::post('/user/{id}/reset-password', [AdminDashboardController::class, 'resetPassword'])->name('user.reset-password');
        Route::delete('/user/{id}', [AdminDashboardController::class, 'deleteUser'])->name('user.delete');
        Route::get('/laporan', [AdminDashboardController::class, 'laporan'])->name('laporan');

        // Sekolah
        Route::get('/sekolah', [AdminDashboardController::class, 'schools'])->name('schools');
        Route::post('/sekolah', [AdminDashboardController::class, 'sekolahStore'])->name('sekolah.store');
        Route::put('/sekolah/{id}', [AdminDashboardController::class, 'sekolahUpdate'])->name('sekolah.update');
        Route::delete('/sekolah/{id}', [AdminDashboardController::class, 'sekolahDelete'])->name('sekolah.delete');

        // Kelas (nested di bawah sekolah)
        Route::get('/sekolah/{school_id}/kelas', [AdminDashboardController::class, 'kelasIndex'])->name('sekolah.kelas.index');
        Route::post('/sekolah/{school_id}/kelas', [AdminDashboardController::class, 'kelasStore'])->name('sekolah.kelas.store');
        Route::get('/sekolah/{school_id}/kelas/{id}', [AdminDashboardController::class, 'kelasShow'])->name('sekolah.kelas.show');
        Route::put('/sekolah/{school_id}/kelas/{id}', [AdminDashboardController::class, 'kelasUpdate'])->name('sekolah.kelas.update');
        Route::delete('/sekolah/{school_id}/kelas/{id}', [AdminDashboardController::class, 'kelasDelete'])->name('sekolah.kelas.delete');
        Route::post('/sekolah/{school_id}/kelas/{id}/verify/{user_id}', [AdminDashboardController::class, 'kelasVerifyStudent'])->name('sekolah.kelas.verify');
        Route::post('/sekolah/{school_id}/kelas/{id}/reject/{user_id}', [AdminDashboardController::class, 'kelasRejectStudent'])->name('sekolah.kelas.reject');
    });

    // Guru pages
    Route::prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
        Route::get('/screening', [GuruDashboardController::class, 'screening'])->name('screening');
        Route::get('/siswa', [GuruDashboardController::class, 'siswa'])->name('siswa');
        Route::get('/laporan', [GuruDashboardController::class, 'laporan'])->name('laporan');
    });
});
