@php($title = 'Input LKH')
@extends('layouts.app', ['title' => $title, 'role' => 'penilai', 'active' => 'input-lkh'])

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
                                <img src="{{ asset('assets/icon/tanggal.svg') }}" alt="Pilih tanggal" class="h-4 w-4 opacity-80" />
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
                            <button type="button" @click="open = !open" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                           px-3.5 py-2.5 text-sm pr-9 text-left flex items-center justify-between
                                           focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                                :class="value === '' ? 'text-slate-400' : 'text-slate-700'">
                                <span x-text="label"></span>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70" alt="">
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
                        <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70 flex-shrink-0" alt="">
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
                        <div x-show="options.length === 0 && !isLoading" class="px-3.5 py-2 text-sm text-slate-400 italic">Data kosong</div>
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
                    <div class="grid gap-4 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)] items-start">
                        
                        {{-- Output --}}
                        <div>
                            <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Output</label>
                            <input type="text" name="output_hasil_kerja" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                          px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                                placeholder="Contoh: Notulensi">
                        </div>

                        {{-- Volume --}}
                        <div>
                            <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Volume</label>
                            <input type="number" name="volume" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                          px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" 
                                placeholder="0">
                        </div>

                        {{-- Satuan --}}
                        <div class="relative">
                            <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Satuan</label>
                            <input type="hidden" name="satuan" x-model="satuanValue">
                            
                            <button type="button" @click="satuanOpen = !satuanOpen" @click.outside="satuanOpen = false" 
                                class="w-full flex items-center justify-between rounded-[10px] border border-slate-200 bg-slate-50/60 pl-3.5 pr-3 py-2.5 text-sm text-left focus:ring-2 focus:ring-[#1C7C54]/30">
                                <span x-text="satuanValue ? satuanValue : 'Satuan'" :class="!satuanValue ? 'text-[#9CA3AF]' : 'text-slate-700'" class="truncate"></span>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70 ml-2 flex-shrink-0" alt="" />
                            </button>

                            <div x-show="satuanOpen" x-transition class="absolute left-0 mt-1 w-full rounded-[10px] border border-slate-200 bg-white shadow-lg z-20 overflow-hidden">
                                <template x-for="opt in ['Jam', 'Dokumen', 'Kegiatan']">
                                    <button type="button" @click="satuanValue = opt; satuanOpen = false" class="w-full px-3.5 py-2 text-sm text-left hover:bg-slate-50">
                                        <span x-text="opt"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Kategori (Trigger Logika SKP) --}}
                        <div class="relative">
                            <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Kategori</label>
                            
                            <button type="button" @click="kategoriOpen = !kategoriOpen" @click.outside="kategoriOpen = false" 
                                class="w-full flex items-center justify-between rounded-[10px] border border-slate-200 bg-slate-50/60 pl-3.5 pr-3 py-2.5 text-sm text-left focus:ring-2 focus:ring-[#1C7C54]/30">
                                <span x-text="kategori === 'skp' ? 'SKP' : 'Non-SKP'" class="text-slate-700"></span>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70 ml-2" alt="" />
                            </button>

                            <div x-show="kategoriOpen" x-transition class="absolute z-20 mt-1 w-full rounded-[10px] bg-white shadow-lg border border-slate-200 overflow-hidden">
                                <button type="button" @click="setKategori('skp')" class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50">SKP</button>
                                <button type="button" @click="setKategori('non-skp')" class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50">Non-SKP</button>
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
                                <span x-text="skpLoading ? 'Memuat data...' : skpLabel" class="truncate text-slate-700"></span>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="h-4 w-4 opacity-70 ml-2" alt="">
                            </button>

                            <div x-show="skpOpen" x-transition class="absolute z-20 mt-1 w-full rounded-[10px] bg-white shadow-lg border border-slate-200 max-h-60 overflow-y-auto">
                                <template x-for="opt in skpOptions" :key="opt.value">
                                    <button type="button" @click="skpId = opt.value; skpLabel = opt.label; skpOpen = false"
                                        class="w-full text-left px-3.5 py-2 text-sm hover:bg-slate-50 border-b border-slate-100 last:border-0">
                                        <span x-text="opt.label" class="line-clamp-2"></span>
                                    </button>
                                </template>
                                <div x-show="skpOptions.length === 0 && !skpLoading" class="p-2 text-xs text-slate-400 text-center">
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
                            <input id="jam_mulai" name="waktu_mulai" type="time" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                          px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                          focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none time-placeholder">
                            <button type="button" id="jam_mulai_btn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/time.svg') }}" class="h-4 w-4 opacity-70 pointer-events-none" alt="Time">
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Jam Selesai</label>
                        <div class="relative">
                            <input id="jam_selesai" name="waktu_selesai" type="time" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                          px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                          focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none time-placeholder">
                            <button type="button" id="jam_selesai_btn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/time.svg') }}" class="h-4 w-4 opacity-70 pointer-events-none" alt="Time">
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
                                <img src="{{ asset('assets/icon/location.svg') }}" class="h-5 w-5 filter brightness-0 invert" alt="GPS">
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">*Pastikan izin lokasi browser aktif.</p>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" class="rounded-[10px] bg-[#155FA6] px-4 py-2 text-sm font-normal text-white">
                        Simpan Draft
                    </button>
                    <button type="submit" class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm font-normal text-white hover:bg-[#0b633b]">
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
        drafts: [
            { title: 'Rapat Koordinasi Pendapatan', saved_at: 'Disimpan: 06 November 2025 | 15:13' },
            { title: 'Rapat Koordinasi Pajak',      saved_at: 'Disimpan: 09 November 2025 | 10:15' },
            { title: 'Kunjungan Lapangan',          saved_at: 'Disimpan: 10 November 2025 | 12:30' },
            { title: 'Pelayanan Masyarakat',        saved_at: 'Disimpan: 12 November 2025 | 09:20' },
            { title: 'Perjalanan Dinas',            saved_at: 'Disimpan: 15 November 2025 | 10:10' },
            { title: 'Rapat Koordinasi Internal',   saved_at: 'Disimpan: 20 November 2025 | 14:00' },
            { title: 'Rapat Bidang',                saved_at: 'Disimpan: 22 November 2025 | 15:40' },
            { title: 'Kunjungan Kerja',             saved_at: 'Disimpan: 24 November 2025 | 13:40' },
            { title: 'Pelayanan Masyarakat',        saved_at: 'Disimpan: 28 November 2025 | 16:40' },
        ],
    }" x-cloak class="rounded-2xl bg-white ring-1 ring-slate-200 px-4 py-3">
        {{-- CARD KECIL DI HALAMAN --}}
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-[15px] font-normal text-black">Draft LKH</h3>
            <button type="button" class="text-[11px] text-slate-500 hover:underline" @click="openDraftModal = true">
                Lihat Semua Draft
            </button>
        </div>

        <div class="rounded-xl bg-[#F1F5F9] px-3 py-2.5 flex items-center justify-between text-xs">
            <div>
                <div class="font-medium text-slate-800">Rapat Koordinasi Pajak</div>
                <div class="mt-[2px] text-[11px] text-slate-500">
                    Disimpan: 09 November 2025 | 10:15
                </div>
            </div>
            <div class="flex items-center gap-2 ml-2">
                <button
                    class="rounded-[6px] bg-[#0E7A4A] text-white text-[11px] px-3 py-[4px] leading-none hover:brightness-95">
                    Lanjutkan
                </button>
                <button
                    class="rounded-[6px] bg-[#B6241C] text-white text-[11px] px-3 py-[4px] leading-none hover:brightness-95">
                    Hapus
                </button>
            </div>
        </div>

        {{-- MODAL DRAFT LENGKAP --}}
        <div x-show="openDraftModal" x-transition class="fixed inset-0 z-40 flex items-center justify-center"
            @keydown.escape.window="openDraftModal = false">
            {{-- Background gelap --}}
            <div class="absolute inset-0 bg-black/40" @click="openDraftModal = false"></div>

            {{-- Card modal --}}
            <div class="relative z-50 w-[95vw] max-w-4xl bg-white rounded-3xl shadow-xl
                   px-5 py-4 md:px-7 md:py-6">
                {{-- Header modal --}}
                <div class="flex items-center justify-between mb-3 md:mb-4">
                    <h2 class="text-base md:text-lg font-semibold text-slate-800">
                        Draft Laporan
                    </h2>

                    <button type="button"
                        class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-slate-100"
                        @click="openDraftModal = false">
                        <span class="text-slate-400 text-lg leading-none">&times;</span>
                    </button>
                </div>

                {{-- Isi daftar draft (scrollable) --}}
                <div class="mt-2 max-h-[70vh] overflow-y-auto pr-1 space-y-2">
                    <template x-for="(draft, idx) in drafts" :key="idx">
                        <div class="flex items-center justify-between rounded-[12px] border border-slate-200
                               bg-slate-50 px-3.5 py-2.5 text-xs md:text-sm">
                            <div class="pr-3">
                                <p class="font-medium text-slate-800" x-text="draft.title"></p>
                                <p class="mt-[2px] text-[11px] text-slate-500" x-text="draft.saved_at"></p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <button class="rounded-[6px] bg-[#0E7A4A] text-white text-[11px] md:text-[12px]
                                       px-3 py-[5px] leading-none hover:brightness-95">
                                    Lanjutkan
                                </button>
                                <button class="rounded-[6px] bg-[#B6241C] text-white text-[11px] md:text-[12px]
                                       px-3 py-[5px] leading-none hover:brightness-95">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- KANAN BAWAH: STATUS LAPORAN TERAKHIR --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4">
        <h3 class="text-sm font-semibold text-slate-800 mb-3">
            Status Laporan Terakhir
        </h3>

        <div class="space-y-2 text-xs">
            <div class="flex items-center justify-between rounded-[10px] bg-slate-50 px-3 py-2">
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-amber-600 text-[11px] font-semibold">
                        P
                    </span>
                    <div>
                        <p class="font-medium text-slate-800">Rapat Koordinasi Pendapatan</p>
                        <p class="text-[11px] text-slate-500">Menunggu Validasi Laporan</p>
                    </div>
                </div>
                <span class="text-[11px] text-slate-400 whitespace-nowrap">07 Nov 2025</span>
            </div>

            <div class="flex items-center justify-between rounded-[10px] bg-slate-50 px-3 py-2">
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-[11px] font-semibold">
                        D
                    </span>
                    <div>
                        <p class="font-medium text-slate-800">Rapat Kerja Pajak</p>
                        <p class="text-[11px] text-slate-500">Laporan Disetujui</p>
                    </div>
                </div>
                <span class="text-[11px] text-slate-400 whitespace-nowrap">09 Nov 2025</span>
            </div>

            <div class="flex items-center justify-between rounded-[10px] bg-slate-50 px-3 py-2">
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-100 text-rose-600 text-[11px] font-semibold">
                        L
                    </span>
                    <div>
                        <p class="font-medium text-slate-800">Perjalanan Dinas</p>
                        <p class="text-[11px] text-slate-500">Laporan Ditolak</p>
                    </div>
                </div>
                <span class="text-[11px] text-slate-400 whitespace-nowrap">13 Nov 2025</span>
            </div>
        </div>
    </div>
</section>

@endsection