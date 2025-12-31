@php
    $title = 'Riwayat Laporan';
    $role = 'staf';
@endphp

@extends('layouts.app', ['title' => $title, 'role' => $role, 'active' => 'riwayat'])

@section('content')
    {{-- Inisialisasi Alpine Component dengan Role Staf --}}
    <section x-data="riwayatCore('{{ $role }}')" x-init="initPage()" class="font-poppins">

        {{-- CARD UTAMA --}}
        <div class="bg-white rounded-[24px] shadow-sm border border-slate-200 flex flex-col min-h-[85vh] overflow-hidden">

            {{-- HEADER + TOOLBAR --}}
            <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">Riwayat Laporan</h2>
                    <p class="text-sm text-slate-500 mt-1">Arsip kinerja dan aktivitas harian Anda</p>
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
                    <div class="flex flex-col md:flex-row gap-5 items-end">

                        {{-- 1. FILTER TANGGAL --}}
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Dari Tanggal</label>
                                <div class="relative">
                                    <input x-model="filter.from" id="tgl_dari" type="date"
                                        class="w-full rounded-xl border-slate-200 bg-white py-2.5 px-4 text-sm focus:border-[#0E7A4A] focus:ring-[#0E7A4A]/20 shadow-sm cursor-pointer transition-all hover:border-slate-300 text-slate-600 placeholder-slate-400" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Sampai Tanggal</label>
                                <div class="relative">
                                    <input x-model="filter.to" id="tgl_sampai" type="date"
                                        class="w-full rounded-xl border-slate-200 bg-white py-2.5 px-4 text-sm focus:border-[#0E7A4A] focus:ring-[#0E7A4A]/20 shadow-sm cursor-pointer transition-all hover:border-slate-300 text-slate-600 placeholder-slate-400" />
                                </div>
                            </div>
                        </div>

                        {{-- 2. BUTTON ACTION --}}
                        <div class="w-full md:w-auto">
                            <button type="submit"
                                class="w-full md:w-auto h-[42px] px-8 bg-[#0E7A4A] hover:bg-[#0b633b] text-white rounded-xl text-sm font-bold shadow-md shadow-emerald-100 hover:shadow-lg hover:shadow-emerald-200 transition-all active:scale-[0.98] flex items-center justify-center gap-2"
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
                <table class="w-full min-w-[900px] text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-y border-slate-200">
                        <tr>
                            <th class="px-6 py-4 font-bold tracking-wider">Tanggal Laporan</th>
                            <th class="px-6 py-4 font-bold tracking-wider w-[35%]">Aktivitas</th>
                            <th class="px-6 py-4 font-bold tracking-wider">Tanggal Validasi</th>
                            <th class="px-6 py-4 font-bold tracking-wider">Pejabat Penilai</th>
                            <th class="px-6 py-4 font-bold tracking-wider text-center">Status</th>
                            <th class="px-6 py-4 font-bold tracking-wider text-right">Opsi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">

                        {{-- STATE: EMPTY --}}
                        <template x-if="items.length === 0 && !loading">
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-slate-800 font-bold text-base">Tidak ada data</h3>
                                        <p class="text-slate-500 text-xs mt-1">Anda belum memiliki riwayat laporan pada periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        {{-- STATE: LOADING --}}
                        <template x-if="loading">
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center">
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
            
            {{-- [MODIFIKASI] CALL COMPONENT PAGINATION --}}
            {{-- Menggantikan placeholder div kosong --}}
            <x-riwayat.pagination />

        </div>

        {{-- CALL COMPONENTS: MODAL --}}
        {{-- Kita panggil 3 komponen terpisah untuk membuat file utama tetap bersih --}}
        <x-riwayat.modal-detail />
        <x-riwayat.modal-bukti />
        <x-riwayat.modal-preview />

    </section>
@endsection