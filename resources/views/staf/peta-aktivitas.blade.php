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

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="//unpkg.com/alpinejs" defer></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mapPageData', () => ({
        // Variable State
        map: null,
        markersLayer: null,
        allActivities: [],
        filter: {
            from: '',
            to: ''
        },

        // State untuk Modal
        showModal: false,
        selectedActivity: null,

        initMap() {
            this.$nextTick(() => {
                // 1. Inisialisasi Peta
                this.map = L.map('map', {
                    zoomControl: true
                }).setView([-4.557, 136.885], 13);

                // 2. Tile Layers
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

                // 3. Layer Group Marker
                this.markersLayer = L.layerGroup().addTo(this.map);

                // 4. Load Data Awal & Datepicker
                this.loadData();
                this.initDatePickers();

                // 5. Resize Observer
                new ResizeObserver(() => this.map.invalidateSize()).observe(document
                    .getElementById('map'));

                // 6. BRIDGING FUNCTION (PENTING)
                // Mendaftarkan fungsi global window agar bisa dipanggil dari string HTML Leaflet
                // Fungsi ini akan memanggil method openModal di dalam Alpine component
                window.openActivityDetail = (id) => {
                    this.openModal(id);
                };
            });
        },

        // --- LOGIKA DATA ---
        loadData() {
            // Menggunakan path sesuai request user
            fetch('/data/peta-aktivitas.json')
                .then(res => res.json())
                .then(data => {
                    this.allActivities = data;
                    this.loadMarkers(this.allActivities);
                })
                .catch(err => console.error("Gagal memuat data:", err));
        },

        loadMarkers(data) {
            this.markersLayer.clearLayers();

            data.forEach(act => {
                // A. Tentukan Warna & Status
                let color = '#f59e0b'; // Default: Amber (Pending)
                let bgColorStatus = '#fffbeb';
                let statusLabel = 'Menunggu';

                if (act.status === 'approved') {
                    color = '#22c55e'; // Green
                    bgColorStatus = '#dcfce7';
                    statusLabel = 'Disetujui';
                } else if (act.status === 'rejected') {
                    color = '#ef4444'; // Red
                    bgColorStatus = '#fee2e2';
                    statusLabel = 'Ditolak';
                }

                // B. Buat Konten Popup (HTML String)
                // Perhatikan tombol menggunakan onclick="window.openActivityDetail(...)"
                const popupContent = `
                    <div style="padding: 12px 10px; min-width: 260px;">
                        
                        <div style="margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                            <strong style="font-size:14px; color:#1C7C54; display:block; line-height:1.3; margin-bottom:2px;">
                                ${act.kegiatan}
                            </strong>
                            <div style="display:flex; align-items:center; gap:4px; font-size:11px; color:#64748b;">
                                <span>👤 ${act.user}</span>
                                <span>•</span>
                                <span style="color:#0E7A4A; font-weight:500;">${act.kategori_aktivitas}</span>
                            </div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <div style="display:flex; gap:10px; font-size:11px; color:#475569; margin-bottom:6px;">
                                <span style="display:flex; align-items:center; gap:3px;">📅 ${act.tanggal}</span>
                                <span style="display:flex; align-items:center; gap:3px;">⏰ ${act.waktu}</span>
                            </div>
                            <p style="font-size:12px; line-height:1.5; color:#334155; margin:0; font-style:italic; background:#f8fafc; padding:6px; border-radius:4px; border-left: 3px solid ${color};">
                                "${act.deskripsi.length > 50 ? act.deskripsi.substring(0,50)+'...' : act.deskripsi}"
                            </p>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding-top:8px; border-top:1px dashed #e2e8f0;">
                            <span style="font-size:10px; font-weight:600; color:${color}; background:${bgColorStatus}; padding:2px 8px; border-radius:10px; border:1px solid ${color}40;">
                                ${statusLabel}
                            </span>

                            <button onclick="window.openActivityDetail(${act.id})"
                               style="cursor: pointer; border: none; display: inline-block; background-color: #0E7A4A; color: #ffffff; padding: 5px 12px; font-size: 11px; font-weight: 500; border-radius: 6px; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.1);"
                               onmouseover="this.style.backgroundColor='#0a5c38'"
                               onmouseout="this.style.backgroundColor='#0E7A4A'"
                            >
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                `;

                // C. Render Marker
                L.circleMarker([act.lat, act.lng], {
                        radius: 7,
                        fillColor: color,
                        color: '#FFF',
                        weight: 2,
                        fillOpacity: 0.9
                    })
                    .bindPopup(popupContent)
                    .addTo(this.markersLayer);
            });
        },

        // --- LOGIKA MODAL ---
        openModal(id) {
            // Cari data array berdasarkan ID
            // Kita pakai '==' agar aman jika tipe data ID string vs number
            const found = this.allActivities.find(item => item.id == id);
            if (found) {
                this.selectedActivity = found;
                this.showModal = true;
            }
        },

        closeModal() {
            this.showModal = false;
            // Delay sedikit agar transisi tutup selesai baru hapus data (smooth UX)
            setTimeout(() => {
                this.selectedActivity = null
            }, 300);
        },

        // --- LOGIKA FILTER ---
        applyFilter() {
            const from = this.filter.from ? new Date(this.filter.from) : null;
            const to = this.filter.to ? new Date(this.filter.to) : null;

            if (from) from.setHours(0, 0, 0, 0);
            if (to) to.setHours(23, 59, 59, 999);

            const filtered = this.allActivities.filter(act => {
                // Asumsi format tanggal di JSON adalah YYYY-MM-DD
                const actDate = new Date(act.tanggal);
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
                    if (input && btn) {
                        btn.addEventListener('click', () => {
                            try {
                                input.showPicker()
                            } catch (e) {
                                input.focus()
                            }
                        });
                    }
                });
            });
        }
    }));
});
</script>
@endpush