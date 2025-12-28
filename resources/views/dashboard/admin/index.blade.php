@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="px-6 py-6 bg-blue-100 min-h-screen">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-[#010E82]">Dashboard Admin</h1>
            <p class="text-gray-600 mt-1">Statistik Keseluruhan Sistem</p>
        </div>

        <!-- Statistik Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Total Pengguna</p>
                        <p class="text-3xl font-bold text-[#010E82]">{{ number_format($totalUsers) }}</p>
                        <p class="text-xs text-gray-500 mt-2">
                            <span class="inline-block bg-blue-100 px-2 py-1 rounded">{{ $totalStudents }} Siswa</span>
                            <span class="inline-block bg-green-100 px-2 py-1 rounded ml-1">{{ $totalTeachers }}
                                Guru</span>
                            <span class="inline-block bg-purple-100 px-2 py-1 rounded ml-1">{{ $totalAdmins }}
                                Admin</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Total Mood Checks</p>
                        <p class="text-3xl font-bold text-[#010E82]">{{ number_format($totalMoodChecks) }}</p>
                        <p class="text-xs text-gray-500 mt-2">Hari ini: {{ $moodChecksToday }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Total Jurnal</p>
                        <p class="text-3xl font-bold text-[#010E82]">{{ number_format($totalJournals) }}</p>
                        <p class="text-xs text-gray-500 mt-2">
                            Minggu ini: {{ $journalsThisWeek }} | Hari ini: {{ $journalsToday }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Total Screening</p>
                        <p class="text-3xl font-bold text-[#010E82]">{{ number_format($totalScreenings) }}</p>
                        <p class="text-xs text-gray-500 mt-2">Data screening siswa</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 1: Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Donut Chart - Distribusi Mood -->
            <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                <h3 class="text-lg font-semibold text-[#010E82] mb-4">Distribusi Mood</h3>
                <div id="adminMoodChart"></div>
            </div>
            <!-- Bar Chart - Mood Checks per Hari -->
            <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                <h3 class="text-lg font-semibold text-[#010E82] mb-4">Mood Checks Minggu Ini</h3>
                <div id="adminMoodWeekChart"></div>
            </div>
        </div>

        <!-- Row 2: Line Chart - Aktivitas Harian -->
        <div class="bg-white rounded-[15px] p-4 mb-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
            <h3 class="text-lg font-semibold text-[#010E82] mb-4">Aktivitas Mood Checks per Hari (Minggu Ini)</h3>
            <div id="adminServiceChart"></div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Data dari backend
            const moodChartData = @json($moodChartData);
            const dailyMoodChecks = @json($dailyMoodChecks);

            // Chart Distribusi Mood (Donut)
            const moodOpts = {
                chart: {
                    type: 'donut',
                    height: 280
                },
                series: [
                    moodChartData[0] || 0, // Sangat Senang
                    moodChartData[1] || 0, // Senang
                    moodChartData[2] || 0, // Netral
                    moodChartData[3] || 0, // Sedih
                    moodChartData[4] || 0 // Sangat Sedih
                ],
                labels: ['Sangat Senang', 'Senang', 'Netral', 'Sedih', 'Sangat Sedih'],
                colors: ['#5EA6FF', '#1C7DFF', '#1358D4', '#0B3BAA', '#00145C'],
                legend: {
                    position: 'right'
                },
                dataLabels: {
                    enabled: true,
                    formatter: (val) => {
                        const total = moodChartData.reduce((a, b) => a + b, 0);
                        return total > 0 ? `${((val / total) * 100).toFixed(0)}%` : '0%';
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '55%'
                        }
                    }
                }
            };
            const moodEl = document.querySelector('#adminMoodChart');
            if (moodEl) new ApexCharts(moodEl, moodOpts).render();

            // Chart Mood Checks per Hari (Bar)
            const moodWeekOpts = {
                chart: {
                    type: 'bar',
                    height: 280,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Mood Checks',
                    data: [
                        dailyMoodChecks['Sen'] || 0,
                        dailyMoodChecks['Sel'] || 0,
                        dailyMoodChecks['Rab'] || 0,
                        dailyMoodChecks['Kam'] || 0,
                        dailyMoodChecks['Jum'] || 0,
                        dailyMoodChecks['Sab'] || 0,
                        dailyMoodChecks['Min'] || 0
                    ]
                }],
                xaxis: {
                    categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
                },
                colors: ['#0B3BAA'],
                plotOptions: {
                    bar: {
                        borderRadius: 6
                    }
                },
                dataLabels: {
                    enabled: true
                },
                grid: {
                    strokeDashArray: 4
                }
            };
            const moodWeekEl = document.querySelector('#adminMoodWeekChart');
            if (moodWeekEl) new ApexCharts(moodWeekEl, moodWeekOpts).render();

            // Chart Aktivitas Harian (Line)
            const serviceOpts = {
                chart: {
                    type: 'line',
                    height: 260,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Mood Checks',
                    data: [
                        dailyMoodChecks['Sen'] || 0,
                        dailyMoodChecks['Sel'] || 0,
                        dailyMoodChecks['Rab'] || 0,
                        dailyMoodChecks['Kam'] || 0,
                        dailyMoodChecks['Jum'] || 0,
                        dailyMoodChecks['Sab'] || 0,
                        dailyMoodChecks['Min'] || 0
                    ]
                }],
                xaxis: {
                    categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
                },
                colors: ['#1358D4'],
                dataLabels: {
                    enabled: true
                },
                stroke: {
                    width: 3,
                    curve: 'smooth'
                },
                grid: {
                    strokeDashArray: 4
                }
            };
            const serviceEl = document.querySelector('#adminServiceChart');
            if (serviceEl) new ApexCharts(serviceEl, serviceOpts).render();
        });
    </script>
@endsection
