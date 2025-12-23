@extends('layouts.app')

@section('title', 'Laporan Guru')

@section('content')
<div class="min-h-screen bg-slate-50">
    @include('layouts.partials.navbar')
    @include('layouts.partials.sidebar')

    <main class="pt-20 px-6 bg-blue-100 min-h-screen transition-all lg:ml-64">
        <div class="bg-white rounded-xl p-4 shadow mb-6">
            <h1 class="text-lg font-semibold text-slate-800">Laporan Guru</h1>
            <p class="text-sm text-slate-600 mt-2">Tempatkan laporan dan hasil pemantauan siswa di sini.</p>
        </div>
    </main>

    @include('layouts.partials.footer')
</div>

<script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    sidebarToggle?.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        sidebarOverlay.classList.toggle('hidden');
    });

    sidebarOverlay?.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.add('hidden');
    });
</script>
@endsection

