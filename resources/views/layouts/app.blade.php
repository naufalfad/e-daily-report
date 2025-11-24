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
                            <div class="relative ml-6" 
                                x-data="{ 
                                    open: false, 
                                    unreadCount: 0, 
                                    notifications: [],
                                    isLoading: false,

                                    init() {
                                        this.fetchNotifications();
                                        
                                        // [BEST PRACTICE] Smart Polling
                                        // Hanya refresh otomatis jika tab sedang aktif dibuka oleh user
                                        setInterval(() => {
                                            if (document.visibilityState === 'visible') {
                                                this.fetchNotifications();
                                            }
                                        }, 60000); // Cek setiap 1 menit
                                    },

                                    async fetchNotifications() {
                                        try {
                                            // Tidak perlu loading spinner untuk background fetch agar tidak mengganggu UI
                                            const response = await fetch('{{ route('notifikasi.index') }}');
                                            
                                            if (!response.ok) throw new Error('Network response was not ok');
                                            
                                            const data = await response.json();
                                            this.notifications = data.data;
                                            this.unreadCount = data.unread_count;
                                        } catch (error) {
                                            console.error('Silent Error: Gagal memuat notifikasi', error);
                                        }
                                    },

                                    async markRead(id, url) {
                                        // Optimistic UI Update: Langsung tandai 'read' di tampilan sebelum request selesai
                                        // Agar UI terasa sangat cepat (Snappy)
                                        const targetIndex = this.notifications.findIndex(n => n.id === id);
                                        if (targetIndex !== -1) {
                                            if(this.notifications[targetIndex].is_read == 0) {
                                                this.notifications[targetIndex].is_read = 1;
                                                this.unreadCount = Math.max(0, this.unreadCount - 1);
                                            }
                                        }

                                        // Background Process
                                        fetch(`/core/notifikasi/${id}/read`, {
                                            method: 'PATCH',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                'Content-Type': 'application/json'
                                            }
                                        });

                                        // Redirect Logic
                                        if (url && url !== '#' && url !== null) {
                                            window.location.href = url;
                                        }
                                    },

                                    async markAllRead() {
                                        this.isLoading = true;
                                        // Optimistic Update
                                        this.unreadCount = 0;
                                        this.notifications.forEach(n => n.is_read = 1);

                                        try {
                                            await fetch('{{ route('notifikasi.markAll') }}', {
                                                method: 'PATCH',
                                                headers: {
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                    'Content-Type': 'application/json'
                                                }
                                            });
                                        } catch (e) {
                                            console.error(e);
                                            // Revert jika gagal (jarang terjadi)
                                            this.fetchNotifications(); 
                                        }
                                        this.isLoading = false;
                                    },

                                    // [Helper] Mempercantik Tampilan Tipe Notifikasi
                                    formatType(type) {
                                        if (!type) return 'INFO';
                                        // Ubah 'LKH_APPROVED' menjadi 'LKH Disetujui'
                                        // Ubah 'App\Models\LaporanHarian' menjadi 'Laporan Harian'
                                        return type.replace(/_/g, ' ')
                                                .replace('App\\Models\\', '')
                                                .replace(/([A-Z])/g, ' $1') // Tambah spasi sebelum huruf besar
                                                .trim(); 
                                    }
                                }"
                                @click.outside="open = false"
                            >
                                {{-- TOMBOL PEMICU --}}
                                <button @click="open = !open" class="relative h-10 w-10 flex items-center justify-center hover:bg-slate-100 rounded-full transition-colors">
                                    <img src="{{ asset('assets/icon/notification.svg') }}" alt="Notifikasi" class="h-5 w-5">
                                    
                                    {{-- BADGE COUNTER --}}
                                    <span x-show="unreadCount > 0" 
                                        x-transition.scale
                                        class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white"
                                        x-text="unreadCount > 9 ? '9+' : unreadCount">
                                    </span>
                                </button>

                                {{-- DROPDOWN CONTENT --}}
                                <div x-show="open" 
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-2"
                                    style="display: none;"
                                    class="absolute right-0 mt-2 w-80 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50 overflow-hidden">
                                    
                                    {{-- HEADER DROPDOWN --}}
                                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 bg-slate-50/50">
                                        <h3 class="text-sm font-semibold text-slate-800">Notifikasi</h3>
                                        <button @click="markAllRead" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium hover:underline disabled:opacity-50" :disabled="isLoading || unreadCount === 0">
                                            Tandai semua dibaca
                                        </button>
                                    </div>

                                    {{-- LIST NOTIFIKASI --}}
                                    <div class="max-h-[400px] overflow-y-auto no-scrollbar">
                                        
                                        {{-- JIKA KOSONG --}}
                                        <div x-show="notifications.length === 0" class="py-8 text-center">
                                            <p class="text-sm text-slate-400">Tidak ada notifikasi baru</p>
                                        </div>

                                        {{-- LOOP DATA --}}
                                        <template x-for="notif in notifications" :key="notif.id">
                                            <div @click="markRead(notif.id, notif.redirect_url)" 
                                                class="cursor-pointer px-4 py-3 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0 group">
                                                <div class="flex gap-3">
                                                    
                                                    {{-- Icon Indikator (Belum dibaca = Dot Merah, Sudah = Icon Abu) --}}
                                                    <div class="flex-shrink-0 mt-1">
                                                        <template x-if="notif.is_read == 0">
                                                            <div class="h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-red-100"></div>
                                                        </template>
                                                        <template x-if="notif.is_read == 1">
                                                            <div class="h-2.5 w-2.5 rounded-full bg-slate-300"></div>
                                                        </template>
                                                    </div>

                                                    {{-- Konten Teks --}}
                                                    <div class="flex-1 space-y-1">
                                                        <p class="text-xs font-medium text-slate-500 uppercase" x-text="notif.tipe_notifikasi"></p>
                                                        <p class="text-sm text-slate-800 leading-snug" 
                                                        :class="{ 'font-semibold': notif.is_read == 0 }"
                                                        x-text="notif.pesan"></p>
                                                        <p class="text-[11px] text-slate-400 pt-1" x-text="new Date(notif.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    {{-- FOOTER DROPDOWN --}}
                                    <div class="bg-slate-50 px-4 py-2 text-center border-t border-slate-100">
                                        <a href="#" class="text-xs text-slate-500 hover:text-emerald-600">Lihat Semua Riwayat</a>
                                    </div>
                                </div>
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