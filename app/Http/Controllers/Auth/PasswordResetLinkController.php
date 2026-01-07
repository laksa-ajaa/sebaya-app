<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the forgot password view.
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        // We will send the password reset link to this user. Once it is sent we will
        // examine the response then see the message we need to show to the user.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Tautan atur ulang kata sandi telah dikirim ke email Anda.');
        }

        return throw ValidationException::withMessages([
            'email' => [match($status) {
                Password::INVALID_USER => 'Kami tidak dapat menemukan pengguna dengan alamat email tersebut.',
                Password::RESET_THROTTLED => 'Mohon tunggu sebelum mencoba kembali.',
                default => 'Terjadi kesalahan saat mengirim email.',
            }],
        ]);
    }

    /**
     * API version for sending reset link.
     */
    public function storeApi(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User dengan email tersebut tidak ditemukan.'
            ], 422);
        }

        $token = Password::createToken($user);
        $user->notify(new \App\Notifications\ResetPasswordNotification($token, 'mobile'));

        return response()->json([
            'message' => 'Tautan atur ulang kata sandi telah dikirim ke email Anda.'
        ]);
    }
}
