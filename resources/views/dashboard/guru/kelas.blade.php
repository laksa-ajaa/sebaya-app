@extends('layouts.app')

@section('title', 'Manajemen Kelas')

@section('content')
  <div class="px-6 py-6 bg-blue-100 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#010E82]">Manajemen Kelas</h1>
        <p class="text-gray-600 mt-1">
          @if (auth()->user()->teacher_level === 'admin')
            Kelola kelas di sekolah yang Anda admin
          @else
            Kelola kelas yang Anda ajar
          @endif
        </p>
      </div>
      @if (auth()->user()->teacher_level === 'admin')
        <button onclick="openAddModal()" class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA]">
          + Tambah Kelas
        </button>
      @endif
    </div>

    @if (session('success'))
      <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        {{ session('success') }}
      </div>
    @endif

    @if (session('error'))
      <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        {{ session('error') }}
      </div>
    @endif

    <!-- Filter -->
    <div class="bg-white rounded-[15px] mb-6" style="box-shadow: 1px 2px 2px 0px #00000040; border: 1px solid #B3b7da;">
      <div style="background-color: #5087E4;" class="px-6 py-3 rounded-t-[15px]">
        <h3 class="text-white font-semibold">Filter Data</h3>
      </div>
      <form method="GET" class="p-6">
        <div class="flex gap-4">
          <div class="flex-1">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama kelas..."
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]" style="border: 1px solid #010E82;">
          </div>
          <button type="submit" class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA]">
            Cari
          </button>
          @if ($search)
            <a href="{{ route('guru.kelas') }}"
              class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center">
              Reset
            </a>
          @endif
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
      <div class="mb-4 flex items-center gap-2">
        <span class="text-sm text-gray-600">Tampilkan:</span>
        <select onchange="changePerPage(this.value)" class="px-3 py-1 border border-gray-300 rounded-lg text-sm">
          <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
          <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
          <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
        </select>
        <span class="text-sm text-gray-600">per halaman</span>
      </div>

      @if ($classes->count() > 0)
        <div class="grid grid-cols-3 gap-6">
          @forelse($classes as $class)
            <div class="bg-white rounded-[15px] p-6"
              style="border: 2px solid #010E82; box-shadow: 1px 2px 2px 0px #00000040; position: relative;">
              @if (auth()->user()->teacher_level === 'admin')
                <!-- Edit Button -->
                <button
                  onclick="openEditModal({{ $class->id }}, {{ json_encode($class->name) }}, {{ json_encode($class->grade ?? '') }}, {{ $class->school_id }}, {{ optional($class->teachers->first())->id ?? 'null' }})"
                  class="absolute top-4 right-4 w-8 h-8 bg-[#5087E4] text-white rounded-lg hover:bg-[#3A68C9] flex items-center justify-center transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                    </path>
                  </svg>
                </button>
              @endif

              <!-- Class Name -->
              <h3 class="text-2xl font-bold text-[#010E82] mb-2">{{ $class->name }}</h3>

              <!-- Class Code with Copy Button -->
              <div class="flex items-center gap-2 mb-6">
                <span
                  class="bg-gray-100 px-3 py-1 rounded-lg text-sm font-medium text-gray-700">{{ $class->code }}</span>
                <button onclick="copyToClipboard('{{ $class->code }}')"
                  class="text-gray-500 hover:text-gray-700 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                    </path>
                  </svg>
                </button>
              </div>

              <!-- Stats Row -->
              <div class="grid grid-cols-3 gap-4 mb-6 pb-6 border-b border-gray-200">
                <div class="text-center">
                  <p class="text-2xl font-bold text-[#010E82]">{{ $class->students->count() }}</p>
                  <p class="text-xs text-gray-600 mt-1">Siswa</p>
                </div>
                <div class="text-center">
                  <p class="text-2xl font-bold text-[#010E82]">0</p>
                  <p class="text-xs text-gray-600 mt-1">Screening</p>
                </div>
                <div class="text-center">
                  <p class="text-2xl font-bold text-[#010E82]">0</p>
                  <p class="text-xs text-gray-600 mt-1">Perhatian</p>
                </div>
              </div>

              <!-- View Detail Button -->
              <a href="{{ route('guru.kelas.detail', $class->id) }}"
                class="block w-full text-center px-4 py-2 bg-[#5087E4] text-white rounded-lg hover:bg-[#3A68C9] transition-colors font-medium text-sm mb-3">
                Lihat Detail Kelas
              </a>

              <!-- Delete Button (Admin Only) -->
              @if (auth()->user()->teacher_level === 'admin')
                <button onclick="deleteClass({{ $class->id }})"
                  class="w-full px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-medium text-sm">
                  Hapus
                </button>
              @endif
            </div>
          @empty
          @endforelse
        </div>
      @endif

      @if ($classes->count() === 0)
        <div class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          <p class="text-gray-500 text-sm">Tidak ada data kelas</p>
        </div>
      @endif

      @if ($classes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 mt-6">
          <div class="text-sm text-gray-600">
            Menampilkan {{ $classes->firstItem() ?? 0 }} - {{ $classes->lastItem() ?? 0 }} dari
            {{ $classes->total() }} data
          </div>
          @if ($classes->hasPages())
            <div class="flex justify-end">{{ $classes->links() }}</div>
          @endif
        </div>
      @endif
    </div>
  </div>

  <!-- Add Modal -->
  @if (auth()->user()->teacher_level === 'admin')
    <div id="addModal" class="hidden fixed inset-0 z-50" style="background-color: rgba(0, 0, 0, 0.6);">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-[15px] shadow-xl w-full max-w-md">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-semibold text-[#010E82]">Tambah Kelas</h3>
              <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                  </path>
                </svg>
              </button>
            </div>

            <form method="POST" action="{{ route('guru.kelas.store') }}">
              @csrf
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sekolah *</label>
                <select name="school_id" required class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                  style="border: 1px solid #010E82;">
                  <option value="">-- Pilih Sekolah --</option>
                  @foreach ($schools as $school)
                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas *</label>
                <input type="text" name="name" required
                  class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                  style="border: 1px solid #010E82;">
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
                <input type="text" name="grade"
                  class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                  style="border: 1px solid #010E82;">
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Guru Bertanggung Jawab</label>
                <div class="space-y-3">
                  <label class="flex items-center">
                    <input type="radio" name="teacher_option" id="addTeacherOptionNone" value="none" checked
                      class="w-4 h-4 text-[#010E82] focus:ring-2 focus:ring-[#010E82]">
                    <span class="ml-2 text-sm text-gray-700">Tidak ada guru</span>
                  </label>
                  <label class="flex items-center">
                    <input type="radio" name="teacher_option" id="addTeacherOptionExisting" value="existing"
                      class="w-4 h-4 text-[#010E82] focus:ring-2 focus:ring-[#010E82]">
                    <span class="ml-2 text-sm text-gray-700">Pilih guru yang ada</span>
                  </label>
                  <label class="flex items-center">
                    <input type="radio" name="teacher_option" id="addTeacherOptionNew" value="new"
                      class="w-4 h-4 text-[#010E82] focus:ring-2 focus:ring-[#010E82]">
                    <span class="ml-2 text-sm text-gray-700">Buat guru baru</span>
                  </label>
                </div>
              </div>

              <div id="addExistingTeacherSection" class="mb-4 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Guru</label>
                <select name="teacher_id" id="addTeacherId"
                  class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                  style="border: 1px solid #010E82;">
                  <option value="">-- Pilih Guru --</option>
                </select>
              </div>

              <div id="addNewTeacherSection" class="mb-4 hidden space-y-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nama Guru *</label>
                  <input type="text" name="new_teacher_name" id="addNewTeacherName"
                    class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                    style="border: 1px solid #010E82;">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                  <input type="email" name="new_teacher_email" id="addNewTeacherEmail"
                    class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                    style="border: 1px solid #010E82;">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                  <input type="password" name="new_teacher_password" id="addNewTeacherPassword"
                    class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                    style="border: 1px solid #010E82;">
                </div>
              </div>

              <div class="flex justify-end gap-3">
                <button type="button" onclick="closeAddModal()"
                  class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                  Batal
                </button>
                <button type="submit" class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA]">
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- Edit Modal -->
  <div id="editModal" class="hidden fixed inset-0 z-50" style="background-color: rgba(0, 0, 0, 0.6);">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-[15px] shadow-xl w-full max-w-md">
        <div class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-[#010E82]">Edit Kelas</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <form id="editForm" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas *</label>
              <input type="text" name="name" id="editName" required
                class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                style="border: 1px solid #010E82;">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
              <input type="text" name="grade" id="editGrade"
                class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                style="border: 1px solid #010E82;">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Guru Bertanggung Jawab</label>
              <div class="space-y-3">
                <label class="flex items-center">
                  <input type="radio" name="teacher_option" id="editTeacherOptionNone" value="none" checked
                    class="w-4 h-4 text-[#010E82] focus:ring-2 focus:ring-[#010E82]">
                  <span class="ml-2 text-sm text-gray-700">Tidak ada guru</span>
                </label>
                <label class="flex items-center">
                  <input type="radio" name="teacher_option" id="editTeacherOptionExisting" value="existing"
                    class="w-4 h-4 text-[#010E82] focus:ring-2 focus:ring-[#010E82]">
                  <span class="ml-2 text-sm text-gray-700">Pilih guru yang ada</span>
                </label>
                <label class="flex items-center">
                  <input type="radio" name="teacher_option" id="editTeacherOptionNew" value="new"
                    class="w-4 h-4 text-[#010E82] focus:ring-2 focus:ring-[#010E82]">
                  <span class="ml-2 text-sm text-gray-700">Buat guru baru</span>
                </label>
              </div>
            </div>

            <div id="editExistingTeacherSection" class="mb-4 hidden">
              <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Guru</label>
              <select name="teacher_id" id="editTeacherId"
                class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                style="border: 1px solid #010E82;">
                <option value="">-- Pilih Guru --</option>
              </select>
            </div>

            <div id="editNewTeacherSection" class="mb-4 hidden space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Guru *</label>
                <input type="text" name="new_teacher_name" id="editNewTeacherName"
                  class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                  style="border: 1px solid #010E82;">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input type="email" name="new_teacher_email" id="editNewTeacherEmail"
                  class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                  style="border: 1px solid #010E82;">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                <input type="password" name="new_teacher_password" id="editNewTeacherPassword"
                  class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                  style="border: 1px solid #010E82;">
              </div>
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" onclick="closeEditModal()"
                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                Batal
              </button>
              <button type="submit" class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA]">
                Simpan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
  </form>

  <script>
    const teachersBySchool = @json($teachersBySchool ?? []);

    function populateTeacherSelect(selectEl, schoolId, selectedId = null) {
      if (!selectEl) return;
      selectEl.innerHTML = '<option value="">-- Pilih Guru --</option>';
      const list = teachersBySchool[String(schoolId)] || [];
      list.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.id;
        opt.textContent = t.name;
        if (selectedId && Number(selectedId) === Number(t.id)) opt.selected = true;
        selectEl.appendChild(opt);
      });
    }

    function applyTeacherOptionUI(prefix) {
      const none = document.getElementById(`${prefix}TeacherOptionNone`).checked;
      const existing = document.getElementById(`${prefix}TeacherOptionExisting`).checked;
      document.getElementById(`${prefix}ExistingTeacherSection`).classList.toggle('hidden', !existing);
      document.getElementById(`${prefix}NewTeacherSection`).classList.toggle('hidden', !document.getElementById(
        `${prefix}TeacherOptionNew`).checked);
      if (none) {
        const select = document.getElementById(`${prefix}TeacherId`);
        if (select) select.value = '';
      }
    }

    function setupTeacherOptionListeners(prefix) {
      ['None', 'Existing', 'New'].forEach(suffix => {
        const el = document.getElementById(`${prefix}TeacherOption${suffix}`);
        if (el && !el.dataset.bound) {
          el.addEventListener('change', () => applyTeacherOptionUI(prefix));
          el.dataset.bound = '1';
        }
      });
    }

    function openAddModal() {
      document.getElementById('addModal').classList.remove('hidden');

      // reset form
      document.querySelector('#addModal form').reset();
      document.getElementById('addTeacherOptionNone').checked = true;
      applyTeacherOptionUI('add');
      setupTeacherOptionListeners('add');

      const schoolSelect = document.querySelector('#addModal select[name="school_id"]');
      const teacherSelect = document.getElementById('addTeacherId');
      const schoolId = schoolSelect?.value || Object.keys(teachersBySchool)[0] || null;
      if (schoolId) populateTeacherSelect(teacherSelect, schoolId);

      if (schoolSelect && !schoolSelect.dataset.bound) {
        schoolSelect.addEventListener('change', (e) => {
          populateTeacherSelect(teacherSelect, e.target.value);
        });
        schoolSelect.dataset.bound = '1';
      }
    }

    function closeAddModal() {
      document.getElementById('addModal').classList.add('hidden');
    }

    function openEditModal(id, name, grade = '', schoolId = null, teacherId = null) {
      const modal = document.getElementById('editModal');
      const form = document.getElementById('editForm');

      form.action = '{{ route('guru.kelas.update', ':id') }}'.replace(':id', id);
      document.getElementById('editName').value = name;
      document.getElementById('editGrade').value = grade || '';

      setupTeacherOptionListeners('edit');
      applyTeacherOptionUI('edit');

      const teacherSelect = document.getElementById('editTeacherId');
      if (schoolId) {
        populateTeacherSelect(teacherSelect, schoolId, teacherId);
        if (teacherId) {
          document.getElementById('editTeacherOptionExisting').checked = true;
        } else {
          document.getElementById('editTeacherOptionNone').checked = true;
        }
        applyTeacherOptionUI('edit');
      }

      modal.classList.remove('hidden');
    }

    function closeEditModal() {
      document.getElementById('editModal').classList.add('hidden');
    }

    function deleteClass(id) {
      Swal.fire({
        title: 'Hapus Kelas?',
        text: "Seluruh data terkait kelas ini akan ikut terhapus!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.getElementById('deleteForm');
          form.action = '{{ route('guru.kelas.delete', ':id') }}'.replace(':id', id);
          form.submit();
        }
      });
    }

    function changePerPage(value) {
      const url = new URL(window.location.href);
      url.searchParams.set('per_page', value);
      window.location.href = url.toString();
    }

    function copyToClipboard(text) {
      navigator.clipboard.writeText(text).then(() => {
        toast('Kode kelas berhasil disalin');
      }).catch(() => {
        showAlert('Gagal menyalin kode kelas', 'error');
      });
    }
  </script>
@endsection
