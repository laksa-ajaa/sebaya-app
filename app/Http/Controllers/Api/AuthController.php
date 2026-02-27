<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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
            return ApiResponse::error('Email atau password salah.', null, 401);
        }

        $user = Auth::guard('api')->user();

        if (!$user->otp_verified_at) {
            return ApiResponse::error('Akun belum diverifikasi. Silakan verifikasi OTP terlebih dahulu.', null, 401);
        }

        $ttlInSeconds = config('jwt.ttl') * 60;

        // Get the first (latest) class
        $userClass = $user->class()->with('school')->first();

        $schoolInfo = null;
        if ($userClass && $userClass->school) {
            $schoolInfo = $userClass->school->name . ' - ' . $userClass->name;
        }

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttlInSeconds,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'whatsapp_number' => $user->whatsapp_number,
                'profile_photo_url' => $user->profile_photo_url,
                'role' => $user->role,
                'mode' => $user->mode ?? 'reguler',
                'school_info' => $schoolInfo,
                'otp_verified_at' => $user->otp_verified_at,
            ],
        ], 'Login berhasil.');
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

        return ApiResponse::success(null, 'Registrasi berhasil. Kode OTP telah dikirim ke email.', 201);
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
            return ApiResponse::error('User dengan email tersebut tidak ditemukan.', null, 404);
        }

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->otp_code = $otpCode;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new OtpMail($otpCode));

        return ApiResponse::success(null, 'Kode OTP telah dikirim ke email.');
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
            return ApiResponse::error('User tidak ditemukan.', null, 404);
        }

        if (! $user->otp_code || $user->otp_code !== $data['otp_code']) {
            return ApiResponse::error('Kode OTP tidak valid.', null, 422);
        }

        if (! $user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            return ApiResponse::error('Kode OTP sudah kedaluwarsa.', null, 422);
        }

        $user->otp_verified_at = now();
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        return ApiResponse::success(null, 'OTP berhasil diverifikasi.');
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

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'whatsapp_number' => $user->whatsapp_number,
            'profile_photo_url' => $user->profile_photo_url,
            'role' => $user->role,
            'mode' => $user->mode ?? 'reguler',
            'school_info' => $schoolInfo,
        ], 'Data user berhasil diambil.');
    }

    /**
     * Update user mode (toggle between reguler and student).
     * Only users who are enrolled in a class can switch to student mode.
     */
    public function updateMode(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'user') {
            return ApiResponse::error('Hanya siswa yang dapat mengubah mode.', null, 403);
        }

        $data = $request->validate([
            'mode' => ['required', 'in:reguler,student'],
        ]);

        // Check if user is enrolled in any class
        $isEnrolled = $user->class()->exists();

        if ($data['mode'] === 'student' && !$isEnrolled) {
            return ApiResponse::error('Anda harus terdaftar di kelas untuk menggunakan mode student.', null, 403);
        }

        $user->mode = $data['mode'];
        $user->save();

        return ApiResponse::success([
            'mode' => $user->mode,
        ], 'Berhasil beralih ke mode ' . $user->mode . '.');
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'whatsapp_number' => ['sometimes', 'required', 'string', 'max:50'],
            'profile_photo' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if it exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Convert photo to WebP
            $file = $request->file('profile_photo');
            $image = imagecreatefromstring(file_get_contents($file->getRealPath()));

            if ($image !== false) {
                // Remove alpha channel blending and keep transparency for PNGs
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);

                $filename = 'profile_photos/' . uniqid('profile_', true) . '.webp';

                // Save to output buffer
                ob_start();
                imagewebp($image, null, 80);
                $imageData = ob_get_clean();
                imagedestroy($image);

                // Store in public disk
                Storage::disk('public')->put($filename, $imageData);
                $data['profile_photo'] = $filename;
            } else {
                // Fallback to storing as original format if conversion fails
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $data['profile_photo'] = $path;
            }
        }

        $user->update($data);

        return ApiResponse::success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'whatsapp_number' => $user->whatsapp_number,
                'profile_photo_url' => $user->profile_photo_url,
                'role' => $user->role,
                'mode' => $user->mode ?? 'reguler',
            ]
        ], 'Profil berhasil diperbarui.');
    }
}
