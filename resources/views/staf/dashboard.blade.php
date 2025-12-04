@php($title = 'Dashboard Staf')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'dashboard'])

@section('content')

{{-- Banner sambutan --}}
<section class="grid gap-4 lg:gap-5 lg:grid-cols-[1fr_380px]">
    {{-- Banner kiri --}}
    <div class="relative rounded-[20px] bg-[#1C7C54] text-white overflow-hidden
             p-6 md:py-8 md:pl-8 md:pr-10 flex justify-between items-start h-[250px]">

        {{-- Kolom teks kiri --}}
        <div class="relative z-10 flex-1 max-w-[64%]">
            {{-- Badge tanggal --}}
            <div class="inline-flex items-center gap-2 rounded-[10px] bg-white/40 px-3 py-1
                    text-sm ring-1 ring-white/20 mb-10">
                <img src="{{ asset('assets/icon/date.svg') }}" alt="Tanggal"
                    class="h-4 w-4 filter invert brightness-0" />
                <span>{{ now()->setTimezone('Asia/Jayapura')->translatedFormat('d F Y | H:i') }} WIT</span>
            </div>

            {{-- Teks utama --}}
            <p class="text-[20px] md:text-[28px] font-bold leading-tight">Selamat Datang,</p>
            <h1 class="text-[20px] md:text-[28px] font-bold leading-tight mt-1 md:whitespace-nowrap" id="banner-nama">
                User...
            </h1>
            <p class="mt-3 text-white/90 text-[16px]">Semoga harimu menyenangkan!</p>
        </div>

        {{-- Ilustrasi kanan --}}
        <div class="relative flex-shrink-0 self-center">
            <img src="{{ asset('img/dashboard-illustration.svg') }}" alt="Dashboard Illustration"
                class="w-[197px] h-[218px] object-contain select-none pointer-events-none translate-x-2" />
        </div>
    </div>

    {{-- CARD PROFIL SAYA --}}
    <aside class="rounded-[15px] bg-white ring-1 ring-slate-200 shadow-[0_6px_18px_rgba(15,23,42,0.06)]
               overflow-hidden mt-0">

        {{-- BODY --}}
        <div class="px-5 pt-4 pb-5">

            {{-- AVATAR + TEKS UTAMA --}}
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div class="flex-shrink-0">
                    <div
                        class="h-[78px] w-[78px] rounded-full overflow-hidden bg-[#FF8A3D] flex items-center justify-center">
                        <img src="{{ asset('assets/icon/avatar.png') }}" class="h-full w-full object-cover"
                            alt="Avatar">
                    </div>
                </div>

                {{-- Info teks utama --}}
                <div class="min-w-0 flex flex-col">
                    <h3 class="text-[17px] font-semibold text-slate-900 leading-snug" id="profile-nama">
                        -
                    </h3>
                    <p class="mt-[2px] text-[13px] text-slate-500 leading-snug" id="profile-nip">
                        -
                    </p>

                    {{-- Lokasi --}}
                    <div class="mt-1.5 flex items-center gap-1.5 text-[13px] text-slate-500">
                        <img src="{{ asset('assets/icon/location.svg') }}" class="h-4 w-4" alt="Lokasi" />
                        <span class="truncate" id="profile-alamat">-</span>
                    </div>
                </div>
            </div>

            {{-- EMAIL + TELEPON --}}
            <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-1 text-[13px] text-slate-600">
                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                    <img src="{{ asset('assets/icon/email.svg') }}" class="h-3.5 w-3.5" alt="Email" />
                    <span class="truncate" id="profile-email">-</span>
                </div>
                <div class="flex items-center gap-1.5 whitespace-nowrap">
                    <img src="{{ asset('assets/icon/telpon.svg') }}" class="h-3.5 w-3.5" alt="Telepon" />
                    <span id="profile-telepon">-</span>
                </div>
            </div>

            {{-- JABATAN / DINAS / ALAMAT --}}
            <div class="mt-4 border-t border-slate-200 pt-3 grid grid-cols-3 text-[13px] text-slate-700">
                {{-- Jabatan --}}
                <div class="pr-3">
                    <p class="text-[11px] tracking-[0.06em] uppercase text-slate-400">Jabatan</p>
                    <p class="mt-1 font-semibold text-slate-900 leading-snug" id="profile-jabatan">-</p>
                </div>

                {{-- Dinas --}}
                <div class="px-3 border-l border-slate-200">
                    <p class="text-[11px] tracking-[0.06em] uppercase text-slate-400">Unit Kerja</p>
                    <p class="mt-1 font-semibold text-slate-900 leading-snug" id="profile-dinas">
                        -
                    </p>
                </div>

                {{-- Alamat --}}
                <div class="pl-3 border-l border-slate-200">
                    <p class="text-[11px] tracking-[0.06em] uppercase text-slate-400">Target</p>
                    <p class="mt-1 font-semibold text-slate-900 leading-snug" id="profile-target">
                        -
                    </p>
                </div>
            </div>

        </div>
    </aside>
</section>

{{-- Statistik ringkas --}}
<section class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mt-4">
    {{-- Card 1: Total Laporan (Mapping ke SKP Diajukan) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-semibold tracking-tight" id="stat-val-1">0</div>
            <div class="flex items-center justify-center h-10 w-10 rounded-[10px] bg-[#155FA6]/50">
                <img src="{{ asset('assets/icon/send.svg') }}" class="h-5 w-5 object-contain">
            </div>
        </div>
        <div class="text-xs text-slate-500">Total Laporan SKP</div>
        <div class="text-xs text-slate-400 font-medium">Total diajukan</div>
    </div>

    {{-- Card 2: Menunggu Verifikasi (Mapping ke Realisasi Tahunan / Waiting) --}}
    {{-- Karena JSON tidak punya count 'Waiting', kita pakai Realisasi Tahunan sebagai pengganti --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-semibold tracking-tight" id="stat-val-2">0</div>
            <div class="flex items-center justify-center h-10 w-10 rounded-[10px] bg-[#D8A106]/50">
                <img src="{{ asset('assets/icon/pending.svg') }}" class="h-5 w-5 object-contain">
            </div>
        </div>
        <div class="text-xs text-slate-500">Realisasi Target SKP</div>
        <div class="text-xs text-emerald-600 font-medium" id="stat-desc-2">0% Capaian</div>
    </div>

    {{-- Card 3: Disetujui (Mapping ke Persen Diterima) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-semibold tracking-tight" id="stat-val-3">0</div>
            <div class="flex items-center justify-center h-10 w-10 rounded-[10px] bg-[#128C60]/50">
                <img src="{{ asset('assets/icon/approve.svg') }}" class="h-5 w-5 object-contain">
            </div>
        </div>
        <div class="text-xs text-slate-500">Rate Disetujui</div>
        <div class="text-xs text-emerald-600 font-medium" id="stat-desc-3">0% Dari total laporan</div>
    </div>

    {{-- Card 4: Ditolak (Mapping ke Persen Ditolak) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-semibold tracking-tight" id="stat-val-4">0</div>
            <div class="flex items-center justify-center h-10 w-10 rounded-[10px] bg-[#B6241C]/50">
                <img src="{{ asset('assets/icon/reject.svg') }}" class="h-5 w-5 object-contain">
            </div>
        </div>
        <div class="text-xs text-slate-500">Rate Ditolak</div>
        <div class="text-xs text-rose-600 font-medium" id="stat-desc-4">0% Dari total laporan</div>
    </div>
</section>

{{-- Grafik + Aktivitas terkini + Draft Laporan --}}
<section class="mt-4 grid grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.4fr)] gap-4">

    {{-- GRAFIK (kartu tinggi, span 2 baris) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 lg:row-span-2 flex flex-col">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">Grafik Kinerja Bulanan</h3>
            <button
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-sm hover:bg-slate-50">
                {{ date('Y') }}
            </button>
        </div>

        {{-- Area chart fleksibel + canvas --}}
        <div class="mt-1 flex-1">
            <canvas id="kinerjaBulananChart" class="w-full h-full"></canvas>
        </div>
    </div>

    {{-- AKTIVITAS TERKINI (kanan atas) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4">
        <h3 class="font-semibold mb-3">Aktivitas Terkini</h3>

        {{-- 
            1. Berikan ID 'aktivitas-list' 
            2. Kosongkan isinya (hapus @foreach PHP)
            3. Biarkan class 'space-y-3' untuk jarak antar item
        --}}
        <ul id="aktivitas-list" class="space-y-3">
            {{-- Skeleton Loading (Opsional: akan hilang saat JS dimuat) --}}
            <li class="text-sm text-slate-400 animate-pulse">Memuat aktivitas...</li>
        </ul>
    </div>

    {{-- DRAFT LAPORAN (TAMPILAN LUAR) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">Draft Laporan</h3>
            {{-- 1. Tambahkan event onclick pada tombol Lihat Semua --}}
            <a href="#" onclick="openModalDraft(event)" class="text-sm text-[#1C7C54] hover:underline">
                Lihat Semua
            </a>
        </div>

        {{-- LIST DRAFT LIMIT (LUAR) --}}
        <ul id="draft-list" class="space-y-2">
            <li class="text-sm text-slate-500">Memuat...</li>
        </ul>
    </div>

    {{-- ======================================================= --}}
    {{-- MODAL POPUP (Hidden by default) --}}
    {{-- ======================================================= --}}
    <div id="modal-all-draft" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Background Backdrop (Gelap & Blur) --}}
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModalDraft()"></div>

        {{-- Modal Panel --}}
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    {{-- Modal Header --}}
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold leading-6 text-slate-900" id="modal-title">
                                Semua Draft Laporan
                            </h3>
                            {{-- Tombol Close (X) --}}
                            <button type="button" onclick="closeModalDraft()" class="rounded-md bg-white text-slate-400 hover:text-slate-500 focus:outline-none">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body (Scrollable List) --}}
                    <div class="bg-white px-4 pb-4 pt-2 sm:p-6">
                        {{-- LIST DRAFT LENGKAP (DALAM MODAL) --}}
                        {{-- Kita beri max-height agar bisa discroll jika datanya banyak --}}
                        <ul id="draft-terbaru" class="space-y-3 max-h-[60vh] overflow-y-auto pr-2">
                            <li class="text-sm text-slate-500">Memuat data lengkap...</li>
                        </ul>
                    </div>
                    
                    {{-- Modal Footer --}}
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" onclick="closeModalDraft()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection