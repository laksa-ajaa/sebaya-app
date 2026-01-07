<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Atur Ulang Kata Sandi</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen antialiased text-slate-900">
  <div class="min-h-screen bg-[#010E82] relative overflow-hidden">

    {{-- LOGO --}}
    <div class="absolute top-5 left-1/2 -translate-x-1/2 text-white">
      <img src="{{ asset('sebaya-full.svg') }}" class="h-40">
    </div>

    {{-- SECTION BAWAH --}}
    <div class="absolute inset-x-0 bottom-0">
      <div class="bg-[#f3f7ff] min-h-[70vh]
                    rounded-tl-[120px] md:rounded-tl-[150px]">

        <div class="mx-auto max-w-md px-6 py-10 md:py-14">

          <h2 class="text-center text-xl md:text-2xl font-bold bg-clip-text text-transparent"
            style="background-image: linear-gradient(90deg, #0553D9 0%, #030D66 100%);">
            Atur Ulang Kata Sandi
          </h2>

          {{-- ERROR --}}
          @if ($errors->any())
            <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600 border border-red-200">
              {{ $errors->first() }}
            </div>
          @endif

          {{-- FORM --}}
          <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <input type="hidden" name="source" value="{{ $source }}">

            <div>
              <input type="email" name="email" value="{{ old('email', $request->email) }}" required readonly
                class="w-full rounded-full border border-slate-200 bg-slate-100
                                 px-5 py-3 text-sm outline-none cursor-not-allowed">
            </div>

            <div class="relative">
              <input type="password" id="password" name="password" required placeholder="Kata Sandi Baru"
                class="w-full rounded-full border border-[#1C0283]
                                   px-5 py-3 text-sm outline-none
                                   focus:ring-2 focus:ring-blue-300">
              <button type="button" id="togglePassword"
                class="absolute right-5 top-1/2 -translate-y-1/2 text-[#1C0283] 
                                       hover:text-[#0d4bb8] transition-colors focus:outline-none">
                <span id="eyeIcon">
                  <x-eye-icon color="currentColor" />
                </span>
                <span id="eyeSlashIcon" class="hidden">
                  <x-eye-slash-icon color="currentColor" />
                </span>
              </button>
            </div>

            <div class="relative">
              <input type="password" id="password_confirmation" name="password_confirmation" required
                placeholder="Konfirmasi Kata Sandi Baru"
                class="w-full rounded-full border border-[#1C0283]
                                   px-5 py-3 text-sm outline-none
                                   focus:ring-2 focus:ring-blue-300">
              <button type="button" id="togglePasswordConfirm"
                class="absolute right-5 top-1/2 -translate-y-1/2 text-[#1C0283] 
                                       hover:text-[#0d4bb8] transition-colors focus:outline-none">
                <span id="eyeIconConfirm">
                  <x-eye-icon color="currentColor" />
                </span>
                <span id="eyeSlashIconConfirm" class="hidden">
                  <x-eye-slash-icon color="currentColor" />
                </span>
              </button>
            </div>

            <button
              class="w-full rounded-full bg-linear-to-r
                                from-[#0d4bb8] to-[#0b3fa1]
                                py-3 text-white font-semibold shadow-lg hover:shadow-xl transition-all">
              Atur Ulang Kata Sandi
            </button>
          </form>

        </div>
      </div>
    </div>

  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Toggle for Password
      const passwordInput = document.getElementById('password');
      const toggleButton = document.getElementById('togglePassword');
      const eyeIcon = document.getElementById('eyeIcon');
      const eyeSlashIcon = document.getElementById('eyeSlashIcon');

      if (passwordInput && toggleButton) {
        toggleButton.addEventListener('click', function() {
          if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeSlashIcon.classList.remove('hidden');
          } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeSlashIcon.classList.add('hidden');
          }
        });
      }

      // Toggle for Password Confirmation
      const confirmInput = document.getElementById('password_confirmation');
      const toggleConfirmButton = document.getElementById('togglePasswordConfirm');
      const eyeIconConfirm = document.getElementById('eyeIconConfirm');
      const eyeSlashIconConfirm = document.getElementById('eyeSlashIconConfirm');

      if (confirmInput && toggleConfirmButton) {
        toggleConfirmButton.addEventListener('click', function() {
          if (confirmInput.type === 'password') {
            confirmInput.type = 'text';
            eyeIconConfirm.classList.add('hidden');
            eyeSlashIconConfirm.classList.remove('hidden');
          } else {
            confirmInput.type = 'password';
            eyeIconConfirm.classList.remove('hidden');
            eyeSlashIconConfirm.classList.add('hidden');
          }
        });
      }
    });
  </script>
</body>

</html>
