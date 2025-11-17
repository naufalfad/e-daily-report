<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'E-Daily Report' }}</title>
    
    {{-- Panggil CSS dan JS sekaligus lewat Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Hapus baris <link rel="stylesheet"...> yang manual tadi --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    body {
        font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif
    }

    input[type="time"]::-webkit-calendar-picker-indicator {
        opacity: 0 !important;
        display: none !important;
    }

    input[type="time"]::-webkit-inner-spin-button,
    input[type="time"]::-webkit-clear-button {
        display: none !important;
    }

    /* Hilangkan ikon datepicker bawaan (Chrome/Edge dll) */
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0 !important;
        display: none !important;
    }

    /* Hilangkan spin & clear jika muncul */
    input[type="date"]::-webkit-inner-spin-button,
    input[type="date"]::-webkit-clear-button {
        display: none !important;
    }

    /* Warna teks untuk input time ketika masih kosong (strip placeholder) */
    input[type="time"].time-placeholder {
        color: #9CA3AF;
        /* abu seperti icon */
    }

    /* Warna teks ketika sudah terisi nilai jam */
    input[type="time"].time-filled {
        color: #111827;
        /* slate-900 / teks normal */
    }
    </style>
</head>

<body class="min-h-dvh bg-[#EFF0F5] text-slate-800">
    <div class="p-5">
        <div class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-5">
            {{-- Sidebar (global) --}}
            @include('partials.sidebar', [
            'role' => $role ?? 'staf',
            'active' => $active ?? null,
            ])

            {{-- Main --}}
            <div class="min-h-dvh flex flex-col pl-9">
                {{-- TOPBAR --}}
                {{-- sticky, nempel atas, background sama dengan layout --}}
                <header class="sticky top-5 z-40">
                    <div class="relative flex items-center gap-35 pb-3">
                        {{-- Burger (mobile) --}}
                        <button id="sb-toggle"
                            class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-md hover:bg-slate-200/60"
                            aria-label="Toggle sidebar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-width="1.7" stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        {{-- WRAPPER KANAN: search + icon --}}
                        <div class="flex-1 flex items-center gap-35">
                            {{-- SEARCH BAR --}}
                            <div class="relative flex-1 max-w-[450px]">
                                <input type="text" placeholder="Cari" class="w-full rounded-[999px] bg-white border border-slate-200 px-10 py-2.5
                                    text-sm text-slate-700 placeholder:text-slate-400 shadow-sm focus:outline-none
                                    focus:ring-2 focus:ring-[#1C7C54]/40 focus:border-[#1C7C54]" />
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor">
                                        <circle cx="11" cy="11" r="6" stroke-width="1.8" />
                                        <path d="m16 16 3 3" stroke-width="1.8" stroke-linecap="round" />
                                    </svg>
                                </span>
                            </div>

                            {{-- ICON NOTIF + PROFIL --}}
                            <div class="flex items-center gap-3">
                                <button class="h-10 w-10 flex items-center justify-center">
                                    <img src="{{ asset('assets/icon/notification.svg') }}" alt="Notifikasi"
                                        class="h-5 w-5">
                                </button>

                                <button class="h-10 w-10 flex items-center justify-center">
                                    <img src="{{ asset('assets/icon/profile.svg') }}" alt="Profile" class="h-7 w-7">
                                </button>

                            </div>
                        </div>
                    </div>
                </header>

                {{-- Content --}}
                <main class="pt-1 p-0">
                    @yield('content')
                </main>

                <footer class="mt-3 mb-0">
                    <div
                        class="mx-auto rounded-[10px] bg-white ring-1 ring-slate-200 px-2 py-4 text-center text-xs text-[#9CA3AF]">
                        © 2025 Badan Pendapatan Daerah Kabupaten Mimika | Sistem E-Daily Report versi 1.0
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('sb-toggle')?.addEventListener('click', () => {
        document.getElementById('sidebar')?.classList.toggle('-translate-x-full');
    });
    </script>

    @stack('scripts')
</body>

</html>