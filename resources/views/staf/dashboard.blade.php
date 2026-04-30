@php($title = 'Dashboard Staf')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'dashboard'])

@section('content')

{{-- ==========================================
     BARIS 1: BANNER SAMBUTAN & PROFIL 
     ========================================== --}}
<section class="grid gap-4 lg:gap-5 lg:grid-cols-[1fr_380px]">
    {{-- CARD BANNER DASHBOARD (FULL) --}}
    <div class="relative w-full h-[250px] rounded-[24px] overflow-hidden shadow-lg shadow-emerald-900/20 bg-[#1C7C54]">

        {{-- LAYER 1: SLIDESHOW BACKGROUND (Alpine.js) --}}
        <div x-data="{
                active: 0,
                images: [
                    '{{ asset('assets/icon/banner/1.png') }}',
                    '{{ asset('assets/icon/banner/2.png') }}',
                    '{{ asset('assets/icon/banner/3.png') }}',
                    '{{ asset('assets/icon/banner/4.png') }}'
                ],
                startInterval() {
                    setInterval(() => {
                        this.active = (this.active + 1) % this.images.length;
                    }, 4000); 
                }
            }" 
            x-init="startInterval()"
            class="absolute inset-0 z-0 w-full h-full">

            <template x-for="(img, index) in images" :key="index">
                <img :src="img" 
                    alt="Background Banner"
                    x-show="active === index"
                    x-transition:enter="transition ease-out duration-1000"
                    x-transition:enter-start="opacity-0 scale-105"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-1000"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-105"
                    class="absolute inset-0 w-full h-full object-cover object-right select-none pointer-events-none" />
            </template>
            
            {{-- Preload Images --}}
            <div class="hidden">
                <img src="{{ asset('assets/icon/banner/1.png') }}">
                <img src="{{ asset('assets/icon/banner/2.png') }}">
                <img src="{{ asset('assets/icon/banner/3.png') }}">
                <img src="{{ asset('assets/icon/banner/4.png') }}">
            </div>
        </div>

        {{-- LAYER 2: GRADIENT OVERLAY --}}
        <div class="absolute inset-0 z-10 bg-gradient-to-r from-[#1C7C54] via-[#1C7C54]/30 to-[#1C7C54]/5 mix-blend-multiply md:mix-blend-normal"></div>
        <div class="absolute inset-0 z-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay pointer-events-none"></div>

        {{-- LAYER 3: KONTEN TEKS --}}
        <div class="relative z-20 flex flex-col justify-between h-full p-6 md:py-8 md:pl-8 md:pr-10 text-white">
            <div>
                <div class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-3 py-1.5 text-sm font-medium ring-1 ring-white/30 backdrop-blur-md shadow-sm">
                    <img src="{{ asset('assets/icon/date.svg') }}" alt="Tanggal" class="h-4 w-4 filter invert brightness-0 opacity-90" />
                    <span>{{ now()->setTimezone('Asia/Jayapura')->translatedFormat('d F Y | H:i') }} WIT</span>
                </div>
            </div>

            <div class="max-w-2xl">
                <p class="text-[18px] md:text-[22px] font-medium leading-tight text-white/90 drop-shadow-sm">Selamat Datang,</p>
                <h1 class="text-[24px] md:text-[32px] font-bold leading-tight mt-1 tracking-wide truncate drop-shadow-md">
                    {{ auth()->user()->name ?? 'User' }}
                </h1>
                <p class="mt-2 text-emerald-50 text-[14px] md:text-[16px] font-light drop-shadow-sm">
                    Semoga harimu menyenangkan dan produktif!
                </p>
            </div>
        </div>
    </div>

    {{-- CARD PROFIL SAYA --}}
    <aside class="rounded-[24px] bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden mt-0 flex flex-col justify-center">
        <div class="px-6 py-6">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <div class="h-20 w-20 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center border-4 border-white shadow-md">
                         <img src="{{ Auth::user()->foto_profil ? asset('storage/' . Auth::user()->foto_profil) : asset('assets/man.png') }}" 
                             class="h-full w-full object-cover" alt="Avatar">
                    </div>
                </div>
                <div class="min-w-0 flex flex-col">
                    <h3 class="text-lg font-bold text-slate-900 leading-snug truncate" id="profile-nama">-</h3>
                    <p class="mt-0.5 text-sm font-medium text-slate-500 leading-snug" id="profile-nip">-</p>
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-400 font-medium bg-slate-50 border border-slate-100 px-2 py-1 rounded-md w-fit">
                        <img src="{{ asset('assets/icon/location.svg') }}" class="h-3.5 w-3.5" alt="Lokasi" />
                        <span class="truncate" id="profile-alamat">-</span>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-600 font-medium">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <img src="{{ asset('assets/icon/email.svg') }}" class="h-4 w-4 opacity-50" alt="Email" />
                    <span class="truncate" id="profile-email">-</span>
                </div>
                <div class="flex items-center gap-2 whitespace-nowrap">
                    <img src="{{ asset('assets/icon/telpon.svg') }}" class="h-4 w-4 opacity-50" alt="Telepon" />
                    <span id="profile-telepon">-</span>
                </div>
            </div>

            <div class="mt-5 border-t border-slate-100 pt-4 grid grid-cols-3 text-sm text-slate-700">
                <div class="pr-3">
                    <p class="text-[10px] font-bold tracking-wider uppercase text-slate-400">Jabatan</p>
                    <p class="mt-1 font-bold text-slate-800 leading-snug truncate" id="profile-jabatan">-</p>
                </div>
                <div class="px-3 border-l border-slate-100">
                    <p class="text-[10px] font-bold tracking-wider uppercase text-slate-400">Unit Kerja</p>
                    <p class="mt-1 font-bold text-slate-800 leading-snug truncate" id="profile-dinas">-</p>
                </div>
                <div class="pl-3 border-l border-slate-100">
                    <p class="text-[10px] font-bold tracking-wider uppercase text-slate-400">Target SKP</p>
                    <p class="mt-1 font-bold text-[#1C7C54] leading-snug truncate" id="profile-target">-</p>
                </div>
            </div>
        </div>
    </aside>
</section>

{{-- ==========================================
     BARIS 2: STATISTIK RINGKAS
     ========================================== --}}
<section class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mt-4 lg:mt-5">
    {{-- Card 1: Total Laporan SKP --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 flex flex-col gap-2 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-extrabold text-slate-800 tracking-tight" id="stat-val-1">0</div>
            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-blue-50 border border-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
        </div>
        <div class="text-sm font-bold text-slate-600">Laporan SKP</div>
        <div class="text-xs text-slate-400 font-medium">Total volume disubmit</div>
    </div>

    {{-- Card 2: Total Laporan Non SKP --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 flex flex-col gap-2 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-extrabold text-slate-800 tracking-tight" id="stat-val-2">0</div>
            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-indigo-50 border border-indigo-100">
                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
            </div>
        </div>
        <div class="text-sm font-bold text-slate-600">Laporan Non-SKP</div>
        <div class="text-xs text-slate-400 font-medium">Total volume disubmit</div>
    </div>

    {{-- Card 3: Disetujui --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 flex flex-col gap-2 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-extrabold text-slate-800 tracking-tight" id="stat-val-3">0</div>
            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-emerald-50 border border-emerald-100">
                <img src="{{ asset('assets/icon/approve.svg') }}" class="h-6 w-6 filter brightness-0 opacity-80" style="filter: invert(41%) sepia(85%) saturate(350%) hue-rotate(108deg) brightness(92%) contrast(92%);">
            </div>
        </div>
        <div class="text-sm font-bold text-slate-600">Disetujui</div>
        <div class="text-xs text-[#1C7C54] font-bold" id="stat-desc-3">0% Dari total</div>
    </div>

    {{-- Card 4: Ditolak --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 flex flex-col gap-2 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-extrabold text-slate-800 tracking-tight" id="stat-val-4">0</div>
            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-rose-50 border border-rose-100">
                <img src="{{ asset('assets/icon/reject.svg') }}" class="h-6 w-6 filter brightness-0 opacity-80" style="filter: invert(30%) sepia(80%) saturate(2000%) hue-rotate(345deg) brightness(90%) contrast(95%);">
            </div>
        </div>
        <div class="text-sm font-bold text-slate-600">Ditolak (Revisi)</div>
        <div class="text-xs text-rose-600 font-bold" id="stat-desc-4">0% Dari total</div>
    </div>
</section>

{{-- ==========================================
     BARIS 3: GRAFIK (KINERJA & LOKASI)
     ========================================== --}}
<section class="mt-4 lg:mt-5 grid grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] gap-4">
    
    {{-- GRAFIK KINERJA (KIRI) --}}
    <div class="rounded-[24px] bg-white ring-1 ring-slate-200 p-6 flex flex-col shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Grafik Kinerja Bulanan</h3>
                <p class="text-xs text-slate-400 font-medium">Akumulasi laporan sepanjang tahun</p>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ date('Y') }}
            </span>
        </div>
        <div class="mt-2 flex-1 relative min-h-[300px]">
            <canvas id="kinerjaBulananChart" class="w-full h-full"></canvas>
        </div>
    </div>

    {{-- GRAFIK DISTRIBUSI LOKASI [NEW] (KANAN) --}}
    <div class="rounded-[24px] bg-white ring-1 ring-slate-200 p-6 flex flex-col shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Distribusi Lokasi</h3>
                <p class="text-xs text-slate-400 font-medium">Berdasarkan kategori tahun ini</p>
            </div>
        </div>
        <div class="mt-2 flex-1 relative min-h-[250px] flex justify-center items-center">
            <canvas id="lokasiChart" class="w-full h-full"></canvas>
        </div>
    </div>

</section>

{{-- ==========================================
     BARIS 4: LIST DATA (AKTIVITAS & DRAFT)
     ========================================== --}}
<section class="mt-4 lg:mt-5 grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- AKTIVITAS TERKINI (KIRI) --}}
    <div class="rounded-[24px] bg-white ring-1 ring-slate-200 p-6 flex flex-col shadow-sm">
        <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-lg">Aktivitas Terkini</h3>
            <a href="/staf/riwayat" class="text-sm font-bold text-[#1C7C54] hover:underline hover:text-[#166443]">Lihat Semua</a>
        </div>
        <ul id="aktivitas-list" class="space-y-4">
            <li class="text-sm text-slate-400 font-medium animate-pulse text-center py-4">Memuat log aktivitas...</li>
        </ul>
    </div>

    {{-- DRAFT LAPORAN (KANAN) --}}
    <div class="rounded-[24px] bg-white ring-1 ring-slate-200 p-6 flex flex-col shadow-sm">
        <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-lg">Draft Tersimpan</h3>
            <button onclick="openModalDraft(event)" class="text-sm font-bold text-[#1C7C54] hover:underline hover:text-[#166443]">
                Buka Brankas
            </button>
        </div>
        <ul id="draft-list" class="space-y-3">
            <li class="text-sm text-slate-400 font-medium animate-pulse text-center py-4">Mengecek brankas draft...</li>
        </ul>
    </div>

</section>

{{-- ======================================================= --}}
{{-- MODAL ALL DRAFT POPUP --}}
{{-- ======================================================= --}}
<div id="modal-all-draft" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModalDraft()"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-[24px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                
                <div class="bg-white px-6 pb-4 pt-6 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800" id="modal-title">Semua Draft Laporan</h3>
                            <p class="text-xs text-slate-500 font-medium mt-1">Laporan yang belum dikirim ke atasan</p>
                        </div>
                        <button type="button" onclick="closeModalDraft()" class="rounded-full bg-slate-50 p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors focus:outline-none">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="bg-slate-50/50 px-6 py-5">
                    <ul id="draft-terbaru" class="space-y-3 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                        <li class="text-sm text-slate-500 font-medium text-center py-4">Memuat data lengkap...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tambahkan custom scrollbar styling jika belum ada --}}
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

@endsection