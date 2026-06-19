<!doctype html>
<html lang="id" class="scroll-smooth" x-data="{ role: 'guest' }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-Daily Report | Bapenda Kab. Mimika</title>

    {{-- Assets --}}
    <link rel="icon" type="image/png" href="{{ asset('img/logo-kab-mimika.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/logo-kab-mimika.png') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Mencegah Flash of Unstyled Content (FOUC) pada Alpine.js */
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #F8FAFC;
        }

        /* Glassmorphism Navbar Taktis */
        .glass-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #E2E8F0;
            z-index: 10000 !important;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 0px;
        }

        /* Map Canvas */
        #simulation-map {
            z-index: 1;
        }

        .feature-card-active {
            border-color: #1C7C54;
            background-color: rgba(28, 124, 84, 0.04);
            box-shadow: none;
        }
    </style>
</head>

<body class="text-slate-800 antialiased overflow-x-hidden selection:bg-[#1C7C54] selection:text-white">

    {{-- 1. NAVBAR --}}
    <nav class="fixed top-0 w-full glass-nav transition-all duration-300 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/logo-kab-mimika.png') }}" alt="Logo" class="h-9 w-auto">
                    <div class="h-6 w-px bg-slate-200"></div>
                    <div class="flex flex-col text-left">
                        <span class="text-base font-bold text-slate-900 tracking-tight leading-none">E-Daily <span class="text-[#1C7C54]">Report</span></span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Kabupaten Mimika</span>
                    </div>
                </div>

                <div class="hidden md:flex items-center space-x-8 text-[11px] font-black uppercase tracking-widest text-slate-500">
                    <a href="{{ $targetUrl ?? '#' }}" class="px-6 py-2 bg-[#1C7C54] text-white rounded-none shadow-md shadow-emerald-700/10 hover:bg-[#156343] transition-all transform active:scale-95">
                        {{ $buttonText ?? 'Login' }}
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- 2. HERO SECTION --}}
    <section class="relative min-h-[85vh] flex items-center bg-slate-950 text-white overflow-hidden py-16 px-4 lg:px-6">
        {{-- Layer Latar Belakang Gambar --}}
        <div class="absolute inset-0 z-0">
            <img 
                src="{{ asset('img/bg-mimika.jpg') }}" 
                alt="Mimika Landscape" 
                class="w-full h-full object-cover object-center filter saturate-75 opacity-90"
            >
        </div>
        {{-- Lapisan Gradien Taktis --}}
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/90 to-slate-950/30 z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 w-full mt-8 md:mt-0">
            <div class="grid lg:grid-cols-[1.2fr_1fr] gap-16 items-center">
                <div class="text-left space-y-5">
                    <span class="uppercase tracking-[0.25em] text-[8px] font-black text-emerald-400 bg-emerald-950/60 border border-emerald-500/30 px-3 py-1 w-max block">
                        Official Government Platform
                    </span>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-none uppercase">
                        Validasi Kinerja Berbasis <span class="text-emerald-400">Lokasi Aktual.</span>
                    </h1>
                    <p class="text-xs md:text-sm text-slate-300 font-semibold leading-relaxed max-w-xl">
                        Sistem pelaporan harian (LKH) terintegrasi untuk meningkatkan akuntabilitas, transparansi data, dan efisiensi birokrasi di lingkungan Bapenda Kabupaten Mimika.
                    </p>
                    <div class="pt-2">
                        <a href="#fitur" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3.5 font-bold text-[11px] uppercase tracking-widest transition-colors shadow-none group">
                            Eksplorasi Fitur <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>

                {{-- Mockup UI Dashboard (Siku Kaku, High-Density) --}}
                <div class="relative">
                    <div class="absolute -inset-4 bg-emerald-500/10 rounded-none blur-3xl"></div>
                    <div class="relative bg-white border border-slate-200 shadow-2xl overflow-hidden text-slate-800 rounded-none">
                        <div class="bg-slate-50 border-b border-slate-200 px-5 py-3 flex justify-between items-center select-none">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Pusat Validasi Atasan</span>
                            <div class="flex gap-1.5">
                                <div class="w-2 h-2 rounded-full bg-slate-200"></div>
                                <div class="w-2 h-2 rounded-full bg-slate-200"></div>
                            </div>
                        </div>
                        <div class="p-4 flex flex-col divide-y divide-slate-100">
                            @php $mockData = [
                                ['n' => 'Andi Pratama', 't' => '08:15 WIT', 's' => 'Waiting'],
                                ['n' => 'Siti Aisyah', 't' => '09:30 WIT', 's' => 'Waiting'],
                                ['n' => 'Budi Sudarsono', 't' => '10:05 WIT', 's' => 'Waiting']
                            ]; @endphp
                            @foreach($mockData as $data)
                            <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-emerald-50 text-emerald-700 rounded-full flex items-center justify-center font-black text-[11px]">{{ substr($data['n'], 0, 1) }}</div>
                                    <div class="text-left leading-none">
                                        <p class="text-xs font-bold text-slate-800 leading-none">{{ $data['n'] }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-1.5 leading-none">{{ $data['t'] }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-1.5 shrink-0 pl-2">
                                    <button class="w-8 h-8 bg-transparent text-emerald-600 hover:text-emerald-700 transition-all"><i class="fas fa-check"></i></button>
                                    <button class="w-8 h-8 bg-transparent text-slate-400 hover:text-slate-600 transition-all"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. BENTO FITUR INTERAKTIF --}}
    <section id="fitur" class="py-16 bg-white" x-data="{ activeTab: 1 }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-start">
                <div class="text-left space-y-6">
                    <div class="border-b-4 border-[#1C7C54] pb-4">
                        <h2 class="text-3xl font-semibold text-slate-900 tracking-tight uppercase leading-none">Teknologi Kualitas <br><span class="text-[#1C7C54]">Birokrasi.</span></h2>
                    </div>
                    <p class="text-slate-500 text-sm font-medium leading-relaxed">Setiap baris kode dioptimasi untuk menghasilkan data kinerja yang akurat, transparan, dan tidak dapat dimanipulasi.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        {{-- Fitur Items --}}
                        <button @click="activeTab = 1" :class="activeTab === 1 ? 'feature-card-active' : 'border-slate-200 bg-slate-50/50'" class="p-5 rounded-none border text-left transition-all outline-none">
                            <i class="fas fa-map-pin mb-3 block text-sm" :class="activeTab === 1 ? 'text-emerald-600' : 'text-slate-400'"></i>
                            <h5 class="font-bold text-xs uppercase tracking-widest text-slate-800">Anti-Fake GPS</h5>
                            <p class="text-[10px] text-slate-500 font-medium leading-normal mt-1.5">Verifikasi lokasi real-time dengan akurasi tinggi.</p>
                        </button>
                        <button @click="activeTab = 2" :class="activeTab === 2 ? 'feature-card-active' : 'border-slate-200 bg-slate-50/50'" class="p-5 rounded-none border text-left transition-all outline-none">
                            <i class="fas fa-file-invoice mb-3 block text-sm" :class="activeTab === 2 ? 'text-emerald-600' : 'text-slate-400'"></i>
                            <h5 class="font-bold text-xs uppercase tracking-widest text-slate-800">Lampiran Bukti</h5>
                            <p class="text-[10px] text-slate-500 font-medium leading-normal mt-1.5">Wajib sertakan dokumen/foto hasil kerja.</p>
                        </button>
                        <button @click="activeTab = 3" :class="activeTab === 3 ? 'feature-card-active' : 'border-slate-200 bg-slate-50/50'" class="p-5 rounded-none border text-left transition-all outline-none">
                            <i class="fas fa-chart-simple mb-3 block text-sm" :class="activeTab === 3 ? 'text-emerald-600' : 'text-slate-400'"></i>
                            <h5 class="font-bold text-xs uppercase tracking-widest text-slate-800">Auto-Skoring</h5>
                            <p class="text-[10px] text-slate-500 font-medium leading-normal mt-1.5">Kalkulasi poin SKP otomatis secara instan.</p>
                        </button>
                        <button @click="activeTab = 4" :class="activeTab === 4 ? 'feature-card-active' : 'border-slate-200 bg-slate-50/50'" class="p-5 rounded-none border text-left transition-all outline-none">
                            <i class="fas fa-bell mb-3 block text-sm" :class="activeTab === 4 ? 'text-emerald-600' : 'text-slate-400'"></i>
                            <h5 class="font-bold text-xs uppercase tracking-widest text-slate-800">Notifikasi Push</h5>
                            <p class="text-[10px] text-slate-500 font-medium leading-normal mt-1.5">Info validasi & pengumuman langsung di HP.</p>
                        </button>
                    </div>
                </div>

                {{-- Preview Panel --}}
                <div class="relative bg-slate-900 rounded-none overflow-hidden shadow-2xl min-h-[500px] border border-white/5">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-15"></div>

                    {{-- Gambar 1: Pemantauan Lokasi / GPS --}}
                    <div x-show="activeTab === 1" x-cloak
                        x-transition:enter="transition ease-out duration-500 delay-100"
                        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
                        class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center">
                        <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" class="rounded-none w-3/4 max-w-sm h-48 object-cover shadow-2xl mb-8" alt="Pemantauan Lokasi GPS">
                        <h4 class="text-xl font-bold uppercase tracking-wider text-white mb-2">Pemantauan Lokasi Presisi</h4>
                        <p class="text-slate-400 text-xs font-medium leading-relaxed px-4">Sistem mengunci koordinat geografis setiap laporan, memastikan setiap aktivitas terekam sesuai lokasi penugasan tanpa celah manipulasi.</p>
                    </div>

                    {{-- Gambar 2: Lampiran Bukti / Verifikasi --}}
                    <div x-show="activeTab === 2" x-cloak
                        x-transition:enter="transition ease-out duration-500 delay-100"
                        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
                        class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center">
                        <img src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" class="rounded-none w-3/4 max-w-sm h-48 object-cover shadow-2xl mb-8" alt="Pemantauan Laporan Keuangan">
                        <h4 class="text-xl font-bold uppercase tracking-wider text-white mb-2">Verifikasi Lampiran Berkas</h4>
                        <p class="text-slate-400 text-xs font-medium leading-relaxed px-4">Memastikan seluruh data verifikasi berupa berkas isian teknis atau scan dokumen diunggah dalam format PDF/JPG yang sah.</p>
                    </div>

                    <!-- {-- Gambar 3: Auto-Skoring --}} -->
                    <div x-show="activeTab === 3" x-cloak
                        x-transition:enter="transition ease-out duration-500 delay-100"
                        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
                        class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center">
                        <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" class="rounded-none w-3/4 max-w-sm h-48 object-cover shadow-2xl mb-8" alt="Kalkulasi Dashboard">
                        <h4 class="text-xl font-bold uppercase tracking-wider text-white mb-2">Auto-Skoring Real-Time</h4>
                        <p class="text-slate-400 text-xs font-medium leading-relaxed px-4">Kalkulasi poin SKP dinonaktifkan dari kerumitan rekapitulasi manual. Sistem secara otomatis menyusun akumulasi skor secara objektif.</p>
                    </div>

                    <!-- {-- Gambar 4: Notifikasi Push --}} -->
                    <div x-show="activeTab === 4" x-cloak
                        x-transition:enter="transition ease-out duration-500 delay-100"
                        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
                        class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center">
                        <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" class="rounded-none w-3/4 max-w-sm h-48 object-cover shadow-2xl mb-8" alt="Notifikasi Mobile">
                        <h4 class="text-xl font-bold uppercase tracking-wider text-white mb-2">Notifikasi Sistem Instan</h4>
                        <p class="text-slate-400 text-xs font-medium leading-relaxed px-4">Pesan verifikasi, instruksi revisi, dan pengumuman kedinasan akan langsung terkirim secara instan ke perangkat handphone pengguna.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. SIMULASI PETA INTERAKTIF --}}
    <section id="peta" class="py-16 bg-[#EFF0F5]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-left">
                <h2 class="text-3xl font-semibold text-slate-900 tracking-tight uppercase leading-none">Pantau Aktivitas Tim Anda</h2>
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mt-2 leading-none">Visualisasi sebaran pegawai di wilayah kerja secara real-time.</p>
            </div>

            <div class="bg-white rounded-none shadow-2xl border border-slate-200 overflow-hidden flex flex-col lg:grid lg:grid-cols-[1fr_360px] h-[700px] lg:h-[650px]">
                {{-- Map Container --}}
                <div id="simulation-map" class="h-1/2 lg:h-full w-full"></div>

                {{-- Activity Feed Sidebar --}}
                <div class="h-1/2 lg:h-full bg-white border-l border-slate-200 flex flex-col overflow-hidden text-left">
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between shrink-0">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-800">Live Activity Feed</h4>
                        <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-3.5 custom-scrollbar" id="activity-list">
                        {{-- Diisi via JS --}}
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-200 text-center">
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Simulation Mode Enabled</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. ALUR KERJA (SYSTEM FLOW) --}}
    <section id="alur" class="py-16 bg-slate-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-semibold mb-12 tracking-tight uppercase">Siklus Pelaporan Modern</h2>

            <div class="grid md:grid-cols-3 gap-12">
                <div class="border-t-2 border-white/10 pt-6 text-left flex flex-col justify-between h-full transition-colors group">
                    <div class="space-y-3.5">
                        <span class="text-3xl font-black text-emerald-400 tracking-tighter block leading-none">01</span>
                        <div class="space-y-1.5">
                            <h4 class="font-black text-white text-xs uppercase tracking-wider leading-none">Pencatatan Mandiri</h4>
                            <p class="text-slate-400 text-[11px] font-semibold leading-relaxed">Pegawai menginput aktivitas, durasi, dan volume pekerjaan langsung melalui perangkat mobile.</p>
                        </div>
                    </div>
                </div>
                <div class="border-t-2 border-white/10 pt-6 text-left flex flex-col justify-between h-full transition-colors group">
                    <div class="space-y-3.5">
                        <span class="text-3xl font-black text-emerald-400 tracking-tighter block leading-none">02</span>
                        <div class="space-y-1.5">
                            <h4 class="font-black text-white text-xs uppercase tracking-wider leading-none">Verifikasi Instan</h4>
                            <p class="text-slate-400 text-[11px] font-semibold leading-relaxed">Atasan menerima notifikasi real-time untuk melakukan pemeriksaan dan validasi laporan.</p>
                        </div>
                    </div>
                </div>
                <div class="border-t-2 border-white/10 pt-6 text-left flex flex-col justify-between h-full transition-colors group">
                    <div class="space-y-3.5">
                        <span class="text-3xl font-black text-emerald-400 tracking-tighter block leading-none">03</span>
                        <div class="space-y-1.5">
                            <h4 class="font-black text-white text-xs uppercase tracking-wider leading-none">Rekapitulasi Kinerja</h4>
                            <p class="text-slate-400 text-[11px] font-semibold leading-relaxed">Data yang tervalidasi otomatis tersusun menjadi laporan capaian bulanan dan tahunan instansi.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 6. FOOTER --}}
    <footer class="bg-white pt-16 pb-8 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12 text-left">
                <div class="col-span-1 lg:col-span-1">
                    <img src="{{ asset('img/logo-kab-mimika.png') }}" alt="Logo" class="h-11 w-auto mb-6">
                    <p class="text-slate-500 text-xs font-semibold leading-relaxed">Sistem Informasi Laporan Kinerja Harian Terintegrasi (E-Daily Report) Badan Pendapatan Daerah Kabupaten Mimika.</p>
                </div>

                <div>
                    <h5 class="text-emerald-600 font-bold text-[10px] uppercase tracking-widest mb-6">Akses Cepat</h5>
                    <ul class="space-y-3 text-slate-500 font-bold text-[11px] tracking-wider">
                        <li><a href="#fitur" class="hover:text-[#1C7C54] transition-colors">Eksplorasi Fitur</a></li>
                        <li><a href="#peta" class="hover:text-[#1C7C54] transition-colors">Peta Aktivitas</a></li>
                        <li><a href="#alur" class="hover:text-[#1C7C54] transition-colors">Alur Pelaporan</a></li>
                        <li><a href="{{ route('login') ?? '#' }}" class="text-[#1C7C54]">Portal Login</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-emerald-600 font-bold text-[10px] uppercase tracking-widest mb-6">Kontak Resmi</h5>
                    <ul class="space-y-4 text-slate-500 font-bold text-[11px] tracking-wider">
                        <li class="flex gap-2"><i class="fas fa-location-dot mt-0.5 text-slate-400"></i> Jl. Cenderawasih No. 1, Timika, Papua Tengah</li>
                        <li class="flex items-center gap-2"><i class="fas fa-envelope text-slate-400"></i> support@bapenda.mimika.go.id</li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-emerald-600 font-bold text-[10px] uppercase tracking-widest mb-6">Media Sosial</h5>
                    <div class="flex gap-2">
                        <a href="#" class="w-9 h-9 rounded-none bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-[#1C7C54] hover:text-white transition-all"><i class="fab fa-facebook-f text-sm"></i></a>
                        <a href="#" class="w-9 h-9 rounded-none bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-[#1C7C54] hover:text-white transition-all"><i class="fab fa-instagram text-sm"></i></a>
                        <a href="#" class="w-9 h-9 rounded-none bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-[#1C7C54] hover:text-white transition-all"><i class="fab fa-youtube text-sm"></i></a>
                    </div>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-200 flex flex-col md:flex-row justify-between items-center gap-2">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">&copy; {{ date('Y') }} Bapenda Kabupaten Mimika. All Rights Reserved.</p>
                <div class="flex items-center gap-6 text-[9px] font-black text-slate-450 uppercase tracking-widest">
                    <span>Privacy Policy</span>
                    <span>Security Audit</span>
                    <span>Terms of Service</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- SCRIPTS --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Leaflet Map Initialization
            const mimikaCenter = [-4.5467, 136.8833];
            const map = L.map('simulation-map', {
                scrollWheelZoom: false,
                zoomControl: false
            }).setView(mimikaCenter, 13);

            L.control.zoom({
                position: 'topright'
            }).addTo(map);

            L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20
            }).addTo(map);

            const dummyMarkers = [{
                    id: 1,
                    lat: -4.5450,
                    lng: 136.8850,
                    n: 'Andi Pratama',
                    r: 'Staf Pajak',
                    c: '#10b981',
                    a: 'Audit Objek Pajak Restoran',
                    st: 'WFO'
                },
                {
                    id: 2,
                    lat: -4.5500,
                    lng: 136.8800,
                    n: 'Siti Aisyah',
                    r: 'Analis Keuangan',
                    c: '#3b82f6',
                    a: 'Rekapitulasi SP2D Bulanan',
                    st: 'WFH'
                },
                {
                    id: 3,
                    lat: -4.5400,
                    lng: 136.8900,
                    n: 'Budi Sudarsono',
                    r: 'Juru Sita',
                    c: '#a855f7',
                    a: 'Penyampaian Surat Teguran',
                    st: 'Dinas Luar'
                },
                {
                    id: 4,
                    lat: -4.5480,
                    lng: 136.8750,
                    n: 'Rahmat Hidayat',
                    r: 'Pelayanan',
                    c: '#10b981',
                    a: 'Pengecekan Berkas Sertifikat',
                    st: 'WFO'
                }
            ];

            const listEl = document.getElementById('activity-list');

            dummyMarkers.forEach(data => {
                // Add Circle Marker
                const marker = L.circleMarker([data.lat, data.lng], {
                    radius: 10,
                    fillColor: data.c,
                    color: '#fff',
                    weight: 3,
                    fillOpacity: 1
                }).addTo(map);

                marker.bindPopup(`
                    <div style="font-family:'Poppins';">
                        <p style="font-size:10px; font-weight:800; color:${data.c}; text-transform:uppercase; margin-bottom:2px;">${data.st}</p>
                        <p style="font-size:14px; font-weight:700; color:#1e293b; margin-bottom:4px;">${data.n}</p>
                        <p style="font-size:11px; color:#64748b; line-height:1.2;">${data.a}</p>
                    </div>
                `);

                // Create Sidebar Item
                const card = document.createElement('div');
                card.className = "group p-4 bg-transparent border-b border-slate-100 last:border-b-0 hover:bg-slate-50 cursor-pointer transition-colors duration-300";
                card.innerHTML = `
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-12 rounded-full shrink-0" style="background:${data.c}"></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <p class="text-[10px] font-semibold text-slate-400 tracking-normal">${data.r}</p>
                                <span class="text-[9px] font-medium px-1.5 py-0.5 rounded bg-slate-50 text-slate-400 uppercase">${data.st}</span>
                            </div>
                            <p class="text-sm font-medium text-slate-800 truncate mt-0.5 group-hover:text-[#1C7C54] transition-colors">${data.n}</p>
                            <p class="text-[11px] text-slate-500 truncate">${data.a}</p>
                        </div>
                    </div>
                `;

                card.onclick = () => {
                    map.flyTo([data.lat, data.lng], 16, {
                        duration: 1.5
                    });
                    setTimeout(() => marker.openPopup(), 1500);
                };

                listEl.appendChild(card);
            });
        });
    </script>
</body>

</html>