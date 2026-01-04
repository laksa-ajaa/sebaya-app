<div class="p-5 font-semibold text-[#010E82]">
  Dashboard Guru Sebaya
</div>

<nav class="px-3 space-y-1 text-sm">

  {{-- Dashboard --}}
  <a href="{{ route('guru.dashboard') }}"
    class="block px-4 py-2 rounded
        {{ request()->routeIs('guru.dashboard') ? 'bg-blue-50' : 'hover:bg-blue-50' }}
        text-[#010E82] font-semibold">
    Dashboard
  </a>

  {{-- Manajemen Sekolah (Admin Guru) --}}
  @if (auth()->user()->teacher_level === 'admin')
    <a href="{{ route('guru.sekolah') }}"
      class="block px-4 py-2 rounded
            {{ request()->routeIs('guru.sekolah') ? 'bg-blue-50' : 'hover:bg-blue-50' }}
            text-[#010E82] font-semibold">
      Manajemen Sekolah
    </a>
  @endif

  {{-- Manajemen Kelas (Admin & Guru Kelas) --}}
  @if (in_array(auth()->user()->teacher_level, ['admin', 'kelas']))
    <a href="{{ route('guru.kelas') }}"
      class="block px-4 py-2 rounded
            {{ request()->routeIs('guru.kelas') ? 'bg-blue-50' : 'hover:bg-blue-50' }}
            text-[#010E82] font-semibold">
      Manajemen Kelas
    </a>
  @endif

  {{-- Data Siswa --}}
  {{-- <a href="{{ route('guru.siswa') }}"
    class="block px-4 py-2 rounded
        {{ request()->routeIs('guru.siswa') ? 'bg-blue-50' : 'hover:bg-blue-50' }}
        text-[#010E82] font-semibold">
    Data Siswa
  </a> --}}

  {{-- Laporan (Collapsible) --}}
  <div x-data="{ open: {{ request()->routeIs('guru.laporan*') || request()->routeIs('guru.laporan.mood-check*') ? 'true' : 'false' }} }">

    <button type="button" @click="open = !open"
      class="w-full flex items-center justify-between px-4 py-2 rounded
            {{ request()->routeIs('guru.laporan*') || request()->routeIs('guru.laporan.mood-check*') ? 'bg-blue-50' : 'hover:bg-blue-50' }}
            text-[#010E82] font-semibold">

      <span>Laporan</span>

      <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none"
        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
      </svg>
    </button>

    {{-- Submenu --}}
    <div x-show="open" x-transition class="ml-6 mt-1 space-y-1">

      <a href="{{ route('guru.laporan.mood-check') }}"
        class="block px-4 py-2 rounded text-sm
                {{ request()->routeIs('guru.laporan.mood-check*') ? 'bg-blue-100' : 'hover:bg-blue-50' }}
                text-[#010E82]">
        Laporan Mood Check
      </a>

      {{-- Screening --}}
      <a href="{{ route('guru.laporan.screening-report') }}"
        class="block px-4 py-2 rounded text-sm
        {{ request()->routeIs('guru.laporan.screening-report*') ? 'bg-blue-100' : 'hover:bg-blue-50' }}
        text-[#010E82]">
        Laporan Screening
      </a>

    </div>
  </div>

</nav>
