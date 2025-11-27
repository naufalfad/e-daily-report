@php($title = 'Dashboard Kadis')
@extends('layouts.app', ['title' => $title, 'role' => 'kadis', 'active' => 'dashboard'])

@section('content')

{{-- ============================
 Banner sambutan
============================ --}}
<section class="grid gap-4 lg:gap-5 lg:grid-cols-[1fr_380px]">

    {{-- Banner kiri --}}
    <div class="relative rounded-[20px] bg-[#1C7C54] text-white overflow-hidden
                p-6 md:py-8 md:pl-8 md:pr-10 flex justify-between items-start h-[250px]">

        <div class="relative z-10 flex-1 max-w-[64%]">

            {{-- Badge tanggal --}}
            <div class="inline-flex items-center gap-2 rounded-[10px] bg-white/40 px-3 py-1
                        text-sm ring-1 ring-white/20 mb-10">
                <img src="{{ asset('assets/icon/date.svg') }}" class="h-4 w-4 filter invert brightness-0" />
                <span id="banner-waktu">
                    {{ now()->setTimezone('Asia/Jayapura')->translatedFormat('d F Y | H:i') }} WIT
                </span>
            </div>

            <p class="text-[20px] md:text-[28px] font-bold leading-tight">Selamat Datang,</p>
            <h1 id="banner-nama" class="text-[20px] md:text-[28px] font-bold leading-tight mt-1">
                -
            </h1>

            <p class="mt-3 text-white/90 text-[16px]">Semoga harimu menyenangkan!</p>
        </div>

        <div class="relative flex-shrink-0 self-center">
            <img src="{{ asset('img/dashboard-illustration.svg') }}" class="w-[197px] h-[218px]" />
        </div>
    </div>

    {{-- Card Profil --}}
    <aside
        class="rounded-[15px] bg-white ring-1 ring-slate-200 shadow-[0_6px_18px_rgba(15,23,42,0.06)] overflow-hidden">
        <div class="px-5 pt-4 pb-5">

            <div class="flex items-center gap-4">

                <div
                    class="h-[78px] w-[78px] rounded-full overflow-hidden bg-[#FF8A3D] flex items-center justify-center">
                    <img src="{{ asset('assets/icon/avatar.png') }}" class="h-full w-full object-cover">
                </div>

                <div class="min-w-0 flex flex-col">
                    <h3 id="profile-nama" class="text-[17px] font-semibold text-slate-900 leading-snug">
                        -
                    </h3>
                    <p id="profile-nip" class="text-[13px] text-slate-500 leading-snug">-</p>

                    <div class="mt-1.5 flex items-center gap-1.5 text-[13px] text-slate-500">
                        <img src="{{ asset('assets/icon/location.svg') }}" class="h-4 w-4" />
                        <span id="profile-daerah" class="truncate">-</span>
                    </div>
                </div>

            </div>

            <div class="mt-3 grid grid-cols-3 text-[13px] text-slate-700 border-t border-slate-200 pt-3">
                <div>
                    <p class="text-[11px] uppercase text-slate-400">Jabatan</p>
                    <p id="profile-jabatan" class="font-semibold">-</p>
                </div>
                <div class="px-3 border-l border-slate-200">
                    <p class="text-[11px] uppercase text-slate-400">Dinas</p>
                    <p id="profile-dinas" class="font-semibold leading-snug">-</p>
                </div>
                <div class="pl-3 border-l border-slate-200">
                    <p class="text-[11px] uppercase text-slate-400">Alamat</p>
                    <p id="profile-alamat" class="font-semibold leading-snug">-</p>
                </div>
            </div>

        </div>
    </aside>

</section>

{{-- ============================
 Statistik Ringkas
============================ --}}
<section class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mt-4">

    {{-- TOTAL HARI INI --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-semibold" id="stat-total-hari-ini">0</div>
            <div class="h-10 w-10 rounded-[10px] bg-[#155FA6]/50 flex justify-center items-center">
                <img src="{{ asset('assets/icon/send.svg') }}" class="h-5 w-5">
            </div>
        </div>
        <div class="text-xs text-slate-500">Total Laporan Terkirim Hari ini</div>
        <div id="rate-total-hari-ini" class="text-xs text-emerald-600 font-medium">↑ 0% dari kemarin</div>
    </div>

    {{-- MENUNGGU --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-semibold" id="stat-menunggu">0</div>
            <div class="h-10 w-10 rounded-[10px] bg-[#D8A106]/50 flex justify-center items-center">
                <img src="{{ asset('assets/icon/pending.svg') }}" class="h-5 w-5">
            </div>
        </div>
        <div class="text-xs text-slate-500">Menunggu Verifikasi</div>
        <div id="rate-menunggu" class="text-xs text-amber-600 font-medium">⚠ Perlu perhatian</div>
    </div>

    {{-- DISETUJUI --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-semibold" id="stat-disetujui">0</div>
            <div class="h-10 w-10 rounded-[10px] bg-[#128C60]/50 flex justify-center items-center">
                <img src="{{ asset('assets/icon/approve.svg') }}" class="h-5 w-5">
            </div>
        </div>
        <div class="text-xs text-slate-500">Disetujui</div>
        <div id="rate-disetujui" class="text-xs text-emerald-600 font-medium">↑ 0% Approval Rate</div>
    </div>

    {{-- DITOLAK --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-semibold" id="stat-ditolak">0</div>
            <div class="h-10 w-10 rounded-[10px] bg-[#B6241C]/50 flex justify-center items-center">
                <img src="{{ asset('assets/icon/reject.svg') }}" class="h-5 w-5">
            </div>
        </div>
        <div class="text-xs text-slate-500">Ditolak</div>
        <div id="rate-ditolak" class="text-xs text-rose-600 font-medium">↓ 0% Rejection Rate</div>
    </div>

</section>


{{-- ============================
 Grafik + Aktivitas Terkini
============================ --}}
<section class="mt-4 grid grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.4fr)] gap-4">

    {{-- Grafik --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col">
        <h3 class="font-semibold mb-3">Grafik Kinerja Bulanan</h3>
        <div class="mt-1 flex-1">
            <canvas id="kinerjaBulananChart" class="w-full h-full"></canvas>
        </div>
    </div>

    {{-- Aktivitas --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 h-full">
        <h3 class="font-semibold mb-3">Aktivitas Terkini</h3>

        <ul id="aktivitas-list" class="space-y-3">
            <li class="text-sm text-slate-500">Memuat data...</li>
        </ul>
    </div>

</section>

@endsection

@push('scripts')
@vite('resources/js/pages/kadis/dashboard.js')
@endpush