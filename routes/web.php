<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\TeacherWebRegistrationController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\GuruDashboardController;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user()->fresh();

        // Jika teacher dan belum verifikasi OTP
        if ($user->role === 'teacher' && is_null($user->otp_verified_at)) {
            // Generate dan kirim OTP
            $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->otp_code = $otpCode;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\OtpMail($otpCode));

            return redirect()->route('teacher.otp.show', ['email' => $user->email])
                ->with('success', 'Kode OTP telah dikirimkan ke email Anda.');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($user->role === 'teacher') {
            return redirect()->route('guru.dashboard');
        }
    }
    return redirect()->route('login');
});

Route::get('/privacy-policy', function () {
    return view('privacy-policy.privacy-policy');
})->name('privacy-policy');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'authenticate'])
        ->middleware('throttle:5,1')
        ->name('authenticated');
});

Route::prefix('guru')->middleware('guest')->group(function () {
    Route::get('registrasi', [TeacherWebRegistrationController::class, 'showRegister'])->name('teacher.register.show');
    Route::post('registrasi', [TeacherWebRegistrationController::class, 'submitRegister'])->name('teacher.register.submit');

    Route::get('menunggu-verifikasi', [TeacherWebRegistrationController::class, 'waiting'])->name('teacher.waiting');
});

// OTP routes untuk guest (saat registrasi)
Route::prefix('guru')->middleware('guest')->group(function () {
    Route::get('otp', [TeacherWebRegistrationController::class, 'showOtp'])->name('teacher.otp.show');
    Route::post('otp', [TeacherWebRegistrationController::class, 'submitOtp'])->name('teacher.otp.submit');
    Route::post('otp/resend', [TeacherWebRegistrationController::class, 'resendOtp'])->name('teacher.otp.resend');
});

// OTP routes untuk authenticated users (saat login dan belum verify OTP)
Route::prefix('guru')->middleware('auth')->group(function () {
    Route::get('otp', [TeacherWebRegistrationController::class, 'showOtp'])->name('teacher.otp.show');
    Route::post('otp', [TeacherWebRegistrationController::class, 'submitOtp'])->name('teacher.otp.submit');
    Route::post('otp/resend', [TeacherWebRegistrationController::class, 'resendOtp'])->name('teacher.otp.resend');

    Route::get('ajukan-ulang', [TeacherWebRegistrationController::class, 'showReapply'])->name('teacher.reapply.show');
    Route::post('ajukan-ulang', [TeacherWebRegistrationController::class, 'submitReapply'])->name('teacher.reapply.submit');

    Route::get('menunggu-verifikasi', [TeacherWebRegistrationController::class, 'waiting'])->name('teacher.waiting');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'logout'])->name('logout');

    // Admin pages
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/chart-data', [AdminDashboardController::class, 'chartData'])->name('dashboard.chartData');
        Route::get('/statistik', [AdminDashboardController::class, 'statistik'])->name('statistik');
        Route::post('/user/{id}/reset-password', [AdminDashboardController::class, 'resetPassword'])->name('user.reset-password');
        Route::delete('/user/{id}', [AdminDashboardController::class, 'deleteUser'])->name('user.delete');
        Route::get('/laporan', [AdminDashboardController::class, 'laporan'])->name('laporan');
        Route::get('/mood-check', [AdminDashboardController::class, 'moodCheck'])->name('mood-check');
        Route::get('/mood-check/export', [AdminDashboardController::class, 'moodCheckExport'])->name('mood-check.export');

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

        // Guru registration approvals
        Route::get('/guru/requests', [AdminDashboardController::class, 'teacherRequests'])->name('guru.requests');
        Route::post('/guru/requests/{id}/approve', [AdminDashboardController::class, 'approveTeacherRegistration'])->name('guru.requests.approve');
        Route::post('/guru/requests/{id}/reject', [AdminDashboardController::class, 'rejectTeacherRegistration'])->name('guru.requests.reject');
    });

    // Guru pages
    Route::prefix('guru')->name('guru.')->middleware('ensure.teacher.otp')->group(function () {
        Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/chart-data', [GuruDashboardController::class, 'chartData'])->name('dashboard.chartData');
        Route::get('/screening', [GuruDashboardController::class, 'screening'])->name('screening');
        Route::get('/siswa', [GuruDashboardController::class, 'siswa'])->name('siswa');
        Route::get('/mood-check', [GuruDashboardController::class, 'moodCheck'])->name('mood-check');
        Route::get('/mood-check/export', [GuruDashboardController::class, 'moodCheckExport'])->name('mood-check.export');
        Route::get('/laporan', [GuruDashboardController::class, 'laporan'])->name('laporan');

        // Manajemen Sekolah (hanya untuk teacher_level = admin)
        Route::get('/sekolah', [GuruDashboardController::class, 'sekolah'])->name('sekolah');
        Route::put('/sekolah/{id}', [GuruDashboardController::class, 'sekolahUpdate'])->name('sekolah.update');

        // Manajemen Kelas (untuk teacher_level admin dan kelas)
        Route::get('/kelas', [GuruDashboardController::class, 'kelas'])->name('kelas');
        Route::post('/kelas', [GuruDashboardController::class, 'kelasStore'])->name('kelas.store');
        Route::get('/kelas/{id}', [GuruDashboardController::class, 'kelasDetail'])->name('kelas.detail');
        Route::put('/kelas/{id}', [GuruDashboardController::class, 'kelasUpdate'])->name('kelas.update');
        Route::delete('/kelas/{id}', [GuruDashboardController::class, 'kelasDelete'])->name('kelas.delete');
        Route::post('/kelas/{id}/verify/{user_id}', [GuruDashboardController::class, 'kelasVerifyStudent'])->name('kelas.verify');
        Route::post('/kelas/{id}/reject/{user_id}', [GuruDashboardController::class, 'kelasRejectStudent'])->name('kelas.reject');
    });
});
