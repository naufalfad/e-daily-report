<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'E-Daily Report' }}</title>

    {{-- Anti-FOUC: sembunyikan body sebelum CSS & asset siap --}}
    <style>
    html.loading body {
        visibility: hidden;
    }
    </style>

    <script>
    document.documentElement.classList.add("loading");
    </script>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/icon/logo-aplikasi.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/icon/logo-aplikasi.png') }}">

    {{-- App utama --}}
    @vite(['resources/js/app.js'])

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="{{ asset('build/tailwind.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @stack('styles')

    <style>
    body {
        font-family: 'Poppins', ui-sans-serif, system-ui;
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Loader Spin */
    .loader-spin {
        animation: spin .8s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }
    </style>

    {{-- Setelah semua CSS / asset siap → tampilkan halaman --}}
    <script>
    window.addEventListener("load", () => {
        document.documentElement.classList.remove("loading");
    });
    </script>

</head>

<body class="h-dvh bg-[#EFF0F5] text-slate-800">

    <div id="global-loader" class="fixed inset-0 bg-black/20 flex items-center justify-center z-[9999] hidden">

        <div class="flex flex-row gap-2">
            <div class="w-4 h-4 rounded-full bg-[#1C7C54] animate-bounce"></div>
            <div class="w-4 h-4 rounded-full bg-[#1C7C54] animate-bounce [animation-delay:-.3s]"></div>
            <div class="w-4 h-4 rounded-full bg-[#1C7C54] animate-bounce [animation-delay:-.5s]"></div>
        </div>

    </div>

    <div class="h-full p-5">
        <div class="grid h-full grid-cols-1 lg:grid-cols-[300px_1fr] gap-5">

            {{-- Sidebar --}}
            @include('partials.sidebar', [
            'role' => $role ?? 'staf',
            'active' => $active ?? null,
            ])

            {{-- KONTEN KANAN --}}
            <div class="h-full flex flex-col pl-9 overflow-hidden">

                {{-- TOPBAR: hanya muncul di dashboard --}}
                @if (($active ?? null) === 'dashboard')
                <header class="sticky top-0 z-40">
                    <div class="relative flex items-center gap-35 py-1">

                        {{-- Burger (mobile) --}}
                        <button id="sb-toggle"
                            class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-md hover:bg-slate-200/60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor">
                                <path stroke-width="1.7" stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        {{-- SEARCH + NOTIF --}}
                        <div class="flex-1 flex items-center justify-between">

                            {{-- SEARCH --}}
                            <div class="relative flex-1 max-w-[500px]">
                                <input type="text" placeholder="Cari" class="w-full rounded-[999px] bg-white border border-slate-200 px-10 py-2.5
                                    text-sm shadow-sm placeholder:text-slate-400
                                    focus:ring-2 focus:ring-[#1C7C54]/40 focus:border-[#1C7C54]" />
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                    <img src="{{ asset('assets/icon/search.svg') }}"
                                        class="h-[18px] w-[18px] opacity-70" />
                                </span>
                            </div>

                            {{-- NOTIF --}}
                            <button class="h-10 w-10 flex items-center justify-center ml-6">
                                <img src="{{ asset('assets/icon/notification.svg') }}" class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </header>
                @endif

                {{-- KONTEN --}}
                <main class="pt-1 p-0 flex-1 flex flex-col overflow-y-auto no-scrollbar">
                    @yield('content')
                </main>

                {{-- FOOTER --}}
                <footer class="pt-3">
                    <div
                        class="mx-auto rounded-[10px] bg-white ring-1 ring-slate-200 px-2 py-4 text-center text-xs text-[#9CA3AF]">
                        © 2025 Badan Pendapatan Daerah Kabupaten Mimika | Sistem E-Daily Report versi 1.0
                    </div>
                </footer>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>

</html>