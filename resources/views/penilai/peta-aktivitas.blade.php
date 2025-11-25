@php($title = 'Peta Aktivitas')
@extends('layouts.app', ['title' => $title, 'role' => 'penilai', 'active' => 'map'])

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
/* Container peta tetap di dalam card tapi tidak terlalu tinggi */
.map-container {
    height: min(60vh, 500px);
    /* maksimal 500px, minimal proporsional */
    width: 100%;
    position: relative;
}

#map {
    width: 100%;
    height: 100%;
}

/* Popup Leaflet */
.leaflet-popup-content-wrapper {
    border-radius: 8px;
}

.leaflet-popup-content {
    font-family: 'Poppins', sans-serif !important;
    font-size: 13px;
    line-height: 1.6;
}
</style>
@endpush

@section('content')
<section x-data="mapPageData()" x-init="initMap()">

    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 relative">
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
                        class="rounded-[10px] bg-[#0E7A4A] px-5 py-2.5 text-sm text-white hover:brightness-95 w-full md:w-auto">
                        Terapkan
                    </button>
                </div>
            </div>
        </form>

        {{-- LEGENDA --}}
        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-600">
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-emerald-200"></span>
                <span>Laporan Diterima</span>
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

        {{-- MAP --}}
        <div class="map-container mt-4 rounded-lg ring-1 ring-slate-200 overflow-hidden">
            <div id="map"></div>
        </div>
    </div>

</section>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="//unpkg.com/alpinejs" defer></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mapPageData', () => ({
        map: null,
        markersLayer: null,
        allActivities: [],
        filter: {
            from: '',
            to: ''
        },

        initMap() {
            this.$nextTick(() => { // pastikan DOM siap
                this.map = L.map('map', {
                    zoomControl: true
                }).setView([-4.557, 136.885], 13);

                const googleRoadmap = L.tileLayer(
                    "https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}", {
                        attribution: "Google Maps",
                        maxZoom: 20
                    }
                );
                const googleSatelit = L.tileLayer(
                    "https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}", {
                        attribution: "Google Satelit",
                        maxZoom: 22
                    }
                );

                const baseLayers = {
                    "Google Maps": googleRoadmap,
                    "Google Satelit": googleSatelit
                };
                L.control.layers(baseLayers).addTo(this.map);
                googleRoadmap.addTo(this.map);

                this.markersLayer = L.layerGroup().addTo(this.map);

                this.loadData();
                this.initDatePickers();

                // Resize observer agar map tidak "meluber"
                new ResizeObserver(() => this.map.invalidateSize()).observe(document
                    .getElementById('map'));
            });
        },

        loadData() {
            fetch('/data/peta-aktivitas.json')
                .then(res => res.json())
                .then(data => {
                    this.allActivities = data;
                    this.loadMarkers(this.allActivities);
                })
                .catch(err => console.error(err));
        },

        loadMarkers(data) {
            this.markersLayer.clearLayers();
            data.forEach(act => {
                let color = '#f59e0b';
                if (act.status === 'approved') color = '#22c55e';
                else if (act.status === 'rejected') color = '#ef4444';

                L.circleMarker([act.lat, act.lng], {
                        radius: 7,
                        fillColor: color,
                        color: '#FFF',
                        weight: 2,
                        fillOpacity: 0.9
                    })
                    .bindPopup(`<div style="font-size:13px;line-height:1.5;">
                    <strong style="font-size:14px;color:#1C7C54;">${act.kegiatan}</strong><br>
                    <strong>Pegawai:</strong> ${act.user}<br>
                    <strong>Status:</strong> ${act.status}
                </div>`)
                    .addTo(this.markersLayer);
            });
        },

        applyFilter() {
            const from = this.filter.from ? new Date(this.filter.from) : null;
            const to = this.filter.to ? new Date(this.filter.to) : null;
            if (from) from.setHours(0, 0, 0, 0);
            if (to) to.setHours(23, 59, 59, 999);

            const filtered = this.allActivities.filter(act => {
                const actDate = new Date(act.tanggal_laporan);
                if (from && actDate < from) return false;
                if (to && actDate > to) return false;
                return true;
            });

            this.loadMarkers(filtered);
        },

        initDatePickers() {
            this.$nextTick(() => {
                ['tgl_dari', 'tgl_sampai'].forEach(id => {
                    const input = document.getElementById(id);
                    const btn = document.getElementById(id + '_btn');
                    if (input && btn) btn.addEventListener('click', () => {
                        try {
                            input.showPicker()
                        } catch (e) {
                            input.focus()
                        }
                    });
                });
            });
        }
    }));
});
</script>
@endpush