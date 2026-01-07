<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Masuk Akun</title>
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
      <div class="bg-[#f3f7ff] min-h-[65vh]
                    rounded-tl-[120px] md:rounded-tl-[150px]">

        <div class="mx-auto max-w-md px-6 py-10 md:py-14">

          <h2 class="text-center text-xl md:text-2xl font-bold bg-clip-text text-transparent"
            style="background-image: linear-gradient(90deg, #0553D9 0%, #030D66 100%);">
            Masuk Akun
          </h2>

          {{-- STATUS --}}
          @if (session('status'))
            <div class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-600 border border-green-200">
              {{ session('status') }}
            </div>
          @endif

          {{-- ERROR --}}
          @if ($errors->any())
            <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">
              {{ $errors->first() }}
            </div>
          @endif

          {{-- FORM --}}
          <form method="POST" action="{{ route('authenticated') }}" class="mt-8 space-y-5">
            @csrf

            <input type="email" name="email" required placeholder="Email"
              class="w-full rounded-full border border-[#1C0283]
                               px-5 py-3 text-sm outline-none
                               focus:ring-2 focus:ring-blue-300">

            <div class="relative">
              <input type="password" id="password" name="password" required placeholder="Password"
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

            <div class="text-right text-xs text-blue-700">
              <a href="{{ route('password.request') }}">Lupa Kata Sandi?</a>
            </div>

            <button
              class="w-full rounded-full bg-linear-to-r
                               from-[#0d4bb8] to-[#0b3fa1]
                               py-3 text-white font-semibold">
              Masuk
            </button>
          </form>

          <div class="mt-6 text-center text-sm text-slate-700">
            Belum punya akun?
            <a href="{{ route('teacher.register.show') }}" class="font-semibold text-blue-700 hover:text-blue-900">
              Daftar sebagai Guru
            </a>
          </div>

          <div class="mt-4">
            <div class="flex items-center justify-center space-x-3">
              <span class="h-px w-16 bg-slate-300"></span>
              <span class="text-sm text-slate-500">atau</span>
              <span class="h-px w-16 bg-slate-300"></span>
            </div>

            <div class="mt-4 flex justify-center space-x-3">
              <div id="googleSignIn" class="w-full flex justify-center"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const passwordInput = document.getElementById('password');
      const toggleButton = document.getElementById('togglePassword');
      const eyeIcon = document.getElementById('eyeIcon');
      const eyeSlashIcon = document.getElementById('eyeSlashIcon');

      if (passwordInput && toggleButton && eyeIcon && eyeSlashIcon) {
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
    });
  </script>
  <script src="https://accounts.google.com/gsi/client" async defer></script>
  <script>
    function handleCredentialResponse(response) {
      const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const id_token = response.credential;

      console.log('Google Sign-In successful, sending to server...');

      fetch('{{ route('login.google') }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          id_token: id_token
        })
      }).then(async res => {
        if (res.ok) {
          const data = await res.json().catch(() => ({}));
          if (data.redirect) {
            window.location.href = data.redirect;
          } else {
            window.location.reload();
          }
        } else {
          const errorText = await res.text();
          console.error('Server error:', errorText);
          alert('Login gagal: ' + (errorText || 'Terjadi kesalahan'));
        }
      }).catch(err => {
        console.error('Network error:', err);
        alert('Login gagal: ' + err.message);
      });
    }

    // Initialize Google Sign-In when window loads
    window.onload = function() {
      const clientId = '{{ env('GOOGLE_CLIENT_ID') }}';

      console.log('Initializing Google Sign-In...');
      console.log('Client ID configured:', clientId ? 'Yes' : 'No');

      if (!clientId || clientId === '') {
        console.error('❌ GOOGLE_CLIENT_ID not configured in .env file');
        const container = document.getElementById('googleSignIn');
        if (container) {
          container.innerHTML = '<p class="text-red-500 text-xs text-center">Google Client ID belum dikonfigurasi</p>';
        }
        return;
      }

      // Wait for Google API to be fully loaded
      const checkGoogleLoaded = setInterval(function() {
        if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
          clearInterval(checkGoogleLoaded);

          console.log('✓ Google API loaded successfully');

          try {
            google.accounts.id.initialize({
              client_id: clientId,
              callback: handleCredentialResponse,
              auto_select: false,
              cancel_on_tap_outside: true
            });

            // Render the button
            google.accounts.id.renderButton(
              document.getElementById('googleSignIn'), {
                theme: 'outline',
                size: 'large',
                width: document.getElementById('googleSignIn').offsetWidth || 350,
                text: 'signin_with',
                shape: 'pill',
                logo_alignment: 'left',
                locale: 'id'
              }
            );

            console.log('✓ Google Sign-In button rendered');
          } catch (error) {
            console.error('❌ Error initializing Google Sign-In:', error);
          }
        }
      }, 100);

      // Timeout after 10 seconds
      setTimeout(function() {
        clearInterval(checkGoogleLoaded);
        if (typeof google === 'undefined') {
          console.error('❌ Google API failed to load after 10 seconds');
          const container = document.getElementById('googleSignIn');
          if (container && !container.hasChildNodes()) {
            container.innerHTML = '<p class="text-red-500 text-xs text-center">Gagal memuat Google Sign-In</p>';
          }
        }
      }, 10000);
    };
  </script>
</body>

</html>
