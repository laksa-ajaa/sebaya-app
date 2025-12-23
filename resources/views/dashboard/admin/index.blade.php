@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="min-h-screen bg-slate-50">
        @include('layouts.partials.navbar')
        @include('layouts.partials.sidebar')

        <main class="pt-20 px-6 bg-blue-100 min-h-screen transition-all lg:ml-64">
            <!-- Row 1 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Donut Chart -->
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    Statistik Hasil Screening
                </div>
                <!-- Line Chart -->
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    Statistik Mood Siswa
                </div>
            </div>

            <!-- Row 2 -->
            <div class="bg-white rounded-[15px] p-4 mb-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
                Layanan Harian
            </div>

            <!-- Row 3 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    Total Siswa Terdaftar
                </div>
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    Screening Aktif
                </div>
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    Perlu Perhatian
                </div>
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
