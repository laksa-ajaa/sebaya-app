@extends('layouts.app')

@section('title', 'Data Screening')

@section('content')

    <style>
        .screening-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .screening-5 {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .screening-4 {
            background: #D1FAE5;
            color: #065F46;
        }

        .screening-3 {
            background: #FEF3C7;
            color: #92400E;
        }

        .screening-2 {
            background: #FED7AA;
            color: #9A3412;
        }

        .screening-1 {
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
            <h1 class="text-2xl font-bold text-[#010E82]">Data Screening</h1>
            <p class="text-gray-600 mt-1">Monitoring hasil screening kesehatan mental user</p>
        </div>

        <!-- Filter Card -->
        <div class="filter-card mb-6">
            <div class="filter-header">
                <h3>Filter Data</h3>
            </div>
            <div class="filter-body">
                <form method="GET" action="{{ route('admin.laporan.screening-report') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">

                        <!-- Sekolah -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Sekolah</label>
                            <select name="school_id" id="schoolFilter" class="input-field">
                                <option value="">Semua Sekolah</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}"
                                        {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Kelas -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Kelas</label>
                            <select name="class_id" id="classFilter" class="input-field">
                                <option value="">Semua Kelas</option>
                                @foreach ($classes ?? [] as $class)
                                    <option value="{{ $class->id }}"
                                        {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Paket Screening -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Paket Screening</label>
                            <select name="package_id" class="input-field">
                                <option value="">Semua Paket</option>
                                @foreach ($packages as $pkg)
                                    <option value="{{ $pkg->id }}"
                                        {{ request('package_id') == $pkg->id ? 'selected' : '' }}>
                                        {{ $pkg->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Cari Nama -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Cari Nama</label>
                            <input type="text" name="search" class="input-field" value="{{ request('search') }}"
                                placeholder="Nama siswa...">
                        </div>

                    </div>

                    <button type="submit" class="btn-filter">Filter</button>
                </form>

            </div>
        </div>

        <!-- Data Table Card -->
        <div class="data-card">
            <div class="data-header">
                <div class="data-title">
                    <h2>Hasil Screening</h2>
                    <p class="data-subtitle">Menampilkan {{ $sessions->total() }} data screening</p>
                </div>
                <a href="{{ route('admin.laporan.screening-report.export', request()->query()) }}" class="btn-primary">
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
                            <th>Email</th>
                            <th>Sekolah</th>
                            <th>Kelas</th>
                            <th>Paket</th>
                            <th>Tanggal Submit</th>
                            <th>Status</th>
                            <th>Interpretasi</th>
                            <th class="w-24 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($sessions as $index => $session)
                            <tr>
                                <td class="text-center">{{ $sessions->firstItem() + $index }}</td>
                                <td class="font-medium">{{ $session->user->name ?? '-' }}</td>
                                <td>{{ $session->user->email ?? '-' }}</td>
                                <td>{{ $session->user?->class?->first()?->school?->name ?? '-' }}</td>
                                <td>{{ $session->user?->class?->first()?->name ?? '-' }}</td>
                                <td>{{ $session->package->name ?? '-' }}</td>

                                <td>
                                    {{ $session->submitted_at ? $session->submitted_at->format('d M Y') : '-' }}
                                </td>

                                <td>
                                    @if ($session->submitted_at)
                                        <span class="screening-badge screening-4">Submitted</span>
                                    @else
                                        <span class="screening-badge screening-3">Active</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="screening-badge screening-{{ $session->overall['level'] }}">
                                        {{ $session->overall['label'] }}
                                    </span>
                                </td>


                                <td class="text-center">
                                    <button class="btn-action"
                                        onclick="showScreeningDetail({{ json_encode([
                                            'name' => $session->user->name,
                                            'class' => $session->user?->class?->first()?->name ?? '-',
                                            'package' => $session->package->name,
                                            'date' => optional($session->submitted_at)->format('d M Y'),
                                            'details' => $session->overall['details'],
                                            'recommendation' => $session->overall['recommendation'],
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
                                <td colspan="10" class="text-center py-8 text-gray-500">
                                    Tidak ada data screening
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

            @if ($sessions->hasPages())
                <div class="pagination-info">
                    <span class="pagination-text">
                        Menampilkan {{ $sessions->firstItem() }} - {{ $sessions->lastItem() }} dari
                        {{ $sessions->total() }}
                        data
                    </span>
                    <div class="pagination-links">
                        {{-- Previous --}}
                        @if ($sessions->onFirstPage())
                            <span class="disabled">Previous</span>
                        @else
                            <a href="{{ $sessions->previousPageUrl() }}">Previous</a>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach ($sessions->getUrlRange(max(1, $sessions->currentPage() - 2), min($sessions->lastPage(), $sessions->currentPage() + 2)) as $page => $url)
                            @if ($page == $sessions->currentPage())
                                <span class="active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next --}}
                        @if ($sessions->hasMorePages())
                            <a href="{{ $sessions->nextPageUrl() }}">Next</a>
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
                <h3>Detail Hasil Screening</h3>
                <button class="modal-close" onclick="closeDetailModal()">✕</button>
            </div>

            <div class="modal-body">
                <div class="detail-row">
                    <span class="detail-label">Nama</span>
                    <span class="detail-value" id="d-name"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Kelas</span>
                    <span class="detail-value" id="d-class"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Paket</span>
                    <span class="detail-value" id="d-package"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tanggal</span>
                    <span class="detail-value" id="d-date"></span>
                </div>

                <hr class="my-4">

                <div id="d-details"></div>

                <div class="mt-4">
                    <label class="block text-sm font-medium mb-2">Rekomendasi</label>
                    <div class="ai-response-box" id="d-recommendation"></div>
                </div>
            </div>
        </div>
    </div>



    <script>
        /* ===============================
         * OPTIONAL: Date Picker
         * (aktifkan kalau ada filter tanggal)
         * =============================== */
        if (window.flatpickr) {
            if (document.getElementById('startDatePicker')) {
                flatpickr('#startDatePicker', {
                    locale: 'id',
                    dateFormat: 'Y-m-d',
                    allowInput: true
                });
            }

            if (document.getElementById('endDatePicker')) {
                flatpickr('#endDatePicker', {
                    locale: 'id',
                    dateFormat: 'Y-m-d',
                    allowInput: true
                });
            }
        }

        /* ===============================
         * Sekolah → Kelas (AJAX)
         * =============================== */
        const schoolFilter = document.getElementById('schoolFilter');
        const classFilter = document.getElementById('classFilter');

        if (schoolFilter && classFilter) {
            schoolFilter.addEventListener('change', async function() {
                const schoolId = this.value;

                classFilter.innerHTML = '<option value="">Semua Kelas</option>';

                if (!schoolId) return;

                try {
                    const res = await fetch(`/admin/sekolah/${schoolId}/kelas`);
                    const data = await res.json();

                    if (Array.isArray(data.classes)) {
                        data.classes.forEach(cls => {
                            const opt = document.createElement('option');
                            opt.value = cls.id;
                            opt.textContent = cls.name;
                            classFilter.appendChild(opt);
                        });
                    }
                } catch (err) {
                    console.error('Gagal load kelas:', err);
                }
            });
        }


        /* ===============================
         * MODAL SCREENING DETAIL
         * =============================== */

        function showScreeningDetail(data) {
            // Basic info
            document.getElementById('d-name').textContent = data.name ?? '-';
            document.getElementById('d-class').textContent = data.class ?? '-';
            document.getElementById('d-package').textContent = data.package ?? '-';
            document.getElementById('d-date').textContent = data.date ?? '-';

            // Detail dimensi (D / A / S)
            const detailContainer = document.getElementById('d-details');
            detailContainer.innerHTML = '';

            if (Array.isArray(data.details)) {
                data.details.forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'detail-row';

                    row.innerHTML = `
        <span class="detail-label">${item.name}</span>
        <span class="detail-value">
          <strong>${item.score}</strong>
          <span style="color:#6B7280">(${item.interpretation})</span>
        </span>
      `;

                    detailContainer.appendChild(row);
                });
            } else {
                detailContainer.innerHTML = `
      <div class="detail-row">
        <span class="detail-value text-gray-500">Detail tidak tersedia</span>
      </div>
    `;
            }

            // Recommendation
            document.getElementById('d-recommendation').textContent =
                data.recommendation ?? '-';

            // Show modal
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

        /* ===============================
         * ESC KEY HANDLER
         * =============================== */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDetailModal();
            }
        });
    </script>

@endsection
