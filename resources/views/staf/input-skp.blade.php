@php($title = 'Input SKP')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'skp'])

@section('content')

{{-- 
    ANALISIS: 
    1. x-data="skpPageData()" menginisialisasi Alpine.js
    2. x-init="initPage()" memanggil loader data dan inisialisasi form
    3. 'flex-1' ditambahkan untuk memenuhi layout flexbox dari app.blade.php
--}}
<section x-data="skpPageData()" x-init="initPage()" class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)] gap-4 flex-1">

    {{-- KOLOM KIRI: FORM DAN DAFTAR SKP --}}
    <div class="space-y-4">
        {{-- FORM INPUT SKP --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
            <h2 class="text-[20px] font-normal mb-4">Form Input SKP</h2>

            <form class="space-y-4" @submit.prevent="console.log('Form Submit Ditamgkap')">
                {{-- Row 1: Periode Awal + Periode Akhir --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="periode_awal" class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Periode Awal</label>
                        <div class="relative">
                            <input id="periode_awal" type="date" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                       px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                       focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" placeholder="dd/mm/yyyy" />
                            <button type="button" id="periode_awal_btn" class="absolute right-3 top-1/2 -translate-y-1/2
                            h-7 w-7 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/tanggal.svg') }}" alt="Pilih tanggal"
                                    class="h-4 w-4 opacity-80" />
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="periode_akhir" class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Periode Akhir</label>
                        <div class="relative">
                            <input id="periode_akhir" type="date" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                       px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                       focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" placeholder="dd/mm/yyyy" />
                            <button type="button" id="periode_akhir_btn" class="absolute right-3 top-1/2 -translate-y-1/2
                            h-7 w-7 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/tanggal.svg') }}" alt="Pilih tanggal"
                                    class="h-4 w-4 opacity-80" />
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Sasaran Kinerja + Indikator Kinerja --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="sasaran_kinerja" class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Sasaran Kinerja</label>
                        <input id="sasaran_kinerja" type="text" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                   px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                   focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" placeholder="Sasaran Kinerja">
                    </div>
                    <div>
                        <label for="indikator_kinerja" class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Indikator Kinerja</label>
                        <input id="indikator_kinerja" type="text" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                   px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                                   focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" placeholder="Indikator Kinerja">
                    </div>
                </div>

                {{-- Row 3: Rencana Aksi --}}
                <div>
                    <label for="rencana_aksi" class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Rencana Aksi</label>
                    <textarea id="rencana_aksi" rows="3" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                         px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2
                                         focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                        placeholder="Tulis uraian rencana aksi..."></textarea>
                </div>

                {{-- Row 4: Target Kuantitas --}}
                <div>
                    <label for="target_kuantitas" class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Target Kuantitas</label>
                    <input id="target_kuantitas" type="number" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                          px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                          focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" placeholder="Contoh: 12 (sebagai jumlah total dalam setahun)">
                </div>
                
                {{-- Row 5: Atasan Langsung --}}
                <div>
                    <label for="atasan_langsung" class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Atasan Langsung</label>
                    <div class="relative">
                        <select id="atasan_langsung" required class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                       px-3.5 py-2.5 text-sm pr-9 focus:outline-none focus:ring-2
                       focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                       appearance-none" style="color: #9CA3AF;">
                            <option value="" disabled selected hidden>Pilih Atasan</option>
                            <option value="1">Atasan 1 (Placeholder)</option>
                            <option value="2">Atasan 2 (Placeholder)</option>
                        </select>
                        <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none" />
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" class="rounded-[10px] bg-slate-100 px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-200 ring-1 ring-slate-300">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm font-normal text-white hover:brightness-95">
                        Tambahkan SKP
                    </button>
                </div>
            </form>
        </div>

        {{-- DAFTAR SKP (DINAMIS DENGAN ALPINE) --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
            <h2 class="text-[20px] font-normal mb-4">Daftar SKP</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-500 uppercase">
                            <th class="px-3 py-2 font-medium">Periode</th>
                            <th class="px-3 py-2 font-medium">Sasaran Kinerja</th>
                            <th class="px-3 py-2 font-medium">Indikator Kinerja</th>
                            <th class="px-3 py-2 font-medium">Atasan Langsung</th>
                            <th class="px-3 py-2 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700">
                        {{-- ANALISIS: Ganti @foreach dengan <template x-for> --}}
                        <template x-for="skp in skpList" :key="skp.id">
                            <tr class="border-t border-slate-100 hover:bg-slate-50">
                                <td class="px-3 py-2.5 whitespace-nowrap" x-text="skp.periode"></td>
                                <td class="px-3 py-2.5" x-text="skp.sasaran_kinerja"></td>
                                <td class="px-3 py-2.5" x-text="skp.indikator_kinerja"></td>
                                <td class="px-3 py-2.5" x-text="skp.atasan_langsung"></td>
                                <td class="px-3 py-2.5">
                                    {{-- ANALISIS: Tambahkan @click event --}}
                                    <button @click.prevent="openModal(skp)"
                                        class="rounded-[6px] bg-[#155FA6] text-white text-[11px] px-3 py-[4px] leading-none shadow-sm hover:brightness-95">
                                        Lihat Detail
                                    </button>
                                </td>
                            </tr>
                        </template>
                        
                        {{-- Pesan jika data kosong --}}
                        <tr x-show="skpList.length === 0" style="display: none;">
                            <td colspan="5" class="px-3 py-4 text-center text-slate-500 italic">
                                Belum ada data SKP. Silakan ambil data atau tambahkan SKP baru.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    {{-- 
        ANALISIS REVISI (KOLOM KANAN):
        1. 'flex flex-col' ditambahkan agar kolom ini menjadi flex container
           vertikal, memungkinkan 'flex-1' pada child-nya.
    --}}
    <div class="space-y-4 flex flex-col">

        {{-- PANDUAN SINGKAT (SCROLLABLE BODY) --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col">
            <h3 class="text-sm font-semibold text-slate-800">Panduan Singkat</h3>

            {{-- BAGIAN INI YANG DISCROLL --}}
            <div class="mt-3 space-y-2 max-h-[420px] overflow-y-auto pr-1">
                @foreach ([
                    ['title' => 'Periode Awal', 'desc' => 'Pilih tanggal penetapan awal SKP.'],
                    ['title' => 'Periode Akhir', 'desc' => 'Pilih tanggal penetapan akhir SKP.'],
                    ['title' => 'Sasaran Kerja', 'desc' => 'Tuliskan sasaran kerja SKP.'],
                    ['title' => 'Indikator Kerja', 'desc' => 'Tuliskan indikator kerja SKP.'],
                    ['title' => 'Rencana Aksi', 'desc' => 'Tuliskan rencana aksi yang akan dilakukan pada SKP yang telah ditetapkan.'],
                    ['title' => 'Target Kuantitas', 'desc' => 'Tentukan target kuantitas berdasar rencana aksi.'],
                    ['title' => 'Atasan Langsung', 'desc' => 'Pilih atasan langsung yang menilai kinerja anda.'],
                ] as $guide)
                <div class="rounded-[10px] bg-[#155FA6] px-3 py-2.5 text-white text-xs leading-snug">
                    <p class="text-[13px] font-semibold">{{ $guide['title'] }}</p>
                    <p class="mt-[2px] text-[11px] text-white/90">{{ $guide['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- 
            ANALISIS REVISI (STATUS LAPORAN):
            1. 'flex-1' ditambahkan agar card ini 'tumbuh' mengisi 
               sisa ruang kosong di kolom kanan, mendorong footer ke bawah.
        --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex-1">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">Status Laporan Terakhir</h3>

            <div class="space-y-2 text-xs">
                {{-- Item 1 --}}
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

                {{-- Item 2 --}}
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

                {{-- Item 3 --}}
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
    </div>

    {{-- 
        ANALISIS: MODAL "LIHAT DETAIL" BARU
        Sesuai desain image_368d28.png
    --}}
    <div x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm p-4" 
         style="display: none;">
        
        {{-- Panel Modal --}}
        <div x-show="open" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.outside="open = false"
             class="relative w-full max-w-2xl rounded-2xl bg-white ring-1 ring-slate-200 p-6 shadow-xl">
            
            {{-- Tombol Close --}}
            <button @click="open = false" 
                    class="absolute top-4 right-5 h-8 w-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2.5" stroke-linecap="round" d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
            
            <h3 class="text-lg font-semibold text-slate-800">Detail SKP</h3>

            {{-- Template untuk memastikan data ada sebelum render --}}
            <template x-if="modalData">
                <div class="mt-6 space-y-4 text-sm">
                    
                    {{-- Row 1: Periode --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-slate-500">Periode Awal</label>
                            <p class="text-slate-800 font-medium" x-text="modalData.periode.split(' - ')[0]"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Periode Akhir</label>
                            <p class="text-slate-800 font-medium" x-text="modalData.periode.split(' - ')[1]"></p>
                        </div>
                    </div>

                    {{-- Row 2: Sasaran & Indikator --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-slate-500">Sasaran Kinerja</label>
                            <p class="text-slate-800" x-text="modalData.sasaran_kinerja"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Indikator Kinerja</label>
                            <p class="text-slate-800" x-text="modalData.indikator_kinerja"></p>
                        </div>
                    </div>

                    {{-- Row 3: Rencana Aksi --}}
                    <div>
                        <label class="text-xs text-slate-500">Rencana Aksi</label>
                        <p class="text-slate-800" x-text="modalData.rencana_aksi"></p>
                    </div>

                    {{-- Row 4: Target --}}
                    <div class="grid grid-cols-3 gap-4 pt-2">
                        <div>
                            <label class="text-xs text-slate-500">Target Kuantitas</label>
                            <p class="text-slate-800" x-text="modalData.target_kuantitas"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Satuan</label>
                            <p class="text-slate-800" x-text="modalData.satuan"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Target Kualitas (%)</label>
                            <p class="text-slate-800" x-text="modalData.target_kualitas"></p>
                        </div>
                    </div>

                    {{-- Row 5: Atasan --}}
                    <div class="pt-2">
                        <label class="text-xs text-slate-500">Atasan Langsung</label>
                        <p class="text-slate-800" x-text="modalData.atasan_langsung"></p>
                    </div>

                    {{-- Tombol Aksi Modal --}}
                    <div class="flex flex-wrap items-center justify-end gap-3 pt-4 border-t border-slate-200">
                        <button @click="open = false" type="button" class="rounded-[10px] bg-slate-100 px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-200 ring-1 ring-slate-300">
                            Batal
                        </button>
                        <button type="button" 
                                @click="openEditModal()" 
                                class="rounded-[10px] bg-[#155FA6] px-4 py-2 text-sm font-normal text-white hover:brightness-95">
                            Edit SKP
                        </button>
                    </div>

                </div>
            </template>
        </div>
    </div>
    {{-- / MODAL --}}

    {{-- MODAL EDIT (BARU) --}}
    <div x-show="openEdit" 
         class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" 
         style="display: none;">
    
        <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl overflow-hidden" @click.outside="openEdit = false">
            
            {{-- Header Modal Edit --}}
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800">Edit Sasaran Kinerja</h3>
                <button @click="openEdit = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Form Edit --}}
            <form @submit.prevent="saveEdit" class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                <template x-if="editData">
                    <div class="space-y-4 text-sm">
                        {{-- Input Tanggal --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Periode Mulai</label>
                                <input type="date" x-model="editData.tgl_mulai_raw" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Periode Selesai</label>
                                <input type="date" x-model="editData.tgl_selesai_raw" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                        </div>

                        {{-- Input Sasaran & Indikator --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sasaran Kinerja</label>
                                <input type="text" x-model="editData.sasaran_kinerja" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Indikator Kinerja</label>
                                <input type="text" x-model="editData.indikator_kinerja" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                        </div>

                        {{-- Input Rencana Aksi --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Rencana Aksi</label>
                            <textarea x-model="editData.rencana_aksi" rows="3" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-none"></textarea>
                        </div>

                        {{-- Input Target --}}
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Target</label>
                                <input type="number" x-model="editData.target_kuantitas" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Satuan</label>
                                <input type="text" x-model="editData.satuan" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kualitas (%)</label>
                                <input type="number" x-model="editData.target_kualitas" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                        </div>
                    </div>
                </template>

                <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                    <button type="button" @click="openEdit = false" class="px-4 py-2 rounded-[8px] border border-slate-300 text-slate-600 text-sm font-medium hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-[8px] bg-[#0F4C75] text-white text-sm font-medium hover:bg-[#0B3A5B] shadow-lg">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection

@push('scripts')
{{-- Memuat Alpine.js --}}
<script src="//unpkg.com/alpinejs" defer></script>

<script>
function skpPageData() {
    return {
        skpList: [],
        open: false,
        modalData: null,

        // Fungsi inisialisasi utama
        initPage() {
            this.loadData();
            this.initDatePickers();
            this.initSelectPlaceholders();
        },

        // 1. Memuat data SKP dari JSON
        loadData() {
            fetch('/data/staff-skp.json')
                .then(res => res.json())
                .then(data => {
                    this.skpList = data;
                })
                .catch(err => {
                    console.error('Gagal memuat data SKP:', err);
                    // Fallback data jika fetch gagal
                    this.skpList = [
                        { "id": 1, "periode": "10/01/2025 - 10/12/2025", "sasaran_kinerja": "Meningkatkan perolehan wajib pajak daerah.", "indikator_kinerja": "Jumlah pajak yang dibayarkan...", "atasan_langsung": "Fahrizal M.", "rencana_aksi": "Melakukan entri data...", "target_kuantitas": 300, "satuan": "Berkas", "target_kualitas": 100 }
                    ];
                });
        },

        // 2. Fungsi untuk membuka modal detail
        openModal(skp) {
            this.modalData = skp;
            this.open = true;
        },

        // 3. Helper untuk date picker
        initDatePickers() {
            this.$nextTick(() => {
                const initDatePicker = (inputId, buttonId) => {
                    const input = document.getElementById(inputId);
                    const button = document.getElementById(buttonId);
                    if (input && button) {
                        button.addEventListener('click', function () {
                            try { input.showPicker(); } 
                            catch (e) { input.focus(); }
                        });
                    }
                };
                initDatePicker('periode_awal', 'periode_awal_btn');
                initDatePicker('periode_akhir', 'periode_akhir_btn');
            });
        },

        // 4. Helper untuk placeholder dropdown
        initSelectPlaceholders() {
            this.$nextTick(() => {
                const initSelectPlaceholder = (selectId) => {
                    const select = document.getElementById(selectId);
                    if (select) {
                        const setColor = () => {
                            select.style.color = (select.value === "") ? '#9CA3AF' : '#111827';
                        };
                        select.addEventListener('change', setColor);
                        setColor();
                    }
                };
                initSelectPlaceholder('atasan_langsung');
            });
        }, // <--- KOMANYA TADI HILANG DISINI YANG MULIA

        // --- TAMBAHAN UNTUK MODAL EDIT ---
        openEdit: false, 
        editData: null, 

        // Fungsi Buka Modal Edit (Dipanggil dari Modal Detail)
        openEditModal() {
            // 1. Salin data agar aman
            this.editData = JSON.parse(JSON.stringify(this.modalData));

            // 2. Pecah tanggal untuk input date HTML
            if (this.editData.periode) {
                let dates = this.editData.periode.split(' - ');
                this.editData.tgl_mulai_raw = this.convertDateToISO(dates[0]);
                this.editData.tgl_selesai_raw = this.convertDateToISO(dates[1]);
            }

            // 3. Switch Modal
            this.open = false;
            this.openEdit = true;
        },

        // Fungsi Simpan Perubahan
        saveEdit() {
            // Format balik tanggal untuk tampilan
            let start = this.convertDateToDisplay(this.editData.tgl_mulai_raw);
            let end = this.convertDateToDisplay(this.editData.tgl_selesai_raw);
            this.editData.periode = `${start} - ${end}`;

            // Update data di tabel (Array skpList)
            let index = this.skpList.findIndex(item => item.id === this.editData.id);
            if (index !== -1) {
                this.skpList[index] = this.editData;
            }

            this.openEdit = false;
        },

        // Helper: "10/01/2025" -> "2025-01-10"
        convertDateToISO(dateStr) {
            if (!dateStr) return '';
            let parts = dateStr.trim().split('/'); 
            return (parts.length === 3) ? `${parts[2]}-${parts[1]}-${parts[0]}` : '';
        },

        // Helper: "2025-01-10" -> "10/01/2025"
        convertDateToDisplay(isoDate) {
            if (!isoDate) return '';
            let parts = isoDate.split('-');
            return (parts.length === 3) ? `${parts[2]}/${parts[1]}/${parts[0]}` : '';
        }
    };
}
</script>
@endpush