@php
    $title = 'Validasi Laporan';
@endphp

@extends('layouts.app', ['title' => $title, 'role' => 'kadis', 'active' => 'validasi'])

@section('content')

<section class="flex flex-col h-full gap-5">

    {{-- ========================= FILTER CARD ========================= --}}
    <div class="bg-white rounded-2xl p-5 ring-1 ring-slate-200 shadow-sm">
        <h2 class="text-[18px] font-semibold text-slate-800 mb-4">Filter Validasi</h2>

        <form id="filter-form" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            
            {{-- Search --}}
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Cari Pegawai / Aktivitas</label>
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

                {{-- STATUS (DEFAULT: ALL) --}}
                <div class="md:col-span-2">
                    <select id="filter-status" class="appearance-none pl-3 pr-8 py-2 text-sm font-medium border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all bg-white text-slate-600 shadow-sm cursor-pointer">
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

            {{-- Button Apply --}}
            <div class="md:col-span-2">
                <button type="submit"
                    class="w-full bg-[#1C7C54] hover:bg-[#156343] text-white py-2 rounded-lg text-sm font-medium transition shadow-sm flex items-center justify-center gap-2">
                    <i class="fas fa-filter"></i> Terapkan
                </button>
            </div>
        </form>
    </div>

    {{-- ========================= TABEL ========================= --}}
    <div class="flex-1 bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
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
    </div>

</section>

{{-- ====================================================================== --}}
{{-- ========================= MODAL DETAIL =============================== --}}
{{-- ====================================================================== --}}
<div id="modal-detail" class="fixed inset-0 z-40 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-[95vw] max-w-3xl max-h-[90vh] overflow-y-auto shadow-2xl">

        {{-- Header --}}
        <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200 sticky top-0 bg-white">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Detail Laporan Harian</h3>
                <p class="text-xs text-slate-500">Validasi kinerja bawahan Anda</p>
            </div>

            <button class="js-close-detail h-8 w-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-6">

            {{-- Informasi Pegawai --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <span class="text-xs font-semibold text-slate-400 uppercase">Informasi Pegawai</span>
                    <div id="detail-pegawai" class="font-bold text-slate-800 mt-1">-</div>
                    <div id="detail-tanggal" class="text-sm text-slate-600">-</div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase">Status Saat Ini</span>
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
                <button type="button"
                    class="js-close-detail h-8 w-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            {{-- Lokasi + Bukti --}}
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="text-xs font-semibold text-slate-500 mb-1">Lokasi</div>
                    <div id="detail-lokasi" class="bg-slate-50 px-3 py-2 border border-slate-100 rounded-lg truncate text-sm">-</div>
                </div>

                <div class="flex-1">
                    <div class="text-xs font-semibold text-slate-500 mb-1">Bukti Dukung</div>
                    <button id="detail-bukti-btn"
                        class="w-full flex items-center justify-center gap-2 bg-blue-50 text-blue-600 px-3 py-2 rounded-lg border border-blue-200 hover:bg-blue-100 text-sm">
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
        <div id="validation-actions"
            class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">

            <button class="js-open-reject px-5 py-2.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-sm font-medium">
                <i class="fas fa-times-circle"></i> Tolak
            </button>

            <button class="js-open-approve px-5 py-2.5 rounded-xl bg-[#1C7C54] text-white hover:bg-[#156343] text-sm font-medium shadow">
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

{{-- ====================================================================== --}}
{{-- ========================= MODAL APPROVE =============================== --}}
{{-- ====================================================================== --}}
<div id="modal-approve" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">

        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl mb-4">
                <i class="fas fa-check"></i>
            </div>
            <h3 class="text-lg font-bold">Setujui Laporan?</h3>
            <p class="text-sm text-slate-500 mt-1">Laporan akan ditandai sebagai disetujui.</p>
        </div>

        <textarea id="approve-note" rows="2"
            class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-emerald-500 focus:border-emerald-500 mb-4"
            placeholder="Catatan opsional..."></textarea>

        <div class="flex gap-3">
            <button class="js-close-approve flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">
                Batal
            </button>

            <button id="btn-submit-approve"
                class="flex-1 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-200">
                Ya, Setujui
            </button>
        </div>
    </div>
</div>

{{-- ====================================================================== --}}
{{-- ========================= MODAL REJECT ================================ --}}
{{-- ====================================================================== --}}
<div id="modal-reject" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">

        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto text-2xl mb-4">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="text-lg font-bold">Tolak Laporan?</h3>
            <p class="text-sm text-slate-500 mt-1">Berikan alasan penolakan.</p>
        </div>

        <textarea id="reject-note" rows="3"
            class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:ring-rose-500 focus:border-rose-500"
            placeholder="Alasan penolakan (Wajib)..."></textarea>

        <p id="reject-error" class="hidden text-xs text-rose-600 mb-3">* Alasan wajib diisi.</p>

        <div class="flex gap-3 mt-4">
            <button class="js-close-reject flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">
                Batal
            </button>

            <button id="btn-submit-reject"
                class="flex-1 py-2.5 rounded-xl bg-rose-600 text-white hover:bg-rose-700 shadow-lg shadow-rose-200">
                Tolak Laporan
            </button>
        </div>

    </div>
</div>

@endsection
