<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Lupa Kata Sandi</title>
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
            Lupa Kata Sandi
          </h2>

          <p class="mt-4 text-center text-sm text-slate-600">
            Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
          </p>

          {{-- STATUS --}}
          @if (session('status'))
            <div class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-600 border border-green-200">
              {{ session('status') }}
            </div>
          @endif

          {{-- ERROR --}}
          @if ($errors->any())
            <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600 border border-red-200">
              {{ $errors->first() }}
            </div>
          @endif

          {{-- FORM --}}
          <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
            @csrf

            <div>
              <input type="email" name="email" :value="old('email')" required autofocus placeholder="Email"
                class="w-full rounded-full border border-[#1C0283]
                                 px-5 py-3 text-sm outline-none
                                 focus:ring-2 focus:ring-blue-300">
            </div>

            <button
              class="w-full rounded-full bg-linear-to-r
                                from-[#0d4bb8] to-[#0b3fa1]
                                py-3 text-white font-semibold shadow-lg hover:shadow-xl transition-all">
              Kirim Tautan Atur Ulang
            </button>
          </form>

          <div class="mt-8 text-center text-sm">
            <a href="{{ route('login') }}" class="text-blue-700 hover:text-blue-900 font-medium">
              Kembali ke Halaman Masuk
            </a>
          </div>

        </div>
      </div>
    </div>

  </div>
</body>

</html>
