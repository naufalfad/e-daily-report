@php($title = 'Peta Aktivitas')
@extends('layouts.app', [
    'title' => $title,
    'role' => 'penilai',
    'active' => 'map'
])

{{-- STYLES --}}
@push('styles')
    {{-- Leaflet Core --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    
    {{-- Leaflet MarkerCluster --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />

    {{-- SweetAlert --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        /* --- MAP CONTAINER --- */
        .map-container {
            height: min(70vh, 650px);
            width: 100%;
            position: relative;
            z-index: 1;
            border-radius: 1rem;
            overflow: hidden;
        }

        #map, .leaflet-container {
            width: 100%;
            height: 100%;
            z-index: 1 !important;
            border-radius: 1rem;
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
        }

        /* --- POPUP LIST STYLING (Humanis UI) --- */
        .custom-cluster-popup .leaflet-popup-content-wrapper {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.15), 0 10px 20px -5px rgba(0, 0, 0, 0.1);
            padding: 0;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .custom-cluster-popup .leaflet-popup-content {
            margin: 0 !important;
            width: 360px !important; /* Lebar optimal */
            line-height: 1.5;
        }

        .custom-cluster-popup .leaflet-popup-tip {
            background: #ffffff;
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }

        .custom-cluster-popup a.leaflet-popup-close-button {
            top: 14px;
            right: 14px;
            color: #94a3b8;
            font-size: 20px;
            padding: 0;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            z-index: 10;
        }
        
        .custom-cluster-popup a.leaflet-popup-close-button:hover {
            color: #ef4444;
            background: #fee2e2;
        }

        /* --- CLUSTER ICONS (BLUE THEME) --- */
        .marker-cluster-custom {
            background-clip: padding-box;
            border-radius: 50%;
            transition: transform 0.2s ease-out;
        }
        
        .marker-cluster-custom:hover {
            transform: scale(1.1);
            z-index: 1000 !important;
        }

        .marker-cluster-custom div {
            width: 36px;
            height: 36px;
            margin: 2px;
            text-align: center;
            border-radius: 50%;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: white;
            line-height: 36px;
            font-size: 11px;
            box-shadow: inset 0 1px 2px rgba(255,255,255,0.25);
        }

        /* Gradasi Biru untuk Kepadatan */
        .marker-cluster-small { background-color: rgba(56, 189, 248, 0.3); }
        .marker-cluster-small div { background-color: #0ea5e9; box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.4); }

        .marker-cluster-medium { background-color: rgba(37, 99, 235, 0.3); }
        .marker-cluster-medium div { background-color: #2563eb; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.4); }

        .marker-cluster-large { background-color: rgba(30, 64, 175, 0.3); }
        .marker-cluster-large div { background-color: #1e40af; animation: pulse-blue 2s infinite; }

        @keyframes pulse-blue {
            0% { box-shadow: 0 0 0 0 rgba(30, 64, 175, 0.7); }
            70% { box-shadow: 0 0 0 8px rgba(30, 64, 175, 0); }
            100% { box-shadow: 0 0 0 0 rgba(30, 64, 175, 0); }
        }
    </style>
@endpush

{{-- SCRIPTS --}}
@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

<section x-data="penilaiMapData()" x-init="initMap()" class="relative font-poppins pb-10">

    {{-- HEADER CARD & FILTERS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6 relative z-10">
        
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-6">
            {{-- Title --}}
            <div>
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Peta Aktivitas Pegawai</h2>
                <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Pantau sebaran kinerja bawahan secara real-time
                </p>
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center gap-3">

                {{-- GPS Button --}}
                <button @click="zoomToCurrentLocation()"
                    class="px-4 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm flex items-center gap-2 group"
                    title="Cek Posisi Saya">
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Lokasi Saya
                </button>

                {{-- Export Button --}}
                <button @click="exportMap()"
                    class="px-5 py-2.5 bg-[#1C7C54] text-white rounded-xl text-sm font-medium hover:bg-[#15683f] hover:shadow-lg hover:shadow-emerald-200 transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-5 h-5 text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Export Laporan
                </button>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form @submit.prevent="applyFilter()" class="bg-slate-50 p-5 rounded-2xl border border-slate-100 mb-2">
            
            {{-- [LOGIKA BARU] View Mode Switcher --}}
            <div class="flex bg-slate-200/50 p-1 rounded-xl mb-5 w-full sm:w-fit">
                <button type="button"
                    @click="switchMode('staff')"
                    :class="viewMode === 'staff' ? 'bg-white text-slate-800 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                    class="px-6 py-2 rounded-lg text-xs sm:text-sm transition-all flex-1 sm:flex-none text-center">
                    Data Bawahan
                </button>
                <button type="button"
                    @click="switchMode('personal')"
                    :class="viewMode === 'personal' ? 'bg-white text-slate-800 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                    class="px-6 py-2 rounded-lg text-xs sm:text-sm transition-all flex-1 sm:flex-none text-center">
                    Peta Saya
                </button>
            </div>

            <div class="flex flex-col md:flex-row items-end gap-5">
                <div class="w-full grid grid-cols-1 sm:grid-cols-2 gap-5 flex-1">
                    {{-- Input Tgl Dari --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Mulai Tanggal</label>
                        <div class="relative group">
                            <input x-model="filter.from" id="tgl_dari" type="date" 
                                class="w-full h-[42px] rounded-xl border-slate-200 bg-white text-slate-700 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all pl-4 pr-10 cursor-pointer shadow-sm">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 group-hover:text-emerald-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Input Tgl Sampai --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                        <div class="relative group">
                            <input x-model="filter.to" id="tgl_sampai" type="date" 
                                class="w-full h-[42px] rounded-xl border-slate-200 bg-white text-slate-700 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all pl-4 pr-10 cursor-pointer shadow-sm">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 group-hover:text-emerald-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit"
                    class="w-full md:w-auto h-[42px] px-8 bg-slate-800 text-white rounded-xl text-sm font-semibold hover:bg-slate-900 hover:shadow-lg transition-all shadow-sm flex items-center justify-center gap-2 min-w-[140px]"
                    :disabled="loading">
                    <span x-show="!loading">Terapkan Filter</span>
                    <span x-show="loading" class="flex items-center gap-2" style="display: none;">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memuat...
                    </span>
                </button>
            </div>
        </form>

        {{-- LEGEND --}}
        <div class="mt-5 flex flex-wrap items-center gap-x-8 gap-y-3 text-xs font-medium text-slate-600 border-t border-slate-100 pt-4">
            <span class="text-slate-400 uppercase font-bold text-[10px] tracking-widest">Keterangan:</span>
            
            {{-- Status Items --}}
            <div class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-50 transition-colors cursor-help" title="Laporan telah disetujui">
                <span class="w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-emerald-100"></span> Disetujui
            </div>
            <div class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-50 transition-colors cursor-help" title="Menunggu validasi">
                <span class="w-3 h-3 rounded-full bg-amber-500 ring-2 ring-amber-100"></span> Menunggu
            </div>
            <div class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-50 transition-colors cursor-help" title="Laporan ditolak">
                <span class="w-3 h-3 rounded-full bg-rose-500 ring-2 ring-rose-100"></span> Ditolak
            </div>
            
            <div class="hidden sm:block w-px h-4 bg-slate-300 mx-2"></div>

            {{-- Cluster Legend --}}
            <div class="flex items-center gap-2 px-3 py-1 bg-blue-50 rounded-full border border-blue-100 text-blue-700 font-semibold cursor-help" title="Menandakan jumlah aktivitas di lokasi berdekatan">
                <span class="flex items-center justify-center h-5 w-5 rounded-full bg-blue-500 text-[10px] text-white font-bold ring-2 ring-blue-200">N</span>
                <span>Area Padat (Cluster)</span>
            </div>
        </div>
    </div>

    {{-- MAP CONTAINER --}}
    <div class="bg-white p-2 rounded-2xl shadow-sm border border-slate-200">
        <div class="map-container shadow-inner">
            <div id="map"></div>
        </div>
    </div>

    {{-- MODAL DETAIL AKTIVITAS (IMPROVED UI) --}}
    <div x-show="showModal" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6" 
        style="display: none;">
        
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>

        {{-- Modal Panel --}}
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all flex flex-col max-h-[90vh] ring-1 ring-slate-900/5">

            {{-- HEADER --}}
            <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-white flex justify-between items-start shrink-0">
                <div>
                    <h3 class="font-bold text-slate-800 text-xl tracking-tight">Detail Laporan</h3>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide bg-blue-50 text-blue-700 border border-blue-100 shadow-sm"
                            x-text="selectedActivity?.kategori_aktivitas">
                        </span>
                    </div>
                </div>
                <button @click="closeModal()" class="text-slate-400 hover:text-rose-500 hover:bg-rose-50 p-2 rounded-full transition-all focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- SCROLLABLE CONTENT --}}
            <div class="p-6 overflow-y-auto grow custom-scrollbar">
                <template x-if="selectedActivity">
                    <div class="space-y-6">
                        
                        {{-- User Info Card --}}
                        <div class="flex items-center justify-between bg-slate-50 p-4 rounded-xl border border-slate-100 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="h-11 w-11 rounded-full bg-white border border-slate-200 flex items-center justify-center text-xl shadow-sm shrink-0">👤</div>
                                <div>
                                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Pegawai</p>
                                    <p class="text-sm font-bold text-slate-800 line-clamp-1" x-text="selectedActivity.user"></p>
                                </div>
                            </div>
                            
                            {{-- Status Badge --}}
                            <div class="text-right shrink-0">
                                <span class="px-3 py-1.5 text-xs rounded-lg font-bold border shadow-sm inline-block tracking-wide" 
                                    :class="{
                                        'bg-emerald-50 text-emerald-700 border-emerald-200': selectedActivity.status === 'approved',
                                        'bg-rose-50 text-rose-700 border-rose-200': selectedActivity.status === 'rejected',
                                        'bg-amber-50 text-amber-700 border-amber-200': selectedActivity.status === 'waiting_review'
                                    }"
                                    x-text="selectedActivity.status === 'approved' ? 'DISETUJUI' : (selectedActivity.status === 'rejected' ? 'DITOLAK' : 'MENUNGGU')">
                                </span>
                            </div>
                        </div>

                        {{-- Main Content --}}
                        <div>
                            <h4 class="text-slate-800 font-bold text-lg leading-snug mb-4 pb-3 border-b border-slate-100" x-text="selectedActivity.kegiatan"></h4>
                            
                            <div class="space-y-5">
                                {{-- Waktu --}}
                                <div class="flex gap-4 items-start group">
                                    <div class="mt-0.5 w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0 border border-blue-100 group-hover:bg-blue-100 transition-colors">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-0.5">Waktu Pelaksanaan</p>
                                        <p class="text-sm text-slate-700 font-medium bg-slate-50 px-2 py-1 rounded inline-block border border-slate-100">
                                            <span x-text="selectedActivity.tanggal"></span> • <span x-text="selectedActivity.waktu"></span>
                                        </p>
                                    </div>
                                </div>

                                {{-- Deskripsi --}}
                                <div class="flex gap-4 items-start group">
                                    <div class="mt-0.5 w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center shrink-0 border border-purple-100 group-hover:bg-purple-100 transition-colors">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-0.5">Output / Hasil</p>
                                        <p class="text-sm text-slate-600 italic leading-relaxed" x-text="selectedActivity.deskripsi || '-'"></p>
                                    </div>
                                </div>

                                {{-- Lokasi --}}
                                <div class="flex gap-4 items-start group">
                                    <div class="mt-0.5 w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center shrink-0 border border-orange-100 group-hover:bg-orange-100 transition-colors">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-0.5">Lokasi Tercatat</p>
                                        <p class="text-sm text-slate-700 font-medium break-words leading-snug" x-text="selectedActivity.lokasi_teks || 'Koordinat GPS'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </template>
            </div>

            {{-- FOOTER ACTIONS --}}
            <div class="px-6 py-5 border-t border-slate-100 bg-slate-50 shrink-0">
                
                {{-- [LOGIKA BARU] MODE VALIDASI: Hanya muncul jika status Waiting Review DAN Mode = Staff --}}
                <template x-if="selectedActivity && selectedActivity.status === 'waiting_review' && viewMode === 'staff'">
                    <div class="grid grid-cols-2 gap-4">
                        <button @click="confirmApprove(selectedActivity.id)" 
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition-all shadow-sm hover:shadow-emerald-200 hover:shadow-lg transform active:scale-[0.98] flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Setujui Laporan
                        </button>

                        <button @click="handleReject(selectedActivity.id)" 
                            class="w-full py-3 bg-white border border-slate-200 text-rose-600 hover:bg-rose-50 hover:border-rose-200 font-bold rounded-xl text-sm transition-all shadow-sm hover:shadow-md active:scale-[0.98] flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            Tolak
                        </button>
                    </div>
                </template>

                {{-- [LOGIKA BARU] MODE PERBAIKAN: Hanya jika status rejected DAN mode personal --}}
                <template x-if="selectedActivity && selectedActivity.status === 'rejected' && viewMode === 'personal'">
                    <div class="flex justify-end gap-3">
                        <button @click="window.editActivity(selectedActivity.id)"
                            class="px-6 py-3 bg-amber-500 text-white font-medium text-sm rounded-xl hover:bg-amber-600 transition-all shadow-lg hover:shadow-xl transform active:scale-[0.98] flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Perbaiki Laporan
                        </button>
                        <button @click="closeModal()" class="px-8 py-3 bg-slate-800 text-white font-medium text-sm rounded-xl hover:bg-slate-900 transition-all shadow-lg hover:shadow-xl transform active:scale-[0.98]">
                            Tutup Detail
                        </button>
                    </div>
                </template>

                {{-- [LOGIKA BARU] DEFAULT: Muncul jika tidak sedang dalam mode aksi di atas --}}
                <template x-if="selectedActivity && !(selectedActivity.status === 'waiting_review' && viewMode === 'staff') && !(selectedActivity.status === 'rejected' && viewMode === 'personal')">
                    <div class="flex justify-end">
                        <button @click="closeModal()"
                            class="px-8 py-3 bg-slate-800 text-white font-medium text-sm rounded-xl hover:bg-slate-900 transition-all shadow-lg hover:shadow-xl transform active:scale-[0.98]">
                            Tutup Detail
                        </button>
                    </div>
                </template>
            </div>

        </div>
    </div>
</section>

@endsection