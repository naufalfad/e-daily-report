<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'E-Daily Report' }}</title>
    @vite(['resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="{{ asset('build/tailwind.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @stack('styles') {{-- Memindahkan stack styles ke head --}}
    <style>
    body {
        font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif
    }

    /* ... [Sisa style Anda yang lain tetap di sini] ... */
    input[type="time"]::-webkit-calendar-picker-indicator {
        opacity: 0 !important;
        display: none !important;
    }

    input[type="time"]::-webkit-inner-spin-button,
    input[type="time"]::-webkit-clear-button {
        display: none !important;
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0 !important;
        display: none !important;
    }

    input[type="date"]::-webkit-inner-spin-button,
    input[type="date"]::-webkit-clear-button {
        display: none !important;
    }

    input[type="time"].time-placeholder {
        color: #9CA3AF;
    }

    input[type="time"].time-filled {
        color: #111827;
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

            {{-- 
                ANALISIS REVISI (app.blade.php):
                Struktur kolom utama diubah menjadi 'flex flex-col' dengan 'min-h-dvh'.
                Ini adalah kunci agar 'flex-1' pada <main> dapat berfungsi.
            --}}
            <div class="min-h-dvh flex flex-col pl-9">
                {{-- TOPBAR --}}
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
                        <div class="flex-1 flex items-center gap-[110px]">
                            {{-- SEARCH BAR --}}
                            <div class="relative flex-1 max-w-[500px]">
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
                            <div class="flex items-center gap-[2px]">
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

                {{-- 
                    ANALISIS REVISI (app.blade.php):
                    1. 'flex-1' ditambahkan agar <main> mengisi sisa ruang.
                    2. 'flex flex-col' ditambahkan agar konten di dalamnya (dari @yield) 
                       juga bisa menggunakan flexbox untuk mengisi <main>.
                --}}
                <main class="pt-1 p-0 flex-1 flex flex-col">
                    @yield('content')
                </main>

                {{-- 
                    ANALISIS REVISI (app.blade.php):
                    'mt-auto' menggantikan 'mt-3'. Ini memaksa footer
                    menempel ke bagian bawah container flex ('div.min-h-dvh').
                    'pt-3' ditambahkan untuk mengganti margin atas.
                --}}
                <footer class="mt-auto mb-0 pt-3">
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