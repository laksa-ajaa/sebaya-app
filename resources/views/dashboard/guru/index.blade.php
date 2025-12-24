@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
    <div class="px-6 py-6 bg-blue-100 min-h-screen">

        <!-- Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div>
                @if ($teacher_level === 'admin')
                    <p class="text-sm font-bold text-black mb-2">Statistik Hasil Screening Siswa (Sekolah)</p>
                @else
                    <p class="text-sm font-bold text-black mb-2">Statistik Hasil Screening Siswa (Kelas)</p>
                @endif
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <div id="guruScreeningChart"></div>
                </div>
            </div>
            <div>
                @if ($teacher_level === 'admin')
                    <p class="text-sm font-bold text-black mb-2">Statistik Mood Siswa (Sekolah)</p>
                @else
                    <p class="text-sm font-bold text-black mb-2">Statistik Mood Siswa (Kelas)</p>
                @endif
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <div id="guruMoodChart"></div>
                </div>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="mb-6">
            <p class="text-sm font-bold text-black mb-2">Layanan Harian</p>
            <div class="space-y-4">
                <!-- First Row: Total Siswa Terdaftar (Large Card) -->
                <div class="bg-white rounded-[15px] p-6 relative" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <div class="flex items-center justify-between">
                        <div>
                            @if ($teacher_level === 'admin')
                                <p class="text-sm font-medium text-gray-600 mb-2">Total Siswa Terdaftar (Sekolah)</p>
                            @else
                                <p class="text-sm font-medium text-gray-600 mb-2">Total Siswa Terdaftar (Kelas)</p>
                            @endif
                            <p class="text-4xl font-bold text-[#010E82]">77</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="white" />
                                <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Second Row: Four Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Screening Aktif -->
                    <div class="bg-white rounded-[15px] p-4 relative flex flex-col"
                        style="box-shadow: 1px 2px 2px 0px #00000040;">
                        <p class="text-sm font-medium text-gray-600 mb-2">Screening Aktif</p>
                        <div class="absolute top-1/2 right-4 transform -translate-y-1/2">
                            <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 12H5L7 8L11 16L13 12L15 16L19 8L21 12H21.01" stroke="white" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-[#010E82] mt-auto">60</p>
                    </div>

                    <!-- Perlu Perhatian -->
                    <div class="bg-white rounded-[15px] p-4 relative flex flex-col"
                        style="box-shadow: 1px 2px 2px 0px #00000040;">
                        <p class="text-sm font-medium text-gray-600 mb-2">Perlu Perhatian</p>
                        <div class="absolute top-1/2 right-4 transform -translate-y-1/2">
                            <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-[#010E82] mt-auto">5</p>
                    </div>

                    <!-- Mood Check-in aktif -->
                    <div class="bg-white rounded-[15px] p-4 relative flex flex-col"
                        style="box-shadow: 1px 2px 2px 0px #00000040;">
                        <p class="text-sm font-medium text-gray-600 mb-2">Mood Check-in aktif</p>
                        <div class="absolute top-1/2 right-4 transform -translate-y-1/2">
                            <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M20.84 4.61C20.3292 4.099 19.7228 3.69364 19.0554 3.41708C18.3879 3.14052 17.6725 2.99817 16.95 2.99817C16.2275 2.99817 15.5121 3.14052 14.8446 3.41708C14.1772 3.69364 13.5708 4.099 13.06 4.61L12 5.67L10.94 4.61C9.9083 3.57831 8.50903 2.99871 7.05 2.99871C5.59096 2.99871 4.19169 3.57831 3.16 4.61C2.1283 5.64169 1.54871 7.04097 1.54871 8.5C1.54871 9.95903 2.1283 11.3583 3.16 12.39L4.22 13.45L12 21.23L19.78 13.45L20.84 12.39C21.351 11.8792 21.7564 11.2728 22.0329 10.6054C22.3095 9.93789 22.4518 9.22248 22.4518 8.5C22.4518 7.77752 22.3095 7.0621 22.0329 6.39464C21.7564 5.72718 21.351 5.12075 20.84 4.61Z"
                                        fill="white" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-[#010E82] mt-auto">60</p>
                    </div>

                    <!-- Atur Jadwal -->
                    <div class="bg-white rounded-[15px] p-4 relative flex flex-col"
                        style="box-shadow: 1px 2px 2px 0px #00000040;">
                        <p class="text-sm font-medium text-gray-600 mb-2">Atur Jadwal</p>
                        <div class="absolute top-1/2 right-4 transform -translate-y-1/2">
                            <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z"
                                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-[#010E82] mt-auto">6</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const screeningOpts = {
                chart: {
                    type: 'donut',
                    height: 280
                },
                series: [8, 10, 15, 60, 7],
                labels: ['Sangat Parah', 'Parah', 'Sedang', 'Normal', 'Ringan'],
                colors: ['#00145C', '#0B3BAA', '#1358D4', '#1C7DFF', '#5EA6FF'],
                legend: {
                    position: 'right'
                },
                dataLabels: {
                    enabled: true,
                    formatter: (v) => `${v.toFixed(0)}%`
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '55%'
                        }
                    }
                }
            };
            const screeningEl = document.querySelector('#guruScreeningChart');
            if (screeningEl) {
                const chart = new ApexCharts(screeningEl, screeningOpts);
                chart.render();
            }

            const moodOpts = {
                chart: {
                    type: 'bar',
                    height: 280
                },
                series: [{
                    name: 'Mood',
                    data: [20, 35, 25, 15, 5]
                }],
                xaxis: {
                    categories: ['Normal', 'Ringan', 'Sedang', 'Parah', 'Sangat Parah']
                },
                colors: ['#0B3BAA'],
                plotOptions: {
                    bar: {
                        borderRadius: 6
                    }
                },
                dataLabels: {
                    enabled: false
                },
                grid: {
                    strokeDashArray: 4
                }
            };
            const moodEl = document.querySelector('#guruMoodChart');
            if (moodEl) {
                const chart = new ApexCharts(moodEl, moodOpts);
                chart.render();
            }
        });
    </script>
@endsection
