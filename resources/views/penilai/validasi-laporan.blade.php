@php($title = 'Validasi Laporan')
@extends('layouts.app', ['title' => $title, 'role' => 'penilai', 'active' => 'validasi'])

@section('content')

<section class="rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5 flex flex-col h-full">
    <h2 class="text-[18px] font-normal mb-4">Validasi Laporan</h2>

    {{-- Tabel daftar laporan --}}
    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-100 text-[13px] text-slate-600">
                <tr>
                    <th class="px-4 py-2 text-left font-medium">Tanggal Dikirim</th>
                    <th class="px-4 py-2 text-left font-medium">Nama Kegiatan</th>
                    <th class="px-4 py-2 text-left font-medium">Waktu</th>
                    <th class="px-4 py-2 text-left font-medium">Pegawai</th>
                    <th class="px-4 py-2 text-left font-medium">Lokasi</th>
                    {{-- Kolom Status --}}
                    <th class="px-4 py-2 text-center font-medium w-[100px]">Status</th> 
                    <th class="px-4 py-2 text-left font-medium w-[120px]">Aksi</th>
                </tr>
            </thead>
            {{-- Hook untuk JavaScript --}}
            <tbody id="lkh-validation-list" class="text-[13px] text-slate-700">
                <tr><td colspan="7" class="p-4 text-center text-slate-500">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</section>

{{-- ================= MODAL DETAIL LAPORAN ================= --}}
<div id="modal-detail" class="fixed inset-0 z-40 hidden items-center justify-center bg-black/40" data-lkh-id="">
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
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                {{-- Tanggal --}}
                <div>
                    <div class="text-[12px] text-slate-500 mb-[2px]">Tanggal Laporan:</div>
                    <div id="detail-tanggal" class="font-medium">-</div>
                </div>
                 {{-- Nama Pegawai --}}
                <div class="md:col-span-2">
                    <div class="text-[12px] text-slate-500 mb-[2px]">Pegawai:</div>
                    <div id="detail-pegawai" class="font-medium text-slate-900">-</div>
                </div>
                 {{-- Status --}}
                <div id="detail-status-wrapper">
                    <div class="text-[12px] text-slate-500 mb-[2px]">Status:</div>
                    <div id="detail-status" class="font-medium">-</div>
                </div>
            </div>

            <div>
                <div class="text-[12px] text-slate-500 mb-[2px]">Kegiatan:</div>
                <div id="detail-nama" class="font-medium text-base text-[#155FA6]">-</div>
            </div>

            <div>
                <div class="text-[12px] text-slate-500 mb-[2px]">Uraian Aktivitas:</div>
                <div id="detail-uraian" class="leading-snug bg-slate-50 p-3 rounded-lg border border-slate-200">-</div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 pt-2 border-t border-slate-100">
                
                {{-- Output --}}
                <div>
                    <div class="text-[12px] text-slate-500 mb-[2px]">Output:</div>
                    <div id="detail-output" class="font-medium">-</div>
                </div>
                 {{-- Volume --}}
                <div>
                    <div class="text-[12px] text-slate-500 mb-[2px]">Volume:</div>
                    <div id="detail-volume" class="font-medium">-</div>
                </div>
                 {{-- Satuan --}}
                <div>
                    <div class="text-[12px] text-slate-500 mb-[2px]">Satuan:</div>
                    <div id="detail-satuan" class="font-medium">-</div>
                </div>
                 {{-- Kategori --}}
                <div>
                    <div class="text-[12px] text-slate-500 mb-[2px]">Kategori:</div>
                    <div id="detail-kategori" class="font-medium">-</div>
                </div>
                 {{-- Lokasi --}}
                <div>
                    <div class="text-[12px] text-slate-500 mb-[2px]">Lokasi:</div>
                    <div id="detail-lokasi" class="font-medium">-</div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                {{-- Jam --}}
                <div>
                    <div class="text-[12px] text-slate-500 mb-[2px]">Waktu Kerja:</div>
                    <div id="detail-jam" class="font-medium text-slate-800">-</div>
                </div>
                
                {{-- Bukti --}}
                <div>
                    <div class="text-[12px] text-slate-500 mb-[2px]">Bukti:</div>
                    <button id="detail-bukti-btn" disabled
                        class="inline-flex items-center justify-center rounded-[6px] bg-[#155FA6] text-white text-[11px] px-3 py-[4px] leading-none hover:brightness-95 disabled:opacity-50">
                        Lihat Bukti
                    </button>
                </div>
                
                 {{-- Catatan Penilai Sebelumnya (Hanya Muncul jika ada) --}}
                <div id="detail-catatan-wrapper" class="md:col-span-3 hidden">
                    <div class="text-[12px] text-slate-500 mb-[2px]">Catatan Verifikasi Sebelumnya:</div>
                    <div id="detail-catatan" class="leading-snug italic text-rose-600 bg-rose-50 p-3 rounded-lg border border-rose-200"></div>
                </div>
            </div>
            
        </div>

        {{-- Footer (ikon centang & silang) - HANYA UNTUK WAITING_REVIEW --}}
        <div id="validation-actions" class="flex items-center justify-end gap-4 px-6 py-4 border-t border-slate-200">
             <span class="text-sm text-slate-600 font-medium mr-2">Tindak Lanjut:</span>
            <button type="button"
                class="js-open-approve h-10 w-10 flex items-center justify-center rounded-full bg-emerald-500 text-white hover:bg-emerald-600 transition-colors shadow-lg">
                ✓
            </button>
            <button type="button"
                class="js-open-reject h-10 w-10 flex items-center justify-center rounded-full bg-rose-500 text-white hover:bg-rose-600 transition-colors shadow-lg">
                ✕
            </button>
        </div>
        <div id="validation-info" class="hidden px-6 py-4 border-t border-slate-200">
            <span class="text-sm text-slate-500 italic">Laporan ini sudah divalidasi.</span>
        </div>
    </div>
</div>

{{-- ================ MODAL TERIMA LAPORAN ================= --}}
<div id="modal-approve" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-[95vw] max-w-md">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-base font-semibold text-slate-800">Terima Laporan</h3>
        </div>

        <div class="px-6 py-4 text-sm">
            <p class="text-[12px] text-slate-500 mb-2">Tambahkan Catatan (Opsional):</p>
            <textarea id="approve-note" rows="4"
                class="w-full rounded-[10px] border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                placeholder="Contoh: Kerja bagus! Tingkatkan lagi."></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 px-6 py-3 border-t border-slate-200">
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

{{-- ================ MODAL TOLAK LAPORAN ================= --}}
<div id="modal-reject" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-[95vw] max-w-md">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-base font-semibold text-slate-800">Tolak Laporan</h3>
        </div>

        <div class="px-6 py-4 text-sm">
            <p class="text-[12px] text-slate-500 mb-2">Tambahkan Catatan (Wajib):</p>
            <textarea id="reject-note" rows="4"
                class="w-full rounded-[10px] border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:border-rose-400"
                placeholder="Contoh: Laporan kurang sesuai, perbaiki lagi!"></textarea>

            <p id="reject-error" class="hidden mt-1 text-[11px] text-rose-600">
                Catatan wajib diisi sebelum menolak laporan.
            </p>
        </div>

        <div class="flex items-center justify-end gap-2 px-6 py-3 border-t border-slate-200">
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

@push('scripts')
@vite('resources/js/pages/penilai/validasi-laporan.js') 
@endpush