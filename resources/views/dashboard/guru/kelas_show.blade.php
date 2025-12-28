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
          <p class="text-3xl font-bold text-[#010E82]">{{ $enrolled_count ?? $students->count() }}</p>
          <p class="text-sm text-gray-600 mt-2">Terdaftar</p>
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
          <p class="text-3xl font-bold text-[#010E82]">{{ $pending_count ?? 0 }}</p>
          <p class="text-sm text-gray-600 mt-2">Perlu Verifikasi</p>
        </div>
      </div>
    </div>

    <!-- Data Siswa Table -->
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
            @forelse($students as $s)
              @php
                $isPending = ($s['status'] ?? '') === 'Perlu Verifikasi';
                $rowClass = $isPending ? 'bg-yellow-50' : 'bg-green-50';
                $badgeClass = $isPending
                    ? 'bg-yellow-100 text-yellow-800 border border-yellow-300'
                    : 'bg-green-100 text-green-800 border border-green-300';
              @endphp
              <tr class="hover:bg-gray-50 {{ $rowClass }}" style="border-bottom: 1px solid #B3b7da;">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                  style="border-right: 1px solid #B3b7da;">
                  {{ $s['name'] ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700" style="border-right: 1px solid #B3b7da;">
                  {{ $s['username'] ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 py-1 rounded text-xs font-medium {{ $badgeClass }}">{{ $s['status'] ?? '-' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  @if ($isPending)
                    <div class="flex items-center gap-2">
                      <form method="POST" id="verify-form-{{ $s['id'] }}"
                        action="{{ route('guru.kelas.verify', ['id' => $s['class_id'], 'user_id' => $s['id']]) }}">
                        @csrf
                        <button type="button" class="p-2 rounded bg-green-600 text-white hover:bg-green-700"
                          onclick="openConfirm('verify', 'verify-form-{{ $s['id'] }}')" title="Verifikasi">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd"
                              d="M16.707 5.293a1 1 0 010 1.414l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 111.414-1.414l2.793 2.793 6.793-6.793a1 1 0 011.414 0z"
                              clip-rule="evenodd" />
                          </svg>
                        </button>
                      </form>
                      <form method="POST" id="reject-form-{{ $s['id'] }}"
                        action="{{ route('guru.kelas.reject', ['id' => $s['class_id'], 'user_id' => $s['id']]) }}">
                        @csrf
                        <button type="button" class="p-2 rounded bg-red-600 text-white hover:bg-red-700"
                          onclick="openConfirm('reject', 'reject-form-{{ $s['id'] }}')" title="Tolak">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            class="w-5 h-5">
                            <path fill-rule="evenodd"
                              d="M10 8.586l3.95-3.95a1 1 0 111.414 1.414L11.414 10l3.95 3.95a1 1 0 01-1.414 1.414L10 11.414l-3.95 3.95a1 1 0 01-1.414-1.414L8.586 10l-3.95-3.95A1 1 0 116.05 4.636L10 8.586z"
                              clip-rule="evenodd" />
                          </svg>
                        </button>
                      </form>
                    </div>
                  @else
                    <span class="text-xs text-gray-400">-</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada siswa pada kelas ini
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Footer dengan Info Data -->
      <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
        <div class="text-sm text-gray-600">
          Terdaftar: {{ $enrolled_count ?? 0 }} | Perlu verifikasi: {{ $pending_count ?? 0 }}
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi -->
  <div id="confirmModal" class="fixed inset-0 z-50 hidden">
    <div id="confirmOverlay" class="absolute inset-0 bg-black/40"></div>
    <div class="relative mx-auto mt-24 w-full max-w-md">
      <div class="bg-white rounded-lg shadow p-5">
        <h3 class="text-lg font-semibold text-slate-800">Konfirmasi</h3>
        <p id="confirmMessage" class="text-sm text-slate-600 mt-2">Yakin melakukan aksi ini?</p>
        <div class="mt-4 flex justify-end gap-2">
          <button type="button" id="confirmCancel"
            class="px-3 py-2 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">Batal</button>
          <button type="button" id="confirmOk" class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Ya,
            Lanjutkan</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    let targetFormId = null;
    let currentAction = null;
    const modalEl = document.getElementById('confirmModal');
    const overlayEl = document.getElementById('confirmOverlay');
    const msgEl = document.getElementById('confirmMessage');
    const btnOk = document.getElementById('confirmOk');
    const btnCancel = document.getElementById('confirmCancel');

    function openConfirm(action, formId) {
      currentAction = action;
      targetFormId = formId;
      if (action === 'verify') {
        msgEl.textContent = 'Verifikasi siswa ini ke kelas?';
        btnOk.className = 'px-3 py-2 rounded bg-green-600 text-white hover:bg-green-700';
      } else {
        msgEl.textContent = 'Tolak permintaan? Kode kelas siswa akan dihapus.';
        btnOk.className = 'px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700';
      }
      modalEl.classList.remove('hidden');
    }

    function closeConfirm() {
      modalEl.classList.add('hidden');
      targetFormId = null;
      currentAction = null;
    }

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

    overlayEl?.addEventListener('click', closeConfirm);
    btnCancel?.addEventListener('click', closeConfirm);
    btnOk?.addEventListener('click', () => {
      if (!targetFormId) return;
      const form = document.getElementById(targetFormId);
      if (form) form.submit();
      closeConfirm();
    });

    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeConfirm();
    });
  </script>
@endsection
