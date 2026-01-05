<?php

namespace App\Http\Controllers\Api;

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
            return response()->json(['message' => 'Token tidak valid.'], 401);
        }

        $payload = $resp->json();

        $email = $payload['email'] ?? null;
        if (! $email) {
            return response()->json(['message' => 'Email tidak ditemukan pada token.'], 401);
        }

        // Verify audience (client id) if configured
        $clientId = env('GOOGLE_CLIENT_ID');
        if ($clientId && (($payload['aud'] ?? '') !== $clientId)) {
            return response()->json(['message' => 'Audience token tidak cocok.'], 401);
        }

        // Verify issuer
        $iss = $payload['iss'] ?? '';
        if (! in_array($iss, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            return response()->json(['message' => 'Issuer token tidak valid.'], 401);
        }

        // Ensure email is verified by Google
        $emailVerified = $payload['email_verified'] ?? null;
        if (! in_array($emailVerified, [true, 'true', '1', 1], true)) {
            return response()->json(['message' => 'Email belum terverifikasi oleh Google.'], 422);
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
                'email_verified_at' => now(),
            ]);
        } else {
            if (! $user->email_verified_at) {
                $user->email_verified_at = now();
                $user->save();
            }
        }

        $token = Auth::guard('api')->login($user);

        $ttlInSeconds = config('jwt.ttl') * 60;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttlInSeconds,
            'user' => $user,
        ]);
    }
}
