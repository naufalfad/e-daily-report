@php
$title = 'Validasi Laporan';

// DATA DUMMY (Tetap dipertahankan untuk display awal/SSR)
$rows = [
    [
        'tanggal_dikirim' => '07 Nov 2025 | 12:30',
        'nama_kegiatan' => 'Rapat Koordinasi Internal',
        'waktu' => '13:00 – 15:30',
        'pegawai' => 'Muhammad Naufal',
        'lokasi' => 'Distrik Mimika',
        'status' => 'waiting_review',
        'detail' => [],
    ]
];
@endphp

@extends('layouts.app', ['title' => $title, 'role' => 'penilai', 'active' => 'validasi'])

@section('content')

{{-- Main Container --}}
<div class="flex flex-col h-full">

    {{-- Header Section & Filter Toolbar --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-2 mb-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Validasi Laporan</h2>
            <p class="text-sm text-slate-500 mt-1">Tinjau dan validasi laporan kinerja harian pegawai.</p>
        </div>

        {{-- Filter Group --}}
        <div class="flex items-center gap-3">

            {{-- 1. Filter Status --}}
            <div class="relative">
                <select id="filter-status"
                    class="appearance-none pl-3 pr-8 py-2 text-sm font-medium border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] transition-all bg-white text-slate-600 shadow-sm cursor-pointer">
                    <option value="waiting_review" selected>Menunggu Review</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                    <option value="all">Semua Status</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            {{-- 2. Filter Bulan --}}
            <div class="relative">
                <select id="filter-month"
                    class="appearance-none pl-3 pr-8 py-2 text-sm font-medium border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] transition-all bg-white text-slate-600 shadow-sm cursor-pointer">
                    <option value="">Semua Bulan</option>
                    @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            {{-- 3. Filter Tahun --}}
            <div class="relative">
                <select id="filter-year"
                    class="appearance-none pl-3 pr-8 py-2 text-sm font-medium border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] transition-all bg-white text-slate-600 shadow-sm cursor-pointer">
                    @foreach(range(date('Y'), date('Y')-1) as $y)
                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            {{-- 4. Search Bar --}}
            <div class="relative group">
                <input type="text" id="filter-search" placeholder="Cari pegawai..."
                    class="pl-10 pr-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] transition-all w-64 shadow-sm">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3 group-focus-within:text-[#1C7C54]"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex-1 flex flex-col">
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead>
                    <tr
                        class="bg-slate-50/50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-semibold">
                        <th class="px-6 py-5 w-[15%]">Tanggal Laporan</th>
                        <th class="px-6 py-5 w-[25%]">Kegiatan</th>
                        <th class="px-6 py-5 w-[15%]">Waktu</th>
                        <th class="px-6 py-5 w-[20%]">Pegawai</th>
                        {{-- NEW: Tambah Kolom Kategori --}}
                        <th class="px-6 py-5 text-center w-[10%]">Kategori</th>
                        <th class="px-6 py-5 text-center w-[10%]">Status</th>
                        <th class="px-6 py-5 text-right w-[5%]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="lkh-validation-list">
                    {{-- DUMMY SSR (Di-replace oleh JS nanti) --}}
                    @foreach($rows as $row)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-6 py-4 align-top">
                            <div class="text-sm font-semibold text-slate-700">
                                {{ explode('|', $row['tanggal_dikirim'])[0] }}</div>
                            <div class="text-xs text-slate-400 mt-1 font-medium">
                                {{ explode('|', $row['tanggal_dikirim'])[1] ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 align-top">
                            <div class="text-sm font-medium text-slate-900">{{ $row['nama_kegiatan'] }}</div>
                        </td>
                        <td class="px-6 py-4 align-top">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">
                                {{ $row['waktu'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 align-top">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-8 w-8 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold shrink-0">
                                    {{ substr($row['pegawai'], 0, 1) }}
                                </div>
                                <span
                                    class="text-sm text-slate-700 font-medium truncate max-w-[150px]">{{ $row['pegawai'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 align-top text-center">
                            <span class="rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-extrabold px-2 py-0.5 tracking-wider">WFO</span>
                        </td>
                        <td class="px-6 py-4 align-top text-center">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-600 border border-amber-100">
                                Pending
                            </span>
                        </td>
                        <td class="px-6 py-4 align-top text-right">
                            <button
                                class="js-open-detail text-sm font-medium text-[#1C7C54] hover:text-[#166443] bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors">
                                Detail
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINATION WRAPPER --}}
        <div class="px-6 py-4 bg-white border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4"
            id="pagination-wrapper">
            
            <span class="text-xs text-slate-500 font-medium" id="pagination-info">Menyiapkan data...</span>
            
            <div class="flex items-center gap-1">
                <button id="prev-page"
                    class="p-2 text-slate-400 hover:text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed transition-all rounded-lg hover:bg-slate-50 active:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div id="pagination-numbers" class="flex items-center gap-1">
                    {{-- JS injection point --}}
                </div>

                <button id="next-page"
                    class="p-2 text-slate-600 hover:text-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition-all rounded-lg hover:bg-slate-50 active:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL DETAIL ================= --}}
<div id="modal-detail" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" data-lkh-id="">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] transition-opacity js-close-detail"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100">
                <div class="bg-white px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Detail Laporan</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Tinjau detail aktivitas pegawai di bawah ini.</p>
                    </div>
                    <button type="button"
                        class="js-close-detail rounded-full p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-all">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-6 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    
                    {{-- Row: Tanggal, Status, Kategori Lokasi --}}
                    <div class="grid grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Tanggal</label>
                            <div id="detail-tanggal" class="text-sm font-bold text-slate-800">-</div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Kategori Lokasi</label>
                            <div id="detail-kategori-lokasi" class="inline-flex">-</div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                            <div id="detail-status" class="inline-flex">-</div>
                        </div>
                    </div>

                    {{-- Row: Identitas Pegawai --}}
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Identitas Pegawai</label>
                        <div class="flex items-center gap-3 mt-1">
                            <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-sm font-bold border border-slate-200">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div id="detail-pegawai" class="text-sm font-bold text-slate-800">-</div>
                                <div id="detail-pegawai-jabatan" class="text-[11px] text-slate-500 mt-0.5">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="h-px bg-slate-100 w-full"></div>

                    {{-- Row: Uraian Kegiatan --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Nama Kegiatan</label>
                            <div id="detail-nama" class="text-base font-bold text-slate-900">-</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Uraian Aktivitas (Deskripsi)</label>
                            <p id="detail-uraian" class="text-sm text-slate-600 leading-relaxed">-</p>
                        </div>
                    </div>

                    {{-- Row: Metrik Kinerja --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="p-3 rounded-lg border border-slate-100 bg-slate-50/50">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Output</label>
                            <div id="detail-output" class="text-sm font-bold text-slate-700">-</div>
                        </div>
                        <div class="p-3 rounded-lg border border-slate-100 bg-slate-50/50">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Volume</label>
                            <div class="text-sm font-bold text-slate-700">
                                <span id="detail-volume">-</span> 
                                <span id="detail-satuan" class="text-[11px] font-semibold text-slate-500"></span>
                            </div>
                        </div>
                        <div class="p-3 rounded-lg border border-slate-100 bg-slate-50/50">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jenis Target</label>
                            <div id="detail-kategori" class="text-sm font-bold text-slate-700">-</div>
                        </div>
                        <div class="p-3 rounded-lg border border-slate-100 bg-slate-50/50">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Waktu</label>
                            <div id="detail-jam" class="text-sm font-bold text-slate-700">-</div>
                        </div>
                    </div>

                    {{-- Row: Lokasi & Bukti --}}
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pt-2 gap-4">
                        <div class="flex-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Titik Lokasi Aktual</label>
                            <div id="detail-lokasi" class="text-sm text-slate-700 font-medium flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>-</span>
                            </div>
                        </div>
                        <div class="shrink-0">
                            <button id="detail-bukti-btn"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-50 border border-indigo-100 rounded-xl text-sm font-bold text-indigo-700 hover:bg-indigo-100 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed js-open-bukti">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                Buka Lampiran Bukti
                            </button>
                        </div>
                    </div>

                    {{-- Row: Catatan Revisi --}}
                    <div id="detail-catatan-wrapper" class="hidden mt-4 bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <div class="flex gap-3">
                            <div class="shrink-0 text-amber-500">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-amber-800">Catatan Historis</h4>
                                <p id="detail-catatan" class="mt-1 text-sm text-amber-700 italic"></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="bg-slate-50 px-6 py-4 flex items-center justify-between border-t border-slate-200">
                    <div id="validation-info" class="hidden text-sm font-bold text-slate-500 italic items-center gap-2">
                        <svg class="w-4 h-4 text-[#1C7C54]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Laporan ini telah selesai divalidasi.
                    </div>
                    <div id="validation-actions" class="flex items-center gap-3 w-full justify-end">
                        <button type="button"
                            class="js-open-reject px-5 py-2.5 bg-white text-rose-600 text-sm font-bold rounded-xl border border-slate-200 hover:bg-rose-50 hover:border-rose-200 transition-all focus:ring-2 focus:ring-rose-500/20">
                            Tolak Revisi
                        </button>
                        <button type="button"
                            class="js-open-approve px-5 py-2.5 bg-[#1C7C54] text-white text-sm font-bold rounded-xl shadow-md shadow-emerald-700/20 hover:bg-[#166443] transition-all focus:ring-2 focus:ring-[#1C7C54]/30 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Terima Laporan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL LIST BUKTI DOKUMEN ================= --}}
<div id="modal-bukti-list"
    class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4" role="dialog"
    aria-modal="true">
    <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-slate-200 animate-slide-up">

        <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Dokumen Bukti</h3>
                <p class="text-xs text-slate-500 mt-1">Daftar lampiran aktivitas ini</p>
            </div>
            <button type="button"
                class="js-close-bukti text-slate-400 hover:text-slate-600 transition-colors bg-slate-50 p-1.5 rounded-full hover:bg-slate-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div id="bukti-list-container" class="grid grid-cols-2 gap-4 max-h-[60vh] overflow-y-auto pr-1 custom-scrollbar">
            {{-- Isi akan di-inject oleh JS --}}
        </div>

    </div>
</div>

{{-- ================= PREVIEW MODAL ================= --}}
<div id="modal-preview" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[70] hidden items-center justify-center p-4"
    role="dialog" aria-modal="true">

    <div class="bg-white rounded-2xl p-2 max-w-4xl w-full shadow-2xl relative">
        <button type="button" class="js-close-preview absolute -top-4 -right-4 bg-white rounded-full p-2 text-slate-500 hover:text-rose-600 shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div id="preview-content" class="w-full bg-slate-100 rounded-xl overflow-hidden min-h-[300px] flex items-center justify-center">
            {{-- Isi akan di-inject oleh JS --}}
        </div>
    </div>
</div>

{{-- ================= MODAL APPROVE ================= --}}
<div id="modal-approve" class="fixed inset-0 z-[60] hidden items-center justify-center" role="dialog">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px]"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transform transition-all animate-slide-up">
        <div class="p-6">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 mb-4 border-4 border-white shadow-sm">
                <svg class="h-7 w-7 text-[#1C7C54]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-center text-slate-800 tracking-tight">Terima Laporan?</h3>
            <p class="text-sm text-center text-slate-500 mt-2">Laporan yang disetujui akan diakumulasikan ke dalam capaian target pegawai.</p>
            <div class="mt-5">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Catatan Apresiasi (Opsional)</label>
                <textarea id="approve-note" rows="3"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-[#1C7C54] focus:ring-[#1C7C54]/20 shadow-sm"
                    placeholder="Tuliskan catatan apresiasi atau pesan..."></textarea>
            </div>
        </div>
        <div class="bg-slate-50 px-6 py-4 flex gap-3 justify-end border-t border-slate-100">
            <button type="button"
                class="js-close-approve w-full inline-flex justify-center rounded-xl border-2 border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all">Batal</button>
            <button type="button" id="btn-submit-approve"
                class="w-full inline-flex justify-center rounded-xl bg-[#1C7C54] px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 hover:bg-[#166443] transition-all focus:outline-none focus:ring-2 focus:ring-[#1C7C54] focus:ring-offset-2">
                Ya, Setujui
            </button>
        </div>
    </div>
</div>

{{-- ================= MODAL REJECT ================= --}}
<div id="modal-reject" class="fixed inset-0 z-[60] hidden items-center justify-center" role="dialog">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px]"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transform transition-all animate-slide-up">
        <div class="p-6">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 mb-4 border-4 border-white shadow-sm">
                <svg class="h-7 w-7 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-center text-slate-800 tracking-tight">Tolak Laporan?</h3>
            <p class="text-sm text-center text-slate-500 mt-2">Pegawai harus merevisi laporan ini. Anda wajib menyertakan alasan penolakan.</p>
            <div class="mt-5">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Alasan Penolakan <span class="text-rose-500">*</span></label>
                <textarea id="reject-note" rows="3"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-rose-500 focus:ring-rose-500/20 shadow-sm"
                    placeholder="Contoh: Bukti foto kurang jelas atau output tidak sesuai..."></textarea>
                <p id="reject-error" class="hidden mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Alasan wajib diisi.
                </p>
            </div>
        </div>
        <div class="bg-slate-50 px-6 py-4 flex gap-3 justify-end border-t border-slate-100">
            <button type="button"
                class="js-close-reject w-full inline-flex justify-center rounded-xl border-2 border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all">Batal</button>
            <button type="button" id="btn-submit-reject"
                class="w-full inline-flex justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-rose-600/20 hover:bg-rose-700 transition-all focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                Tolak Laporan
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/validasi.js') }}"></script>
@endpush