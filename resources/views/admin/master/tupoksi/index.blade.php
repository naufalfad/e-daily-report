@extends('layouts.app', [
    'role' => 'admin',])

@section('title', 'Data Master Tupoksi')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 42px !important;
            border-color: #cbd5e1 !important;
            border-radius: 0.5rem !important;
            padding-top: 5px !important;
            outline: none !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 8px !important;
        }
        .select2-container--default .select2-selection--single:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2) !important;
        }
        .loading-overlay {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(2px);
        }
    </style>
@endpush

@section('content')
<div class="w-full px-4 py-6 mx-auto">
    
    {{-- Header Page --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Manajemen Tupoksi</h1>
            <p class="text-sm text-slate-500">Kelola Tugas Pokok dan Fungsi (Tupoksi) berdasarkan Bidang.</p>
        </div>
        <div>
            <button id="btn-create" 
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm">
                <i class="fas fa-plus mr-2"></i> Tambah Data
            </button>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative min-h-[400px]">
        
        {{-- Toolbar: Filter & Search --}}
        <div class="p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            {{-- Filter Bidang (Kiri) --}}
            <div class="flex item-center w-full gap-4 lg:w-auto">
                <div class="relative lg:w-auto">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-filter text-slate-400"></i>
                    </div>
                    <select id="filterBidang" 
                        class="block w-full pl-10 pr-10 py-2 border border-slate-300 rounded-lg bg-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm text-slate-700 appearance-none cursor-pointer hover:bg-slate-50 transition">
                        <option value="">-- Semua Bidang --</option>
                        @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}">
                                {{ $bidang->nama_bidang }} 
                            </option>
                        @endforeach
                    </select>
                    {{-- Chevron Custom --}}
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-chevron-down text-xs text-slate-400"></i>
                    </div>
                </div>
                <div class="relative w-full lg:w-auto">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-slate-400"></i>
                    </div>
                    <input type="text" id="searchInput" 
                        class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:placeholder-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm transition duration-150 ease-in-out" 
                        placeholder="Cari uraian atau nama bidang...">
                </div>
            </div>

            {{-- Search Input (Kanan) --}}
            <div class="flex items-center gap-3 w-full lg:w-auto">
                {{-- Info Paginasi Kecil --}}
                <div id="pagination-info" class="text-xs text-slate-500 font-medium whitespace-nowrap hidden sm:block">
                    Memuat...
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200" id="table-tupoksi">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-16 text-center">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/4">Bidang / Unit</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Uraian Tugas</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="bg-white divide-y divide-slate-200">
                    {{-- Data will be injected here via JS --}}
                </tbody>
            </table>
        </div>

        {{-- Loading State --}}
        <div id="loading-state" class="loading-overlay absolute inset-0 z-10 flex flex-col items-center justify-center hidden">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mb-2"></div>
            <span class="text-sm text-indigo-600 font-medium">Sedang memuat data...</span>
        </div>

        {{-- Empty State --}}
        <div id="empty-state" class="hidden flex flex-col items-center justify-center py-12">
            <div class="bg-slate-50 p-4 rounded-full mb-3">
                <i class="fas fa-search text-3xl text-slate-300"></i>
            </div>
            <h3 class="text-lg font-medium text-slate-900">Data tidak ditemukan</h3>
            <p class="text-slate-500 text-sm mt-1">Coba ubah kata kunci pencarian atau filter bidang.</p>
        </div>

        {{-- Footer: Pagination --}}
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-4">
             <div class="text-sm text-slate-500 sm:hidden block" id="pagination-info-mobile"></div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm ml-auto" aria-label="Pagination" id="pagination-links">
                {{-- Pagination Links via JS --}}
            </nav>
        </div>
    </div>
</div>

{{-- MODAL FORM --}}
<div id="modal-tupoksi" class="relative z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0" id="modal-backdrop"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" id="modal-panel">
                <div class="bg-indigo-600 px-4 py-3 sm:px-6">
                    <h3 class="text-base font-semibold leading-6 text-white" id="modal-title">Form Data Tupoksi</h3>
                </div>
                <form id="form-tupoksi">
                    <div class="px-4 py-5 sm:p-6 space-y-4">
                        <input type="hidden" name="id" id="id">
                        <div>
                            <label for="bidang_id" class="block text-sm font-medium leading-6 text-slate-900 mb-1">
                                Pilih Bidang <span class="text-red-500">*</span>
                            </label>
                            <select class="select2 w-full" id="bidang_id" name="bidang_id" style="width: 100%;">
                                <option value="">-- Pilih Bidang --</option>
                                @foreach($bidangs as $bidang)
                                    <option value="{{ $bidang->id }}">
                                        {{ $bidang->nama_bidang }} 
                                        @if($bidang->level == 'sub_bidang') (Sub) @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-red-600 hidden" id="error-bidang_id"></p>
                        </div>
                        <div>
                            <label for="uraian_tugas" class="block text-sm font-medium leading-6 text-slate-900 mb-1">
                                Uraian Tugas <span class="text-red-500">*</span>
                            </label>
                            <textarea id="uraian_tugas" name="uraian_tugas" rows="4" 
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                                placeholder="Contoh: Menyusun rencana strategis..."></textarea>
                            <p class="mt-1 text-xs text-red-600 hidden" id="error-uraian_tugas"></p>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-100">
                        <button type="submit" id="btn-save" 
                            class="inline-flex w-full justify-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="btn-save-text">Simpan Data</span>
                            <span id="btn-save-loading" class="hidden"><i class="fas fa-spinner fa-spin mr-1"></i> Proses...</span>
                        </button>
                        <button type="button" class="close-modal mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
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