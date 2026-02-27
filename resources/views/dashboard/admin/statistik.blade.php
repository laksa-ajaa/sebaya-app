@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
    <div class="px-6 py-6 bg-blue-100 min-h-screen">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-[#010E82]">Manajemen Pengguna</h1>
                <p class="text-gray-600 mt-1">Kelola data pengguna sistem</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.user.export', request()->query()) }}"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Excel
                </a>
                <button onclick="openAddUserModal()"
                    class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors shadow flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Pengguna
                </button>
            </div>
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

        @if ($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ $errors->first() }}</span>
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
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search User -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari Pengguna</label>
                        <input type="text" name="search" value="{{ $search }}"
                            placeholder="Nama, username, email..."
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

                    <!-- Filter Sekolah -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sekolah</label>
                        <select name="school_id"
                            class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
                            style="border: 1px solid #010E82;">
                            <option value="">-Semua Sekolah-</option>
                            @foreach ($allSchools as $school)
                                <option value="{{ $school->id }}"
                                    {{ $schoolIdFilter == $school->id ? 'selected' : '' }}>
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 mt-4">
                    <button type="submit"
                        class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
                        Cari
                    </button>
                    @if ($search || $role !== 'all' || $classCode || $schoolIdFilter)
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
                                Nama Kelas
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                                Asal Sekolah
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    style="border-right: 1px solid #B3b7da;">
                                    {{ $user->username }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    style="border-right: 1px solid #B3b7da;">
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
                                            Guru {{ $user->teacher_level === 'admin' ? '(Admin)' : '(Kelas)' }}
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    style="border-right: 1px solid #B3b7da;">
                                    {{ $user->class_code ?? 'Belum Bergabung' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500"
                                    style="border-right: 1px solid #B3b7da; min-width: 150px;">
                                    @php
                                        $className = '-';
                                        if (
                                            $user->role === 'teacher' &&
                                            $user->teacher_level === 'kelas' &&
                                            $user->teacherClasses->count() > 0
                                        ) {
                                            $className = $user->teacherClasses->pluck('name')->join(', ');
                                        } elseif ($user->class->count() > 0) {
                                            $className = $user->class->first()->name;
                                        }
                                    @endphp
                                    {{ $className }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500"
                                    style="border-right: 1px solid #B3b7da; min-width: 150px;">
                                    @php
                                        $schoolName = '-';
                                        if ($user->role === 'teacher') {
                                            if (
                                                $user->teacher_level === 'admin' &&
                                                $user->schoolTeachers->count() > 0
                                            ) {
                                                $schoolName = $user->schoolTeachers->pluck('name')->join(', ');
                                            } elseif (
                                                $user->teacher_level === 'kelas' &&
                                                $user->teacherClasses->count() > 0
                                            ) {
                                                $schoolName = $user->teacherClasses
                                                    ->map(fn($c) => $c->school ? $c->school->name : '')
                                                    ->filter()
                                                    ->unique()
                                                    ->join(', ');
                                            }
                                        } elseif ($user->class->count() > 0 && $user->class->first()->school) {
                                            $schoolName = $user->class->first()->school->name;
                                        }
                                    @endphp
                                    {{ $schoolName }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    style="border-right: 1px solid #B3b7da;">
                                    {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium relative">
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
                                            class="hidden fixed w-52 bg-white rounded-lg shadow-lg z-50"
                                            style="min-width: 200px; border: 1px solid #010E82;">
                                            <!-- Arrow/Pointer -->
                                            <div id="actionMenuArrow-{{ $user->id }}" class="absolute w-0 h-0"
                                                style="right: 12px; top: -8px; border-left: 8px solid transparent; border-right: 8px solid transparent; border-bottom: 8px solid #010E82;">
                                            </div>
                                            <!-- Arrow inner untuk efek solid -->
                                            <div id="actionMenuArrowInner-{{ $user->id }}" class="absolute w-0 h-0"
                                                style="right: 12px; top: -7px; border-left: 7px solid transparent; border-right: 7px solid transparent; border-bottom: 7px solid white; z-index: 1;">
                                            </div>
                                            <div class="py-1 relative bg-white rounded-lg">
                                                @if ($user->role === 'user')
                                                    @if ($user->class->count() > 0)
                                                        {{-- Siswa: sudah terdaftar di kelas --}}
                                                        <button
                                                            onclick="openMoveClassModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                            class="w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-gray-100 flex items-center gap-2">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                            </svg>
                                                            Pindahkan Kelas
                                                        </button>
                                                        <button
                                                            onclick="removeFromClass({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                            class="w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-gray-100 flex items-center gap-2">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                            </svg>
                                                            Hapus dari Kelas
                                                        </button>
                                                    @else
                                                        {{-- User umum: belum terdaftar di kelas --}}
                                                        <button
                                                            onclick="openEnrollModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                            class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-100 flex items-center gap-2">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                            </svg>
                                                            Tambah ke Kelas
                                                        </button>
                                                    @endif
                                                @endif
                                                @if ($user->role !== 'admin')
                                                    <button onclick="resetPassword({{ $user->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                                            </path>
                                                        </svg>
                                                        Reset Password
                                                    </button>
                                                    <button onclick="deleteUser({{ $user->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                @else
                                                    <span class="block px-4 py-2 text-xs text-gray-400">Tidak ada
                                                        aksi</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
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

    <!-- Form Daftarkan ke Kelas -->
    <form id="enrollToClassForm" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="class_id" id="enrollClassId">
    </form>

    {{-- ===================== MODAL TAMBAH PENGGUNA ===================== --}}
    <div id="addUserModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60" onclick="closeAddUserModal()"></div>

        <!-- Modal Content -->
        <div class="bg-white rounded-[15px] shadow-xl w-full max-w-lg relative z-10 flex flex-col max-h-[92vh]">
            <!-- Modal Header -->
            <div
                class="p-5 border-b border-gray-100 flex-shrink-0 flex justify-between items-center bg-[#5087e4] rounded-t-[15px]">
                <h3 class="text-xl font-semibold text-white">Tambah Pengguna Baru</h3>
                <button onclick="closeAddUserModal()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Role Type Selector Tabs -->
            <div class="flex-shrink-0 p-4 pb-0">
                <p class="text-xs text-gray-500 mb-2 font-medium uppercase tracking-wide">Pilih Tipe Pengguna</p>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" onclick="setUserType('umum')" id="tab-umum"
                        class="user-type-tab flex flex-col items-center gap-1 p-3 rounded-xl border-2 border-gray-200 hover:border-[#5087e4] transition-all text-center">
                        <span class="text-xl">👤</span>
                        <span class="text-xs font-semibold text-gray-600">Umum</span>
                        <span class="text-[10px] text-gray-400 leading-tight">User biasa</span>
                    </button>
                    <button type="button" onclick="setUserType('student')" id="tab-student"
                        class="user-type-tab flex flex-col items-center gap-1 p-3 rounded-xl border-2 border-gray-200 hover:border-[#5087e4] transition-all text-center">
                        <span class="text-xl">🎓</span>
                        <span class="text-xs font-semibold text-gray-600">Siswa</span>
                        <span class="text-[10px] text-gray-400 leading-tight">Daftar ke kelas</span>
                    </button>
                    <button type="button" onclick="setUserType('teacher')" id="tab-teacher"
                        class="user-type-tab flex flex-col items-center gap-1 p-3 rounded-xl border-2 border-gray-200 hover:border-[#5087e4] transition-all text-center">
                        <span class="text-xl">📚</span>
                        <span class="text-xs font-semibold text-gray-600">Guru</span>
                        <span class="text-[10px] text-gray-400 leading-tight">Kelas / Admin</span>
                    </button>
                    <button type="button" onclick="setUserType('admin')" id="tab-admin"
                        class="user-type-tab flex flex-col items-center gap-1 p-3 rounded-xl border-2 border-gray-200 hover:border-[#5087e4] transition-all text-center">
                        <span class="text-xl">🛡️</span>
                        <span class="text-xs font-semibold text-gray-600">Admin</span>
                        <span class="text-[10px] text-gray-400 leading-tight">Super admin</span>
                    </button>
                </div>
            </div>

            <!-- Modal Form Body -->
            <div class="p-5 overflow-y-auto flex-1">
                <form id="addUserForm" method="POST" action="{{ route('admin.user.store') }}">
                    @csrf
                    <input type="hidden" name="user_type" id="userTypeInput" value="umum">

                    <!-- Informasi Deskriptif per tipe -->
                    <div id="typeDesc-umum" class="type-desc mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-600">Membuat akun pengguna biasa yang belum terdaftar di kelas manapun.
                        </p>
                    </div>
                    <div id="typeDesc-student"
                        class="type-desc hidden mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <p class="text-sm text-blue-700">Membuat akun siswa dan langsung mendaftarkannya ke dalam kelas
                            yang dipilih.</p>
                    </div>
                    <div id="typeDesc-teacher"
                        class="type-desc hidden mb-4 p-3 bg-green-50 rounded-lg border border-green-200">
                        <p class="text-sm text-green-700">Membuat akun guru (level kelas atau admin sekolah), dengan opsi
                            assign ke sekolah dan/atau kelas.</p>
                    </div>
                    <div id="typeDesc-admin"
                        class="type-desc hidden mb-4 p-3 bg-purple-50 rounded-lg border border-purple-200">
                        <p class="text-sm text-purple-700">Membuat akun admin dengan akses penuh ke seluruh sistem.</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Nama -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" id="addUserName" required
                                class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                                style="border: 1px solid #010E82;">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span
                                    class="text-red-500">*</span></label>
                            <input type="email" name="email" required
                                class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                                style="border: 1px solid #010E82;">
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password <span
                                    class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="password" id="addUserPassword" required
                                    class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] pr-10"
                                    style="border: 1px solid #010E82;">
                                <button type="button" id="toggleAddUserPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#1C0283] hover:text-[#0d4bb8] transition-colors focus:outline-none">
                                    <span id="eyeIconUserAdd"><x-eye-icon color="currentColor" /></span>
                                    <span id="eyeSlashIconUserAdd" class="hidden"><x-eye-slash-icon
                                            color="currentColor" /></span>
                                </button>
                            </div>
                        </div>

                        <!-- ====== FIELDS KHUSUS STUDENT ====== -->
                        <div id="studentFields" class="hidden space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kelas <span
                                        class="text-red-500">*</span></label>
                                <select id="studentClassSelect" name="class_id"
                                    class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                                    style="border: 1px solid #010E82;">
                                    <option value="">— Pilih Kelas —</option>
                                    @foreach ($allClasses as $cls)
                                        <option value="{{ $cls->id }}">
                                            {{ $cls->name }}{{ $cls->grade ? ' (Grade ' . $cls->grade . ')' : '' }}
                                            {{ $cls->school ? ' — ' . $cls->school->name : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- ====== FIELDS KHUSUS TEACHER ====== -->
                        <div id="teacherFields" class="hidden space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Level Guru <span
                                        class="text-red-500">*</span></label>
                                <select id="teacherLevelSelect" name="teacher_level"
                                    onchange="handleTeacherLevelChange()"
                                    class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                                    style="border: 1px solid #010E82;">
                                    <option value="">— Pilih Level —</option>
                                    <option value="kelas">Guru Kelas</option>
                                    <option value="admin">Admin Sekolah</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assign ke Sekolah
                                    <span class="text-xs text-gray-400">(opsional)</span>
                                </label>
                                <select id="teacherSchoolSelect" name="school_id" onchange="handleTeacherSchoolChange()"
                                    class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                                    style="border: 1px solid #010E82;">
                                    <option value="">— Tidak Assign ke Sekolah —</option>
                                    @foreach ($allSchools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Assign ke Kelas (hanya muncul jika level=kelas DAN sekolah dipilih) -->
                            <div id="teacherClassContainer" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assign ke Kelas
                                    <span class="text-xs text-gray-400">(opsional)</span>
                                </label>
                                <select id="teacherClassSelect" name="class_id"
                                    class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82]"
                                    style="border: 1px solid #010E82;">
                                    <option value="">— Tidak Assign ke Kelas —</option>
                                    @foreach ($allClasses as $cls)
                                        <option value="{{ $cls->id }}" data-school="{{ $cls->school_id }}">
                                            {{ $cls->name }}{{ $cls->grade ? ' (Grade ' . $cls->grade . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" onclick="closeAddUserModal()"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                            Batal
                        </button>
                        <button type="submit" id="submitUserBtn"
                            class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL DAFTARKAN KE KELAS ===================== --}}
    <div id="enrollModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" onclick="closeEnrollModal()"></div>
        <div class="bg-white rounded-[15px] shadow-xl w-full max-w-sm relative z-10">
            <div class="p-5 bg-[#5087e4] rounded-t-[15px] flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-white">Daftarkan ke Kelas</h3>
                    <p class="text-blue-100 text-sm" id="enrollUserName"></p>
                </div>
                <button onclick="closeEnrollModal()" class="text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kelas <span
                        class="text-red-500">*</span></label>
                <select id="enrollClassDropdown"
                    class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] mb-4"
                    style="border: 1px solid #010E82;">
                    <option value="">— Pilih Kelas —</option>
                    @foreach ($allClasses as $cls)
                        <option value="{{ $cls->id }}">
                            {{ $cls->name }}{{ $cls->grade ? ' (Grade ' . $cls->grade . ')' : '' }}
                            {{ $cls->school ? ' — ' . $cls->school->name : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeEnrollModal()"
                        class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm">
                        Batal
                    </button>
                    <button type="button" onclick="submitEnroll()"
                        class="px-5 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors text-sm">
                        Daftarkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL PINDAHKAN KELAS ===================== --}}
    <div id="moveClassModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" onclick="closeMoveClassModal()"></div>
        <div class="bg-white rounded-[15px] shadow-xl w-full max-w-sm relative z-10">
            <div class="p-5 rounded-t-[15px] flex justify-between items-center" style="background-color: #4f46e5;">
                <div>
                    <h3 class="text-lg font-semibold text-white">Pindahkan Kelas</h3>
                    <p class="text-indigo-100 text-sm" id="moveClassUserName"></p>
                </div>
                <button onclick="closeMoveClassModal()" class="text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kelas Baru <span
                        class="text-red-500">*</span></label>
                <select id="moveClassDropdown" class="w-full px-4 py-2 rounded-lg focus:ring-2 mb-4"
                    style="border: 1px solid #4f46e5;">
                    <option value="">— Pilih Kelas —</option>
                    @foreach ($allClasses as $cls)
                        <option value="{{ $cls->id }}">
                            {{ $cls->name }}{{ $cls->grade ? ' (Grade ' . $cls->grade . ')' : '' }}
                            {{ $cls->school ? ' — ' . $cls->school->name : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mb-4">Siswa akan dilepas dari kelas saat ini dan dipindah ke kelas baru.
                </p>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeMoveClassModal()"
                        class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm">
                        Batal
                    </button>
                    <button type="button" onclick="submitMoveClass()"
                        class="px-5 py-2 text-white rounded-lg transition-colors text-sm"
                        style="background-color: #4f46e5;">
                        Pindahkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Form tersembunyi: pindah kelas --}}
    <form id="moveClassForm" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="class_id" id="moveClassId">
    </form>

    {{-- Form tersembunyi: hapus dari kelas --}}
    <form id="removeFromClassForm" method="POST" style="display: none;">
        @csrf
    </form>

    <style>
        .user-type-tab.active {
            border-color: #010E82;
            background-color: #EEF1FF;
        }

        .user-type-tab.active span.text-gray-600 {
            color: #010E82;
        }
    </style>

    <script>
        /* ===================== PER PAGE ===================== */
        function changePerPage(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            window.location.href = url.toString();
        }

        /* ===================== ACTION MENU ===================== */
        function toggleActionMenu(userId) {
            const menu = document.getElementById('actionMenu-' + userId);
            const button = document.getElementById('actionBtn-' + userId);
            const isHidden = menu.classList.contains('hidden');

            document.querySelectorAll('[id^="actionMenu-"]').forEach(m => {
                if (m.id !== 'actionMenu-' + userId) {
                    m.classList.add('hidden');
                }
            });

            if (isHidden) {
                menu.classList.remove('hidden');
                const arrow = document.getElementById('actionMenuArrow-' + userId);
                const arrowInner = document.getElementById('actionMenuArrowInner-' + userId);

                requestAnimationFrame(() => {
                    const rect = button.getBoundingClientRect();
                    const menuHeight = 130;
                    const spaceBelow = window.innerHeight - rect.bottom;
                    const spaceAbove = rect.top;

                    menu.style.top = '';
                    menu.style.bottom = '';
                    menu.style.right = '';
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

                    if (spaceBelow < menuHeight && spaceAbove > menuHeight) {
                        menu.style.bottom = (window.innerHeight - rect.top + 8) + 'px';
                        arrow.style.bottom = '-8px';
                        arrow.style.borderTop = '8px solid #010E82';
                        arrowInner.style.bottom = '-7px';
                        arrowInner.style.borderTop = '7px solid white';
                    } else {
                        menu.style.top = (rect.bottom + 8) + 'px';
                        arrow.style.top = '-8px';
                        arrow.style.borderBottom = '8px solid #010E82';
                        arrowInner.style.top = '-7px';
                        arrowInner.style.borderBottom = '7px solid white';
                    }
                    menu.style.right = (window.innerWidth - rect.right) + 'px';
                });
            } else {
                menu.classList.add('hidden');
            }
        }

        document.addEventListener('click', function(event) {
            if (!event.target.closest('[id^="actionBtn-"]') && !event.target.closest('[id^="actionMenu-"]')) {
                document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => {
                    menu.classList.add('hidden');
                });
            }
        });

        // Tutup semua menu saat user scroll atau resize (menu posisi fixed tidak ikut bergerak)
        const closeAllMenus = () => {
            document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        };
        window.addEventListener('scroll', closeAllMenus, {
            passive: true
        });
        window.addEventListener('resize', closeAllMenus, {
            passive: true
        });
        // Juga tangkap scroll di dalam elemen overflow (misal tabel horizontall)
        document.querySelectorAll('.overflow-x-auto').forEach(el => {
            el.addEventListener('scroll', closeAllMenus, {
                passive: true
            });
        });

        /* ===================== RESET PASSWORD ===================== */
        function resetPassword(userId) {
            document.getElementById('actionMenu-' + userId).classList.add('hidden');
            Swal.fire({
                title: 'Reset Password?',
                text: 'Password akan direset menjadi "password123".',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#010E82',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Reset!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('resetPasswordForm');
                    form.action = '{{ route('admin.user.reset-password', ':id') }}'.replace(':id', userId);
                    form.submit();
                }
            });
        }

        /* ===================== DELETE USER ===================== */
        function deleteUser(userId) {
            document.getElementById('actionMenu-' + userId).classList.add('hidden');
            Swal.fire({
                title: 'Hapus Pengguna?',
                text: 'Tindakan ini tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteUserForm');
                    form.action = '{{ route('admin.user.delete', ':id') }}'.replace(':id', userId);
                    form.submit();
                }
            });
        }

        /* ===================== ADD USER MODAL ===================== */
        function openAddUserModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setUserType('umum');
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('addUserForm').reset();
        }

        function setUserType(type) {
            // Update hidden input
            document.getElementById('userTypeInput').value = type;

            // Update tabs visual
            ['umum', 'student', 'teacher', 'admin'].forEach(t => {
                const tab = document.getElementById('tab-' + t);
                tab.classList.remove('active');
            });
            document.getElementById('tab-' + type).classList.add('active');

            // Show/hide descriptions
            document.querySelectorAll('.type-desc').forEach(el => el.classList.add('hidden'));
            document.getElementById('typeDesc-' + type).classList.remove('hidden');

            // Show/hide contextual fields
            const studentFields = document.getElementById('studentFields');
            const teacherFields = document.getElementById('teacherFields');

            studentFields.classList.add('hidden');
            teacherFields.classList.add('hidden');

            // Toggle required on selects
            document.getElementById('studentClassSelect').required = false;
            document.getElementById('teacherLevelSelect').required = false;

            if (type === 'student') {
                studentFields.classList.remove('hidden');
                document.getElementById('studentClassSelect').required = true;
            } else if (type === 'teacher') {
                teacherFields.classList.remove('hidden');
                document.getElementById('teacherLevelSelect').required = true;
            }

            // Update button color per type
            const colors = {
                umum: ['#010E82', '#0B3BAA'],
                student: ['#1d4ed8', '#1e40af'],
                teacher: ['#15803d', '#166534'],
                admin: ['#7e22ce', '#6b21a8'],
            };
            const [bg, hover] = colors[type];
            const btn = document.getElementById('submitUserBtn');
            btn.style.backgroundColor = bg;
            btn.onmouseover = () => btn.style.backgroundColor = hover;
            btn.onmouseout = () => btn.style.backgroundColor = bg;
        }

        function handleTeacherLevelChange() {
            const level = document.getElementById('teacherLevelSelect').value;
            const schoolVal = document.getElementById('teacherSchoolSelect').value;
            const classContainer = document.getElementById('teacherClassContainer');

            // Show class picker only if level=kelas AND school is selected
            if (level === 'kelas' && schoolVal) {
                classContainer.classList.remove('hidden');
                filterTeacherClasses(schoolVal);
            } else {
                classContainer.classList.add('hidden');
            }
        }

        function handleTeacherSchoolChange() {
            const schoolVal = document.getElementById('teacherSchoolSelect').value;
            const level = document.getElementById('teacherLevelSelect').value;
            const classContainer = document.getElementById('teacherClassContainer');

            if (level === 'kelas' && schoolVal) {
                classContainer.classList.remove('hidden');
                filterTeacherClasses(schoolVal);
            } else {
                classContainer.classList.add('hidden');
            }
        }

        function filterTeacherClasses(schoolId) {
            const select = document.getElementById('teacherClassSelect');
            const options = select.querySelectorAll('option');
            options.forEach(opt => {
                if (!opt.value) return; // keep placeholder
                if (opt.dataset.school == schoolId) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                }
            });
            // reset selection if current selection is not in this school
            if (select.value && select.options[select.selectedIndex]?.dataset.school != schoolId) {
                select.value = '';
            }
        }

        /* ===================== ENROLL TO CLASS MODAL ===================== */
        let currentEnrollUserId = null;

        function openEnrollModal(userId, userName) {
            // Close action menu
            document.getElementById('actionMenu-' + userId).classList.add('hidden');
            currentEnrollUserId = userId;
            document.getElementById('enrollUserName').textContent = userName;
            document.getElementById('enrollClassDropdown').value = '';
            document.getElementById('enrollModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeEnrollModal() {
            document.getElementById('enrollModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            currentEnrollUserId = null;
        }

        function submitEnroll() {
            const classId = document.getElementById('enrollClassDropdown').value;
            if (!classId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Kelas',
                    text: 'Harap pilih kelas terlebih dahulu.',
                    confirmButtonColor: '#010E82'
                });
                return;
            }
            const form = document.getElementById('enrollToClassForm');
            document.getElementById('enrollClassId').value = classId;
            form.action = '{{ route('admin.user.add-to-class', ':id') }}'.replace(':id', currentEnrollUserId);
            form.submit();
        }

        /* ===================== MOVE CLASS MODAL ===================== */
        let currentMoveUserId = null;

        function openMoveClassModal(userId, userName) {
            document.getElementById('actionMenu-' + userId).classList.add('hidden');
            currentMoveUserId = userId;
            document.getElementById('moveClassUserName').textContent = userName;
            document.getElementById('moveClassDropdown').value = '';
            document.getElementById('moveClassModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeMoveClassModal() {
            document.getElementById('moveClassModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            currentMoveUserId = null;
        }

        function submitMoveClass() {
            const classId = document.getElementById('moveClassDropdown').value;
            if (!classId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Kelas',
                    text: 'Harap pilih kelas tujuan terlebih dahulu.',
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }
            const form = document.getElementById('moveClassForm');
            document.getElementById('moveClassId').value = classId;
            form.action = '{{ route('admin.user.move-class', ':id') }}'.replace(':id', currentMoveUserId);
            form.submit();
        }

        /* ===================== REMOVE FROM CLASS ===================== */
        function removeFromClass(userId, userName) {
            document.getElementById('actionMenu-' + userId).classList.add('hidden');
            Swal.fire({
                title: 'Hapus dari Kelas?',
                html: `<b>${userName}</b> akan dilepas dari kelasnya. Akun tidak akan dihapus.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ea580c',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus dari Kelas',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('removeFromClassForm');
                    form.action = '{{ route('admin.user.remove-from-class', ':id') }}'.replace(':id', userId);
                    form.submit();
                }
            });
        }

        /* ===================== PASSWORD TOGGLE ===================== */
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('addUserPassword');
            const toggle = document.getElementById('toggleAddUserPassword');
            const eye = document.getElementById('eyeIconUserAdd');
            const eyeSlash = document.getElementById('eyeSlashIconUserAdd');

            if (input && toggle) {
                toggle.addEventListener('click', function() {
                    if (input.type === 'password') {
                        input.type = 'text';
                        eye.classList.add('hidden');
                        eyeSlash.classList.remove('hidden');
                    } else {
                        input.type = 'password';
                        eye.classList.remove('hidden');
                        eyeSlash.classList.add('hidden');
                    }
                });
            }

            // Re-open modal if validation failed (errors present)
            @if ($errors->any())
                openAddUserModal();
            @endif

            // Prevent duplicate field submission: disable inputs in hidden containers before submit
            document.getElementById('addUserForm').addEventListener('submit', function() {
                const hiddenContainers = ['studentFields', 'teacherFields', 'teacherClassContainer'];
                hiddenContainers.forEach(id => {
                    const container = document.getElementById(id);
                    if (container && container.classList.contains('hidden')) {
                        container.querySelectorAll('input, select, textarea').forEach(el => {
                            el.disabled = true;
                        });
                    }
                });
            });
        }); // end DOMContentLoaded
    </script>
@endsection
