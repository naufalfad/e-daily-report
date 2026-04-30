@php
$title = 'Validasi Laporan';
@endphp

@extends('layouts.app', ['title' => $title, 'role' => 'kadis', 'active' => 'validasi'])

@section('content')

<section class="flex flex-col h-full gap-5">

    {{-- ========================= FILTER CARD ========================= --}}
    <div class="bg-white rounded-2xl p-5 ring-1 ring-slate-200 shadow-sm">
        <h2 class="text-[18px] font-semibold text-slate-800 mb-4">Filter Validasi</h2>

        <form id="filter-form" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">

            {{-- Search --}}
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Cari Pegawai /
                    Aktivitas</label>
                <div class="relative">
                    <input id="filter-search" type="text" placeholder="Ketik nama / kegiatan..."
                        class="w-full pl-9 pr-4 py-2 rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54] focus:border-[#1C7C54] shadow-sm">
                    <i class="fas fa-search absolute left-3 top-3 text-slate-400 text-xs"></i>
                </div>
            </div>

            {{-- Bulan --}}
            <div class="lg:col-span-2">
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
            <div class="lg:col-span-2">
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

            {{-- [NEW] KATEGORI LOKASI --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Kategori</label>
                <select id="filter-kategori"
                    class="w-full rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54] focus:border-[#1C7C54] cursor-pointer">
                    <option value="all" selected>Semua Kategori</option>
                    <option value="WFO">WFO (Office)</option>
                    <option value="WFH">WFH (Home)</option>
                    <option value="WFA">WFA (Anywhere)</option>
                    <option value="DL">Dinas Luar</option>
                </select>
            </div>

            {{-- STATUS --}}
            <div class="lg:col-span-2">
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
            <div class="sm:col-span-2 lg:col-span-1">
                <button type="submit"
                    class="w-full bg-[#1C7C54] hover:bg-[#156343] text-white py-2 px-2 rounded-lg text-sm font-medium transition shadow-sm flex items-center justify-center gap-1.5" title="Terapkan Filter">
                    <i class="fas fa-filter"></i> <span class="lg:hidden">Terapkan</span>
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
                        {{-- NEW: Tambahan Kolom Kategori --}}
                        <th class="px-6 py-4 text-center">Kategori</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="lkh-validation-list" class="divide-y divide-slate-100 text-slate-700">
                    {{-- JS inject row here --}}
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
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div id="pagination-numbers" class="flex items-center gap-1">
                    {{-- JS injection point --}}
                </div>

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
        <div class="flex justify-between items-start px-6 py-4 border-b border-slate-200 sticky top-0 bg-white z-10">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Detail Laporan Harian</h3>
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-xs text-slate-500">Validasi kinerja bawahan Anda</p>
                    {{-- NEW: Container Badge Kategori Lokasi --}}
                    <div id="detail-kategori-lokasi"></div>
                </div>
            </div>

            <button
                class="js-close-detail h-8 w-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
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
                        <span class="text-xs font-semibold text-slate-400 uppercase">Target Kinerja</span>
                        <div id="detail-kategori" class="font-medium text-slate-700 mt-1">-</div>
                    </div>
                </div>
            </div>

            {{-- Detail Kegiatan --}}
            <div>
                <h4 class="text-sm font-bold text-slate-800 mb-2 flex items-center gap-2">
                    <i class="fas fa-tasks text-[#1C7C54]"></i> Uraian Kegiatan
                </h4>
                <div class="p-4 rounded-xl border border-slate-200 bg-white text-sm text-slate-600 leading-relaxed shadow-sm">
                    <div id="detail-nama" class="font-semibold text-slate-800 mb-1">-</div>
                    <div id="detail-uraian">-</div>
                </div>
            </div>

            {{-- Grid Metrik --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-3 rounded-lg border border-slate-100 text-center bg-slate-50/50">
                    <div class="text-xs text-slate-400 mb-1">Output</div>
                    <div id="detail-output" class="font-semibold text-slate-700">-</div>
                </div>

                <div class="p-3 rounded-lg border border-slate-100 text-center bg-slate-50/50">
                    <div class="text-xs text-slate-400 mb-1">Volume</div>
                    <div id="detail-volume" class="font-semibold text-slate-700">-</div>
                </div>

                <div class="p-3 rounded-lg border border-slate-100 text-center bg-slate-50/50">
                    <div class="text-xs text-slate-400 mb-1">Jam Mulai</div>
                    <div id="detail-jam-mulai" class="font-semibold text-slate-700">-</div>
                </div>

                <div class="p-3 rounded-lg border border-slate-100 text-center bg-slate-50/50">
                    <div class="text-xs text-slate-400 mb-1">Jam Selesai</div>
                    <div id="detail-jam-selesai" class="font-semibold text-slate-700">-</div>
                </div>
            </div>

            {{-- Lokasi + Bukti --}}
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="text-xs font-semibold text-slate-500 mb-1">Titik Lokasi Aktual</div>
                    <div id="detail-lokasi"
                        class="bg-slate-50 px-3 py-2 border border-slate-100 rounded-lg text-sm text-slate-700 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-rose-500"></i> <span>-</span>
                    </div>
                </div>

                <div class="flex-1">
                    <div class="text-xs font-semibold text-slate-500 mb-1">Bukti Dukung</div>
                    <button type="button" id="detail-bukti-btn"
                        class="js-open-bukti w-full flex items-center justify-center gap-2 bg-blue-50 text-blue-600 px-3 py-2 rounded-lg border border-blue-200 hover:bg-blue-100 text-sm transition-colors">
                        <i class="fas fa-file-image"></i> Lihat Lampiran
                    </button>
                </div>
            </div>

            {{-- Catatan Validator --}}
            <div id="detail-catatan-wrapper" class="hidden bg-yellow-50 p-4 border border-yellow-100 rounded-xl">
                <div class="text-xs font-bold text-yellow-700 mb-1 uppercase"><i class="fas fa-comment-dots mr-1"></i> Catatan Validasi</div>
                <div id="detail-catatan" class="text-sm text-yellow-800 italic"></div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div id="validation-actions" class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">

            <button
                class="js-open-reject px-5 py-2.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-sm font-medium transition-colors">
                <i class="fas fa-times-circle mr-1"></i> Tolak
            </button>

            <button
                class="js-open-approve px-5 py-2.5 rounded-xl bg-[#1C7C54] text-white hover:bg-[#156343] text-sm font-medium shadow transition-colors">
                <i class="fas fa-check-circle mr-1"></i> Setujui
            </button>
        </div>

        {{-- Sudah tervalidasi --}}
        <div id="validation-info" class="hidden px-6 py-4 text-center bg-slate-50 border-t border-slate-200">
            <span class="text-sm text-slate-500 font-medium flex items-center justify-center gap-2">
                <i class="fas fa-lock text-[#1C7C54]"></i> Laporan ini telah selesai divalidasi.
            </span>
        </div>
    </div>
</div>

{{-- ================= MODAL LIST BUKTI DOKUMEN ================= --}}
<div id="modal-bukti-list"
    class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
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

{{-- ================= PREVIEW MODAL ================= --}}
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

{{-- ================= MODAL APPROVE ================= --}}
<div id="modal-approve" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 transform scale-100 transition-all">

        <div class="text-center mb-6">
            <div
                class="w-16 h-16 bg-emerald-100 text-[#1C7C54] rounded-full flex items-center justify-center mx-auto text-2xl mb-4 border-4 border-white shadow-sm">
                <i class="fas fa-check"></i>
            </div>
            <h3 class="text-lg font-bold">Setujui Laporan?</h3>
            <p class="text-sm text-slate-500 mt-1">Laporan akan ditandai sebagai
                disetujui.
            </p>
        </div>

        <textarea id="approve-note" rows="2"
            class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-[#1C7C54] focus:border-[#1C7C54] mb-4 transition-colors"
            placeholder="Catatan apresiasi opsional..."></textarea>

        <div class="flex gap-3">
            <button type="button"
                class="js-close-approve flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors font-medium">
                Batal
            </button>

            <button id="btn-submit-approve"
                class="flex-1 py-2.5 rounded-xl bg-[#1C7C54] text-white hover:bg-[#156343] shadow-lg shadow-emerald-200 transition-all font-medium">
                Ya, Setujui
            </button>
        </div>
    </div>
</div>

{{-- ================= MODAL REJECT ================= --}}
<div id="modal-reject" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 transform scale-100 transition-all">
        <div class="text-center mb-6">
            <div
                class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-600 text-2xl border-4 border-white shadow-sm">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Tolak Laporan?</h3>
            <p class="text-sm text-slate-500 mt-1">Berikan alasan penolakan agar pegawai dapat memperbaikinya.</p>
        </div>

        <textarea id="reject-note" rows="3"
            class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-rose-500 focus:border-rose-500 transition-colors"
            placeholder="Alasan penolakan (Wajib)..."></textarea>

        <p id="reject-error" class="hidden text-xs text-rose-600 mb-3 mt-1 font-medium"><i class="fas fa-info-circle"></i> Alasan wajib diisi.</p>

        <div class="flex gap-3 mt-4">
            <button type="button"
                class="js-close-reject flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-colors">
                Batal
            </button>

            <button id="btn-submit-reject"
                class="flex-1 py-2.5 rounded-xl bg-rose-600 text-white font-medium hover:bg-rose-700 shadow-lg shadow-rose-200 transition-all">
                Tolak Laporan
            </button>
        </div>

    </div>
</div>
@endsection