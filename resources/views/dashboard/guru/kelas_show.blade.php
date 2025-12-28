@extends('layouts.app')

@section('title', 'Detail Kelas')

@section('content')
  <div class="px-6 py-6 bg-blue-100 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <a href="{{ route('guru.kelas') }}"
          class="text-[#010E82] hover:text-[#0B3BAA] mb-2 inline-flex items-center text-sm">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Kembali ke Kelas
        </a>
        <h1 class="text-3xl font-bold text-[#010E82]">{{ $class->name }}</h1>
        <p class="text-gray-600 mt-1">Sekolah: {{ $school->name }}</p>
        <p class="text-gray-600 mt-1 flex items-center gap-2">
          <span>Kode Kelas: <span id="classCode">{{ $class->code ?? '-' }}</span></span>
          @if ($class->code)
            <button type="button" onclick="copyClassCode(this)"
              class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition"
              title="Salin Kode Kelas">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                <path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z" />
                <path
                  d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5zM15 11h2a1 1 0 110 2h-2v-2z" />
              </svg>
              Salin
            </button>
          @endif
        </p>
      </div>
    </div>

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

    <!-- Summary Cards -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="text-center">
          <p class="text-3xl font-bold text-[#010E82]">{{ $students->count() }}</p>
          <p class="text-sm text-gray-600 mt-2">Total Siswa</p>
        </div>
      </div>
      <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="text-center">
          <p class="text-3xl font-bold text-[#010E82]">0</p>
          <p class="text-sm text-gray-600 mt-2">Screening</p>
        </div>
      </div>
      <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="text-center">
          <p class="text-3xl font-bold text-[#010E82]">0</p>
          <p class="text-sm text-gray-600 mt-2">Perhatian</p>
        </div>
      </div>
    </div>

    <!-- Data Siswa Table -->
    <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
      <div class="overflow-x-auto rounded-[15px]" style="border: 1px solid #B3b7da; position: relative;">
        <table class="min-w-full" style="border-collapse: separate; border-spacing: 0;">
          <thead style="background-color: #5087E4;">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Nama</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Username</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-bottom: 1px solid #B3b7da;">
                Status</th>
            </tr>
          </thead>
          <tbody class="bg-white">
            @forelse($students as $s)
              <tr class="hover:bg-gray-50 bg-green-50" style="border-bottom: 1px solid #B3b7da;">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                  style="border-right: 1px solid #B3b7da;">
                  {{ $s['name'] ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700" style="border-right: 1px solid #B3b7da;">
                  {{ $s['username'] ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 border border-green-300">Terdaftar</span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada siswa pada kelas ini</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Footer dengan Info Data -->
      <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
        <div class="text-sm text-gray-600">
          Total {{ $students->count() }} siswa
        </div>
      </div>
    </div>
  </div>

  <script>
    function copyClassCode(btn) {
      const codeText = document.getElementById('classCode')?.textContent;
      if (!codeText || codeText === '-') return;

      navigator.clipboard.writeText(codeText).then(() => {
        // Show success feedback
        const originalHTML = btn.innerHTML;
        btn.innerHTML =
          '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 111.414-1.414l2.793 2.793 6.793-6.793a1 1 0 011.414 0z" clip-rule="evenodd" /></svg> Tersalin!';
        btn.classList.add('bg-green-600');
        btn.classList.remove('bg-blue-600');

        setTimeout(() => {
          btn.innerHTML = originalHTML;
          btn.classList.remove('bg-green-600');
          btn.classList.add('bg-blue-600');
        }, 2000);
      }).catch(err => {
        alert('Gagal menyalin kode kelas');
        console.error('Copy failed:', err);
      });
    }
  </script>
@endsection
