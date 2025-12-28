<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifikasi OTP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f3f7ff] min-h-screen antialiased text-slate-900">
  {{-- HEADER SECTION --}}
  <div class="bg-[#010E82] pt-16 pb-28 px-8 md:px-20 rounded-bl-[80px] md:rounded-bl-[150px]">
    <div class="max-w-5xl mx-auto">
      <h1 class="text-white text-2xl md:text-3xl font-bold mb-2">Silahkan Masukkan Kode OTP</h1>
      <p class="text-white text-sm md:text-base opacity-90">
        Kode OTP dikirimkan melalui Gmail
      </p>
    </div>
  </div>

  {{-- FORM SECTION --}}
  <div class="max-w-5xl mx-auto px-6 mt-16 pb-20 text-center">
    <h2 class="text-[#1C0283] text-2xl font-bold mb-12">Kode OTP</h2>

    @if (session('success'))
      <div class="mb-6 text-sm text-green-600 font-semibold">
        {{ session('success') }}
      </div>
    @endif

    <form id="otpForm" method="POST" action="{{ route('teacher.otp.submit') }}" class="max-w-2xl mx-auto">
      @csrf
      <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}">

      {{-- OTP Inputs --}}
      <div class="flex justify-center gap-3 md:gap-6 mb-12">
        @for ($i = 0; $i < 6; $i++)
          <input type="text" maxlength="1"
            class="otp-field w-10 h-12 md:w-14 md:h-16 text-center text-2xl font-bold border-b-2 border-[#1C0283] bg-transparent outline-none focus:border-blue-500 transition-colors"
            data-index="{{ $i }}">
        @endfor
      </div>
      <input type="hidden" name="otp_code" id="otp_code_hidden">

      @error('otp_code')
        <p class="mb-6 text-sm text-red-600">{{ $message }}</p>
      @enderror
      @error('email')
        <p class="mb-6 text-sm text-red-600">{{ $message }}</p>
      @enderror

      {{-- Submit Button --}}
      <div class="flex justify-center">
        <button type="submit"
          class="w-full md:w-1/2 rounded-full bg-[#0D53D9] py-3 text-white font-semibold hover:bg-[#0b46b8] transition-colors shadow-lg">
          Kirim
        </button>
      </div>
    </form>

    <div class="mt-12 text-sm text-slate-600">
      Tidak menerima kode?
      <form method="POST" action="{{ route('teacher.otp.resend') }}" class="inline">
        @csrf
        <input type="hidden" name="email" value="{{ $email ?? '' }}">
        <button type="submit" class="font-semibold text-[#1C0283] hover:underline">
          Kirim ulang
        </button>
      </form>
    </div>

    <div class="mt-8 text-sm text-slate-600">
      Belum punya akun?
      <a href="{{ route('teacher.register.show') }}" class="font-semibold text-[#1C0283] hover:underline">
        Daftar disini
      </a>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const fields = document.querySelectorAll('.otp-field');
      const hiddenInput = document.getElementById('otp_code_hidden');
      const form = document.getElementById('otpForm');

      fields.forEach((field, index) => {
        field.addEventListener('input', (e) => {
          if (e.target.value.length === 1 && index < fields.length - 1) {
            fields[index + 1].focus();
          }
          updateHiddenInput();
        });

        field.addEventListener('keydown', (e) => {
          if (e.key === 'Backspace' && !e.target.value && index > 0) {
            fields[index - 1].focus();
          }
        });
      });

      function updateHiddenInput() {
        let code = '';
        fields.forEach(field => code += field.value);
        hiddenInput.value = code;
      }

      form.addEventListener('submit', function(e) {
        updateHiddenInput();
      });
    });
  </script>
</body>

</html>
