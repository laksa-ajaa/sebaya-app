<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password Berhasil</title>
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

          <h2 class="text-xl md:text-2xl font-bold bg-clip-text text-transparent mb-6"
            style="background-image: linear-gradient(90deg, #0553D9 0%, #030D66 100%);">
            Reset Password Berhasil
          </h2>

          <div class="mb-8 text-slate-700 leading-relaxed font-medium">
            <p>Kata sandi Anda telah berhasil diperbarui.</p>
            <p class="mt-2 text-sm text-slate-500">Anda sekarang dapat kembali ke aplikasi mobile Sebaya dan masuk menggunakan kata sandi baru Anda.</p>
          </div>

          <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 mb-8">
            <div class="flex items-center justify-center mb-4">
              <div class="bg-blue-600 rounded-full p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
            <p class="text-blue-800 text-sm">Silakan tutup tab browser ini dan kembali ke aplikasi.</p>
          </div>

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
