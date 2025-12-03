@php($title = 'Riwayat Laporan')
{{-- Asumsi $role tersedia dari @extends dan bernilai 'penilai' atau 'staf' --}}
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'riwayat'])


@section('content')
<section x-data="riwayatData('{{ $role ?? 'pegawai' }}')" x-init="initPage()">

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

        {{-- FILTER TANGGAL --}}
        <form class="mt-1 mb-4" @submit.prevent="filterData()">
            <label class="block text-xs font-normal text-slate-600 mb-2">
                Filter Berdasarkan Tanggal
            </label>

            <div class="grid md:grid-cols-[1fr_1fr_auto] gap-3">
                {{-- Dari --}}
                <div>
                    <label class="sr-only">Dari Tanggal</label>
                    <div class="relative">
                        <input x-model="filter.from" id="tgl_dari" type="date" name="from_date"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />

                        <button type="button" id="tgl_dari_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-80">
                        </button>
                    </div>
                </div>

                {{-- Sampai --}}
                <div>
                    <label class="sr-only">Sampai Tanggal</label>
                    <div class="relative">
                        <input x-model="filter.to" id="tgl_sampai" type="date" name="to_date"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />

                        <button type="button" id="tgl_sampai_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-80">
                        </button>
                    </div>
                </div>

                {{-- Button Terapkan --}}
                <div class="flex items-end">
                    <button type="submit"
                        class="rounded-[10px] bg-[#0E7A4A] px-5 py-2.5 text-sm text-white hover:brightness-95 w-full md:w-auto"
                        :disabled="loading">
                        <span x-show="!loading">Terapkan</span>
                        <span x-show="loading">Memuat...</span>
                    </button>
                </div>
            </div>
        </form>

        {{-- TABLE --}}
        <div class="overflow-x-auto mt-2 flex-1">
            <table class="w-full min-w-[900px] text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500 uppercase bg-slate-50">
                        <th class="px-3 py-2 font-medium">Tanggal Laporan</th>
                        <th class="px-3 py-2 font-medium">Nama Kegiatan</th>

                        <template x-if="filter.mode === 'subordinates'">
                            <th class="px-3 py-2 font-medium">Pegawai</th>
                        </template>

                        <th class="px-3 py-2 font-medium">Tanggal Verifikasi</th>
                        <th class="px-3 py-2 font-medium">Pejabat Penilai</th>
                        <th class="px-3 py-2 font-medium">Status</th>
                        <th class="px-3 py-2 font-medium">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-slate-700">

                    <!-- KOSONG -->
                    <template x-if="items.length === 0 && !loading">
                        <tr>
                            <td :colspan="role === 'penilai' && filter.mode === 'subordinates' ? 7 : 6"
                                class="px-3 py-4 text-center text-slate-500">
                                Tidak ada data laporan ditemukan.
                            </td>
                        </tr>
                    </template>

                    <!-- LOADING -->
                    <template x-if="loading">
                        <tr>
                            <td :colspan="role === 'penilai' && filter.mode === 'subordinates' ? 7 : 6"
                                class="px-3 py-4 text-center text-slate-500">
                                Memuat data...
                            </td>
                        </tr>
                    </template>

                    <!-- DATA -->
                    <template x-for="item in items" :key="item.id">
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="px-3 py-3 whitespace-nowrap" x-text="formatDate(item.tanggal_laporan)"></td>
                            <td class="px-3 py-3" x-text="item.deskripsi_aktivitas"></td>

                            <template x-if="filter.mode === 'subordinates'">
                                <td class="px-3 py-3 whitespace-nowrap" x-text="item.user.name || '-'"></td>
                            </template>

                            <td class="px-3 py-3 whitespace-nowrap" x-text="formatDate(item.validated_at)"></td>

                            <td class="px-3 py-3"
                                x-text="item.atasan ? item.atasan.name : (item.validator ? item.validator.name : '-')">
                            </td>

                            <td class="px-3 py-3">
                                <span :class="statusBadgeClass(item.status)" x-text="statusText(item.status)"></span>
                            </td>

                            <td class="px-3 py-3">
                                <button @click="openModal(item)"
                                    class="rounded-[6px] bg-[#155FA6] text-white text-[11px] px-3 py-[4px] leading-none shadow-sm hover:brightness-95">
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

    {{-- MODAL DETAIL --}}
    <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm p-4"
        style="display: none;">

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            @click.outside="open = false"
            class="relative w-full max-w-2xl rounded-2xl bg-white ring-1 ring-slate-200 p-6 shadow-xl">

            <button @click="open = false"
                class="absolute top-4 right-5 h-8 w-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor">
                    <path stroke-width="2.5" stroke-linecap="round" d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>

            <h3 class="text-lg font-semibold text-slate-800">Detail Laporan</h3>

            <template x-if="modalData">
                <div class="mt-4 space-y-4 text-sm">
                    <div class="space-y-2">
                        <div>
                            <label class="text-xs text-slate-500">Tanggal Laporan:</label>
                            <p class="text-slate-800" x-text="formatDate(modalData.tanggal_laporan)"></p>
                        </div>
                        <div x-show="role === 'penilai' && filter.mode === 'subordinates'">
                            <label class="text-xs text-slate-500">Pegawai:</label>
                            <p class="text-slate-800 font-medium" x-text="modalData.user.name"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Nama Kegiatan:</label>
                            <p class="text-slate-800 font-semibold text-base" x-text="modalData.jenis_kegiatan"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Uraian Kegiatan:</label>
                            <p class="text-slate-800" x-text="modalData.deskripsi_aktivitas"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-3 pt-2 border-t border-slate-200">
                        <div>
                            <label class="text-xs text-slate-500">Output:</label>
                            <p class="text-slate-800" x-text="modalData.output_hasil_kerja"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Jam Mulai:</label>
                            <p class="text-slate-800" x-text="modalData.waktu_mulai.substring(0, 5)"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Volume:</label>
                            <p class="text-slate-800" x-text="modalData.volume"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Jam Selesai:</label>
                            <p class="text-slate-800" x-text="modalData.waktu_selesai.substring(0, 5)"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Satuan:</label>
                            <p class="text-slate-800" x-text="modalData.satuan"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Bukti:</label>
                            <button @click="viewBukti(modalData.bukti)" :disabled="modalData.bukti.length === 0"
                                class="rounded-[6px] bg-[#155FA6] text-white text-[11px] px-3 py-[4px] leading-none shadow-sm hover:brightness-95 disabled:opacity-50">
                                Lihat Bukti (<span x-text="modalData.bukti.length"></span>)
                            </button>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Kategori:</label>
                            <p class="text-slate-800" x-text="modalData.skp_id ? 'SKP' : 'Non-SKP'"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Lokasi:</label>
                            <p class="text-slate-800" x-text="getLokasi(modalData)"></p>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-4 space-y-3">
                        <div>
                            <label class="text-xs text-slate-500">Status:</label>
                            <div x-html="statusBadgeHtml(modalData.status)"></div>
                        </div>

                        <div x-show="modalData.komentar_validasi">
                            <label class="text-xs text-slate-500">Catatan Penilai:</label>
                            <p class="text-slate-800 italic bg-slate-50 p-2 rounded"
                                x-text="modalData.komentar_validasi"></p>
                        </div>

                        <div>
                            <label class="text-xs text-slate-500">Pejabat Penilai Kerja:</label>
                            <p class="text-slate-800 font-medium"
                                x-text="modalData.validator ? modalData.validator.name : modalData.atasan.name"></p>
                        </div>
                    </div>

                    <div x-show="modalData.status === 'rejected' && role === 'pegawai'" class="flex justify-end pt-2">
                        <button
                            class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm font-normal text-white hover:brightness-95">
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