@php
    $title = 'Riwayat Laporan';
    $role = 'penilai';
@endphp

@extends('layouts.app', ['title' => $title, 'role' => $role, 'active' => 'riwayat'])

@section('content')
    <section x-data="riwayatDataPenilai('{{ $role }}')" x-init="initPage()" class="font-poppins">

        {{-- CARD UTAMA --}}
        <div class="bg-white rounded-[24px] shadow-sm border border-slate-200 flex flex-col min-h-[85vh] overflow-hidden">

            {{-- HEADER + TOOLBAR --}}
            <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">Riwayat Laporan</h2>
                    <p class="text-sm text-slate-500 mt-1">Arsip dan histori kinerja pegawai</p>
                </div>

                <button @click="exportPdf()"
                    class="group flex items-center gap-2 bg-white text-slate-700 border border-slate-300 px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:text-[#155FA6] hover:border-[#155FA6] transition-all shadow-sm active:scale-[0.98]">
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-[#155FA6] transition-colors" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export PDF
                </button>
            </div>

            {{-- FILTER SECTION --}}
            <div class="p-6 bg-slate-50/50">
                <form @submit.prevent="filterData()">
                    <div class="flex flex-col lg:flex-row gap-5 items-end">

                        {{-- 1. FILTER MODE (KHUSUS PENILAI) --}}
                        @if ($role === 'penilai')
                            <div class="w-full lg:w-56">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">
                                    Tampilkan Data
                                </label>
                                <div class="relative group">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#155FA6]">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <select x-model="filter.mode" @change="filterData()"
                                        class="w-full pl-10 pr-10 rounded-xl border-slate-200 bg-white py-2.5 text-sm font-medium text-slate-700 focus:border-[#155FA6] focus:ring-[#155FA6]/20 shadow-sm cursor-pointer transition-all hover:border-slate-300 appearance-none">
                                        <option value="mine">Riwayat Saya</option>
                                        <option value="subordinates">Riwayat Bawahan</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- 2. FILTER TANGGAL --}}
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Dari</label>
                                <div class="relative">
                                    <input x-model="filter.from" type="date"
                                        class="w-full rounded-xl border-slate-200 bg-white py-2.5 px-4 text-sm focus:border-[#0E7A4A] focus:ring-[#0E7A4A]/20 shadow-sm cursor-pointer transition-all hover:border-slate-300" />
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Sampai</label>
                                <div class="relative">
                                    <input x-model="filter.to" type="date"
                                        class="w-full rounded-xl border-slate-200 bg-white py-2.5 px-4 text-sm focus:border-[#0E7A4A] focus:ring-[#0E7A4A]/20 shadow-sm cursor-pointer transition-all hover:border-slate-300" />
                                </div>
                            </div>
                        </div>

                        {{-- 3. TOMBOL TERAPKAN --}}
                        <div class="w-full lg:w-auto">
                            <button type="submit"
                                class="w-full lg:w-auto h-[42px] px-8 bg-[#0E7A4A] hover:bg-[#0b633b] text-white rounded-xl text-sm font-bold shadow-md shadow-emerald-100 hover:shadow-lg hover:shadow-emerald-200 transition-all active:scale-[0.98] flex items-center justify-center gap-2"
                                :disabled="loading">
                                <span x-show="!loading">Terapkan Filter</span>
                                <span x-show="loading" class="flex items-center gap-2" style="display: none;">
                                    <svg class="animate-spin h-4 w-4 text-white/90" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Memuat...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- TABLE SECTION --}}
            <div class="flex-1 overflow-x-auto relative">
                <table class="w-full min-w-[1000px] text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-y border-slate-200">
                        <tr>
                            <th class="px-6 py-4 font-bold tracking-wider">Tanggal</th>
                            <th class="px-6 py-4 font-bold tracking-wider w-[30%]">Aktivitas</th>

                            <template x-if="filter.mode === 'subordinates'">
                                <th class="px-6 py-4 font-bold tracking-wider text-[#155FA6]">Pegawai</th>
                            </template>

                            <th class="px-6 py-4 font-bold tracking-wider">Tanggal Validasi</th>
                            <th class="px-6 py-4 font-bold tracking-wider">Penilai</th>
                            <th class="px-6 py-4 font-bold tracking-wider text-center">Status</th>
                            <th class="px-6 py-4 font-bold tracking-wider text-right">Opsi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        
                        {{-- STATE: EMPTY --}}
                        <template x-if="items.length === 0 && !loading">
                            <tr>
                                <td :colspan="role === 'penilai' && filter.mode === 'subordinates' ? 7 : 6"
                                    class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-slate-800 font-bold text-base">Tidak ada data</h3>
                                        <p class="text-slate-500 text-xs mt-1 max-w-xs mx-auto"
                                            x-text="filter.mode === 'mine' ? 'Anda belum memiliki riwayat laporan pada periode ini.' : 'Belum ada riwayat laporan dari bawahan pada periode ini.'">
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        {{-- STATE: LOADING --}}
                        <template x-if="loading">
                            <tr>
                                <td :colspan="role === 'penilai' && filter.mode === 'subordinates' ? 7 : 6"
                                    class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0E7A4A]"></div>
                                        <span class="text-slate-500 font-medium animate-pulse">Sedang memuat data...</span>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        {{-- STATE: DATA LIST --}}
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap align-top">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                                        <span class="font-semibold text-slate-700" x-text="formatDate(item.tanggal_laporan)"></span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 align-top">
                                    <div class="font-bold text-slate-800 mb-1" x-text="item.jenis_kegiatan || '-'"></div>
                                    <div class="text-xs text-slate-500 line-clamp-2 leading-relaxed"
                                        x-text="item.deskripsi_aktivitas"></div>
                                </td>

                                <template x-if="filter.mode === 'subordinates'">
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-[#E0F2FE] text-[#0369A1] flex items-center justify-center text-xs font-bold border border-[#BAE6FD]"
                                                x-text="(item.user.name || '?').charAt(0)"></div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-700 text-xs" x-text="item.user.name || '-'"></span>
                                                <span class="text-[10px] text-slate-400">Staf</span>
                                            </div>
                                        </div>
                                    </td>
                                </template>

                                <td class="px-6 py-4 whitespace-nowrap align-top text-slate-500 text-xs"
                                    x-text="item.waktu_validasi ? formatDate(item.waktu_validasi) : '-'"></td>

                                <td class="px-6 py-4 align-top text-xs text-slate-600 font-medium"
                                    x-text="item.atasan ? item.atasan.name : (item.validator ? item.validator.name : '-')">
                                </td>

                                <td class="px-6 py-4 align-top text-center">
                                    <div class="inline-flex" x-html="statusBadgeHtml(item.status)"></div>
                                </td>

                                <td class="px-6 py-4 align-top text-right">
                                    <button @click="openModal(item)"
                                        class="inline-flex items-center gap-1.5 text-[#155FA6] hover:text-[#0C4A85] bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                        <span>Detail</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            
            {{-- PAGINATION (Opsional placeholder jika JS handle pagination) --}}
            <div class="p-4 border-t border-slate-100 bg-slate-50/30 flex justify-center">
                {{-- Pagination controls render here --}}
            </div>
        </div>


        {{-- ================= MODAL DETAIL ================= --}}
        <div x-show="open" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
            style="display: none;">

            <div @click.outside="open = false"
                class="relative w-full max-w-2xl bg-white rounded-[20px] shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-slide-up">

                {{-- Header Modal --}}
                <div class="bg-gradient-to-r from-slate-50 to-white px-6 py-5 border-b border-slate-100 flex items-start justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Detail Laporan</h3>
                        <p class="text-xs text-slate-500 mt-1">ID Laporan: <span class="font-mono text-slate-400" x-text="'#'+modalData?.id"></span></p>
                    </div>
                    <button @click="open = false"
                        class="text-slate-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-all">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto custom-scrollbar">
                    <template x-if="modalData">
                        <div class="space-y-6">

                            {{-- Info Utama Card --}}
                            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 relative overflow-hidden">
                                {{-- Background Decor --}}
                                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-100/50 rounded-bl-[80px] -mr-4 -mt-4 pointer-events-none"></div>

                                <div class="relative z-10 grid grid-cols-2 gap-y-5 gap-x-8">
                                    
                                    {{-- Tanggal --}}
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Kegiatan</label>
                                        <div class="flex items-center gap-2 mt-1">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <p class="text-sm font-bold text-slate-800" x-text="formatDate(modalData.tanggal_laporan)"></p>
                                        </div>
                                    </div>

                                    {{-- Status --}}
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Saat Ini</label>
                                        <div class="mt-1" x-html="statusBadgeHtml(modalData.status)"></div>
                                    </div>

                                    {{-- Kegiatan (Full Width) --}}
                                    <div class="col-span-2 border-t border-slate-200/60 pt-4 mt-1">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kegiatan</label>
                                        <p class="text-lg font-bold text-slate-800 leading-snug mt-1" x-text="modalData.jenis_kegiatan"></p>
                                        <p class="text-sm text-slate-600 mt-2 leading-relaxed bg-white p-3 rounded-lg border border-slate-200/50" x-text="modalData.deskripsi_aktivitas"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Statistik Grid --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="bg-white border border-slate-200 p-3 rounded-xl hover:border-blue-300 transition-colors group">
                                    <label class="text-[10px] text-slate-400 block mb-1 group-hover:text-blue-500">Output</label>
                                    <p class="font-bold text-slate-700 text-sm truncate" x-text="modalData.output_hasil_kerja"></p>
                                </div>
                                <div class="bg-white border border-slate-200 p-3 rounded-xl hover:border-blue-300 transition-colors group">
                                    <label class="text-[10px] text-slate-400 block mb-1 group-hover:text-blue-500">Volume</label>
                                    <p class="font-bold text-slate-700 text-sm">
                                        <span x-text="modalData.volume"></span> <span x-text="modalData.satuan" class="text-xs font-normal text-slate-500"></span>
                                    </p>
                                </div>
                                <div class="bg-white border border-slate-200 p-3 rounded-xl hover:border-blue-300 transition-colors group">
                                    <label class="text-[10px] text-slate-400 block mb-1 group-hover:text-blue-500">Jam</label>
                                    <p class="font-bold text-slate-700 text-sm">
                                        <span x-text="modalData.waktu_mulai.substring(0, 5)"></span> - <span x-text="modalData.waktu_selesai.substring(0, 5)"></span>
                                    </p>
                                </div>
                                <div class="bg-white border border-slate-200 p-3 rounded-xl hover:border-blue-300 transition-colors group">
                                    <label class="text-[10px] text-slate-400 block mb-1 group-hover:text-blue-500">Tipe</label>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold"
                                        :class="modalData.skp_rencana_id ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-600 border border-slate-200'">
                                        <span x-text="modalData.skp_rencana_id ? 'TARGET SKP' : 'NON-SKP'"></span>
                                    </span>
                                </div>
                            </div>

                            {{-- Lokasi & Bukti --}}
                            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between bg-slate-50 px-4 py-3 rounded-xl border border-slate-200 border-dashed">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lokasi Pengerjaan</label>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="text-sm font-medium text-slate-700 truncate max-w-[200px]" x-text="getLokasi(modalData)"></span>
                                    </div>
                                </div>
                                <button @click="viewBukti(modalData.bukti)"
                                    :disabled="!modalData.bukti || modalData.bukti.length === 0"
                                    class="w-full sm:w-auto px-4 py-2 bg-white text-slate-700 border border-slate-300 rounded-lg text-xs font-bold hover:bg-slate-50 hover:text-blue-600 hover:border-blue-300 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    Lihat Lampiran
                                </button>
                            </div>

                            {{-- Validator Info --}}
                            <div class="border-t border-slate-100 pt-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500">Diverifikasi oleh</p>
                                        <p class="text-sm font-bold text-slate-800" x-text="modalData.validator ? modalData.validator.name : (modalData.atasan ? modalData.atasan.name : '-')"></p>
                                    </div>
                                </div>

                                {{-- Komentar --}}
                                <div x-show="modalData.komentar_validasi" class="bg-amber-50 border border-amber-200 rounded-xl p-4 relative mt-2">
                                    <div class="absolute -top-2 left-4 w-4 h-4 bg-amber-50 border-t border-l border-amber-200 transform rotate-45"></div>
                                    <p class="text-xs font-bold text-amber-800 uppercase mb-1">Catatan Penolakan:</p>
                                    <p class="text-sm text-amber-900 italic leading-relaxed">"<span x-text="modalData.komentar_validasi"></span>"</p>
                                </div>
                            </div>

                        </div>
                    </template>
                </div>

                {{-- Footer Action --}}
                <div class="p-5 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                    {{-- TOMBOL EDIT/PERBAIKI --}}
                    <template x-if="(modalData?.status === 'rejected' || modalData?.status === 'draft') && filter.mode === 'mine'">
                        <button @click="editLaporan(modalData.id)"
                            class="px-5 py-2.5 rounded-xl text-sm font-bold text-white shadow-md shadow-emerald-100 hover:shadow-lg hover:shadow-emerald-200 transition-all flex items-center gap-2"
                            :class="modalData.status === 'draft' ? 'bg-slate-600 hover:bg-slate-700' : 'bg-[#0E7A4A] hover:bg-[#0b633b]'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span x-text="modalData.status === 'draft' ? 'Lanjutkan Edit' : 'Perbaiki Laporan'"></span>
                        </button>
                    </template>
                    
                    <button @click="open = false"
                        class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </div>


        {{-- ================= MODAL BUKTI ================= --}}
        <div x-show="openBukti" style="display: none;"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="relative w-full max-w-lg bg-white rounded-[24px] p-6 shadow-2xl"
                @click.outside="openBukti = false" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Lampiran Bukti</h3>
                        <p class="text-sm text-slate-500">Dokumen pendukung aktivitas</p>
                    </div>
                    <button @click="openBukti = false" class="bg-slate-100 p-2 rounded-full hover:bg-slate-200 transition-colors">
                        <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                    <template x-for="(bukti, index) in daftarBukti" :key="index">
                        <div class="group relative bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden hover:border-[#155FA6] hover:shadow-md transition-all cursor-pointer"
                            @click="preview(bukti)">
                            
                            {{-- THUMBNAIL AREA --}}
                            <div class="h-32 bg-slate-100 flex items-center justify-center overflow-hidden relative">
                                
                                {{-- IMAGE --}}
                                <template x-if="getFileType(bukti.file_url) === 'image'">
                                    <img :src="bukti.file_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                </template>

                                {{-- PDF --}}
                                <template x-if="getFileType(bukti.file_url) === 'pdf'">
                                    <div class="flex flex-col items-center gap-2 text-red-500">
                                        <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        <span class="text-[10px] font-bold">PDF FILE</span>
                                    </div>
                                </template>

                                {{-- VIDEO --}}
                                <template x-if="getFileType(bukti.file_url) === 'video'">
                                    <div class="flex flex-col items-center gap-2 text-blue-500">
                                        <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <span class="text-[10px] font-bold">VIDEO</span>
                                    </div>
                                </template>

                                {{-- OTHER --}}
                                <template x-if="getFileType(bukti.file_url) === 'other'">
                                    <div class="flex flex-col items-center gap-2 text-slate-400">
                                        <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="text-[10px] font-bold">FILE</span>
                                    </div>
                                </template>

                                {{-- Overlay Hover --}}
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <span class="bg-white/90 px-3 py-1 rounded-full text-xs font-bold text-slate-800 shadow-sm backdrop-blur-sm">Lihat</span>
                                </div>
                            </div>

                            {{-- Footer Item --}}
                            <div class="p-3 bg-white">
                                <p class="text-xs font-bold text-slate-700 truncate" x-text="'Lampiran #' + (index + 1)"></p>
                                <p class="text-[10px] text-slate-400 truncate mt-0.5" x-text="bukti.file_url.split('/').pop()"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>


        {{-- ================= PREVIEW MODAL ================= --}}
        <div x-show="showPreview"
            class="fixed inset-0 bg-black/80 backdrop-blur-md z-[70] flex items-center justify-center p-4 md:p-8"
            style="display:none;" @click.self.stop="showPreview = false"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="relative bg-white rounded-2xl overflow-hidden shadow-2xl max-w-4xl w-full flex flex-col max-h-[90vh]">
                
                {{-- Header Preview --}}
                <div class="flex items-center justify-between px-4 py-3 bg-slate-900 text-white shrink-0">
                    <span class="text-sm font-medium truncate opacity-90" x-text="selectedBukti ? selectedBukti.file_url.split('/').pop() : 'Preview'"></span>
                    <button @click.stop="showPreview = false" class="text-white/70 hover:text-white p-1 rounded-md hover:bg-white/10 transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Content Preview --}}
                <div class="flex-1 bg-slate-100 overflow-y-auto flex items-center justify-center p-4">
                    
                    <template x-if="selectedBukti && getFileType(selectedBukti.file_url) === 'image'">
                        <img :src="selectedBukti.file_url" class="max-w-full max-h-full rounded shadow-lg object-contain" />
                    </template>

                    <template x-if="selectedBukti && getFileType(selectedBukti.file_url) === 'pdf'">
                        <iframe :src="selectedBukti.file_url" class="w-full h-full min-h-[500px] rounded-lg shadow border border-slate-300"></iframe>
                    </template>

                    <template x-if="selectedBukti && getFileType(selectedBukti.file_url) === 'video'">
                        <video controls class="max-w-full max-h-full rounded-lg shadow-lg bg-black">
                            <source :src="selectedBukti.file_url" type="video/mp4">
                            Browser tidak support video.
                        </video>
                    </template>

                    <template x-if="selectedBukti && getFileType(selectedBukti.file_url) === 'other'">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-slate-600 font-medium mb-4">File ini tidak dapat dipreview.</p>
                            <a :href="selectedBukti.file_url" target="_blank"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#155FA6] text-white rounded-xl font-bold hover:bg-[#0f4a85] transition shadow-lg shadow-blue-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download File
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </section>
@endsection