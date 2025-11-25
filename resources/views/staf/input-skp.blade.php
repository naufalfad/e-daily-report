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
        
        {{-- 1. FORM INPUT SKP (CREATE) --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
            <h2 class="text-[20px] font-normal mb-4">Form Input SKP</h2>

            {{-- Menggunakan submitCreate dari Alpine --}}
            <form class="space-y-4" @submit.prevent="submitCreate">
                {{-- Row 1: Periode Awal + Periode Akhir --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Periode Mulai</label>
                        <div class="relative">
                            {{-- Menggunakan x-model formData.periode_mulai --}}
                            <input type="date" x-model="formData.periode_mulai" required
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Periode Selesai</label>
                        <div class="relative">
                            {{-- Menggunakan x-model formData.periode_selesai --}}
                            <input type="date" x-model="formData.periode_selesai" required
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                        </div>
                    </div>
                </div>

                {{-- Row 2: Sasaran Kinerja + Indikator Kinerja --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Sasaran Kinerja</label>
                        {{-- Menggunakan x-model formData.nama_skp (sesuai DB) --}}
                        <input type="text" x-model="formData.nama_skp" required
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" 
                            placeholder="Contoh: Meningkatkan PAD...">
                    </div>
                    <div>
                        <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Indikator Kinerja</label>
                        {{-- Menggunakan x-model formData.indikator (sesuai DB) --}}
                        <input type="text" x-model="formData.indikator" required
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" 
                            placeholder="Contoh: Jumlah dokumen terverifikasi...">
                    </div>
                </div>

                {{-- Row 3: Rencana Aksi --}}
                <div>
                    <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Rencana Aksi</label>
                    <textarea x-model="formData.rencana_aksi" rows="3" required
                        class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                        placeholder="Tulis uraian rencana aksi..."></textarea>
                </div>

                {{-- Row 4: Target Kuantitas & Atasan --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Target (Angka)</label>
                         {{-- Menggunakan x-model formData.target (sesuai DB) --}}
                        <input type="number" x-model="formData.target" required min="1"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" 
                            placeholder="Contoh: 12">
                    </div>
                    
                    {{-- Row 5: Atasan Langsung (Otomatis dari API) --}}
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Atasan Langsung</label>
                        <input type="text" :value="atasanName" readonly disabled 
                            class="w-full rounded-[10px] border border-slate-200 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-500 cursor-not-allowed focus:outline-none"
                            placeholder="Memuat data atasan...">
                        <p class="text-[10px] text-gray-400 mt-1">*Sesuai struktur organisasi user saat ini.</p>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" @click="resetForm" class="rounded-[10px] bg-slate-100 px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-200 ring-1 ring-slate-300">
                        Reset
                    </button>
                    <button type="submit"
                        class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm font-normal text-white hover:brightness-95 disabled:opacity-50" :disabled="isLoading">
                        <span x-show="!isLoading">Tambahkan SKP</span>
                        <span x-show="isLoading">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- DAFTAR SKP (DINAMIS DENGAN ALPINE) --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
            <h2 class="text-[20px] font-normal mb-4">Daftar SKP Saya</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-500 uppercase bg-slate-50/50">
                            <th class="px-3 py-3 font-medium">Periode</th>
                            <th class="px-3 py-3 font-medium">Sasaran Kinerja</th>
                            <th class="px-3 py-3 font-medium">Indikator</th>
                            <th class="px-3 py-3 font-medium text-center">Target</th>
                            <th class="px-3 py-3 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    {{-- Diperbaiki sesuai nama kolom DB --}}
                    <tbody class="text-slate-700 divide-y divide-slate-100">
                        <template x-for="skp in skpList" :key="skp.id">
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-3 py-3 whitespace-nowrap text-xs">
                                    <div class="font-medium" x-text="formatDate(skp.periode_mulai)"></div>
                                    <div class="text-slate-500" x-text="formatDate(skp.periode_selesai)"></div>
                                </td>
                                <td class="px-3 py-3 line-clamp-2 font-medium text-slate-800" x-text="skp.nama_skp"></td>
                                <td class="px-3 py-3 line-clamp-2 text-slate-500" x-text="skp.indikator"></td>
                                <td class="px-3 py-3 text-center font-bold text-[#155FA6]" x-text="skp.target"></td>
                                <td class="px-3 py-3 text-center">
                                    <button @click.prevent="openDetailModal(skp)"
                                        class="rounded-[8px] bg-[#155FA6]/10 text-[#155FA6] border border-[#155FA6]/20 text-xs px-3 py-1.5 font-medium hover:bg-[#155FA6] hover:text-white transition-all">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        </template>
                        
                        <tr x-show="skpList.length === 0" style="display: none;">
                            <td colspan="5" class="px-3 py-8 text-center text-slate-400 italic">
                                Belum ada data SKP yang ditambahkan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    {{-- KOLOM KANAN (SIDEBAR INFO) --}}
    <div class="space-y-4 flex flex-col">

        {{-- PANDUAN SINGKAT (SCROLLABLE BODY) --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col">
            <h3 class="text-sm font-semibold text-slate-800">Panduan Singkat</h3>

            {{-- BAGIAN INI YANG DISCROLL --}}
            <div class="mt-3 space-y-2 max-h-[420px] overflow-y-auto pr-1 custom-scrollbar">
                @foreach ([
                    ['title' => 'Periode Awal', 'desc' => 'Pilih tanggal penetapan awal SKP.'],
                    ['title' => 'Periode Akhir', 'desc' => 'Pilih tanggal penetapan akhir SKP.'],
                    ['title' => 'Sasaran Kerja', 'desc' => 'Tuliskan sasaran kerja SKP.'],
                    ['title' => 'Indikator Kerja', 'desc' => 'Tuliskan indikator kerja SKP.'],
                    ['title' => 'Rencana Aksi', 'desc' => 'Tuliskan rencana aksi yang akan dilakukan.'],
                    ['title' => 'Target', 'desc' => 'Tentukan target angka (kuantitas) berdasar rencana aksi.'],
                    ['title' => 'Atasan Langsung', 'desc' => 'Otomatis terisi dari data profil Anda.'],
                ] as $guide)
                <div class="rounded-[10px] bg-[#155FA6] px-3 py-2.5 text-white text-xs leading-snug">
                    <p class="text-[13px] font-semibold">{{ $guide['title'] }}</p>
                    <p class="mt-[2px] text-[11px] text-white/90">{{ $guide['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- STATUS LAPORAN (Placeholder) --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex-1">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">Status Laporan Terakhir</h3>
            <div class="space-y-2 text-xs">
                {{-- Item 1 --}}
                <div class="flex items-center justify-between rounded-[10px] bg-slate-50 px-3 py-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-amber-600 text-[11px] font-semibold">P</span>
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
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-[11px] font-semibold">D</span>
                        <div>
                            <p class="font-medium text-slate-800">Rapat Kerja Pajak</p>
                            <p class="text-[11px] text-slate-500">Laporan Disetujui</p>
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-400 whitespace-nowrap">09 Nov 2025</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 
        MODAL 1: LIHAT DETAIL (Diperbaiki variabelnya)
    --}}
    <div x-show="openDetail" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm p-4" 
         style="display: none;">
        
        <div x-show="openDetail" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.outside="openDetail = false"
             class="relative w-full max-w-2xl rounded-2xl bg-white ring-1 ring-slate-200 p-6 shadow-xl">
            
            <button @click="openDetail = false" 
                    class="absolute top-4 right-5 h-8 w-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2.5" stroke-linecap="round" d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
            
            <h3 class="text-lg font-semibold text-slate-800">Detail SKP</h3>

            <template x-if="detailData">
                <div class="mt-6 space-y-4 text-sm">
                    {{-- Periode --}}
                    <div class="grid grid-cols-2 gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <label class="text-xs text-slate-500">Periode Mulai</label>
                            <p class="text-slate-800 font-medium" x-text="formatDate(detailData.periode_mulai)"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Periode Selesai</label>
                            <p class="text-slate-800 font-medium" x-text="formatDate(detailData.periode_selesai)"></p>
                        </div>
                    </div>

                    {{-- Sasaran & Indikator --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-slate-500">Sasaran Kinerja</label>
                            <p class="text-slate-800" x-text="detailData.nama_skp"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Indikator Kinerja</label>
                            <p class="text-slate-800" x-text="detailData.indikator"></p>
                        </div>
                    </div>

                    {{-- Rencana Aksi --}}
                    <div>
                        <label class="text-xs text-slate-500">Rencana Aksi</label>
                        <p class="text-slate-800" x-text="detailData.rencana_aksi"></p>
                    </div>

                    {{-- Target & Atasan --}}
                    <div class="grid grid-cols-2 gap-4 pt-2 bg-slate-50 p-3 rounded-lg">
                        <div>
                            <label class="text-xs text-slate-500">Target (Angka)</label>
                            <p class="text-slate-800 font-bold" x-text="detailData.target"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Atasan Langsung</label>
                            <p class="text-slate-800" x-text="atasanName"></p>
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex flex-wrap items-center justify-end gap-3 pt-4 border-t border-slate-200">
                        <button @click="openDetail = false" type="button" class="rounded-[10px] bg-slate-100 px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-200 ring-1 ring-slate-300">
                            Tutup
                        </button>
                        <button type="button" @click="openEditModal()" class="rounded-[10px] bg-[#155FA6] px-4 py-2 text-sm font-normal text-white hover:brightness-95">
                            Edit SKP
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- 
        MODAL 2: EDIT SKP (Diperbaiki: Dihapus Satuan & Kualitas)
    --}}
    <div x-show="openEdit" 
         class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" 
         style="display: none;">
    
        <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl overflow-hidden" @click.outside="openEdit = false">
            
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800">Edit Sasaran Kinerja</h3>
                <button @click="openEdit = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form @submit.prevent="submitEdit" class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                <template x-if="editData">
                    <div class="space-y-4 text-sm">
                        {{-- Input Tanggal --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Periode Mulai</label>
                                <input type="date" x-model="editData.periode_mulai" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-[#155FA6]/20 focus:border-[#155FA6]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Periode Selesai</label>
                                <input type="date" x-model="editData.periode_selesai" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-[#155FA6]/20 focus:border-[#155FA6]">
                            </div>
                        </div>

                        {{-- Input Sasaran --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sasaran Kinerja</label>
                            <input type="text" x-model="editData.nama_skp" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-[#155FA6]/20 focus:border-[#155FA6]">
                        </div>

                        {{-- Input Indikator --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Indikator Kinerja</label>
                            <input type="text" x-model="editData.indikator" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-[#155FA6]/20 focus:border-[#155FA6]">
                        </div>

                        {{-- Input Rencana Aksi --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Rencana Aksi</label>
                            <textarea x-model="editData.rencana_aksi" rows="3" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-[#155FA6]/20 focus:border-[#155FA6] resize-none"></textarea>
                        </div>

                        {{-- Input Target & Atasan --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Target (Angka)</label>
                                <input type="number" x-model="editData.target" class="w-full rounded-[8px] border border-slate-300 px-3 py-2 outline-none focus:ring-2 focus:ring-[#155FA6]/20 focus:border-[#155FA6]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Atasan Langsung</label>
                                <input type="text" :value="atasanName" readonly disabled class="w-full rounded-[8px] border border-slate-200 bg-gray-100 px-3 py-2 text-gray-500 cursor-not-allowed">
                            </div>
                        </div>
                    </div>
                </template>

                <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                    <button type="button" @click="openEdit = false" class="px-4 py-2 rounded-[8px] border border-slate-300 text-slate-600 text-sm font-medium hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-[8px] bg-[#0F4C75] text-white text-sm font-medium hover:bg-[#0B3A5B] shadow-lg disabled:opacity-50" :disabled="isLoading">
                        <span x-show="!isLoading">Simpan Perubahan</span>
                        <span x-show="isLoading">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection