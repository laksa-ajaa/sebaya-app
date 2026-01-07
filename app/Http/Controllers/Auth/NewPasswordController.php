<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request)
    {
        return view('auth.reset-password', [
            'request' => $request,
            'source' => $request->query('source')
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password baru harus diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal harus 8 karakter.',
        ]);

        // If the password reset attempt was successful, we will update the password on
        // the user object and persist it to the database. Otherwise we will parse
        // the error and return the response to the user with an error message.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            if ($request->source === 'mobile') {
                return view('auth.reset-password-success');
            }
            return redirect()->route('login')->with('status', 'Kata sandi Anda berhasil diperbarui.');
        }

        return throw ValidationException::withMessages([
            'email' => [match($status) {
                Password::INVALID_USER => 'Kami tidak dapat menemukan pengguna dengan alamat email tersebut.',
                Password::INVALID_TOKEN => 'Token atur ulang kata sandi ini tidak valid atau sudah kedaluwarsa.',
                default => 'Terjadi kesalahan saat mengatur ulang kata sandi.',
            }],
        ]);
    }
}
