@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
    <div class="px-6 py-6 bg-blue-100 min-h-screen">

        <!-- Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-sm font-bold text-black mb-2">Statistik Hasil Screening Siswa</p>
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <div id="guruScreeningChart"></div>
                </div>
            </div>
            <div>
                <p class="text-sm font-bold text-black mb-2">Statistik Mood Siswa</p>
                <div class="bg-white rounded-[15px] p-4" style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <div id="guruMoodChart"></div>
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
                <div class="bg-white rounded-[15px] p-4 flex items-center justify-center"
                    style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <span class="text-2xl font-bold text-[#010E82]">0</span>
                </div>
            </div>
            <div>
                <p class="text-sm font-bold text-black mb-2">Screening Aktif</p>
                <div class="bg-white rounded-[15px] p-4 flex items-center justify-center"
                    style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <span class="text-2xl font-bold text-[#010E82]">0</span>
                </div>
            </div>
            <div>
                <p class="text-sm font-bold text-black mb-2">Perlu Perhatian</p>
                <div class="bg-white rounded-[15px] p-4 flex items-center justify-center"
                    style="box-shadow: 1px 2px 2px 0px #00000040;">
                    <span class="text-2xl font-bold text-[#010E82]">0</span>
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
