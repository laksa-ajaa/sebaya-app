@extends('layouts.app')

@section('title', 'Siswa')

@section('content')
  <div class="min-h-screen bg-slate-50">
    @include('layouts.partials.navbar')
    @include('layouts.partials.sidebar')

    <main class="pt-20 px-6 bg-blue-100 min-h-screen transition-all lg:ml-64">
      <div class="bg-white rounded-xl p-4 shadow mb-6">
        <h1 class="text-lg font-semibold text-slate-800">Siswa</h1>
        <p class="text-sm text-slate-600 mt-2">Daftar siswa dalam satu tabel. Baris dibedakan: yang sudah terdaftar vs yang
          membutuhkan verifikasi.</p>
        <div class="mt-3 flex items-center gap-3 text-sm">
          <span class="inline-flex items-center gap-2"><span
              class="w-3 h-3 rounded bg-green-200 border border-green-400"></span> Terdaftar</span>
          <span class="inline-flex items-center gap-2"><span
              class="w-3 h-3 rounded bg-yellow-200 border border-yellow-400"></span> Perlu Verifikasi</span>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-100">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Username</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">WhatsApp</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Kelas</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-700">Tanggal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @forelse(($students ?? []) as $s)
                @php
                  $rowClass = $s['status'] === 'Terdaftar' ? 'bg-green-50' : 'bg-yellow-50';
                  $badgeClass =
                      $s['status'] === 'Terdaftar'
                          ? 'bg-green-100 text-green-800 border border-green-300'
                          : 'bg-yellow-100 text-yellow-800 border border-yellow-300';
                @endphp
                <tr class="{{ $rowClass }}">
                  <td class="px-4 py-3 text-sm text-slate-800">{{ $s['name'] }}</td>
                  <td class="px-4 py-3 text-sm text-slate-700">{{ $s['username'] }}</td>
                  <td class="px-4 py-3 text-sm text-slate-700">{{ $s['whatsapp_number'] ?? '-' }}</td>
                  <td class="px-4 py-3 text-sm text-slate-700">{{ $s['class_name'] ?? '-' }}</td>
                  <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-xs font-medium {{ $badgeClass }}">{{ $s['status'] }}</span>
                  </td>
                  <td class="px-4 py-3 text-xs text-slate-600">
                    {{ optional($s['reference_at']) ? \Carbon\Carbon::parse($s['reference_at'])->format('d M Y') : '-' }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="px-4 py-6 text-center text-slate-500 text-sm">Belum ada data siswa untuk kelas
                    Anda.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </main>

    @include('layouts.partials.footer')
  </div>

  <script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    sidebarToggle?.addEventListener('click', () => {
      sidebar.classList.toggle('-translate-x-full');
      sidebarOverlay.classList.toggle('hidden');
    });

    sidebarOverlay?.addEventListener('click', () => {
      sidebar.classList.add('-translate-x-full');
      sidebarOverlay.classList.add('hidden');
    });
  </script>
@endsection
