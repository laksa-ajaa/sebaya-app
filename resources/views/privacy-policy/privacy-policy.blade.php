<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kebijakan Privasi - Sebaya</title>
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
      <img src="{{ asset('sebaya-full.svg') }}" class="h-70 mx-auto">
    </div>

    {{-- SECTION BAWAH --}}
    <div class="flex-1 mt-auto">
      <div class="bg-[#f3f7ff] rounded-tl-[120px] md:rounded-tl-[150px]">

        <div class="mx-auto max-w-4xl px-6 py-10 md:py-14 text-center">

          <h1 class="text-3xl md:text-4xl font-bold mb-6 text-slate-900">Kebijakan Privasi</h1>

          <p class="text-base leading-relaxed mb-8 text-slate-700">
            Sebaya adalah aplikasi pendamping kesehatan mental yang menyediakan fitur
            <strong>check mood harian</strong>, <strong>jurnal reflektif</strong>,
            serta <strong>respon suportif berbasis kecerdasan buatan (AI)</strong>.
            Kami berkomitmen untuk menjaga privasi dan keamanan data pengguna.
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">1. Informasi yang Kami Kumpulkan</h2>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">Kami mengumpulkan data secara terbatas
            untuk mendukung fungsi aplikasi, meliputi:</p>
          <ul class="text-base leading-relaxed mb-8 text-slate-700 text-left space-y-2 list-disc list-inside">
            <li>Data akun: nama, email, dan informasi login</li>
            <li>Data mood harian yang dipilih pengguna</li>
            <li>Isi jurnal harian yang ditulis secara sukarela oleh pengguna</li>
            <li>Data teknis: jenis perangkat, sistem operasi, dan alamat IP (anonim)</li>
          </ul>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">2. Penggunaan Data</h2>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">Data pengguna digunakan untuk:</p>
          <ul class="text-base leading-relaxed mb-8 text-slate-700 text-left space-y-2 list-disc list-inside">
            <li>Menyediakan fitur check mood dan jurnal harian</li>
            <li>Menghasilkan respon empatik dan suportif berbasis AI</li>
            <li>Menyimpan riwayat refleksi pengguna secara pribadi</li>
            <li>Meningkatkan kualitas dan stabilitas aplikasi</li>
          </ul>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">3. Penggunaan Teknologi AI</h2>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">
            Sebaya menggunakan layanan AI pihak ketiga untuk menghasilkan respon dukungan emosional.
            Data yang dikirim ke layanan AI hanya berupa <strong>konteks jurnal dan mood</strong>
            tanpa menyertakan identitas pribadi pengguna.
          </p>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Respon AI disimpan di sistem Sebaya agar tidak dilakukan pemanggilan ulang
            ke layanan AI secara berulang.
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">4. Penyimpanan dan Keamanan Data</h2>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Data pengguna disimpan di server yang aman dengan perlindungan teknis
            seperti autentikasi, pembatasan akses, dan enkripsi data tertentu.
            Kami berupaya semaksimal mungkin untuk mencegah akses tidak sah.
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">5. Kerahasiaan dan Pembagian Data</h2>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Sebaya <strong>tidak menjual, menyewakan, atau membagikan</strong>
            data pribadi atau isi jurnal pengguna kepada pihak lain,
            kecuali diwajibkan oleh hukum yang berlaku.
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">6. Hak Pengguna</h2>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">Pengguna memiliki hak untuk:</p>
          <ul class="text-base leading-relaxed mb-8 text-slate-700 text-left space-y-2 list-disc list-inside">
            <li>Mengakses data pribadinya</li>
            <li>Mengubah atau menghapus jurnal dan riwayat mood</li>
            <li>Menghapus akun secara permanen</li>
          </ul>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">7. Batasan Layanan</h2>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Sebaya bukan pengganti layanan profesional kesehatan mental.
            Respon yang diberikan bersifat dukungan emosional umum,
            bukan diagnosis atau terapi medis.
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">8. Perubahan Kebijakan</h2>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Kebijakan privasi ini dapat diperbarui sewaktu-waktu.
            Setiap perubahan akan ditampilkan pada halaman ini.
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">9. Kontak</h2>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Jika Anda memiliki pertanyaan atau kekhawatiran terkait privasi,
            silakan hubungi tim pengembang Sebaya melalui kontak resmi aplikasi.
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
