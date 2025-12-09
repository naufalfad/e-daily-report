@php
    $title = 'Riwayat Laporan';
    $role = 'penilai'; // Definisi variabel role eksplisit
@endphp

@extends('layouts.app', ['title' => $title, 'role' => $role, 'active' => 'riwayat'])

@section('content')
<section x-data="riwayatDataPenilai('{{ $role }}')" x-init="initPage()">

    {{-- CARD UTAMA --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 flex flex-col min-h-[100vh]">

        {{-- HEADER + BUTTON --}}
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-[20px] font-normal">Riwayat Laporan</h2>

            <button @click="exportPdf()"
                class="rounded-[10px] bg-[#155FA6] text-white px-4 py-2 text-sm hover:brightness-95 shadow-sm">
                Export PDF
            </button>
        </div>

        {{-- FILTER SECTION --}}
        <form class="mt-2 mb-6" @submit.prevent="filterData()">
            <div class="flex flex-col lg:flex-row gap-4 lg:items-end">
                
                {{-- 1. FILTER MODE (KHUSUS PENILAI) --}}
                @if($role === 'penilai')
                <div class="w-full lg:w-48">
                    <label class="block text-xs font-semibold text-slate-600 mb-2">
                        Tampilkan Data
                    </label>
                    <div class="relative">
                        <select x-model="filter.mode" @change="filterData()"
                            class="w-full rounded-[10px] border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#155FA6]/30 focus:border-[#155FA6] appearance-none cursor-pointer shadow-sm">
                            <option value="mine">Riwayat Saya</option>
                            <option value="subordinates">Riwayat Bawahan</option>
                        </select>
                        {{-- Chevron Icon --}}
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 2. FILTER TANGGAL (GRID) --}}
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                    {{-- Dari --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-2">Dari Tanggal</label>
                        <div class="relative">
                            <input x-model="filter.from" id="tgl_dari" type="date"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                            
                            {{-- PERBAIKAN: pointer-events-none DIHAPUS, diganti cursor-pointer --}}
                            <button type="button" id="tgl_dari_btn"
                                class="absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8 flex items-center justify-center cursor-pointer hover:bg-slate-200 rounded-full transition-colors"
                                title="Pilih Tanggal">
                                <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-80">
                            </button>
                        </div>
                    </div>

                    {{-- Sampai --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-2">Sampai Tanggal</label>
                        <div class="relative">
                            <input x-model="filter.to" id="tgl_sampai" type="date"
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                            
                            {{-- PERBAIKAN: pointer-events-none DIHAPUS, diganti cursor-pointer --}}
                            <button type="button" id="tgl_sampai_btn"
                                class="absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8 flex items-center justify-center cursor-pointer hover:bg-slate-200 rounded-full transition-colors"
                                title="Pilih Tanggal">
                                <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-80">
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 3. BUTTON ACTION --}}
                <div class="flex items-end pb-0.5">
                    <button type="submit"
                        class="h-[42px] rounded-[10px] bg-[#0E7A4A] px-6 text-sm font-medium text-white hover:brightness-95 w-full lg:w-auto shadow-sm flex items-center justify-center gap-2 transition-all"
                        :disabled="loading">
                        <span x-show="!loading">Terapkan Filter</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memuat...
                        </span>
                    </button>
                </div>
            </div>
        </form>

        {{-- TABLE --}}
        <div class="overflow-x-auto mt-2 flex-1">
            <table class="w-full min-w-[900px] text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500 uppercase bg-slate-50">
                        <th class="px-3 py-3 font-semibold">Tanggal Laporan</th>
                        <th class="px-3 py-3 font-semibold">Nama Kegiatan</th>

                        {{-- Kolom Dinamis: Pegawai --}}
                        <template x-if="filter.mode === 'subordinates'">
                            <th class="px-3 py-3 font-semibold text-blue-600 bg-blue-50/50">Pegawai</th>
                        </template>

                        <th class="px-3 py-3 font-semibold">Tanggal Verifikasi</th>
                        <th class="px-3 py-3 font-semibold">Pejabat Penilai</th>
                        <th class="px-3 py-3 font-semibold">Status</th>
                        <th class="px-3 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-slate-700 divide-y divide-slate-100">

                    <template x-if="items.length === 0 && !loading">
                        <tr>
                            <td :colspan="role === 'penilai' && filter.mode === 'subordinates' ? 7 : 6"
                                class="px-3 py-12 text-center text-slate-500 italic">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <span x-text="filter.mode === 'mine' ? 'Anda belum memiliki riwayat laporan.' : 'Belum ada riwayat laporan bawahan.'"></span>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="loading">
                        <tr>
                            <td :colspan="role === 'penilai' && filter.mode === 'subordinates' ? 7 : 6"
                                class="px-3 py-12 text-center text-slate-500">
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span>Sedang memuat data...</span>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-for="item in items" :key="item.id">
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-3 py-3 whitespace-nowrap" x-text="formatDate(item.tanggal_laporan)"></td>
                            
                            <td class="px-3 py-3">
                                <div class="font-medium text-slate-800" x-text="item.jenis_kegiatan || '-'"></div>
                                <div class="text-xs text-slate-500 truncate max-w-[200px]" x-text="item.deskripsi_aktivitas"></div>
                            </td>

                            <template x-if="filter.mode === 'subordinates'">
                                <td class="px-3 py-3 whitespace-nowrap bg-blue-50/10">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold" x-text="(item.user.name || '?').charAt(0)"></div>
                                        <span class="font-medium text-slate-700" x-text="item.user.name || '-'"></span>
                                    </div>
                                </td>
                            </template>

                            <td class="px-3 py-3 whitespace-nowrap text-slate-500" x-text="formatDate(item.validated_at)"></td>

                            <td class="px-3 py-3 text-slate-600"
                                x-text="item.atasan ? item.atasan.name : (item.validator ? item.validator.name : '-')">
                            </td>

                            <td class="px-3 py-3">
                                <span :class="statusBadgeClass(item.status)" x-text="statusText(item.status)"></span>
                            </td>

                            <td class="px-3 py-3 text-right">
                                <button @click="openModal(item)"
                                    class="text-blue-600 hover:text-blue-800 text-xs font-medium hover:underline">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                    </template>

                </tbody>
            </table>
        </div>
    </div>
    {{-- END CARD --}}

    {{-- MODAL DETAIL (Tetap Sama) --}}
    <div x-show="open" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm p-4"
        style="display: none;">

        <div @click.outside="open = false"
            class="relative w-full max-w-2xl rounded-2xl bg-white ring-1 ring-slate-200 p-6 shadow-xl overflow-y-auto max-h-[90vh]">

            <button @click="open = false"
                class="absolute top-4 right-5 h-8 w-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor">
                    <path stroke-width="2.5" stroke-linecap="round" d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>

            <h3 class="text-lg font-bold text-slate-800 mb-1">Detail Laporan</h3>
            <p class="text-xs text-slate-500 mb-6">Informasi detail mengenai laporan kinerja.</p>

            <template x-if="modalData">
                <div class="space-y-6 text-sm">
                    
                    {{-- Header Info --}}
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <div>
                            <label class="text-[10px] uppercase font-bold text-slate-400">Tanggal Laporan</label>
                            <p class="text-slate-800 font-medium mt-0.5" x-text="formatDate(modalData.tanggal_laporan)"></p>
                        </div>
                        <div x-show="role === 'penilai' && filter.mode === 'subordinates'">
                            <label class="text-[10px] uppercase font-bold text-slate-400">Pegawai</label>
                            <p class="text-slate-800 font-medium mt-0.5" x-text="modalData.user ? modalData.user.name : '-'"></p>
                        </div>
                        <div class="col-span-2">
                            <label class="text-[10px] uppercase font-bold text-slate-400">Nama Kegiatan</label>
                            <p class="text-slate-800 font-bold text-base mt-0.5" x-text="modalData.jenis_kegiatan"></p>
                        </div>
                        <div class="col-span-2">
                            <label class="text-[10px] uppercase font-bold text-slate-400">Uraian</label>
                            <p class="text-slate-600 mt-0.5 leading-relaxed" x-text="modalData.deskripsi_aktivitas"></p>
                        </div>
                    </div>

                    {{-- Detail Grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="p-3 border border-slate-100 rounded-lg">
                            <label class="block text-[10px] text-slate-400 mb-1">Output</label>
                            <div class="font-semibold text-slate-700" x-text="modalData.output_hasil_kerja"></div>
                        </div>
                        <div class="p-3 border border-slate-100 rounded-lg">
                            <label class="block text-[10px] text-slate-400 mb-1">Waktu</label>
                            <div class="font-semibold text-slate-700">
                                <span x-text="modalData.waktu_mulai.substring(0, 5)"></span> - 
                                <span x-text="modalData.waktu_selesai.substring(0, 5)"></span>
                            </div>
                        </div>
                        <div class="p-3 border border-slate-100 rounded-lg">
                            <label class="block text-[10px] text-slate-400 mb-1">Volume</label>
                            <div class="font-semibold text-slate-700">
                                <span x-text="modalData.volume"></span> <span x-text="modalData.satuan" class="text-xs font-normal text-slate-500"></span>
                            </div>
                        </div>
                        <div class="p-3 border border-slate-100 rounded-lg">
                            <label class="block text-[10px] text-slate-400 mb-1">Kategori</label>
                            <div class="font-semibold text-slate-700" x-text="modalData.skp_rencana_id ? 'SKP' : 'Non-SKP'"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <div>
                            <label class="text-[10px] text-slate-400 block">Lokasi</label>
                            <p class="text-slate-800 font-medium flex items-center gap-1">
                                <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span x-text="getLokasi(modalData)"></span>
                            </p>
                        </div>
                        <button @click="viewBukti(modalData.bukti)" :disabled="!modalData.bukti || modalData.bukti.length === 0"
                            class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                            Lihat Bukti
                        </button>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Status Laporan</label>
                                <div x-html="statusBadgeHtml(modalData.status)"></div>
                            </div>
                            <div class="text-right">
                                <label class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Validator</label>
                                <p class="text-slate-800 font-medium" x-text="modalData.validator ? modalData.validator.name : (modalData.atasan ? modalData.atasan.name : '-')"></p>
                            </div>
                        </div>

                        <div x-show="modalData.komentar_validasi" class="bg-amber-50 border border-amber-100 rounded-lg p-3">
                            <label class="text-[10px] font-bold text-amber-700 uppercase mb-1 block">Catatan Penilai</label>
                            <p class="text-amber-800 text-sm italic" x-text="modalData.komentar_validasi"></p>
                        </div>
                    </div>

                    <div x-show="modalData.status === 'rejected' && role === 'pegawai'" class="flex justify-end pt-2">
                        <button
                            @click="editLaporan(modalData.id)"
                            class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm font-normal text-white hover:brightness-95 shadow-sm">
                            Perbaiki Laporan
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
    {{-- END MODAL --}}

    {{-- MODAL LIST BUKTI DOKUMEN --}}
    <div x-show="openBukti" style="display: none;"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-slate-200"
            @click.outside="openBukti = false" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            {{-- Header Modal --}}
            <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Dokumen Bukti</h3>
                    <p class="text-xs text-slate-500 mt-1">Daftar lampiran aktivitas ini</p>
                </div>
                <button @click="openBukti = false"
                    class="text-slate-400 hover:text-slate-600 transition-colors bg-slate-50 p-1.5 rounded-full hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            {{-- List Dokumen --}}
            <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1 custom-scrollbar">
                <template x-for="(bukti, index) in daftarBukti" :key="index">
                    <a :href="bukti.file_url" target="_blank"
                        class="flex items-center p-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-blue-50 hover:border-blue-200 transition-all group relative overflow-hidden">

                        {{-- Icon Dokumen --}}
                        <div
                            class="h-10 w-10 shrink-0 rounded-lg bg-white flex items-center justify-center text-slate-500 shadow-sm group-hover:text-blue-600 border border-slate-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                        </div>

                        {{-- Info Dokumen --}}
                        <div class="ml-3.5 flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-700 truncate group-hover:text-blue-700"
                                x-text="'Dokumen Lampiran ' + (index + 1)"></p>
                            <div class="flex items-center text-[11px] text-slate-500 mt-0.5 space-x-2">
                                <span class="truncate max-w-[150px]" x-text="bukti.file_url.split('/').pop()"></span>
                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                <span
                                    class="text-blue-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Buka
                                    File</span>
                            </div>
                        </div>

                        {{-- Icon External Link --}}
                        <div class="ml-2 text-slate-300 group-hover:text-blue-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                <polyline points="15 3 21 3 21 9" />
                                <line x1="10" y1="14" x2="21" y2="3" />
                            </svg>
                        </div>
                    </a>
                </template>
            </div>

            {{-- Footer --}}
            <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end">
                <button @click="openBukti = false"
                    class="px-5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors shadow-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</section>

@endsection