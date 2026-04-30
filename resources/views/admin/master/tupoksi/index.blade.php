@extends('layouts.app', [
    'role' => 'admin',
])

@section('title', 'Data Master Tupoksi')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .form-input-tegas { 
            @apply w-full rounded-xl border-2 border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-[#1C7C54] focus:ring-0 transition-all outline-none; 
        }
        .form-label-tegas { 
            @apply block text-xs font-extrabold uppercase tracking-wide text-slate-500 mb-2; 
        }
        
        /* Custom Scrollbar for Table */
        .custom-scrollbar::-webkit-scrollbar { height: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Select2 Form Input Tegas Override */
        .select2-container .select2-selection--single {
            height: 46px !important;
            border: 2px solid #e2e8f0 !important;
            background-color: #f8fafc !important;
            border-radius: 0.75rem !important;
            padding-top: 6px !important;
            padding-left: 8px !important;
            font-size: 0.875rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            outline: none !important;
            transition: all 0.2s ease-in-out;
        }
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--single {
            background-color: #ffffff !important;
            border-color: #1C7C54 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            font-weight: 700 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 8px !important;
            right: 10px !important;
        }
        .select2-dropdown {
            border: 2px solid #1C7C54 !important;
            border-radius: 0.75rem !important;
            overflow: hidden !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }
    </style>
@endpush

@section('content')
<div class="w-full px-4 sm:px-6 py-6">
    
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 sm:mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Tupoksi</h1>
            <p class="text-sm font-medium text-slate-500 mt-1">Kelola data Tugas Pokok dan Fungsi berdasarkan pembagian Bidang.</p>
        </div>
        
        <button id="btn-create" 
            class="group bg-[#1C7C54] hover:bg-[#166443] text-white px-5 py-2.5 rounded-xl shadow-lg shadow-emerald-700/20 transition-all duration-200 flex items-center gap-2 text-sm font-bold transform active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#1C7C54] focus:ring-offset-2">
            <div class="bg-white/20 p-1 rounded-md group-hover:rotate-90 transition-transform">
                <i class="fas fa-plus fa-xs"></i>
            </div>
            Tambah Tupoksi
        </button>
    </div>

    {{-- CARD TABEL --}}
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200/60 overflow-hidden flex flex-col min-h-[500px]">
        
        {{-- Toolbar: Filters & Search --}}
        <div class="p-5 sm:p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <div class="flex items-center gap-3">
                <div class="bg-gradient-to-br from-indigo-100 to-indigo-50 text-indigo-600 w-10 h-10 rounded-xl flex items-center justify-center shadow-sm border border-indigo-100/50">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h6 class="font-bold text-slate-700">Daftar Tupoksi Organisasi</h6>
                    <span class="text-xs font-medium text-slate-400" id="total-records">Memuat kalkulasi data...</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                {{-- Selector Limit Paginasi --}}
                <div class="w-full sm:w-24">
                    <select id="limitSelect" class="block w-full pl-3 pr-8 py-2.5 border-2 border-slate-200 rounded-xl leading-5 bg-white text-sm font-semibold text-slate-700 focus:outline-none focus:ring-0 focus:border-[#1C7C54] cursor-pointer transition-colors shadow-sm">
                        <option value="10">10 Baris</option>
                        <option value="25">25 Baris</option>
                        <option value="50">50 Baris</option>
                    </select>
                </div>

                {{-- Filter Bidang --}}
                <div class="relative w-full sm:w-56">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fas fa-filter text-slate-400"></i>
                    </div>
                    <select id="filterBidang" 
                        class="block w-full pl-10 pr-8 py-2.5 border-2 border-slate-200 rounded-xl leading-5 bg-white text-sm font-semibold text-slate-700 focus:outline-none focus:ring-0 focus:border-[#1C7C54] appearance-none cursor-pointer transition-colors shadow-sm text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="">Semua Bidang</option>
                        @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}">{{ $bidang->nama_bidang }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none">
                        <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                    </div>
                </div>

                {{-- Input Pencarian --}}
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400"></i>
                    </div>
                    <input type="text" id="searchInput" 
                        class="block w-full pl-10 pr-4 py-2.5 border-2 border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 font-medium text-sm text-slate-700 focus:outline-none focus:ring-0 focus:border-[#1C7C54] transition-colors shadow-inner" 
                        placeholder="Cari uraian tupoksi...">
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="relative flex-1 overflow-x-auto custom-scrollbar bg-slate-50/30">
            <table class="min-w-full divide-y divide-slate-200" id="table-tupoksi">
                <thead class="bg-white text-slate-500 uppercase text-[10px] font-extrabold tracking-wider sticky top-0 z-10 shadow-sm border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 w-[5%] text-center">No</th>
                        <th scope="col" class="px-6 py-4 w-[30%]">Bidang / Unit Kerja Terikat</th>
                        <th scope="col" class="px-6 py-4 w-[50%]">Uraian Tugas & Fungsi</th>
                        <th scope="col" class="px-6 py-4 w-[15%] text-center">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="text-sm font-medium text-slate-600 divide-y divide-slate-100 bg-white">
                    {{-- Data dirender secara dinamis via JavaScript --}}
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
                    <i class="fas fa-clipboard-list text-3xl text-slate-300"></i>
                </div>
                <h3 class="text-slate-700 font-bold text-base mb-1">Tidak ada tupoksi ditemukan</h3>
                <p class="text-slate-500 text-sm">Sesuaikan filter atau kata kunci pencarian Anda.</p>
            </div>
        </div>

        {{-- PAGINATION CONTROLS --}}
        <div class="px-6 py-4 bg-white border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 z-20">
            <span class="text-xs text-slate-500 font-bold bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 hidden sm:block" id="pagination-info">Kalkulasi indeks...</span>
            <div class="text-xs text-slate-500 font-bold sm:hidden block w-full text-center" id="pagination-info-mobile">Memuat...</div>
            
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm ml-auto flex-wrap justify-center gap-1.5" aria-label="Pagination" id="pagination-links">
                {{-- Pagination Links dinamis dirender via JS --}}
            </nav>
        </div>
    </div>
</div>

{{-- MODAL FORM --}}
<div id="modal-tupoksi" class="fixed inset-0 z-[9999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0" id="modal-backdrop"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-visible rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" id="modal-panel">
                
                <form id="form-tupoksi" novalidate>
                    {{-- Hidden Identifiers --}}
                    <input type="hidden" name="id" id="id">

                    <div class="bg-white px-6 py-5 border-b border-slate-100 flex justify-between items-center sticky top-0 z-10 rounded-t-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-[#1C7C54] flex items-center justify-center">
                                <i class="fas fa-clipboard-check text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-800 tracking-tight" id="modal-title">Konfigurasi Tupoksi</h3>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Penugasan Berdasarkan Bidang</p>
                            </div>
                        </div>
                        <button type="button" class="close-modal text-slate-400 hover:text-slate-700 bg-slate-50 hover:bg-slate-200 w-8 h-8 rounded-lg transition-colors flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-slate-300">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        
                        {{-- INPUT BIDANG --}}
                        <div>
                            <label for="bidang_id" class="form-label-tegas">
                                Unit / Bidang Penugasan <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select class="select2 w-full" id="bidang_id" name="bidang_id" style="width: 100%;">
                                    <option value="">-- Tentukan Bidang --</option>
                                    @foreach($bidangs as $bidang)
                                        <option value="{{ $bidang->id }}">
                                            {{ $bidang->nama_bidang }} 
                                            @if($bidang->level == 'sub_bidang') (Sub Bidang) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1.5 text-xs font-medium text-red-500 hidden" id="error-bidang_id"></p>
                        </div>

                        {{-- INPUT URAIAN TUGAS --}}
                        <div class="relative pb-2">
                            <label for="uraian_tugas" class="form-label-tegas">
                                Deskripsi Uraian Tugas <span class="text-red-500">*</span>
                            </label>
                            <textarea id="uraian_tugas" name="uraian_tugas" rows="4" required
                                class="form-input-tegas placeholder-slate-300 resize-none" 
                                placeholder="Contoh: Menyusun rencana strategis dan operasional untuk pendapatan daerah..."></textarea>
                            <p class="mt-2 text-[11px] font-medium text-slate-400 flex items-center gap-1">
                                <i class="fas fa-info-circle text-[#1C7C54]"></i> Uraikan dengan jelas tugas dan fungsionalnya.
                            </p>
                            <p class="mt-1.5 text-xs font-medium text-red-500 hidden" id="error-uraian_tugas"></p>
                        </div>

                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3 border-t border-slate-100 rounded-b-2xl">
                        <button type="submit" id="btn-save" 
                            class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-[#1C7C54] px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-600/20 hover:bg-[#166443] focus:outline-none focus:ring-2 focus:ring-[#1C7C54] focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto">
                            <i class="fas fa-check-circle" id="btn-save-icon"></i>
                            <span id="btn-save-text">Simpan Data</span>
                            <span id="btn-save-loading" class="hidden flex items-center gap-2">
                                <i class="fas fa-circle-notch fa-spin"></i> Memproses...
                            </span>
                        </button>
                        <button type="button" class="close-modal inline-flex w-full justify-center rounded-xl bg-white px-6 py-2.5 text-sm font-bold text-slate-600 shadow-sm border-2 border-slate-200 hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-200 sm:w-auto transition-all">
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite('resources/js/pages/admin/master/tupoksi.js')
@endpush