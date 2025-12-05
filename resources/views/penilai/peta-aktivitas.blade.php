@php($title = 'Peta Aktivitas')
@extends('layouts.app', [
    'title' => $title,
    'role' => 'penilai',
    'active' => 'map'
])

{{-- STYLES --}}
@push('styles')

<!-- LEAFLET CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
    .map-container {
        height: min(60vh, 500px);
        width: 100%;
        position: relative;
        z-index: 1;
    }

    #map, .leaflet-container {
        width: 100%;
        height: 100%;
        z-index: 1 !important;
    }

    .leaflet-popup-content-wrapper {
        border-radius: 10px;
        padding: 0;
    }

    .leaflet-popup-content {
        margin: 0 !important;
        font-family: 'Poppins', sans-serif;
    }
</style>

@endpush


{{-- SCRIPTS --}}
@push('scripts')
<!-- LEAFLET JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush



@section('content')

<section x-data="penilaiMapData" x-init="initMap()" class="relative">

    {{-- CARD CONTENT --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 relative z-10">
        <h2 class="text-[20px] font-normal mb-1">Peta Aktivitas Pegawai</h2>

        {{-- FILTER --}}
        <form class="mt-4" @submit.prevent="applyFilter()">
            <label class="block text-xs text-slate-600 mb-2">Filter Berdasarkan Tanggal</label>

            <div class="grid md:grid-cols-[1fr_1fr_auto] gap-3">

                {{-- Dari --}}
                <div>
                    <div class="relative">
                        <input 
                            x-model="filter.from"
                            @change="applyFilter()"
                            id="tgl_dari"
                            type="date"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                   px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#1C7C54]/30 
                                   focus:border-[#1C7C54]">

                        <button type="button" id="tgl_dari_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-70">
                        </button>
                    </div>
                </div>

                {{-- Sampai --}}
                <div>
                    <div class="relative">
                        <input 
                            x-model="filter.to"
                            @change="applyFilter()"
                            id="tgl_sampai"
                            type="date"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                   px-3.5 py-2.5 text-sm focus:ring-2
                                   focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">

                        <button type="button" id="tgl_sampai_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-70">
                        </button>
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="flex items-end">
                    <button type="submit"
                        class="rounded-[10px] bg-[#0E7A4A] px-6 py-2.5 text-sm text-white hover:brightness-95 transition">
                        Terapkan
                    </button>
                </div>

            </div>
        </form>

        {{-- LEGENDA --}}
        <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-600">
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-emerald-200"></span>
                Laporan Disetujui
            </div>
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-amber-500 ring-2 ring-amber-200"></span>
                Menunggu Validasi
            </div>
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-rose-500 ring-2 ring-rose-200"></span>
                Laporan Ditolak
            </div>
        </div>

        {{-- MAP --}}
        <div class="map-container mt-4 rounded-lg ring-1 ring-slate-200 overflow-hidden">
            <div id="map"></div>
        </div>
    </div>




    {{-- MODAL --}}
    <div 
        x-show="showModal"
        x-transition.opacity
        class="fixed inset-0 z-[9999] flex items-center justify-center px-4"
    >
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden">

            {{-- HEADER --}}
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-semibold text-slate-800 text-lg">Detail Aktivitas</h3>
                <button @click="closeModal()" class="text-slate-400 hover:text-rose-500 p-1">
                    ✕
                </button>
            </div>

            {{-- CONTENT --}}
            <div class="p-6 overflow-y-auto max-h-[70vh]">

                <template x-if="selectedActivity">

                    <div>

                        {{-- Kategori --}}
                        <span class="text-xs font-bold uppercase text-emerald-600"
                              x-text="selectedActivity.kategori_aktivitas"></span>

                        {{-- Judul --}}
                        <h2 class="text-xl font-bold text-slate-800 mt-1 mb-3"
                            x-text="selectedActivity.kegiatan"></h2>

                        {{-- Status + User --}}
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 text-xs rounded-md font-semibold border"
                                :class="{
                                    'bg-emerald-50 text-emerald-600 border-emerald-200': selectedActivity.status === 'approved',
                                    'bg-rose-50 text-rose-600 border-rose-200': selectedActivity.status === 'rejected',
                                    'bg-amber-50 text-amber-600 border-amber-200': selectedActivity.status === 'waiting_review'
                                }"
                                x-text="selectedActivity.status === 'approved' ? 'Disetujui' :
                                        (selectedActivity.status === 'rejected' ? 'Ditolak' : 'Menunggu Validasi')">
                            </span>

                            <span class="text-xs text-slate-500 flex items-center gap-1">
                                👤 <span x-text="selectedActivity.user"></span>
                            </span>
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

                        {{-- BUTTON --}}
                        <div class="mt-4 text-right">
                            <button @click="closeModal()"
                                class="px-5 py-2 bg-white border border-slate-300 rounded-lg text-slate-600 text-sm">
                                Tutup
                            </button>
                        </div>

                    </div>

                </template>

            </div>

        </div>
    </div>


</section>

@endsection
