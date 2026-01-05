@php
$title = 'Validasi Laporan';
@endphp

@extends('layouts.app', ['title' => $title, 'role' => 'kadis', 'active' => 'validasi'])

@section('content')

<section class="flex flex-col h-full gap-5">

    {{-- ========================= FILTER CARD ========================= --}}
    <div class="bg-white rounded-2xl p-5 ring-1 ring-slate-200 shadow-sm">
        <h2 class="text-[18px] font-semibold text-slate-800 mb-4">Filter Validasi</h2>

        <form id="filter-form" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">

            {{-- Search --}}
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Cari Pegawai /
                    Aktivitas</label>
                <div class="relative">
                    <input id="filter-search" type="text" placeholder="Ketikkan nama atau kegiatan..."
                        class="w-full pl-9 pr-4 py-2 rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54] focus:border-[#1C7C54] shadow-sm">
                    <i class="fas fa-search absolute left-3 top-3 text-slate-400 text-xs"></i>
                </div>
            </div>

            {{-- Bulan --}}
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Bulan</label>
                <select id="filter-month"
                    class="w-full rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54] focus:border-[#1C7C54] cursor-pointer">
                    @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Tahun --}}
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Tahun</label>
                <select id="filter-year"
                    class="w-full rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54] focus:border-[#1C7C54] cursor-pointer">
                    @for ($y = date('Y'); $y >= 2023; $y--)
                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                    @endfor
                </select>
            </div>

            {{-- STATUS --}}
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Status</label>
                <select id="filter-status"
                    class="w-full rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54] focus:border-[#1C7C54] cursor-pointer">
                    <option value="waiting_review" selected>Menunggu Review</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                    <option value="all">Semua Status</option>
                </select>
            </div>

            {{-- TOMBOL FILTER --}}
            <div class="md:col-span-2">
                <button type="submit"
                    class="w-full bg-[#1C7C54] hover:bg-[#156343] text-white py-2 px-4 rounded-lg text-sm font-medium transition shadow-sm flex items-center justify-center gap-2">
                    <i class="fas fa-filter"></i> Terapkan
                </button>
            </div>
        </form>
    </div>

    {{-- ========================= TABEL CARD ========================= --}}
    <div class="flex-1 bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden flex flex-col">
        
        {{-- Table Container --}}
        <div class="overflow-x-auto flex-1">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-slate-50 text-[12px] uppercase text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Pegawai</th>
                        <th class="px-6 py-4">Kegiatan</th>
                        <th class="px-6 py-4 text-center">Waktu</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="lkh-validation-list" class="divide-y divide-slate-100 text-slate-700">
                    {{-- JS inject row here --}}
                </tbody>
            </table>
        </div>

        {{-- 
            PAGINATION WRAPPER (NEW STRUCTURE) 
            Konsisten dengan modul Penilai, menggunakan FontAwesome untuk ikon.
        --}}
        <div class="px-6 py-4 bg-white border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4"
            id="pagination-wrapper">
            
            {{-- Info Paginasi --}}
            <span class="text-xs text-slate-500 font-medium" id="pagination-info">Menyiapkan data...</span>
            
            {{-- Kontrol Paginasi --}}
            <div class="flex items-center gap-1">
                {{-- Tombol Previous --}}
                <button id="prev-page"
                    class="p-2 text-slate-400 hover:text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed transition-all rounded-lg hover:bg-slate-50 active:bg-slate-100">
                    <i class="fas fa-chevron-left"></i>
                </button>

                {{-- Container Angka Halaman (Akan diisi oleh JS) --}}
                <div id="pagination-numbers" class="flex items-center gap-1">
                    {{-- JS injection point: <button class="...">1</button> ... --}}
                </div>

                {{-- Tombol Next --}}
                <button id="next-page"
                    class="p-2 text-slate-600 hover:text-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition-all rounded-lg hover:bg-slate-50 active:bg-slate-100">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

</section>

{{-- ====================================================================== --}}
{{-- ========================= MODAL DETAIL =============================== --}}
{{-- ====================================================================== --}}
<div id="modal-detail" class="fixed inset-0 z-40 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div
        class="bg-white rounded-2xl shadow-2xl w-[95vw] max-w-3xl max-h-[90vh] overflow-y-auto transform transition-all scale-100">

        {{-- Header --}}
        <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200 sticky top-0 bg-white">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Detail Laporan Harian</h3>
                <p class="text-xs text-slate-500">Validasi kinerja bawahan Anda</p>
            </div>

            <button
                class="js-close-detail h-8 w-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-6">

            {{-- Informasi Pegawai --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <span class="text-xs font-semibold text-slate-400 uppercase">Informasi
                        Pegawai</span>
                    <div id="detail-pegawai" class="font-bold text-slate-800 mt-1">-</div>
                    <div id="detail-tanggal" class="text-sm text-slate-600">-</div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase">Status Saat
                            Ini</span>
                        <div id="detail-status" class="mt-1">-</div>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase">Kategori</span>
                        <div id="detail-kategori" class="font-medium text-slate-700 mt-1">-</div>
                    </div>
                </div>
            </div>

            {{-- Detail Kegiatan --}}
            <div>
                <h4 class="text-sm font-bold text-slate-800 mb-2 flex items-center gap-2">
                    <i class="fas fa-tasks text-[#1C7C54]"></i> Uraian Kegiatan
                </h4>
                <div class="p-4 rounded-xl border border-slate-200 bg-white text-sm text-slate-600 leading-relaxed">
                    <div id="detail-nama" class="font-semibold text-slate-800 mb-1">-</div>
                    <div id="detail-uraian">-</div>
                </div>
            </div>

            {{-- Grid Metrik --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-3 rounded-lg border border-slate-100 text-center">
                    <div class="text-xs text-slate-400 mb-1">Output</div>
                    <div id="detail-output" class="font-semibold text-slate-700">-</div>
                </div>

                <div class="p-3 rounded-lg border border-slate-100 text-center">
                    <div class="text-xs text-slate-400 mb-1">Volume</div>
                    <div id="detail-volume" class="font-semibold text-slate-700">-</div>
                </div>

                <div class="p-3 rounded-lg border border-slate-100 text-center">
                    <div class="text-xs text-slate-400 mb-1">Jam Mulai</div>
                    <div id="detail-jam-mulai" class="font-semibold text-slate-700">-</div>
                </div>

                <div class="p-3 rounded-lg border border-slate-100 text-center">
                    <div class="text-xs text-slate-400 mb-1">Jam Selesai</div>
                    <div id="detail-jam-selesai" class="font-semibold text-slate-700">-</div>
                </div>
            </div>

            {{-- Lokasi + Bukti --}}
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="text-xs font-semibold text-slate-500 mb-1">Lokasi</div>
                    <div id="detail-lokasi"
                        class="bg-slate-50 px-3 py-2 border border-slate-100 rounded-lg truncate text-sm">
                        -
                    </div>
                </div>

                <div class="flex-1">
                    <div class="text-xs font-semibold text-slate-500 mb-1">Bukti Dukung</div>
                    <button type="button" id="detail-bukti-btn"
                        class="js-open-bukti w-full flex items-center justify-center gap-2 bg-blue-50 text-blue-600 px-3 py-2 rounded-lg border border-blue-200 hover:bg-blue-100 text-sm">
                        <i class="fas fa-file-image"></i> Lihat Bukti
                    </button>
                </div>
            </div>

            {{-- Catatan Validator --}}
            <div id="detail-catatan-wrapper" class="hidden bg-yellow-50 p-4 border border-yellow-100 rounded-xl">
                <div class="text-xs font-bold text-yellow-700 mb-1 uppercase">Catatan Validasi</div>
                <div id="detail-catatan" class="text-sm text-yellow-800 italic"></div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div id="validation-actions" class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">

            <button
                class="js-open-reject px-5 py-2.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-sm font-medium">
                <i class="fas fa-times-circle"></i> Tolak
            </button>

            <button
                class="js-open-approve px-5 py-2.5 rounded-xl bg-[#1C7C54] text-white hover:bg-[#156343] text-sm font-medium shadow">
                <i class="fas fa-check-circle"></i> Setujui
            </button>
        </div>

        {{-- Sudah tervalidasi --}}
        <div id="validation-info" class="hidden px-6 py-4 text-center bg-slate-50 border-t border-slate-200">
            <span class="text-sm text-slate-500 flex items-center justify-center gap-2">
                <i class="fas fa-lock"></i> Laporan ini telah divalidasi.
            </span>
        </div>
    </div>
</div>

{{-- ================= MODAL LIST BUKTI DOKUMEN (NEW) ================= --}}
<div id="modal-bukti-list"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-slate-200">

        <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Dokumen Bukti</h3>
                <p class="text-xs text-slate-500 mt-1">Daftar lampiran aktivitas ini</p>
            </div>
            <button type="button"
                class="js-close-bukti text-slate-400 hover:text-slate-600 transition-colors bg-slate-50 p-1.5 rounded-full hover:bg-slate-100">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="bukti-list-container"
            class="grid grid-cols-2 sm:grid-cols-3 gap-4 max-h-[60vh] overflow-y-auto pr-1 custom-scrollbar">
            {{-- Isi akan di-inject oleh JS --}}
        </div>


        <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end">
            <button type="button"
                class="js-close-bukti px-5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors shadow-sm">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- ================= PREVIEW MODAL (NEW) ================= --}}
<div id="modal-preview"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[70] hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl p-4 max-w-3xl w-full">
        <button type="button"
            class="js-close-preview float-right text-slate-500 hover:text-slate-700 text-xl font-bold">
            &times;
        </button>
        <div id="preview-content" class="mt-8">
            {{-- Isi akan di-inject oleh JS --}}
        </div>
    </div>
</div>

{{-- ================= MODAL APPROVE (TIDAK BERUBAH) ================= --}}
<div id="modal-approve" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 transform scale-100 transition-all">

        <div class="text-center mb-6">
            <div
                class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl mb-4">
                <i class="fas fa-check"></i>
            </div>
            <h3 class="text-lg font-bold">Setujui Laporan?</h3>
            <p class="text-sm text-slate-500 mt-1">Laporan akan ditandai sebagai
                disetujui.
            </p>
        </div>

        <textarea id="approve-note" rows="2"
            class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-emerald-500 focus:border-emerald-500 mb-4"
            placeholder="Catatan opsional..."></textarea>

        <div class="flex gap-3">
            <button type="button"
                class="js-close-approve flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">
                Batal
            </button>

            <button id="btn-submit-approve"
                class="flex-1 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-200">
                Ya, Setujui
            </button>
        </div>
    </div>
</div>

{{-- ================= MODAL REJECT (TIDAK BERUBAH) ================= --}}
<div id="modal-reject" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 transform scale-100 transition-all">
        <div class="text-center mb-6">
            <div
                class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-600 text-2xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Tolak Laporan?</h3>
            <p class="text-sm text-slate-500 mt-1">Berikan alasan penolakan agar staf
                dapat
                memperbaikinya.</p>
        </div>

        <textarea id="reject-note" rows="3"
            class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-rose-500 focus:border-rose-500"
            placeholder="Alasan penolakan (Wajib)..."></textarea>

        <p id="reject-error" class="hidden text-xs text-rose-600 mb-3">* Alasan wajib diisi.</p>

        <div class="flex gap-3 mt-4">
            <button type="button"
                class="js-close-reject flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition">
                Batal
            </button>

            <button id="btn-submit-reject"
                class="flex-1 py-2.5 rounded-xl bg-rose-600 text-white font-medium hover:bg-rose-700 shadow-lg shadow-rose-200">
                Tolak Laporan
            </button>
        </div>

    </div>
</div>
@endsection