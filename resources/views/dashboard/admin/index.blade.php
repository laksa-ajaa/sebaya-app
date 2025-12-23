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
                    <div id="adminScreeningChart"></div>
                </div>
                <!-- Line Chart -->
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <div id="adminMoodChart"></div>
                </div>
            </div>

            <!-- Row 2 -->
            <div class="bg-white rounded-[15px] p-4 mb-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
                <div id="adminServiceChart"></div>
            </div>

            <!-- Row 3 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-[15px] p-4 flex items-center justify-center"
                    style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <span class="text-2xl font-bold text-[#010E82]">0</span>
                </div>
                <div class="bg-white rounded-[15px] p-4 flex items-center justify-center"
                    style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <span class="text-2xl font-bold text-[#010E82]">0</span>
                </div>
                <div class="bg-white rounded-[15px] p-4 flex items-center justify-center"
                    style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <span class="text-2xl font-bold text-[#010E82]">0</span>
                </div>
            </div>
        </main>

        @include('layouts.partials.footer')
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
            const screeningEl = document.querySelector('#adminScreeningChart');
            if (screeningEl) new ApexCharts(screeningEl, screeningOpts).render();

            const moodOpts = {
                chart: {
                    type: 'bar',
                    height: 280
                },
                series: [{
                    name: 'Mood',
                    data: [12, 28, 32, 18, 10]
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
            const moodEl = document.querySelector('#adminMoodChart');
            if (moodEl) new ApexCharts(moodEl, moodOpts).render();

            const serviceOpts = {
                chart: {
                    type: 'line',
                    height: 260,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Layanan',
                    data: [10, 15, 12, 20, 18, 24, 22]
                }],
                xaxis: {
                    categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
                },
                colors: ['#1358D4'],
                dataLabels: {
                    enabled: false
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
