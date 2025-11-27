@php
$title = 'Validasi Laporan';
@endphp

@extends('layouts.app', ['title' => $title, 'role' => 'kadis', 'active' => 'validasi'])

@section('content')

<section class="rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5 flex flex-col h-full">
    <h2 class="text-[18px] font-normal mb-4">Validasi Laporan</h2>

    {{-- Tabel daftar laporan --}}
    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-100 text-[13px] text-slate-600">
                <tr>
                    <th class="px-4 py-2 text-left font-medium">Tanggal Laporan Dikirim</th>
                    <th class="px-4 py-2 text-left font-medium">Nama Kegiatan</th>
                    <th class="px-4 py-2 text-left font-medium">Waktu</th>
                    <th class="px-4 py-2 text-left font-medium">Nama Pegawai</th>
                    <th class="px-4 py-2 text-left font-medium">Lokasi</th>
                    <th class="px-4 py-2 text-left font-medium">Status</th>
                    <th class="px-4 py-2 text-left font-medium w-[120px]">Aksi</th>
                </tr>
            </thead>
            <tbody id="lkh-validation-list" class="text-[13px] text-slate-700">
                {{-- Akan diisi JS --}}
            </tbody>
        </table>
    </div>
</section>


{{-- ========================================================= --}}
{{-- ================= MODAL DETAIL LAPORAN ================= --}}
{{-- ========================================================= --}}
<div id="modal-detail" class="fixed inset-0 z-40 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-3xl shadow-xl w-[95vw] max-w-4xl max-h-[90vh] overflow-y-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
            <h3 class="text-base md:text-lg font-semibold text-slate-800">Detail Laporan</h3>
            <button type="button"
                class="js-close-detail h-8 w-8 flex items-center justify-center rounded-full hover:bg-slate-100">
                <span class="text-slate-400 text-lg">&times;</span>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 text-sm text-slate-800 space-y-4">

            <div>
                <div class="text-xs text-slate-500">Tanggal</div>
                <div id="detail-tanggal" class="font-medium">-</div>
            </div>

            <div>
                <div class="text-xs text-slate-500">Nama Pegawai</div>
                <div id="detail-pegawai" class="font-medium">-</div>
            </div>

            <div>
                <div class="text-xs text-slate-500">Status</div>
                <div id="detail-status">-</div>
            </div>

            <div>
                <div class="text-xs text-slate-500">Nama Kegiatan</div>
                <div id="detail-nama" class="font-medium">-</div>
            </div>

            <div>
                <div class="text-xs text-slate-500">Uraian Aktivitas</div>
                <div id="detail-uraian" class="leading-snug">-</div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-slate-500">Output</div>
                    <div id="detail-output" class="font-medium">-</div>
                </div>

                <div>
                    <div class="text-xs text-slate-500">Volume</div>
                    <div id="detail-volume" class="font-medium">-</div>
                </div>

                <div>
                    <div class="text-xs text-slate-500">Satuan</div>
                    <div id="detail-satuan" class="font-medium">-</div>
                </div>

                <div>
                    <div class="text-xs text-slate-500">Kategori</div>
                    <div id="detail-kategori" class="font-medium">-</div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-slate-500">Jam Mulai</div>
                    <div id="detail-jam-mulai" class="font-medium">-</div>
                </div>

                <div>
                    <div class="text-xs text-slate-500">Jam Selesai</div>
                    <div id="detail-jam-selesai" class="font-medium">-</div>
                </div>

                <div>
                    <div class="text-xs text-slate-500">Bukti</div>
                    <button id="detail-bukti-btn"
                        class="inline-flex items-center justify-center rounded-lg bg-[#155FA6] text-white px-3 py-1.5 text-xs hover:brightness-95 disabled:opacity-40">
                        Tidak Ada Bukti
                    </button>
                </div>

                <div>
                    <div class="text-xs text-slate-500">Lokasi</div>
                    <div id="detail-lokasi" class="font-medium">-</div>
                </div>
            </div>

            <div id="detail-catatan-wrapper" class="hidden">
                <div class="text-xs text-slate-500">Catatan Validasi</div>
                <div id="detail-catatan" class="italic text-slate-700"></div>
            </div>

        </div>

        {{-- Footer --}}
        <div id="validation-actions" class="flex justify-end gap-4 px-6 py-4 border-t border-slate-200">
            <button type="button"
                class="js-open-approve h-8 w-8 flex items-center justify-center rounded-full border border-emerald-500 text-emerald-600 hover:bg-emerald-50">
                ✓
            </button>
            <button type="button"
                class="js-open-reject h-8 w-8 flex items-center justify-center rounded-full border border-rose-500 text-rose-600 hover:bg-rose-50">
                ✕
            </button>
        </div>

        <div id="validation-info" class="hidden px-6 py-4 border-t border-slate-200 text-sm text-slate-600">
            Laporan ini sudah divalidasi.
        </div>

    </div>
</div>


{{-- ========================================================= --}}
{{-- ================== MODAL APPROVE ========================= --}}
{{-- ========================================================= --}}
<div id="modal-approve" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-[95vw] max-w-md">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-base font-semibold text-slate-800">Terima Laporan</h3>
        </div>

        <div class="px-6 py-4 text-sm">
            <p class="text-xs text-slate-500 mb-2">Tambahkan Catatan (Opsional):</p>
            <textarea id="approve-note" rows="4"
                class="w-full rounded-[10px] border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300"
                placeholder="Contoh: Sudah sesuai, lanjutkan."></textarea>
        </div>

        <div class="flex justify-end gap-2 px-6 py-3 border-t border-slate-200">
            <button type="button"
                class="js-close-approve rounded-[8px] px-3 py-1.5 text-[12px] bg-slate-200 text-slate-700 hover:brightness-95">
                Batal
            </button>
            <button type="button" id="btn-submit-approve"
                class="rounded-[8px] px-3 py-1.5 text-[12px] bg-[#0E7A4A] text-white hover:brightness-95">
                Terima Laporan
            </button>
        </div>
    </div>
</div>


{{-- ========================================================= --}}
{{-- ================== MODAL REJECT ========================= --}}
{{-- ========================================================= --}}
<div id="modal-reject" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-[95vw] max-w-md">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-base font-semibold text-slate-800">Tolak Laporan</h3>
        </div>

        <div class="px-6 py-4 text-sm">
            <p class="text-xs text-slate-500 mb-2">Tambahkan Catatan (Wajib):</p>
            <textarea id="reject-note" rows="4"
                class="w-full rounded-[10px] border border-rose-400 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300"
                placeholder="Contoh: Tolong perbaiki uraian, masih belum jelas."></textarea>

            <p id="reject-error" class="hidden mt-1 text-xs text-rose-600">
                Catatan wajib diisi sebelum menolak laporan.
            </p>
        </div>

        <div class="flex justify-end gap-2 px-6 py-3 border-t border-slate-200">
            <button type="button"
                class="js-close-reject rounded-[8px] px-3 py-1.5 text-[12px] bg-slate-200 text-slate-700 hover:brightness-95">
                Batal
            </button>
            <button type="button" id="btn-submit-reject"
                class="rounded-[8px] px-3 py-1.5 text-[12px] bg-[#B6241C] text-white hover:brightness-95">
                Tolak Laporan
            </button>
        </div>
    </div>
</div>

@endsection