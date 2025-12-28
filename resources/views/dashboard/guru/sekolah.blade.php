@extends('layouts.app')

@section('title', 'Manajemen Sekolah')

@section('content')
  <div class="px-6 py-6 bg-blue-100 min-h-screen">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-[#010E82]">Manajemen Sekolah</h1>
      <p class="text-gray-600 mt-1">Kelola data sekolah</p>
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

    @forelse($schools as $school)
      <div class="bg-white rounded-[15px] p-6 mb-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="flex justify-between items-start mb-6">
          <div>
            <h2 class="text-2xl font-bold text-[#010E82]">{{ $school->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">NPSN: {{ $school->npsn ?? 'Tidak ada' }}</p>
          </div>
          <button onclick="toggleEditMode({{ $school->id }})"
            class="px-4 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
            <span id="toggle-btn-{{ $school->id }}">Edit</span>
          </button>
        </div>

        <!-- Display Mode -->
        <div id="display-mode-{{ $school->id }}" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-2">Alamat</label>
              <p class="text-gray-900">{{ $school->address ?? '-' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-2">Nomor Telepon</label>
              <p class="text-gray-900">{{ $school->phone ?? '-' }}</p>
            </div>
          </div>
        </div>

        <!-- Edit Mode -->
        <div id="edit-mode-{{ $school->id }}" class="hidden">
          <form method="POST" action="{{ route('guru.sekolah.update', $school->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sekolah (Locked)</label>
              <input type="text" value="{{ $school->name }}" disabled
                class="w-full px-4 py-2 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                style="border: 1px solid #ccc;">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">NPSN (Locked)</label>
              <input type="text" value="{{ $school->npsn ?? '-' }}" disabled
                class="w-full px-4 py-2 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                style="border: 1px solid #ccc;">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
              <textarea name="address" rows="3"
                class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
                style="border: 1px solid #010E82;">{{ $school->address ?? '' }}</textarea>
              @error('address')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
              <input type="text" name="phone" value="{{ $school->phone ?? '' }}"
                class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
                style="border: 1px solid #010E82;">
              @error('phone')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div class="flex gap-3 pt-4">
              <button type="button" onclick="toggleEditMode({{ $school->id }})"
                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                Batal
              </button>
              <button type="submit"
                class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
                Simpan Perubahan
              </button>
            </div>
          </form>
        </div>
      </div>
    @empty
      <div class="bg-white rounded-[15px] p-12 text-center" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <p class="text-gray-500">Tidak ada data sekolah</p>
      </div>
    @endforelse
  </div>

  <script>
    function toggleEditMode(schoolId) {
      const displayMode = document.getElementById(`display-mode-${schoolId}`);
      const editMode = document.getElementById(`edit-mode-${schoolId}`);
      const toggleBtn = document.getElementById(`toggle-btn-${schoolId}`);

      displayMode.classList.toggle('hidden');
      editMode.classList.toggle('hidden');

      toggleBtn.textContent = editMode.classList.contains('hidden') ? 'Edit' : 'Batal';
    }
  </script>
@endsection
