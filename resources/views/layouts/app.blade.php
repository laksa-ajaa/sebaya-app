<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Sebaya')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">

  <!-- Flatpickr CSS & JS -->
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/combine/npm/flatpickr@4.6.13/dist/flatpickr.min.css,npm/tom-select@2.4.3/dist/css/tom-select.css,npm/flatpickr@4.6.13/dist/themes/airbnb.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css"
    integrity="sha256-GzSkJVLJbxDk36qko2cnawOGiqz/Y8GsQv/jMTUrx1Q=" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"
    integrity="sha256-Huqxy3eUcaCwqqk92RwusapTfWlvAasF6p2rxV6FJaE=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"
    integrity="sha256-cvHCpHmt9EqKfsBeDHOujIlR5wZ8Wy3s90da1L3sGkc=" crossorigin="anonymous"></script>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.3/dist/cdn.min.js"
    integrity="sha256-e2nmRsTW/W5F0yF1XHx48Hdf+vCgsat5O3q4YPaizUQ=" crossorigin="anonymous"></script>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen antialiased text-slate-900" style="background: #F9FAFF;">

  {{-- HEADER --}}
  @include('layouts.partials.navbar')

  {{-- SIDEBAR --}}
  @include('layouts.partials.sidebar')

  {{-- MAIN CONTENT --}}
  <main id="mainContent" class="pt-[80px] transition-all duration-300">

    @yield('content')

  </main>

  {{-- FOOTER --}}
  @include('layouts.partials.footer')
</body>

</html>
