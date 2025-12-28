<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacherOtpVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek jika user sudah login
        if (Auth::check()) {
            $user = Auth::user()->fresh();

            // Jika teacher dan otp_verified_at masih null, redirect ke OTP
            if ($user->role === 'teacher' && is_null($user->otp_verified_at)) {
                return redirect()->route('teacher.otp.show', ['email' => $user->email])
                    ->with('warning', 'Silakan verifikasi OTP terlebih dahulu.');
            }

            // Jika teacher disuspended, arahkan ke halaman pengajuan ulang
            if ($user->role === 'teacher' && $user->account_status === 'suspended' && ! $request->routeIs('teacher.reapply.*')) {
                return redirect()->route('teacher.reapply.show')
                    ->with('warning', 'Akun Anda ditolak. Silakan ajukan ulang data sekolah.');
            }

            // Jika teacher dan account_status masih pending_verification, redirect ke waiting
            if ($user->role === 'teacher' && $user->account_status === 'pending_verification') {
                return redirect()->route('teacher.waiting')
                    ->with('warning', 'Akun Anda menunggu verifikasi admin.');
            }
        }


        return $next($request);
    }
}
