@php($title = 'Peta Aktivitas')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'map'])

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
/* Container peta responsive */
.map-container {
    height: min(60vh, 500px);
    width: 100%;
    position: relative;
    z-index: 1;
    /* Pastikan lebih rendah dari modal */
}

#map {
    width: 100%;
    height: 100%;
}

/* Popup Leaflet Custom Styling */
.leaflet-popup-content-wrapper {
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.leaflet-popup-content {
    font-family: 'Poppins', sans-serif !important;
    font-size: 13px;
    margin: 0 !important;
    line-height: 1.6;
}

.leaflet-popup-tip {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>
@endpush

@section('content')
<section x-data="mapPageData()" x-init="initMap()">

    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 relative z-10">
        <h2 class="text-[20px] font-normal mb-1">Peta Aktivitas Pegawai</h2>

        {{-- FILTER TANGGAL --}}
        <form class="mt-4" @submit.prevent="applyFilter()">
            <label class="block text-xs font-normal text-slate-600 mb-2">Filter Berdasarkan Tanggal</label>
            <div class="grid md:grid-cols-[1fr_1fr_auto] gap-3">

                {{-- Dari --}}
                <div>
                    <label class="sr-only">Dari Tanggal</label>
                    <div class="relative">
                        <input x-model="filter.from" id="tgl_dari" type="date" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                            px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                            focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                        <button type="button" id="tgl_dari_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-80">
                        </button>
                    </div>
                </div>

                {{-- Sampai --}}
                <div>
                    <label class="sr-only">Sampai Tanggal</label>
                    <div class="relative">
                        <input x-model="filter.to" id="tgl_sampai" type="date" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                            px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                            focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                        <button type="button" id="tgl_sampai_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-80">
                        </button>
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="flex items-end">
                    <button type="submit"
                        class="rounded-[10px] bg-[#0E7A4A] px-5 py-2.5 text-sm text-white hover:brightness-95 w-full md:w-auto transition-all">
                        Terapkan
                    </button>
                </div>
            </div>
        </form>

        {{-- LEGENDA --}}
        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-600">
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-emerald-200"></span>
                <span>Laporan Disetujui</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-amber-500 ring-2 ring-amber-200"></span>
                <span>Menunggu Validasi</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-rose-500 ring-2 ring-rose-200"></span>
                <span>Laporan Ditolak</span>
            </div>
        </div>

        {{-- MAP CONTAINER --}}
        <div class="map-container mt-4 rounded-lg ring-1 ring-slate-200 overflow-hidden">
            <div id="map"></div>
        </div>
    </div>

    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center px-4"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">

        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]"
            @click.stop>
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-semibold text-slate-800 text-lg">Detail Aktivitas</h3>
                <button @click="closeModal()"
                    class="text-slate-400 hover:text-rose-500 transition-colors p-1 rounded-md hover:bg-rose-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto" x-if="selectedActivity">

                <div class="mb-6">
                    <span class="text-xs font-bold tracking-wider uppercase text-emerald-600"
                        x-text="selectedActivity.kategori_aktivitas"></span>
                    <h2 class="text-xl font-bold text-slate-800 mt-1 leading-snug" x-text="selectedActivity.kegiatan">
                    </h2>

                    <div class="flex items-center gap-3 mt-3">
                        <span class="px-2.5 py-1 rounded-md text-xs font-semibold border" :class="{
                                  'bg-emerald-50 text-emerald-600 border-emerald-200': selectedActivity.status === 'approved',
                                  'bg-rose-50 text-rose-600 border-rose-200': selectedActivity.status === 'rejected',
                                  'bg-amber-50 text-amber-600 border-amber-200': selectedActivity.status === 'pending' || !['approved','rejected'].includes(selectedActivity.status)
                              }"
                            x-text="selectedActivity.status === 'approved' ? 'Disetujui' : (selectedActivity.status === 'rejected' ? 'Ditolak' : 'Menunggu Validasi')">
                        </span>
                        <span class="text-xs text-slate-500 flex items-center gap-1">
                            <span>👤</span> <span x-text="selectedActivity.user"></span>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-5 gap-x-4 text-sm">

                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs text-slate-400 mb-1 uppercase tracking-wide">Tanggal & Waktu</label>
                        <div class="text-slate-700 font-medium flex items-center gap-2">
                            <span x-text="selectedActivity.tanggal"></span>
                            <span class="text-slate-300">|</span>
                            <span x-text="selectedActivity.waktu"></span>
                        </div>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs text-slate-400 mb-1 uppercase tracking-wide">Lokasi</label>
                        <div class="text-slate-700 font-medium" x-text="selectedActivity.lokasi || '-'"></div>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs text-slate-400 mb-1 uppercase tracking-wide">Deskripsi</label>
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-slate-600 leading-relaxed italic"
                            x-text="selectedActivity.deskripsi"></div>
                    </div>

                    <div class="col-span-2 border-t border-slate-100 pt-4 mt-2">
                        <label class="block text-xs text-slate-400 mb-3 uppercase tracking-wide">Lampiran</label>

                        <div class="flex flex-wrap gap-3">

                            <a :href="selectedActivity.surat_tugas || 'javascript:void(0)'"
                                :target="selectedActivity.surat_tugas ? '_blank' : '_self'"
                                :download="selectedActivity.surat_tugas ? true : false"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-700 font-medium text-sm hover:bg-slate-50 hover:text-slate-900 transition shadow-sm cursor-pointer text-decoration-none">

                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7 10 12 15 17 10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>

                                Unduh Surat Tugas
                            </a>

                            <a :href="selectedActivity.foto || 'javascript:void(0)'"
                                :target="selectedActivity.foto ? '_blank' : '_self'"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-300 rounded-lg text-slate-700 font-medium text-sm hover:bg-slate-50 hover:text-slate-900 transition shadow-sm cursor-pointer text-decoration-none">

                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <polyline points="21 15 16 10 5 21" />
                                </svg>

                                Lihat Foto
                            </a>

                        </div>
                    </div>
                </div>

                <div class="bg-slate-10 px-6 py-3 flex justify-end">
                    <button @click="closeModal()"
                        class="px-5 py-2 bg-white border border-slate-300 rounded-lg text-slate-600 text-sm font-medium hover:bg-slate-50 hover:text-slate-800 transition shadow-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

</section>

@endsection