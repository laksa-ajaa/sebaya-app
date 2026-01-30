@extends('layouts.app')

@section('title', 'Data Screening Siswa')

@section('content')

    <style>
        /* ===== BADGE ===== */
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

        /* ===== CARD ===== */
        .filter-card,
        .data-card {
            background: white;
            border-radius: 15px;
            box-shadow: 1px 2px 2px 0px #00000040;
            border: 1px solid #B3B7DA;
            overflow: hidden;
        }

        .filter-header {
            background: linear-gradient(135deg, #5087E4 0%, #3B6FD4 100%);
            padding: .875rem 1.5rem;
        }

        .filter-header h3 {
            color: white;
            font-weight: 600;
            font-size: .9375rem;
        }

        .filter-body {
            padding: 1.25rem 1.5rem;
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

        /* ===== TABLE ===== */
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
            padding: .875rem 1rem;
            font-size: .8125rem;
            font-weight: 600;
            color: white;
            white-space: nowrap;
        }

        .data-table td {
            padding: .875rem 1rem;
            font-size: .875rem;
            color: #374151;
            border-bottom: 1px solid #F3F4F6;
        }

        .data-table tbody tr:hover {
            background: #F9FAFB;
        }

        /* ===== BUTTON ===== */
        .btn-filter {
            background: linear-gradient(135deg, #5087E4 0%, #3B6FD4 100%);
            color: white;
            padding: .625rem 1.5rem;
            border-radius: 25px;
            font-size: .875rem;
            font-weight: 600;
            border: none;
            width: 100%;
        }

        .btn-action {
            background: linear-gradient(135deg, #5087E4 0%, #3B6FD4 100%);
            color: white;
            padding: .375rem .625rem;
            border-radius: 6px;
            font-size: .75rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-action:hover {
            transform: scale(1.05);
        }

        /* ===== INPUT ===== */
        .input-field {
            width: 100%;
            padding: .625rem .875rem;
            border: 1px solid #010E82;
            border-radius: 8px;
            font-size: .875rem;
        }

        /* ===== MODAL ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
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
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            justify-content: space-between;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .detail-row {
            display: flex;
            padding: .75rem 0;
            border-bottom: 1px solid #F3F4F6;
        }

        .detail-label {
            width: 140px;
            font-size: .875rem;
            font-weight: 500;
            color: #6B7280;
        }

        .detail-value {
            font-size: .875rem;
            color: #111827;
            flex: 1;
        }

        .ai-response-box {
            background: #F9FAFB;
            border-radius: 8px;
            padding: 1rem;
            font-size: .875rem;
        }
    </style>

    <div class="px-6 py-6 bg-blue-100 min-h-screen">

        <!-- HEADER -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-[#010E82]">Data Screening Siswa</h1>
            <p class="text-gray-600 mt-1">Monitoring hasil screening kesehatan mental siswa</p>
        </div>

        <!-- FILTER -->
        <div class="filter-card mb-6">
            <div class="filter-header">
                <h3>Filter Data</h3>
            </div>
            <div class="filter-body">
                <form method="GET" action="{{ route('guru.laporan.screening-report') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">

                        <!-- KELAS -->
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Kelas</label>
                            <select name="class_id" class="input-field">
                                <option value="">Semua Kelas</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}"
                                        {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- PAKET -->
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Paket Screening</label>
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

                        <!-- SEARCH -->
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Cari Nama</label>
                            <input type="text" name="search" class="input-field" value="{{ request('search') }}"
                                placeholder="Nama siswa...">
                        </div>

                    </div>

                    <button type="submit" class="btn-filter">Filter</button>
                </form>
            </div>
        </div>

        <!-- TABLE -->
        <div class="data-card">
            <div class="data-header">
                <div class="data-title">
                    <h2>Hasil Screening Siswa</h2>
                    <p class="data-subtitle">Menampilkan {{ $sessions->total() }} data screening</p>
                </div>
                <a href="{{ route('guru.laporan.screening-report.export', request()->query()) }}" class="btn-primary">
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
                            <th>No</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Paket</th>
                            <th>Tanggal Submit</th>
                            <th>Status</th>
                            <th>Interpretasi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $index => $session)
                            <tr>
                                <td>{{ $sessions->firstItem() + $index }}</td>
                                <td>{{ $session->user->name }}</td>
                                <td>{{ $session->user?->class?->first()?->name ?? '-' }}</td>
                                <td>{{ $session->package->name }}</td>
                                <td>{{ $session->submitted_at?->format('d M Y') ?? '-' }}</td>
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
                                    <div class="inline-flex items-center gap-2">
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
                                        <button class="btn-action"
                                            style="background:linear-gradient(135deg,#10B981,#059669)"
                                            onclick="showScheduleModal({{ json_encode([
                                                'student_id' => $session->user->id,
                                                'name' => $session->user->name,
                                                'class_id' => $session->user?->class?->first()?->id ?? null,
                                                'class_name' => $session->user?->class?->first()?->name ?? '-',
                                            ]) }})">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500">
                                    Tidak ada data screening
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($sessions->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Menampilkan {{ $sessions->firstItem() ?? 0 }} - {{ $sessions->lastItem() ?? 0 }} dari
                            {{ $sessions->total() }} data
                        </div>
                        <div>
                            {{ $sessions->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL DETAIL -->
    <div id="detailModal" class="modal-overlay" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3>Detail Hasil Screening</h3>
                <button onclick="closeDetailModal()">✕</button>
            </div>
            <div class="modal-body">
                <div class="detail-row"><span class="detail-label">Nama</span><span id="d-name"></span></div>
                <div class="detail-row"><span class="detail-label">Kelas</span><span id="d-class"></span></div>
                <div class="detail-row"><span class="detail-label">Paket</span><span id="d-package"></span></div>
                <div class="detail-row"><span class="detail-label">Tanggal</span><span id="d-date"></span></div>
                <hr class="my-4">
                <div id="d-details"></div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-2">Rekomendasi</label>
                    <div class="ai-response-box" id="d-recommendation"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCHEDULE MODAL -->
    <div id="scheduleModal" class="modal-overlay" onclick="closeScheduleModalEvent(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3>Atur Jadwal</h3>
                <button onclick="closeScheduleModal()">✕</button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <input type="hidden" name="student_id" id="sched_student_id">
                    <input type="hidden" name="class_id" id="sched_class_id">
                    <div class="detail-row"><span class="detail-label">Nama Siswa</span><span id="sched_name"
                            class="detail-value"></span></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Tanggal <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="date" id="sched_date" class="input-field" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Waktu <span
                                    class="text-red-500">*</span></label>
                            <input type="time" name="time" id="sched_time" class="input-field" required>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Pesan</label>
                        <textarea name="message" id="sched_message" rows="4" class="input-field"
                            placeholder="Contoh: Halo, temui saya di ruang BK sesuai jadwal"></textarea>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn-filter">Simpan Pesan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showScreeningDetail(data) {
            document.getElementById('d-name').textContent = data.name;
            document.getElementById('d-class').textContent = data.class;
            document.getElementById('d-package').textContent = data.package;
            document.getElementById('d-date').textContent = data.date;
            const c = document.getElementById('d-details');
            c.innerHTML = '';
            data.details.forEach(d => {
                c.innerHTML += `<div class="detail-row">
      <span class="detail-label">${d.name}</span>
      <span class="detail-value">${d.score} (${d.interpretation})</span>
    </div>`;
            });
            document.getElementById('d-recommendation').textContent = data.recommendation;
            document.getElementById('detailModal').classList.add('active');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.remove('active');
        }

        function closeModal(e) {
            if (e.target.id === 'detailModal') closeDetailModal();
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeDetailModal();
        });
    </script>

    <script>
        function showScheduleModal(data) {
            document.getElementById('sched_student_id').value = data.student_id || '';
            document.getElementById('sched_class_id').value = data.class_id || '';
            document.getElementById('sched_name').textContent = data.name || '';
            document.getElementById('sched_date').value = '';
            document.getElementById('sched_time').value = '';
            document.getElementById('sched_message').value = '';
            document.getElementById('scheduleModal').classList.add('active');
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').classList.remove('active');
        }

        function closeScheduleModalEvent(e) {
            if (e.target.id === 'scheduleModal') closeScheduleModal();
        }

        document.getElementById('scheduleForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const payload = {
                student_id: document.getElementById('sched_student_id').value || null,
                class_id: document.getElementById('sched_class_id').value || null,
                date: document.getElementById('sched_date').value,
                time: document.getElementById('sched_time').value,
                message: document.getElementById('sched_message').value,
            };
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const res = await fetch("{{ route('guru.dashboard.schedule.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                if (!res.ok) throw new Error('Network response was not ok');
                await res.json().catch(() => ({}));
                closeScheduleModal();
                toast('Jadwal berhasil disimpan.', 'success');
            } catch (err) {
                console.error(err);
                showAlert('Gagal menyimpan jadwal.', 'error');
            }
        });
    </script>

@endsection
