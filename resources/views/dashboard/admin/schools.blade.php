@extends('layouts.app')

@section('title', 'Manajemen Sekolah')

@section('content')
  <div class="px-6 py-6 bg-blue-100 min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#010E82]">Manajemen Sekolah</h1>
        <p class="text-gray-600 mt-1">Kelola data sekolah</p>
      </div>
      <button onclick="openModal('create')"
        class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
        + Tambah Sekolah
      </button>
    </div>

    <!-- Success Message -->
    @if (session('success'))
      <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
      </div>
    @endif

    <!-- Statistik Card -->
    <div class="bg-white rounded-[15px] p-6 mb-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
      <div class="text-center">
        <p class="text-gray-600 text-sm mb-2">Total Sekolah</p>
        <p class="text-3xl font-bold text-[#010E82]">{{ number_format($totalSchools) }}</p>
      </div>
    </div>

    <!-- Filter Data -->
    <div class="bg-white rounded-[15px] mb-6" style="box-shadow: 1px 2px 2px 0px #00000040; border: 1px solid #B3b7da;">
      <!-- Header Filter -->
      <div style="background-color: #5087E4;" class="px-6 py-3 rounded-t-[15px]">
        <h3 class="text-white font-semibold">Filter Data</h3>
      </div>

      <!-- Form Filter -->
      <form method="GET" action="{{ route('admin.schools') }}" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
          <!-- Nama atau Kode Sekolah -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sekolah</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama sekolah..."
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;">
          </div>
        </div>

        <div class="flex gap-3 mt-4">
          <button type="submit"
            class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
            Cari
          </button>
          @if ($search)
            <a href="{{ route('admin.schools') }}"
              class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center">
              Reset
            </a>
          @endif
        </div>
      </form>
    </div>

    <!-- Tabel Sekolah -->
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
                Nama Sekolah
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                NPSN
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Alamat
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Guru Bertanggung Jawab
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Telepon
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-bottom: 1px solid #B3b7da;">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="bg-white">
            @forelse($schools as $school)
              <!-- Row Sekolah -->
              <tr class="hover:bg-gray-50" style="border-bottom: 1px solid #B3b7da;">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                  style="border-right: 1px solid #B3b7da;">
                  {{ $school->name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" style="border-right: 1px solid #B3b7da;">
                  {{ $school->npsn ?? '-' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500" style="border-right: 1px solid #B3b7da;">
                  {{ $school->address ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" style="border-right: 1px solid #B3b7da;">
                  @if ($school->admins->count() > 0)
                    <div class="space-y-1">
                      @foreach ($school->admins as $admin)
                        <div class="text-gray-700 font-medium">{{ $admin->name }}</div>
                      @endforeach
                    </div>
                  @else
                    <span class="text-gray-400">-</span>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" style="border-right: 1px solid #B3b7da;">
                  {{ $school->phone ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center gap-2">
                    <a href="{{ route('admin.sekolah.kelas.index', $school->id) }}"
                      class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full transition-colors"
                      title="Lihat Kelas">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                      </svg>
                    </a>
                    <button
                      onclick="openModal('edit', {{ $school->id }}, {{ json_encode($school->name) }}, {{ json_encode($school->npsn ?? '') }}, {{ json_encode($school->address ?? '') }}, {{ json_encode($school->phone ?? '') }})"
                      class="inline-flex items-center justify-center w-8 h-8 text-[#010E82] hover:text-[#0B3BAA] hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-[#010E82] rounded-full transition-colors"
                      title="Edit">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                      </svg>
                    </button>
                    <button onclick="deleteSchool({{ $school->id }})"
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
                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                  Tidak ada sekolah ditemukan
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
          Menampilkan {{ $schools->firstItem() ?? 0 }} - {{ $schools->lastItem() ?? 0 }} dari
          {{ $schools->total() }} data
        </div>

        <!-- Kanan: Pagination -->
        @if ($schools->hasPages())
          <div>
            {{ $schools->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Modal Create/Edit Sekolah -->
  <div id="schoolModal" class="hidden fixed inset-0 overflow-y-auto h-full w-full z-50"
    style="display: none; background-color: rgba(0, 0, 0, 0.6);">
    <div class="relative bg-white rounded-[15px] shadow-xl w-full max-w-md mx-4 my-8"
      style="box-shadow: 1px 2px 2px 0px #00000040;">
      <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-semibold text-[#010E82]" id="modalTitle">Tambah Sekolah</h3>
          <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form id="schoolForm" method="POST">
          @csrf
          <div id="methodField"></div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sekolah *</label>
            <input type="text" name="name" id="schoolName" required
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;">
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">NPSN</label>
            <input type="text" name="npsn" id="schoolNpsn"
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;">
          </div>

          <!-- school code is auto-generated; no input required -->

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
            <textarea name="address" id="schoolAddress" rows="3"
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;"></textarea>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
            <input type="text" name="phone" id="schoolPhone"
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;">
          </div>

          <div class="flex justify-end gap-3">
            <button type="button" onclick="closeModal()"
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

  <!-- Delete Form -->
  <form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
  </form>

  <script>
    function openModal(action, id = null, name = '', npsn = '', address = '', phone = '') {
      const modal = document.getElementById('schoolModal');
      const form = document.getElementById('schoolForm');
      const methodField = document.getElementById('methodField');
      const title = document.getElementById('modalTitle');

      if (action === 'create') {
        title.textContent = 'Tambah Sekolah';
        form.action = '{{ route('admin.sekolah.store') }}';
        methodField.innerHTML = '';
        document.getElementById('schoolName').value = '';
        document.getElementById('schoolNpsn').value = '';
        document.getElementById('schoolAddress').value = '';
        document.getElementById('schoolPhone').value = '';
      } else {
        title.textContent = 'Edit Sekolah';
        form.action = '{{ route('admin.sekolah.update', ':id') }}'.replace(':id', id);
        methodField.innerHTML = '@method('PUT')';
        document.getElementById('schoolName').value = name;
        document.getElementById('schoolNpsn').value = npsn;
        document.getElementById('schoolAddress').value = address;
        document.getElementById('schoolPhone').value = phone;
      }

      modal.classList.remove('hidden');
      modal.style.display = 'flex';
      modal.classList.add('items-center', 'justify-center');
    }

    function closeModal() {
      const modal = document.getElementById('schoolModal');
      modal.classList.add('hidden');
      modal.style.display = 'none';
    }

    function deleteSchool(id) {
      if (confirm('Apakah Anda yakin ingin menghapus sekolah ini?')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route('admin.sekolah.delete', ':id') }}'.replace(':id', id);
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
      const schoolModal = document.getElementById('schoolModal');
      if (event.target == schoolModal) {
        closeModal();
      }
    }
  </script>
@endsection
