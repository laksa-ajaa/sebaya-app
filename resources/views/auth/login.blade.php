@extends('layouts.app')

@section('title', 'Masuk Akun')

@section('content')
    <div class="min-h-screen bg-[#010E82] relative overflow-hidden">

        {{-- LOGO --}}
        <div class="absolute top-5 left-1/2 -translate-x-1/2 text-white">
            <img src="{{ asset('sebaya-full.svg') }}" class="h-70">
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

                    {{-- ERROR --}}
                    @if ($errors->any())
                        <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    {{-- FORM --}}
                    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
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
                            <a href="#">Lupa Kata Sandi?</a>
                        </div>

                        <button
                            class="w-full rounded-full bg-gradient-to-r
                               from-[#0d4bb8] to-[#0b3fa1]
                               py-3 text-white font-semibold">
                            Masuk
                        </button>
                    </form>
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
                    // Toggle password visibility
                    if (passwordInput.type === 'password') {
                        // Password akan ditampilkan, ganti icon ke eye-slash
                        passwordInput.type = 'text';
                        eyeIcon.classList.add('hidden');
                        eyeSlashIcon.classList.remove('hidden');
                    } else {
                        // Password akan disembunyikan, ganti icon ke eye
                        passwordInput.type = 'password';
                        eyeIcon.classList.remove('hidden');
                        eyeSlashIcon.classList.add('hidden');
                    }
                });
            }
        });
    </script>
@endsection
