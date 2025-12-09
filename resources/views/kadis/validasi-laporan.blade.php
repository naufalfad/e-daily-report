@php
    $title = 'Validasi Laporan';
@endphp

@extends('layouts.app', ['title' => $title, 'role' => 'kadis', 'active' => 'validasi'])

@section('content')

    <section class="flex flex-col h-full gap-5">

        {{-- CARD FILTER --}}
        <div class="bg-white rounded-2xl p-5 ring-1 ring-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-[18px] font-semibold text-slate-800">Filter Validasi</h2>
            </div>

            <form id="filter-form" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">

                {{-- SEARCH --}}
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Cari Pegawai /
                        Aktivitas</label>
                    <div class="relative">
                        <input type="text" id="filter-search" placeholder="Ketikan nama atau kegiatan..."
                            class="w-full pl-9 pr-4 py-2 rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54] focus:border-[#1C7C54] transition shadow-sm">
                        <i class="fas fa-search absolute left-3 top-3 text-slate-400 text-xs"></i>
                    </div>
                </div>

                {{-- BULAN --}}
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

                {{-- TAHUN --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Tahun</label>
                    <select id="filter-year"
                        class="w-full rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54] focus:border-[#1C7C54] cursor-pointer">
                        @for ($y = date('Y'); $y >= 2023; $y--)
                            <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>
                                {{ $y }}</option>
                        @endfor
                    </select>
                </div>

                {{-- STATUS (DEFAULT: ALL) --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Status</label>
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

        {{-- CARD TABEL --}}
        <div class="flex-1 bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm flex flex-col overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead
                        class="bg-slate-50 text-[12px] uppercase text-slate-500 font-semibold border-b border-slate-200">
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
                        {{-- Diisi oleh JavaScript --}}
                    </tbody>
                </table>
            </div>
        </div>

    </section>

    {{-- ================= MODAL DETAIL (PASTIKAN ID LENGKAP) ================= --}}
    <div id="modal-detail"
        class="fixed inset-0 z-40 hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
        <div
            class="bg-white rounded-2xl shadow-2xl w-[95vw] max-w-3xl max-h-[90vh] overflow-y-auto transform transition-all scale-100">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Detail Laporan Harian</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Validasi kinerja bawahan Anda</p>
                </div>
                <button type="button"
                    class="js-close-detail h-8 w-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 space-y-6">
                {{-- Grid Info Utama --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Informasi Pegawai</span>
                        <div id="detail-pegawai" class="font-bold text-slate-800 text-base mt-1">-</div>
                        <div id="detail-tanggal" class="text-sm text-slate-600 mt-0.5">-</div>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status Saat Ini</span>
                            <div id="detail-status" class="mt-1">-</div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kategori</span>
                            {{-- ID PENTING UNTUK JS --}}
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

                {{-- Lokasi & Bukti --}}
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="text-xs font-semibold text-slate-500 mb-1">Lokasi</div>
                        <div id="detail-lokasi"
                            class="text-sm text-slate-700 bg-slate-50 px-3 py-2 rounded-lg border border-slate-100 truncate">
                            -
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-semibold text-slate-500 mb-1">Bukti Dukung</div>
                        <button id="detail-bukti-btn"
                            class="w-full flex items-center justify-center gap-2 bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-2 rounded-lg text-sm font-medium transition border border-blue-200">
                            <i class="fas fa-file-image"></i> Lihat Bukti
                        </button>
                    </div>
                </div>

                {{-- Catatan Validator --}}
                <div id="detail-catatan-wrapper" class="hidden bg-yellow-50 p-4 rounded-xl border border-yellow-100">
                    <div class="text-xs font-bold text-yellow-700 mb-1 uppercase">Catatan Validasi</div>
                    <div id="detail-catatan" class="text-sm text-yellow-800 italic"></div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div id="validation-actions"
                class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-2xl">
                <button type="button"
                    class="js-open-reject px-5 py-2.5 rounded-xl bg-white border border-rose-200 text-rose-600 font-medium text-sm hover:bg-rose-50 transition shadow-sm flex items-center gap-2">
                    <i class="fas fa-times-circle"></i> Tolak
                </button>
                <button type="button"
                    class="js-open-approve px-5 py-2.5 rounded-xl bg-[#1C7C54] text-white font-medium text-sm hover:bg-[#156343] transition shadow-md flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Setujui Laporan
                </button>
            </div>

            <div id="validation-info"
                class="hidden px-6 py-4 bg-slate-50 border-t border-slate-100 text-center rounded-b-2xl">
                <span class="text-sm text-slate-500 flex items-center justify-center gap-2">
                    <i class="fas fa-lock"></i> Laporan ini telah divalidasi.
                </span>
            </div>
        </div>
    </div>

    {{-- ================= MODAL APPROVE ================= --}}
    <div id="modal-approve" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 transform scale-100 transition-all">
            <div class="text-center mb-6">
                <div
                    class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-600 text-2xl">
                    <i class="fas fa-check"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Setujui Laporan?</h3>
                <p class="text-sm text-slate-500 mt-1">Laporan akan ditandai sebagai diterima.</p>
            </div>

            <textarea id="approve-note" rows="2"
                class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-emerald-500 focus:border-emerald-500 mb-4"
                placeholder="Catatan opsional..."></textarea>

            <div class="flex gap-3">
                <button type="button"
                    class="js-close-approve flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition">Batal</button>
                <button type="button" id="btn-submit-approve"
                    class="flex-1 py-2.5 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">Ya,
                    Setujui</button>
            </div>
        </div>
    </div>

    {{-- ================= MODAL REJECT ================= --}}
    <div id="modal-reject" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 transform scale-100 transition-all">
            <div class="text-center mb-6">
                <div
                    class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-600 text-2xl">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Tolak Laporan?</h3>
                <p class="text-sm text-slate-500 mt-1">Berikan alasan penolakan agar staf dapat memperbaikinya.</p>
            </div>

            <textarea id="reject-note" rows="3"
                class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-rose-500 focus:border-rose-500 mb-2"
                placeholder="Alasan penolakan (Wajib)..."></textarea>
            <p id="reject-error" class="hidden text-xs text-rose-600 mb-4 text-left">* Alasan wajib diisi.</p>

            <div class="flex gap-3 mt-4">
                <button type="button"
                    class="js-close-reject flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition">Batal</button>
                <button type="button" id="btn-submit-reject"
                    class="flex-1 py-2.5 rounded-xl bg-rose-600 text-white font-medium hover:bg-rose-700 transition shadow-lg shadow-rose-200">Tolak
                    Laporan</button>
            </div>
        </div>
    </div>

@endsection