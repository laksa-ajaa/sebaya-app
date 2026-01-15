<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Handle Google Sign-In from mobile by verifying id_token,
     * creating or finding the user, and returning a JWT.
     */
    public function google(Request $request)
    {
        $data = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $idToken = $data['id_token'];

        $resp = Http::asForm()->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if (! $resp->successful()) {
            return ApiResponse::error('Token tidak valid.', null, 401);
        }

        $payload = $resp->json();

        $email = $payload['email'] ?? null;
        if (! $email) {
            return ApiResponse::error('Email tidak ditemukan pada token.', null, 401);
        }

        // Verify audience (client id) if configured
        $clientId = env('GOOGLE_CLIENT_ID');
        if ($clientId && (($payload['aud'] ?? '') !== $clientId)) {
            return ApiResponse::error('Audience token tidak cocok.', null, 401);
        }

        // Verify issuer
        $iss = $payload['iss'] ?? '';
        if (! in_array($iss, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            return ApiResponse::error('Issuer token tidak valid.', null, 401);
        }

        // Ensure email is verified by Google
        $emailVerified = $payload['email_verified'] ?? null;
        if (! in_array($emailVerified, [true, 'true', '1', 1], true)) {
            return ApiResponse::error('Email belum terverifikasi oleh Google.', null, 422);
        }

        $name = $payload['name'] ?? explode('@', $email)[0];

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $name,
                'username' => explode('@', $email)[0],
                'email' => $email,
                'password' => Str::random(32),
                'role' => 'user',
                'otp_verified_at' => now(), // Auto verify if using Google
            ]);
        } else {
            if (! $user->otp_verified_at) {
                $user->otp_verified_at = now();
                $user->save();
            }
        }

        $token = Auth::guard('api')->login($user);

        $ttlInSeconds = config('jwt.ttl') * 60;

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttlInSeconds,
            'user' => $user,
        ], 'Login Google berhasil.');
    }
}
