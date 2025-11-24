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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- App utama --}}
    @vite(['resources/js/app.js'])

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
                <header 
                    x-data="{ 
                        notifOpen:false, 
                        notifList: [
                            {
                                id: 1,
                                type: 'success',
                                title: 'Laporan Telah Diterima!',
                                message: 'Laporan kamu telah diterima pada pukul 14.32 WIT.',
                                icon: '/assets/icon/success.png',
                                time: '24 November 2025'
                            },
                            {
                                id: 2,
                                type: 'error',
                                title: 'Laporan Ditolak!',
                                message: 'Perbaiki laporan yang dikirim pada 22 November.',
                                icon: '/assets/icon/error.png',
                                time: '23 November 2025'
                            },
                            {
                                id: 3,
                                type: 'warning',
                                title: 'Validasi Terlambat!',
                                message: 'Beberapa laporan membutuhkan perhatian segera.',
                                icon: '/assets/icon/warning.png',
                                time: '22 November 2025'
                            }
                        ] 
                    }"
                    x-init="
                        window.addEventListener('global-notif', e => {
                            notifList.unshift(e.detail);
                        });
                    "
                    class="sticky top-0 z-40"
                    >
                    <div class="relative flex items-center gap-35 py-1">

                        <div class="flex-1 flex items-center justify-between">

                            {{-- SEARCH --}}
                            <div class="relative flex-1 max-w-[500px]">
                                <input type="text" placeholder="Cari" 
                                    class="w-full rounded-[999px] bg-white border border-slate-200 px-10 py-2.5
                                    text-sm shadow-sm placeholder:text-slate-400
                                    focus:ring-2 focus:ring-[#1C7C54]/40 focus:border-[#1C7C54]" />

                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                    <img src="{{ asset('assets/icon/search.svg') }}" class="h-[18px] w-[18px] opacity-70" />
                                </span>
                            </div>

                            {{-- NOTIF --}}
                            <div class="relative">

                                <button 
                                    @click="notifOpen = !notifOpen" 
                                    class="h-10 w-10 flex items-center justify-center ml-3 transition-transform active:scale-95"
                                    >
                                    <div class="relative">
                                        
                                        <img src="{{ asset('assets/icon/notification.svg') }}" class="h-5 w-5" />

                                        <span 
                                            x-show="notifList.length > 0"
                                            class="absolute -top-1.5 -right-1.5
                                                h-4 min-w-[16px] px-[4px]
                                                bg-red-500 text-white text-[10px] font-semibold
                                                flex items-center justify-center
                                                rounded-full
                                                border-1 border-[#EFF0F5] shadow-sm" 
                                        >
                                            <span x-text="notifList.length"></span>
                                        </span>
                                    </div>
                                </button>

                                {{-- DROPDOWN CONTAINER --}}
                                <div 
                                    x-show="notifOpen"
                                    @click.outside="notifOpen = false"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-1"
                                    class="absolute right-0 top-9
                                        w-[380px] bg-white shadow-xl 
                                        ring-1 ring-slate-900/5 rounded-xl 
                                        p-2 space-y-1 z-[999] origin-top-right"
                                    >
                                    <template x-for="item in notifList" :key="item.id">
                                        <div class="flex items-start gap-3 rounded-lg bg-white shadow-sm p-3">
                                            <img :src="item.icon" class="h-6 w-6 object-contain">

                                            <div class="flex-1">
                                                <p class="font-semibold text-[14px] text-slate-800" x-text="item.title"></p>
                                                <p class="text-[12px] text-slate-600 leading-relaxed" x-text="item.message"></p>
                                                <p class="text-[10px] text-slate-400 mt-1" x-text="item.time"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                            </div>

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