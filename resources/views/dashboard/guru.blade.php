@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="flex items-center justify-between bg-white px-6 py-4 shadow-sm">
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-400">Guru</p>
            <p class="text-lg font-semibold text-slate-800">Selamat datang, {{ auth()->user()->name }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                Keluar
            </button>
        </form>
    </header>

    <main class="px-6 py-10">
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-10 text-center shadow-sm">
            <p class="text-base font-semibold text-slate-800">Dashboard Guru</p>
            <p class="mt-2 text-sm text-slate-500">Tambahkan fitur kelas & screening di sini.</p>
        </div>
    </main>
</div>
@endsection

