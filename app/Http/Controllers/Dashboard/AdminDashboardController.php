<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
        return view('dashboard.admin.index');
    }

    public function statistik()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
        return view('dashboard.admin.statistik');
    }

    public function laporan()
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
        return view('dashboard.admin.laporan');
    }
}
