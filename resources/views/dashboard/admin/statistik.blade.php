@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
  <div class="px-6 py-6 bg-blue-100 min-h-screen">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-[#010E82]">Manajemen Pengguna</h1>
      <p class="text-gray-600 mt-1">Kelola data pengguna sistem</p>
    </div>

    <!-- Success/Error Message -->
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

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
      <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="text-center">
          <p class="text-gray-600 text-sm mb-2">Total Pengguna</p>
          <p class="text-3xl font-bold text-[#010E82]">{{ number_format($totalUsers) }}</p>
        </div>
      </div>
      <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="text-center">
          <p class="text-gray-600 text-sm mb-2">Umum</p>
          <p class="text-3xl font-bold text-gray-600">{{ number_format($totalUmum) }}</p>
        </div>
      </div>
      <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="text-center">
          <p class="text-gray-600 text-sm mb-2">Siswa</p>
          <p class="text-3xl font-bold text-blue-600">{{ number_format($totalStudents) }}</p>
        </div>
      </div>
      <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="text-center">
          <p class="text-gray-600 text-sm mb-2">Guru</p>
          <p class="text-3xl font-bold text-green-600">{{ number_format($totalTeachers) }}</p>
        </div>
      </div>
      <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="text-center">
          <p class="text-gray-600 text-sm mb-2">Admin</p>
          <p class="text-3xl font-bold text-purple-600">{{ number_format($totalAdmins) }}</p>
        </div>
      </div>
    </div>

    <!-- Filter Data -->
    <div class="bg-white rounded-[15px] mb-6" style="box-shadow: 1px 2px 2px 0px #00000040; border: 1px solid #B3b7da;">
      <!-- Header Filter -->
      <div style="background-color: #5087E4;" class="px-6 py-3 rounded-t-[15px]">
        <h3 class="text-white font-semibold">Filter Data</h3>
      </div>

      <!-- Form Filter -->
      <form method="GET" action="{{ route('admin.statistik') }}" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Nama -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama..."
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;">
          </div>

          <!-- Role -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select name="role"
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;">
              <option value="all" {{ $role === 'all' ? 'selected' : '' }}>-Pilih-</option>
              <option value="umum" {{ $role === 'umum' ? 'selected' : '' }}>Umum</option>
              <option value="student" {{ $role === 'student' ? 'selected' : '' }}>Siswa</option>
              <option value="teacher" {{ $role === 'teacher' ? 'selected' : '' }}>Guru</option>
              <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
          </div>

          <!-- Kode Kelas -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Kelas</label>
            <select name="class_code"
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;">
              <option value="">-Pilih-</option>
              @foreach ($classCodes as $code)
                <option value="{{ $code }}" {{ $classCode === $code ? 'selected' : '' }}>
                  {{ $code }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="flex gap-3 mt-4">
          <button type="submit"
            class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
            Cari
          </button>
          @if ($search || $role !== 'all' || $classCode)
            <a href="{{ route('admin.statistik') }}"
              class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center">
              Reset
            </a>
          @endif
        </div>
      </form>
    </div>

    <!-- Tabel Pengguna -->
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
                Nama
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Username
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Email
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Role
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Kode Kelas
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Terdaftar
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-bottom: 1px solid #B3b7da;">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="bg-white">
            @forelse($users as $user)
              <tr class="hover:bg-gray-50" style="border-bottom: 1px solid #B3b7da;">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                  style="border-right: 1px solid #B3b7da;">
                  {{ $user->name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" style="border-right: 1px solid #B3b7da;">
                  {{ $user->username }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" style="border-right: 1px solid #B3b7da;">
                  {{ $user->email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap" style="border-right: 1px solid #B3b7da;">
                  @if ($user->role === 'admin')
                    <span
                      class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                      Admin
                    </span>
                  @elseif($user->role === 'teacher')
                    <span
                      class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      Guru
                    </span>
                  @elseif($user->role === 'user' && $user->class->count() > 0)
                    <span
                      class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                      Siswa
                    </span>
                  @else
                    <span
                      class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                      Umum
                    </span>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" style="border-right: 1px solid #B3b7da;">
                  {{ $user->class_code ?? 'Belum Bergabung' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" style="border-right: 1px solid #B3b7da;">
                  {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium relative">
                  @if ($user->role !== 'admin')
                    <div class="relative">
                      <button onclick="toggleActionMenu({{ $user->id }})"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#010E82] rounded-full transition-colors"
                        id="actionBtn-{{ $user->id }}" title="Aksi">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                          <path
                            d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z">
                          </path>
                        </svg>
                      </button>
                      <div id="actionMenu-{{ $user->id }}"
                        class="hidden fixed w-48 bg-white rounded-lg shadow-lg z-50"
                        style="min-width: 180px; border: 1px solid #010E82;">
                        <!-- Arrow/Pointer -->
                        <div id="actionMenuArrow-{{ $user->id }}" class="absolute w-0 h-0"
                          style="right: 12px; top: -8px; border-left: 8px solid transparent; border-right: 8px solid transparent; border-bottom: 8px solid #010E82;">
                        </div>
                        <!-- Arrow inner untuk efek solid -->
                        <div id="actionMenuArrowInner-{{ $user->id }}" class="absolute w-0 h-0"
                          style="right: 12px; top: -7px; border-left: 7px solid transparent; border-right: 7px solid transparent; border-bottom: 7px solid white; z-index: 1;">
                        </div>
                        <div class="py-1 relative bg-white rounded-lg">
                          <button onclick="resetPassword({{ $user->id }})"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                              </path>
                            </svg>
                            Reset Password
                          </button>
                          <button onclick="deleteUser({{ $user->id }})"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                              </path>
                            </svg>
                            Hapus
                          </button>
                        </div>
                      </div>
                    </div>
                  @else
                    <span class="text-gray-400 text-xs"></span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                  Tidak ada pengguna ditemukan
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
          Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari
          {{ $users->total() }} data
        </div>

        <!-- Kanan: Pagination -->
        @if ($users->hasPages())
          <div>
            {{ $users->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Forms untuk Reset Password dan Delete -->
  <form id="resetPasswordForm" method="POST" style="display: none;">
    @csrf
  </form>

  <form id="deleteUserForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
  </form>

  <script>
    function changePerPage(value) {
      const url = new URL(window.location.href);
      url.searchParams.set('per_page', value);
      window.location.href = url.toString();
    }

    function toggleActionMenu(userId) {
      const menu = document.getElementById('actionMenu-' + userId);
      const button = document.getElementById('actionBtn-' + userId);
      const isHidden = menu.classList.contains('hidden');

      // Tutup semua menu lain
      document.querySelectorAll('[id^="actionMenu-"]').forEach(m => {
        if (m.id !== 'actionMenu-' + userId) {
          m.classList.add('hidden');
          m.classList.remove('bottom-full', 'mt-2', 'mb-2');
        }
      });

      // Toggle menu saat ini
      if (isHidden) {
        menu.classList.remove('hidden');
        const arrow = document.getElementById('actionMenuArrow-' + userId);
        const arrowInner = document.getElementById('actionMenuArrowInner-' + userId);

        // Posisikan menu menggunakan fixed positioning
        requestAnimationFrame(() => {
          const rect = button.getBoundingClientRect();
          const menuHeight = 100; // estimasi tinggi menu
          const spaceBelow = window.innerHeight - rect.bottom;
          const spaceAbove = rect.top;

          // Reset style
          menu.style.top = '';
          menu.style.bottom = '';
          menu.style.right = '';

          // Reset arrow
          arrow.style.top = '';
          arrow.style.bottom = '';
          arrow.style.borderTop = '';
          arrow.style.borderBottom = '';
          arrow.style.borderLeft = '8px solid transparent';
          arrow.style.borderRight = '8px solid transparent';

          arrowInner.style.top = '';
          arrowInner.style.bottom = '';
          arrowInner.style.borderTop = '';
          arrowInner.style.borderBottom = '';
          arrowInner.style.borderLeft = '7px solid transparent';
          arrowInner.style.borderRight = '7px solid transparent';

          // Jika tidak ada cukup ruang di bawah tapi ada ruang di atas, munculkan ke atas
          if (spaceBelow < menuHeight && spaceAbove > menuHeight) {
            // Muncul di atas - arrow di bawah menu
            menu.style.bottom = (window.innerHeight - rect.top + 8) + 'px';
            arrow.style.bottom = '-8px';
            arrow.style.top = '';
            arrow.style.borderTop = '8px solid #010E82';
            arrow.style.borderBottom = '';

            arrowInner.style.bottom = '-7px';
            arrowInner.style.top = '';
            arrowInner.style.borderTop = '7px solid white';
            arrowInner.style.borderBottom = '';
          } else {
            // Muncul di bawah - arrow di atas menu
            menu.style.top = (rect.bottom + 8) + 'px';
            arrow.style.top = '-8px';
            arrow.style.bottom = '';
            arrow.style.borderBottom = '8px solid #010E82';
            arrow.style.borderTop = '';

            arrowInner.style.top = '-7px';
            arrowInner.style.bottom = '';
            arrowInner.style.borderBottom = '7px solid white';
            arrowInner.style.borderTop = '';
          }

          // Posisi horizontal (kanan sejajar dengan button)
          menu.style.right = (window.innerWidth - rect.right) + 'px';
        });
      } else {
        menu.classList.add('hidden');
      }
    }

    // Tutup menu ketika klik di luar
    document.addEventListener('click', function(event) {
      if (!event.target.closest('[id^="actionBtn-"]') && !event.target.closest('[id^="actionMenu-"]')) {
        document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => {
          menu.classList.add('hidden');
        });
      }
    });

    function resetPassword(userId) {
      // Tutup menu
      document.getElementById('actionMenu-' + userId).classList.add('hidden');

      if (confirm(
          'Apakah Anda yakin ingin mereset password pengguna ini? Password akan direset menjadi "password123".'
        )) {
        const form = document.getElementById('resetPasswordForm');
        form.action = '{{ route('admin.user.reset-password', ':id') }}'.replace(':id', userId);
        form.submit();
      }
    }

    function deleteUser(userId) {
      // Tutup menu
      document.getElementById('actionMenu-' + userId).classList.add('hidden');

      if (confirm('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.')) {
        const form = document.getElementById('deleteUserForm');
        form.action = '{{ route('admin.user.delete', ':id') }}'.replace(':id', userId);
        form.submit();
      }
    }
  </script>
@endsection
