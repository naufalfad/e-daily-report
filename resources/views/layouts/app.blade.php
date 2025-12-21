<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- [PERBAIKAN 1] Meta Tag Identitas User & CSRF --}}
    {{-- Ini PENTING agar JavaScript (pengumuman.js) bisa membaca ID user yang sedang login --}}
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- App utama --}}
    @vite(['resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

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

    /* Hapus icon bawaan input tanggal */
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0 !important;
        display: none !important;
    }

    /* Hapus icon bawaan input time */
    input[type="time"]::-webkit-calendar-picker-indicator {
        opacity: 0 !important;
        display: none !important;
    }

    /* Hilangkan spinners Android/Edge */
    input[type="time"]::-webkit-inner-spin-button,
    input[type="date"]::-webkit-inner-spin-button {
        display: none !important;
    }
    </style>

    {{-- Setelah semua CSS / asset siap → tampilkan halaman --}}
    <script>
    window.addEventListener("load", () => {
        document.documentElement.classList.remove("loading");
    });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

</head>

<body class="min-h-screen bg-[#EFF0F5] text-slate-800">

    <div id="global-loader" class="fixed inset-0 bg-black/20 flex items-center justify-center z-[9999] hidden">

        <div class="flex flex-row gap-2">
            <div class="w-4 h-4 rounded-full bg-[#1C7C54] animate-bounce"></div>
            <div class="w-4 h-4 rounded-full bg-[#1C7C54] animate-bounce [animation-delay:-.3s]"></div>
            <div class="w-4 h-4 rounded-full bg-[#1C7C54] animate-bounce [animation-delay:-.5s]"></div>
        </div>

    </div>

    <div class="p-5 h-screen">
        <div class="grid h-full grid-cols-1 lg:grid-cols-[300px_1fr] gap-5 overflow-hidden">

            {{-- Sidebar --}}
            @include('partials.sidebar', [
            'role' => $role ?? 'staf',
            'active' => $active ?? null,
            ])

            {{-- KONTEN KANAN --}}
            <div class="h-full flex flex-col pl-9 overflow-hidden">

                {{-- TOPBAR: hanya muncul di dashboard --}}
                @if (($active ?? null) === 'dashboard')
                <header class="sticky top-0 z-40 bg-[#EFF0F5] backdrop-blur-xl">
                    <div class="py-1">

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
                                <input type="text" placeholder="Cari Pengumuman" class="w-full rounded-[999px] bg-white border border-slate-200 px-10 py-2.5
                                    text-sm shadow-sm placeholder:text-slate-400
                                    focus:ring-2 focus:ring-[#1C7C54]/40 focus:border-[#1C7C54]" />
                                    <div id="search-dropdown"
                                        class="absolute left-0 right-0 mt-2 bg-white rounded-xl shadow-lg ring-1 ring-slate-200 hidden z-50 max-h-[280px] overflow-y-auto no-scrollbar">
                                    </div>
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                    <img src="{{ asset('assets/icon/search.svg') }}"
                                        class="h-[18px] w-[18px] opacity-70" />
                                </span>
                            </div>

                            {{-- NOTIFIKASI --}}
                            <div x-data="{ openNotif:false }" class="relative ml-6">

                                {{-- BUTTON --}}
                                <button @click="openNotif = !openNotif"
                                    class="h-10 w-10 flex items-center justify-center transition-transform active:scale-95">

                                    {{-- WRAPPER BARU: Agar badge nempel ke icon (bukan ke tombol) --}}
                                    <div class="relative">
                                        <img src="{{ asset('assets/icon/notification.svg') }}" class="h-5 w-5" />

                                        {{-- BADGE --}}
                                        <span id="notif-badge" class="absolute -top-1.5 -right-1.5
                                                                w-4 h-4 min-w-[16px] px-[4px]
                                                                bg-[#B6241C] text-white text-[10px] font-semibold 
                                                                rounded-full flex items-center justify-center shadow-md
                                                                border-2 border-white box-content">
                                        </span>
                                    </div>

                                </button>

                                {{-- DROPDOWN --}}
                                <div x-show="openNotif" @click.outside="openNotif = false" x-transition
                                    class="absolute right-0 top-9 w-[340px] rounded-[15px] bg-white shadow-xl ring-1 ring-slate-200 p-4 z-50 origin-top-right">

                                    <h3 class="text-[14px] font-semibold text-slate-700 mb-3">Pemberitahuan</h3>

                                    <div id="notif-list"
                                        class="space-y-3 max-h-[300px] overflow-y-auto no-scrollbar pr-2">
                                        {{-- List akan diisi oleh JS --}}
                                    </div>
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

    {{-- Load script sesuai role --}}
    {{-- [PERBAIKAN 2] Pastikan script pengumuman.js dipanggil untuk Staf & Kadis juga --}}
    @switch($role)

    @case('admin')
    @vite('resources/js/pages/admin/dashboard.js')
    @vite('resources/js/pages/admin/manajemen-pegawai.js')
    @vite('resources/js/pages/admin/akun-pengguna.js')
    @vite('resources/js/pages/admin/setting-sistem.js')
    @break

    @case('staf')
    @vite('resources/js/pages/staf/dashboard.js')
    @vite('resources/js/pages/staf/input-lkh.js')
    @vite('resources/js/pages/staf/input-skp.js')
    @vite('resources/js/pages/staf/log-aktivitas.js')
    {{-- Tambahkan ini agar fitur pengumuman di role staf jalan --}}
    @vite('resources/js/pages/staf/pengumuman.js')
    @break

    @case('penilai')
    @vite('resources/js/pages/penilai/dashboard.js')
    @vite('resources/js/pages/penilai/input-lkh.js')
    @vite('resources/js/pages/penilai/pengumuman.js')
    @vite('resources/js/pages/penilai/validasi-laporan.js')
    @vite('resources/js/pages/penilai/input-skp.js')
    @vite('resources/js/pages/penilai/skoring-kinerja.js')
    @vite('resources/js/pages/penilai/peta-aktivitas.js')
    @vite('resources/js/pages/penilai/log-aktivitas.js') s
    @break

    @case('kadis')
    @vite('resources/js/pages/kadis/dashboard.js')
    @vite('resources/js/pages/kadis/validasi-laporan.js')
    @vite('resources/js/pages/kadis/skoring-bidang.js')
    {{-- Tambahkan ini agar fitur pengumuman di role kadis jalan --}}
    @vite('resources/js/pages/kadis/pengumuman.js')
    @break

    @endswitch

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("DOMContentLoaded", () => {

        const input = document.querySelector('input[placeholder="Cari Pengumuman"]');
        const dropdown = document.getElementById("search-dropdown");

        if (!input || !dropdown) return;

        let typingTimer;

        input.addEventListener("keyup", function () {
            clearTimeout(typingTimer);

            const query = this.value.trim();
            if (query.length < 2) {
                dropdown.classList.add("hidden");
                return;
            }

            typingTimer = setTimeout(() => {

                const token = localStorage.getItem("auth_token");

                fetch(`/api/search/pengumuman?q=${encodeURIComponent(query)}`, {
                    headers: {
                        "Authorization": `Bearer ${token}`,
                        "Accept": "application/json"
                    }
                })
                .then(res => {
                    if (res.status === 401) {
                        console.error("UNAUTHORIZED – token tidak dikirim / salah");
                    }
                    return res.json();
                })
                .then(data => {

                    if (!Array.isArray(data) || !data.length) {
                        dropdown.innerHTML = `
                            <div class="p-3 text-sm text-slate-500">Tidak ada hasil.</div>
                        `;
                        dropdown.classList.remove("hidden");
                        return;
                    }

                    dropdown.innerHTML = data.map(item => `
                        <div class="px-4 py-3 border-b border-slate-100 hover:bg-slate-50 transition text-sm">
                            <div class="font-semibold text-slate-700">${item.judul}</div>
                            <div class="text-xs text-slate-500 line-clamp-1">${item.isi_pengumuman}</div>
                            <div class="text-[10px] text-slate-400 mt-1">
                                Pembuat: ${item.creator ? item.creator.name : 'Tidak diketahui'}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                ${new Date(item.created_at).toLocaleDateString()}
                            </div>
                        </div>
                    `).join('');

                    dropdown.classList.remove("hidden");
                });

            }, 300);
        });

        document.addEventListener("click", (e) => {
            if (!dropdown.contains(e.target) && !input.contains(e.target)) {
                dropdown.classList.add("hidden");
            }
        });
    });
    </script>
</body>

</html>