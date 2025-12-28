@extends('layouts.app')

@section('title', 'Verifikasi Guru')

@section('content')
  <div class="px-6 py-6 bg-blue-100 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <a href="{{ route('admin.dashboard') }}"
          class="text-[#010E82] hover:text-[#0B3BAA] mb-2 inline-flex items-center text-sm">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Kembali ke Dashboard
        </a>
        <h1 class="text-3xl font-bold text-[#010E82]">Verifikasi Guru</h1>
        <p class="text-gray-600 mt-1">Daftar pengajuan guru yang menunggu verifikasi admin.</p>
      </div>
    </div>

    @if (session('success'))
      <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
      </div>
    @endif

    @if (session('error'))
      <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
      </div>
    @endif

    <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040; border: 1px solid #B3b7da;">
      <div class="overflow-x-auto rounded-[15px]" style="border: 1px solid #B3b7da; position: relative;">
        <table class="min-w-full" style="border-collapse: separate; border-spacing: 0;">
          <thead style="background-color: #5087E4;">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">Guru</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">Kontak</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">Sekolah</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-bottom: 1px solid #B3b7da;">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white">
            @forelse($requests as $req)
              <tr class="hover:bg-gray-50" style="border-bottom: 1px solid #B3b7da;">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                  style="border-right: 1px solid #B3b7da;">
                  {{ $req->user->name }}<br>
                  <span class="text-xs text-gray-600">{{ $req->user->username }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700" style="border-right: 1px solid #B3b7da;">
                  {{ $req->user->email }}<br>
                  <span class="text-xs text-gray-600">WA: {{ $req->user->whatsapp_number }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700" style="border-right: 1px solid #B3b7da;">
                  <div class="font-semibold text-gray-900">{{ $req->school_name }}</div>
                  <div class="text-xs text-gray-600">NPSN: {{ $req->school_npsn ?? '-' }}</div>
                  @if ($req->school_phone)
                    <div class="text-xs text-gray-600">Telp: {{ $req->school_phone }}</div>
                  @endif
                  @if ($req->school_address)
                    <div class="text-xs text-gray-600 mt-1">{{ $req->school_address }}</div>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.guru.requests.approve', $req->id) }}">
                      @csrf
                      <button type="submit"
                        class="px-3 py-2 rounded bg-green-600 text-white hover:bg-green-700">Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('admin.guru.requests.reject', $req->id) }}"
                      class="flex items-center gap-2">
                      @csrf
                      <input type="text" name="rejection_reason" placeholder="Alasan (opsional)"
                        class="px-2 py-1 text-sm border rounded" />
                      <button type="submit"
                        class="px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700">Tolak</button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada pengajuan guru.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
        <div class="text-sm text-gray-600">
          Menampilkan {{ $requests->firstItem() ?? 0 }} - {{ $requests->lastItem() ?? 0 }} dari
          {{ $requests->total() }} pengajuan
        </div>
        <div>
          {{ $requests->links() }}
        </div>
      </div>
    </div>
  </div>
@endsection
