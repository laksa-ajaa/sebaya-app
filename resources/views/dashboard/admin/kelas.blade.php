@extends('layouts.app')

@section('title', 'Manajemen Kelas')

@section('content')
  <div class="px-6 py-6 bg-blue-100 min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
      <div>
        <a href="{{ route('admin.schools') }}"
          class="text-[#010E82] hover:text-[#0B3BAA] mb-2 inline-flex items-center text-sm">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Kembali ke Sekolah
        </a>
        <h1 class="text-3xl font-bold text-[#010E82]">Manajemen Kelas</h1>
        <p class="text-gray-600 mt-1">Sekolah: {{ $school->name }}</p>
      </div>
      <button onclick="openClassModal('create', {{ $school->id }})"
        class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
        + Tambah Kelas
      </button>
    </div>

    <!-- Success Message -->
    @if (session('success'))
      <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
      </div>
    @endif

    @if (session('error'))
      <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
      </div>
    @endif

    <!-- Statistik Card -->
    <div class="bg-white rounded-[15px] p-6 mb-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
      <div class="text-center">
        <p class="text-gray-600 text-sm mb-2">Total Kelas</p>
        <p class="text-3xl font-bold text-[#010E82]">{{ $classes->total() }}</p>
      </div>
    </div>

    <!-- Filter Data -->
    <div class="bg-white rounded-[15px] mb-6" style="box-shadow: 1px 2px 2px 0px #00000040; border: 1px solid #B3b7da;">
      <!-- Header Filter -->
      <div style="background-color: #5087E4;" class="px-6 py-3 rounded-t-[15px]">
        <h3 class="text-white font-semibold">Filter Data</h3>
      </div>

      <!-- Form Filter -->
      <form method="GET" action="{{ route('admin.sekolah.kelas.index', $school->id) }}" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Nama Kelas -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama kelas..."
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;">
          </div>

          <!-- Grade -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
            <input type="text" name="grade" value="{{ request('grade') }}" placeholder="Cari grade..."
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;">
          </div>
        </div>

        <div class="flex gap-3 mt-4">
          <button type="submit"
            class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
            Cari
          </button>
          @if ($search || request('grade'))
            <a href="{{ route('admin.sekolah.kelas.index', $school->id) }}"
              class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center">
              Reset
            </a>
          @endif
        </div>
      </form>
    </div>

    <!-- Tabel Kelas -->
    <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
      <!-- Dropdown Limit di atas tabel -->
      <div class="mb-4 flex items-center gap-2">
        <span class="text-sm text-gray-600">Tampilkan:</span>
        <select id="perPageSelect" onchange="changePerPage(this.value)"
          class="px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#010E82] focus:border-transparent">
          <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
          <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
          <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
        </select>
        <span class="text-sm text-gray-600">per halaman</span>
      </div>

      <div class="overflow-x-auto rounded-[15px]" style="border: 1px solid #B3b7da; position: relative;">
        <table class="min-w-full" style="border-collapse: separate; border-spacing: 0;">
          <thead style="background-color: #5087E4;">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Nama Kelas</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Grade</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-bottom: 1px solid #B3b7da;">
                Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white">
            @forelse($classes as $class)
              <tr class="hover:bg-gray-50" style="border-bottom: 1px solid #B3b7da;">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                  style="border-right: 1px solid #B3b7da;">
                  {{ $class->name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" style="border-right: 1px solid #B3b7da;">
                  {{ $class->grade ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center gap-2">
                    <a href="{{ route('admin.sekolah.kelas.show', [$school->id, $class->id]) }}"
                      class="inline-flex items-center justify-center w-8 h-8 text-[#010E82] hover:text-[#0B3BAA] hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-[#010E82] rounded-full transition-colors"
                      title="Detail">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                      </svg>
                    </a>
                    <button
                      onclick="openClassModal('edit', {{ $school->id }}, {{ $class->id }}, {{ json_encode($class->name) }}, {{ json_encode($class->grade ?? '') }})"
                      class="inline-flex items-center justify-center w-8 h-8 text-[#010E82] hover:text-[#0B3BAA] hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-[#010E82] rounded-full transition-colors"
                      title="Edit">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                      </svg>
                    </button>
                    <button onclick="deleteClass({{ $school->id }}, {{ $class->id }})"
                      class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 rounded-full transition-colors"
                      title="Hapus">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                  Tidak ada kelas ditemukan
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Footer dengan Info Data dan Pagination -->
      <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
        <!-- Kiri: Info Data -->
        <div class="text-sm text-gray-600">
          Menampilkan {{ $classes->firstItem() ?? 0 }} - {{ $classes->lastItem() ?? 0 }} dari
          {{ $classes->total() }} data
        </div>

        <!-- Kanan: Pagination -->
        @if ($classes->hasPages())
          <div>
            {{ $classes->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Modal Create/Edit Kelas -->
  <div id="classModal" class="hidden fixed inset-0 overflow-y-auto h-full w-full z-50"
    style="display: none; background-color: rgba(0, 0, 0, 0.6);">
    <div class="relative bg-white rounded-[15px] shadow-xl w-full max-w-md mx-4 my-8"
      style="box-shadow: 1px 2px 2px 0px #00000040;">
      <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-semibold text-[#010E82]" id="classModalTitle">Tambah Kelas</h3>
          <button onclick="closeClassModal()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form id="classForm" method="POST">
          @csrf
          <div id="classMethodField"></div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas *</label>
            <input type="text" name="name" id="className" required
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;">
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
            <input type="text" name="grade" id="classGrade" placeholder="contoh: 7, 8, 9"
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;">
          </div>

          <div class="flex justify-end gap-3">
            <button type="button" onclick="closeClassModal()"
              class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
              Batal
            </button>
            <button type="submit"
              class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Class Form -->
  <form id="deleteClassForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
  </form>

  <script>
    function openClassModal(action, schoolId, classId = null, name = '', grade = '') {
      const modal = document.getElementById('classModal');
      const form = document.getElementById('classForm');
      const methodField = document.getElementById('classMethodField');
      const title = document.getElementById('classModalTitle');

      if (action === 'create') {
        title.textContent = 'Tambah Kelas';
        form.action = '{{ route('admin.sekolah.kelas.store', ':school_id') }}'.replace(':school_id', schoolId);
        methodField.innerHTML = '';
        document.getElementById('className').value = '';
        document.getElementById('classGrade').value = '';
      } else {
        title.textContent = 'Edit Kelas';
        form.action = '{{ route('admin.sekolah.kelas.update', [':school_id', ':id']) }}'.replace(':school_id', schoolId)
          .replace(':id', classId);
        methodField.innerHTML = '@method('PUT')';
        document.getElementById('className').value = name;
        document.getElementById('classGrade').value = grade;
      }

      modal.classList.remove('hidden');
      modal.style.display = 'flex';
      modal.classList.add('items-center', 'justify-center');
    }

    function closeClassModal() {
      const modal = document.getElementById('classModal');
      modal.classList.add('hidden');
      modal.style.display = 'none';
    }

    function deleteClass(schoolId, id) {
      if (confirm('Apakah Anda yakin ingin menghapus kelas ini?')) {
        const form = document.getElementById('deleteClassForm');
        form.action = '{{ route('admin.sekolah.kelas.delete', [':school_id', ':id']) }}'.replace(':school_id', schoolId)
          .replace(':id', id);
        form.submit();
      }
    }

    function changePerPage(value) {
      const url = new URL(window.location.href);
      url.searchParams.set('per_page', value);
      window.location.href = url.toString();
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const classModal = document.getElementById('classModal');
      if (event.target == classModal) {
        closeClassModal();
      }
    }
  </script>
@endsection
