<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function authenticate(Request $request)
    {
        // Validasi input dengan pesan error custom
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        // Attempt login dengan remember me
        $remember = $request->boolean('remember');

        // Coba autentikasi
        if (!Auth::attempt($credentials, $remember)) {
            // Throw validation exception untuk menampilkan error di form
            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi salah.'],
            ])->errorBag('default');
        }

        // Regenerate session untuk keamanan (prevent session fixation)
        $request->session()->regenerate();

        // Redirect berdasarkan role user
        return $this->redirectToDashboard(Auth::user());
    }

    /**
     * Logout user and destroy authenticated session.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect the user to the correct dashboard based on role.
     */
    protected function redirectToDashboard($user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if (in_array($user->role, ['guru', 'teacher'])) {
            return redirect()->route('guru.dashboard');
        }

        // Tolak login untuk role selain admin dan guru
        Auth::logout();
        throw ValidationException::withMessages([
            'email' => ['Akun Anda tidak memiliki akses untuk login.'],
        ])->errorBag('default');
    }
}
