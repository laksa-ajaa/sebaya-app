<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\TeacherRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class TeacherRegistrationController extends Controller
{
    public function register(Request $request)
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
                'password' => $data['password'], // hashed by cast
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

            return response()->json([
                'message' => 'Registrasi guru berhasil. Cek email untuk OTP.',
            ], 201);
        });
    }

    public function resendOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->where('role', 'teacher')->first();
        if (! $user) {
            return response()->json(['message' => 'Guru tidak ditemukan.'], 404);
        }

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->otp_code = $otpCode;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new OtpMail($otpCode));

        return response()->json(['message' => 'OTP telah dikirim ulang.']);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $data['email'])->where('role', 'teacher')->first();
        if (! $user) {
            return response()->json(['message' => 'Guru tidak ditemukan.'], 404);
        }

        if (! $user->otp_code || $user->otp_code !== $data['otp_code']) {
            return response()->json(['message' => 'OTP tidak valid.'], 422);
        }

        if (! $user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP kadaluarsa.'], 422);
        }

        $user->otp_verified_at = now();
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'OTP berhasil diverifikasi. Menunggu verifikasi admin.',
        ]);
    }
}
