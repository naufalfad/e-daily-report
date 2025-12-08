@php($title = 'Input LKH')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'input-lkh'])

@section('content')

{{-- Style Tambahan untuk Hasil Pencarian Peta --}}
<style>
    .search-results::-webkit-scrollbar {
        width: 6px;
    }
    .search-results::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .search-results::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 4px;
    }
    .search-results::-webkit-scrollbar-thumb:hover {
        background: #aaa;
    }
</style>

{{-- GRID UTAMA --}}
<section class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)] gap-4 lg:auto-rows-min">

    {{-- KIRI ATAS: FORM INPUT LKH --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
        <h2 class="text-[20px] font-normal mb-4">Form Input LKH</h2>

        <form id="form-lkh">
            <input type="hidden" name="status" id="status_input" value="draft">
            
            {{-- Mode Lokasi Hidden Input (Default: geofence) --}}
            <input type="hidden" name="mode_lokasi" id="mode_lokasi_input" value="geofence">

            <div class="space-y-4">

                {{-- Row 1: Tanggal + Jenis Kegiatan --}}
                <div class="grid md:grid-cols-2 gap-4">
                    {{-- Tanggal --}}
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Tanggal</label>
                        <div class="relative">
                            <input id="tanggal_lkh" name="tanggal_laporan" type="date"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                            <button type="button" id="tanggal_lkh_btn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-80"
                                    alt="Date" />
                            </button>
                        </div>
                    </div>

                    {{-- Jenis Kegiatan (Alpine Optimized) --}}
                    <div x-data="{
                                open: false,
                                value: '',
                                label: 'Pilih Jenis Kegiatan',
                                options: ['Rapat', 'Pelayanan Publik', 'Penyusunan Dokumen', 'Kunjungan Lapangan', 'Lainnya'],
                                select(opt) { this.value = opt; this.label = opt; this.open = false; }
                            }">
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Jenis Kegiatan</label>
                        <input type="hidden" name="jenis_kegiatan" x-model="value">

                        <div class="relative">
                            <button type="button" @click="open = !open" @click.outside="open = false"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm pr-3 text-left flex items-center justify-between focus:ring-2 focus:ring-[#1C7C54]/30"
                                :class="!value ? 'text-slate-400' : 'text-slate-700'">
                                <span x-text="label"></span>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70"
                                    alt="">
                            </button>

                            <div x-show="open" x-transition
                                class="absolute z-20 mt-1 w-full rounded-[10px] bg-white shadow-lg border border-slate-200 py-1">
                                <template x-for="opt in options" :key="opt">
                                    <button type="button"
                                        class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50 flex justify-between"
                                        :class="value === opt ? 'text-[#1C7C54] font-medium' : 'text-slate-700'"
                                        @click="select(opt)">
                                        <span x-text="opt"></span>
                                        <span x-show="value === opt">✓</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Row 2: Referensi Tupoksi (Fetch Logic Optimized) --}}
                <div x-data="{
                            open: false,
                            value: '',
                            label: 'Pilih Referensi Tupoksi',
                            options: [],
                            loading: false,
                            async init() {
                                this.loading = true;
                                try {
                                    const res = await fetch('/api/lkh/referensi', {
                                        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}`, 'Accept': 'application/json' }
                                    });
                                    const data = await res.json();
                                    if(data.tupoksi) this.options = data.tupoksi.map(t => ({id: t.id, text: t.uraian_tugas}));
                                    if(!this.options.length) this.label = 'Data Tupoksi Kosong';
                                } catch(e) { this.label = 'Gagal memuat data'; } 
                                finally { this.loading = false; }
                            },
                            select(opt) { this.value = opt.id; this.label = opt.text; this.open = false; }
                        }" x-init="init()">
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Referensi Tupoksi</label>
                    <input type="hidden" name="tupoksi_id" x-model="value">

                    <div class="relative">
                        <button type="button" @click="open = !open" @click.outside="open = false"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm pr-3 text-left flex items-center justify-between focus:ring-2 focus:ring-[#1C7C54]/30"
                            :class="!value ? 'text-slate-400' : 'text-slate-700'">
                            <span x-text="loading ? 'Memuat...' : label" class="truncate mr-2"></span>
                            <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                                class="h-4 w-4 opacity-70 flex-shrink-0" alt="">
                        </button>

                        <div x-show="open" x-transition
                            class="absolute z-20 mt-1 w-full rounded-[10px] bg-white shadow-lg border border-slate-200 py-1 max-h-60 overflow-y-auto">
                            <template x-for="opt in options" :key="opt.id">
                                <button type="button"
                                    class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50 flex justify-between gap-2"
                                    :class="value === opt.id ? 'text-[#1C7C54] font-medium' : 'text-slate-700'"
                                    @click="select(opt)">
                                    <span x-text="opt.text" class="line-clamp-2"></span>
                                    <span x-show="value === opt.id" class="shrink-0">✓</span>
                                </button>
                            </template>
                            <div x-show="!options.length && !loading" class="px-3 text-sm text-slate-400 italic py-2">
                                Data kosong</div>
                        </div>
                    </div>
                </div>

                {{-- Row 3: Uraian Kegiatan --}}
                <div>
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Uraian Kegiatan</label>
                    <textarea name="deskripsi_aktivitas" rows="3"
                        class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                        placeholder="Tulis uraian kegiatan yang dilakukan..."></textarea>
                </div>

                {{-- Logic Kategori SKP/Non-SKP + Satuan & Volume --}}
                <div x-data="{
                            kategori: 'non-skp',
                            skpId: '',
                            skpLabel: 'Pilih Target SKP',
                            skpOptions: [],
                            skpLoading: false,
                            satuanValue: '',
                            satuanOpen: false,
                            kategoriOpen: false,
                            skpOpen: false,
                            isSatuanLocked: false,

                            async fetchSkp() {
                                this.skpLoading = true;
                                try {
                                    const res = await fetch('/api/lkh/referensi', {
                                        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}`, 'Accept': 'application/json' }
                                    });
                                    const data = await res.json();
                                    if(data.list_skp) {
                                        this.skpOptions = data.list_skp.map(s => ({
                                            value: s.id,
                                            label: s.rencana_hasil_kerja,
                                            satuan: s.satuan ?? '-',
                                            target: s.target_qty ?? 0
                                        }));
                                    }
                                } catch(e) { console.error(e); } finally { this.skpLoading = false; }
                            },
                            pilihSkp(opt) {
                                this.skpId = opt.value;
                                this.skpLabel = opt.label;
                                this.skpOpen = false;
                                if(opt.satuan && opt.satuan !== '-') {
                                    this.satuanValue = opt.satuan;
                                    this.isSatuanLocked = true;
                                } else {
                                    this.isSatuanLocked = false;
                                }
                            },
                            setKategori(val) {
                                this.kategori = val;
                                this.kategoriOpen = false;
                                if(val === 'skp') {
                                    if(!this.skpOptions.length) this.fetchSkp();
                                } else {
                                    this.skpId = '';
                                    this.skpLabel = 'Pilih Target SKP';
                                    this.satuanValue = '';
                                    this.isSatuanLocked = false;
                                }
                            }
                        }">
                    {{-- Row 4: Output & Kategori --}}
                    <div class="grid md:grid-cols-[2fr_1fr] gap-4">
                        <div>
                            <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Output</label>
                            <input type="text" name="output_hasil_kerja"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                                placeholder="Contoh: Notulensi">
                        </div>
                        <div class="relative">
                            <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Kategori</label>
                            <input type="hidden" name="kategori" x-model="kategori">
                            <button type="button" @click="kategoriOpen = !kategoriOpen"
                                @click.outside="kategoriOpen = false"
                                class="w-full flex items-center justify-between rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#1C7C54]/30">
                                <span x-text="kategori === 'skp' ? 'SKP' : 'Non-SKP'"
                                    :class="kategori === 'skp' ? 'text-[#1C7C54] font-medium' : 'text-slate-700'"></span>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70 ml-2">
                            </button>
                            <div x-show="kategoriOpen" x-transition
                                class="absolute z-20 mt-1 w-full rounded-[10px] bg-white shadow-lg border border-slate-200 overflow-hidden">
                                <button type="button" @click="setKategori('skp')"
                                    class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50">SKP</button>
                                <button type="button" @click="setKategori('non-skp')"
                                    class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50">Non-SKP</button>
                            </div>
                        </div>
                    </div>

                    {{-- Row 5: List SKP --}}
                    <div x-show="kategori === 'skp'" x-transition class="mt-4">
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Target SKP</label>
                        <input type="hidden" name="skp_rencana_id" x-model="skpId">
                        <div class="relative">
                            <button type="button" @click="skpOpen = !skpOpen" @click.outside="skpOpen = false"
                                class="w-full flex items-center justify-between rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm text-left focus:ring-2 focus:ring-[#1C7C54]/30">
                                <span x-text="skpLoading ? 'Memuat data...' : skpLabel"
                                    class="truncate text-slate-700"></span>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70 ml-2">
                            </button>
                            <div x-show="skpOpen" x-transition
                                class="absolute z-20 mt-1 w-full rounded-[10px] bg-white shadow-lg border border-slate-200 max-h-60 overflow-y-auto">
                                <template x-for="opt in skpOptions" :key="opt.value">
                                    <button type="button" @click="pilihSkp(opt)"
                                        class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50 border-b border-slate-100">
                                        <span x-text="opt.label" class="line-clamp-2"></span>
                                        <span x-show="opt.satuan" x-text="'Target: ' + opt.satuan"
                                            class="text-[10px] text-[#1C7C54] block mt-0.5"></span>
                                    </button>
                                </template>
                                <div x-show="!skpOptions.length && !skpLoading"
                                    class="p-2 text-xs text-slate-400 text-center">Tidak ada data SKP.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 6: Satuan & Volume --}}
                    <div class="grid md:grid-cols-2 gap-4 mt-4">
                        <div class="relative">
                            <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Satuan</label>
                            <input type="hidden" name="satuan" x-model="satuanValue">
                            <div x-show="isSatuanLocked"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-500 cursor-not-allowed flex justify-between items-center">
                                <span x-text="satuanValue"></span>
                                <img src="{{ asset('assets/icon/lock.svg') }}" class="h-3.5 w-3.5 opacity-50">
                            </div>
                            <div x-show="!isSatuanLocked">
                                <button type="button" @click="satuanOpen = !satuanOpen"
                                    @click.outside="satuanOpen = false"
                                    class="w-full flex justify-between items-center rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm text-left focus:ring-2 focus:ring-[#1C7C54]/30">
                                    <span x-text="satuanValue || 'Pilih Satuan'"
                                        :class="!satuanValue ? 'text-slate-400' : 'text-slate-700'"></span>
                                    <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70">
                                </button>
                                <div x-show="satuanOpen" x-transition
                                    class="absolute z-20 mt-1 w-full rounded-[10px] bg-white shadow-lg border border-slate-200">
                                    <template x-for="opt in ['Jam', 'Dokumen', 'Kegiatan', 'Laporan', 'Berkas']">
                                        <button type="button" @click="satuanValue = opt; satuanOpen = false"
                                            class="w-full px-3.5 py-2 text-sm text-left hover:bg-slate-50">
                                            <span x-text="opt"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Volume</label>
                            <input type="number" name="volume" min="0"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                                placeholder="0">
                        </div>
                    </div>
                </div>

                {{-- Row 7: Waktu --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Jam Mulai</label>
                        <div class="relative">
                            <input id="jam_mulai" name="waktu_mulai" type="time"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                            <button type="button" id="jam_mulai_btn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center"><img
                                    src="{{ asset('assets/icon/time.svg') }}" class="h-4 w-4 opacity-70"></button>
                        </div>
                    </div>
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Jam Selesai</label>
                        <div class="relative">
                            <input id="jam_selesai" name="waktu_selesai" type="time"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                            <button type="button" id="jam_selesai_btn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center"><img
                                    src="{{ asset('assets/icon/time.svg') }}" class="h-4 w-4 opacity-70"></button>
                        </div>
                    </div>
                </div>
                {{-- Row 8: Bukti & Lokasi Modern --}}
                <div class="grid md:grid-cols-2 gap-4">
                    {{-- Upload Bukti --}}
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Unggah Bukti</label>
                        <label
                            class="w-full flex items-center justify-between rounded-[10px] border border-dashed border-slate-300 bg-slate-50/60 px-3.5 py-2.5 text-sm text-slate-500 cursor-pointer hover:bg-slate-100">
                            <span id="bukti_filename" class="truncate">Pilih File</span>
                            <img src="{{ asset('assets/icon/upload.svg') }}" class="h-4 w-4 opacity-70">
                            <input type="file" id="bukti_input" name="bukti[]" multiple class="hidden">
                        </label>
                    </div>

                    {{-- Modul Input Lokasi Dual Mode --}}
                    <div x-data="{
                            mode: 'geofence', // geofence or geocoding
                            status: 'Klik tombol untuk ambil lokasi',
                            lat: '',
                            lng: '',
                            loading: false,
                            searchText: '',
                            searchResults: [],
                            showResults: false,
                            lokasiTeksFinal: '',

                            init() {
                                // Sinkronisasi mode ke input hidden utama
                                this.$watch('mode', value => {
                                    document.getElementById('mode_lokasi_input').value = value;
                                    // Reset nilai saat ganti mode
                                    if(value === 'geofence') {
                                        this.searchText = '';
                                        this.lokasiTeksFinal = '';
                                        this.status = 'Klik tombol untuk ambil lokasi';
                                    } else {
                                        this.status = 'Cari lokasi pada kolom input';
                                    }
                                    this.lat = '';
                                    this.lng = '';
                                });
                            },

                            // Logic Geofence (GPS)
                            getGPS() {
                                this.loading = true; this.status = 'Mencari koordinat GPS...';
                                if(navigator.geolocation) {
                                    navigator.geolocation.getCurrentPosition(
                                        (pos) => { 
                                            this.lat = pos.coords.latitude; 
                                            this.lng = pos.coords.longitude; 
                                            this.status = `Terkunci: ${this.lat.toFixed(5)}, ${this.lng.toFixed(5)}`; 
                                            this.loading = false; 
                                        },
                                        () => { this.status = 'Gagal mengambil GPS.'; this.loading = false; }
                                    );
                                } else { this.status = 'Browser tidak mendukung GPS.'; this.loading = false; }
                            },

                            // Logic Geocoding (Nominatim API)
                            async searchLocation() {
                                if(this.searchText.length < 3) return;
                                this.loading = true;
                                try {
                                    // Menggunakan OpenStreetMap Nominatim (Gratis, No Key)
                                    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchText)}&limit=5`;
                                    const res = await fetch(url);
                                    const data = await res.json();
                                    this.searchResults = data;
                                    this.showResults = true;
                                } catch(e) {
                                    console.error(e);
                                    this.status = 'Gagal mencari lokasi';
                                } finally {
                                    this.loading = false;
                                }
                            },

                            selectLocation(item) {
                                this.lat = item.lat;
                                this.lng = item.lon;
                                this.lokasiTeksFinal = item.display_name;
                                this.searchText = item.display_name; // Tampilkan nama di input
                                this.showResults = false;
                                this.status = `Dipilih: ${item.display_name.substring(0, 30)}...`;
                            }
                        }">
                        
                        {{-- Header Label + Switcher Mode --}}
                        <div class="flex items-center justify-between mb-[10px]">
                            <label class="block font-normal text-[15px] text-[#5B687A]">Lokasi</label>
                            
                            {{-- Switcher --}}
                            <div class="flex bg-slate-100 p-0.5 rounded-lg border border-slate-200">
                                <button type="button" @click="mode = 'geofence'"
                                    class="px-2 py-1 text-[10px] font-medium rounded-md transition-all"
                                    :class="mode === 'geofence' ? 'bg-white text-[#1C7C54] shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                                    GPS (Otomatis)
                                </button>
                                <button type="button" @click="mode = 'geocoding'"
                                    class="px-2 py-1 text-[10px] font-medium rounded-md transition-all"
                                    :class="mode === 'geocoding' ? 'bg-white text-[#1C7C54] shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                                    Cari Peta
                                </button>
                            </div>
                        </div>

                        {{-- Hidden Inputs untuk Form Submission --}}
                        <input type="hidden" name="latitude" x-model="lat">
                        <input type="hidden" name="longitude" x-model="lng">
                        <input type="hidden" name="lokasi_teks" x-model="lokasiTeksFinal">

                        {{-- Tampilan Mode Geofence --}}
                        <div x-show="mode === 'geofence'" class="flex gap-2">
                            <input type="text"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-600 focus:outline-none cursor-not-allowed"
                                x-model="status" readonly>
                            <button type="button" @click="getGPS()" :disabled="loading"
                                class="shrink-0 bg-[#1C7C54] hover:bg-[#156a44] text-white rounded-[10px] w-10 flex items-center justify-center transition-colors"
                                :class="loading ? 'opacity-70 cursor-wait' : ''">
                                <template x-if="!loading">
                                    <img src="{{ asset('assets/icon/location.svg') }}" class="h-5 w-5 brightness-0 invert">
                                </template>
                                <template x-if="loading">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </template>
                            </button>
                        </div>

                        {{-- Tampilan Mode Geocoding (Search) --}}
                        <div x-show="mode === 'geocoding'" class="relative">
                            <div class="relative">
                                <input type="text" x-model="searchText"
                                    @keydown.enter.prevent="searchLocation()"
                                    class="w-full rounded-[10px] border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#155FA6]/30 focus:border-[#155FA6]"
                                    placeholder="Ketik nama lokasi (misal: Hotel Horison)...">
                                
                                <button type="button" @click="searchLocation()"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-slate-100 hover:bg-slate-200 p-1.5 rounded-md text-slate-500 transition-colors">
                                    <img src="{{ asset('assets/icon/search.svg') }}" class="h-4 w-4 opacity-60">
                                </button>
                            </div>

                            {{-- Dropdown Hasil Pencarian --}}
                            <div x-show="showResults && searchResults.length" @click.outside="showResults = false"
                                class="search-results absolute z-30 mt-1 w-full bg-white rounded-[10px] shadow-xl border border-slate-200 max-h-60 overflow-y-auto">
                                <template x-for="item in searchResults" :key="item.place_id">
                                    <button type="button" @click="selectLocation(item)"
                                        class="w-full text-left px-4 py-3 text-xs hover:bg-slate-50 border-b border-slate-100 last:border-0 transition-colors">
                                        <div class="font-medium text-slate-800" x-text="item.display_name.split(',')[0]"></div>
                                        <div class="text-slate-500 truncate mt-0.5" x-text="item.display_name"></div>
                                    </button>
                                </template>
                            </div>
                            
                            {{-- Pesan Status Search --}}
                            <div x-show="loading" class="absolute right-10 top-3 text-xs text-slate-400">Mencari...</div>
                        </div>

                        <p class="text-[11px] text-slate-400 mt-1">
                            <span x-show="mode === 'geofence'">*Pastikan GPS aktif.</span>
                            <span x-show="mode === 'geocoding'">*Gunakan pencarian untuk lokasi spesifik.</span>
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="exportPDF()"
                        class="rounded-[10px] bg-[#6B7280] px-4 py-2 text-sm text-white hover:bg-[#555]">Export
                        PDF</button>
                    <button type="button" onclick="submitForm('draft')"
                        class="rounded-[10px] bg-[#155FA6] px-4 py-2 text-sm text-white hover:bg-[#104d87]">Simpan Draft</button>
                    <button type="button" onclick="submitForm('waiting_review')"
                        class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm text-white hover:bg-[#0b633b]">Kirim
                        LKH</button>
                </div>
            </div>
        </form>
    </div>

    {{-- KANAN ATAS: PANDUAN SINGKAT --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col h-full">
        <h3 class="text-sm font-semibold text-slate-800">Panduan Singkat</h3>

        <div class="mt-3 space-y-2 flex-1 overflow-y-auto pr-1">
            @foreach ([
                ['title' => 'Tanggal', 'desc' => 'Pilih tanggal kegiatan dilakukan.'],
                ['title' => 'Jenis Kegiatan', 'desc' => 'Pilih jenis kegiatan yang dilakukan.'],
                ['title' => 'Lokasi (Baru)', 'desc' => 'Gunakan "Cari Peta" jika lokasi Anda berbeda dengan posisi GPS saat ini.'],
                ['title' => 'Uraian Kegiatan', 'desc' => 'Isi dengan kalimat yang ringkas dan jelas.'],
                ['title' => 'Kategori', 'desc' => 'Pilih kategori SKP jika kegiatan terkait target kinerja.'],
                ['title' => 'Unggah Bukti', 'desc' => 'Wajib lampirkan foto kegiatan.'],
            ] as $guide)
            <div class="rounded-[10px] bg-[#155FA6] px-3 py-2.5 text-white text-xs leading-snug">
                <p class="text-[13px] font-semibold">{{ $guide['title'] }}</p>
                <p class="mt-[2px] text-[11px] text-white/90">{{ $guide['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- KIRI BAWAH: DRAFT LKH (Tidak berubah banyak) --}}
    <div x-data="{ openDraftModal: false, draftsLimit: [], draftsAll: [] }"
        @update-drafts.window="draftsLimit = $event.detail.limit; draftsAll = $event.detail.all;" x-cloak
        class="rounded-2xl bg-white ring-1 ring-slate-200 px-4 py-3 shadow-sm h-full flex flex-col">

        <div class="flex items-center justify-between mb-3 shrink-0">
            <h3 class="text-[15px] font-medium text-slate-800">Draft LKH</h3>
            <button type="button" x-show="draftsAll.length > 0"
                class="text-[11px] text-[#0E7A4A] font-medium hover:underline" @click="openDraftModal = true">
                Lihat Semua (<span x-text="draftsAll.length"></span>)
            </button>
        </div>

        <div class="space-y-3 flex-1 overflow-y-auto pr-1">
            <template x-if="draftsLimit.length === 0">
                <p class="text-sm text-slate-400 italic">Tidak ada draft.</p>
            </template>
            <template x-for="item in draftsLimit" :key="item.id">
                <div class="bg-[#F8F9FA] rounded-[12px] p-4 flex items-center justify-between gap-3 border border-slate-100">
                    <div class="flex-1 min-w-0">
                        <h4 class="text-[12px] font-medium text-slate-900 truncate" x-text="item.deskripsi"></h4>
                        <p class="text-[10px] text-slate-500 mt-1" x-text="item.waktu_simpan"></p>
                    </div>
                    <a :href="'/staf/input-lkh/' + item.id" 
                        class="bg-[#0E7A4A] text-white text-[12px] px-3 py-1.5 rounded-[8px]">
                        Lanjutkan
                    </a>
                    <button @click="deleteDraft(item.id)"
                        class="bg-[#B6241C] text-white text-[12px] px-3 py-1.5 rounded-[8px]">
                        Hapus
                    </button>
                </div>
            </template>
        </div>

        {{-- Modal Draft --}}
        <div x-show="openDraftModal" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openDraftModal = false"></div>
            <div class="relative z-10 w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[85vh]">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h2 class="text-lg font-semibold text-slate-800">Semua Draft</h2>
                    <button @click="openDraftModal = false" class="text-2xl text-slate-500">&times;</button>
                </div>
                <div class="overflow-y-auto p-6 space-y-3">
                    <template x-for="item in draftsAll" :key="item.id">
                        <div class="bg-[#F8F9FA] rounded-[12px] p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border border-slate-100">
                            <div>
                                <h4 class="text-[12px] font-medium" x-text="item.deskripsi"></h4>
                                <p class="text-[10px] text-slate-500" x-text="item.waktu_simpan"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a :href="'/staf/input-lkh/' + item.id"
                                    class="bg-[#0E7A4A] text-white text-[12px] px-2 py-1 rounded-[8px]">
                                    Lanjutkan
                                </a>
                                <button @click="deleteDraft(item.id)"
                                    class="bg-[#B6241C] text-white text-[12px] px-2 py-1 rounded-[8px]">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- KANAN BAWAH: STATUS --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 shadow-sm">
        <h3 class="text-[18px] font-medium text-slate-800 mb-5">Status Laporan</h3>
        <ul class="space-y-3" id="aktivitas-list">
            <li class="text-sm text-slate-400 italic">Memuat...</li>
        </ul>
    </div>
</section>

@push('scripts')
<script>
function updateAlpineDropdown(inputName, value, label = null) {
    const el = document.querySelector(`input[name="${inputName}"]`);
    if (el) {
        const scope = Alpine.$data(el.closest('[x-data]'));
        scope.value = value;
        scope.label = label || value;
    }
}

function setAlpineValue(selector, key, value) {
    const el = document.querySelector(selector);
    if (el && el.closest("[x-data]")) {
        Alpine.$data(el.closest("[x-data]"))[key] = value;
    }
}

const lkhIdToEdit = "{{ $id ?? '' }}";

document.addEventListener("DOMContentLoaded", async function() {
    const token = localStorage.getItem("auth_token");
    const headers = {
        "Accept": "application/json",
        "Authorization": "Bearer " + token
    };

    // File preview
    const fileInput = document.getElementById("bukti_input");
    if (fileInput) {
        fileInput.addEventListener("change", () => {
            const count = fileInput.files.length;
            document.getElementById("bukti_filename").textContent =
                count === 0 ? "Pilih File" :
                count === 1 ? fileInput.files[0].name :
                `${count} file dipilih`;
        });
    }

    // Load dashboard stats
    try {
        const res = await fetch("/api/dashboard/stats", { headers });
        if (res.ok) {
            const data = await res.json();
            renderAktivitas(data.aktivitas_terbaru || []);
            renderDrafts(data.draft_terbaru || []);
        }
    } catch (e) { console.error("Stats Error", e); }

    // Edit mode logic
    if (lkhIdToEdit) loadEditLKH(lkhIdToEdit, headers);

    // Date/Time pickers trigger
    ["tanggal_lkh", "jam_mulai", "jam_selesai"].forEach(id => {
        document.getElementById(id + "_btn")?.addEventListener("click", () =>
            document.getElementById(id).showPicker()
        );
    });
});

function renderAktivitas(list) {
    const el = document.getElementById("aktivitas-list");
    el.innerHTML = "";
    if (!list.length) {
        el.innerHTML = `<li class="text-sm text-slate-500">Belum ada aktivitas.</li>`;
        return;
    }
    list.forEach(item => {
        const color = item.status === "approved" ? "bg-[#128C60]/50" : item.status.includes("reject") ? "bg-[#B6241C]/50" : "bg-slate-200";
        const icon = item.status === "approved" ? "{{ asset('assets/icon/approve.svg') }}" : "{{ asset('assets/icon/pending.svg') }}";
        const text = item.status === "approved" ? "Disetujui" : item.status.includes("reject") ? "Ditolak" : "Menunggu";
        el.insertAdjacentHTML("beforeend", `
            <li class="flex items-start gap-3">
                <div class="h-8 w-8 rounded-[10px] flex items-center justify-center ${color}">
                    <img src="${icon}" class="h-5 w-5 opacity-90">
                </div>
                <div class="flex-1 overflow-hidden">
                    <div class="text-[13px] font-medium truncate">${item.deskripsi_aktivitas}</div>
                    <div class="flex justify-between mt-0.5 text-xs text-slate-500">
                        <span>${text}</span>
                        <span>${new Date(item.tanggal_laporan).toLocaleDateString("id-ID")}</span>
                    </div>
                </div>
            </li>
        `);
    });
}

function renderDrafts(data) {
    const drafts = data.map(d => ({
        id: d.id,
        deskripsi: d.deskripsi_aktivitas || "Draft",
        waktu_simpan: new Date(d.updated_at).toLocaleString()
    }));
    window.dispatchEvent(new CustomEvent("update-drafts", { detail: { limit: drafts.slice(0, 3), all: drafts } }));
}

async function loadEditLKH(id, headers) {
    try {
        const res = await fetch(`/api/lkh/${id}`, { headers });
        const json = await res.json();
        const data = json.data;

        // 1. Populate Standard Inputs
        // Fix: Format tanggal dari ISO (YYYY-MM-DDTHH:mm:ss...) ke YYYY-MM-DD
        if (data.tanggal_laporan) {
            document.getElementById("tanggal_lkh").value = data.tanggal_laporan.split('T')[0];
        }
        
        document.getElementById("jam_mulai").value = data.waktu_mulai;
        document.getElementById("jam_selesai").value = data.waktu_selesai;
        
        // Deskripsi & Output
        const deskripsiEl = document.querySelector('textarea[name="deskripsi_aktivitas"]');
        if(deskripsiEl) deskripsiEl.value = data.deskripsi_aktivitas ?? "";

        const outputEl = document.querySelector('input[name="output_hasil_kerja"]');
        if(outputEl) outputEl.value = data.output_hasil_kerja ?? "";
        
        // Volume
        const volumeEl = document.querySelector('input[name="volume"]');
        if(volumeEl) volumeEl.value = data.volume ?? "";

        // Satuan (Input hidden atau text biasa jika tidak pakai Alpine)
        const satuanEl = document.querySelector('input[name="satuan"]');
        if(satuanEl) satuanEl.value = data.satuan ?? "";

        updateAlpineDropdown('jenis_kegiatan', data.jenis_kegiatan);
        updateAlpineDropdown('tupoksi_id', data.tupoksi_id, data.tupoksi.uraian_tugas || 'Tupoksi Terpilih');

        // 2. Populate Mode Lokasi & Koordinat
        const modeLokasi = data.mode_lokasi || 'geofence';
        const lokasiContainer = document.querySelector('[x-data*="mode:"]');
        
        if(lokasiContainer && lokasiContainer.__x) {
            const alpine = lokasiContainer.__x.$data;
            alpine.mode = modeLokasi;
            alpine.lat = data.lat; 
            alpine.lng = data.lng;
            alpine.lokasiTeksFinal = data.lokasi_teks || '';
            
            if(modeLokasi === 'geocoding') {
                alpine.searchText = data.lokasi_teks || '';
                alpine.status = `Tersimpan: ${data.lokasi_teks}`;
            } else {
                // Tampilkan koordinat jika geofence/manual map
                alpine.status = `Terkunci: ${data.lat}, ${data.lng}`;
            }
        }

        // 3. Logic Kategori & SKP
        const isSkp = !!data.skp_rencana_id; // Cek apakah ada ID SKP
        const kategoriInput = document.querySelector('input[name="kategori"]');
        if(kategoriInput) kategoriInput.value = isSkp ? "skp" : "non-skp";
        
        // Trigger Alpine update untuk Kategori
        const mainLogicDiv = document.querySelector('[x-data*="kategori:"]');
        if(mainLogicDiv && mainLogicDiv.__x) {
            const alpineData = mainLogicDiv.__x.$data;
            
            // Set state kategori di Alpine
            alpineData.setKategori(isSkp ? "skp" : "non-skp");

            setTimeout(() => {
                // Populate data SKP jika kategori adalah SKP
                if (isSkp && data.rencana) {
                    alpineData.skpId = data.skp_rencana_id;
                    alpineData.skpLabel = data.rencana.rencana_hasil_kerja;
                    
                    // PERBAIKAN: Ambil satuan langsung dari root data.satuan
                    // karena di JSON tidak ada data.rencana.targets
                    alpineData.satuanValue = data.satuan; 
                    alpineData.isSatuanLocked = true; // Biasanya SKP satuannya dikunci
                } else {
                    // Jika Non-SKP
                    alpineData.satuanValue = data.satuan;
                    alpineData.isSatuanLocked = false;
                }
            }, 300); // Sedikit delay agar transisi Alpine selesai
        }

    } catch (e) { 
        console.error("Edit Load Error", e);
        alert("Gagal memuat data LKH.");
    }
}

async function submitForm(type) {
    const form = document.getElementById("form-lkh");
    const formData = new FormData(form);
    formData.set("status", type);

    // Validasi Sederhana
    if (type === "waiting_review") {
        if (!formData.get("output_hasil_kerja") || !formData.get("satuan")) {
            return Swal.fire({ icon: "warning", title: "Belum Lengkap", text: "Output dan Satuan wajib diisi" });
        }
        // Validasi Lokasi
        if (!formData.get("latitude") || !formData.get("longitude")) {
            return Swal.fire({ icon: "warning", title: "Lokasi Kosong", text: "Mohon ambil lokasi GPS atau cari lokasi di peta." });
        }
    }

    try {
        const url = lkhIdToEdit ? `/api/lkh/update/${lkhIdToEdit}` : "/api/lkh";
        const res = await fetch(url, {
            method: "POST",
            headers: { "Authorization": `Bearer ${localStorage.getItem("auth_token")}`, "Accept": "application/json" },
            body: formData
        });
        const json = await res.json();

        if (res.ok) {
            Swal.fire({ icon: "success", title: "Berhasil", showConfirmButton: false, timer: 1500 });
            setTimeout(() => window.location.href = "/staf/dashboard", 1000);
        } else {
            throw new Error(json.message || "Gagal menyimpan data");
        }
    } catch (e) {
        Swal.fire({ icon: "error", title: "Gagal", text: e.message });
    }
}

async function exportPDF() {
    const res = await Swal.fire({
        title: "Export PDF?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya",
        confirmButtonColor: "#1C7C54"
    });

    if (!res.isConfirmed) return;

    try {
        const resp = await fetch("/api/lkh/export-pdf", {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("auth_token")}`
            },
            body: new FormData(document.getElementById("form-lkh"))
        });

        if (resp.ok) {
            const blob = await resp.blob();
            window.open(URL.createObjectURL(blob), "_blank");
        } else {
            throw new Error("Gagal export");
        }
    } catch (e) {
        Swal.fire("Error", "Gagal export PDF", "error");
    }
}
</script>
@endpush

@endsection