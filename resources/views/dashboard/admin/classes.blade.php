@extends('layouts.app')

@section('title', 'Manajemen Kelas')

@section('content')
    <div class="px-6 py-6 bg-blue-100 min-h-screen">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-[#010E82]">Manajemen Kelas</h1>
                <p class="text-gray-600 mt-1">Kelola data kelas</p>
            </div>
            <button onclick="openModal('create')" 
                    class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
                + Tambah Kelas
            </button>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Statistik Card -->
        <div class="bg-white rounded-[15px] p-6 mb-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
            <div class="text-center">
                <p class="text-gray-600 text-sm mb-2">Total Kelas</p>
                <p class="text-3xl font-bold text-[#010E82]">{{ number_format($totalClasses) }}</p>
            </div>
        </div>

        <!-- Filter dan Search -->
        <div class="bg-white rounded-[15px] p-6 mb-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
            <form method="GET" action="{{ route('admin.classes') }}" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}"
                           placeholder="Cari nama kelas atau grade..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent">
                </div>
                <div>
                    <select name="school_id" 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent">
                        <option value="">Semua Sekolah</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ $schoolId == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" 
                        class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
                    Cari
                </button>
                @if($search || $schoolId)
                    <a href="{{ route('admin.classes') }}" 
                       class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Kelas -->
        <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead style="background-color: #5087E4;">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                Nama Kelas
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                Grade
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                Sekolah
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($classes as $class)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $class->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $class->grade ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $class->school->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openModal('edit', {{ $class->id }}, '{{ $class->name }}', '{{ $class->grade ?? '' }}', {{ $class->school_id }})" 
                                            class="text-[#010E82] hover:text-[#0B3BAA] mr-4">Edit</button>
                                    <button onclick="deleteClass({{ $class->id }})" 
                                            class="text-red-600 hover:text-red-800">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada kelas ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($classes->hasPages())
                <div class="mt-6">
                    {{ $classes->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Create/Edit -->
    <div id="classModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">Tambah Kelas</h3>
                <form id="classForm" method="POST">
                    @csrf
                    <div id="methodField"></div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sekolah *</label>
                        <select name="school_id" id="classSchoolId" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent">
                            <option value="">Pilih Sekolah</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas *</label>
                        <input type="text" name="name" id="className" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
                        <input type="text" name="grade" id="classGrade" placeholder="contoh: 7, 8, 9"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent">
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeModal()" 
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA]">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function openModal(action, id = null, name = '', grade = '', schoolId = '') {
            const modal = document.getElementById('classModal');
            const form = document.getElementById('classForm');
            const methodField = document.getElementById('methodField');
            const title = document.getElementById('modalTitle');
            
            if (action === 'create') {
                title.textContent = 'Tambah Kelas';
                form.action = '{{ route("admin.kelas.store") }}';
                methodField.innerHTML = '';
                document.getElementById('className').value = '';
                document.getElementById('classGrade').value = '';
                document.getElementById('classSchoolId').value = '';
            } else {
                title.textContent = 'Edit Kelas';
                form.action = '{{ route("admin.kelas.update", ":id") }}'.replace(':id', id);
                methodField.innerHTML = '@method("PUT")';
                document.getElementById('className').value = name;
                document.getElementById('classGrade').value = grade;
                document.getElementById('classSchoolId').value = schoolId;
            }
            
            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('classModal').classList.add('hidden');
        }

        function deleteClass(id) {
            if (confirm('Apakah Anda yakin ingin menghapus kelas ini?')) {
                const form = document.getElementById('deleteForm');
                form.action = '{{ route("admin.kelas.delete", ":id") }}'.replace(':id', id);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('classModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
@endsection

