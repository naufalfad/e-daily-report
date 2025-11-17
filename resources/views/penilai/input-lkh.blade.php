@php($title = 'Input LKH')
@extends('layouts.app', ['title' => $title, 'role' => 'penilai', 'active' => 'input-lkh'])

@section('content')

{{-- GRID UTAMA: FORM + PANDUAN + DRAFT + STATUS --}}
<section class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)] gap-4 lg:auto-rows-min">

    {{-- KIRI ATAS: FORM INPUT LKH --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
        <h2 class="text-[20px] font-normal mb-4">Form Input LKH</h2>

        <div class="space-y-4">
            {{-- Row 1: Tanggal + Jenis Kegiatan --}}
            <div class="grid md:grid-cols-2 gap-4">

                {{-- Tanggal --}}
                <div>
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Tanggal</label>
                    <div class="relative">
                        <input id="tanggal_lkh" type="date" class="tanggal-placeholder w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                      px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                      focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />

                        {{-- Icon kalender lokal --}}
                        <button type="button" id="tanggal_lkh_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/tanggal.svg') }}" alt="Pilih tanggal"
                                class="h-4 w-4 opacity-80" />
                        </button>
                    </div>
                </div>

                {{-- Jenis Kegiatan (custom dropdown) --}}
                <div x-data="{
                        open: false,
                        value: '',
                        label: 'Pilih Jenis Kegiatan',
                        options: [
                            { value: 'rapat',     label: 'Rapat' },
                            { value: 'pelayanan', label: 'Pelayanan Publik' },
                            { value: 'dokumen',   label: 'Penyusunan Dokumen' },
                            { value: 'lapangan',  label: 'Kunjungan Lapangan' },
                            { value: 'lainnya',   label: 'Lainnya' },
                        ],
                        select(opt) { this.value = opt.value; this.label = opt.label; this.open = false; },
                    }">
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Jenis Kegiatan</label>

                    {{-- untuk submit ke backend --}}
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

            {{-- Row 2: Referensi Tupoksi --}}
            <div x-data="{
                    open: false,
                    value: '',
                    label: 'Pilih Referensi Tupoksi',
                    options: [
                        { value: 'rencana',    label: 'Penyusunan rencana dan program kerja' },
                        { value: 'petunjuk',   label: 'Penyusunan dan perumusan bahan petunjuk teknis' },
                        { value: 'pengawasan', label: 'Pengawasan, pemantauan dan evaluasi pelaksanaan' },
                        { value: 'pembukuan',  label: 'Pelaksanaan dan pembinaan pembukuan' },
                    ],
                    select(opt) { this.value = opt.value; this.label = opt.label; this.open = false; },
                }">
                <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Referensi Tupoksi</label>

                <input type="hidden" name="referensi_tupoksi" x-model="value">

                <div class="relative">
                    <button type="button" @click="open = !open" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                   px-3.5 py-2.5 text-sm pr-3 text-left flex items-center justify-between
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

            {{-- Row 3: Uraian Kegiatan --}}
            <div>
                <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Uraian Kegiatan</label>
                <textarea rows="3" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                 px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2
                                 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                    placeholder="Tulis uraian kegiatan yang dilakukan..."></textarea>
            </div>

            {{-- Row 4 & 5: Output + Volume + Satuan + Kategori (1 baris) --}}
            <div class="grid gap-4 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)] items-start">

                {{-- Output --}}
                <div>
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Output</label>
                    <input type="text" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                  px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                  focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                        placeholder="Contoh: Notulensi Rapat">
                </div>

                {{-- Volume --}}
                <div>
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Volume</label>
                    <input type="number" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                  px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                  focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" placeholder="Volume">
                </div>

                {{-- Satuan (Alpine dropdown) --}}
                <div x-data="{
                        open: false,
                        value: '',
                        options: [
                            { value: 'jam',      label: 'Jam' },
                            { value: 'dokumen',  label: 'Dokumen' },
                            { value: 'kegiatan', label: 'Kegiatan' },
                        ],
                        get label() {
                            const found = this.options.find(o => o.value === this.value);
                            return found ? found.label : 'Satuan';
                        },
                    }" x-cloak class="relative">
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">
                        Satuan
                    </label>

                    <button type="button" @click="open = !open" @click.outside="open = false" class="w-full flex items-center justify-between rounded-[10px]
                                   border border-slate-200 bg-slate-50/60
                                   pl-3.5 pr-3 py-2.5 text-sm text-left
                                   focus:outline-none focus:ring-2
                                   focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                        <span x-text="label" :class="value === '' ? 'text-[#9CA3AF]' : 'text-slate-700'"
                            class="truncate"></span>

                        <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                            class="h-4 w-4 opacity-70 ml-2 flex-shrink-0" alt="" />
                    </button>

                    <div x-show="open" x-transition class="absolute left-0 mt-1 w-full rounded-[10px]
                                border border-slate-200 bg-white shadow-lg
                                z-20 overflow-hidden">
                        <template x-for="opt in options" :key="opt.value">
                            <button type="button" @click="value = opt.value; open = false" class="w-full flex items-center justify-between
                                           px-3.5 py-2 text-sm text-left hover:bg-slate-50">
                                <span x-text="opt.label" class="text-slate-700"></span>
                                <span x-show="opt.value === value" class="text-xs text-[#1C7C54]">
                                    •
                                </span>
                            </button>
                        </template>
                    </div>

                    <input type="hidden" name="satuan" x-model="value">
                </div>

                {{-- Kategori (Alpine dropdown) --}}
                <div x-data="{
                        open: false,
                        value: '',
                        options: [
                            { value: 'skp',     label: 'SKP' },
                            { value: 'non-skp', label: 'Non-SKP' },
                        ],
                        get label() {
                            const found = this.options.find(o => o.value === this.value);
                            return found ? found.label : 'Kategori';
                        },
                    }" x-cloak class="relative">
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">
                        Kategori
                    </label>

                    <button type="button" @click="open = !open" @click.outside="open = false" class="w-full flex items-center justify-between rounded-[10px]
                                   border border-slate-200 bg-slate-50/60
                                   pl-3.5 pr-3 py-2.5 text-sm text-left
                                   focus:outline-none focus:ring-2
                                   focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                        <span x-text="label" :class="value === '' ? 'text-[#9CA3AF]' : 'text-slate-700'"
                            class="truncate"></span>

                        <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                            class="h-4 w-4 opacity-70 ml-2 flex-shrink-0" alt="" />
                    </button>

                    <div x-show="open" x-transition class="absolute left-0 mt-1 w-full rounded-[10px]
                                border border-slate-200 bg-white shadow-lg
                                z-20 overflow-hidden">
                        <template x-for="opt in options" :key="opt.value">
                            <button type="button" @click="value = opt.value; open = false" class="w-full flex items-center justify-between
                                           px-3.5 py-2 text-sm text-left hover:bg-slate-50">
                                <span x-text="opt.label" class="text-slate-700"></span>
                                <span x-show="opt.value === value" class="text-xs text-[#1C7C54]">
                                    •
                                </span>
                            </button>
                        </template>
                    </div>

                    <input type="hidden" name="kategori" x-model="value">
                </div>
            </div>

            {{-- Row 6: Jam Mulai + Jam Selesai --}}
            <div class="grid md:grid-cols-2 gap-4">
                {{-- Jam Mulai --}}
                <div>
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Jam Mulai</label>
                    <div class="relative">
                        <input id="jam_mulai" type="time" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                      px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                      focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none time-placeholder">

                        <button type="button" id="jam_mulai_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/time.svg') }}"
                                class="h-4 w-4 opacity-70 pointer-events-none" alt="Pilih jam mulai">
                        </button>
                    </div>
                </div>

                {{-- Jam Selesai --}}
                <div>
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Jam Selesai</label>
                    <div class="relative">
                        <input id="jam_selesai" type="time" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                      px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                      focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none time-placeholder">

                        <button type="button" id="jam_selesai_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/time.svg') }}"
                                class="h-4 w-4 opacity-70 pointer-events-none" alt="Pilih jam selesai">
                        </button>
                    </div>
                </div>
            </div>

            {{-- Row 7: Unggah Bukti + Lokasi --}}
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Unggah Bukti</label>
                    <label class="w-full flex items-center justify-between rounded-[10px]
                                   border border-dashed border-slate-300 bg-slate-50/60
                                   px-3.5 py-2.5 text-sm text-slate-500 cursor-pointer">
                        <span class="truncate">Pilih File</span>
                        <img src="{{ asset('assets/icon/upload.svg') }}" class="h-4 w-4 opacity-70" alt="Upload">
                        <input type="file" class="hidden">
                    </label>
                </div>

                <div>
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Lokasi</label>
                    <input type="text" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                  px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                  focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] placeholder:text-slate-400"
                        placeholder="Otomatis Terisi">
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                <button class="rounded-[10px] bg-[#155FA6] px-4 py-2 text-sm font-normal text-white">
                    Simpan Draft
                </button>
                <button class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm font-normal text-white">
                    Kirim LKH
                </button>
            </div>
        </div>
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