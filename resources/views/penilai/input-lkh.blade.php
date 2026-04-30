@php($title = 'Input LKH')
@extends('layouts.app', ['title' => $title, 'role' => 'penilai', 'active' => 'input-lkh'])

@section('content')

{{-- GRID UTAMA DENGAN 2 KOLOM EKSPLISIT (KIRI & KANAN) --}}
<section class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)] gap-4 lg:gap-5 items-start">

    {{-- ========================================================== --}}
    {{-- KOLOM KIRI (FORM UTAMA & DRAFT) --}}
    {{-- ========================================================== --}}
    <div class="flex flex-col gap-4 lg:gap-5">
        
        {{-- KIRI ATAS: FORM INPUT LKH --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 shadow-sm">
            <h2 class="text-[20px] font-bold text-slate-800 mb-4">Form Input LKH</h2>

            <form id="form-lkh" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="status" id="status_input" value="draft">

                {{-- Mode Lokasi Hidden Input (Default: geofence) --}}
                <input type="hidden" name="mode_lokasi" id="mode_lokasi_input" value="geofence">

                <div class="space-y-5">

                    {{-- Row 1: Tanggal + Jenis Kegiatan --}}
                    <div class="grid md:grid-cols-2 gap-4">
                        {{-- Tanggal --}}
                        <div>
                            <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Tanggal</label>
                            <div class="relative">
                                <input id="tanggal_lkh" name="tanggal_laporan" type="date"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none transition-all" />
                                <button type="button" id="tanggal_lkh_btn"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center hover:bg-slate-200 p-1 rounded-md transition-colors">
                                    <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-80" alt="Date" />
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
                            <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Jenis Kegiatan</label>
                            <input type="hidden" name="jenis_kegiatan" x-model="value">

                            <div class="relative">
                                <button type="button" @click="open = !open" @click.outside="open = false"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm pr-3 text-left flex items-center justify-between focus:ring-2 focus:ring-[#1C7C54]/30 transition-all"
                                    :class="!value ? 'text-slate-400' : 'text-slate-700'">
                                    <span x-text="label"></span>
                                    <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70" alt="">
                                </button>

                                <div x-show="open" x-transition
                                    class="absolute z-20 mt-1 w-full rounded-xl bg-white shadow-lg border border-slate-200 py-1">
                                    <template x-for="opt in options" :key="opt">
                                        <button type="button"
                                            class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50 flex justify-between transition-colors"
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

                    {{-- Row 2: Referensi Tupoksi --}}
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
                        <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Referensi Tupoksi</label>
                        <input type="hidden" name="tupoksi_id" x-model="value">

                        <div class="relative">
                            <button type="button" @click="open = !open" @click.outside="open = false"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm pr-3 text-left flex items-center justify-between focus:ring-2 focus:ring-[#1C7C54]/30 transition-all"
                                :class="!value ? 'text-slate-400' : 'text-slate-700'">
                                <span x-text="loading ? 'Memuat...' : label" class="truncate mr-2"></span>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70 flex-shrink-0" alt="">
                            </button>

                            <div x-show="open" x-transition
                                class="absolute z-20 mt-1 w-full rounded-xl bg-white shadow-lg border border-slate-200 py-1 max-h-60 overflow-y-auto custom-scrollbar">
                                <template x-for="opt in options" :key="opt.id">
                                    <button type="button"
                                        class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50 flex justify-between gap-2 border-b border-slate-50 last:border-0"
                                        :class="value === opt.id ? 'text-[#1C7C54] font-medium' : 'text-slate-700'"
                                        @click="select(opt)">
                                        <span x-text="opt.text" class="line-clamp-2"></span>
                                        <span x-show="value === opt.id" class="shrink-0">✓</span>
                                    </button>
                                </template>
                                <div x-show="!options.length && !loading" class="px-3 text-sm text-slate-400 italic py-2">Data kosong</div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 3: Uraian Kegiatan --}}
                    <div>
                        <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Uraian Kegiatan</label>
                        <textarea name="deskripsi_aktivitas" rows="3"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] transition-all"
                            placeholder="Tulis uraian kegiatan yang dilakukan secara detail..."></textarea>
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
                        <div class="grid md:grid-cols-[2fr_1fr] gap-4 bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                            <div>
                                <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Output / Hasil</label>
                                <input type="text" name="output_hasil_kerja"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] transition-all"
                                    placeholder="Contoh: Dokumen Laporan">
                            </div>
                            <div class="relative">
                                <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Kategori Kinerja</label>
                                <input type="hidden" name="kategori" x-model="kategori">
                                <button type="button" @click="kategoriOpen = !kategoriOpen"
                                    @click.outside="kategoriOpen = false"
                                    class="w-full flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#1C7C54]/30 transition-all">
                                    <span x-text="kategori === 'skp' ? 'SKP' : 'Non-SKP'"
                                        :class="kategori === 'skp' ? 'text-[#1C7C54] font-bold' : 'text-slate-700'"></span>
                                    <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70 ml-2">
                                </button>
                                <div x-show="kategoriOpen" x-transition
                                    class="absolute z-20 mt-1 w-full rounded-xl bg-white shadow-lg border border-slate-200 overflow-hidden">
                                    <button type="button" @click="setKategori('skp')"
                                        class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50">SKP</button>
                                    <button type="button" @click="setKategori('non-skp')"
                                        class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50">Non-SKP</button>
                                </div>
                            </div>
                        </div>

                        {{-- Row 5: List SKP (Hidden by default) --}}
                        <div x-show="kategori === 'skp'" x-transition class="mt-4">
                            <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Target SKP Tahunan</label>
                            <input type="hidden" name="skp_rencana_id" x-model="skpId">
                            <div class="relative">
                                <button type="button" @click="skpOpen = !skpOpen" @click.outside="skpOpen = false"
                                    class="w-full flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-sm text-left focus:ring-2 focus:ring-[#1C7C54]/30 transition-all">
                                    <span x-text="skpLoading ? 'Memuat data...' : skpLabel" class="truncate text-emerald-800 font-medium"></span>
                                    <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70 ml-2 text-emerald-800">
                                </button>
                                <div x-show="skpOpen" x-transition
                                    class="absolute z-20 mt-1 w-full rounded-xl bg-white shadow-lg border border-slate-200 max-h-60 overflow-y-auto custom-scrollbar">
                                    <template x-for="opt in skpOptions" :key="opt.value">
                                        <button type="button" @click="pilihSkp(opt)"
                                            class="w-full text-left px-3.5 py-2.5 text-sm hover:bg-slate-50 border-b border-slate-100 transition-colors">
                                            <span x-text="opt.label" class="line-clamp-2 text-slate-700"></span>
                                            <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full inline-block mt-1 font-bold" x-text="'Target: ' + opt.target + ' ' + opt.satuan"></span>
                                        </button>
                                    </template>
                                    <div x-show="!skpOptions.length && !skpLoading" class="p-3 text-sm text-slate-400 italic text-center">Tidak ada data SKP.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Row 6: Satuan & Volume --}}
                        <div class="grid md:grid-cols-2 gap-4 mt-4">
                            <div class="relative">
                                <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Satuan</label>
                                <input type="hidden" name="satuan" x-model="satuanValue">
                                <div x-show="isSatuanLocked"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-500 cursor-not-allowed flex justify-between items-center shadow-inner">
                                    <span x-text="satuanValue" class="font-medium"></span>
                                    <img src="{{ asset('assets/icon/lock.svg') }}" class="h-3.5 w-3.5 opacity-50">
                                </div>
                                <div x-show="!isSatuanLocked">
                                    <button type="button" @click="satuanOpen = !satuanOpen"
                                        @click.outside="satuanOpen = false"
                                        class="w-full flex justify-between items-center rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm text-left focus:ring-2 focus:ring-[#1C7C54]/30 transition-all">
                                        <span x-text="satuanValue || 'Pilih Satuan'" :class="!satuanValue ? 'text-slate-400' : 'text-slate-700 font-medium'"></span>
                                        <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70">
                                    </button>
                                    <div x-show="satuanOpen" x-transition
                                        class="absolute z-20 mt-1 w-full rounded-xl bg-white shadow-lg border border-slate-200 py-1 max-h-48 overflow-y-auto custom-scrollbar">
                                        <template x-for="opt in ['Jam', 'Dokumen', 'Kegiatan', 'Laporan', 'Berkas']">
                                            <button type="button" @click="satuanValue = opt; satuanOpen = false"
                                                class="w-full px-3.5 py-2 text-sm text-left hover:bg-slate-50 transition-colors">
                                                <span x-text="opt"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Volume</label>
                                <input type="number" name="volume" min="0"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] transition-all"
                                    placeholder="0">
                            </div>
                        </div>
                    </div>

                    {{-- Row 7: Waktu --}}
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Jam Mulai</label>
                            <div class="relative">
                                <input id="jam_mulai" name="waktu_mulai" type="time"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none transition-all" />
                                <button type="button" id="jam_mulai_btn"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center hover:bg-slate-200 p-1 rounded-md transition-colors">
                                    <img src="{{ asset('assets/icon/time.svg') }}" class="h-4 w-4 opacity-70">
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Jam Selesai</label>
                            <div class="relative">
                                <input id="jam_selesai" name="waktu_selesai" type="time"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none transition-all" />
                                <button type="button" id="jam_selesai_btn"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center hover:bg-slate-200 p-1 rounded-md transition-colors">
                                    <img src="{{ asset('assets/icon/time.svg') }}" class="h-4 w-4 opacity-70">
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Row 8: Bukti & Lokasi Modern --}}
                    <div class="grid md:grid-cols-2 gap-4">
                        
                        {{-- Unggah Bukti --}}
                        <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                            <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Unggah Bukti</label>
                            <label
                                class="w-full flex items-center justify-between rounded-xl border-2 border-dashed border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-500 cursor-pointer hover:bg-slate-50 hover:border-[#1C7C54] transition-all">
                                <span id="bukti_label_text" class="truncate font-medium">Klik untuk pilih file...</span>
                                <img src="{{ asset('assets/icon/upload.svg') }}" class="h-4 w-4 opacity-70">
                                <input type="file" id="bukti_input" name="bukti[]" multiple class="hidden"
                                    onchange="handleNewFiles(this)">
                            </label>
                            <div id="preview_file_baru" class="mt-2 space-y-2"></div>
                            <div id="container_hapus_bukti"></div>
                            <div id="preview_file_lama" class="mt-3 space-y-2"></div>
                        </div>

                        {{-- Modul Input Lokasi & Kategori [UPDATED] --}}
                        <div id="lokasi-wrapper" x-data="{ kategori_lokasi: 'WFO' }" class="bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                            <label class="block font-semibold text-[13px] text-slate-500 uppercase tracking-wide mb-2">Lokasi Aktual</label>

                            {{-- NEW: Dropdown Kategori Lokasi --}}
                            <div class="mb-3">
                                <select id="kategori_lokasi_input" name="kategori_lokasi" x-model="kategori_lokasi" 
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] cursor-pointer transition-all shadow-sm">
                                    <option value="WFO">🏢 WFO (Kantor)</option>
                                    <option value="WFH">🏠 WFH (Rumah)</option>
                                    <option value="WFA">💻 WFA (Bebas)</option>
                                    <option value="DL">✈️ Dinas Luar</option>
                                </select>
                            </div>

                            {{-- Hidden Inputs Data --}}
                            <input type="hidden" name="latitude" id="input_lat">
                            <input type="hidden" name="longitude" id="input_lng">
                            <input type="hidden" name="lokasi_teks" id="input_lokasi_teks">
                            <input type="hidden" name="address_auto" id="input_address_auto">
                            <input type="hidden" name="location_provider" id="input_provider" value="manual_pin">

                            <div class="flex gap-2">
                                {{-- Preview Lokasi (Readonly) --}}
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <img src="{{ asset('assets/icon/location.svg') }}" class="w-4 h-4 opacity-50">
                                    </div>
                                    <input type="text" id="preview_lokasi" readonly
                                        class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3.5 py-2.5 text-sm text-slate-600 focus:outline-none cursor-not-allowed truncate font-medium shadow-sm"
                                        placeholder="Koordinat belum diatur">
                                </div>

                                {{-- Tombol Trigger Fullscreen --}}
                                <button type="button" id="btnOpenMap"
                                    class="shrink-0 bg-[#1C7C54] hover:bg-[#156343] text-white px-3 py-2.5 rounded-xl text-sm font-bold flex items-center transition-colors shadow-md hover:shadow-lg active:scale-95">
                                    <span class="hidden md:inline">📍 Buka Peta</span>
                                    <span class="md:hidden">📍 Peta</span>
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-2 leading-snug italic">
                                *Pilih kategori di atas, lalu <strong class="text-slate-500">Buka Peta</strong> untuk pin lokasi secara akurat.
                            </p>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-wrap items-center justify-end gap-3 pt-4 mt-2 border-t border-slate-100">
                        <button type="button" onclick="exportPDF(this)"
                            class="btn-action rounded-xl bg-slate-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-700 shadow-sm hover:shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            Export PDF
                        </button>

                        <button type="button" onclick="submitForm('draft', this)"
                            class="btn-action rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-700 shadow-sm hover:shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            Simpan Draft
                        </button>

                        <button type="button" onclick="submitForm('waiting_review', this)"
                            class="btn-action rounded-xl bg-[#0E7A4A] px-6 py-2.5 text-sm font-bold text-white hover:bg-[#0b633b] shadow-md hover:shadow-lg shadow-emerald-200 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                            Kirim LKH
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- KIRI BAWAH: DRAFT LKH (Dengan Alpine logic yang aman) --}}
        <div x-data="{ 
            openDraftModal: false, 
            draftsLimit: [], 
            draftsAll: [], 
            deleteDraft(id) {
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
                        fetch(`/api/lkh/delete/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                                'Accept': 'application/json'
                            }
                        }).then(res => {
                            if (res.ok) {
                                Swal.fire('Terhapus!', 'Draft berhasil dihapus.', 'success');
                                fetchDashboardStats();
                            } else {
                                Swal.fire('Gagal!', 'Gagal menghapus draft.', 'error');
                            }
                        });
                    }
                });
            }
        }" @update-drafts.window="draftsLimit = $event.detail.limit; draftsAll = $event.detail.all;" x-cloak
            class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 shadow-sm flex flex-col">

            <div class="flex items-center justify-between mb-4 shrink-0 border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-archive text-amber-500"></i> Brankas Draft</h3>
                <button type="button" x-show="draftsAll.length > 0"
                    class="text-sm text-[#0E7A4A] font-bold hover:underline" @click="openDraftModal = true">
                    Lihat Semua (<span x-text="draftsAll.length"></span>)
                </button>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto pr-1 custom-scrollbar">
                <template x-if="draftsLimit.length === 0">
                    <p class="text-sm text-slate-400 italic text-center py-4">Belum ada draft tersimpan.</p>
                </template>
                <template x-for="item in draftsLimit" :key="item.id">
                    <div class="bg-slate-50 rounded-xl p-4 flex items-center justify-between gap-3 border border-slate-200 shadow-sm hover:border-blue-300 transition-colors">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-slate-800 truncate" x-text="item.deskripsi"></h4>
                            <p class="text-xs font-medium text-slate-500 mt-1"><i class="far fa-clock mr-1"></i> <span x-text="item.waktu_simpan"></span></p>
                        </div>
                        <div class="flex gap-2">
                            <a :href="'/penilai/input-laporan/' + item.id"
                                class="bg-[#0E7A4A] text-white text-xs font-bold px-3 py-2 rounded-lg hover:bg-[#0b633b] transition-colors">
                                Lanjutkan
                            </a>
                            <button @click="deleteDraft(item.id)"
                                class="bg-white border border-rose-200 text-rose-600 text-xs font-bold px-3 py-2 rounded-lg hover:bg-rose-50 transition-colors">
                                Hapus
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Modal Full Draft --}}
            <div x-show="openDraftModal" x-transition.opacity
                class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display: none;">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openDraftModal = false"></div>
                <div class="relative z-10 w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[85vh] ring-1 ring-slate-900/5">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/80 rounded-t-2xl">
                        <h2 class="text-lg font-bold text-slate-800"><i class="fas fa-archive text-amber-500 mr-2"></i> Semua Draft Tersimpan</h2>
                        <button @click="openDraftModal = false" class="h-8 w-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:text-rose-500 hover:bg-rose-50 transition-colors">&times;</button>
                    </div>
                    <div class="overflow-y-auto p-6 space-y-3 bg-white custom-scrollbar">
                        <template x-for="item in draftsAll" :key="item.id">
                            <div class="bg-slate-50 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border border-slate-200 shadow-sm hover:border-blue-300 transition-colors">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800" x-text="item.deskripsi"></h4>
                                    <p class="text-xs font-medium text-slate-500 mt-1"><i class="far fa-clock mr-1"></i> <span x-text="item.waktu_simpan"></span></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a :href="'/penilai/input-laporan/' + item.id"
                                        class="bg-[#0E7A4A] hover:bg-[#0b633b] text-white text-xs font-bold px-4 py-2 rounded-lg transition-colors">
                                        Lanjutkan
                                    </a>
                                    <button @click="deleteDraft(item.id)"
                                        class="bg-white hover:bg-rose-50 border border-rose-200 text-rose-600 text-xs font-bold px-4 py-2 rounded-lg transition-colors">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================================================== --}}
    {{-- KOLOM KANAN (PANDUAN & STATUS) --}}
    {{-- ========================================================== --}}
    <div class="flex flex-col gap-4 lg:gap-5">
        
        {{-- KANAN ATAS: PANDUAN SINGKAT --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 flex flex-col shadow-sm">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fas fa-book-open text-blue-500"></i> Panduan Pengisian LKH
            </h3>

            <div class="space-y-4">
                {{-- Panduan Umum --}}
                <div class="rounded-xl bg-[#155FA6] px-4 py-3.5 text-white shadow-sm">
                    <p class="text-[14px] font-bold border-b border-blue-400/30 pb-2 mb-2">1. Data Waktu & Kategori</p>
                    <ul class="text-[12px] text-blue-50 list-disc pl-4 space-y-1.5 font-medium leading-relaxed">
                        <li>Pastikan <span class="bg-blue-800 px-1 rounded">Jam Mulai</span> lebih awal dari Jam Selesai.</li>
                        <li><strong>[PENTING]</strong> Pilih <strong>Kategori Lokasi</strong> (WFO, WFH, WFA, atau Dinas Luar) dengan jujur sebelum Anda mengirim laporan.</li>
                    </ul>
                </div>

                {{-- Panduan SKP --}}
                <div class="rounded-xl bg-emerald-600 px-4 py-3.5 text-white shadow-sm">
                    <p class="text-[14px] font-bold border-b border-emerald-400/30 pb-2 mb-2">2. Kategori Kinerja & Output</p>
                    <ul class="text-[12px] text-emerald-50 list-disc pl-4 space-y-1.5 font-medium leading-relaxed">
                        <li><strong>Non-SKP:</strong> Tentukan satuan hasil kerja secara manual.</li>
                        <li><strong>SKP:</strong> Sistem mengunci satuan agar selaras dengan perjanjian target tahunan Anda.</li>
                        <li>Volume (jumlah hasil) wajib diisi.</li>
                    </ul>
                </div>
                
                {{-- Panduan Lokasi --}}
                <div class="rounded-xl bg-rose-600 px-4 py-3.5 text-white shadow-sm">
                    <p class="text-[14px] font-bold border-b border-rose-400/30 pb-2 mb-2">3. Titik Lokasi Validasi</p>
                    <ul class="text-[12px] text-rose-50 list-disc pl-4 space-y-1.5 font-medium leading-relaxed">
                        <li>Gunakan tombol <strong>Buka Peta</strong> untuk menentukan koordinat GPS yang presisi.</li>
                        <li>Jika WFO, pastikan titik berada di area kantor. Sistem melacak status WFH/WFO ini untuk kebutuhan audit pimpinan.</li>
                    </ul>
                </div>

                {{-- Panduan Bukti --}}
                <div class="rounded-xl bg-slate-700 px-4 py-3.5 text-white shadow-sm">
                    <p class="text-[14px] font-bold border-b border-slate-500/30 pb-2 mb-2">4. Unggah Bukti</p>
                    <ul class="text-[12px] text-slate-100 list-disc pl-4 space-y-1.5 font-medium leading-relaxed">
                        <li>Sertakan dokumen (PDF) atau foto JPG/PNG sebagai bukti dukung.</li>
                        <li>Klik <i class="fas fa-times text-rose-400 mx-1"></i> pada daftar untuk membatalkan file.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- KANAN BAWAH: STATUS LOG --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fas fa-history text-indigo-500"></i> Status Laporan
            </h3>
            <ul class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar" id="aktivitas-list">
                <li class="text-sm text-slate-400 font-medium italic text-center py-4">Memuat data...</li>
            </ul>
        </div>
        
    </div>

</section>

{{-- MODAL FULLSCREEN MAP --}}
<div id="fullscreenMapModal" class="fixed inset-0 z-[9999] bg-slate-100 hidden flex-col font-sans">
    
    {{-- Close Button --}}
    <div class="absolute top-4 left-4 z-[1001]">
        <button type="button" id="btnCloseMap" 
            class="h-10 w-10 bg-white rounded-xl shadow-lg flex items-center justify-center hover:bg-rose-50 text-slate-600 hover:text-rose-600 border border-slate-200 transition-all active:scale-95" title="Tutup Peta">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Map Container --}}
    <div id="map_fullscreen" class="w-full h-full bg-slate-100 relative z-0"></div>

    {{-- Fixed Center Pin --}}
    <div class="center-pin-wrapper absolute top-1/2 left-1/2 z-[1000] pointer-events-none flex flex-col items-center pb-[40px] transform -translate-x-1/2 -translate-y-1/2 transition-transform duration-100">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-12 h-12 text-rose-600 drop-shadow-xl filter" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));">
            <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
        </svg>
        <div class="absolute bottom-[38px] w-1 h-1 bg-black rounded-full opacity-50"></div>
        <div class="pin-shadow w-3 h-1.5 bg-black/30 rounded-full mt-[-4px] blur-[1px]"></div>
    </div>

    {{-- Floating Controls --}}
    <div class="absolute bottom-8 left-4 right-4 z-[1001] max-w-lg mx-auto w-full">
        <div class="bg-white rounded-2xl shadow-2xl p-5 ring-1 ring-slate-900/5 animate-slide-up">
            <div class="flex items-start gap-4 mb-4">
                <div class="mt-1 shrink-0 bg-rose-50 p-3 rounded-full border border-rose-100">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-rose-600">
                        <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                </div>
                
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Titik Koordinat</p>
                    <p id="mapAddressPreview" class="text-sm font-bold text-slate-800 leading-snug line-clamp-2">
                        Sedang mencari lokasi...
                    </p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 rounded border border-slate-200 font-mono font-medium" id="mapCoordsPreview">-</span>
                    </div>
                </div>
            </div>

            <button type="button" id="btnConfirmLocation" 
                class="w-full bg-[#1C7C54] hover:bg-[#156343] text-white font-bold text-sm py-3.5 rounded-xl shadow-lg shadow-emerald-200 transition-all active:scale-[0.98] flex items-center justify-center gap-2 group">
                <span>Konfirmasi Lokasi Ini</span>
                <i class="fas fa-check group-hover:scale-110 transition-transform"></i>
            </button>
        </div>
    </div>
</div>

{{-- Tambahkan custom scrollbar agar tampilan dalam card rapi --}}
@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endpush

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

// [FIX] Menggunakan document.getElementById lebih aman daripada querySelector x-data
function setAlpineValue(id, key, value) {
    const el = document.getElementById(id);
    if (el && el._x_dataStack) {
        // Alpine v3 menaruh data state di _x_dataStack
        el._x_dataStack[0][key] = value;
    }
}

function handleNewFiles(inputElement) {
    const files = inputElement.files;
    for (let i = 0; i < files.length; i++) {
        newFilesDataTransfer.items.add(files[i]);
    }
    inputElement.files = newFilesDataTransfer.files;
    updateNewFileUI();
}

function removeNewFile(index) {
    const dt = new DataTransfer();
    const currentFiles = newFilesDataTransfer.files;

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

    labelText.textContent = files.length === 0 ? "Pilih File Baru" : `${files.length} file baru akan diunggah`;
    container.innerHTML = '';

    if (files.length > 0) {
        const header = document.createElement('p');
        header.className = "text-[11px] text-[#155FA6] font-bold mb-2 mt-3 uppercase tracking-wide";
        header.innerText = "Akan diunggah (Baru):";
        container.appendChild(header);
    }

    Array.from(files).forEach((file, index) => {
        const isImage = file.type.startsWith('image/');
        const fileSizeKB = (file.size / 1024).toFixed(1);

        const div = document.createElement('div');
        div.className = "flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl p-2 shadow-sm relative group";

        let thumbnailHtml = `<div class="h-10 w-10 shrink-0 rounded-lg bg-white flex items-center justify-center border border-blue-100 text-[9px] font-bold text-blue-500 uppercase shadow-sm">${file.name.split('.').pop()}</div>`;

        div.innerHTML = `
            <div class="flex items-center gap-3 overflow-hidden w-full">
                <div id="thumb-new-${index}" class="shrink-0">${thumbnailHtml}</div>
                <div class="min-w-0 flex-1">
                    <p class="text-[12px] font-bold text-slate-800 truncate">${file.name}</p>
                    <p class="text-[10px] font-medium text-slate-500 mt-0.5">${fileSizeKB} KB <span class="text-emerald-600 ml-1 bg-emerald-100 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider text-[8px]">Baru</span></p>
                </div>
                <button type="button" onclick="removeNewFile(${index})" 
                    class="h-7 w-7 flex items-center justify-center rounded-full text-slate-400 hover:bg-rose-100 hover:text-rose-600 transition-colors shrink-0">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(div);

        if (isImage) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.getElementById(`thumb-new-${index}`);
                if (imgContainer) {
                    imgContainer.innerHTML = `
                        <div class="h-10 w-10 shrink-0 rounded-lg bg-white overflow-hidden border border-blue-100 shadow-sm">
                            <img src="${e.target.result}" class="h-full w-full object-cover">
                        </div>`;
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

function renderExistingFiles(files) {
    const container = document.getElementById('preview_file_lama');
    container.innerHTML = ''; 

    if (!files || files.length === 0) return;

    container.innerHTML = '<p class="text-[11px] font-bold text-slate-500 mb-2 mt-2 uppercase tracking-wide">File Tersimpan:</p>';

    files.forEach(file => {
        const ext = file.file_type ? file.file_type.toLowerCase() : 'file';
        const isImage = ['jpg', 'jpeg', 'png', 'webp'].includes(ext);
        const fileUrl = `/storage/${file.file_path}`;

        const div = document.createElement('div');
        div.className = "flex items-center justify-between bg-white border border-slate-200 rounded-xl p-2 shadow-sm";
        div.id = `file-wrapper-${file.id}`;

        div.innerHTML = `
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="h-10 w-10 shrink-0 rounded-lg bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200 shadow-sm">
                    ${isImage ? `<img src="${fileUrl}" class="h-full w-full object-cover">` : `<span class="text-[9px] font-bold text-slate-500 uppercase">${ext}</span>`}
                </div>
                <div class="min-w-0">
                    <a href="${fileUrl}" target="_blank" class="text-[12px] font-bold text-slate-800 hover:text-blue-600 hover:underline truncate block">
                        ${file.file_name_original || 'File Tanpa Nama'}
                    </a>
                    <p class="text-[10px] font-medium text-slate-500 mt-0.5">${(file.file_size / 1024).toFixed(1)} KB</p>
                </div>
            </div>
            <button type="button" onclick="markFileForDeletion(${file.id})" 
                class="h-7 w-7 flex items-center justify-center rounded-full text-slate-400 hover:bg-rose-100 hover:text-rose-600 transition-colors" title="Hapus file ini">
                <i class="fas fa-trash-alt text-[10px]"></i>
            </button>
        `;
        container.appendChild(div);
    });
}

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
    const token = localStorage.getItem("auth_token");
    const headers = {
        "Accept": "application/json",
        "Authorization": "Bearer " + token
    };

    fetchDashboardStats();

    if (lkhIdToEdit) {
        loadEditLKH(lkhIdToEdit, headers);
    }

    ["tanggal_lkh", "jam_mulai", "jam_selesai"].forEach(id => {
        const btn = document.getElementById(id + "_btn");
        const input = document.getElementById(id);
        if (btn && input) {
            btn.addEventListener("click", () => {
                if (typeof input.showPicker === "function") { input.showPicker(); } 
                else { input.focus(); }
            });
        }
    });

    if (typeof window.initMapComponent === 'function') {
        window.initMapComponent();
    } else {
        console.warn("GIS Module Error: window.initMapComponent is not defined.");
    }
});

async function fetchDashboardStats() {
    const token = localStorage.getItem("auth_token");
    try {
        const res = await fetch("/api/dashboard/stats", {
            headers: { "Accept": "application/json", "Authorization": "Bearer " + token }
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
        el.innerHTML = `<li class="text-sm font-medium text-slate-500 text-center py-4">Belum ada aktivitas.</li>`;
        return;
    }
    list.forEach(item => {
        let tone = 'bg-slate-100 border-slate-200 text-slate-600';
        let iconHtml = '<i class="fas fa-clock"></i>';
        let text = 'Menunggu';

        if (item.status === 'approved') {
            tone = 'bg-emerald-50 border-emerald-200 text-emerald-600';
            iconHtml = '<i class="fas fa-check"></i>';
            text = 'Disetujui';
        } else if (item.status === 'rejected' || item.status.includes('reject')) {
            tone = 'bg-rose-50 border-rose-200 text-rose-600';
            iconHtml = '<i class="fas fa-times"></i>';
            text = 'Ditolak';
        }

        el.insertAdjacentHTML("beforeend", `
            <li class="flex items-start gap-3 bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                <div class="h-8 w-8 rounded-lg flex items-center justify-center border shrink-0 ${tone}">
                    ${iconHtml}
                </div>
                <div class="flex-1 overflow-hidden">
                    <div class="text-[13px] font-bold text-slate-800 truncate mb-1" title="${item.deskripsi_aktivitas}">${item.deskripsi_aktivitas}</div>
                    <div class="flex justify-between items-center text-[11px] font-medium text-slate-500">
                        <span class="uppercase tracking-wider ${tone.split(' ')[2]}">${text}</span>
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
        deskripsi: d.deskripsi_aktivitas || "Draft Tanpa Deskripsi",
        waktu_simpan: new Date(d.updated_at).toLocaleString('id-ID', {day: 'numeric', month: 'short', hour: '2-digit', minute:'2-digit'})
    }));
    window.dispatchEvent(new CustomEvent("update-drafts", {
        detail: { limit: drafts.slice(0, 3), all: drafts }
    }));
}

async function loadEditLKH(id, headers) {
    try {
        const res = await fetch(`/api/lkh/${id}`, { headers });
        const json = await res.json();
        const data = json.data;

        if (data.tanggal_laporan) document.getElementById("tanggal_lkh").value = data.tanggal_laporan.split('T')[0];
        document.getElementById("jam_mulai").value = data.waktu_mulai;
        document.getElementById("jam_selesai").value = data.waktu_selesai;

        const deskripsiEl = document.querySelector('textarea[name="deskripsi_aktivitas"]');
        if (deskripsiEl) deskripsiEl.value = data.deskripsi_aktivitas ?? "";

        const outputEl = document.querySelector('input[name="output_hasil_kerja"]');
        if (outputEl) outputEl.value = data.output_hasil_kerja ?? "";

        const volumeEl = document.querySelector('input[name="volume"]');
        if (volumeEl) volumeEl.value = data.volume ?? "";

        const satuanEl = document.querySelector('input[name="satuan"]');
        if (satuanEl) satuanEl.value = data.satuan ?? "";

        // [NEW] Set Kategori Lokasi Value dengan aman
        const katLokasiEl = document.getElementById("kategori_lokasi_input");
        if (katLokasiEl) {
            katLokasiEl.value = data.kategori_lokasi || 'WFO';
            // Sync with Alpine x-model via ID
            setAlpineValue('lokasi-wrapper', 'kategori_lokasi', data.kategori_lokasi || 'WFO');
        }

        updateAlpineDropdown('jenis_kegiatan', data.jenis_kegiatan);
        updateAlpineDropdown('tupoksi_id', data.tupoksi_id, data.tupoksi ? data.tupoksi.uraian_tugas : 'Tupoksi Terpilih');

        if (data.bukti) renderExistingFiles(data.bukti);

        if (data.latitude || data.lokasi_teks) {
            document.getElementById('input_lat').value = data.latitude ?? '';
            document.getElementById('input_lng').value = data.longitude ?? '';
            document.getElementById('input_lokasi_teks').value = data.lokasi_teks ?? '';
            document.getElementById('input_address_auto').value = data.address_auto ?? '';
            document.getElementById('input_provider').value = data.location_provider ?? 'manual_pin';

            let displayLocation = "Lokasi tersimpan";
            if (data.lokasi_teks) displayLocation = data.lokasi_teks;
            else if (data.address_auto) displayLocation = data.address_auto;
            else if (data.latitude) displayLocation = `${data.latitude}, ${data.longitude}`;

            const previewEl = document.getElementById('preview_lokasi');
            if (previewEl) previewEl.value = displayLocation;
        }

        const isSkp = !!data.skp_rencana_id;
        const kategoriInput = document.querySelector('input[name="kategori"]');
        if (kategoriInput) kategoriInput.value = isSkp ? "skp" : "non-skp";

        const mainLogicDiv = document.querySelector('[x-data*="kategori:"]');
        if (mainLogicDiv && Alpine.raw(mainLogicDiv.__x.isRoot)) {
            const alpineData = mainLogicDiv.__x.$data;
            alpineData.setKategori(isSkp ? "skp" : "non-skp");

            setTimeout(() => {
                if (isSkp && data.rencana) {
                    alpineData.skpId = data.skp_rencana_id;
                    alpineData.skpLabel = data.rencana.rencana_hasil_kerja;
                    alpineData.satuanValue = data.satuan;
                    alpineData.isSatuanLocked = true;
                } else {
                    alpineData.satuanValue = data.satuan;
                    alpineData.isSatuanLocked = false;
                }
            }, 300);
        }

    } catch (e) {
        console.error("Edit Load Error", e);
    }
}

function toggleLoading(isLoading, activeBtn = null) {
    const allButtons = document.querySelectorAll('.btn-action');

    allButtons.forEach(btn => {
        if (isLoading) {
            if (!btn.dataset.originalText) btn.dataset.originalText = btn.innerHTML;
            btn.disabled = true; 
        } else {
            btn.disabled = false; 
            if (btn.dataset.originalText) btn.innerHTML = btn.dataset.originalText;
        }
    });

    if (isLoading && activeBtn) {
        activeBtn.innerHTML = `<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses...`;
    }
}

async function submitForm(type, btnElement) {
    toggleLoading(true, btnElement);

    const form = document.getElementById("form-lkh");
    const formData = new FormData(form);
    formData.set("status", type);

    if (type === "waiting_review") {
        if (!formData.get("output_hasil_kerja") || !formData.get("satuan")) {
            Swal.fire({ icon: "warning", title: "Belum Lengkap", text: "Output dan Satuan wajib diisi", confirmButtonColor: "#1C7C54" });
            toggleLoading(false); 
            return;
        }
        if (!formData.get("latitude") || !formData.get("longitude")) {
            Swal.fire({ icon: "warning", title: "Lokasi Kosong", text: "Mohon ambil lokasi GPS atau cari lokasi di peta.", confirmButtonColor: "#1C7C54" });
            toggleLoading(false); 
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
            Swal.fire({ icon: "success", title: "Berhasil", text: "Laporan berhasil disimpan.", showConfirmButton: false, timer: 1500 });
            setTimeout(() => window.location.href = "/penilai/dashboard", 1000);
        } else {
            throw new Error(json.message || "Gagal menyimpan data");
        }
    } catch (e) {
        Swal.fire({ icon: "error", title: "Gagal", text: e.message, confirmButtonColor: "#B6241C" });
        toggleLoading(false); 
    }
}

async function exportPDF(btnElement) {
    const res = await Swal.fire({
        title: "Preview PDF?", text: "Sistem akan memvalidasi data dan membuka preview di tab baru.", icon: "question", showCancelButton: true, confirmButtonText: "Ya, Buka PDF", confirmButtonColor: "#1C7C54"
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
            const json = await resp.json();
            let errorMsg = json.message;
            if (json.details && Array.isArray(json.details)) {
                errorMsg += "<br><br><div style='text-align:left; font-size:12px; max-height:200px; overflow-y:auto;' class='custom-scrollbar'><ul>";
                json.details.forEach(err => { errorMsg += `<li class="text-rose-600 font-medium mb-1"><i class="fas fa-exclamation-circle mr-1"></i> ${err}</li>`; });
                errorMsg += "</ul></div>";
            }
            Swal.fire({ title: "Data Tidak Lengkap", html: errorMsg, icon: "error", confirmButtonColor: "#1C7C54" });
        } else if (resp.ok) {
            const blob = await resp.blob();
            const url = window.URL.createObjectURL(blob);
            window.open(url, '_blank');
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
            Toast.fire({ icon: 'success', title: 'PDF berhasil dibuka' });
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