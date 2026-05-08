<!doctype html>
<html lang="id" class="scroll-smooth" x-data="{ role: 'guest' }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-Daily Report | Bapenda Kab. Mimika</title>

    {{-- Assets --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/icon/logo-aplikasi.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            font-family: 'Poppins', sans-serif;
            background-color: #F8FAFC;
        }

        /* FIX: Z-Index Navbar harus lebih tinggi dari elemen Leaflet manapun */
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
            border-radius: 10px;
        }

        /* Map Layout Fix */
        #simulation-map {
            border-radius: 1rem 0 0 1rem;
            z-index: 1;
        }

        @media (max-width: 1024px) {
            #simulation-map {
                border-radius: 1rem 1rem 0 0;
            }
        }

        .feature-card-active {
            border-color: #1C7C54;
            background-color: #F0FDF4;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="text-slate-800 antialiased overflow-x-hidden">

    {{-- 1. NAVBAR --}}
    <nav class="fixed top-0 w-full glass-nav transition-all duration-300 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/icon/logo-aplikasi.png') }}" alt="Logo" class="h-10 w-auto">
                    <div>
                        <span class="text-xl font-bold text-[#1C7C54] tracking-tight block">E-Daily Report</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Kabupaten Mimika</span>
                    </div>
                </div>

                <div class="hidden md:flex items-center space-x-10">
                    <a href="#fitur" class="text-sm font-bold text-slate-600 hover:text-[#1C7C54] transition-colors">Fitur</a>
                    <a href="#peta" class="text-sm font-bold text-slate-600 hover:text-[#1C7C54] transition-colors">Monitoring</a>
                    <a href="#alur" class="text-sm font-bold text-slate-600 hover:text-[#1C7C54] transition-colors">Cara Kerja</a>
                    <a href="{{ $targetUrl ?? '#' }}" class="px-7 py-3 bg-[#1C7C54] text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-700/20 hover:bg-[#156343] transition-all transform active:scale-95">
                        {{ $buttonText ?? 'Login' }}
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- 2. HERO SECTION --}}
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-40 bg-gradient-to-br from-white via-slate-50 to-[#EFF0F5]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-[1.2fr_1fr] gap-16 items-center">
                <div class="text-center lg:text-left">
                    <div class="inline-block px-4 py-1.5 mb-6 text-[11px] font-bold tracking-widest text-emerald-700 uppercase bg-emerald-100 rounded-full border border-emerald-200">
                        Official Government Platform
                    </div>
                    <h1 class="text-4xl sm:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.1] mb-8">
                        Validasi Kinerja Berbasis <span class="text-[#1C7C54]">Lokasi Aktual.</span>
                    </h1>
                    <p class="text-lg text-slate-500 mb-10 max-w-xl mx-auto lg:mx-0 leading-relaxed font-medium">
                        Sistem pelaporan harian (LKH) terintegrasi untuk meningkatkan akuntabilitas dan efisiensi birokrasi di lingkungan Bapenda Kabupaten Mimika.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="#fitur" class="px-10 py-4 bg-slate-900 text-white font-bold rounded-2xl shadow-xl hover:bg-slate-800 transition-all flex items-center justify-center gap-3 group">
                            Eksplorasi Fitur <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>

                {{-- Mockup UI Dashboard --}}
                <div class="relative">
                    <div class="absolute -inset-4 bg-emerald-500/10 rounded-[3rem] blur-3xl"></div>
                    <div class="relative bg-white rounded-[2rem] shadow-2xl border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-tighter">Pusat Validasi Atasan</span>
                            <div class="flex gap-1.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-slate-200"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-slate-200"></div>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            @php $mockData = [
                            ['n' => 'Andi Pratama', 't' => '08:15 WIT', 's' => 'Waiting'],
                            ['n' => 'Siti Aisyah', 't' => '09:30 WIT', 's' => 'Waiting'],
                            ['n' => 'Budi Sudarsono', 't' => '10:05 WIT', 's' => 'Waiting']
                            ]; @endphp
                            @foreach($mockData as $data)
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm border border-slate-100 text-[#1C7C54] font-bold text-xs">{{ substr($data['n'], 0, 1) }}</div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">{{ $data['n'] }}</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $data['t'] }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button class="w-8 h-8 rounded-lg bg-emerald-500 text-white text-xs"><i class="fas fa-check"></i></button>
                                    <button class="w-8 h-8 rounded-lg bg-slate-200 text-slate-400 text-xs"><i class="fas fa-eye"></i></button>
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
    <section id="fitur" class="py-32 bg-white" x-data="{ activeTab: 1 }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-start">
                <div>
                    <h2 class="text-4xl font-extrabold text-slate-900 mb-6 tracking-tight">Teknologi Untuk <br><span class="text-[#1C7C54]">Kualitas Birokrasi.</span></h2>
                    <p class="text-slate-500 font-medium mb-12">Setiap baris kode dioptimasi untuk menghasilkan data kinerja yang akurat, transparan, dan tidak dapat dimanipulasi.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Fitur Items --}}
                        <button @click="activeTab = 1" :class="activeTab === 1 ? 'feature-card-active' : 'border-slate-100 bg-slate-50'" class="p-5 rounded-2xl border text-left transition-all group">
                            <i class="fas fa-map-pin text-xl mb-3 block" :class="activeTab === 1 ? 'text-emerald-600' : 'text-slate-400'"></i>
                            <h5 class="font-bold text-sm text-slate-800">Anti-Fake GPS</h5>
                            <p class="text-[11px] text-slate-500 mt-1">Verifikasi lokasi real-time dengan akurasi tinggi.</p>
                        </button>
                        <button @click="activeTab = 2" :class="activeTab === 2 ? 'feature-card-active' : 'border-slate-100 bg-slate-50'" class="p-5 rounded-2xl border text-left transition-all group">
                            <i class="fas fa-file-invoice text-xl mb-3 block" :class="activeTab === 2 ? 'text-emerald-600' : 'text-slate-400'"></i>
                            <h5 class="font-bold text-sm text-slate-800">Lampiran Bukti</h5>
                            <p class="text-[11px] text-slate-500 mt-1">Wajib sertakan dokumen/foto hasil kerja.</p>
                        </button>
                        <button @click="activeTab = 3" :class="activeTab === 3 ? 'feature-card-active' : 'border-slate-100 bg-slate-50'" class="p-5 rounded-2xl border text-left transition-all group">
                            <i class="fas fa-chart-simple text-xl mb-3 block" :class="activeTab === 3 ? 'text-emerald-600' : 'text-slate-400'"></i>
                            <h5 class="font-bold text-sm text-slate-800">Auto-Skoring</h5>
                            <p class="text-[11px] text-slate-500 mt-1">Kalkulasi poin SKP otomatis secara instan.</p>
                        </button>
                        <button @click="activeTab = 4" :class="activeTab === 4 ? 'feature-card-active' : 'border-slate-100 bg-slate-50'" class="p-5 rounded-2xl border text-left transition-all group">
                            <i class="fas fa-bell text-xl mb-3 block" :class="activeTab === 4 ? 'text-emerald-600' : 'text-slate-400'"></i>
                            <h5 class="font-bold text-sm text-slate-800">Notifikasi Push</h5>
                            <p class="text-[11px] text-slate-500 mt-1">Info validasi & pengumuman langsung di HP.</p>
                        </button>
                    </div>
                </div>

                <div class="relative bg-slate-900 rounded-[3rem] overflow-hidden shadow-2xl min-h-[500px]">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-20"></div>

                    {{-- Gambar 1: Pemantauan Lokasi / GPS --}}
                    <div x-show="activeTab === 1" x-cloak
                        x-transition:enter="transition ease-out duration-500 delay-100"
                        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
                        class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center">
                        <div class="bg-white/10 p-4 rounded-3xl backdrop-blur-md mb-8 inline-block w-3/4 max-w-sm">
                            <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" class="rounded-2xl w-full h-48 object-cover shadow-2xl" alt="Pemantauan Lokasi GPS">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-4">Pemantauan Lokasi Presisi</h4>
                        <p class="text-slate-400 text-sm leading-relaxed px-4">Sistem mengunci koordinat geografis setiap laporan, memastikan setiap aktivitas terekam sesuai lokasi penugasan tanpa celah manipulasi.</p>
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
                        <div class="bg-white/10 p-4 rounded-3xl backdrop-blur-md mb-8 inline-block w-3/4 max-w-sm">
                            <img src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" class="rounded-2xl w-full h-48 object-cover shadow-2xl" alt="Verifikasi Dokumen">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-4">Verifikasi Berbasis Data</h4>
                        <p class="text-slate-400 text-sm leading-relaxed px-4">Mendukung unggah dokumen PDF dan foto dalam satu alur kerja, memudahkan atasan melakukan verifikasi kualitas pekerjaan secara visual.</p>
                    </div>

                    {{-- Gambar 3: Auto-Skoring / Kalkulasi --}}
                    <div x-show="activeTab === 3" x-cloak
                        x-transition:enter="transition ease-out duration-500 delay-100"
                        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
                        class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center">
                        <div class="bg-white/10 p-4 rounded-3xl backdrop-blur-md mb-8 inline-block w-3/4 max-w-sm">
                            <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" class="rounded-2xl w-full h-48 object-cover shadow-2xl" alt="Dashboard Kalkulasi">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-4">Kalkulasi Performa Dinamis</h4>
                        <p class="text-slate-400 text-sm leading-relaxed px-4">Mengelola bobot dan skor SKP (Sasaran Kinerja Pegawai) secara otomatis tanpa proses rekapitulasi manual yang memakan waktu.</p>
                    </div>

                    {{-- Gambar 4: Notifikasi Push / Smartphone --}}
                    <div x-show="activeTab === 4" x-cloak
                        x-transition:enter="transition ease-out duration-500 delay-100"
                        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-8 scale-95"
                        class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center">
                        <div class="bg-white/10 p-4 rounded-3xl backdrop-blur-md mb-8 inline-block w-3/4 max-w-sm">
                            <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" class="rounded-2xl w-full h-48 object-cover shadow-2xl" alt="Notifikasi Mobile">
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-4">Sistem Notifikasi Real-Time</h4>
                        <p class="text-slate-400 text-sm leading-relaxed px-4">Peringatan instan masuk ke perangkat pengguna untuk status persetujuan, instruksi revisi, dan pengumuman birokrasi penting.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. SIMULASI PETA INTERAKTIF --}}
    <section id="peta" class="py-24 bg-[#EFF0F5]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Pantau Aktivitas Tim Anda</h2>
                <p class="text-slate-500 font-medium mt-2">Visualisasi sebaran pegawai di wilayah kerja secara real-time.</p>
            </div>

            <div class="bg-white rounded-[2rem] shadow-2xl border border-slate-200 overflow-hidden flex flex-col lg:grid lg:grid-cols-[1fr_400px] h-[700px] lg:h-[650px]">
                {{-- Map Container --}}
                <div id="simulation-map" class="h-1/2 lg:h-full w-full"></div>

                {{-- Activity Feed Sidebar --}}
                <div class="h-1/2 lg:h-full bg-white border-l border-slate-100 flex flex-col overflow-hidden">
                    <div class="p-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between shrink-0">
                        <h4 class="font-bold text-slate-800 tracking-tight">Live Activity Feed</h4>
                        <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar" id="activity-list">
                        {{-- Diisi via JS --}}
                    </div>
                    <div class="p-5 bg-slate-50 border-t border-slate-100 text-center">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">Simulation Mode Enabled</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. ALUR KERJA (SYSTEM FLOW) --}}
    <section id="alur" class="py-32 bg-slate-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-20 tracking-tight">Siklus Pelaporan Modern</h2>

            <div class="grid md:grid-cols-3 gap-12">
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-emerald-500 rounded-3xl flex items-center justify-center text-3xl mb-8 rotate-3 shadow-lg shadow-emerald-500/20"><i class="fas fa-pen-nib"></i></div>
                    <h4 class="text-xl font-bold mb-4">Pencatatan Mandiri</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">Pegawai menginput aktivitas, durasi, dan volume pekerjaan langsung melalui perangkat mobile.</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-blue-500 rounded-3xl flex items-center justify-center text-3xl mb-8 -rotate-3 shadow-lg shadow-blue-500/20"><i class="fas fa-user-check"></i></div>
                    <h4 class="text-xl font-bold mb-4">Verifikasi Instan</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">Atasan menerima notifikasi real-time untuk melakukan pemeriksaan dan validasi laporan.</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-amber-500 rounded-3xl flex items-center justify-center text-3xl mb-8 rotate-6 shadow-lg shadow-amber-500/20"><i class="fas fa-file-contract"></i></div>
                    <h4 class="text-xl font-bold mb-4">Rekapitulasi Kinerja</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">Data yang tervalidasi otomatis tersusun menjadi laporan capaian bulanan dan tahunan instansi.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 6. FOOTER --}}
    <footer class="bg-white pt-24 pb-12 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-24">
                <div class="col-span-1 lg:col-span-1">
                    <img src="{{ asset('assets/icon/logo-aplikasi.png') }}" alt="Logo" class="h-12 w-auto mb-6">
                    <p class="text-sm text-slate-500 leading-relaxed font-medium">Sistem Informasi Laporan Kinerja Harian Terintegrasi (E-Daily Report) Badan Pendapatan Daerah Kabupaten Mimika.</p>
                </div>

                <div>
                    <h5 class="text-slate-900 font-bold mb-8 uppercase text-xs tracking-widest">Akses Cepat</h5>
                    <ul class="space-y-4 text-sm font-semibold text-slate-500">
                        <li><a href="#fitur" class="hover:text-[#1C7C54] transition-colors">Eksplorasi Fitur</a></li>
                        <li><a href="#peta" class="hover:text-[#1C7C54] transition-colors">Peta Aktivitas</a></li>
                        <li><a href="#alur" class="hover:text-[#1C7C54] transition-colors">Alur Pelaporan</a></li>
                        <li><a href="{{ route('login') ?? '#' }}" class="text-[#1C7C54]">Portal Login</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-slate-900 font-bold mb-8 uppercase text-xs tracking-widest">Kontak Resmi</h5>
                    <ul class="space-y-5 text-sm font-medium text-slate-500">
                        <li class="flex gap-3"><i class="fas fa-location-dot mt-1 text-emerald-600"></i> Jl. Cenderawasih No. 1, Timika, Papua Tengah</li>
                        <li class="flex items-center gap-3"><i class="fas fa-envelope text-emerald-600"></i> support@bapenda.mimika.go.id</li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-slate-900 font-bold mb-8 uppercase text-xs tracking-widest">Media Sosial</h5>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-[#1C7C54] hover:text-white transition-all"><i class="fab fa-facebook-f text-sm"></i></a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-[#1C7C54] hover:text-white transition-all"><i class="fab fa-instagram text-sm"></i></a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-[#1C7C54] hover:text-white transition-all"><i class="fab fa-youtube text-sm"></i></a>
                    </div>
                </div>
            </div>

            <div class="pt-10 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">&copy; {{ date('Y') }} Bapenda Kabupaten Mimika. All Rights Reserved.</p>
                <div class="flex items-center gap-6 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
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
                card.className = "group p-4 bg-white border border-slate-100 rounded-2xl hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-50 cursor-pointer transition-all duration-300";
                card.innerHTML = `
                    <div class="flex items-center gap-4">
                        <div class="w-1.5 h-12 rounded-full shrink-0" style="background:${data.c}"></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-tighter">${data.r}</p>
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-50 text-slate-400 uppercase">${data.st}</span>
                            </div>
                            <p class="text-sm font-bold text-slate-800 truncate mt-0.5 group-hover:text-emerald-700 transition-colors">${data.n}</p>
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