<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pendaftaran Berhasil</title>
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

        <div class="mx-auto max-w-md px-6 py-10 md:py-14 text-center">



          @php $isAuthenticated = auth()->check(); @endphp

          @if (!$isAuthenticated)
            <h2 class="text-xl md:text-2xl font-bold bg-clip-text text-transparent mb-6"
              style="background-image: linear-gradient(90deg, #0553D9 0%, #030D66 100%);">
              Pendaftaran Berhasil
            </h2>
          @else
            <h2 class="text-xl md:text-2xl font-bold bg-clip-text text-transparent mb-6"
              style="background-image: linear-gradient(90deg, #0553D9 0%, #030D66 100%);">
              Menunggu Verifikasi
            </h2>
          @endif
          <div class="mb-8 text-slate-700 leading-relaxed">
            @if (!$isAuthenticated)
              <p>Terima kasih telah mendaftar. Akun Anda saat ini sedang menunggu proses verifikasi oleh admin.
                Kami akan mengirimkan pemberitahuan melalui email terdaftar setelah akun Anda disetujui.</p>
            @else
              <p>Akun Anda sedang menunggu verifikasi admin. Kami akan memberitahu Anda saat akun disetujui.
                Anda dapat keluar terlebih dahulu dan kembali lagi setelah menerima konfirmasi.</p>
            @endif
          </div>

          @if (!$isAuthenticated)
            <a href="/login"
              class="inline-block w-full rounded-full bg-linear-to-r from-[#0d4bb8] to-[#0b3fa1] py-3 text-white font-semibold hover:opacity-90 transition-opacity">
              Kembali ke Halaman Login
            </a>
          @else
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit"
                class="w-full rounded-full bg-linear-to-r from-[#0d4bb8] to-[#0b3fa1] py-3 text-white font-semibold hover:opacity-90 transition-opacity">
                Keluar
              </button>
            </form>
          @endif

          <div class="mt-8 text-sm text-slate-600">
            <p>Ada Kendala? hubungi <a href="mailto:temansebaya@gmail.com"
                class="text-blue-600 hover:underline">temansebaya@gmail.com</a></p>
          </div>
        </div>
      </div>
    </div>

  </div>
</body>

</html>
