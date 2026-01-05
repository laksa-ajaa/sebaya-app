@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')

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
      font-size: 0.875rem;
      font-weight: 600;
      color: #111827;
    }

    .chart-subtitle {
      font-size: 0.75rem;
      color: #6b7280;
      margin-top: 0.125rem;
    }

    .date-range-picker {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 0.375rem 0.75rem;
      font-size: 0.75rem;
      color: #475569;
      cursor: pointer;
      transition: all 0.2s;
    }

    .date-range-picker:hover {
      border-color: #010E82;
      background: #fff;
    }

    .date-range-picker svg {
      width: 14px;
      height: 14px;
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

    <!-- Row 1: Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Donut Chart - Distribusi Mood -->
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <p class="chart-title">Distribusi Mood Siswa</p>
            <p class="chart-subtitle">{{ $teacher_level === 'admin' ? 'Sekolah' : 'Kelas' }}</p>
          </div>
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
          <div id="guruMoodDonutChart"></div>
        </div>
      </div>

      <!-- Stacked Bar Chart - Mood Harian -->
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <p class="chart-title">Mood Siswa Harian</p>
            <p class="chart-subtitle">{{ $teacher_level === 'admin' ? 'Sekolah' : 'Kelas' }}</p>
          </div>
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
          <div id="guruMoodBarChart"></div>
        </div>
      </div>
    </div>

    <!-- Row 2: Statistik Cards -->
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
              <p class="text-4xl font-bold text-[#010E82]">{{ $totalStudents ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
          <div class="bg-white rounded-[15px] p-4 relative flex flex-col" style="box-shadow: 1px 2px 2px 0px #00000040;">
            <p class="text-sm font-medium text-gray-600 mb-2">Screening Aktif</p>
            <div class="absolute top-1/2 right-4 transform -translate-y-1/2">
              <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M3 12H5L7 8L11 16L13 12L15 16L19 8L21 12H21.01" stroke="white" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>
            </div>
            <p class="text-3xl font-bold text-[#010E82] mt-auto">0</p>
          </div>

          <!-- Perlu Perhatian -->
          <div class="bg-white rounded-[15px] p-4 relative flex flex-col" style="box-shadow: 1px 2px 2px 0px #00000040;">
            <p class="text-sm font-medium text-gray-600 mb-2">Perlu Perhatian</p>
            <div class="absolute top-1/2 right-4 transform -translate-y-1/2">
              <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                    stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>
            </div>
            <p class="text-3xl font-bold text-[#010E82] mt-auto">0</p>
          </div>

          <!-- Mood Check-in aktif -->
          <div class="bg-white rounded-[15px] p-4 relative flex flex-col" style="box-shadow: 1px 2px 2px 0px #00000040;">
            <p class="text-sm font-medium text-gray-600 mb-2">Mood Check-in aktif</p>
            <div class="absolute top-1/2 right-4 transform -translate-y-1/2">
              <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M20.84 4.61C20.3292 4.099 19.7228 3.69364 19.0554 3.41708C18.3879 3.14052 17.6725 2.99817 16.95 2.99817C16.2275 2.99817 15.5121 3.14052 14.8446 3.41708C14.1772 3.69364 13.5708 4.099 13.06 4.61L12 5.67L10.94 4.61C9.9083 3.57831 8.50903 2.99871 7.05 2.99871C5.59096 2.99871 4.19169 3.57831 3.16 4.61C2.1283 5.64169 1.54871 7.04097 1.54871 8.5C1.54871 9.95903 2.1283 11.3583 3.16 12.39L4.22 13.45L12 21.23L19.78 13.45L20.84 12.39C21.351 11.8792 21.7564 11.2728 22.0329 10.6054C22.3095 9.93789 22.4518 9.22248 22.4518 8.5C22.4518 7.77752 22.3095 7.0621 22.0329 6.39464C21.7564 5.72718 21.351 5.12075 20.84 4.61Z"
                    fill="white" />
                </svg>
              </div>
            </div>
            <p class="text-3xl font-bold text-[#010E82] mt-auto">0</p>
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
            <p class="text-3xl font-bold text-[#010E82] mt-auto">0</p>
          </div>
        </div>
      </div>
    </div>

  </div>


  <script src="https://cdn.jsdelivr.net/npm/apexcharts@5.3.6/dist/apexcharts.min.js"
    integrity="sha256-qNJtESJROYHRKwS/u3zdu4Fev69db17hKHZvrqGiqRs=" crossorigin="anonymous"></script>
  <script>
    document.addEventListener('DOMContentLoaded', async () => {
      // Initial data from backend
      const initialCategories = @json($chartDateCategories);
      const startDate = '{{ $startDate->toDateString() }}';
      const endDate = '{{ $endDate->toDateString() }}';

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

      // Helper format date range
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

      // Helper untuk format tanggal ke YYYY-MM-DD tanpa timezone issue
      const formatDateToYMD = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      };

      // Fetch data based on range
      const fetchChartData = async (startDate, endDate) => {
        const params = new URLSearchParams({
          start_date: startDate,
          end_date: endDate,
          ajax: 1
        });
        try {
          const res = await fetch(`{{ route('guru.dashboard.chartData') }}?${params}`);
          return await res.json();
        } catch (e) {
          console.error('Fetch error:', e);
          return null;
        }
      };

      // ============ DONUT CHART ============
      let donutChart;
      const renderDonutChart = (data) => {
        const total = data.reduce((sum, val) => sum + (val || 0), 0);
        const opts = {
          chart: {
            type: 'donut',
            height: 350
          },
          series: [data[0] || 0, data[1] || 0, data[2] || 0, data[3] || 0, data[4] || 0],
          labels: ['Sangat Senang', 'Senang', 'Netral', 'Sedih', 'Sangat Sedih'],
          colors: ['#5EA6FF', '#1C7DFF', '#1358D4', '#0B3BAA', '#00145C'],
          legend: {
            position: 'right',
            offsetY: 40,
            height: 230,
            fontSize: '14px',
            markers: {
              width: 12,
              height: 12,
              radius: 12
            }
          },
          dataLabels: {
            enabled: true,
            formatter: function(val) {
              return Math.round(val) + "%"
            }
          },
          plotOptions: {
            pie: {
              donut: {
                size: '65%',
                labels: {
                  show: true,
                  name: {
                    show: true,
                    fontSize: '14px',
                    fontWeight: 600,
                    color: '#64748b',
                    offsetY: -10
                  },
                  value: {
                    show: true,
                    fontSize: '32px',
                    fontWeight: 700,
                    color: '#010E82',
                    offsetY: 5,
                    formatter: function(val) {
                      return val
                    }
                  },
                  total: {
                    show: true,
                    showAlways: true,
                    label: 'Total Mood Checks',
                    fontSize: '14px',
                    fontWeight: 600,
                    color: '#64748b',
                    formatter: function(w) {
                      return total
                    }
                  }
                }
              }
            }
          }
        };
        if (donutChart) {
          donutChart.updateOptions(opts);
        } else {
          donutChart = new ApexCharts(document.querySelector('#guruMoodDonutChart'), opts);
          donutChart.render();
        }
      };

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
          barChart = new ApexCharts(document.querySelector('#guruMoodBarChart'), opts);
          barChart.render();
        }
      };

      // ============ RENDER INITIAL CHARTS ============
      // Load data via AJAX on page load
      const initialData = await fetchChartData(startDate, endDate);
      if (initialData) {
        renderDonutChart(initialData.moodChartData);
        renderBarChart(initialData.chartDateCategories, initialData.dailyMoodStacked);
      }

      // Update labels with initial dates
      const initialDates = [new Date(startDate), new Date(endDate)];
      document.getElementById('donutDateLabel').textContent = formatDateRange(initialDates);
      document.getElementById('barDateLabel').textContent = formatDateRange(initialDates);

      // ============ DONUT DATE PICKER ============
      flatpickr('#donutDatePicker', {
        mode: 'range',
        locale: 'id',
        dateFormat: 'Y-m-d',
        defaultDate: [startDate, endDate],
        onChange: async (dates) => {
          if (dates.length === 2) {
            document.getElementById('donutDateLabel').textContent = formatDateRange(dates);
            const data = await fetchChartData(
              formatDateToYMD(dates[0]),
              formatDateToYMD(dates[1])
            );
            if (data && data.moodChartData) {
              renderDonutChart(data.moodChartData);
            }
          }
        },
        onReady: (_, __, fp) => {
          document.getElementById('donutDateLabel').textContent = formatDateRange(fp.selectedDates);
        }
      });

      // ============ BAR DATE PICKER ============
      flatpickr('#barDatePicker', {
        mode: 'range',
        locale: 'id',
        dateFormat: 'Y-m-d',
        defaultDate: [startDate, endDate],
        onChange: async (dates) => {
          if (dates.length === 2) {
            document.getElementById('barDateLabel').textContent = formatDateRange(dates);
            const data = await fetchChartData(
              formatDateToYMD(dates[0]),
              formatDateToYMD(dates[1])
            );
            if (data && data.chartDateCategories && data.dailyMoodStacked) {
              renderBarChart(data.chartDateCategories, data.dailyMoodStacked);
            }
          }
        },
        onReady: (_, __, fp) => {
          document.getElementById('barDateLabel').textContent = formatDateRange(fp.selectedDates);
        }
      });

    });
  </script>
@endsection
