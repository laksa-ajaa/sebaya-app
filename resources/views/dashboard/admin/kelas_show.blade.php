@extends('layouts.app')

@section('title', 'Detail Kelas')

@section('content')
  <div class="px-6 py-6 bg-blue-100 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <a href="{{ route('admin.sekolah.kelas.index', $school->id) }}"
          class="text-[#010E82] hover:text-[#0B3BAA] mb-2 inline-flex items-center text-sm">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Kembali ke Kelas
        </a>
        <h1 class="text-3xl font-bold text-[#010E82]">{{ $class->name }}</h1>
        <p class="text-gray-600 mt-1">Sekolah: {{ $school->name }}</p>
        <p class="text-gray-600 mt-1 flex items-center gap-2">
          <span>Kode Kelas: <span id="classCode">{{ $class->code ?? '-' }}</span> | Grade:
            {{ $class->grade ?? '-' }}</span>
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

    <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
      <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-4 text-sm">
          <span class="inline-flex items-center gap-2"><span
              class="w-3 h-3 rounded bg-green-200 border border-green-400"></span> Terdaftar</span>
          <span class="inline-flex items-center gap-2"><span
              class="w-3 h-3 rounded bg-yellow-200 border border-yellow-400"></span> Perlu Verifikasi</span>
        </div>
      </div>

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
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-bottom: 1px solid #B3b7da;">
                Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white">
            @forelse(($students ?? []) as $s)
              @php
                $status = is_array($s) ? $s['status'] ?? '' : $s->status ?? '';
                $rowClass = $status === 'Terdaftar' ? 'bg-green-50' : 'bg-yellow-50';
                $badgeClass =
                    $status === 'Terdaftar'
                        ? 'bg-green-100 text-green-800 border border-green-300'
                        : 'bg-yellow-100 text-yellow-800 border border-yellow-300';
              @endphp
              <tr class="hover:bg-gray-50 {{ $rowClass }}" style="border-bottom: 1px solid #B3b7da;">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                  style="border-right: 1px solid #B3b7da;">
                  {{ is_array($s) ? $s['name'] ?? '-' : $s->name ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700" style="border-right: 1px solid #B3b7da;">
                  {{ is_array($s) ? $s['username'] ?? '-' : $s->username ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap" style="border-right: 1px solid #B3b7da;">
                  <span class="px-2 py-1 rounded text-xs font-medium {{ $badgeClass }}">{{ $status ?: '-' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  @if ($status === 'Perlu Verifikasi')
                    <div class="flex items-center gap-2">
                      <form method="POST" id="verify-form-{{ is_array($s) ? $s['id'] ?? 'x' : $s->id ?? 'x' }}"
                        action="{{ route('admin.sekolah.kelas.verify', [$school->id, $class->id, is_array($s) ? $s['id'] : $s->id]) }}">
                        @csrf
                        <button type="button" class="p-2 rounded bg-green-600 text-white hover:bg-green-700"
                          onclick="openConfirm('verify', 'verify-form-{{ is_array($s) ? $s['id'] ?? 'x' : $s->id ?? 'x' }}')"
                          title="Verifikasi">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd"
                              d="M16.707 5.293a1 1 0 010 1.414l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 111.414-1.414l2.793 2.793 6.793-6.793a1 1 0 011.414 0z"
                              clip-rule="evenodd" />
                          </svg>
                        </button>
                      </form>
                      <form method="POST" id="reject-form-{{ is_array($s) ? $s['id'] ?? 'x' : $s->id ?? 'x' }}"
                        action="{{ route('admin.sekolah.kelas.reject', [$school->id, $class->id, is_array($s) ? $s['id'] : $s->id]) }}">
                        @csrf
                        <button type="button" class="p-2 rounded bg-red-600 text-white hover:bg-red-700"
                          onclick="openConfirm('reject', 'reject-form-{{ is_array($s) ? $s['id'] ?? 'x' : $s->id ?? 'x' }}')"
                          title="Tolak">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd"
                              d="M10 8.586l3.95-3.95a1 1 0 111.414 1.414L11.414 10l3.95 3.95a1 1 0 01-1.414 1.414L10 11.414l-3.95 3.95a1 1 0 01-1.414-1.414L8.586 10l-3.95-3.95A1 1 0 116.05 4.636L10 8.586z"
                              clip-rule="evenodd" />
                          </svg>
                        </button>
                      </form>
                    </div>
                  @else
                    <span class="text-sm text-gray-600">-</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada siswa pada kelas ini</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Footer dengan Info Data -->
      <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
        <!-- Kiri: Info Data -->
        <div class="text-sm text-gray-600">
          Total {{ ($students ?? collect())->count() }} siswa
        </div>
      </div>
    </div>
  </div>



  <script>
    function openConfirm(action, formId) {
      const isVerify = action === 'verify';
      Swal.fire({
        title: isVerify ? 'Verifikasi Siswa?' : 'Tolak Permintaan?',
        text: isVerify ? 'Siswa akan terdaftar di kelas ini.' : 'Kode kelas siswa akan dihapus.',
        icon: isVerify ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: isVerify ? '#16a34a' : '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: isVerify ? 'Ya, Verifikasi!' : 'Ya, Tolak!',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.getElementById(formId);
          if (form) form.submit();
        }
      });
    }

    function copyClassCode(btn) {
      const codeText = document.getElementById('classCode')?.textContent;
      if (!codeText || codeText === '-') return;

      navigator.clipboard.writeText(codeText).then(() => {
        toast('Kode kelas berhasil disalin');
        
        // Visual feedback on button
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
        showAlert('Gagal menyalin kode kelas', 'error');
        console.error('Copy failed:', err);
      });
    }
  </script>
@endsection
