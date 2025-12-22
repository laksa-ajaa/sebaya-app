@extends('layouts.app')

@section('title', 'Masuk Akun')

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-[#052a85] via-[#0b2e7d] to-[#0b2e7d] relative">

        {{-- LOGO CENTER --}}
        <div class="absolute top-20 left-1/2 -translate-x-1/2 text-white z-0">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-sebaya.svg') }}" class="h-12">
                <span class="text-3xl font-semibold tracking-wide">Sebaya</span>
            </div>
        </div>

        {{-- CARD FULL WIDTH --}}
        <div class="absolute inset-x-0 bottom-0 z-10">

            <div class="bg-[#f3f7ff] min-h-[65vh]
                    rounded-tl-[140px] shadow-2xl">

                <div class="mx-auto max-w-md px-6 py-14">

                    <h2 class="text-center text-2xl font-bold text-[#0d3880]">
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

                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Gmail" required
                            class="w-full rounded-full border border-[#6b7cff]
                               bg-white px-5 py-3 text-sm outline-none
                               focus:ring-2 focus:ring-blue-200">

                        <div class="relative">
                            <input type="password" name="password" placeholder="Password" required
                                class="w-full rounded-full border border-[#6b7cff]
                                   bg-white px-5 py-3 text-sm outline-none
                                   focus:ring-2 focus:ring-blue-200">
                            <span
                                class="absolute right-4 top-1/2 -translate-y-1/2
                                     text-gray-400 cursor-pointer">
                                👁
                            </span>
                        </div>

                        <div class="text-right text-xs text-blue-700">
                            <a href="#">Lupa Kata Sandi?</a>
                        </div>

                        <button type="submit"
                            class="mt-4 w-full rounded-full bg-gradient-to-r
                               from-[#0d4bb8] to-[#0b3fa1]
                               py-3 text-sm font-semibold text-white
                               shadow-md hover:opacity-95">
                            Masuk
                        </button>

                        <div class="pt-4 text-center text-xs text-gray-500">
                            Atau masuk dengan
                        </div>

                        <button type="button"
                            class="flex w-full items-center justify-center gap-2
                               rounded-full border bg-white py-2 text-sm">
                            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="h-4">
                            Akun Google
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
