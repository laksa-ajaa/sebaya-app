@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
  <style>
    .chart-card {
      background: white;
      border-radius: 15px;
      box-shadow: 1px 2px 2px 0px #00000040;
    }

    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.25rem;
      border-bottom: 1px solid #f3f4f6;
    }

    .chart-title {
      font-size: 1rem;
      font-weight: 600;
      color: #010E82;
    }

    .date-range-picker {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 0.375rem 0.75rem;
      font-size: 0.8125rem;
      color: #475569;
      cursor: pointer;
      transition: all 0.2s;
    }

    .date-range-picker:hover {
      border-color: #010E82;
      background: #fff;
    }

    .date-range-picker svg {
      width: 16px;
      height: 16px;
      color: #64748b;
    }

    .chart-body {
      padding: 1rem 1.25rem;
    }

    .flatpickr-calendar {
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
      border-radius: 12px !important;
    }
  </style>

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
              <span class="inline-block bg-gray-100 px-2 py-1 rounded">{{ $totalUmum }} Umum</span>
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
      <div class="chart-card">
        <div class="chart-header">
          <span class="chart-title">Distribusi Mood</span>
          <div class="date-range-picker" id="donutDatePicker">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            <span id="donutDateLabel">Minggu ini</span>
          </div>
        </div>
        <div class="chart-body">
          <div id="adminMoodChart"></div>
        </div>
      </div>

      <!-- Bar Chart - Mood Checks per Hari -->
      <div class="chart-card">
        <div class="chart-header">
          <span class="chart-title">Mood Checks Harian</span>
          <div class="date-range-picker" id="barDatePicker">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            <span id="barDateLabel">Minggu ini</span>
          </div>
        </div>
        <div class="chart-body">
          <div id="adminMoodWeekChart"></div>
        </div>
      </div>
    </div>

    <!-- Row 2: Line Chart - Aktivitas Harian -->
    <div class="chart-card mb-6">
      <div class="chart-header">
        <span class="chart-title">Aktivitas Mood Checks</span>
        <div class="date-range-picker" id="lineDatePicker">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
          </svg>
          <span id="lineDateLabel">Minggu ini</span>
        </div>
      </div>
      <div class="chart-body">
        <div id="adminServiceChart"></div>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Data awal dari backend
      const initialMoodChartData = @json($moodChartData);
      const initialDailyMoodChecks = @json($dailyMoodChecks);
      const initialDailyMoodStacked = @json($dailyMoodStacked ?? []);
      const initialCategories = @json($chartDateCategories);

      const moodLevels = [{
          code: 5,
          label: 'Sangat Senang',
          color: '#5EA6FF'
        },
        {
          code: 4,
          label: 'Senang',
          color: '#1C7DFF'
        },
        {
          code: 3,
          label: 'Netral',
          color: '#1358D4'
        },
        {
          code: 2,
          label: 'Sedih',
          color: '#0B3BAA'
        },
        {
          code: 1,
          label: 'Sangat Sedih',
          color: '#00145C'
        },
      ];

      // Helper format tanggal
      const formatDateRange = (dates) => {
        if (!dates || dates.length === 0) return 'Pilih periode';
        const opts = {
          day: 'numeric',
          month: 'short'
        };
        if (dates.length === 1 || dates[0].getTime() === dates[1]?.getTime()) {
          return dates[0].toLocaleDateString('id-ID', opts);
        }
        return `${dates[0].toLocaleDateString('id-ID', opts)} - ${dates[1].toLocaleDateString('id-ID', opts)}`;
      };

      // Fetch data berdasarkan range
      const fetchChartData = async (startDate, endDate) => {
        const params = new URLSearchParams({
          start_date: startDate,
          end_date: endDate,
          ajax: 1
        });
        try {
          const res = await fetch(`{{ route('admin.dashboard.chartData') }}?${params}`);
          return await res.json();
        } catch (e) {
          console.error('Fetch error:', e);
          return null;
        }
      };

      // ============ DONUT CHART ============
      let donutChart;
      const renderDonutChart = (data) => {
        const opts = {
          chart: {
            type: 'donut',
            height: 280
          },
          series: [data[0] || 0, data[1] || 0, data[2] || 0, data[3] || 0, data[4] || 0],
          labels: ['Sangat Senang', 'Senang', 'Netral', 'Sedih', 'Sangat Sedih'],
          colors: ['#5EA6FF', '#1C7DFF', '#1358D4', '#0B3BAA', '#00145C'],
          legend: {
            position: 'right'
          },
          dataLabels: {
            enabled: true
          },
          plotOptions: {
            pie: {
              donut: {
                size: '55%'
              }
            }
          }
        };
        if (donutChart) {
          donutChart.updateSeries(opts.series);
        } else {
          donutChart = new ApexCharts(document.querySelector('#adminMoodChart'), opts);
          donutChart.render();
        }
      };
      renderDonutChart(initialMoodChartData);

      flatpickr('#donutDatePicker', {
        mode: 'range',
        locale: 'id',
        dateFormat: 'Y-m-d',
        defaultDate: [
          '{{ $startDate->toDateString() }}',
          '{{ $endDate->toDateString() }}'
        ],
        onChange: async (dates) => {
          if (dates.length === 2) {
            document.getElementById('donutDateLabel').textContent = formatDateRange(dates);
            const data = await fetchChartData(
              dates[0].toISOString().split('T')[0],
              dates[1].toISOString().split('T')[0]
            );
            if (data) renderDonutChart(data.moodChartData);
          }
        },
        onReady: (_, __, fp) => {
          document.getElementById('donutDateLabel').textContent = formatDateRange(fp.selectedDates);
        }
      });

      // ============ STACKED BAR CHART ============
      let barChart;
      const renderBarChart = (categories, stackedData) => {
        const series = moodLevels.map(level => ({
          name: level.label,
          data: categories.map(day => (stackedData[day]?.[level.code]) || 0)
        }));
        const opts = {
          chart: {
            type: 'bar',
            height: 280,
            stacked: true,
            toolbar: {
              show: false
            }
          },
          series,
          xaxis: {
            categories
          },
          colors: moodLevels.map(l => l.color),
          plotOptions: {
            bar: {
              borderRadius: 6,
              columnWidth: '55%'
            }
          },
          dataLabels: {
            enabled: false
          },
          legend: {
            position: 'top'
          },
          grid: {
            strokeDashArray: 4
          }
        };
        if (barChart) {
          barChart.updateOptions({
            xaxis: {
              categories
            },
            series
          });
        } else {
          barChart = new ApexCharts(document.querySelector('#adminMoodWeekChart'), opts);
          barChart.render();
        }
      };
      renderBarChart(initialCategories, initialDailyMoodStacked);

      flatpickr('#barDatePicker', {
        mode: 'range',
        locale: 'id',
        dateFormat: 'Y-m-d',
        defaultDate: [
          '{{ $startDate->toDateString() }}',
          '{{ $endDate->toDateString() }}'
        ],
        onChange: async (dates) => {
          if (dates.length === 2) {
            document.getElementById('barDateLabel').textContent = formatDateRange(dates);
            const data = await fetchChartData(
              dates[0].toISOString().split('T')[0],
              dates[1].toISOString().split('T')[0]
            );
            if (data) renderBarChart(data.chartDateCategories, data.dailyMoodStacked);
          }
        },
        onReady: (_, __, fp) => {
          document.getElementById('barDateLabel').textContent = formatDateRange(fp.selectedDates);
        }
      });

      // ============ LINE CHART ============
      let lineChart;
      const renderLineChart = (categories, dailyData) => {
        const series = [{
          name: 'Mood Checks',
          data: categories.map(day => dailyData[day] || 0)
        }];
        const opts = {
          chart: {
            type: 'line',
            height: 260,
            toolbar: {
              show: false
            }
          },
          series,
          xaxis: {
            categories
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
        if (lineChart) {
          lineChart.updateOptions({
            xaxis: {
              categories
            },
            series
          });
        } else {
          lineChart = new ApexCharts(document.querySelector('#adminServiceChart'), opts);
          lineChart.render();
        }
      };
      renderLineChart(initialCategories, initialDailyMoodChecks);

      flatpickr('#lineDatePicker', {
        mode: 'range',
        locale: 'id',
        dateFormat: 'Y-m-d',
        defaultDate: [
          '{{ $startDate->toDateString() }}',
          '{{ $endDate->toDateString() }}'
        ],
        onChange: async (dates) => {
          if (dates.length === 2) {
            document.getElementById('lineDateLabel').textContent = formatDateRange(dates);
            const data = await fetchChartData(
              dates[0].toISOString().split('T')[0],
              dates[1].toISOString().split('T')[0]
            );
            if (data) renderLineChart(data.chartDateCategories, data.dailyMoodChecks);
          }
        },
        onReady: (_, __, fp) => {
          document.getElementById('lineDateLabel').textContent = formatDateRange(fp.selectedDates);
        }
      });

    });
  </script>
@endsection
