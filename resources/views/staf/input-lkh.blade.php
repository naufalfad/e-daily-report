@php($title = 'Input LKH')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'input-lkh'])

@section('content')

{{-- Style Tambahan untuk Hasil Pencarian Peta --}}
{{-- Style Tambahan untuk Map & Animasi --}}
<style>
    .search-results::-webkit-scrollbar { width: 6px; }
    .search-results::-webkit-scrollbar-track { background: #f1f1f1; }
    .search-results::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    .search-results::-webkit-scrollbar-thumb:hover { background: #aaa; }

    /* Animasi Pin Melompat */
    @keyframes pinJump {
        0% { transform: translate(-50%, -50%) translateY(0); }
        50% { transform: translate(-50%, -50%) translateY(-15px); }
        100% { transform: translate(-50%, -50%) translateY(-15px); }
    }
    @keyframes pinLand {
        0% { transform: translate(-50%, -50%) translateY(-15px); }
        100% { transform: translate(-50%, -50%) translateY(0); }
    }
    
    /* Animasi Bayangan */
    @keyframes shadowShrink {
        0% { transform: scale(1); opacity: 0.4; }
        100% { transform: scale(0.6); opacity: 0.2; }
    }
    @keyframes shadowGrow {
        0% { transform: scale(0.6); opacity: 0.2; }
        80% { transform: scale(1.2); opacity: 0.5; }
        100% { transform: scale(1); opacity: 0.4; }
    }

    /* Utilitas Animasi */
    .animate-pin-jump { animation: pinJump 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    .animate-pin-land { animation: pinLand 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    .animate-shadow-shrink { animation: shadowShrink 0.2s forwards; }
    .animate-shadow-grow { animation: shadowGrow 0.2s forwards; }
    
    /* Animasi Panel Riwayat */
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide-up { animation: slideUpFade 0.3s ease-out forwards; }
</style>

{{-- GRID UTAMA --}}
<section class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)] gap-4 lg:auto-rows-min">

    {{-- KIRI ATAS: FORM INPUT LKH --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
        <h2 class="text-[20px] font-normal mb-4">Form Input LKH</h2>

        <form id="form-lkh" method="POST" enctype="multipart/form-data">
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
                            {{-- PERUBAHAN: Tambahkan lang="en-GB" untuk memaksa format 24 jam di browser --}}
                            <input id="jam_mulai" name="waktu_mulai" type="time" step="60" lang="en-GB"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                            <button type="button" id="jam_mulai_btn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/time.svg') }}" class="h-4 w-4 opacity-70">
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Jam Selesai</label>
                        <div class="relative">
                            {{-- PERUBAHAN: Tambahkan lang="en-GB" dan step="60" (untuk menghilangkan detik) --}}
                            <input id="jam_selesai" name="waktu_selesai" type="time" step="60" lang="en-GB"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                            <button type="button" id="jam_selesai_btn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/time.svg') }}" class="h-4 w-4 opacity-70">
                            </button>
                        </div>
                    </div>
                </div>
                {{-- Row 8: Bukti & Lokasi Modern --}}
                <div class="grid md:grid-cols-2 gap-4">
                    {{-- Unggah Bukti --}}
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Unggah Bukti</label>

                        {{-- Input File Utama --}}
                        <label
                            class="w-full flex items-center justify-between rounded-[10px] border border-dashed border-slate-300 bg-slate-50/60 px-3.5 py-2.5 text-sm text-slate-500 cursor-pointer hover:bg-slate-100 transition-colors">
                            <span id="bukti_label_text" class="truncate">Pilih File Baru</span>
                            <img src="{{ asset('assets/icon/upload.svg') }}" class="h-4 w-4 opacity-70">
                            <input type="file" id="bukti_input" name="bukti[]" multiple class="hidden"
                                onchange="handleNewFiles(this)">
                        </label>

                        <div id="preview_file_baru" class="mt-2 space-y-2"></div>

                        {{-- Container untuk Menampung Input Hidden ID File yang akan Dihapus --}}
                        <div id="container_hapus_bukti"></div>

                        {{-- Container Preview File Lama (Akan diisi via JS saat Edit) --}}
                        <div id="preview_file_lama" class="mt-3 space-y-2">
                        </div>
                    </div>

                    {{-- Modul Input Lokasi (UPDATED: Fullscreen Trigger) --}}
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Lokasi Kegiatan</label>

                        {{-- Hidden Inputs Data --}}
                        <input type="hidden" name="latitude" id="input_lat">
                        <input type="hidden" name="longitude" id="input_lng">
                        <input type="hidden" name="lokasi_teks" id="input_lokasi_teks">
                        <input type="hidden" name="address_auto" id="input_address_auto"> {{-- NEW: Hasil Reverse Geocoding --}}
                        <input type="hidden" name="location_provider" id="input_provider" value="manual_pin">

                        <div class="flex gap-2">
                            {{-- Preview Lokasi (Readonly) --}}
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <img src="{{ asset('assets/icon/location.svg') }}" class="w-4 h-4 opacity-50">
                                </div>
                                <input type="text" id="preview_lokasi" readonly
                                    class="w-full rounded-[10px] border border-slate-200 bg-slate-50 pl-10 pr-3.5 py-2.5 text-sm text-slate-600 focus:outline-none cursor-not-allowed truncate"
                                    placeholder="Belum ada lokasi dipilih...">
                            </div>

                            {{-- Tombol Trigger Fullscreen --}}
                            <button type="button" id="btnOpenMap"
                                class="shrink-0 bg-[#155FA6] hover:bg-[#104d87] text-white px-2 py-2.5 rounded-[10px] text-sm flex items-center transition-colors shadow-sm">
                                <span class="hidden md:inline">📍 Buka Peta</span>
                            </button>
                        </div>  
                        <p class="text-[11px] text-slate-400 mt-1 leading-snug">
                            *Gunakan tombol <strong class="text-slate-600">Gedung (🏢)</strong> di dalam peta untuk pencarian cepat berdasarkan <strong>Distrik & Kampung</strong>.
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="exportPDF(this)"
                        class="btn-action rounded-[10px] bg-[#6B7280] px-4 py-2 text-sm text-white hover:bg-[#555] disabled:opacity-50 disabled:cursor-not-allowed">
                        Export PDF
                    </button>

                    <button type="button" onclick="submitForm('draft', this)"
                        class="btn-action rounded-[10px] bg-[#155FA6] px-4 py-2 text-sm text-white hover:bg-[#104d87] disabled:opacity-50 disabled:cursor-not-allowed">
                        Simpan Draft
                    </button>

                    <button type="button" onclick="submitForm('waiting_review', this)"
                        class="btn-action rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm text-white hover:bg-[#0b633b] disabled:opacity-50 disabled:cursor-not-allowed">
                        Kirim LKH
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- KANAN ATAS: PANDUAN SINGKAT (REVISI DETAIL) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col h-full">
        <h3 class="text-lg font-semibold text-slate-800 mb-3">Panduan Pengisian Detil LKH</h3>

        <div class="mt-3 space-y-4 flex-1 overflow-y-auto pr-1">
            {{-- Panduan Umum --}}
            <div class="rounded-[10px] bg-[#155FA6] px-4 py-3 text-white leading-normal">
                <p class="text-[14px] font-bold">1. Data Waktu & Tupoksi</p>
                <ul class="mt-2 text-[12px] text-white/90 list-disc pl-4 space-y-1">
                    <li>Tanggal & Waktu: Pastikan Jam Mulai < Jam Selesai. Pengisian harus logis.</li>
                    <li>Jenis Kegiatan: Pilih kategori yang paling sesuai (Rapat, Pelayanan, dll.).</li>
                    <li>Tupoksi: Kaitkan dengan uraian tugas Anda.</li>
                    <li>Uraian Kegiatan: Tulis deskripsi yang ringkas, padat, dan jelas mengenai apa yang dikerjakan.
                    </li>
                </ul>
            </div>

            {{-- Panduan Kategori & SKP (Alur Logis) --}}
            <div class="rounded-[10px] bg-[#0E7A4A] px-4 py-3 text-white leading-normal">
                <p class="text-[14px] font-bold">2. Kategori Kinerja & Output</p>
                <ul class="mt-2 text-[12px] text-white/90 list-disc pl-4 space-y-1">
                    <li>Non-SKP: Isi Output (misalnya: *Notulensi*) dan tentukan Satuan secara manual (*Jam*,
                        *Dokumen*).</li>
                    <li>SKP: Pilih Target SKP dari daftar. Sistem akan mengunci Satuan untuk menjaga konsistensi dengan
                        target kinerja Anda.</li>
                    <li>Volume: Jumlah kuantitas Output yang dihasilkan (Wajib diisi saat Kirim LKH).</li>
                </ul>
            </div>

            {{-- Panduan Lokasi (Alur Teknis) --}}
            <div class="rounded-[10px] bg-[#B6241C] px-4 py-3 text-white leading-normal">
                <p class="text-[14px] font-bold">3. Lokasi (Geospatial) - Wajib Kirim LKH</p>
                <ul class="mt-2 text-[12px] text-white/90 list-disc pl-4 space-y-1">
                    <li>GPS (Otomatis/Geofence): Digunakan untuk kegiatan di tempat dengan GPS. Tekan tombol lokasi
                        untuk mengunci posisi.</li>
                    <li>Cari Peta (Geocoding): Digunakan untuk kegiatan yang lokasinya tidak dapat dijangkau GPS. Cari
                        nama lokasi (misal: Alamat Kantor atau kota tempat dinas), lalu pilih untuk menyimpan koordinat
                        dan nama lokasi.</li>
                    <li>Penting: Koordinat atau lokasi kerja harus terisi sebelum Kirim LKH.</li>
                </ul>
            </div>

            {{-- Panduan Bukti --}}
            <div class="rounded-[10px] bg-[#6B7280] px-4 py-3 text-white leading-normal">
                <p class="text-[14px] font-bold">4. Unggah Bukti Kegiatan</p>
                <ul class="mt-2 text-[12px] text-white/90 list-disc pl-4 space-y-1">
                    <li>Wajib lampirkan dokumen (Gambar/PDF).</li>
                    <li>Penambahan: File baru ditambahkan ke daftar.</li>
                    <li>Penghapusan: Klik 'X' pada file yang sudah tersimpan untuk menghapus lampiran tersebut.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- KIRI BAWAH: DRAFT LKH (Tidak berubah banyak) --}}
    <div x-data="{ openDraftModal: false, draftsLimit: [], draftsAll: [], deleteDraft(id) {
        // Implementasi deleteDraft untuk tombol Hapus
        Swal.fire({
            title: 'Hapus Draft?',
            text: 'Draft ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B6241C',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                // Panggil API Hapus
                fetch(`/api/lkh/delete/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Accept': 'application/json'
                    }
                }).then(res => {
                    if (res.ok) {
                        Swal.fire('Terhapus!', 'Draft berhasil dihapus.', 'success');
                        // Refresh drafts dan stats
                        fetchDashboardStats();
                    } else {
                        Swal.fire('Gagal!', 'Gagal menghapus draft.', 'error');
                    }
                });
            }
        });
    }}" @update-drafts.window="draftsLimit = $event.detail.limit; draftsAll = $event.detail.all;" x-cloak
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
                <div
                    class="bg-[#F8F9FA] rounded-[12px] p-4 flex items-center justify-between gap-3 border border-slate-100">
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
                        <div
                            class="bg-[#F8F9FA] rounded-[12px] p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border border-slate-100">
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

        {{-- MODAL FULLSCREEN MAP (FINAL UX UPDATE) --}}
        <div id="fullscreenMapModal" class="fixed inset-0 z-[9999] bg-white hidden flex-col font-sans">
            
            {{-- 1. Close Button (Floating Alone - Pojok Kiri Atas) --}}
        <div class="absolute top-4 left-4 z-[1001]">
            <button type="button" id="btnCloseMap" 
                class="h-10 w-10 bg-white rounded-lg shadow-lg flex items-center justify-center hover:bg-red-50 text-slate-500 hover:text-red-500 border border-slate-200 transition-all active:scale-95" title="Tutup Peta">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- 2. Map Container --}}
        <div id="map_fullscreen" class="w-full h-full bg-slate-100 relative z-0"></div>

        {{-- 3. Fixed Center Pin (Visual Element) --}}
        <div class="center-pin-wrapper absolute top-1/2 left-1/2 z-[1000] pointer-events-none flex flex-col items-center pb-[40px] transform -translate-x-1/2 -translate-y-1/2 transition-transform duration-100">
            {{-- Pin Merah Besar --}}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-12 h-12 text-[#DC2626] drop-shadow-2xl filter" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4));">
                <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
            </svg>
            {{-- Titik Fokus Lantai --}}
            <div class="absolute bottom-[38px] w-1 h-1 bg-black rounded-full opacity-50"></div>
            {{-- Bayangan --}}
            <div class="pin-shadow w-3 h-1.5 bg-black/40 rounded-[100%] mt-[-4px] blur-[1px]"></div>
        </div>

        {{-- 4. Floating Controls Area (Bottom) --}}
        <div class="absolute bottom-8 left-4 right-4 z-[1001] max-w-lg mx-auto w-full flex flex-col gap-3">
            
            {{-- 5. Main Info Card (BIGGER & CLEANER) --}}
            <div class="bg-white rounded-2xl shadow-2xl p-5 ring-1 ring-black/5 animate-slide-up">
                <div class="flex items-start gap-4 mb-4">
                    {{-- Icon Lokasi Besar --}}
                    <div class="mt-1 shrink-0 bg-red-50 p-2.5 rounded-full border border-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-[#DC2626]">
                            <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    
                    {{-- Teks Alamat --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Lokasi Terpilih</p>
                        
                        {{-- Alamat Utama --}}
                        <p id="mapAddressPreview" class="text-[15px] font-semibold text-slate-800 leading-snug line-clamp-2">
                            Sedang mencari lokasi...
                        </p>
                        
                        {{-- Koordinat Kecil --}}
                        <div class="flex items-center gap-2 mt-1">
                            <span class="bg-slate-50 text-slate-500 text-[10px] px-2 py-0.5 rounded font-mono border border-slate-100" id="mapCoordsPreview">
                                -
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Tombol Konfirmasi Besar --}}
                <button type="button" id="btnConfirmLocation" 
                    class="w-full bg-[#1C7C54] hover:bg-[#156343] text-white font-bold text-[15px] py-3.5 rounded-xl shadow-lg shadow-emerald-200 transition-all active:scale-[0.98] flex items-center justify-center gap-2 group">
                    <span>Pilih Lokasi Ini</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
// --- Global DataTransfer & Helper ---
let newFilesDataTransfer = new DataTransfer();

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

function handleNewFiles(inputElement) {
    const files = inputElement.files;
    // Pengecekan ukuran file terintegrasi dengan fungsi submitForm (atau bisa ditambah di sini)

    // Tambahkan file yang baru dipilih ke penampung (DataTransfer)
    for (let i = 0; i < files.length; i++) {
        newFilesDataTransfer.items.add(files[i]);
    }

    // Update input file sesungguhnya dengan data terbaru
    inputElement.files = newFilesDataTransfer.files;

    // Update Label & Render Preview
    updateNewFileUI();
}

function removeNewFile(index) {
    const dt = new DataTransfer();
    const currentFiles = newFilesDataTransfer.files;

    // Masukkan kembali semua file KECUALI index yang dihapus
    for (let i = 0; i < currentFiles.length; i++) {
        if (i !== index) {
            dt.items.add(currentFiles[i]);
        }
    }

    newFilesDataTransfer = dt;
    document.getElementById('bukti_input').files = dt.files;
    updateNewFileUI();
}

function updateNewFileUI() {
    const inputElement = document.getElementById('bukti_input');
    const labelText = document.getElementById('bukti_label_text');
    const container = document.getElementById('preview_file_baru');
    const files = inputElement.files;

    // 1. Update Label Text
    labelText.textContent = files.length === 0 ?
        "Pilih File Baru" :
        `${files.length} file baru akan diunggah`;

    // 2. Render Preview List
    container.innerHTML = '';

    if (files.length > 0) {
        // Label kecil pembeda
        const header = document.createElement('p');
        header.className = "text-[11px] text-[#155FA6] font-medium mb-1 mt-3";
        header.innerText = "Akan diunggah (Baru):";
        container.appendChild(header);
    }

    Array.from(files).forEach((file, index) => {
        const isImage = file.type.startsWith('image/');
        const fileSizeKB = (file.size / 1024).toFixed(1);

        const div = document.createElement('div');
        div.className =
            "flex items-center justify-between bg-[#F0F7FF] border border-[#155FA6]/30 rounded-[8px] p-2 shadow-sm relative group";

        // Placeholder Thumbnail
        let thumbnailHtml =
            `<div class="h-10 w-10 shrink-0 rounded bg-white flex items-center justify-center border border-blue-100 text-[9px] font-bold text-blue-500 uppercase">${file.name.split('.').pop()}</div>`;

        div.innerHTML = `
            <div class="flex items-center gap-3 overflow-hidden w-full">
                <div id="thumb-new-${index}" class="shrink-0">
                    ${thumbnailHtml}
                </div>
                
                <div class="min-w-0 flex-1">
                    <p class="text-[12px] font-medium text-slate-700 truncate">${file.name}</p>
                    <p class="text-[10px] text-slate-400">${fileSizeKB} KB <span class="text-emerald-600 ml-1">• Baru</span></p>
                </div>

                <button type="button" onclick="removeNewFile(${index})" 
                    class="h-7 w-7 flex items-center justify-center rounded-full text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;

        container.appendChild(div);

        // Jika Gambar, baca file untuk ditampilkan preview-nya
        if (isImage) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.getElementById(`thumb-new-${index}`);
                if (imgContainer) {
                    imgContainer.innerHTML = `
                        <div class="h-10 w-10 shrink-0 rounded bg-white overflow-hidden border border-blue-100">
                            <img src="${e.target.result}" class="h-full w-full object-cover">
                        </div>`;
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

// --- FUNGSI RENDER FILE LAMA ---
function renderExistingFiles(files) {
    const container = document.getElementById('preview_file_lama');
    container.innerHTML = ''; // Reset container

    if (!files || files.length === 0) return;

    container.innerHTML =
        '<p class="text-[11px] text-slate-400 mb-1">File tersimpan (Klik silang untuk menghapus):</p>';

    files.forEach(file => {
        // Cek ekstensi untuk menentukan icon (Gambar vs Dokumen)
        const ext = file.file_type ? file.file_type.toLowerCase() : 'file';
        const isImage = ['jpg', 'jpeg', 'png', 'webp'].includes(ext);

        // URL File (Sesuaikan path storage Anda)
        const fileUrl = `/storage/${file.file_path}`;

        const div = document.createElement('div');
        div.className =
            "flex items-center justify-between bg-white border border-slate-200 rounded-[8px] p-2 shadow-sm";
        div.id = `file-wrapper-${file.id}`;

        div.innerHTML = `
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="h-10 w-10 shrink-0 rounded bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-100">
                    ${isImage 
                        ? `<img src="${fileUrl}" class="h-full w-full object-cover">` 
                        : `<span class="text-[9px] font-bold text-slate-500 uppercase">${ext}</span>`
                    }
                </div>
                <div class="min-w-0">
                    <a href="${fileUrl}" target="_blank" class="text-[12px] font-medium text-slate-700 hover:text-[#155FA6] hover:underline truncate block">
                        ${file.file_name_original || 'File Tanpa Nama'}
                    </a>
                    <p class="text-[10px] text-slate-400">${(file.file_size / 1024).toFixed(1)} KB</p>
                </div>
            </div>
            <button type="button" onclick="markFileForDeletion(${file.id})" 
                class="h-7 w-7 flex items-center justify-center rounded-full text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                title="Hapus file ini">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        `;
        container.appendChild(div);
    });
}

// --- FUNGSI TANDAI HAPUS ---
function markFileForDeletion(id) {
    const inputContainer = document.getElementById('container_hapus_bukti');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'hapus_bukti[]';
    input.value = id;
    inputContainer.appendChild(input);

    const wrapper = document.getElementById(`file-wrapper-${id}`);
    if (wrapper) wrapper.remove();
}

const lkhIdToEdit = "{{ $id ?? '' }}";

document.addEventListener("DOMContentLoaded", async function() {
    // 1. Setup Auth Headers
    const token = localStorage.getItem("auth_token");
    const headers = {
        "Accept": "application/json",
        "Authorization": "Bearer " + token
    };

    // 2. Load Data Statistik Dashboard (Sidebar)
    // Mengambil data aktivitas terbaru & draft tersimpan
    fetchDashboardStats();

    // 3. Load Data Edit (Logic Mode Edit)
    // Variable 'lkhIdToEdit' di-inject dari Blade di baris sebelumnya
    if (lkhIdToEdit) {
        console.log("Mode Edit Detected for ID:", lkhIdToEdit);
        loadEditLKH(lkhIdToEdit, headers);
    }

    // 4. UX Helper: Trigger Native Date/Time Pickers via Icons
    // Memungkinkan user klik ikon kalender/jam untuk membuka picker
    ["tanggal_lkh", "jam_mulai", "jam_selesai"].forEach(id => {
        const btn = document.getElementById(id + "_btn");
        const input = document.getElementById(id);
        
        if (btn && input) {
            btn.addEventListener("click", () => {
                // Fallback untuk browser yang mendukung showPicker()
                if (typeof input.showPicker === "function") {
                    input.showPicker();
                } else {
                    input.focus(); // Fallback focus biasa
                }
            });
        }
    });

    // 5. [INTEGRASI PETA & WILAYAH]
    // Memanggil fungsi global dari map-input.js
    if (typeof window.initMapComponent === 'function') {
        window.initMapComponent();
        console.log("GIS Module: Map Component & Local Search Initialized.");
    } else {
        // Warning untuk debugging jika peta tidak muncul
        console.warn("GIS Module Error: window.initMapComponent is not defined. Pastikan script map-input.js termuat.");
    }
});

// --- FETCH DASHBOARD STATS ---
async function fetchDashboardStats() {
    const token = localStorage.getItem("auth_token");
    const headers = {
        "Accept": "application/json",
        "Authorization": "Bearer " + token
    };
    try {
        const res = await fetch("/api/dashboard/stats", {
            headers
        });
        if (res.ok) {
            const data = await res.json();
            renderAktivitas(data.aktivitas_terbaru || []);
            renderDrafts(data.draft_terbaru || []);
        }
    } catch (e) {
        console.error("Stats Error", e);
    }
}

function renderAktivitas(list) {
    const el = document.getElementById("aktivitas-list");
    el.innerHTML = "";
    if (!list.length) {
        el.innerHTML = `<li class="text-sm text-slate-500">Belum ada aktivitas.</li>`;
        return;
    }
    list.forEach(item => {
        const color = item.status === "approved" ? "bg-[#128C60]/50" : item.status.includes("reject") ?
            "bg-[#B6241C]/50" : "bg-slate-200";
        const icon = item.status === "approved" ? "{{ asset('assets/icon/approve.svg') }}" :
            "{{ asset('assets/icon/pending.svg') }}";
        const text = item.status === "approved" ? "Disetujui" : item.status.includes("reject") ? "Ditolak" :
            "Menunggu";
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
    window.dispatchEvent(new CustomEvent("update-drafts", {
        detail: {
            limit: drafts.slice(0, 3),
            all: drafts
        }
    }));
}

// PENTING: Terapkan perubahan ini di file Blade/JS Anda.
async function loadEditLKH(id, headers) {
    try {
        const res = await fetch(`/api/lkh/${id}`, {
            headers
        });
        const json = await res.json();
        const data = json.data;

        // Populate Standard Inputs
        if (data.tanggal_laporan) document.getElementById("tanggal_lkh").value = data.tanggal_laporan.split('T')[0];
        document.getElementById("jam_mulai").value = data.waktu_mulai;
        document.getElementById("jam_selesai").value = data.waktu_selesai;

        const deskripsiEl = document.querySelector('textarea[name="deskripsi_aktivitas"]');
        if (deskripsiEl) deskripsiEl.value = data.deskripsi_aktivitas ?? "";

        const outputEl = document.querySelector('input[name="output_hasil_kerja"]');
        if (outputEl) outputEl.value = data.output_hasil_kerja ?? "";

        const volumeEl = document.querySelector('input[name="volume"]');
        if (volumeEl) volumeEl.value = data.volume ?? "";

        updateAlpineDropdown('jenis_kegiatan', data.jenis_kegiatan);
        updateAlpineDropdown('tupoksi_id', data.tupoksi_id, data.tupoksi ? data.tupoksi.uraian_tugas :
            'Tupoksi Terpilih');

        // Populate File Lama (Bukti)
        if (data.bukti) {
            renderExistingFiles(data.bukti);
        }

        // --- 1. POPULATE LOGIC KATEGORI & SATUAN (PERBAIKAN KRUSIAL) ---
        const isSkp = !!data.skp_rencana_id;
        const kategoriInput = document.querySelector('input[name="kategori"]');
        if (kategoriInput) kategoriInput.value = isSkp ? "skp" : "non-skp";

        const mainLogicDiv = document.querySelector('[x-data*="kategori:"]');
        if (mainLogicDiv && Alpine.raw(mainLogicDiv.__x.isRoot)) {
            const alpineData = mainLogicDiv.__x.$data;
            alpineData.setKategori(isSkp ? "skp" : "non-skp");

            // Perbaikan: SET VALUE SATUAN DI SINI, UNTUK NON-SKP
            alpineData.satuanValue = data.satuan ?? '';

            setTimeout(() => {
                if (isSkp) {
                    alpineData.skpId = data.skp_rencana_id;
                    alpineData.skpLabel = data.rencana ? data.rencana.rencana_hasil_kerja :
                        'Target SKP Tersimpan';

                    // Lock satuan jika ada
                    alpineData.isSatuanLocked = !!data.satuan && data.satuan !== '-';
                } else {
                    // Pastikan Non-SKP tidak terkunci
                    alpineData.isSatuanLocked = false;
                }
            }, 300);
        }

        // --- 2. POPULATE LOGIC LOKASI (Menggunakan data.latitude & data.longitude yang baru dari Controller) ---
        const modeLokasi = data.mode_lokasi || 'geofence';
        const lokasiContainer = document.querySelector('[x-data*="mode:"]');

        if (lokasiContainer && Alpine.raw(lokasiContainer.__x.isRoot)) {
            const alpine = lokasiContainer.__x.$data;
            alpine.mode = modeLokasi;

            // Asumsi Controller sudah mengembalikan data.latitude & data.longitude
            alpine.lat = data.latitude;
            alpine.lng = data.longitude;
            alpine.lokasiTeksFinal = data.lokasi_teks || '';

            if (modeLokasi === 'geocoding') {
                alpine.searchText = data.lokasi_teks || '';
                // SET STATUS UNTUK MODE GEOCDING
                alpine.status = data.lokasi_teks ? `Tersimpan: ${data.lokasi_teks.substring(0, 30)}...` :
                    'Cari lokasi pada kolom input';
            } else {
                // SET STATUS UNTUK MODE GEOFENCE
                if (data.latitude && data.longitude) {
                    alpine.status =
                        `Terkunci: ${parseFloat(data.latitude).toFixed(5)}, ${parseFloat(data.longitude).toFixed(5)}`;
                } else {
                    alpine.status = 'Klik tombol untuk ambil lokasi';
                }
            }
        }

    } catch (e) {
        console.error("Edit Load Error", e);
    }
}

// --- HELPER FUNCTION UNTUK DISABLE TOMBOL ---
function toggleLoading(isLoading, activeBtn = null) {
    const allButtons = document.querySelectorAll('.btn-action');

    allButtons.forEach(btn => {
        if (isLoading) {
            // Simpan teks asli jika belum disimpan
            if (!btn.dataset.originalText) {
                btn.dataset.originalText = btn.innerHTML;
            }
            btn.disabled = true; // Matikan tombol
        } else {
            btn.disabled = false; // Hidupkan tombol
            // Kembalikan teks asli
            if (btn.dataset.originalText) {
                btn.innerHTML = btn.dataset.originalText;
            }
        }
    });

    // Ubah teks tombol yang diklik menjadi loading spinner/text
    if (isLoading && activeBtn) {
        activeBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg> Memproses...`;
    }
}

// --- FUNGSI SUBMIT FORM UTAMA ---
async function submitForm(type, btnElement) {
    // 1. Matikan semua tombol agar user tidak klik 2x
    toggleLoading(true, btnElement);

    const form = document.getElementById("form-lkh");
    const formData = new FormData(form);
    formData.set("status", type);

    // Validasi Sederhana
    if (type === "waiting_review") {
        if (!formData.get("output_hasil_kerja") || !formData.get("satuan")) {
            Swal.fire({
                icon: "warning",
                title: "Belum Lengkap",
                text: "Output dan Satuan wajib diisi"
            });
            toggleLoading(false); // Hidupkan tombol lagi jika validasi gagal
            return;
        }
        // Validasi Lokasi
        if (!formData.get("latitude") || !formData.get("longitude")) {
            Swal.fire({
                icon: "warning",
                title: "Lokasi Kosong",
                text: "Mohon ambil lokasi GPS atau cari lokasi di peta."
            });
            toggleLoading(false); // Hidupkan tombol lagi
            return;
        }
    }

    try {
        const url = lkhIdToEdit ? `/api/lkh/update/${lkhIdToEdit}` : "/api/lkh";
        const res = await fetch(url, {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("auth_token")}`,
                "Accept": "application/json"
            },
            body: formData
        });
        const json = await res.json();

        if (res.ok) {
            Swal.fire({
                icon: "success",
                title: "Berhasil",
                showConfirmButton: false,
                timer: 1500
            });

            // Arahkan ke dashboard staf
            setTimeout(() => window.location.href = "/staf/dashboard", 1000);
        } else {
            throw new Error(json.message || "Gagal menyimpan data");
        }
    } catch (e) {
        Swal.fire({
            icon: "error",
            title: "Gagal",
            text: e.message
        });
        toggleLoading(false); // Hidupkan tombol lagi jika error API
    }
}

// --- FUNGSI EXPORT PDF ---
async function exportPDF(btnElement) {
    const res = await Swal.fire({
        title: "Preview PDF?",
        text: "Sistem akan memvalidasi data dan membuka preview di tab baru.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya, Buka PDF",
        confirmButtonColor: "#1C7C54"
    });

    if (!res.isConfirmed) return;

    toggleLoading(true, btnElement);

    try {
        const resp = await fetch("/api/lkh/export-pdf", {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("auth_token")}`,
                "Accept": "application/json"
            },
            body: new FormData(document.getElementById("form-lkh"))
        });

        const contentType = resp.headers.get("content-type");

        if (contentType && contentType.indexOf("application/json") !== -1) {
            // --- KASUS GAGAL (Validasi Error) ---
            const json = await resp.json();

            let errorMsg = json.message;
            if (json.details && Array.isArray(json.details)) {
                errorMsg +=
                    "<br><br><div style='text-align:left; font-size:12px; max-height:200px; overflow-y:auto;'><ul>";
                json.details.forEach(err => {
                    errorMsg += `<li class="text-red-600 mb-1">• ${err}</li>`;
                });
                errorMsg += "</ul></div>";
            }

            Swal.fire({
                title: "Data Tidak Lengkap",
                html: errorMsg,
                icon: "error"
            });

        } else if (resp.ok) {
            // --- KASUS SUKSES (Membuka Tab Baru) ---
            const blob = await resp.blob();
            const url = window.URL.createObjectURL(blob);
            window.open(url, '_blank');

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            Toast.fire({
                icon: 'success',
                title: 'PDF berhasil dibuka'
            });

        } else {
            throw new Error("Terjadi kesalahan server");
        }

    } catch (e) {
        console.error(e);
        Swal.fire("Error", "Gagal menghubungi server", "error");
    } finally {
        toggleLoading(false, btnElement);
    }
}
</script>
@endpush

@endsection