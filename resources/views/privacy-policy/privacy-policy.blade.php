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
            <strong>screening kesehatan mental</strong>, serta
            <strong>respon dukungan berbasis kecerdasan buatan (AI)</strong>.
            Privasi dan kerahasiaan data pengguna merupakan prioritas utama kami.
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">1. Informasi yang Kami Kumpulkan</h2>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">Kami mengumpulkan data secara terbatas dan
            relevan dengan fungsi aplikasi, meliputi:</p>
          <ul class="text-base leading-relaxed mb-8 text-slate-700 text-left space-y-2 list-disc list-inside">
            <li>Data akun: nama, email, dan informasi autentikasi</li>
            <li>Data mood harian yang dipilih pengguna</li>
            <li>Isi jurnal harian yang ditulis secara sukarela</li>
            <li>Data hasil screening kesehatan mental</li>
            <li>Data teknis anonim seperti alamat IP, perangkat, dan sistem operasi</li>
          </ul>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">2. Fitur Screening Kesehatan Mental</h2>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">
            Sebaya menyediakan fitur screening menggunakan instrumen
            <strong>DASS-21 (Depression, Anxiety, and Stress Scale)</strong>
            sebagai alat bantu refleksi diri.
          </p>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">
            Hasil screening:
          </p>
          <ul class="text-base leading-relaxed mb-8 text-slate-700 text-left space-y-2 list-disc list-inside">
            <li>Bersifat <strong>informatif dan non-diagnostik</strong></li>
            <li>Tidak menggantikan diagnosis atau layanan profesional</li>
            <li>Digunakan untuk membantu pengguna memahami kondisi emosionalnya</li>
          </ul>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">3. Penggunaan Data</h2>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">Data yang dikumpulkan digunakan untuk:</p>
          <ul class="text-base leading-relaxed mb-8 text-slate-700 text-left space-y-2 list-disc list-inside">
            <li>Menyediakan fitur mood tracking, jurnal, dan screening</li>
            <li>Menyimpan riwayat refleksi dan hasil screening secara pribadi</li>
            <li>Menghasilkan respon empatik berbasis AI sesuai konteks pengguna</li>
            <li>Meningkatkan kualitas dan stabilitas sistem Sebaya</li>
          </ul>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">4. Penggunaan Teknologi AI</h2>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">
            Sebaya menggunakan layanan AI pihak ketiga untuk menghasilkan respon dukungan emosional.
            Data yang dikirim ke layanan AI hanya berupa <strong>konteks mood, jurnal, dan hasil screening</strong>
            tanpa menyertakan identitas pribadi pengguna.
          </p>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Respon AI disimpan secara terbatas di sistem Sebaya untuk efisiensi
            dan peningkatan pengalaman pengguna.
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">5. Penyimpanan dan Keamanan Data</h2>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Data pengguna disimpan di server yang aman dengan penerapan kontrol akses,
            autentikasi, dan perlindungan teknis lainnya.
            Kami berupaya mencegah akses, perubahan, atau pengungkapan data tanpa izin.
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">6. Kerahasiaan dan Pembagian Data</h2>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Sebaya <strong>tidak menjual, menyewakan, atau membagikan</strong>
            data pribadi, isi jurnal, maupun hasil screening pengguna kepada pihak mana pun,
            kecuali diwajibkan oleh hukum yang berlaku.
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">7. Hak Pengguna</h2>
          <p class="text-base leading-relaxed mb-4 text-slate-700 text-left">Pengguna memiliki hak penuh untuk:</p>
          <ul class="text-base leading-relaxed mb-8 text-slate-700 text-left space-y-2 list-disc list-inside">
            <li>Mengakses data pribadi dan hasil screening</li>
            <li>Mengubah atau menghapus jurnal, mood, dan riwayat screening</li>
            <li>Menghapus akun secara permanen</li>
          </ul>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">8. Batasan dan Penyangkalan Layanan</h2>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Sebaya bukan layanan medis atau psikologis profesional.
            Seluruh fitur, termasuk AI dan screening DASS-21,
            disediakan sebagai sarana pendamping dan refleksi diri,
            bukan sebagai alat diagnosis atau terapi.
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">9. Perubahan Kebijakan</h2>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Kebijakan privasi ini dapat diperbarui sewaktu-waktu.
            Setiap perubahan akan diumumkan melalui halaman ini.
          </p>

          <h2 class="text-2xl font-bold mt-8 mb-3 text-slate-900 text-left">10. Kontak</h2>
          <p class="text-base leading-relaxed mb-8 text-slate-700 text-left">
            Jika Anda memiliki pertanyaan terkait privasi atau pengelolaan data,
            silakan hubungi tim pengembang Sebaya melalui kanal resmi aplikasi.
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
