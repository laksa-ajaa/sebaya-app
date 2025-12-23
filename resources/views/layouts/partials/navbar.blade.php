    <header class="fixed top-0 left-0 right-0 z-40 bg-white" style="box-shadow: 0px 4px 4px 0px #00000040;">
        <div class="h-[80px] px-6 flex items-center justify-between">

            <!-- LEFT -->
            @php($user = auth()->user())

            <div class="flex items-center gap-4">
                <button id="toggleSidebar" type="button" class="text-[#010E82]">
                    <!-- hamburger -->
                    <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="4" y1="7" x2="22" y2="7" />
                        <line x1="4" y1="13" x2="22" y2="13" />
                        <line x1="4" y1="19" x2="22" y2="19" />
                    </svg>
                </button>

                <div>
                    <p class="font-semibold text-[#010E82] text-sm">
                        Halo,
                        @if ($user?->role === 'admin')
                            Admin!
                        @elseif ($user?->role)
                            Pak/Bu Guru!
                        @else
                            Pengunjung!
                        @endif
                    </p>
                    <p class="text-xs text-gray-500">
                        Selamat datang di Dashboard Sebaya
                    </p>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="flex items-center gap-3 relative">
                @if ($user)
                    <button id="profileMenuButton" type="button"
                        class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-50 focus:outline-none">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold text-slate-800">{{ $user->name ?? '' }}</p>
                            <p class="text-xs text-slate-500 capitalize">{{ $user->role ?? '' }}</p>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center text-white">
                            <svg width="18" height="18" fill="currentColor">
                                <circle cx="9" cy="7" r="4" />
                                <path d="M2 18c0-4 14-4 14 0" />
                            </svg>
                        </div>
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                            class="text-slate-500">
                            <polyline points="3,6 8,11 13,6" />
                        </svg>
                    </button>

                    <div id="profileMenu"
                        class="absolute right-0 top-full mt-2 w-40 bg-white border border-slate-200 rounded shadow-lg hidden">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Keluar
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-[#010E82]">
                        Masuk
                    </a>
                @endif
            </div>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('profileMenuButton');
            const menu = document.getElementById('profileMenu');
            if (!btn || !menu) return;

            const close = () => menu.classList.add('hidden');
            const toggle = () => menu.classList.toggle('hidden');

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                toggle();
            });

            document.addEventListener('click', (e) => {
                if (!menu.classList.contains('hidden') && !menu.contains(e.target)) {
                    close();
                }
            });
        });
    </script>
