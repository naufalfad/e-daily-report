@php($title = 'Input LKH')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'input-lkh'])

@section('content')

{{-- GRID UTAMA: FORM + PANDUAN + DRAFT + STATUS --}}
<section class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)] gap-4 lg:auto-rows-min">

    {{-- KIRI ATAS: FORM INPUT LKH --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
        <h2 class="text-[20px] font-normal mb-4">Form Input LKH</h2>

        {{-- 
            [PENTING] Kita tambahkan tag <form> di sini agar tombol submit berfungsi normal 
            Action kosong karena kita asumsikan handle via JS atau default submit 
        --}}
        <form id="form-lkh">
            <input type="hidden" name="status" id="status_input" value="draft">
            <div class="space-y-4">

                {{-- Row 1: Tanggal + Jenis Kegiatan --}}
                <div class="grid md:grid-cols-2 gap-4">
                    {{-- Tanggal --}}
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Tanggal</label>
                        <div class="relative">
                            <input id="tanggal_lkh" name="tanggal_laporan" type="date" class="tanggal-placeholder w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                          px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                          focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                            <button type="button" id="tanggal_lkh_btn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/tanggal.svg') }}" alt="Pilih tanggal"
                                    class="h-4 w-4 opacity-80" />
                            </button>
                        </div>
                    </div>

                    {{-- Jenis Kegiatan (SUDAH DIPERBAIKI: Value Title Case) --}}
                    <div x-data="{
                            open: false,
                            value: '',
                            label: 'Pilih Jenis Kegiatan',
                            options: [
                                { value: 'Rapat',              label: 'Rapat' },
                                { value: 'Pelayanan Publik',   label: 'Pelayanan Publik' },
                                { value: 'Penyusunan Dokumen', label: 'Penyusunan Dokumen' },
                                { value: 'Kunjungan Lapangan', label: 'Kunjungan Lapangan' },
                                { value: 'Lainnya',            label: 'Lainnya' },
                            ],
                            select(opt) { this.value = opt.value; this.label = opt.label; this.open = false; },
                        }">
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Jenis Kegiatan</label>
                        <input type="hidden" name="jenis_kegiatan" x-model="value">

                        <div class="relative">
                            <button type="button" @click="open = !open"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                           px-3.5 py-2.5 text-sm pr-3 text-left flex items-center justify-between
                                           focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                                :class="value === '' ? 'text-slate-400' : 'text-slate-700'">
                                <span x-text="label"></span>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70"
                                    alt="">
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition
                                class="absolute z-20 mt-1 w-full rounded-[10px] bg-white shadow-lg border border-slate-200 py-1">
                                <template x-for="opt in options" :key="opt.value">
                                    <button type="button"
                                        class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50 flex items-center justify-between"
                                        :class="opt.value === value ? 'text-[#1C7C54] font-medium' : 'text-slate-700'"
                                        @click="select(opt)">
                                        <span x-text="opt.label"></span>
                                        <span x-show="opt.value === value" class="text-xs">✓</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Referensi Tupoksi (SUDAH DIPERBAIKI: Dynamic Fetching) --}}
                <div x-data="{
                    open: false,
                    value: '',
                    label: 'Pilih Referensi Tupoksi',
                    options: [],
                    isLoading: false,
                    async init() {
                        this.isLoading = true;
                        try {
                            const token = localStorage.getItem('auth_token'); 
                            const response = await fetch('/api/lkh/referensi', {
                                headers: {
                                    'Authorization': `Bearer ${token}`,
                                    'Accept': 'application/json'
                                }
                            });
                            if(!response.ok) throw new Error('HTTP error ' + response.status);
                            const data = await response.json();

                            // Pastikan key sesuai JSON
                            if(data.tupoksi && Array.isArray(data.tupoksi)) {
                                this.options = data.tupoksi.map(item => ({
                                    value: item.id,
                                    label: item.uraian_tugas
                                }));
                                if(this.options.length === 0) this.label = 'Data Tupoksi Kosong';
                            } else {
                                this.label = 'Format data tidak valid';
                            }
                        } catch (error) {
                            console.error(error);
                            this.label = 'Gagal memuat data';
                        } finally {
                            this.isLoading = false;
                        }
                    },
                    select(opt) { this.value = opt.value; this.label = opt.label; this.open = false; },
                }" x-init="init()">

                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Referensi Tupoksi</label>
                    <input type="hidden" name="tupoksi_id" x-model="value">

                    <div class="relative">
                        <button type="button" @click="open = !open" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                px-3.5 py-2.5 text-sm pr-3 text-left flex items-center justify-between
                                focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                            :class="value === '' ? 'text-slate-400' : 'text-slate-700'">
                            <span x-text="isLoading ? 'Memuat data...' : label" class="truncate mr-2"></span>
                            <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                                class="h-4 w-4 opacity-70 flex-shrink-0" alt="">
                        </button>

                        <div x-show="open" @click.outside="open = false" x-transition
                            class="absolute z-20 mt-1 w-full rounded-[10px] bg-white shadow-lg border border-slate-200 py-1 max-h-60 overflow-y-auto">
                            <template x-for="opt in options" :key="opt.value">
                                <button type="button"
                                    class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50 flex items-center justify-between gap-2"
                                    :class="opt.value === value ? 'text-[#1C7C54] font-medium' : 'text-slate-700'"
                                    @click="select(opt)">
                                    <span x-text="opt.label" class="line-clamp-2"></span>
                                    <span x-show="opt.value === value" class="text-xs flex-shrink-0">✓</span>
                                </button>
                            </template>
                            <div x-show="options.length === 0 && !isLoading"
                                class="px-3.5 py-2 text-sm text-slate-400 italic">Data kosong</div>
                        </div>
                    </div>
                </div>


                {{-- Row 3: Uraian Kegiatan --}}
                <div>
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Uraian Kegiatan</label>
                    {{-- [FIX] Name ditambahkan --}}
                    <textarea name="deskripsi_aktivitas" rows="3" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                     px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2
                                     focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                        placeholder="Tulis uraian kegiatan yang dilakukan..."></textarea>
                </div>

                {{-- 
                    [MODIFIKASI BESAR] Row 4 & 5: Output, Volume, Satuan, Kategori, dan SKP 
                    Kita bungkus dalam satu x-data besar agar logika Kategori & SKP terhubung 
                --}}
                <div x-data="{
                    kategori: 'non-skp',
                    skpId: '',
                    skpLabel: 'Pilih Target SKP',
                    skpOptions: [],
                    skpLoading: false,
                    satuanValue: '',
                    satuanLabel: 'Satuan',
                    satuanOpen: false,
                    kategoriOpen: false,
                    skpOpen: false,

                    // Fungsi fetch SKP
                    async fetchSkp() {
                        this.skpLoading = true;
                        try {
                            const token = localStorage.getItem('auth_token');
                            const response = await fetch('/api/skp?year=' + new Date().getFullYear(), {
                                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                            });
                            const res = await response.json();
                            if(res.data) {
                                this.skpOptions = res.data.map(s => ({ value: s.id, label: s.rencana_aksi }));
                            }
                        } catch (e) { console.error(e); } finally { this.skpLoading = false; }
                    },
                    // Saat kategori berubah
                    setKategori(val) {
                        this.kategori = val;
                        this.kategoriOpen = false;
                        if (val === 'skp' && this.skpOptions.length === 0) {
                            this.fetchSkp();
                        }
                    }
                }">

                    {{-- Baris Atas: Output, Volume, Satuan, Kategori --}}
                    <div
                        class="grid gap-4 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)] items-start">

                        {{-- Output --}}
                        <div>
                            <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Output</label>
                            <input type="text" name="output_hasil_kerja"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                          px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                                placeholder="Contoh: Notulensi">
                        </div>

                        {{-- Volume --}}
                        <div>
                            <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Volume</label>
                            <input type="number" name="volume"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                          px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                                placeholder="0">
                        </div>

                        {{-- Satuan --}}
                        <div class="relative">
                            <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Satuan</label>
                            <input type="hidden" name="satuan" x-model="satuanValue">

                            <button type="button" @click="satuanOpen = !satuanOpen" @click.outside="satuanOpen = false"
                                class="w-full flex items-center justify-between rounded-[10px] border border-slate-200 bg-slate-50/60 pl-3.5 pr-3 py-2.5 text-sm text-left focus:ring-2 focus:ring-[#1C7C54]/30">
                                <span x-text="satuanValue ? satuanValue : 'Satuan'"
                                    :class="!satuanValue ? 'text-[#9CA3AF]' : 'text-slate-700'" class="truncate"></span>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                                    class="h-4 w-4 opacity-70 ml-2 flex-shrink-0" alt="" />
                            </button>

                            <div x-show="satuanOpen" x-transition
                                class="absolute left-0 mt-1 w-full rounded-[10px] border border-slate-200 bg-white shadow-lg z-20 overflow-hidden">
                                <template x-for="opt in ['Jam', 'Dokumen', 'Kegiatan']">
                                    <button type="button" @click="satuanValue = opt; satuanOpen = false"
                                        class="w-full px-3.5 py-2 text-sm text-left hover:bg-slate-50">
                                        <span x-text="opt"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Kategori (Trigger Logika SKP) --}}
                        <div class="relative">
                            <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Kategori</label>

                            <button type="button" @click="kategoriOpen = !kategoriOpen"
                                @click.outside="kategoriOpen = false"
                                class="w-full flex items-center justify-between rounded-[10px] border border-slate-200 bg-slate-50/60 pl-3.5 pr-3 py-2.5 text-sm text-left focus:ring-2 focus:ring-[#1C7C54]/30">
                                <span x-text="kategori === 'skp' ? 'SKP' : 'Non-SKP'" class="text-slate-700"></span>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70 ml-2"
                                    alt="" />
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

                    {{-- Dropdown Tambahan: PILIH SKP (Hanya muncul jika Kategori = SKP) --}}
                    <div x-show="kategori === 'skp'" x-transition class="mt-4">
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Target SKP</label>
                        <input type="hidden" name="skp_id" x-model="skpId">

                        <div class="relative">
                            <button type="button" @click="skpOpen = !skpOpen" @click.outside="skpOpen = false"
                                class="w-full flex items-center justify-between rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm text-left focus:ring-2 focus:ring-[#1C7C54]/30">
                                <span x-text="skpLoading ? 'Memuat data...' : skpLabel"
                                    class="truncate text-slate-700"></span>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70 ml-2"
                                    alt="">
                            </button>

                            <div x-show="skpOpen" x-transition
                                class="absolute z-20 mt-1 w-full rounded-[10px] bg-white shadow-lg border border-slate-200 max-h-60 overflow-y-auto">
                                <template x-for="opt in skpOptions" :key="opt.value">
                                    <button type="button"
                                        @click="skpId = opt.value; skpLabel = opt.label; skpOpen = false"
                                        class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50 border-b border-slate-100 last:border-0">
                                        <span x-text="opt.label" class="line-clamp-2"></span>
                                    </button>
                                </template>
                                <div x-show="skpOptions.length === 0 && !skpLoading"
                                    class="p-2 text-xs text-slate-400 text-center">
                                    Tidak ada data SKP.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 6: Jam Mulai + Jam Selesai --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Jam Mulai</label>
                        <div class="relative">
                            <input id="jam_mulai" name="waktu_mulai" type="time"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                          px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                          focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none time-placeholder">
                            <button type="button" id="jam_mulai_btn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/time.svg') }}"
                                    class="h-4 w-4 opacity-70 pointer-events-none" alt="Time">
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Jam Selesai</label>
                        <div class="relative">
                            <input id="jam_selesai" name="waktu_selesai" type="time"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                          px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                          focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none time-placeholder">
                            <button type="button" id="jam_selesai_btn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/time.svg') }}"
                                    class="h-4 w-4 opacity-70 pointer-events-none" alt="Time">
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Row 7: Unggah Bukti + [MODIFIKASI BESAR] Geolocation --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Unggah Bukti</label>
                        <label class="w-full flex items-center justify-between rounded-[10px]
                                       border border-dashed border-slate-300 bg-slate-50/60
                                       px-3.5 py-2.5 text-sm text-slate-500 cursor-pointer hover:bg-slate-100">
                            <span class="truncate">Pilih File</span>
                            <img src="{{ asset('assets/icon/upload.svg') }}" class="h-4 w-4 opacity-70" alt="Upload">
                            <input type="file" name="bukti[]" multiple class="hidden">
                        </label>
                    </div>

                    {{-- Input Lokasi dengan Geolocation --}}
                    <div x-data="{
                        lat: '',
                        lng: '',
                        status: 'Klik tombol untuk ambil lokasi',
                        loading: false,
                        getLocation() {
                            this.loading = true;
                            this.status = 'Mencari koordinat...';
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(
                                    (position) => {
                                        this.lat = position.coords.latitude;
                                        this.lng = position.coords.longitude;
                                        this.status = 'Terkunci: ' + this.lat.toFixed(5) + ', ' + this.lng.toFixed(5);
                                        this.loading = false;
                                    },
                                    (error) => {
                                        this.status = 'Gagal: Izin lokasi ditolak/error.';
                                        this.loading = false;
                                    }
                                );
                            } else {
                                this.status = 'Browser tidak support GPS.';
                                this.loading = false;
                            }
                        }
                    }">
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Lokasi</label>

                        {{-- Hidden Input untuk Backend --}}
                        <input type="hidden" name="latitude" x-model="lat">
                        <input type="hidden" name="longitude" x-model="lng">

                        <div class="flex gap-2">
                            {{-- Input Visual (Readonly) --}}
                            <input type="text" class="w-full rounded-[10px] border border-slate-200 bg-slate-100
                                          px-3.5 py-2.5 text-sm text-slate-600 focus:outline-none cursor-not-allowed"
                                x-model="status" readonly>

                            {{-- Tombol Trigger GPS --}}
                            <button type="button" @click="getLocation()"
                                class="shrink-0 bg-[#1C7C54] hover:bg-[#156a44] text-white rounded-[10px] w-10 flex items-center justify-center transition-colors"
                                :disabled="loading">
                                {{-- Icon Maps/Pin --}}
                                <img src="{{ asset('assets/icon/location.svg') }}"
                                    class="h-5 w-5 filter brightness-0 invert" alt="GPS">
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">*Pastikan izin lokasi browser aktif.</p>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="submitForm('draft')"
                        class="rounded-[10px] bg-[#155FA6] px-4 py-2 text-sm font-normal text-white">
                        Simpan Draft
                    </button>
                    <button type="button" onclick="submitForm('waiting_review')"
                        class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm font-normal text-white hover:bg-[#0b633b]">
                        Kirim LKH
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- KANAN ATAS: PANDUAN SINGKAT --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col h-full">
        <h3 class="text-sm font-semibold text-slate-800">Panduan Singkat</h3>

        <div class="mt-3 space-y-2 flex-1 overflow-y-auto pr-1">
            @foreach ([
            ['title' => 'Tanggal', 'desc' => 'Pilih tanggal kegiatan dilakukan, bukan tanggal pengisian.'],
            ['title' => 'Jenis Kegiatan', 'desc' => 'Pilih jenis kegiatan yang dilakukan.'],
            ['title' => 'Referensi Tupoksi', 'desc' => 'Pilih jenis tupoksi yang sesuai.'],
            ['title' => 'Uraian Kegiatan', 'desc' => 'Isi dengan kalimat yang ringkas dan jelas.'],
            ['title' => 'Output', 'desc' => 'Sebutkan hasil nyata dari kegiatan.'],
            ['title' => 'Volume', 'desc' => 'Masukkan jumlah output kegiatan yang sesuai.'],
            ['title' => 'Satuan', 'desc' => 'Pilih satuan yang sesuai dengan output kegiatan.'],
            ['title' => 'Kategori', 'desc' => 'Pilih kategori SKP atau Non-SKP.'],
            ['title' => 'Jam Mulai & Jam Selesai', 'desc' => 'Isi jam mulai dan jam selesai kegiatan.'],
            ['title' => 'Unggah Bukti', 'desc' => 'Unggah bukti foto/dokumen kegiatan.'],
            ['title' => 'Lokasi', 'desc' => 'Sistem akan otomatis membaca lokasi Anda.'],
            ] as $guide)
            <div class="rounded-[10px] bg-[#155FA6] px-3 py-2.5 text-white text-xs leading-snug">
                <p class="text-[13px] font-semibold">{{ $guide['title'] }}</p>
                <p class="mt-[2px] text-[11px] text-white/90">{{ $guide['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>


    {{-- KIRI BAWAH: DRAFT LKH --}}
    <div x-data="{ 
            openDraftModal: false, 
            drafts: [] 
        }" @update-drafts.window="drafts = $event.detail" x-cloak
        class="rounded-2xl bg-white ring-1 ring-slate-200 px-4 py-3 shadow-sm h-full flex flex-col">

        {{-- HEADER CARD --}}
        <div class="flex items-center justify-between mb-3 shrink-0">
            <h3 class="text-[15px] font-medium text-slate-800">Draft LKH</h3>
            {{-- Tombol hanya muncul jika ada draft --}}
            <button type="button" x-show="drafts.length > 0"
                class="text-[11px] text-[#0E7A4A] font-medium hover:underline" @click="openDraftModal = true">
                Lihat Semua (<span x-text="drafts.length"></span>)
            </button>
        </div>

        {{-- LIST PREVIEW (Diisi oleh JavaScript native via ID - Maksimal 3) --}}
        <div id="draft-list" class="space-y-3 flex-1 overflow-y-auto pr-1">
            <p class="text-sm text-slate-400 italic">Memuat draft...</p>
        </div>

        {{-- MODAL DRAFT LENGKAP --}}
        <div x-show="openDraftModal" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">

            {{-- Background gelap --}}
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openDraftModal = false"></div>

            {{-- Card modal --}}
            <div class="relative z-10 w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[85vh]">

                {{-- Header Modal --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                    <h2 class="text-lg font-semibold text-slate-800">
                        Semua Draft Laporan
                    </h2>
                    <button type="button"
                        class="h-8 w-8 flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 transition-colors"
                        @click="openDraftModal = false">
                        <span class="text-slate-500 text-xl leading-none">&times;</span>
                    </button>
                </div>

                {{-- Isi Modal (Scrollable & Full List) --}}
                <div class="overflow-y-auto p-6 space-y-3">
                    <template x-for="item in drafts" :key="item.id">
                        <!-- TAMPILAN SAMA PERSIS DENGAN YANG DILUAR -->
                        <div
                            class="bg-[#F8F9FA] rounded-[12px] p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 border border-slate-100">
                            <div>
                                <h4 class="text-[12px] font-medium text-slate-900" x-text="item.deskripsi"></h4>
                                <p class="text-[10px] text-slate-500 mt-1" x-text="item.waktu_simpan"></p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a :href="'input-lkh/' + item.id"
                                    class="bg-[#0E7A4A] hover:bg-[#0b633b] text-white text-[12px] font-medium px-2 py-1 rounded-[8px] transition text-center">
                                    Lanjutkan
                                </a>
                                <button type="button" @click="deleteDraft(item.id)"
                                    class="bg-[#B6241C] hover:bg-[#8f1e17] text-white text-[12px] font-medium px-2 py-1 rounded-[8px] transition">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Empty State di Modal --}}
                    <div x-show="drafts.length === 0" class="text-center py-10 text-slate-400">
                        Tidak ada draft tersimpan saat ini.
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="px-6 py-3 bg-slate-50 rounded-b-2xl border-t border-slate-100 text-right shrink-0">
                    <button @click="openDraftModal = false"
                        class="text-xs text-slate-500 hover:text-slate-700 font-medium">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- KANAN BAWAH: STATUS LAPORAN TERAKHIR --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 shadow-sm">
        <h3 class="text-[18px] font-medium text-slate-800 mb-5">
            Status Laporan Terakhir
        </h3>

        <ul class="space-y-3" id="aktivitas-list">
            {{-- Diisi via JS --}}
            <li class="text-sm text-slate-400 italic">Memuat aktivitas...</li>
        </ul>
    </div>
</section>

@push('scripts')
<script>
// Variabel Global untuk menyimpan ID jika sedang mode edit
const lkhIdToEdit = "{{ $id ?? '' }}";

document.addEventListener("DOMContentLoaded", async function() {
    const token = localStorage.getItem("auth_token");

    // Headers dasar
    const headers = {
        "Accept": "application/json"
    };
    if (token) headers["Authorization"] = "Bearer " + token;

    // ==========================================
    // 1. LOGIKA DASHBOARD & DRAFT (Existing)
    // ==========================================
    try {
        const response = await fetch("http://127.0.0.1:8000/api/dashboard/stats", {
            method: "GET",
            headers: headers
        });

        if (response.ok) {
            const data = await response.json();

            // A. RENDER AKTIVITAS TERKINI
            const listContainer = document.getElementById("aktivitas-list");
            listContainer.innerHTML = "";
            const aktivitas = data.aktivitas_terbaru || [];

            const iconPaths = {
                pending: "{{ asset('assets/icon/pending.svg') }}",
                approve: "{{ asset('assets/icon/approve.svg') }}",
                reject: "{{ asset('assets/icon/reject.svg') }}"
            };

            if (aktivitas.length === 0) {
                listContainer.innerHTML =
                    '<li class="text-sm text-slate-500">Belum ada aktivitas terbaru.</li>';
            } else {
                aktivitas.forEach(item => {
                    const dateObj = new Date(item.tanggal_laporan);
                    const tanggalFormatted = dateObj.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });

                    let tone = 'bg-slate-200';
                    let iconUrl = iconPaths.pending;
                    let statusLabel = item.status;

                    if (item.status === 'approved') {
                        tone = 'bg-[#128C60]/50';
                        iconUrl = iconPaths.approve;
                        statusLabel = 'Disetujui';
                    } else if (item.status === 'rejected' || item.status.includes('reject')) {
                        tone = 'bg-[#B6241C]/50';
                        iconUrl = iconPaths.reject;
                        statusLabel = 'Ditolak';
                    } else if (item.status === 'waiting_review') {
                        tone = 'bg-[#D8A106]/50';
                        iconUrl = iconPaths.pending;
                        statusLabel = 'Menunggu Review';
                    }

                    const htmlItem = `
                        <li class="flex items-start gap-3">
                            <div class="h-8 w-8 rounded-[10px] flex items-center justify-center flex-shrink-0 ${tone}">
                                <img src="${iconUrl}" class="h-5 w-5 opacity-90" alt="${statusLabel}">
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <div class="text-[13px] font-medium leading-snug truncate" style="max-width: 250px;" title="${item.deskripsi_aktivitas}">
                                    ${item.deskripsi_aktivitas}
                                </div>
                                <div class="flex justify-between mt-[2px]">
                                    <span class="text-xs text-slate-500 capitalize">${statusLabel}</span>
                                    <span class="text-xs text-slate-500 whitespace-nowrap">${tanggalFormatted}</span>
                                </div>
                            </div>
                        </li>`;
                    listContainer.insertAdjacentHTML('beforeend', htmlItem);
                });
            }

            // B. RENDER DRAFT (SIDEBAR & MODAL)
            const draftContainer = document.getElementById("draft-list");
            draftContainer.innerHTML = "";
            const rawDrafts = data.draft_terbaru || [];

            const processedDrafts = rawDrafts.map(item => {
                const d = new Date(item.updated_at);
                return {
                    id: item.id,
                    deskripsi: item.deskripsi_aktivitas || 'Draft tanpa judul',
                    waktu_simpan: `Disimpan: ${d.toLocaleDateString('id-ID', {day:'numeric', month:'long'})} | ${d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'})}`
                };
            });

            if (processedDrafts.length === 0) {
                draftContainer.innerHTML =
                    '<div class="p-4 text-sm text-slate-500 bg-slate-50 rounded-lg text-center">Tidak ada draft.</div>';
            } else {
                processedDrafts.slice(0, 3).forEach(item => {
                    const html = `
                        <div class="bg-[#F8F9FA] rounded-[12px] p-4 flex items-center justify-between gap-3 border border-slate-100">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-[12px] font-medium text-slate-900 truncate" title="${item.deskripsi}">${item.deskripsi}</h4>
                                <p class="text-[10px] text-slate-500 mt-1">${item.waktu_simpan}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="/staf/input-lkh/${item.id}" class="bg-[#0E7A4A] hover:bg-[#0b633b] text-white text-[12px] font-medium px-3 py-1.5 rounded-[8px] transition">Lanjutkan</a>
                            </div>
                            <button type="button" onclick="deleteDraft('${item.id}')"
                            class="bg-[#B6241C] hover:bg-[#8f1e17] text-white text-[12px] font-medium px-3 py-1.5 rounded-[8px] transition">
                                Hapus
                            </button>
                        </div>`;
                    draftContainer.insertAdjacentHTML('beforeend', html);
                });
            }

            // Kirim data ke Alpine Modal
            window.dispatchEvent(new CustomEvent('update-drafts', {
                detail: processedDrafts
            }));
        }
    } catch (err) {
        console.error("Gagal load dashboard stats:", err);
    }

    // ==========================================
    // 2. LOGIKA EDIT (Load Data ke Form)
    // ==========================================
    if (lkhIdToEdit) {
        console.log("Mode Edit ID:", lkhIdToEdit);
        document.querySelector('h2').innerText = "Edit LKH (Memuat...)";

        try {
            const resEdit = await fetch(`/api/lkh/${lkhIdToEdit}`, {
                method: "GET",
                headers: headers
            });

            if (!resEdit.ok) throw new Error("Gagal ambil detail LKH");

            const json = await resEdit.json();
            const data = json.data;

            // A. Isi Input Biasa
            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.value = val;
            };
            setVal('tanggal_lkh', data.tanggal_laporan);
            setVal('jam_mulai', data.waktu_mulai);
            setVal('jam_selesai', data.waktu_selesai);
            document.querySelector('textarea[name="deskripsi_aktivitas"]').value = data.deskripsi_aktivitas;
            document.querySelector('input[name="output_hasil_kerja"]').value = data.output_hasil_kerja;
            document.querySelector('input[name="volume"]').value = data.volume;

            // B. Isi Koordinat (Hidden)
            if (data.latitude) setAlpineValue('input[name="latitude"]', 'lat', data.latitude);
            if (data.longitude) setAlpineValue('input[name="longitude"]', 'lng', data.longitude);

            // C. Update Dropdown Alpine
            updateAlpineDropdown('jenis_kegiatan', data.jenis_kegiatan);
            updateAlpineDropdown('satuan', data.satuan);
            updateAlpineDropdown('tupoksi_id', data.tupoksi_id, data.tupoksi_label || 'Tupoksi Terpilih');

            // D. Update Kategori & SKP
            const skpEl = document.querySelector('input[name="skp_id"]');
            if (skpEl) {
                const wrapper = skpEl.closest('[x-data]');
                if (wrapper) {
                    const scope = Alpine.$data(wrapper);
                    if (data.skp_id) {
                        scope.setKategori('skp');
                        scope.skpId = data.skp_id;
                        scope.skpLabel = data.skp_label || 'SKP Terpilih';
                    } else {
                        scope.setKategori('non-skp');
                    }
                }
            }

            document.querySelector('h2').innerText = "Edit LKH";

        } catch (e) {
            console.error(e);
            alert("Gagal memuat data edit.");
        }
    }
});

// ==========================================
// 3. FUNGSI HELPER
// ==========================================

// Helper untuk update dropdown Alpine sederhana
function updateAlpineDropdown(inputName, value, label = null) {
    const el = document.querySelector(`input[name="${inputName}"]`);
    if (el) {
        const wrapper = el.closest('[x-data]');
        if (wrapper) {
            const scope = Alpine.$data(wrapper);
            scope.value = value;
            scope.label = label || value;
        }
    }
}

// Helper khusus untuk koordinat
function setAlpineValue(selector, key, value) {
    const el = document.querySelector(selector);
    if (el && el.closest('[x-data]')) {
        Alpine.$data(el.closest('[x-data]'))[key] = value;
    }
}

// ==========================================
// 4. FUNGSI SUBMIT (Global & Dinamis)
// ==========================================
// Panggil fungsi ini di onclick tombol HTML: onclick="submitForm('draft')"
async function submitForm(statusType) {
    if (event) event.preventDefault();

    const token = localStorage.getItem("auth_token");
    const form = document.getElementById('form-lkh');
    const formData = new FormData(form);

    // SET STATUS
    formData.set("status", statusType);

    // ====================================================
    // 🔥 VALIDASI FORM KOSONG (Hanya untuk "Kirim LKH")
    // ====================================================
    if (statusType === "waiting_review") {

        const requiredFields = [
            "tanggal_laporan",
            "jenis_kegiatan",
            "tupoksi_id",
            "deskripsi_aktivitas",
            "output_hasil_kerja",
            "volume",
            "satuan",
            "waktu_mulai",
            "waktu_selesai"
        ];

        let missing = [];

        requiredFields.forEach(name => {
            const val = formData.get(name);
            if (!val || val.trim() === "") missing.push(name);
        });

        if (missing.length > 0) {
            return Swal.fire({
                icon: "warning",
                title: "Form Belum Lengkap",
                text: "Masih ada beberapa field yang harus diisi sebelum mengirim LKH.",
                confirmButtonText: "Mengerti",
                confirmButtonColor: "#155FA6"
            });
        }
    }

    // ====================================================
    // 🔥 VALIDASI DRAFT (Minimal 1 field terisi)
    // ====================================================
    if (statusType === "draft") {
        const minimalField = [
            "deskripsi_aktivitas",
            "output_hasil_kerja",
            "tanggal_laporan"
        ];

        const isEmpty = minimalField.every(name => {
            const val = formData.get(name);
            return !val || val.trim() === "";
        });

        if (isEmpty) {
            return Swal.fire({
                icon: "info",
                title: "Tidak Ada Data",
                text: "Tidak ada data yang diisi. Isi minimal satu field untuk menyimpan draft.",
                confirmButtonText: "Oke",
                confirmButtonColor: "#155FA6"
            });
        }
    }

    // ====================================================
    // 🔥 MODE: CREATE / UPDATE
    // ====================================================
    let url = '/api/lkh';

    if (typeof lkhIdToEdit !== 'undefined' && lkhIdToEdit) {
        url = `/api/lkh/update/${lkhIdToEdit}`;
    }

    // ====================================================
    // 🔥 KIRIM KE BACKEND
    // ====================================================
    try {
        const btn = event.target;
        const oldText = btn.innerText;
        btn.innerText = "Memproses...";
        btn.disabled = true;

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                "Authorization": `Bearer ${token}`,
                "Accept": "application/json"
            },
            body: formData
        });

        const json = await response.json();

        // ==========================
        // 🔥 RESPONSE BERHASIL
        // ==========================
        if (response.ok) {
            if (statusType === "draft") {

                Swal.fire({
                    icon: "success",
                    title: "Draft Disimpan",
                    text: "Draft LKH berhasil disimpan.",
                    timer: 1800,
                    showConfirmButton: false
                });

            } else if (statusType === "waiting_review") {

                Swal.fire({
                    icon: "success",
                    title: "LKH Terkirim!",
                    text: "LKH berhasil dikirim ke Penilai.",
                    timer: 1500,
                    showConfirmButton: false
                });

            }

            setTimeout(() => window.location.href = "/staf/dashboard", 1200);
            return;
        }

        // ==========================
        // 🔥 RESPONSE GAGAL
        // ==========================
        Swal.fire({
            icon: "error",
            title: "Gagal Menyimpan",
            text: json.message || "Terjadi kesalahan saat menyimpan data.",
            confirmButtonText: "Coba Lagi",
            confirmButtonColor: "#B6241C"
        });

        btn.disabled = false;
        btn.innerText = oldText;

    } catch (err) {
        console.error(err);

        Swal.fire({
            icon: "error",
            title: "Kesalahan Koneksi",
            text: "Periksa koneksi internet Anda.",
            confirmButtonText: "Mengerti"
        });

        if (event && event.target) {
            const btn = event.target;
            btn.disabled = false;
            btn.innerText = "Coba Lagi";
        }
    }
}


// === TOMBOL DATE ===
const btnTanggal = document.getElementById("tanggal_lkh_btn");
const inputTanggal = document.getElementById("tanggal_lkh");

if (btnTanggal && inputTanggal) {
    btnTanggal.addEventListener("click", (e) => {
        e.preventDefault();
        inputTanggal.showPicker ? inputTanggal.showPicker() : inputTanggal.click();
    });
}

// === TOMBOL JAM MULAI ===
const btnJamMulai = document.getElementById("jam_mulai_btn");
const inputJamMulai = document.getElementById("jam_mulai");

if (btnJamMulai && inputJamMulai) {
    btnJamMulai.addEventListener("click", (e) => {
        e.preventDefault();
        inputJamMulai.showPicker ? inputJamMulai.showPicker() : inputJamMulai.click();
    });
}

// === TOMBOL JAM SELESAI ===
const btnJamSelesai = document.getElementById("jam_selesai_btn");
const inputJamSelesai = document.getElementById("jam_selesai");

if (btnJamSelesai && inputJamSelesai) {
    btnJamSelesai.addEventListener("click", (e) => {
        e.preventDefault();
        inputJamSelesai.showPicker ? inputJamSelesai.showPicker() : inputJamSelesai.click();
    });
}
</script>
@endpush

@endsection