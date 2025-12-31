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
                  <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
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
                  <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>
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
                  <button class="btn-action"
                    onclick="showScreeningDetail({{ json_encode([
                        'name' => $session->user->name,
                        'class' => $session->user?->class?->first()?->name ?? '-',
                        'package' => $session->package->name,
                        'date' => optional($session->submitted_at)->format('d M Y'),
                        'details' => $session->overall['details'],
                        'recommendation' => $session->overall['recommendation'],
                    ]) }})">
                    👁️
                  </button>
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

@endsection
