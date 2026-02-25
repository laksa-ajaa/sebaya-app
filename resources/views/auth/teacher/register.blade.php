<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi Guru</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f3f7ff] min-h-screen antialiased text-slate-900">
  {{-- HEADER SECTION --}}
  <div class="bg-[#010E82] pt-16 pb-28 px-8 md:px-20 rounded-br-[80px] md:rounded-br-[150px]">
    <div class="max-w-5xl mx-auto">
      <h1 class="text-white text-3xl md:text-4xl font-bold mb-4">Daftar Akun</h1>
      <p class="text-white text-sm md:text-base opacity-90">
        Silahkan lengkapi data diri anda untuk dapat menjadi guru di Sebaya
      </p>
    </div>
  </div>

  {{-- FORM SECTION --}}
  <div class="max-w-5xl mx-auto mt-10 pb-20">
    <form method="POST" action="{{ route('teacher.register.submit') }}" class="space-y-6">
      @csrf

      {{-- Nama --}}
      <div>
        <label class="block text-sm font-semibold text-[#1C0283] mb-2 ml-1">Nama</label>
        <input type="text" name="name" value="{{ old('name') }}" required
          placeholder="Silahkan isi nama lengkap anda"
          class="w-full rounded-full border border-[#1C0283] bg-white px-6 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-300 placeholder:text-slate-300">
        @error('name')
          <p class="mt-1 text-xs text-red-600 ml-4">{{ $message }}</p>
        @enderror
      </div>

      {{-- Username (Hidden or kept for functionality, but following image layout) --}}
      <input type="hidden" name="username" value="{{ old('username', 'user_' . time()) }}">

      {{-- Gmail --}}
      <div>
        <label class="block text-sm font-semibold text-[#1C0283] mb-2 ml-1">Gmail (pastikan tidak ada kesalahan)</label>
        <input type="email" name="email" value="{{ old('email') }}" required placeholder="Silahkan Isi Gmail Anda"
          class="w-full rounded-full border border-[#1C0283] bg-white px-6 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-300 placeholder:text-slate-300">
        @error('email')
          <p class="mt-1 text-xs text-red-600 ml-4">{{ $message }}</p>
        @enderror
      </div>

      {{-- Nomor WhatsApp --}}
      <div>
        <label class="block text-sm font-semibold text-[#1C0283] mb-2 ml-1">Nomor WhatsApp</label>
        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number') }}" required
          placeholder="Silahkan isi nomor WhatsApp anda"
          class="w-full rounded-full border border-[#1C0283] bg-white px-6 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-300 placeholder:text-slate-300">
        @error('whatsapp_number')
          <p class="mt-1 text-xs text-red-600 ml-4">{{ $message }}</p>
        @enderror
      </div>

      {{-- Nama Sekolah & NPSN --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-[#1C0283] mb-2 ml-1">Nama Sekolah</label>
          <input type="text" name="school_name" value="{{ old('school_name') }}" required
            placeholder="Silahkan isi nama sekolah, tempat anda mengajar"
            class="w-full rounded-full border border-[#1C0283] bg-white px-6 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-300 placeholder:text-slate-300">
          @error('school_name')
            <p class="mt-1 text-xs text-red-600 ml-4">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="block text-sm font-semibold text-[#1C0283] mb-2 ml-1">Nomor Pokok Sekolah / Kode Perguruan
            Tinggi</label>
          <input type="text" name="school_npsn" value="{{ old('school_npsn') }}"
            placeholder="Nomor Pokok Sekolah Nasional (NPSN) atau Kode Perguruan Tinggi"
            class="w-full rounded-full border border-[#1C0283] bg-white px-6 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-300 placeholder:text-slate-300">
          @error('school_npsn')
            <p class="mt-1 text-xs text-red-600 ml-4">{{ $message }}</p>
          @enderror
        </div>
      </div>

      {{-- Password & Konfirmasi --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-[#1C0283] mb-2 ml-1">Password</label>
          <div class="relative">
            <input type="password" name="password" id="password" required placeholder="Password"
              class="w-full rounded-full border border-[#1C0283] bg-white px-6 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-300 placeholder:text-slate-300 pr-12">
            <button type="button" id="togglePassword"
              class="absolute right-4 top-1/2 -translate-y-1/2 text-[#1C0283] hover:text-[#0d4bb8] transition-colors focus:outline-none">
              <span id="eyeIcon">
                <x-eye-icon color="currentColor" />
              </span>
              <span id="eyeSlashIcon" class="hidden">
                <x-eye-slash-icon color="currentColor" />
              </span>
            </button>
          </div>
          @error('password')
            <p class="mt-1 text-xs text-red-600 ml-4">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="block text-sm font-semibold text-[#1C0283] mb-2 ml-1">Verifikasi Password</label>
          <div class="relative">
            <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Verifikasi Password"
              class="w-full rounded-full border border-[#1C0283] bg-white px-6 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-300 placeholder:text-slate-300 pr-12">
            <button type="button" id="togglePasswordConfirmation"
              class="absolute right-4 top-1/2 -translate-y-1/2 text-[#1C0283] hover:text-[#0d4bb8] transition-colors focus:outline-none">
              <span id="eyeIconConfirmation">
                <x-eye-icon color="currentColor" />
              </span>
              <span id="eyeSlashIconConfirmation" class="hidden">
                <x-eye-slash-icon color="currentColor" />
              </span>
            </button>
          </div>
        </div>
      </div>

      {{-- Submit Button --}}
      <div class="pt-8 flex justify-center">
        <button type="submit"
          class="w-full md:w-1/3 rounded-full bg-[#0D53D9] py-3 text-white font-semibold hover:bg-[#0b46b8] transition-colors shadow-lg">
          Kirim
        </button>
      </div>
    </form>

    <div class="mt-8 text-center text-sm text-slate-600">
      Sudah punya akun?
      <a href="{{ route('login') }}" class="font-semibold text-[#0D53D9] hover:underline">
        Masuk di sini
      </a>
    </div>
  </div>

  <script>
    function setupPasswordToggle(inputId, toggleId, eyeId, eyeSlashId) {
      const input = document.getElementById(inputId);
      const toggle = document.getElementById(toggleId);
      const eye = document.getElementById(eyeId);
      const eyeSlash = document.getElementById(eyeSlashId);

      if (input && toggle && eye && eyeSlash) {
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
    }

    document.addEventListener('DOMContentLoaded', function() {
      setupPasswordToggle('password', 'togglePassword', 'eyeIcon', 'eyeSlashIcon');
      setupPasswordToggle('password_confirmation', 'togglePasswordConfirmation', 'eyeIconConfirmation', 'eyeSlashIconConfirmation');
    });
  </script>
</body>

</html>
