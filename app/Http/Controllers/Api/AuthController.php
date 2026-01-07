<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Handle a login request and return a JWT token.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user = Auth::guard('api')->user();

        if (!$user->otp_verified_at) {
            // Generate and send OTP if not verified
            $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $user->otp_code = $otpCode;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            Mail::to($user->email)->send(new OtpMail($otpCode));

            return response()->json([
                'message' => 'Login berhasil, namun akun belum diverifikasi. Kode OTP telah dikirim ke email.',
                'requires_otp_verification' => true,
            ], 200);
        }

        $ttlInSeconds = config('jwt.ttl') * 60;

        // Get the first (latest) class
        $userClass = $user->class()->with('school')->first();

        $schoolInfo = null;
        if ($userClass && $userClass->school) {
            $schoolInfo = $userClass->school->name . ' - ' . $userClass->name;
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttlInSeconds,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'whatsapp_number' => $user->whatsapp_number,
                'role' => $user->role,
                'school_info' => $schoolInfo,
                'otp_verified_at' => $user->otp_verified_at,
            ],
        ]);
    }

    /**
     * Handle user registration and send OTP via email.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'whatsapp_number' => ['required', 'string', 'max:50'],
            'class_code' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:user,teacher,admin'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $data['role'] = $data['role'] ?? 'user';

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'whatsapp_number' => $data['whatsapp_number'],
            'class_code' => $data['class_code'] ?? null,
            'role' => $data['role'],
            'email' => $data['email'],
            'password' => $data['password'], // akan di-hash oleh cast
        ]);

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->otp_code = $otpCode;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new OtpMail($otpCode));

        return response()->json([
            'message' => 'Registrasi berhasil. Kode OTP telah dikirim ke email.',
        ], 201);
    }

    /**
     * Send or resend OTP to an existing user by email.
     */
    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'User dengan email tersebut tidak ditemukan.',
            ], 404);
        }

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->otp_code = $otpCode;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new OtpMail($otpCode));

        return response()->json([
            'message' => 'Kode OTP telah dikirim ke email.',
        ]);
    }

    /**
     * Verify OTP for a user.
     */
    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        if (! $user->otp_code || $user->otp_code !== $data['otp_code']) {
            return response()->json([
                'message' => 'Kode OTP tidak valid.',
            ], 422);
        }

        if (! $user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'message' => 'Kode OTP sudah kedaluwarsa.',
            ], 422);
        }

        $user->otp_verified_at = now();
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'OTP berhasil diverifikasi.',
        ]);
    }

    /**
     * Get user data with school and class information.
     */
    public function getUserData(Request $request)
    {
        $user = $request->user();

        // Get the first (latest) class
        $userClass = $user->class()->with('school')->first();

        $schoolInfo = null;
        if ($userClass && $userClass->school) {
            $schoolInfo = $userClass->school->name . ' - ' . $userClass->name;
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'whatsapp_number' => $user->whatsapp_number,
            'role' => $user->role,
            'school_info' => $schoolInfo,
        ]);
    }
}
