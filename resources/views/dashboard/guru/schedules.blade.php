@extends('layouts.app')

@section('title', 'Atur Jadwal')

@section('content')

<style>
    /* Table & Card Styles */
    .data-card {
      background: white;
      border-radius: 15px;
      box-shadow: 1px 2px 2px 0px #00000040;
      border: 1px solid #B3B7DA;
      overflow: hidden;
    }
    .data-table {
      width: 100%;
      border-collapse: collapse;
      background: white;
    }
    .data-table thead th {
      background: #5087E4;
      color: white;
      font-size: 0.875rem;
      font-weight: 600;
      padding: 12px 16px;
      text-align: center;
      border: 1px solid #3b6fd4;
      white-space: nowrap;
    }
    .data-table tbody td {
      padding: 12px 16px;
      font-size: 0.875rem;
      color: #374151;
      text-align: center;
      vertical-align: middle;
      border: 1px solid #E5E7EB;
    }
    .data-table tbody tr:hover {
      background: #F9FAFB;
    }

    /* Badge Styles from Design */
    .badge-aktif {
        background-color: #1CC642;
        color: white;
        padding: 4px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-block;
    }
    .badge-selesai {
        background-color: #5087E4;
        color: white;
        padding: 4px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-block;
    }
    .btn-edit-square {
        background-color: #7CA5F8;
        color: white;
        border-radius: 6px;
        padding: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    .btn-edit-square:hover {
        background-color: #5087E4;
    }

    /* Modal Styles */
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
      max-width: 500px;
      padding: 0;
      overflow: hidden;
      transform: scale(0.9);
      transition: transform 0.3s;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .modal-overlay.active .modal-content {
      transform: scale(1);
    }
    .modal-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #E5E7EB;
      background: #F9FAFB;
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
        color: #6B7280;
        cursor: pointer;
        transition: color 0.2s;
    }
    .modal-close:hover {
        color: #111827;
    }
    .modal-body {
      padding: 1.5rem;
    }
    
    /* Form Styles */
    .form-group {
        margin-bottom: 1rem;
    }
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    .form-input {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1px solid #D1D5DB;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    .form-input:focus {
        outline: none;
        border-color: #5087E4;
        box-shadow: 0 0 0 3px rgba(80, 135, 228, 0.15);
    }
</style>

<div class="px-6 py-6 bg-blue-100 min-h-screen">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#010E82]">Atur Jadwal</h1>
        <p class="text-sm text-gray-600">Kelola jadwal pertemuan dengan siswa.</p>
    </div>

    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Filter -->
    <div class="bg-white rounded-[15px] mb-6 shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-[#5087E4] px-6 py-3">
            <h3 class="text-white font-semibold text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter Data
            </h3>
        </div>
        <form id="filterForm" class="p-6">
            <div class="flex flex-wrap items-end gap-4">
                <!-- Search -->
                <div class="w-full md:w-64">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Cari Nama</label>
                    <input type="text" name="search" id="filterSearch" value="{{ $search }}" placeholder="Ketik nama siswa..."
                        class="w-full px-4 py-2 rounded-lg text-sm border border-gray-300 focus:ring-2 focus:ring-[#5087E4] focus:border-[#5087E4] outline-none transition-all">
                </div>

                <!-- Class Filter -->
                <div class="w-full md:w-48">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Kelas</label>
                    <select name="class_id" id="filterClass" class="w-full px-4 py-2 rounded-lg text-sm border border-gray-300 focus:ring-2 focus:ring-[#5087E4] focus:border-[#5087E4] outline-none transition-all cursor-pointer">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Filter -->
                <div class="w-full md:w-48">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Tanggal</label>
                    <div class="relative">
                        <input type="text" name="date" id="filterDate" value="{{ $selectedDate }}" placeholder="Pilih tanggal..."
                            class="w-full px-4 py-2 rounded-lg text-sm border border-gray-300 focus:ring-2 focus:ring-[#5087E4] focus:border-[#5087E4] outline-none transition-all bg-white">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="w-full md:w-40">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" id="filterStatus" class="w-full px-4 py-2 rounded-lg text-sm border border-gray-300 focus:ring-2 focus:ring-[#5087E4] focus:border-[#5087E4] outline-none transition-all cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="upcoming" {{ $selectedStatus == 'upcoming' ? 'selected' : '' }}>Aktif</option>
                        <option value="finished" {{ $selectedStatus == 'finished' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="w-full md:w-auto">
                    <button type="button" onclick="resetFilters()" class="w-full md:w-auto px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-200 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Reset
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="data-card bg-white p-6">
        <div class="mb-4 flex items-center gap-2">
            <span class="text-sm text-gray-600">Tampilkan:</span>
            <select id="filterLimit" class="px-3 py-1 border border-gray-300 rounded-lg text-sm bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
            </select>
            <span class="text-sm text-gray-600">per halaman</span>
        </div>

        <div class="overflow-x-auto border rounded-lg">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Tanggal Konsultasi</th>
                        <th>Waktu Konsultasi</th>
                        <th>Status</th>
                        <th style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="schedulesTableBody">
                    @include('dashboard.guru.schedules_table')
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-4 bg-white">
            <div id="paginationInfo" class="text-xs text-gray-500 font-medium italic">
                Menampilkan {{ $schedules->firstItem() ?? 0 }}-{{ $schedules->lastItem() ?? 0 }} dari {{ $schedules->total() }} Data
            </div>
            <div id="paginationLinks">
                {{ $schedules->appends(['per_page' => $perPage, 'search' => $search, 'class_id' => $selectedClassId, 'date' => $selectedDate])->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay" onclick="closeModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Atur Ulang Jadwal</h3>
            <button type="button" class="modal-close" onclick="closeEditModal()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="modal-body">
            <!-- Student/Class Info Box -->
            <div class="mb-6 p-4 bg-blue-50 rounded-xl border border-blue-100 flex justify-between items-center">
                <div>
                    <label class="block text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-0.5">Nama Siswa</label>
                    <p id="modalInfoName" class="text-sm font-bold text-gray-800">-</p>
                </div>
                <div class="text-right">
                    <label class="block text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-0.5">Kelas</label>
                    <p id="modalInfoClass" class="text-sm font-bold text-gray-800">-</p>
                </div>
            </div>

            <!-- Update Form -->
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" id="editDate" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Jam</label>
                    <input type="time" name="time" id="editTime" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Pesan</label>
                    <textarea name="message" id="editMessage" rows="3" class="form-input"></textarea>
                </div>
            </form>

            <!-- Actions Footer -->
            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-100">
                <!-- Finish Form (Left) -->
                <form id="finishForm" method="POST" onsubmit="confirmFinish(event)">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none shadow-sm transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Sudahi Pertemuan
                    </button>
                </form>

                <!-- Update Actions (Right) -->
                <div class="flex gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none transition-colors">
                        Batal
                    </button>
                    <!-- Trigger Edit Form Submit -->
                    <button type="button" onclick="document.getElementById('editForm').submit()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none shadow-sm transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let searchTimer;
    let datePicker;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Flatpickr
        datePicker = flatpickr("#filterDate", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d F Y",
            allowInput: true,
            locale: "id",
            onChange: function(selectedDates, dateStr) {
                fetchSchedules(1);
            }
        });

        // Initial bind
        bindPagination();
    });

    function openEditModal(schedule) {
        const modal = document.getElementById('editModal');
        const editForm = document.getElementById('editForm');
        const finishForm = document.getElementById('finishForm');
        
        const dt = new Date(schedule.scheduled_at);
        const dateStr = dt.toISOString().split('T')[0];
        const timeStr = dt.toTimeString().split(' ')[0].substring(0,5);

        document.getElementById('editDate').value = dateStr;
        document.getElementById('editTime').value = timeStr;
        document.getElementById('editMessage').value = schedule.message || '';

        // Populate Name & Class Info
        let targetName = 'Semua Siswa';
        let targetClass = '-';

        if (schedule.student) {
            targetName = schedule.student.name;
            targetClass = (schedule.student.class && schedule.student.class.length > 0) 
                          ? schedule.student.class[0].name 
                          : '-';
        } else if (schedule.kelas) {
            targetName = schedule.kelas.name;
            targetClass = schedule.kelas.name;
        }

        document.getElementById('modalInfoName').textContent = targetName;
        document.getElementById('modalInfoClass').textContent = targetClass;
        
        // Update both form actions
        editForm.action = "{{ route('guru.dashboard.schedule.update', ':id') }}".replace(':id', schedule.id);
        finishForm.action = "{{ route('guru.dashboard.schedule.finish', ':id') }}".replace(':id', schedule.id);
        
        modal.classList.add('active');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    function closeModal(event) {
        if (event.target.id === 'editModal') {
            closeEditModal();
        }
    }

    function fetchSchedules(page = 1) {
        const search = document.getElementById('filterSearch').value;
        const classId = document.getElementById('filterClass').value;
        const date = document.getElementById('filterDate').value;
        const status = document.getElementById('filterStatus').value;
        const limit = document.getElementById('filterLimit').value;

        const url = new URL("{{ route('guru.dashboard.schedules.index') }}");
        url.searchParams.set('page', page);
        url.searchParams.set('search', search);
        url.searchParams.set('class_id', classId);
        url.searchParams.set('date', date);
        url.searchParams.set('status', status);
        url.searchParams.set('per_page', limit);

        // Update URL in browser for bookmarking
        window.history.pushState({}, '', url.toString());

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('schedulesTableBody').innerHTML = data.html;
            document.getElementById('paginationLinks').innerHTML = data.pagination;
            document.getElementById('paginationInfo').innerText = data.info;
            
            // Re-bind pagination click events
            bindPagination();
        })
        .catch(error => console.error('Error fetching schedules:', error));
    }

    function resetFilters() {
        document.getElementById('filterSearch').value = '';
        document.getElementById('filterClass').value = '';
        if (datePicker) datePicker.clear();
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterLimit').value = '10';
        fetchSchedules(1);
    }

    function bindPagination() {
        document.querySelectorAll('#paginationLinks a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const pageNum = url.searchParams.get('page');
                fetchSchedules(pageNum);
            });
        });
    }

    // Event Listeners
    document.getElementById('filterSearch').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => fetchSchedules(1), 500);
    });

    document.getElementById('filterClass').addEventListener('change', () => fetchSchedules(1));
    document.getElementById('filterStatus').addEventListener('change', () => fetchSchedules(1));
    document.getElementById('filterLimit').addEventListener('change', () => fetchSchedules(1));

    // Handle back/forward browser buttons
    window.addEventListener('popstate', function() {
        const url = new URL(window.location.href);
        document.getElementById('filterSearch').value = url.searchParams.get('search') || '';
        document.getElementById('filterClass').value = url.searchParams.get('class_id') || '';
        const dateVal = url.searchParams.get('date') || '';
        if (datePicker) datePicker.setDate(dateVal);
        document.getElementById('filterStatus').value = url.searchParams.get('status') || '';
        document.getElementById('filterLimit').value = url.searchParams.get('per_page') || '10';
        fetchSchedules(1);
    });

    // Escape key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditModal();
        }
    });

    function confirmFinish(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Sudahi Pertemuan?',
            text: "Jadwal akan ditandai sebagai selesai.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Selesai!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                event.target.submit();
            }
        });
    }
</script>
@endsection
