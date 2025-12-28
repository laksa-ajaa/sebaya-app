<div class="p-5 font-semibold text-[#010E82]">
    Dashboard Guru Sebaya
</div>

<nav class="px-3 space-y-1 text-sm">
    <a href="{{ route('guru.dashboard') }}" 
       class="block px-4 py-2 rounded {{ request()->routeIs('guru.dashboard') ? 'bg-blue-50' : 'hover:bg-blue-50' }} text-[#010E82] font-semibold">
        Dashboard
    </a>
    
    @if(auth()->user()->teacher_level === 'admin')
    <a href="{{ route('guru.sekolah') }}" 
       class="block px-4 py-2 rounded {{ request()->routeIs('guru.sekolah') ? 'bg-blue-50' : 'hover:bg-blue-50' }} text-[#010E82] font-semibold">
        Manajemen Sekolah
    </a>
    @endif
    
    @if(in_array(auth()->user()->teacher_level, ['admin', 'kelas']))
    <a href="{{ route('guru.kelas') }}" 
       class="block px-4 py-2 rounded {{ request()->routeIs('guru.kelas') ? 'bg-blue-50' : 'hover:bg-blue-50' }} text-[#010E82] font-semibold">
        Manajemen Kelas
    </a>
    @endif
    
    <a href="{{ route('guru.screening') }}" 
       class="block px-4 py-2 rounded {{ request()->routeIs('guru.screening') ? 'bg-blue-50' : 'hover:bg-blue-50' }} text-[#010E82] font-semibold">
        Screening Siswa
    </a>
    <a href="{{ route('guru.siswa') }}" 
       class="block px-4 py-2 rounded {{ request()->routeIs('guru.siswa') ? 'bg-blue-50' : 'hover:bg-blue-50' }} text-[#010E82] font-semibold">
        Data Siswa
    </a>
    <a href="{{ route('guru.laporan') }}" 
       class="block px-4 py-2 rounded {{ request()->routeIs('guru.laporan') ? 'bg-blue-50' : 'hover:bg-blue-50' }} text-[#010E82] font-semibold">
        Laporan
    </a>
</nav>
