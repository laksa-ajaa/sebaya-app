<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TeacherRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Google_Client;

class GoogleAuthController extends Controller
{
    public function handleGoogleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            // Verify Google token
            $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $payload = $client->verifyIdToken($request->id_token);

            if (!$payload) {
                return response()->json(['error' => 'Invalid Google token'], 401);
            }

            $email = $payload['email'];
            $name = $payload['name'];
            $googleId = $payload['sub'];

            // Check if user exists
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Create new teacher user without OTP requirement
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'username' => explode('@', $email)[0] . '_' . substr($googleId, 0, 5),
                    'whatsapp_number' => '', // Empty for Google users
                    'role' => 'teacher',
                    'password' => Hash::make(uniqid()), // Random password (not used)
                    'otp_verified_at' => now(), // Auto-verify OTP for Google users
                    'email_verified_at' => now(),
                    'account_status' => 'pending_verification', // Pending admin verification
                ]);
            } else {
                // Update existing user to mark as OTP verified
                if (is_null($user->otp_verified_at)) {
                    $user->otp_verified_at = now();
                    $user->email_verified_at = now();
                    $user->save();
                }
            }

            // Login user
            Auth::login($user);

            // Check if teacher has completed school information
            $hasSchoolInfo = TeacherRegistration::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if (!$hasSchoolInfo) {
                // Redirect to school info form if not filled yet
                return response()->json([
                    'success' => true,
                    'redirect' => route('teacher.school-info.show')
                ]);
            }

            // Determine redirect URL based on account status
            $redirectUrl = '';

            if ($user->account_status === 'pending_verification' || $user->account_status === 'suspended') {
                $redirectUrl = route('teacher.waiting');
            } elseif ($user->role === 'admin') {
                $redirectUrl = route('admin.dashboard');
            } elseif ($user->role === 'teacher') {
                $redirectUrl = route('guru.dashboard');
            } else {
                $redirectUrl = route('login');
            }

            return response()->json([
                'success' => true,
                'redirect' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Login dengan Google gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}
