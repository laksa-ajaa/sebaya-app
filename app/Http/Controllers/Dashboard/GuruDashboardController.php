<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class GuruDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user?->role === 'teacher', 403);
        return view('dashboard.guru.index', [
            'teacher_level' => $user->teacher_level
        ]);
    }

    public function screening()
    {
        abort_unless(Auth::user()?->role === 'teacher', 403);
        return view('dashboard.guru.screening');
    }

    public function siswa()
    {
        abort_unless(Auth::user()?->role === 'teacher', 403);
        return view('dashboard.guru.siswa');
    }

    public function laporan()
    {
        abort_unless(Auth::user()?->role === 'teacher', 403);
        return view('dashboard.guru.laporan');
    }
}

