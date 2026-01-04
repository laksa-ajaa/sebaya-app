<div class="p-5 font-semibold text-[#010E82]">
  Dashboard Admin Sebaya
</div>

<nav class="px-3 space-y-1 text-sm">

  {{-- Dashboard --}}
  <a href="{{ route('admin.dashboard') }}"
    class="block px-4 py-2 rounded
        {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50' : 'hover:bg-blue-50' }}
        text-[#010E82] font-semibold">
    Dashboard
  </a>

  {{-- Pengguna --}}
  <a href="{{ route('admin.statistik') }}"
    class="block px-4 py-2 rounded
        {{ request()->routeIs('admin.statistik') ? 'bg-blue-50' : 'hover:bg-blue-50' }}
        text-[#010E82] font-semibold">
    Pengguna
  </a>

  {{-- Permintaan Guru --}}
  <a href="{{ route('admin.guru.requests') }}"
    class="block px-4 py-2 rounded
        {{ request()->routeIs('admin.guru.requests') ? 'bg-blue-50' : 'hover:bg-blue-50' }}
        text-[#010E82] font-semibold">
    Permintaan Guru
  </a>

  {{-- Sekolah --}}
  <a href="{{ route('admin.schools') }}"
    class="block px-4 py-2 rounded
        {{ request()->routeIs('admin.schools') || request()->routeIs('admin.sekolah.*') ? 'bg-blue-50' : 'hover:bg-blue-50' }}
        text-[#010E82] font-semibold">
    Sekolah
  </a>

  {{-- Laporan (Collapsible) --}}
  <div x-data="{ open: {{ request()->routeIs('admin.laporan*') || request()->routeIs('admin.laporan.mood-check*') ? 'true' : 'false' }} }">

    <button @click="open = !open" type="button"
      class="w-full flex items-center justify-between px-4 py-2 rounded
            {{ request()->routeIs('admin.laporan*') || request()->routeIs('admin.laporan.mood-check*') ? 'bg-blue-50' : 'hover:bg-blue-50' }}
            text-[#010E82] font-semibold">

      <span>Laporan</span>

      <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none"
        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
      </svg>
    </button>

    {{-- Submenu --}}
    <div x-show="open" x-transition class="ml-6 mt-1 space-y-1">

      <a href="{{ route('admin.laporan.mood-check') }}"
        class="block px-4 py-2 rounded text-sm
                {{ request()->routeIs('admin.laporan.mood-check*') ? 'bg-blue-100' : 'hover:bg-blue-50' }}
                text-[#010E82]">
        Laporan Mood Check
      </a>

      <a href="{{ route('admin.laporan.screening-report') }}"
        class="block px-4 py-2 rounded text-sm
                {{ request()->routeIs('admin.laporan.screening-report*') ? 'bg-blue-100' : 'hover:bg-blue-50' }}
                text-[#010E82]">
        Laporan Screening
      </a>

    </div>
  </div>

</nav>
