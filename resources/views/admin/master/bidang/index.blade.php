@extends('layouts.app', [
    'role' => 'admin',
])

@section('title', 'Master Bidang')

@section('content')
<div class="w-full px-4 sm:px-6 py-6">
    
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 sm:mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Bidang</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data hierarki Bidang dan Sub Bidang pada struktur organisasi.</p>
        </div>
        
        <button onclick="window.openModal('add')" 
            class="group bg-[#1C7C54] hover:bg-[#166443] text-white px-5 py-2.5 rounded-xl shadow-lg shadow-emerald-700/20 transition-all duration-200 flex items-center gap-2 text-sm font-bold transform active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#1C7C54] focus:ring-offset-2">
            <div class="bg-white/20 p-1 rounded-md group-hover:rotate-90 transition-transform">
                <i class="fas fa-plus fa-xs"></i>
            </div>
            Tambah Bidang
        </button>
    </div>

    {{-- CARD TABEL --}}
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200/60 overflow-hidden flex flex-col min-h-[500px]">
        
        {{-- Toolbar: Limit, Search & Info --}}
        <div class="p-5 sm:p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="bg-gradient-to-br from-blue-100 to-blue-50 text-blue-600 w-10 h-10 rounded-xl flex items-center justify-center shadow-sm border border-blue-100/50">
                    <i class="fas fa-sitemap"></i>
                </div>
                <div>
                    <h6 class="font-bold text-slate-700">Daftar Struktur Bidang</h6>
                    <span class="text-xs font-medium text-slate-400" id="total-records">Memuat kalkulasi data...</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                <div class="w-full sm:w-24">
                    <select id="limitSelect" class="block w-full pl-3 pr-8 py-2.5 border-2 border-slate-200 rounded-xl leading-5 bg-white text-sm font-semibold text-slate-700 focus:outline-none focus:ring-0 focus:border-[#1C7C54] cursor-pointer transition-colors">
                        <option value="10">10 Baris</option>
                        <option value="25">25 Baris</option>
                        <option value="50">50 Baris</option>
                    </select>
                </div>
                <div class="relative w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400"></i>
                    </div>
                    <input type="text" id="searchInput" 
                        class="block w-full pl-10 pr-4 py-2.5 border-2 border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 font-medium text-sm text-slate-700 focus:outline-none focus:ring-0 focus:border-[#1C7C54] transition-colors shadow-inner" 
                        placeholder="Cari nama bidang atau unit kerja...">
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="relative flex-1 overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead class="bg-white text-slate-500 uppercase text-[10px] font-extrabold tracking-wider sticky top-0 z-10 shadow-sm border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 w-[5%] text-center">No</th>
                        <th class="px-6 py-4 w-[30%]">Hierarki Bidang</th>
                        <th class="px-6 py-4 w-[25%]">Unit Kerja</th>
                        <th class="px-6 py-4 w-[15%] text-center">Tingkatan</th>
                        <th class="px-6 py-4 w-[10%] text-center">Personil</th>
                        <th class="px-6 py-4 w-[15%] text-center">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="text-sm font-medium text-slate-600 divide-y divide-slate-100 bg-white">
                    {{-- Baris dirender secara dinamis oleh DOM JavaScript --}}
                </tbody>
            </table>

            {{-- Loading State --}}
            <div id="loading-state" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center z-20 transition-opacity duration-300">
                <div class="p-4 bg-white shadow-lg rounded-2xl flex items-center gap-3 border border-slate-100">
                    <svg class="animate-spin h-6 w-6 text-[#1C7C54]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-slate-700 font-bold text-sm tracking-tight">Sinkronisasi Data...</span>
                </div>
            </div>

            {{-- Empty State --}}
            <div id="empty-state" class="hidden absolute inset-0 flex flex-col items-center justify-center text-center z-10 bg-slate-50/50">
                <div class="w-20 h-20 mb-4 bg-white rounded-full shadow-sm flex items-center justify-center border border-slate-100">
                    <i class="fas fa-folder-open text-3xl text-slate-300"></i>
                </div>
                <h3 class="text-slate-700 font-bold text-base mb-1">Tidak ada data ditemukan</h3>
                <p class="text-slate-500 text-sm">Sesuaikan filter atau kata kunci pencarian Anda.</p>
            </div>
        </div>

        {{-- PAGINATION CONTROLS --}}
        <div class="px-6 py-4 bg-white border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 z-20" id="pagination-wrapper">
            <span class="text-xs text-slate-500 font-bold bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200" id="pagination-info">Kalkulasi indeks...</span>
            
            <div class="flex items-center gap-1.5">
                <button id="prev-page" class="px-3.5 py-2 text-slate-500 hover:text-[#1C7C54] disabled:opacity-40 disabled:cursor-not-allowed transition-all rounded-lg border border-slate-200 hover:border-[#1C7C54] hover:bg-emerald-50 active:bg-emerald-100 text-xs font-bold flex items-center gap-1">
                    <i class="fas fa-chevron-left text-[10px]"></i> Prev
                </button>
                <div id="pagination-numbers" class="flex items-center gap-1.5 overflow-x-auto hide-scrollbar"></div>
                <button id="next-page" class="px-3.5 py-2 text-slate-500 hover:text-[#1C7C54] disabled:opacity-40 disabled:cursor-not-allowed transition-all rounded-lg border border-slate-200 hover:border-[#1C7C54] hover:bg-emerald-50 active:bg-emerald-100 text-xs font-bold flex items-center gap-1">
                    Next <i class="fas fa-chevron-right text-[10px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL FORM --}}
<div id="modalBidang" class="fixed inset-0 z-[9999] hidden" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-visible rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" id="modalPanel">
                
                <form id="formBidang" novalidate>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" id="bidang_id" name="id">
                    <input type="hidden" name="_method" id="method" value="POST">

                    <div class="bg-white px-6 py-5 border-b border-slate-100 flex justify-between items-center sticky top-0 z-10 rounded-t-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-[#1C7C54] flex items-center justify-center">
                                <i class="fas fa-layer-group text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-800 tracking-tight" id="modalTitle">Tambah Bidang</h3>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Konfigurasi Entitas SOTK</p>
                            </div>
                        </div>
                        <button type="button" onclick="window.closeModal()" class="text-slate-400 hover:text-slate-700 bg-slate-50 hover:bg-slate-200 w-8 h-8 rounded-lg transition-colors flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-slate-300">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        
                        {{-- 1. INPUT UNIT KERJA --}}
                        <div>
                            <label for="unit_kerja_id" class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-2">
                                Induk Unit Kerja <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="unit_kerja_id" id="unit_kerja_id" required
                                    class="block w-full rounded-xl border-2 border-slate-200 py-2.5 pl-4 pr-10 text-slate-800 font-semibold focus:border-[#1C7C54] focus:ring-0 sm:text-sm appearance-none bg-slate-50 hover:bg-white transition-colors cursor-pointer">
                                    <option value="" disabled selected>-- Tentukan Unit Kerja --</option>
                                    @foreach($unitKerjas as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <p id="error-unit_kerja_id" class="hidden text-red-500 text-xs font-medium mt-1.5"></p>
                        </div>

                        {{-- 2. INPUT LEVEL --}}
                        <div>
                            <label for="level" class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-2">
                                Tingkatan Hierarki <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="level" id="level" required
                                    class="block w-full rounded-xl border-2 border-slate-200 py-2.5 pl-4 pr-10 text-slate-800 font-semibold focus:border-[#1C7C54] focus:ring-0 sm:text-sm appearance-none bg-slate-50 hover:bg-white transition-colors cursor-pointer">
                                    <option value="" disabled selected>-- Tentukan Tingkatan --</option>
                                    <option value="bidang">Bidang (Level Utama)</option>
                                    <option value="sub_bidang">Sub Bidang (Level Cabang)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5 font-medium flex items-center gap-1">
                                <i class="fas fa-info-circle"></i> Tentukan apakah ini entitas utama atau percabangan.
                            </p>
                            <p id="error-level" class="hidden text-red-500 text-xs font-medium mt-1.5"></p>
                        </div>

                        {{-- 3. INPUT INDUK BIDANG (Conditional) --}}
                        <div id="parent_container" class="hidden transition-all duration-300 ease-in-out p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <label for="parent_id" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wide mb-2">
                                Induk Bidang Spesifik <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="parent_id" id="parent_id"
                                    class="block w-full rounded-xl border-2 border-slate-200 py-2.5 pl-4 pr-10 text-slate-800 font-semibold focus:border-blue-500 focus:ring-0 sm:text-sm appearance-none bg-white transition-colors cursor-pointer">
                                    <option value="" disabled selected>-- Menunggu Unit Kerja --</option>
                                    {{-- Diisi secara dinamis via JS --}}
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                    <i class="fas fa-level-down-alt text-xs"></i>
                                </div>
                            </div>
                            <p id="error-parent_id" class="hidden text-red-500 text-xs font-medium mt-1.5"></p>
                        </div>

                        {{-- 4. INPUT NAMA --}}
                        <div class="relative pb-2">
                            <label for="nama_bidang" class="block text-xs font-extrabold text-slate-500 uppercase tracking-wide mb-2">
                                Nomenklatur Bidang <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_bidang" id="nama_bidang" required
                                class="block w-full rounded-xl border-2 border-slate-200 py-2.5 px-4 text-slate-900 font-bold shadow-sm focus:border-[#1C7C54] focus:ring-[#1C7C54]/20 sm:text-sm transition-all outline-none placeholder-slate-300"
                                placeholder="Contoh: Bidang Pengelolaan Pendapatan">
                            <p id="error-nama_bidang" class="hidden text-red-500 text-xs font-medium mt-1.5"></p>
                        </div>

                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3 border-t border-slate-100 rounded-b-2xl">
                        <button type="submit" id="btn-save"
                            class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-[#1C7C54] px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-600/20 hover:bg-[#166443] focus:outline-none focus:ring-2 focus:ring-[#1C7C54] focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto">
                            <i class="fas fa-check-circle"></i>
                            <span id="btn-save-text">Simpan Data</span>
                        </button>
                        <button type="button" onclick="window.closeModal()"
                            class="inline-flex w-full justify-center rounded-xl bg-white px-6 py-2.5 text-sm font-bold text-slate-600 shadow-sm border-2 border-slate-200 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-200 sm:w-auto transition-all">
                            Batal
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/pages/admin/master/bidang.js'])
@endpush