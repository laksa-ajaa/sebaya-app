<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Penghapusan Akun dan Data – Sebaya Mobile</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen antialiased text-slate-900 bg-[#010E82]">
  <div class="min-h-screen flex flex-col relative">

    {{-- LOGO --}}
    <div class="pt-5 text-white text-center">
      <img src="{{ asset('sebaya-full.svg') }}" class="h-40 mx-auto">
    </div>

    {{-- SECTION BAWAH --}}
    <div class="flex-1 mt-auto">
      <div class="bg-[#f3f7ff] rounded-tl-[120px] md:rounded-tl-[150px]">

        <div class="mx-auto max-w-4xl px-6 py-10 md:py-14 text-center">

          <h1 class="text-3xl md:text-4xl font-bold mb-6 text-slate-900">Penghapusan Akun dan Data</h1>

          <p class="text-base leading-relaxed mb-8 text-slate-700">
            Pengguna aplikasi Sebaya Mobile dapat meminta penghapusan akun dan data pribadi dengan langkah berikut:
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">Cara menghapus akun:</h2>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">
            Kirim email ke: <a href="mailto:temansebaya@gmail.com"
              class="text-slate-700 hover:underline"><strong>temansebaya@gmail.com</strong></a>
          </p>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">
            Gunakan subjek email: <strong>Permintaan Penghapusan Akun Sebaya</strong>
          </p>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">
            Sertakan informasi:
          </p>
          <ul class="text-base leading-relaxed mb-8 text-slate-700 text-left space-y-2 list-disc list-inside">
            <li>Nama pengguna</li>
            <li>Alamat email yang terdaftar</li>
          </ul>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">Data yang akan dihapus:</h2>
          <ul class="text-base leading-relaxed mb-8 text-slate-700 text-left space-y-2 list-disc list-inside">
            <li>Data akun (nama, email, ID pengguna)</li>
            <li>Data autentikasi</li>
            <li>Data yang disimpan sementara: Log sistem (disimpan maksimal 30 hari untuk keperluan keamanan)</li>
          </ul>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">Waktu pemrosesan:</h2>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Maksimal 7 hari kerja sejak permintaan diterima.
          </p>

          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Jika Anda memiliki pertanyaan lebih lanjut, silakan hubungi kami melalui email di atas.
          </p>

          <div class="mt-12 text-sm text-slate-600 border-t pt-8">
            <p>© 2025 Sebaya. Seluruh hak dilindungi.</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</body>

</html>
