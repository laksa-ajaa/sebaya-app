@extends('layouts.app')

@section('title', 'Data Mood Check')

@section('content')

  <style>
    .mood-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;
    }

    .mood-5 {
      background: #DBEAFE;
      color: #1E40AF;
    }

    .mood-4 {
      background: #D1FAE5;
      color: #065F46;
    }

    .mood-3 {
      background: #FEF3C7;
      color: #92400E;
    }

    .mood-2 {
      background: #FED7AA;
      color: #9A3412;
    }

    .mood-1 {
      background: #FECACA;
      color: #991B1B;
    }

    .filter-card {
      background: white;
      border-radius: 15px;
      box-shadow: 1px 2px 2px 0px #00000040;
      border: 1px solid #B3B7DA;
      overflow: hidden;
    }

    .filter-header {
      background: linear-gradient(135deg, #5087E4 0%, #3B6FD4 100%);
      padding: 0.875rem 1.5rem;
    }

    .filter-header h3 {
      color: white;
      font-weight: 600;
      font-size: 0.9375rem;
    }

    .filter-body {
      padding: 1.25rem 1.5rem;
    }

    .data-card {
      background: white;
      border-radius: 15px;
      box-shadow: 1px 2px 2px 0px #00000040;
      overflow: hidden;
    }

    .data-header {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #E5E7EB;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .data-title h2 {
      font-size: 1rem;
      font-weight: 700;
      color: #111827;
    }

    .data-subtitle {
      font-size: 0.8125rem;
      color: #6B7280;
      margin-top: 0.125rem;
    }

    .table-container {
      overflow-x: auto;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
    }

    .data-table thead {
      background: linear-gradient(135deg, #5087E4 0%, #3B6FD4 100%);
    }

    .data-table th {
      padding: 0.875rem 1rem;
      text-align: left;
      font-size: 0.8125rem;
      font-weight: 600;
      color: white;
      white-space: nowrap;
    }

    .data-table th:first-child {
      border-radius: 0;
    }

    .data-table th:last-child {
      border-radius: 0;
    }

    .data-table td {
      padding: 0.875rem 1rem;
      font-size: 0.875rem;
      color: #374151;
      border-bottom: 1px solid #F3F4F6;
    }

    .data-table tbody tr:hover {
      background: #F9FAFB;
    }

    .data-table tbody tr:last-child td {
      border-bottom: none;
    }

    .btn-primary {
      background: linear-gradient(135deg, #10B981 0%, #059669 100%);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-size: 0.8125rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      transition: all 0.2s;
      border: none;
      cursor: pointer;
    }

    .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-filter {
      background: linear-gradient(135deg, #5087E4 0%, #3B6FD4 100%);
      color: white;
      padding: 0.625rem 1.5rem;
      border-radius: 25px;
      font-size: 0.875rem;
      font-weight: 600;
      border: none;
      cursor: pointer;
      width: 100%;
      transition: all 0.2s;
    }

    .btn-filter:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(80, 135, 228, 0.4);
    }

    .btn-action {
      background: linear-gradient(135deg, #5087E4 0%, #3B6FD4 100%);
      color: white;
      padding: 0.375rem 0.625rem;
      border-radius: 6px;
      font-size: 0.75rem;
      border: none;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-action:hover {
      transform: scale(1.05);
    }

    .input-field {
      width: 100%;
      padding: 0.625rem 0.875rem;
      border: 1px solid #010E82;
      border-radius: 8px;
      font-size: 0.875rem;
      transition: all 0.2s;
    }

    .input-field:focus {
      outline: none;
      border-color: #5087E4;
      box-shadow: 0 0 0 3px rgba(80, 135, 228, 0.15);
    }

    .pagination-info {
      padding: 0.75rem 1.5rem;
      background: #F9FAFB;
      border-top: 1px solid #E5E7EB;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .pagination-text {
      font-size: 0.8125rem;
      color: #6B7280;
    }

    .pagination-links {
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }

    .pagination-links a,
    .pagination-links span {
      padding: 0.375rem 0.75rem;
      border-radius: 6px;
      font-size: 0.8125rem;
      font-weight: 500;
      transition: all 0.2s;
    }

    .pagination-links a {
      color: #374151;
      background: white;
      border: 1px solid #E5E7EB;
    }

    .pagination-links a:hover {
      background: #F3F4F6;
      border-color: #D1D5DB;
    }

    .pagination-links .active {
      background: #5087E4;
      color: white;
      border: 1px solid #5087E4;
    }

    .pagination-links .disabled {
      color: #9CA3AF;
      background: #F9FAFB;
      cursor: not-allowed;
    }

    /* Modal */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 50;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s;
    }

    .modal-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .modal-content {
      background: white;
      border-radius: 16px;
      width: 90%;
      max-width: 600px;
      max-height: 90vh;
      overflow-y: auto;
      transform: scale(0.9);
      transition: transform 0.3s;
    }

    .modal-overlay.active .modal-content {
      transform: scale(1);
    }

    .modal-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #E5E7EB;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h3 {
      font-size: 1.125rem;
      font-weight: 700;
      color: #111827;
    }

    .modal-close {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      color: #6B7280;
      cursor: pointer;
      transition: all 0.2s;
    }

    .modal-close:hover {
      background: #F3F4F6;
      color: #111827;
    }

    .modal-body {
      padding: 1.5rem;
    }

    .detail-row {
      display: flex;
      padding: 0.75rem 0;
      border-bottom: 1px solid #F3F4F6;
    }

    .detail-row:last-child {
      border-bottom: none;
    }

    .detail-label {
      width: 140px;
      font-size: 0.875rem;
      font-weight: 500;
      color: #6B7280;
      flex-shrink: 0;
    }

    .detail-value {
      font-size: 0.875rem;
      color: #111827;
      flex: 1;
    }

    .ai-response-box {
      background: #F9FAFB;
      border-radius: 8px;
      padding: 1rem;
      font-size: 0.875rem;
      color: #374151;
      line-height: 1.6;
      max-height: 200px;
      overflow-y: auto;
    }
  </style>

  <div class="px-6 py-6 bg-blue-100 min-h-screen">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-[#010E82]">Data Mood Check</h1>
      <p class="text-gray-600 mt-1">Kelola dan monitor data mood check seluruh pengguna</p>
    </div>

    <!-- Filter Card -->
    <div class="filter-card mb-6">
      <div class="filter-header">
        <h3>Filter Data</h3>
      </div>
      <div class="filter-body">
        <form method="GET" action="{{ route('admin.mood-check') }}">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            <!-- Pilih Sekolah -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Sekolah</label>
              <select name="school_id" id="schoolFilter" class="input-field">
                <option value="">Semua Sekolah</option>
                @foreach ($schools as $school)
                  <option value="{{ $school->id }}" {{ $schoolId == $school->id ? 'selected' : '' }}>
                    {{ $school->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Pilih Kelas -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Kelas</label>
              <select name="class_id" id="classFilter" class="input-field">
                <option value="">Semua Kelas</option>
                @foreach ($classes as $class)
                  <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                    {{ $class->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Mood Level -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Mood</label>
              <select name="mood_level" class="input-field">
                <option value="all">Semua Mood</option>
                <option value="5" {{ $moodLevel == '5' ? 'selected' : '' }}>Sangat Senang</option>
                <option value="4" {{ $moodLevel == '4' ? 'selected' : '' }}>Senang</option>
                <option value="3" {{ $moodLevel == '3' ? 'selected' : '' }}>Netral</option>
                <option value="2" {{ $moodLevel == '2' ? 'selected' : '' }}>Sedih</option>
                <option value="1" {{ $moodLevel == '1' ? 'selected' : '' }}>Sangat Sedih</option>
              </select>
            </div>

            <!-- Periode -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Mulai</label>
              <input type="text" name="start_date" id="startDatePicker" class="input-field"
                value="{{ $startDate }}" placeholder="Pilih tanggal" readonly>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Akhir</label>
              <input type="text" name="end_date" id="endDatePicker" class="input-field" value="{{ $endDate }}"
                placeholder="Pilih tanggal" readonly>
            </div>

            <!-- Cari Nama -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Cari Nama</label>
              <input type="text" name="search" class="input-field" value="{{ $search }}"
                placeholder="Ketik nama siswa...">
            </div>
          </div>

          <button type="submit" class="btn-filter">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Filter
          </button>
        </form>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="data-card">
      <div class="data-header">
        <div class="data-title">
          <h2>Hasil Mood Check</h2>
          <p class="data-subtitle">Menampilkan {{ $moodChecks->total() }} data mood check</p>
        </div>
        <a href="{{ route('admin.mood-check.export', request()->query()) }}" class="btn-primary">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Export Data
        </a>
      </div>

      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th class="w-16">No</th>
              <th>Nama</th>
              <th>Kelas</th>
              <th>Tanggal Check-in</th>
              <th>Mood</th>
              <th class="w-24 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($moodChecks as $index => $check)
              <tr>
                <td class="text-center font-medium">{{ $moodChecks->firstItem() + $index }}</td>
                <td class="font-medium">{{ $check->user->name }}</td>
                <td>{{ $check->user->class->first()?->name ?? '-' }}</td>
                <td>{{ $check->date->format('d M Y') }}</td>
                <td>
                  @php
                    $moodLabels = [
                        5 => 'Sangat Senang',
                        4 => 'Senang',
                        3 => 'Netral',
                        2 => 'Sedih',
                        1 => 'Sangat Sedih',
                    ];
                  @endphp
                  <span class="mood-badge mood-{{ $check->mood_level }}">
                    {{ $moodLabels[$check->mood_level] ?? '-' }}
                  </span>
                </td>
                <td class="text-center">
                  <button type="button" class="btn-action"
                    onclick="showDetail({{ json_encode([
                        'name' => $check->user->name,
                        'class' => $check->user->class->first()?->name ?? '-',
                        'date' => $check->date->format('d M Y'),
                        'mood_level' => $check->mood_level,
                        'mood_label' => $moodLabels[$check->mood_level] ?? '-',
                        'ai_response' => $check->ai_response ?? '-',
                    ]) }})">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-8 text-gray-500">
                  <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Tidak ada data mood check ditemukan
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($moodChecks->hasPages())
        <div class="pagination-info">
          <span class="pagination-text">
            Menampilkan {{ $moodChecks->firstItem() }} - {{ $moodChecks->lastItem() }} dari {{ $moodChecks->total() }}
            data
          </span>
          <div class="pagination-links">
            {{-- Previous --}}
            @if ($moodChecks->onFirstPage())
              <span class="disabled">Previous</span>
            @else
              <a href="{{ $moodChecks->previousPageUrl() }}">Previous</a>
            @endif

            {{-- Page Numbers --}}
            @foreach ($moodChecks->getUrlRange(max(1, $moodChecks->currentPage() - 2), min($moodChecks->lastPage(), $moodChecks->currentPage() + 2)) as $page => $url)
              @if ($page == $moodChecks->currentPage())
                <span class="active">{{ $page }}</span>
              @else
                <a href="{{ $url }}">{{ $page }}</a>
              @endif
            @endforeach

            {{-- Next --}}
            @if ($moodChecks->hasMorePages())
              <a href="{{ $moodChecks->nextPageUrl() }}">Next</a>
            @else
              <span class="disabled">Next</span>
            @endif
          </div>
        </div>
      @endif
    </div>
  </div>

  <!-- Detail Modal -->
  <div id="detailModal" class="modal-overlay" onclick="closeModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
      <div class="modal-header">
        <h3>Detail Mood Check</h3>
        <button type="button" class="modal-close" onclick="closeDetailModal()">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="modal-body">
        <div class="detail-row">
          <span class="detail-label">Nama Siswa</span>
          <span class="detail-value" id="detail-name">-</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Kelas</span>
          <span class="detail-value" id="detail-class">-</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Tanggal</span>
          <span class="detail-value" id="detail-date">-</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Mood</span>
          <span class="detail-value" id="detail-mood">-</span>
        </div>
        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">AI Response</label>
          <div class="ai-response-box" id="detail-ai-response">-</div>
        </div>
      </div>
    </div>
  </div>


  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize Flatpickr for date inputs
      flatpickr('#startDatePicker', {
        locale: 'id',
        dateFormat: 'Y-m-d',
        allowInput: true
      });

      flatpickr('#endDatePicker', {
        locale: 'id',
        dateFormat: 'Y-m-d',
        allowInput: true
      });

      // School filter change - load classes via AJAX
      const schoolFilter = document.getElementById('schoolFilter');
      const classFilter = document.getElementById('classFilter');

      schoolFilter?.addEventListener('change', async function() {
        const schoolId = this.value;
        classFilter.innerHTML = '<option value="">Semua Kelas</option>';

        if (schoolId) {
          try {
            // Fetch classes for selected school
            const response = await fetch(`/admin/sekolah/${schoolId}/kelas`);
            const data = await response.json();

            if (data.classes) {
              data.classes.forEach(cls => {
                const option = document.createElement('option');
                option.value = cls.id;
                option.textContent = cls.name;
                classFilter.appendChild(option);
              });
            }
          } catch (e) {
            console.error('Error loading classes:', e);
          }
        }
      });
    });

    function showDetail(data) {
      document.getElementById('detail-name').textContent = data.name;
      document.getElementById('detail-class').textContent = data.class;
      document.getElementById('detail-date').textContent = data.date;

      const moodBadge = document.createElement('span');
      moodBadge.className = `mood-badge mood-${data.mood_level}`;
      moodBadge.textContent = data.mood_label;
      const moodContainer = document.getElementById('detail-mood');
      moodContainer.innerHTML = '';
      moodContainer.appendChild(moodBadge);

      document.getElementById('detail-ai-response').innerHTML = data.ai_response || '-';
      document.getElementById('detailModal').classList.add('active');
    }

    function closeDetailModal() {
      document.getElementById('detailModal').classList.remove('active');
    }

    function closeModal(event) {
      if (event.target.id === 'detailModal') {
        closeDetailModal();
      }
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeDetailModal();
      }
    });
  </script>
@endsection
