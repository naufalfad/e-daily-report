@php($title = 'Manajemen Pegawai')

@extends('layouts.app', [
    'title' => $title,
    'role' => 'admin',
    'active' => 'manajemen-pegawai',
])

{{-- Inject Model untuk Dropdown Filter Server-Side --}}
@inject('unitKerjaModel', 'App\Models\UnitKerja')
@php($unitKerjas = $unitKerjaModel->orderBy('nama_unit', 'asc')->get())

@section('content')
<style>
    [x-cloak] { display: none !important; }
    
    /* Custom Scrollbar for Table */
    .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }

    /* Custom Select Icon */
    .custom-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.2em 1.2em;
        appearance: none;
        padding-right: 2.5rem;
    }
</style>

{{-- x-data Wrapper: Menghubungkan Blade dengan AlpineJS Component --}}
<div x-data="manajemenPegawaiData()" x-init="initPage()" class="w-full h-full px-4 sm:px-6 py-6 flex flex-col relative min-h-screen">

    {{-- HEADER SECTION --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-5 shrink-0">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Data Master Pegawai</h1>
            <p class="text-sm font-medium text-slate-500 mt-1">Kelola direktori kepegawaian, alokasi jabatan, dan struktur organisasi.</p>
        </div>
        
        <div class="flex flex-wrap gap-2.5">
            {{-- Button Download Template --}}
            <a href="{{ asset('assets/template/template_import_user.csv') }}" download
                class="group flex items-center gap-2 px-4 py-2.5 bg-white border-2 border-slate-200 text-slate-600 text-sm font-bold rounded-xl shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all focus:outline-none focus:ring-2 focus:ring-slate-200">
                <div class="bg-slate-100 p-1 rounded-md group-hover:bg-white transition-colors">
                    <i class="fas fa-file-csv text-slate-500 group-hover:text-[#1C7C54]"></i>
                </div>
                <span>Template Excel</span>
            </a>

            {{-- Button Import --}}
            <button @click="toggleUpload(true)"
                class="group flex items-center gap-2 px-4 py-2.5 bg-emerald-50 text-[#1C7C54] border-2 border-emerald-100 text-sm font-bold rounded-xl hover:bg-emerald-100 hover:border-emerald-200 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-200">
                <i class="fas fa-cloud-upload-alt text-base"></i>
                <span>Import Data</span>
            </button>

            {{-- Button Add --}}
            <button @click="toggleAdd(true)"
                class="flex items-center gap-2 px-5 py-2.5 bg-[#1C7C54] text-white text-sm font-bold rounded-xl shadow-md shadow-emerald-700/20 hover:bg-[#166443] transition-all transform active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#1C7C54] focus:ring-offset-2">
                <div class="bg-white/20 p-0.5 rounded-md">
                    <i class="fas fa-plus fa-sm"></i>
                </div>
                <span>Tambah Pegawai</span>
            </button>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-5 shrink-0 bg-white p-3 rounded-2xl border border-slate-200 shadow-sm">
        
        {{-- Limit Selector (Baru ditambahkan untuk konsistensi) --}}
        <div class="md:col-span-2">
            <select x-model="limit" @change="fetchData(1)" class="w-full rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-white focus:bg-white focus:border-[#1C7C54] focus:ring-0 custom-select transition-all outline-none">
                <option value="10">10 Baris</option>
                <option value="25">25 Baris</option>
                <option value="50">50 Baris</option>
                <option value="100">100 Baris</option>
            </select>
        </div>

        {{-- Search Input --}}
        <div class="md:col-span-6 relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400 text-sm"></i>
            </div>
            <input type="text" x-model="search" @input.debounce.500ms="fetchData(1)"
                placeholder="Cari berdasarkan Nama atau NIP pegawai..."
                class="w-full pl-11 pr-4 py-2.5 rounded-xl border-2 border-slate-100 bg-slate-50 text-sm font-bold text-slate-800 placeholder-slate-400 hover:bg-white focus:bg-white focus:border-[#1C7C54] focus:ring-0 transition-all outline-none">
        </div>

        {{-- Filter Unit Kerja --}}
        <div class="md:col-span-4 relative">
            <select x-model="filterUnitKerja" @change="fetchData(1)" class="w-full rounded-xl border-2 border-slate-100 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-white focus:bg-white focus:border-[#1C7C54] focus:ring-0 custom-select transition-all outline-none text-ellipsis overflow-hidden whitespace-nowrap">
                <option value="">-- Semua Unit Kerja --</option>
                @foreach($unitKerjas as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex-1 flex flex-col relative min-h-[500px]">
        
        {{-- Table Container --}}
        <div class="overflow-x-auto custom-scrollbar flex-1 relative bg-slate-50/30">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead class="bg-white text-slate-500 uppercase text-[10px] font-extrabold tracking-wider sticky top-0 z-10 shadow-sm border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 w-[35%]">Identitas Pegawai</th>
                        <th class="px-6 py-4 w-[30%]">Konfigurasi Jabatan & Unit</th>
                        <th class="px-6 py-4 text-center w-[12%]">Role Akses</th>
                        <th class="px-6 py-4 text-center w-[10%]">Status</th>
                        <th class="px-6 py-4 text-center w-[13%]">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="text-sm font-medium text-slate-600 divide-y divide-slate-100 bg-white">
                    
                    <template x-for="item in items" :key="item.id">
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            
                            {{-- Kolom 1: Identitas --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-start gap-4">
                                    {{-- Avatar --}}
                                    <div class="w-11 h-11 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200 shrink-0 shadow-sm">
                                        <template x-if="item.foto_profil_url">
                                            <img :src="item.foto_profil_url" class="w-full h-full object-cover" alt="Foto Profil">
                                        </template>
                                        <template x-if="!item.foto_profil_url">
                                            <i class="fas fa-user text-slate-400"></i>
                                        </template>
                                    </div>
                                    
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 text-[15px] truncate" x-text="item.name"></div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[9px] uppercase font-extrabold text-slate-500 border border-slate-200 rounded px-1.5 py-0.5 bg-slate-50 shadow-sm">NIP</span>
                                            <span class="text-xs font-mono font-bold text-[#1C7C54]" x-text="item.nip"></span>
                                        </div>
                                        <template x-if="item.pangkat">
                                            <div class="text-[11px] font-bold text-slate-400 mt-1.5 truncate" x-text="item.pangkat"></div>
                                        </template>
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom 2: Jabatan & Unit --}}
                            <td class="px-6 py-4 align-top pt-5">
                                <div class="flex flex-col gap-2.5">
                                    <div>
                                        <span class="block text-[9px] font-extrabold text-slate-400 uppercase tracking-wider mb-0.5">Penugasan Jabatan</span>
                                        <span class="text-sm font-bold text-slate-700 line-clamp-2 leading-snug" x-text="item.jabatan?.nama_jabatan || '-'"></span>
                                    </div>
                                    
                                    <div class="pl-2.5 border-l-2 border-[#1C7C54]/30 bg-emerald-50/30 py-1 pr-2 rounded-r-md">
                                        <div class="text-xs font-bold text-[#1C7C54] mb-0.5 truncate" x-text="item.unit_kerja?.nama_unit || '-'"></div>
                                        <template x-if="item.bidang">
                                            <div class="text-[10px] font-bold text-slate-500 flex items-center gap-1.5 truncate">
                                                <i class="fas fa-arrow-turn-up rotate-90 text-slate-300"></i>
                                                <span x-text="item.bidang.nama_bidang"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom 3: Role --}}
                            <td class="px-6 py-4 align-top pt-5 text-center">
                                <div class="flex flex-wrap justify-center gap-1">
                                    <template x-for="role in item.roles" :key="role.id">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-extrabold uppercase tracking-wider shadow-sm"
                                              :class="{
                                                  'bg-purple-50 text-purple-700 border border-purple-200': role.name === 'admin',
                                                  'bg-blue-50 text-blue-700 border border-blue-200': role.name === 'penilai' || role.name === 'kadis',
                                                  'bg-slate-50 text-slate-600 border border-slate-200': role.name === 'staf' || role.name === 'pegawai'
                                              }"
                                              x-text="role.name">
                                        </span>
                                    </template>
                                </div>
                            </td>

                            {{-- Kolom 4: Status --}}
                            <td class="px-6 py-4 align-top pt-5 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider border shadow-sm"
                                      :class="item.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="item.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'"></span>
                                    <span x-text="item.is_active ? 'Aktif' : 'Nonaktif'"></span>
                                </span>
                            </td>

                            {{-- Kolom 5: Aksi --}}
                            <td class="px-6 py-4 align-top pt-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button @click="openModalEdit(item)" 
                                        class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-amber-500 rounded-lg hover:bg-amber-50 hover:border-amber-200 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-200 transition-all"
                                        title="Edit Profil">
                                        <i class="fas fa-pen-to-square text-xs"></i>
                                    </button>
                                    <button @click="deleteItem(item.id)" 
                                        class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-red-500 rounded-lg hover:bg-red-50 hover:border-red-200 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-red-200 transition-all"
                                        title="Hapus Kredensial">
                                        <i class="fas fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    {{-- Empty State --}}
                    <tr x-show="!isLoading && items.length === 0" x-cloak>
                        <td colspan="5" class="py-16 text-center bg-slate-50/50">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-20 h-20 mb-4 bg-white rounded-full shadow-sm flex items-center justify-center border border-slate-100">
                                    <i class="fas fa-users-slash text-3xl text-slate-300"></i>
                                </div>
                                <h3 class="text-slate-700 font-bold text-base mb-1">Entitas tidak ditemukan</h3>
                                <p class="text-slate-500 text-sm">Sesuaikan filter pencarian atau registrasi pegawai baru.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Loading State --}}
            <div x-show="isLoading" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center z-20 transition-opacity duration-300">
                <div class="p-4 bg-white shadow-lg rounded-2xl flex items-center gap-3 border border-slate-100">
                    <svg class="animate-spin h-6 w-6 text-[#1C7C54]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-slate-700 font-bold text-sm tracking-tight">Sinkronisasi Direktori...</span>
                </div>
            </div>
        </div>

        {{-- PAGINATION CONTROLS (Direfaktor sesuai standarisasi Emerald) --}}
        <div x-show="pagination.total > 0" x-cloak class="px-6 py-4 bg-white border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 z-20 transition-all">
            
            {{-- Bagian Kiri: Info Data --}}
            <div class="text-xs text-slate-500 font-bold bg-slate-50 px-3 py-2 rounded-lg border border-slate-200 w-full sm:w-auto text-center sm:text-left shadow-sm">
                Menampilkan 
                <span class="font-extrabold text-slate-800" x-text="pagination.from"></span> 
                - 
                <span class="font-extrabold text-slate-800" x-text="pagination.to"></span> 
                dari 
                <span class="font-extrabold text-[#1C7C54]" x-text="pagination.total"></span> 
                data
            </div>

            {{-- Bagian Kanan: Tombol Navigasi --}}
            <div class="flex items-center justify-center sm:justify-end gap-1.5 w-full sm:w-auto">
                
                {{-- Tombol: First Page --}}
                <button @click="fetchData(1)" 
                    :disabled="pagination.current_page === 1"
                    class="px-3 py-2 text-slate-500 hover:text-[#1C7C54] disabled:opacity-40 disabled:cursor-not-allowed transition-all rounded-lg border border-slate-200 hover:border-[#1C7C54] hover:bg-emerald-50 active:bg-emerald-100 flex items-center justify-center shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30"
                    title="Halaman Pertama">
                    <i class="fas fa-angle-double-left text-[11px]"></i>
                </button>

                {{-- Tombol: Previous --}}
                <button @click="fetchData(pagination.current_page - 1)" 
                    :disabled="!pagination.prev_page_url"
                    class="px-3.5 py-2 text-slate-500 hover:text-[#1C7C54] disabled:opacity-40 disabled:cursor-not-allowed transition-all rounded-lg border border-slate-200 hover:border-[#1C7C54] hover:bg-emerald-50 active:bg-emerald-100 text-xs font-bold flex items-center gap-1.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30"
                    title="Halaman Sebelumnya">
                    <i class="fas fa-chevron-left text-[10px]"></i> Prev
                </button>

                {{-- Info Halaman (Page X of Y) --}}
                <div class="bg-white border-y border-slate-200 text-slate-700 px-4 py-2 font-extrabold text-xs shadow-sm flex items-center gap-1 cursor-default">
                    <span x-text="pagination.current_page" class="text-[#1C7C54]"></span>
                    <span class="text-slate-300">/</span>
                    <span x-text="pagination.last_page"></span>
                </div>

                {{-- Tombol: Next --}}
                <button @click="fetchData(pagination.current_page + 1)" 
                    :disabled="!pagination.next_page_url"
                    class="px-3.5 py-2 text-slate-500 hover:text-[#1C7C54] disabled:opacity-40 disabled:cursor-not-allowed transition-all rounded-lg border border-slate-200 hover:border-[#1C7C54] hover:bg-emerald-50 active:bg-emerald-100 text-xs font-bold flex items-center gap-1.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30"
                    title="Halaman Selanjutnya">
                    Next <i class="fas fa-chevron-right text-[10px]"></i>
                </button>

                {{-- Tombol: Last Page --}}
                <button @click="fetchData(pagination.last_page)" 
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-3 py-2 text-slate-500 hover:text-[#1C7C54] disabled:opacity-40 disabled:cursor-not-allowed transition-all rounded-lg border border-slate-200 hover:border-[#1C7C54] hover:bg-emerald-50 active:bg-emerald-100 flex items-center justify-center shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30"
                    title="Halaman Terakhir">
                    <i class="fas fa-angle-double-right text-[11px]"></i>
                </button>

            </div>
        </div>
    </div>

    {{-- MODALS PARTIALS --}}
    @include('admin.partials.modal-pegawai') 
    @include('admin.partials.modal-upload-pegawai')

</div>
@endsection

@push('scripts')
    {{-- Memuat script JS --}}
    @vite(['resources/js/pages/admin/manajemen-pegawai.js'])
@endpush