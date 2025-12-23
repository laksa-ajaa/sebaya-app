<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sebaya')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
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
