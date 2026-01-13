@extends('layouts.app', [
    'role' => 'admin',])

@section('title', 'Master Unit Kerja')

@section('content')
<div class="w-full px-6 py-6">
    
    {{-- 1. HEADER & STATS SUMMARY --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Unit Kerja</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data OPD, struktur organisasi, dan hierarki jabatan.</p>
        </div>
        
        <button onclick="openModal('add')" 
            class="group bg-[#1C7C54] hover:bg-[#166443] text-white px-5 py-2.5 rounded-xl shadow-lg shadow-emerald-700/20 transition-all duration-200 flex items-center gap-2 text-sm font-medium transform active:scale-95">
            <div class="bg-white/20 p-1 rounded-md group-hover:rotate-90 transition-transform">
                <i class="fas fa-plus fa-xs"></i>
            </div>
            Tambah Unit Baru
        </button>
    </div>

    {{-- 2. MAIN CARD --}}
    <div class="bg-white rounded-[24px] shadow-sm ring-1 ring-slate-200/60 overflow-hidden flex flex-col min-h-[500px]">
        
        {{-- Toolbar: Custom Search & Filters --}}
        <div class="p-6 border-b border-slate-100 bg-slate-50/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            
            {{-- Title Section --}}
            <div class="flex items-center gap-3">
                <div class="bg-emerald-100 text-emerald-600 w-10 h-10 rounded-xl flex items-center justify-center shadow-sm">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <h6 class="font-semibold text-slate-700">Daftar Unit Kerja</h6>
                    <span class="text-xs text-slate-400" id="total-records">Memuat data...</span>
                </div>
            </div>

            {{-- Custom Search Input --}}
            <div class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
                <input type="text" id="searchInput" 
                    class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] sm:text-sm transition-shadow duration-200" 
                    placeholder="Cari nama unit kerja...">
            </div>
        </div>

        {{-- Table Content --}}
        <div class="relative flex-1 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] font-bold tracking-wider sticky top-0 z-10 backdrop-blur-sm">
                    <tr>
                        <th class="px-6 py-4 border-b border-slate-100 w-[5%] text-center">No</th>
                        <th class="px-6 py-4 border-b border-slate-100">Nama Unit Kerja</th>
                        <th class="px-6 py-4 border-b border-slate-100 text-center w-[15%]">Struktur</th>
                        <th class="px-6 py-4 border-b border-slate-100 text-center w-[15%]">Personil</th>
                        <th class="px-6 py-4 border-b border-slate-100 text-center w-[15%]">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="text-sm text-slate-600 divide-y divide-slate-100 bg-white">
                    {{-- Data via AJAX --}}
                </tbody>
            </table>

            {{-- Loading State --}}
            <div id="loading-state" class="absolute inset-0 bg-white/80 backdrop-blur-[1px] flex flex-col items-center justify-center z-20 hidden">
                <div class="flex items-center gap-3">
                    <svg class="animate-spin h-6 w-6 text-[#1C7C54]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-slate-500 font-medium text-sm">Memuat data...</span>
                </div>
            </div>

            {{-- Empty State --}}
            <div id="empty-state" class="hidden absolute inset-0 flex flex-col items-center justify-center text-center z-10">
                <img src="{{ asset('assets/tips.svg') }}" class="h-24 w-24 mb-3 opacity-60" alt="No Data">
                <p class="text-slate-500 font-medium">Data tidak ditemukan.</p>
            </div>
        </div>

        {{-- PAGINATION CONTROLS --}}
        <div class="px-6 py-4 bg-white border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4" id="pagination-wrapper">
            <span class="text-xs text-slate-500 font-medium" id="pagination-info">Menyiapkan data...</span>
            
            <div class="flex items-center gap-1">
                <button id="prev-page" class="p-2 text-slate-400 hover:text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed transition-all rounded-lg hover:bg-slate-50 active:bg-slate-100">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center gap-1"></div>
                <button id="next-page" class="p-2 text-slate-600 hover:text-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition-all rounded-lg hover:bg-slate-50 active:bg-slate-100">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 3. MODAL COMPONENT (Optimized) --}}
<div id="modalUnit" class="fixed inset-0 z-[9999] hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] transition-opacity opacity-0" id="modalBackdrop"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-md opacity-0 scale-95" id="modalPanel">
                
                <form id="formUnit">
                    {{-- CSRF Token untuk AJAX --}}
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" id="unit_id" name="id">
                    <input type="hidden" name="_method" id="method" value="POST">

                    <div class="bg-white px-6 py-5 border-b border-slate-100 flex justify-between items-center sticky top-0 z-10">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800" id="modalTitle">Tambah Unit Kerja</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Isi informasi unit kerja dengan benar.</p>
                        </div>
                        <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 p-2 rounded-lg transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <label for="nama_unit" class="block text-sm font-semibold text-slate-700 mb-2">
                                Nama Unit Kerja <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_unit" id="nama_unit" required
                                class="block w-full rounded-xl border-slate-300 py-3 px-4 text-slate-900 shadow-sm focus:border-[#1C7C54] focus:ring-[#1C7C54] sm:text-sm transition-all"
                                placeholder="Contoh: Badan Pendapatan Daerah">
                            <p class="mt-2 text-xs text-slate-400">Pastikan penulisan nama unit sesuai dengan nomenklatur resmi.</p>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-100">
                        <button type="submit" 
                            class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-[#1C7C54] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#166443] focus:outline-none focus:ring-2 focus:ring-[#1C7C54] focus:ring-offset-2 sm:w-auto transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-save"></i> Simpan Data
                        </button>
                        <button type="button" onclick="closeModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-all">
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
{{-- Load JS Modular --}}
@vite(['resources/js/pages/admin/master/unit-kerja.js'])
@endpush