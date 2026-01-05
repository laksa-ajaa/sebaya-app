<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Informasi Sekolah</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f3f7ff] min-h-screen antialiased text-slate-900">
  {{-- HEADER SECTION --}}
  <div class="bg-[#010E82] pt-16 pb-24 px-8 md:px-20 rounded-bl-[80px] md:rounded-bl-[150px]">
    <div class="max-w-3xl mx-auto">
      <h1 class="text-white text-2xl md:text-3xl font-bold mb-2">Lengkapi Informasi Sekolah</h1>
      <p class="text-white text-sm md:text-base opacity-90">
        Untuk melanjutkan, silakan lengkapi informasi sekolah tempat Anda mengajar.
      </p>
    </div>
  </div>

  {{-- FORM SECTION --}}
  <div class="max-w-3xl mx-auto px-6 mt-12 pb-16">
    @if (session('success'))
      <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-600">
        {{ session('success') }}
      </div>
    @endif

    @if (session('error'))
      <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">
        {{ session('error') }}
      </div>
    @endif

    <form method="POST" action="{{ route('teacher.school-info.submit') }}"
      class="bg-white shadow rounded-2xl p-6 md:p-8 space-y-6">
      @csrf

      <div>
        <label for="school_name" class="block text-sm font-semibold text-slate-700 mb-2">
          Nama Sekolah <span class="text-red-500">*</span>
        </label>
        <input id="school_name" name="school_name" type="text" value="{{ old('school_name') }}" required
          placeholder="Contoh: SMA Negeri 1 Jakarta"
          class="w-full rounded-lg border border-slate-200 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
        @error('school_name')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="school_npsn" class="block text-sm font-semibold text-slate-700 mb-2">
          NPSN (Nomor Pokok Sekolah Nasional)
        </label>
        <input id="school_npsn" name="school_npsn" type="text" value="{{ old('school_npsn') }}"
          placeholder="8 digit angka (opsional)"
          class="w-full rounded-lg border border-slate-200 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
        @error('school_npsn')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="school_address" class="block text-sm font-semibold text-slate-700 mb-2">
          Alamat Sekolah
        </label>
        <textarea id="school_address" name="school_address" rows="3" placeholder="Alamat lengkap sekolah (opsional)"
          class="w-full rounded-lg border border-slate-200 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none resize-none">{{ old('school_address') }}</textarea>
        @error('school_address')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="school_phone" class="block text-sm font-semibold text-slate-700 mb-2">
          Nomor Telepon Sekolah
        </label>
        <input id="school_phone" name="school_phone" type="text" value="{{ old('school_phone') }}"
          placeholder="Contoh: 021-1234567 (opsional)"
          class="w-full rounded-lg border border-slate-200 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
        @error('school_phone')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <button type="submit"
        class="w-full rounded-full bg-linear-to-r from-[#0d4bb8] to-[#0b3fa1] py-3 text-white font-semibold hover:opacity-90 transition-opacity">
        Simpan & Lanjutkan
      </button>
    </form>

    <div class="mt-8 text-sm text-slate-600 text-center">
      <p>Data ini akan diverifikasi oleh admin sebelum akun Anda dapat digunakan.</p>
    </div>
  </div>
</body>

</html>
