<div class="p-5 font-semibold text-[#010E82]">
  Dashboard Admin Sebaya
</div>

<nav class="px-3 space-y-1 text-sm">
  <a href="{{ route('admin.dashboard') }}"
    class="block px-4 py-2 rounded {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50' : 'hover:bg-blue-50' }} text-[#010E82] font-semibold">
    Dashboard
  </a>
  <a href="{{ route('admin.statistik') }}"
    class="block px-4 py-2 rounded {{ request()->routeIs('admin.statistik') ? 'bg-blue-50' : 'hover:bg-blue-50' }} text-[#010E82] font-semibold">
    Pengguna
  </a>
  <a href="{{ route('admin.guru.requests') }}"
    class="block px-4 py-2 rounded {{ request()->routeIs('admin.guru.requests') ? 'bg-blue-50' : 'hover:bg-blue-50' }} text-[#010E82] font-semibold">
    Permintaan Guru
  </a>
  <a href="{{ route('admin.schools') }}"
    class="block px-4 py-2 rounded {{ request()->routeIs('admin.schools') || request()->routeIs('admin.sekolah.*') ? 'bg-blue-50' : 'hover:bg-blue-50' }} text-[#010E82] font-semibold">
    Sekolah
  </a>
  <a href="{{ route('admin.mood-check') }}"
    class="block px-4 py-2 rounded {{ request()->routeIs('admin.mood-check*') ? 'bg-blue-50' : 'hover:bg-blue-50' }} text-[#010E82] font-semibold">
    Data Mood Check
  </a>
  <a href="{{ route('admin.laporan') }}"
    class="block px-4 py-2 rounded {{ request()->routeIs('admin.laporan') ? 'bg-blue-50' : 'hover:bg-blue-50' }} text-[#010E82] font-semibold">
    Laporan
  </a>
</nav>
