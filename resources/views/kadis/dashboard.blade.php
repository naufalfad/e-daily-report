@php($title = 'Dashboard Kepala Dinas')
@extends('layouts.app', ['title' => $title, 'role' => 'kadis', 'active' => 'dashboard'])

@section('content')

    {{-- =======================================================================
         BAGIAN 1: BANNER & PROFIL (TETAP DIPERTAHANKAN)
    ======================================================================== --}}
    <section class="grid gap-4 lg:gap-5 lg:grid-cols-[1fr_380px]">
        
        {{-- Banner Kiri --}}
        <div class="relative rounded-[24px] bg-[#1C7C54] text-white overflow-hidden p-6 md:py-8 md:pl-8 md:pr-10 flex justify-between items-start h-[250px] shadow-lg shadow-[#1C7C54]/20">
            <div class="relative z-10 flex-1 max-w-[64%]">
                <div class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-3 py-1.5 text-sm ring-1 ring-white/30 mb-8 backdrop-blur-md shadow-sm">
                    <img src="{{ asset('assets/icon/date.svg') }}" alt="Tanggal" class="h-4 w-4 filter invert brightness-0 opacity-90" />
                    <span>{{ now()->setTimezone('Asia/Jayapura')->translatedFormat('d F Y | H:i') }} WIT</span>
                </div>

                <p class="text-[18px] md:text-[22px] font-medium leading-tight text-white/90 drop-shadow-sm">Selamat Datang,</p>
                <h1 id="banner-nama" class="text-[24px] md:text-[32px] font-extrabold leading-tight mt-1 md:whitespace-nowrap tracking-tight drop-shadow-md">
                    {{ Auth::user()->name }}
                </h1>
                <p class="mt-3 text-emerald-50 text-[15px] font-light drop-shadow-sm">Pantau kinerja seluruh bidang dalam satu dashboard.</p>
            </div>

            <div class="relative flex-shrink-0 self-center hidden sm:block">
                <img src="{{ asset('img/dashboard-illustration.svg') }}" alt="Dashboard Illustration"
                    class="w-[200px] h-[220px] object-contain select-none pointer-events-none drop-shadow-xl translate-y-2 translate-x-2" />
            </div>
            
            {{-- GRADIENT OVERLAY --}}
            <div class="absolute inset-0 z-0 bg-gradient-to-r from-[#1C7C54] via-[#1C7C54]/30 to-[#1C7C54]/5 mix-blend-multiply md:mix-blend-normal"></div>
            <div class="absolute inset-0 z-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay pointer-events-none"></div>
        </div>

        {{-- Card Profil Kanan --}}
        <aside class="rounded-[24px] bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden flex flex-col justify-center h-full">
            <div class="px-6 py-6">
                <div class="flex items-center gap-5">
                    {{-- Foto Profil --}}
                    <div class="flex-shrink-0 relative">
                        <div class="h-[80px] w-[80px] rounded-full overflow-hidden bg-slate-100 ring-4 ring-white shadow-md flex items-center justify-center">
                            <img id="profile-foto" 
                                 src="{{ Auth::user()->foto_profil ? asset('storage/' . Auth::user()->foto_profil) : asset('assets/man.png') }}" 
                                 class="h-full w-full object-cover" alt="Avatar">
                        </div>
                        <div class="absolute bottom-0 right-0 h-5 w-5 bg-emerald-500 border-2 border-white rounded-full" title="Online"></div>
                    </div>

                    {{-- Info User --}}
                    <div class="min-w-0 flex flex-col justify-center">
                        <h3 id="profile-nama" class="text-[18px] font-bold text-slate-800 leading-tight truncate">
                            {{ Auth::user()->name }}
                        </h3>
                        <p id="profile-nip" class="text-sm text-slate-500 font-medium mb-1.5">
                            {{ Auth::user()->nip ?? '-' }}
                        </p>
                        <span id="profile-jabatan" class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide bg-emerald-50 text-emerald-700 w-fit border border-emerald-100">
                            {{ Auth::user()->jabatan->nama_jabatan ?? '-' }}
                        </span>
                    </div>
                </div>

                {{-- Detail Tambahan --}}
                <div class="mt-6 space-y-4 pt-5 border-t border-slate-100">
                    <div class="flex items-start gap-3">
                        <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Unit Kerja</p>
                            <p id="profile-unit" class="text-sm font-bold text-slate-700">
                                {{ Auth::user()->unitKerja->nama_unit ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="h-8 w-8 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0 text-purple-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Lokasi Dinas</p>
                            <p id="profile-alamat" class="text-sm font-bold text-slate-700 truncate max-w-[200px]">
                                {{ Auth::user()->alamat ?? 'Bapenda Kab. Mimika' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </section>

    {{-- =======================================================================
         BAGIAN 2: DISTRIBUSI LOKASI GLOBAL (DONUT CHART) & INSIGHT CARD
    ======================================================================== --}}
    <section class="mt-4 lg:mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-5">
        
        {{-- KIRI: DONUT CHART LOKASI GLOBAL --}}
        <div class="rounded-[24px] bg-white ring-1 ring-slate-200 p-6 flex flex-col shadow-sm lg:col-span-1">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg tracking-tight">Sebaran Lokasi</h3>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Pegawai aktif bulan ini</p>
                </div>
            </div>
            <div class="mt-2 flex-1 relative min-h-[250px] flex justify-center items-center">
                <canvas id="lokasiGlobalChart" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- KANAN: INSIGHT / PENGUMUMAN CARD (Lebih Eksekutif) --}}
        <div class="rounded-[24px] bg-[#155FA6] text-white ring-1 ring-slate-200 p-8 flex flex-col justify-center shadow-sm lg:col-span-2 relative overflow-hidden">
             <div class="relative z-10">
                 <div class="inline-flex items-center justify-center p-3 bg-white/10 rounded-xl mb-4 backdrop-blur-sm border border-white/20">
                     <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                 </div>
                 <h2 class="text-2xl font-bold mb-2 tracking-tight">Pemantauan Kinerja Aktif</h2>
                 <p class="text-blue-100 text-sm max-w-lg leading-relaxed font-light">
                     Layar pemantauan ini menarik akumulasi data kinerja seluruh bidang secara aktual (real-time). Pastikan persentase pelaporan lokasi kerja WFO dan WFH selaras dengan regulasi dinas yang berlaku bulan ini.
                 </p>
             </div>
             
             {{-- Dekorasi Abstrak --}}
             <svg class="absolute right-0 bottom-0 opacity-10 w-64 h-64 transform translate-x-10 translate-y-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        </div>
    </section>

    {{-- =======================================================================
         BAGIAN 3: CONTAINER GRAFIK KINERJA BIDANG
    ======================================================================== --}}
    <div class="mt-8">
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5 px-1">
            <div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Monitoring Kinerja Bidang</h2>
                <p class="text-slate-500 text-sm mt-1 font-medium">Akumulasi laporan harian pegawai yang telah disetujui.</p>
            </div>
            
            {{-- Filter Tahun (Visual Saja) --}}
            <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1.5 shadow-sm">
                <span class="px-4 py-2 text-xs font-bold text-slate-600 uppercase tracking-wider bg-slate-50 rounded-lg border border-slate-100">
                    Tahun {{ date('Y') }}
                </span>
            </div>
        </div>

        {{-- GRID CONTAINER (Target JS: #grafik-bidang-container) --}}
        <div id="grafik-bidang-container" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 lg:gap-6">
            
            {{-- SKELETON LOADING STATE --}}
            @for($i=0; $i<6; $i++)
            <div class="bg-white p-6 rounded-[24px] border border-slate-100 shadow-sm h-[320px] flex flex-col animate-pulse">
                <div class="flex justify-between items-start mb-6">
                    <div class="space-y-3 w-2/3">
                        <div class="h-4 bg-slate-200 rounded-md w-3/4"></div>
                        <div class="h-3 bg-slate-100 rounded-md w-1/2"></div>
                    </div>
                    <div class="h-10 w-16 bg-slate-100 rounded-lg"></div>
                </div>
                <div class="flex-1 bg-slate-50 rounded-xl border border-slate-50"></div>
            </div>
            @endfor

        </div>
    </div>

@endsection

{{-- Inject Script Khusus Halaman kadis --}}
@push('scripts')
    @vite('resources/js/pages/kadis/dashboard.js')
@endpush