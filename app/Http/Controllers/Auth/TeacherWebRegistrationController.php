<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\TeacherRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TeacherWebRegistrationController extends Controller
{
    public function showRegister()
    {
        return view('auth.teacher.register');
    }

    public function submitRegister(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'whatsapp_number' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'school_name' => ['required', 'string', 'max:255'],
            'school_npsn' => ['nullable', 'string', 'max:50'],
            'school_address' => ['nullable', 'string'],
            'school_phone' => ['nullable', 'string', 'max:50'],
        ]);

        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'whatsapp_number' => $data['whatsapp_number'],
                'role' => 'teacher',
                'account_status' => 'pending_verification',
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->otp_code = $otpCode;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            TeacherRegistration::create([
                'user_id' => $user->id,
                'school_name' => $data['school_name'],
                'school_npsn' => $data['school_npsn'] ?? null,
                'school_address' => $data['school_address'] ?? null,
                'school_phone' => $data['school_phone'] ?? null,
                'status' => 'pending',
            ]);

            Mail::to($user->email)->send(new OtpMail($otpCode));

            // Sertakan email sebagai query param agar halaman OTP tidak tergantung flash session
            return redirect()->route('teacher.otp.show', ['email' => $user->email])
                ->with('email', $user->email);
        });
    }

    public function showOtp(Request $request)
    {
        $email = session('email') ?? $request->query('email');
        if (! $email) {
            return redirect()->route('teacher.register.show');
        }
        return view('auth.teacher.otp', ['email' => $email]);
    }

    public function resendOtp(Request $request)
    {
        $email = session('email') ?? $request->query('email');
        if (! $email) {
            return redirect()->route('teacher.register.show');
        }

        $user = User::where('email', $email)->where('role', 'teacher')->first();
        if (! $user) {
            return back()->withErrors(['email' => 'Guru tidak ditemukan.']);
        }

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->otp_code = $otpCode;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new OtpMail($otpCode));

        return back()->with('success', 'OTP baru telah dikirim ke email Anda.');
    }

    public function submitOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $data['email'])->where('role', 'teacher')->first();
        if (! $user) {
            return back()->withErrors(['email' => 'Guru tidak ditemukan.']);
        }

        if (! $user->otp_code || $user->otp_code !== $data['otp_code']) {
            return back()->withErrors(['otp_code' => 'OTP tidak valid.']);
        }

        if (! $user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            return back()->withErrors(['otp_code' => 'OTP kadaluarsa.']);
        }

        $user->otp_verified_at = now();
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        return redirect()->route('teacher.waiting');
    }

    public function waiting()
    {
        return view('auth.teacher.waiting');
    }

    public function showReapply()
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'teacher', 403);

        // Hanya tampilkan untuk akun yang disuspended
        if ($user->account_status !== 'suspended') {
            return redirect()->route('guru.dashboard');
        }

        return view('auth.teacher.reapply');
    }

    public function submitReapply(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'teacher', 403);

        if ($user->account_status !== 'suspended') {
            return redirect()->route('guru.dashboard');
        }

        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_npsn' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($user, $data) {
            TeacherRegistration::create([
                'user_id' => $user->id,
                'school_name' => $data['school_name'],
                'school_npsn' => $data['school_npsn'] ?? null,
                'school_address' => null,
                'school_phone' => null,
                'status' => 'pending',
            ]);

            $user->account_status = 'pending_verification';
            $user->save();
        });

        return redirect()->route('teacher.waiting')->with('success', 'Pengajuan ulang dikirim. Menunggu verifikasi admin.');
    }

    public function showSchoolInfo()
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'teacher', 403);

        // Check if already has school info
        $hasSchoolInfo = TeacherRegistration::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasSchoolInfo) {
            // Already filled, redirect based on account status
            if ($user->account_status === 'pending_verification' || $user->account_status === 'suspended') {
                return redirect()->route('teacher.waiting');
            }
            return redirect()->route('guru.dashboard');
        }

        return view('auth.teacher.school-info');
    }

    public function submitSchoolInfo(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'teacher', 403);

        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_npsn' => ['nullable', 'string', 'max:50'],
            'school_address' => ['nullable', 'string'],
            'school_phone' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($user, $data) {
            TeacherRegistration::create([
                'user_id' => $user->id,
                'school_name' => $data['school_name'],
                'school_npsn' => $data['school_npsn'] ?? null,
                'school_address' => $data['school_address'] ?? null,
                'school_phone' => $data['school_phone'] ?? null,
                'status' => 'pending',
            ]);

            // Keep account status as pending_verification
            $user->account_status = 'pending_verification';
            $user->save();
        });

        return redirect()->route('teacher.waiting')->with('success', 'Informasi sekolah berhasil disimpan. Menunggu verifikasi admin.');
    }
}
