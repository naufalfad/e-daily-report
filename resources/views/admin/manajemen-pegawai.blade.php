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
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }

    /* Custom Select Icon */
    .custom-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        appearance: none;
        padding-right: 2.5rem;
    }
</style>

{{-- x-data Wrapper: Menghubungkan Blade dengan AlpineJS Component --}}
<div x-data="manajemenPegawaiData()" x-init="initPage()" class="w-full h-full px-6 flex flex-col relative min-h-screen">

    {{-- HEADER SECTION --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-5 shrink-0">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Data Master Pegawai</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data kepegawaian, jabatan, dan struktur organisasi.</p>
        </div>
        
        <div class="flex flex-wrap gap-3">
            {{-- Button Download Template --}}
            <a href="{{ asset('assets/template/template_import_user.csv') }}" download
                class="group flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all">
                <div class="bg-slate-100 p-1 rounded-md group-hover:bg-white transition-colors">
                    <i class="fas fa-file-csv text-slate-500 group-hover:text-[#1C7C54]"></i>
                </div>
                <span>Template Excel</span>
            </a>

            {{-- Button Import --}}
            <button @click="toggleUpload(true)"
                class="group flex items-center gap-2 px-4 py-2.5 bg-emerald-50 text-[#1C7C54] border border-emerald-100 text-sm font-bold rounded-xl hover:bg-emerald-100 hover:border-emerald-200 transition-all">
                <i class="fas fa-cloud-upload-alt text-lg"></i>
                <span>Import Excel</span>
            </button>

            {{-- Button Add --}}
            <button @click="toggleAdd(true)"
                class="flex items-center gap-2 px-5 py-2.5 bg-[#1C7C54] text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-700/20 hover:bg-[#166443] hover:shadow-emerald-700/30 transition-all transform active:scale-95">
                <i class="fas fa-plus"></i>
                <span>Tambah Pegawai</span>
            </button>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-5 shrink-0">
        {{-- Search Input --}}
        <div class="md:col-span-8 lg:col-span-9 relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400 text-sm"></i>
            </div>
            {{-- x-model binding ke variable search di JS --}}
            <input type="text" x-model="search" @input.debounce.500ms="fetchData()"
                placeholder="Cari pegawai berdasarkan Nama atau NIP..."
                class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-700 placeholder-slate-400 shadow-sm focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 transition-all outline-none">
        </div>

        {{-- Filter Unit Kerja --}}
        <div class="md:col-span-4 lg:col-span-3">
            {{-- ID filterUnitKerja digunakan oleh JS untuk Event Listener --}}
            <select x-model="filterUnitKerja" @change="fetchData()" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 outline-none custom-select cursor-pointer">
                <option value="">Semua Unit Kerja</option>
                @foreach($unitKerjas as $unit)
                    {{-- [FIX] Menggunakan nama_unit --}}
                    <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex-1 flex flex-col relative min-h-[500px]">
        
        {{-- Table Container --}}
        <div class="overflow-x-auto custom-scrollbar flex-1 relative">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 sticky top-0 z-10 backdrop-blur-sm shadow-sm border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-[35%]">Identitas Pegawai</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-[25%]">Jabatan & Unit</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-[15%]">Role</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-[10%]">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-[15%]">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="divide-y divide-slate-100 bg-white">
                    
                    {{-- Loop Data from Alpine JS --}}
                    <template x-for="item in items" :key="item.id">
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            
                            {{-- Kolom 1: Identitas --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-start gap-4">
                                    {{-- Avatar --}}
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200 shrink-0">
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
                                            <span class="text-[10px] uppercase font-bold text-slate-400 border border-slate-200 rounded px-1.5 py-0.5 bg-slate-50">NIP</span>
                                            <span class="text-xs font-mono font-medium text-slate-600" x-text="item.nip"></span>
                                        </div>
                                        {{-- Tampilkan Pangkat jika ada --}}
                                        <template x-if="item.pangkat">
                                            <div class="text-[11px] text-slate-500 mt-1 truncate" x-text="item.pangkat"></div>
                                        </template>
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom 2: Jabatan & Unit --}}
                            <td class="px-6 py-4 align-top pt-5">
                                <div class="flex flex-col gap-2">
                                    {{-- Jabatan --}}
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-0.5">Jabatan</span>
                                        <span class="text-sm font-bold text-slate-700 line-clamp-2" x-text="item.jabatan?.nama_jabatan || '-'"></span>
                                    </div>
                                    
                                    {{-- Unit Kerja / Bidang --}}
                                    <div class="pl-2 border-l-2 border-slate-200">
                                        {{-- [FIX] Menggunakan nama_unit untuk tampilan Alpine --}}
                                        <div class="text-xs font-medium text-slate-600 mb-0.5" x-text="item.unit_kerja?.nama_unit || '-'"></div>
                                        
                                        {{-- Tampilkan Bidang/Sub-Bidang dengan indikator --}}
                                        <template x-if="item.bidang">
                                            <div class="text-[11px] text-slate-500 flex items-center gap-1">
                                                <i class="fas fa-level-up-alt rotate-90 text-slate-300"></i>
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
                                        <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wide"
                                              :class="{
                                                  'bg-purple-50 text-purple-700 border border-purple-100': role.name === 'admin',
                                                  'bg-blue-50 text-blue-700 border border-blue-100': role.name === 'penilai' || role.name === 'kadis',
                                                  'bg-slate-50 text-slate-600 border border-slate-200': role.name === 'staf' || role.name === 'pegawai'
                                              }"
                                              x-text="role.name">
                                        </span>
                                    </template>
                                </div>
                            </td>

                            {{-- Kolom 4: Status --}}
                            <td class="px-6 py-4 align-top pt-5 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border"
                                      :class="item.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-700 border-red-100'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="item.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                    <span x-text="item.is_active ? 'Aktif' : 'Nonaktif'"></span>
                                </span>
                            </td>

                            {{-- Kolom 5: Aksi --}}
                            <td class="px-6 py-4 align-top pt-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button @click="openModalEdit(item)" 
                                        class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-amber-500 rounded-lg hover:bg-amber-50 hover:border-amber-200 hover:shadow-sm transition-all"
                                        title="Edit Data">
                                        <i class="fas fa-pen-to-square text-xs"></i>
                                    </button>
                                    <button @click="deleteItem(item.id)" 
                                        class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-red-500 rounded-lg hover:bg-red-50 hover:border-red-200 hover:shadow-sm transition-all"
                                        title="Hapus Data">
                                        <i class="fas fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    {{-- Empty State --}}
                    <tr x-show="!isLoading && items.length === 0" x-cloak>
                        <td colspan="5" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <img src="{{ asset('assets/tips.svg') }}" class="h-32 w-32 opacity-50 mb-4 grayscale" alt="Empty">
                                <p class="text-slate-500 font-medium">Data tidak ditemukan.</p>
                                <p class="text-slate-400 text-sm mt-1">Coba kata kunci lain atau tambahkan pegawai baru.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Loading Overlay --}}
            <div x-show="isLoading" class="absolute inset-0 bg-white/80 backdrop-blur-[1px] flex flex-col items-center justify-center z-20">
                <svg class="animate-spin h-8 w-8 text-[#1C7C54] mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-slate-500 font-medium text-sm">Memuat data pegawai...</span>
            </div>
        </div>

        {{-- PAGINATION CONTROLS --}}
        <div class="px-6 py-4 bg-white border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 shrink-0">
            <span class="text-xs text-slate-500 font-medium">
                Menampilkan <span x-text="pagination.from"></span> - <span x-text="pagination.to"></span> dari <span x-text="pagination.total"></span> data
            </span>
            
            <div class="flex items-center gap-1">
                <button @click="fetchData(pagination.current_page - 1)" 
                        :disabled="!pagination.prev_page_url"
                        class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed transition-all hover:bg-slate-50 active:bg-slate-100">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
                
                <span class="px-3 text-xs font-medium text-slate-600">
                    Halaman <span x-text="pagination.current_page"></span>
                </span>

                <button @click="fetchData(pagination.current_page + 1)" 
                        :disabled="!pagination.next_page_url"
                        class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg text-slate-600 hover:text-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition-all hover:bg-slate-50 active:bg-slate-100">
                    <i class="fas fa-chevron-right text-xs"></i>
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