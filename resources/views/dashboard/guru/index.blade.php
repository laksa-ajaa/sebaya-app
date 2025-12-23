@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
    <div class="px-6 py-6 bg-blue-100 min-h-screen">

        <!-- Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-sm font-bold text-black mb-2">Statistik Hasil Screening Siswa</p>
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <!-- isi statistik hasil screening -->
                </div>
            </div>
            <div>
                <p class="text-sm font-bold text-black mb-2">Statistik Mood Siswa</p>
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <!-- isi statistik mood -->
                </div>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="mb-6">
            <p class="text-sm font-bold text-black mb-2">Layanan Harian</p>
            <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                <!-- isi layanan harian -->
            </div>
        </div>

        <!-- Row 3 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm font-bold text-black mb-2">Total Siswa Terdaftar</p>
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <!-- isi total siswa -->
                </div>
            </div>
            <div>
                <p class="text-sm font-bold text-black mb-2">Screening Aktif</p>
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <!-- isi screening aktif -->
                </div>
            </div>
            <div>
                <p class="text-sm font-bold text-black mb-2">Perlu Perhatian</p>
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <!-- isi perlu perhatian -->
                </div>
            </div>
        </div>
    </div>
@endsection
