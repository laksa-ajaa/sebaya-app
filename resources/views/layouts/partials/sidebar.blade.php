<aside id="sidebar"
    class="fixed top-0 left-0 w-64 h-screen
           bg-white shadow-lg z-50
           transform -translate-x-full
           transition-transform duration-300"
    style="box-shadow: 0px 4px 4px 0px #00000040;">

    <div class="flex items-center justify-between px-4 h-[80px] border-b border-slate-200">
        <span class="font-semibold text-slate-800">Menu</span>
        <button id="closeSidebarButton" type="button" aria-label="Tutup sidebar"
            class="p-1 text-slate-500 hover:text-slate-800">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="4" y1="4" x2="16" y2="16" />
                <line x1="16" y1="4" x2="4" y2="16" />
            </svg>
        </button>
    </div>

    @if (auth()->user()->role === 'admin')
        @include('layouts.partials.sidebar-admin')
    @else
        @include('layouts.partials.sidebar-guru')
    @endif

</aside>

<div id="overlay"
    class="fixed inset-0 bg-black/40 opacity-0 pointer-events-none
           transition-opacity duration-300 hidden z-40">
</div>
{{-- Utility holder to keep dynamic classes from being purged by Tailwind --}}
<div class="hidden opacity-100 pointer-events-auto lg:ml-64"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const toggle = document.getElementById('toggleSidebar');
        const main = document.getElementById('mainContent');
        const closeBtn = document.getElementById('closeSidebarButton');

        if (!sidebar || !overlay || !toggle || !main) return;

        const showOverlay = () => {
            overlay.classList.remove('hidden');
            overlay.classList.remove('pointer-events-none');
            requestAnimationFrame(() => {
                overlay.classList.add('opacity-100', 'pointer-events-auto');
            });
        };

        const hideOverlay = () => {
            overlay.classList.remove('opacity-100', 'pointer-events-auto');
            const onTransitionEnd = () => {
                overlay.classList.add('pointer-events-none');
                overlay.classList.add('hidden');
                overlay.removeEventListener('transitionend', onTransitionEnd);
            };
            overlay.addEventListener('transitionend', onTransitionEnd);
        };

        const openSidebar = () => {
            sidebar.classList.remove('-translate-x-full');
            main.classList.add('pointer-events-none');
            showOverlay();
        };

        const closeSidebar = () => {
            sidebar.classList.add('-translate-x-full');
            main.classList.remove('pointer-events-none');
            hideOverlay();
        };

        toggle.addEventListener('click', () => {
            const isHidden = sidebar.classList.contains('-translate-x-full');
            isHidden ? openSidebar() : closeSidebar();
        });

        closeBtn?.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);

        window.addEventListener('resize', () => {
            if (window.innerWidth < 1024 && !sidebar.classList.contains('-translate-x-full')) {
                showOverlay();
            }
        });
    });
</script>
