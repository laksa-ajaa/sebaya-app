<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi salah.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        return $this->redirectToDashboard(Auth::user());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
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

        return redirect()->route('dashboard');
    }
}

