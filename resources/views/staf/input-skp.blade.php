@php($title = 'Input SKP')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'skp'])

@section('content')

{{-- 
    LAYOUT T-SHAPE (STAF VERSION):
    1. Atas: Form Input (Kiri) & Sidebar Panduan/Status (Kanan)
    2. Bawah: Tabel Daftar SKP (Full Width)
--}}
<section x-data="skpPageData()" x-init="initPage()" class="flex flex-col gap-6 flex-1">

    {{-- ================================================== --}}
    {{-- BAGIAN ATAS: GRID 2 KOLOM --}}
    {{-- ================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)] gap-6">

        {{-- KOLOM KIRI: FORM INPUT SKP --}}
        <div class="space-y-4">
            <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 h-full">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-[20px] font-normal text-slate-800">Form Rencana SKP</h2>
                    <span class="bg-emerald-100 text-emerald-700 text-[10px] px-2 py-1 rounded-md font-bold tracking-wide">FORMAT BARU</span>
                </div>

                <form class="space-y-6" @submit.prevent="submitCreate">
                    
                    {{-- A. HEADER RENCANA --}}
                    <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 space-y-4">
                        <h3 class="text-sm font-bold text-slate-700 border-b border-slate-200 pb-2">A. Rencana Hasil Kerja</h3>
                        
                        {{-- Periode --}}
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-[#5B687A] mb-[10px]">Periode Mulai</label>
                                <input type="date" x-model="formData.periode_awal" required
                                    class="w-full rounded-[10px] border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[#5B687A] mb-[10px]">Periode Selesai</label>
                                <input type="date" x-model="formData.periode_akhir" required
                                    class="w-full rounded-[10px] border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] outline-none" />
                            </div>
                        </div>

                        {{-- RHK Manual --}}
                        <div>
                            <label class="block text-xs font-medium text-[#5B687A] mb-[10px]">RHK Pimpinan yang Diintervensi</label>
                            <textarea x-model="formData.rhk_intervensi" rows="2" required
                                class="w-full rounded-[10px] border border-slate-200 bg-white px-3.5 py-2.5 text-sm resize-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] outline-none"
                                placeholder="Ketik RHK Atasan (Kepala Bidang/Kasubag) yang Anda dukung..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#5B687A] mb-[10px]">Rencana Hasil Kerja (RHK) Anda</label>
                            <textarea x-model="formData.rencana_hasil_kerja" rows="2" required
                                class="w-full rounded-[10px] border border-slate-200 bg-white px-3.5 py-2.5 text-sm resize-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] outline-none font-medium text-slate-800"
                                placeholder="Ketik Rencana Kerja Anda..."></textarea>
                        </div>
                    </div>

                    {{-- B. TARGET DINAMIS --}}
                    <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <h3 class="text-sm font-bold text-slate-700">B. Aspek & Indikator</h3>
                            <button type="button" @click="addTarget()" class="text-[11px] font-bold text-[#155FA6] hover:underline flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                Tambah Aspek
                            </button>
                        </div>

                        {{-- Scrollable Container (Max Height 260px agar pas 2 item) --}}
                        <div class="space-y-3 max-h-[260px] overflow-y-auto pr-2 custom-scrollbar">
                            <template x-for="(item, index) in formData.targets" :key="index">
                                <div class="grid grid-cols-12 gap-3 bg-white p-3 rounded-lg border border-slate-200 shadow-sm relative group hover:border-[#1C7C54]/40 transition-colors">
                                    
                                    {{-- Jenis Aspek --}}
                                    <div class="col-span-3 md:col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-400 mb-1">Aspek</label>
                                        <select x-model="item.jenis_aspek" class="w-full rounded-[8px] border border-slate-200 bg-slate-50 px-2 py-2 text-xs font-bold text-slate-700 focus:outline-none focus:border-[#1C7C54]">
                                            <option value="Kuantitas">Kuantitas</option>
                                            <option value="Kualitas">Kualitas</option>
                                            <option value="Waktu">Waktu</option>
                                            <option value="Biaya">Biaya</option>
                                        </select>
                                    </div>

                                    {{-- Indikator --}}
                                    <div class="col-span-9 md:col-span-5">
                                        <label class="block text-[10px] font-bold text-slate-400 mb-1">Indikator</label>
                                        <input type="text" x-model="item.indikator" placeholder="Contoh: Jumlah Laporan" 
                                            class="w-full rounded-[8px] border border-slate-200 px-3 py-2 text-xs focus:outline-none focus:border-[#1C7C54]">
                                    </div>

                                    {{-- Target Angka --}}
                                    <div class="col-span-4 md:col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-400 mb-1">Target</label>
                                        <input type="number" x-model="item.target" placeholder="0" 
                                            class="w-full rounded-[8px] border border-slate-200 px-3 py-2 text-xs font-bold text-center focus:outline-none focus:border-[#1C7C54]">
                                    </div>

                                    {{-- Satuan --}}
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-400 mb-1">Satuan</label>
                                        <input type="text" x-model="item.satuan" placeholder="Dokumen" 
                                            class="w-full rounded-[8px] border border-slate-200 px-3 py-2 text-xs focus:outline-none focus:border-[#1C7C54]">
                                    </div>
                                    
                                    {{-- Hapus --}}
                                    <div class="col-span-2 md:col-span-1 flex items-end justify-center pb-1">
                                        <button type="button" @click="removeTarget(index)" class="text-slate-300 hover:text-red-500 transition-colors" :class="{'invisible': index < 2}" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                        <button type="button" @click="resetForm"
                            class="rounded-[10px] bg-slate-100 px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-200 ring-1 ring-slate-300">
                            Reset
                        </button>
                        <button type="submit"
                            class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm font-normal text-white hover:brightness-95 disabled:opacity-50"
                            :disabled="isLoading">
                            <span x-show="!isLoading">Tambahkan SKP</span>
                            <span x-show="isLoading">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- KOLOM KANAN: SIDEBAR INFO --}}
        <div class="space-y-4 flex flex-col">
            
            {{-- Panduan Singkat --}}
            <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col">
                <h3 class="text-sm font-semibold text-slate-800">Panduan Singkat</h3>
                <div class="mt-3 space-y-2 max-h-[400px] overflow-y-auto pr-1 custom-scrollbar">
                    @foreach ([
                    ['title' => 'Periode Awal', 'desc' => 'Pilih tanggal penetapan awal SKP.'],
                    ['title' => 'Periode Akhir', 'desc' => 'Pilih tanggal penetapan akhir SKP.'],
                    ['title' => 'RHK Intervensi', 'desc' => 'Ketik manual RHK Atasan yang Anda dukung.'],
                    ['title' => 'Rencana Hasil Kerja', 'desc' => 'Tuliskan rencana kinerja utama Anda.'],
                    ['title' => 'Target Kuantitas', 'desc' => 'Wajib diisi. Ini output fisik untuk Laporan Harian.'],
                    ['title' => 'Target Waktu', 'desc' => 'Wajib diisi. Estimasi lama pengerjaan.'],
                    ['title' => 'Target Kualitas', 'desc' => 'Opsional. Standar mutu hasil kerja.'],
                    ] as $guide)
                    <div class="rounded-[10px] bg-[#155FA6] px-3 py-2.5 text-white text-xs leading-snug">
                        <p class="text-[13px] font-semibold">{{ $guide['title'] }}</p>
                        <p class="mt-[2px] text-[11px] text-white/90">{{ $guide['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Status Laporan Terakhir --}}
            <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex-1">
                <h3 class="text-sm font-semibold text-slate-800 mb-3">Status Laporan Terakhir</h3>
                {{-- Scrollable List --}}
                <div class="space-y-2 text-xs max-h-[150px] overflow-y-auto pr-1 custom-scrollbar">
                    <div class="flex items-center justify-between rounded-[10px] bg-slate-50 px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-amber-600 text-[11px] font-semibold">P</span>
                            <div>
                                <p class="font-medium text-slate-800">Rapat Koordinasi</p>
                                <p class="text-[11px] text-slate-500">Menunggu Validasi</p>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-400 whitespace-nowrap">07 Nov</span>
                    </div>
                    <div class="flex items-center justify-between rounded-[10px] bg-slate-50 px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-[11px] font-semibold">D</span>
                            <div>
                                <p class="font-medium text-slate-800">Rekapitulasi Pajak</p>
                                <p class="text-[11px] text-slate-500">Laporan Disetujui</p>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-400 whitespace-nowrap">09 Nov</span>
                    </div>
                    <div class="flex items-center justify-between rounded-[10px] bg-slate-50 px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-red-600 text-[11px] font-semibold">T</span>
                            <div>
                                <p class="font-medium text-slate-800">Dinas Luar Kota</p>
                                <p class="text-[11px] text-slate-500">Laporan Ditolak</p>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-400 whitespace-nowrap">10 Nov</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ================================================== --}}
    {{-- BAGIAN BAWAH: DAFTAR SKP (FULL WIDTH) --}}
    {{-- ================================================== --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 w-full">
        <h2 class="text-[20px] font-normal mb-4">Daftar Rencana SKP Saya</h2>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500 uppercase bg-slate-50/50">
                        <th class="px-3 py-3 font-medium w-[15%]">Periode</th>
                        <th class="px-3 py-3 font-medium w-[35%]">Rencana Hasil Kerja</th>
                        <th class="px-3 py-3 font-medium w-[30%]">RHK Pimpinan</th>
                        <th class="px-3 py-3 font-medium text-center w-[20%]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700 divide-y divide-slate-100">
                    <template x-for="rencana in skpList" :key="rencana.id">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-3 py-4 align-top text-xs whitespace-nowrap">
                                <div class="font-bold text-slate-800" x-text="formatDate(rencana.periode_awal)"></div>
                                <div class="text-slate-400 text-[10px]">s.d.</div>
                                <div class="font-bold text-slate-800" x-text="formatDate(rencana.periode_akhir)"></div>
                            </td>
                            <td class="px-3 py-4 align-top">
                                <p class="font-bold text-slate-800 mb-1" x-text="rencana.rencana_hasil_kerja"></p>
                                
                                {{-- Preview Target Kuantitas --}}
                                <div class="flex items-center gap-1.5 mt-2">
                                    <span class="bg-blue-50 text-blue-600 text-[10px] px-1.5 py-0.5 rounded border border-blue-100 font-medium">Kuantitas</span>
                                    <span class="text-xs text-slate-500" x-text="getKuantitasLabel(rencana.targets)"></span>
                                </div>
                            </td>
                            <td class="px-3 py-4 align-top text-xs text-slate-500 italic" x-text="rencana.rhk_intervensi || '-'"></td>
                            <td class="px-3 py-4 align-top text-center">
                                <div class="flex justify-center gap-2">
                                    <button @click.prevent="openDetailModal(rencana)" class="rounded-[8px] bg-[#155FA6]/10 text-[#155FA6] border border-[#155FA6]/20 text-xs px-3 py-1.5 font-medium hover:bg-[#155FA6] hover:text-white transition-all">Detail</button>
                                    <button @click.prevent="openEditModal(rencana)" class="p-2 rounded-lg hover:bg-amber-50 text-amber-600 transition"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 00 2 2h11a2 2 0 00 2-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button>
                                    <button @click.prevent="deleteSkp(rencana.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-600 transition"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="skpList.length === 0" style="display: none;">
                        <td colspan="4" class="px-3 py-12 text-center text-slate-400 italic bg-slate-50/30 rounded-lg border border-dashed border-slate-200 m-4">
                            <div class="flex flex-col items-center">
                                <img src="{{ asset('assets/icon/doc-skp.svg') }}" class="w-10 h-10 mb-2 opacity-50">
                                <span>Belum ada Rencana SKP yang dibuat.</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================================================== --}}
    {{-- MODAL 1: LIHAT DETAIL --}}
    {{-- ================================================== --}}
    <div x-show="openDetail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" x-transition.opacity>
        <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl overflow-hidden max-h-[90vh] flex flex-col" @click.outside="openDetail = false">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                <h3 class="text-lg font-bold text-slate-800">Detail Rencana SKP</h3>
                <button @click="openDetail = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <template x-if="detailData">
                <div class="p-6 overflow-y-auto">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Periode</label>
                            <p class="text-sm font-bold text-slate-800 mt-1">
                                <span x-text="formatDate(detailData.periode_awal)"></span> s.d. <span x-text="formatDate(detailData.periode_akhir)"></span>
                            </p>
                        </div>
                        <div>
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">RHK Intervensi</label>
                            <p class="text-sm text-slate-600 mt-1" x-text="detailData.rhk_intervensi || '-'"></p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rencana Hasil Kerja (RHK)</label>
                        <div class="mt-1 p-3 bg-blue-50 border border-blue-100 rounded-lg text-sm font-medium text-blue-900" x-text="detailData.rencana_hasil_kerja"></div>
                    </div>

                    <div>
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 block">Detail Target & Indikator</label>
                        <div class="border border-slate-200 rounded-lg overflow-hidden">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 w-[20%]">Aspek</th>
                                        <th class="px-4 py-3 w-[40%]">Indikator</th>
                                        <th class="px-4 py-3 w-[20%] text-center">Target</th>
                                        <th class="px-4 py-3 w-[20%]">Satuan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="target in detailData.targets" :key="target.id">
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-slate-700" x-text="target.jenis_aspek"></td>
                                            <td class="px-4 py-3 text-slate-600" x-text="target.indikator"></td>
                                            <td class="px-4 py-3 font-bold text-center text-emerald-600" x-text="target.target"></td>
                                            <td class="px-4 py-3 text-slate-500" x-text="target.satuan"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>

            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 shrink-0 flex justify-end gap-2">
                <button @click="openDetail = false" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-50">Tutup</button>
            </div>
        </div>
    </div>

    {{-- ================================================== --}}
    {{-- MODAL 2: EDIT SKP --}}
    {{-- ================================================== --}}
    <div x-show="openEdit" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" x-transition.opacity>
        <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl overflow-hidden max-h-[90vh] flex flex-col" @click.outside="openEdit = false">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                <h3 class="text-lg font-bold text-slate-800">Edit Rencana SKP</h3>
                <button @click="openEdit = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <div class="p-6 overflow-y-auto">
                <template x-if="editData">
                    <form @submit.prevent="submitEdit" class="space-y-6">
                         <div class="space-y-4">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 mb-1">Periode Awal</label>
                                    <input type="date" x-model="editData.periode_awal" required class="w-full rounded-[8px] border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#155FA6]">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 mb-1">Periode Akhir</label>
                                    <input type="date" x-model="editData.periode_akhir" required class="w-full rounded-[8px] border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#155FA6]">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">RHK Intervensi</label>
                                <textarea x-model="editData.rhk_intervensi" rows="2" required class="w-full rounded-[8px] border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#155FA6]"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Rencana Hasil Kerja</label>
                                <textarea x-model="editData.rencana_hasil_kerja" rows="2" required class="w-full rounded-[8px] border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#155FA6]"></textarea>
                            </div>
                         </div>

                         <div class="border-t border-slate-100 pt-4">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-sm font-bold text-slate-700">Edit Target</h4>
                                <button type="button" @click="editData.targets.push({jenis_aspek:'Kuantitas', indikator:'', target:'', satuan:''})" class="text-xs text-blue-600 font-bold hover:underline">+ Tambah Target</button>
                            </div>
                            
                            <div class="space-y-3">
                                <template x-for="(item, index) in editData.targets" :key="index">
                                    <div class="grid grid-cols-12 gap-2 bg-slate-50 p-3 rounded border border-slate-200">
                                        <div class="col-span-3">
                                            <select x-model="item.jenis_aspek" class="w-full text-xs rounded border-slate-300 py-1">
                                                <option value="Kuantitas">Kuantitas</option>
                                                <option value="Kualitas">Kualitas</option>
                                                <option value="Waktu">Waktu</option>
                                                <option value="Biaya">Biaya</option>
                                            </select>
                                        </div>
                                        <div class="col-span-5">
                                            <input type="text" x-model="item.indikator" class="w-full text-xs rounded border-slate-300 py-1" placeholder="Indikator">
                                        </div>
                                        <div class="col-span-2">
                                            <input type="number" x-model="item.target" class="w-full text-xs rounded border-slate-300 py-1" placeholder="Jml">
                                        </div>
                                        <div class="col-span-2 flex gap-1">
                                            <input type="text" x-model="item.satuan" class="w-full text-xs rounded border-slate-300 py-1" placeholder="Satuan">
                                            <button type="button" @click="editData.targets.splice(index, 1)" class="text-red-500 hover:text-red-700" title="Hapus">x</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                         </div>

                         <div class="pt-4 flex justify-end gap-3">
                            <button type="button" @click="openEdit = false" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 text-sm font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-[#155FA6] text-white text-sm font-medium hover:bg-blue-700">Simpan Perubahan</button>
                         </div>
                    </form>
                </template>
            </div>
        </div>
    </div>

</section>

{{-- SCRIPT JS LOGIC --}}
<script>
document.addEventListener("alpine:init", () => {
    Alpine.data("skpPageData", () => ({
        
        skpList: [],
        isLoading: false,
        
        // State Form
        formData: {
            periode_awal: '',
            periode_akhir: '',
            rhk_intervensi: '',
            rencana_hasil_kerja: '',
            targets: [
                { jenis_aspek: 'Kuantitas', indikator: '', target: '', satuan: '' },
                { jenis_aspek: 'Waktu', indikator: '', target: '', satuan: '' }
            ]
        },

        openDetail: false,
        openEdit: false,
        detailData: null,
        editData: null,

        initPage() {
            this.fetchSkpList();
        },

        async fetchSkpList() {
            const token = localStorage.getItem('auth_token');
            try {
                const res = await fetch('/api/skp', {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const json = await res.json();
                if(res.ok) this.skpList = json.data || [];
            } catch (e) { console.error(e); }
        },

        addTarget() {
            this.formData.targets.push({ jenis_aspek: 'Kualitas', indikator: '', target: '', satuan: '' });
        },
        removeTarget(index) {
            if(this.formData.targets.length > 1) {
                this.formData.targets.splice(index, 1);
            }
        },
        resetForm() {
            this.formData = {
                periode_awal: '', periode_akhir: '', rhk_intervensi: '', rencana_hasil_kerja: '',
                targets: [
                    { jenis_aspek: 'Kuantitas', indikator: '', target: '', satuan: '' },
                    { jenis_aspek: 'Waktu', indikator: '', target: '', satuan: '' }
                ]
            };
        },

        getKuantitasLabel(targets) {
            const t = targets.find(x => x.jenis_aspek === 'Kuantitas');
            return t ? `${t.target} ${t.satuan}` : '-';
        },
        formatDate(d) {
            if(!d) return '-';
            return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric'});
        },

        async submitCreate() {
            this.isLoading = true;
            const token = localStorage.getItem('auth_token');
            try {
                const res = await fetch('/api/skp', {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(this.formData)
                });
                const json = await res.json();
                if (res.ok) {
                    Swal.fire('Sukses', 'Rencana SKP berhasil dibuat!', 'success');
                    this.resetForm();
                    this.fetchSkpList();
                } else {
                    Swal.fire('Gagal', json.message || 'Validasi Gagal', 'error');
                }
            } catch (e) { Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'); }
            this.isLoading = false;
        },
        
        openDetailModal(data) {
            this.detailData = data;
            this.openDetail = true;
        },
        openEditModal(data) {
            this.editData = JSON.parse(JSON.stringify(data));
            this.openEdit = true;
        },
        async submitEdit() {
             this.isLoading = true;
             const token = localStorage.getItem('auth_token');
             try {
                const res = await fetch(`/api/skp/${this.editData.id}`, {
                    method: 'PUT',
                    headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(this.editData)
                });
                if (res.ok) {
                    Swal.fire('Sukses', 'SKP diperbarui!', 'success');
                    this.openEdit = false;
                    this.fetchSkpList();
                } else {
                    const json = await res.json();
                    Swal.fire('Gagal', json.message, 'error');
                }
             } catch(e) { Swal.fire('Error', 'Koneksi error', 'error'); }
             this.isLoading = false;
        },
        async deleteSkp(id) {
            const c = await Swal.fire({ title:'Hapus?', text:'Data tidak bisa kembali', icon:'warning', showCancelButton:true, confirmButtonColor:'#d33'});
            if(c.isConfirmed) {
                const token = localStorage.getItem('auth_token');
                await fetch(`/api/skp/${id}`, { method:'DELETE', headers: {'Authorization':`Bearer ${token}`} });
                this.fetchSkpList();
                Swal.fire('Terhapus', '', 'success');
            }
        }

    }));
});
</script>
@endsection