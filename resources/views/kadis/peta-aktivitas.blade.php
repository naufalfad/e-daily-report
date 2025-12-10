@php($title = 'Peta Aktivitas')
@extends('layouts.app', [
'title' => $title,
'role' => 'kadis',
'active' => 'map'
])

{{-- STYLES --}}
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
{{-- Tambahkan CSS SweetAlert agar tampilannya cantik --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
.map-container {
    height: min(60vh, 500px);
    width: 100%;
    position: relative;
    z-index: 1;
}

#map,
.leaflet-container {
    width: 100%;
    height: 100%;
    z-index: 1 !important;
}


    /* Custom Popup Styles - Lebih minimalis karena tombol pindah ke modal */
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    }

    .leaflet-popup-content {
        margin: 0 !important;
        font-family: 'Poppins', sans-serif;
    }
</style>
@endpush

{{-- SCRIPTS --}}
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
{{-- Tambahkan JS SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

<section x-data="kadisMapData" x-init="initMap()" class="relative">

    {{-- CARD UTAMA --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 relative z-10">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-[20px] font-normal">Peta Aktivitas Pegawai</h2>
            
            {{-- Indikator Loading --}}
            <div x-show="loading" class="text-xs font-medium text-emerald-600 flex items-center gap-2 bg-emerald-50 px-3 py-1 rounded-full animate-pulse" style="display: none;">
                <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Memuat Data...
            </div>
            <div class="flex justify-end mt-4">
                <button @click="exportMap()"
                    class="px-4 py-2 bg-[#1C7C54] text-white rounded-lg text-sm hover:brightness-95 shadow">
                    Export Peta ke PDF
                </button>
            </div>
        </div>

        {{-- FILTER FORM --}}
        <form class="mb-4" @submit.prevent="applyFilter()">
            <label class="block text-xs font-semibold text-slate-600 mb-2">Filter Berdasarkan Tanggal</label>

            <div class="grid md:grid-cols-[1fr_1fr_auto] gap-3">

                {{-- Dari Tanggal --}}
                <div>
                    <div class="relative">
                        <input x-model="filter.from" id="tgl_dari" type="date" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                    px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30
                    focus:border-[#1C7C54] transition-all">

                        {{-- Tombol Kalender --}}
                        <button type="button" id="tgl_dari_btn" class="absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8 flex items-center justify-center
                    cursor-pointer hover:bg-slate-200 rounded-full transition-colors" title="Pilih Tanggal">
                            <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-70">
                        </button>
                    </div>
                </div>

                {{-- Sampai Tanggal --}}
                <div>
                    <div class="relative">
                        <input x-model="filter.to" id="tgl_sampai" type="date" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                    px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30
                    focus:border-[#1C7C54] transition-all">

                        {{-- Tombol Kalender --}}
                        <button type="button" id="tgl_sampai_btn" class="absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8 flex items-center justify-center
                    cursor-pointer hover:bg-slate-200 rounded-full transition-colors" title="Pilih Tanggal">
                            <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-70">
                        </button>
                    </div>
                </div>

                {{-- Tombol Terapkan --}}
                {{-- Tombol Terapkan --}}
                <div class="flex items-end">
                    <button type="submit"
                        class="h-[42px] rounded-[10px] bg-[#0E7A4A] px-6 text-sm font-medium text-white hover:brightness-95 transition-all shadow-sm flex items-center justify-center gap-2 min-w-[100px]"
                        :disabled="loading">
                        <span x-show="!loading">Terapkan</span>

                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135
                            5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Memuat...</span>
                        </span>
                    </button>
                </div>

            </div>

        </form>

        {{-- LEGENDA --}}
        <div
            class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs font-medium text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-emerald-200"></span>
                Disetujui
            </div>
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-amber-500 ring-2 ring-amber-200"></span>
                Menunggu Validasi
            </div>
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-rose-500 ring-2 ring-rose-200"></span>
                Ditolak
            </div>
        </div>

        {{-- MAP CONTAINER --}}
        <div class="map-container mt-4 rounded-xl ring-1 ring-slate-200 overflow-hidden shadow-sm relative">
            <div id="map"></div>
        </div>
    </div>

    {{-- MODAL --}} <div x-show="showModal" x-transition.opacity
        class="fixed inset-0 z-[9999] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal()"></div>
        {{-- MODAL DETAIL AKTIVITAS --}}
        <div x-show="showModal" x-transition.opacity
            class="fixed inset-0 z-[9999] flex items-center justify-center px-4" style="display: none;">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeModal()">
            </div>

            {{-- Modal Panel --}}
            <div
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all ring-1 ring-slate-200">

                {{-- HEADER --}}
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg tracking-tight">Detail Aktivitas</h3>
                        <p class="text-xs text-slate-500">Informasi lengkap lokasi aktivitas.</p>
                    </div>
                    <button @click="closeModal()"
                        class="text-slate-400 hover:text-rose-500 p-2 rounded-full hover:bg-rose-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- CONTENT --}}
                <div class="p-6 overflow-y-auto max-h-[70vh]">

                    <template x-if="selectedActivity">
                        <div>
                            {{-- Kategori --}} <span class="text-xs font-bold uppercase text-emerald-600"
                                x-text="selectedActivity.kategori_aktivitas"></span>

                            {{-- Judul --}}
                            <h2 class="text-xl font-bold text-slate-800 mt-1 mb-3" x-text="selectedActivity.kegiatan">
                            </h2>

                            {{-- Status + User --}}
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-1 text-xs rounded-md font-semibold border" :class="{
                                        'bg-emerald-50 text-emerald-600 border-emerald-200': selectedActivity.status === 'approved',
                                        'bg-rose-50 text-rose-600 border-rose-200': selectedActivity.status === 'rejected',
                                        'bg-amber-50 text-amber-600 border-amber-200': selectedActivity.status === 'waiting_review'
                                    }" x-text="selectedActivity.status === 'approved' ? 'Disetujui' :
                            {{-- Kategori Badge --}}
                            <span class=" inline-block px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-600 text-[10px]
                                    font-bold uppercase tracking-wide border border-blue-100 mb-2"
                                    x-text="selectedActivity.kategori_aktivitas"></span>

                                {{-- Judul Kegiatan --}}
                                <h2 class="text-lg font-bold text-slate-900 leading-snug mb-4"
                                    x-text="selectedActivity.kegiatan"></h2>

                                {{-- Status Bar --}}
                                <div class="flex items-center gap-3 mb-6">
                                    <span class="px-2.5 py-1 text-xs rounded-lg font-semibold border shadow-sm" :class="{
                                        'bg-emerald-50 text-emerald-600 border-emerald-100': selectedActivity.status === 'approved',
                                        'bg-rose-50 text-rose-600 border-rose-100': selectedActivity.status === 'rejected',
                                        'bg-amber-50 text-amber-600 border-amber-100': selectedActivity.status === 'waiting_review'
                                    }"
                                        x-text="selectedActivity.status === 'approved' ? 'Disetujui' :
                                            (selectedActivity.status === 'rejected' ? 'Ditolak' : 'Menunggu Validasi')">
                                    </span>

                                    <div class="h-4 w-px bg-slate-200"></div>

                            <div class="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                                <span class="bg-slate-100 px-2 py-0.5 rounded text-slate-700">
                                    👤 <span x-text="selectedActivity.user"></span>
                                </span>
                            </div>
                        </div>
                                    {{-- Detail --}}
                                    <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
                                        <div>
                                            <label class="text-xs text-slate-400">Tanggal</label>
                                            <div class="font-medium" x-text="selectedActivity.tanggal"></div>
                                        </div>

                                        <div>
                                            <label class="text-xs text-slate-400">Waktu</label>
                                            <div class="font-medium" x-text="selectedActivity.waktu"></div>
                                        </div>

                                        <div class="col-span-2">
                                            <label class="text-xs text-slate-400">Deskripsi</label>
                                            <div class="bg-slate-50 p-3 border rounded-lg italic text-slate-600"
                                                x-text="selectedActivity.deskripsi"></div>
                                        </div>
                                    </div>

                                    {{-- Grid Detail --}}
                                    <div
                                        class="bg-slate-50 rounded-xl p-4 border border-slate-100 grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <label
                                                class="text-[10px] uppercase font-bold text-slate-400 mb-1 block">Tanggal</label>
                                            <div class="font-semibold text-slate-700 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span x-text="selectedActivity.tanggal"></span>
                                            </div>
                                        </div>

                                        <div>
                                            <label
                                                class="text-[10px] uppercase font-bold text-slate-400 mb-1 block">Waktu</label>
                                            <div class="font-semibold text-slate-700 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span x-text="selectedActivity.waktu"></span>
                                            </div>
                                        </div>

                                        <div class="col-span-2 pt-2 border-t border-slate-200/60">
                                            <label
                                                class="text-[10px] uppercase font-bold text-slate-400 mb-1 block">Uraian
                                                / Deskripsi</label>
                                            <div class="text-slate-600 leading-relaxed italic"
                                                x-text="selectedActivity.deskripsi || '-'"></div>
                                        </div>

                                        <div class="col-span-2 pt-2 border-t border-slate-200/60">
                                            <label
                                                class="text-[10px] uppercase font-bold text-slate-400 mb-1 block">Lokasi
                                                Tercatat</label>
                                            <div class="text-slate-700 font-medium text-xs flex items-start gap-1">
                                                <svg class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span x-text="selectedActivity.lokasi_teks || 'Koordinat GPS'"></span>
                                            </div>
                                        </div>
                                    </div>

                        {{-- [UPDATE UTAMA] TOMBOL AKSI HANYA MUNCUL DI SINI --}}
                        {{-- Logika: Jika status 'waiting_review', tampilkan tombol Setujui/Tolak --}}
                        <template x-if="selectedActivity.status === 'waiting_review'">
                            <div class="mt-4 pt-4 border-t border-slate-100 bg-white sticky bottom-0">
                                <p class="text-[10px] font-bold text-slate-400 uppercase mb-3 tracking-wider">Tindakan Validasi</p>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    {{-- Tombol Setujui --}}
                                    <button @click="confirmApprove(selectedActivity.id)" 
                                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-all shadow-sm flex items-center justify-center gap-2 hover:shadow-emerald-200 hover:shadow-lg transform active:scale-[0.98]">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        Setujui
                                    </button>

                                    {{-- Tombol Tolak --}}
                                    <button @click="handleReject(selectedActivity.id)" 
                                        class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm transition-all shadow-sm flex items-center justify-center gap-2 hover:shadow-rose-200 hover:shadow-lg transform active:scale-[0.98]">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        Tolak
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- Jika Bukan Waiting Review, Tampilkan Tombol Tutup Saja --}}
                        <template x-if="selectedActivity.status !== 'waiting_review'">
                            <div class="mt-6 flex justify-end">
                                <button @click="closeModal()"
                                    class="px-6 py-2.5 bg-slate-100 text-slate-600 font-medium text-sm rounded-xl hover:bg-slate-200 transition-all">
                                    Tutup
                                </button>
                            </div>
                        </template>

                    </div>
                </template>
            </div>
        </div>
    </div>
</section>

@endsection