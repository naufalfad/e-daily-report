<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    {{-- [CRITICAL FIX] Token Keamanan CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'E-Daily Report' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/icon/logo-aplikasi.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/icon/logo-aplikasi.png') }}">

    {{-- App utama + JS profile/modal terpisah --}}
    @vite(['resources/js/app.js', 'resources/js/profile-modal.js'])

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="{{ asset('build/tailwind.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @stack('styles')

    <style>
    body {
        font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    }

    /* Remove default time/date picker icons */
    input[type="time"]::-webkit-calendar-picker-indicator,
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0 !important;
        display: none !important;
    }

    input[type="time"]::-webkit-inner-spin-button,
    input[type="date"]::-webkit-inner-spin-button,
    input[type="time"]::-webkit-clear-button,
    input[type="date"]::-webkit-clear-button {
        display: none !important;
    }

    /* Placeholder styling */
    input[type="time"].time-placeholder {
        color: #9CA3AF;
    }

    input[type="time"].time-filled {
        color: #111827;
    }

    /* Hide scrollbar */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* GLOBAL PLACEHOLDER COLOR */
    input::placeholder,
    textarea::placeholder,
    select::placeholder {
        color: #9CA3AF !important;
    }

    /* Untuk browser WebKit (Chrome, Edge, Safari) */
    input::-webkit-input-placeholder,
    textarea::-webkit-input-placeholder,
    select::-webkit-input-placeholder {
        color: #9CA3AF !important;
    }

    /* Firefox */
    input::-moz-placeholder,
    textarea::-moz-placeholder,
    select::-moz-placeholder {
        color: #9CA3AF !important;
    }

    /* IE & Edge lama */
    input:-ms-input-placeholder,
    textarea:-ms-input-placeholder,
    select:-ms-input-placeholder {
        color: #9CA3AF !important;
    }
    </style>
</head>

<body class="h-dvh bg-[#EFF0F5] text-slate-800">
    <div class="h-full p-5">
        <div class="grid h-full grid-cols-1 lg:grid-cols-[300px_1fr] gap-5">
            {{-- Sidebar (global) --}}
            @include('partials.sidebar', [
            'role' => $role ?? 'staf',
            'active' => $active ?? null,
            ])

            {{-- Kolom kanan: topbar + konten scroll + footer sticky di bawah --}}
            <div class="h-full flex flex-col pl-9 overflow-hidden">

                {{-- TOPBAR: hanya muncul jika halaman aktif = dashboard --}}
                @if (($active ?? null) === 'dashboard')
                <header class="sticky top-0 z-40">
                    <div class="relative flex items-center gap-35 py-1">
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
                        <div class="flex-1 flex items-center justify-between">
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

                            {{-- ICON NOTIF (profile dihapus) --}}
                            <div class="flex items-center gap-[2px] ml-6">
                                <button class="h-10 w-10 flex items-center justify-center">
                                    <img src="{{ asset('assets/icon/notification.svg') }}" alt="Notifikasi"
                                        class="h-5 w-5">
                                </button>
                            </div>
                        </div>
                    </div>
                </header>
                @endif

                {{-- AREA KONTEN YANG BISA DISCROLL --}}
                <main class="pt-1 p-0 flex-1 flex flex-col overflow-y-auto no-scrollbar pb-0">
                    @yield('content')
                </main>

                {{-- FOOTER: selalu nempel bawah kolom kanan --}}
                <footer class="pt-3">
                    <div
                        class="mx-auto rounded-[10px] bg-white ring-1 ring-slate-200 px-2 py-4 text-center text-xs text-[#9CA3AF]">
                        © 2025 Badan Pendapatan Daerah Kabupaten Mimika | Sistem E-Daily Report versi 1.0
                    </div>
                </footer>
            </div>
        </div>

        {{-- MODAL PROFIL PENGGUNA --}}
        @php
        use Illuminate\Support\Facades\Auth;

        $user = Auth::user();
        // Gunakan operator null safe atau default value untuk mencegah error jika user belum login/null
        $userName = $user->name ?? 'Nama Pengguna';
        $userEmail = $user->email ?? 'email@example.com';
        $userNip = $user->nip ?? '196703101988030109';
        $userJabatan = $user->jabatan ?? 'Jabatan';
        $userUnit = $user->unit_kerja ?? 'Unit Kerja';
        $userAlamat = $user->alamat ?? 'Alamat pengguna';
        $userWa = $user->no_wa ?? '08xxxxxxxxxx';
        @endphp

        <div id="profile-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/60 px-4">
            <div class="relative w-full max-w-[560px] bg-white rounded-[24px] shadow-xl px-6 md:px-8 py-6 md:py-8">
                <button type="button" id="btn-close-profile-modal"
                    class="absolute right-5 top-5 text-slate-400 hover:text-slate-600 text-2xl leading-none">
                    &times;
                </button>

                <h2 class="text-[18px] md:text-[20px] font-semibold text-slate-800 mb-5">
                    Profil Pengguna
                </h2>

                <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-6 mb-6">
                    <div class="flex-shrink-0">
                        <div
                            class="w-[96px] h-[96px] rounded-full bg-emerald-50 flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('assets/img/avatar-default.png') }}" alt="Avatar"
                                class="w-[90px] h-[90px] object-cover">
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="text-[18px] md:text-[20px] font-semibold text-slate-900">
                            {{ $userName }}
                        </div>
                        <div class="text-[14px] text-slate-600">
                            {{ $userNip }}
                        </div>
                        <div class="mt-2 flex items-center gap-2 text-[14px] text-slate-600">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 11.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 10.5C19 16 12 21 12 21S5 16 5 10.5a7 7 0 1114 0z" />
                                </svg>
                            </span>
                            <span>Mimika, Papua Tengah</span>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-200 mb-5">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-4 text-[14px]">
                    <div class="space-y-3">
                        <div>
                            <div class="text-slate-400 text-[13px]">Email</div>
                            <div class="text-slate-800">{{ $userEmail }}</div>
                        </div>
                        <div>
                            <div class="text-slate-400 text-[13px]">Jabatan</div>
                            <div class="text-slate-800">{{ $userJabatan }}</div>
                        </div>
                        <div>
                            <div class="text-slate-400 text-[13px]">Alamat</div>
                            <div class="text-slate-800">{{ $userAlamat }}</div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <div class="text-slate-400 text-[13px]">Nomor WhatsApp</div>
                            <div class="text-slate-800">{{ $userWa }}</div>
                        </div>
                        <div>
                            <div class="text-slate-400 text-[13px]">Unit Kerja</div>
                            <div class="text-slate-800">{{ $userUnit }}</div>
                        </div>
                        <div>
                            <div class="text-slate-400 text-[13px]">NIP</div>
                            <div class="text-slate-800">{{ $userNip }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>

</html>