<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'authenticate'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    Route::get('/admin/dashboard', function () {
        abort_unless(auth()->user()->role === 'admin', 403);

        return view('dashboard.admin');
    })->name('admin.dashboard');

    Route::get('/guru/dashboard', function () {
        abort_unless(in_array(auth()->user()->role, ['guru', 'teacher']), 403);

        return view('dashboard.guru');
    })->name('guru.dashboard');
});
