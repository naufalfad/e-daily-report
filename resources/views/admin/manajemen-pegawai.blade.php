@php($title = 'Manajemen Pegawai')

@extends('layouts.app', [
    'title' => $title,
    'role' => 'admin',
    'active' => 'manajemen-pegawai',
])

@section('content')
<style>
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
    
    /* Modern Input Styling */
    .modern-input {
        @apply w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-700 
               placeholder-slate-400 shadow-sm transition-all duration-200
               focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 focus:outline-none;
    }
    .modern-label {
        @apply block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2;
    }
    .custom-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        appearance: none;
        padding-right: 2.5rem;
    }
</style>

{{-- [PERBAIKAN] Menggunakan w-full h-full dan menghapus max-w/mx-auto agar full layar --}}
<div x-data="manajemenPegawaiData()" x-init="initPage()" class="w-full h-full px-6 py-6 flex flex-col relative">

    {{-- HEADER SECTION --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-5 shrink-0">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Data Master Pegawai</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data kepegawaian, jabatan, dan struktur organisasi.</p>
        </div>
        
        <div class="flex flex-wrap gap-3">
            {{-- Download Template --}}
            <a href="{{ asset('assets/template/template_import_user.csv') }}" download
                class="group flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-semibold rounded-xl shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all">
                <div class="bg-slate-100 p-1 rounded-md group-hover:bg-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                </div>
                <span>Template Excel</span>
            </a>

            {{-- Upload Button --}}
            <button @click="toggleUpload(true)"
                class="group flex items-center gap-2 px-4 py-2.5 bg-emerald-50 text-[#1C7C54] border border-emerald-100 text-sm font-bold rounded-xl hover:bg-emerald-100 hover:border-emerald-200 transition-all">
                <img src="{{ asset('assets/icon/upload-excel.svg') }}" class="h-4 w-4 opacity-80 group-hover:opacity-100 transition-opacity">
                <span>Import Excel</span>
            </button>

            {{-- Add Button --}}
            <button @click="toggleAdd(true)"
                class="flex items-center gap-2 px-5 py-2.5 bg-[#1C7C54] text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-700/20 hover:bg-[#166443] hover:shadow-emerald-700/30 transition-all transform active:scale-95">
                <i class="fas fa-plus"></i>
                <span>Tambah Pegawai</span>
            </button>
        </div>
    </div>

    {{-- SEARCH BAR (Full Width) --}}
    <div class="mb-6 relative w-full shrink-0">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i class="fas fa-search text-slate-400 text-sm"></i>
        </div>
        <input type="text" x-model="search" @input.debounce.500ms="fetchData()" 
            placeholder="Cari berdasarkan Nama atau NIP..."
            class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-700 placeholder-slate-400 shadow-sm focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 transition-all">
    </div>

    {{-- TABLE CARD (Full Height & Width) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex-1 flex flex-col relative min-h-0">
        
        {{-- Loading Overlay --}}
        <div x-show="isLoading" 
             x-transition.opacity
             class="absolute inset-0 z-20 bg-white/90 backdrop-blur-[1px] flex flex-col items-center justify-center">
            <div class="w-10 h-10 border-4 border-slate-200 border-t-[#1C7C54] rounded-full animate-spin mb-3"></div>
            <span class="text-sm font-semibold text-slate-600">Memuat data pegawai...</span>
        </div>

        <div class="overflow-x-auto custom-scrollbar flex-1">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 sticky top-0 z-10 backdrop-blur-sm shadow-sm">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-[30%]">Identitas Pegawai</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-[15%]">Username</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-[20%]">Jabatan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-[20%]">Unit Kerja</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-[15%]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <template x-for="item in items" :key="item.id">
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            
                            {{-- Nama & NIP --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-sm shrink-0">
                                        <span x-text="item.name.charAt(0)"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 text-[15px] truncate" x-text="item.name"></div>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="text-[10px] uppercase font-bold text-slate-400 border border-slate-200 rounded px-1.5 py-0.5 bg-slate-50">NIP</span>
                                            <span class="text-xs font-mono font-medium text-slate-600" x-text="item.nip"></span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Username --}}
                            <td class="px-6 py-4 align-top pt-5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                    @<span x-text="item.username"></span>
                                </span>
                            </td>

                            {{-- Jabatan --}}
                            <td class="px-6 py-4 align-top pt-5">
                                <span class="text-sm font-medium text-slate-700 block line-clamp-2" x-text="item.jabatan?.nama_jabatan || '-'"></span>
                            </td>

                            {{-- Unit Kerja --}}
                            <td class="px-6 py-4 align-top pt-5">
                                <div class="flex items-start gap-2 text-slate-600">
                                    <i class="fas fa-building text-slate-300 text-xs mt-1"></i>
                                    <span class="text-sm font-medium line-clamp-2" x-text="item.unit_kerja?.nama_unit || '-'"></span>
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 align-top pt-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button @click="openModalEdit(item)" 
                                        class="p-2 bg-white border border-slate-200 text-amber-500 rounded-lg hover:bg-amber-50 hover:border-amber-200 hover:shadow-sm transition-all"
                                        title="Edit Data">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>
                                    <button @click="deleteItem(item.id)" 
                                        class="p-2 bg-white border border-slate-200 text-red-500 rounded-lg hover:bg-red-50 hover:border-red-200 hover:shadow-sm transition-all"
                                        title="Hapus Data">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    {{-- Empty State --}}
                    <tr x-show="!isLoading && items.length === 0">
                        <td colspan="5" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <img src="{{ asset('assets/tips.svg') }}" class="h-32 w-32 opacity-50 mb-4 grayscale">
                                <p class="text-slate-500 font-medium">Tidak ada data pegawai ditemukan.</p>
                                <p class="text-slate-400 text-sm mt-1">Coba kata kunci lain atau tambahkan data baru.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- MODAL ADD/EDIT --}}
    {{-- ======================================================================= --}}
    <div x-show="openAdd || openEdit" x-cloak
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        role="dialog" aria-modal="true">
        
        <div x-show="openAdd || openEdit" 
             x-transition.opacity.duration.300ms
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
             @click="openAdd ? toggleAdd(false) : toggleEdit(false)"></div>

        <div x-show="openAdd || openEdit"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col relative z-10 overflow-hidden border border-slate-100">
            
            {{-- Header --}}
            <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-white shrink-0">
                <div>
                    <h3 class="text-xl font-bold text-slate-800" x-text="openEdit ? 'Edit Data Pegawai' : 'Tambah Pegawai Baru'"></h3>
                    <p class="text-sm text-slate-500 mt-0.5">Lengkapi informasi HR dan penempatan jabatan.</p>
                </div>
                <button @click="openAdd ? toggleAdd(false) : toggleEdit(false)"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-8 py-6 overflow-y-auto custom-scrollbar flex-1">
                <form @submit.prevent="submitForm(openEdit ? 'edit' : 'add')" id="pegawaiForm" class="space-y-6">
                    
                    {{-- Alert Info --}}
                    <div x-show="openAdd" class="p-4 rounded-xl bg-blue-50 border border-blue-100 flex gap-3 items-start">
                        <div class="text-blue-600 mt-0.5"><i class="fas fa-info-circle text-lg"></i></div>
                        <div>
                            <h4 class="text-sm font-bold text-blue-800">Informasi Akun Otomatis</h4>
                            <p class="text-sm text-blue-700 mt-1 leading-relaxed">
                                Sistem akan otomatis membuat akun login dengan <strong>Username</strong> dan <strong>Password</strong> sesuai <span class="font-mono bg-blue-100 px-1 rounded">NIP</span>.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Data Diri --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="modern-label">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" x-model="formData.name" class="modern-input" placeholder="Contoh: Budi Santoso, S.Kom">
                        </div>
                        
                        <div class="col-span-2 md:col-span-1">
                            <label class="modern-label">NIP (Nomor Induk Pegawai) <span class="text-red-500">*</span></label>
                            <input type="text" x-model="formData.nip" class="modern-input font-mono" placeholder="19xxxxxxxxxxxxxx">
                        </div>

                        <div class="col-span-2 border-t border-dashed border-slate-200 my-1"></div>

                        {{-- Unit Kerja --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="modern-label">Unit Kerja (OPD) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select x-model="formData.unit_kerja_id" class="modern-input custom-select cursor-pointer">
                                    <option value="">-- Pilih Unit Kerja --</option>
                                    <template x-for="u in unitKerjaList" :key="u.id">
                                        <option :value="u.id" x-text="u.nama_unit"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Jabatan --}}
                        <div class="col-span-2 md:col-span-1">
                            <label class="modern-label">Jabatan <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select x-model="formData.jabatan_id" class="modern-input custom-select cursor-pointer bg-slate-50" :disabled="!formData.unit_kerja_id">
                                    <option value="">-- Pilih Jabatan --</option>
                                    <template x-for="j in jabatanList" :key="j.id">
                                        <option :value="j.id" x-text="j.nama_jabatan"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Bidang --}}
                        <div class="col-span-2">
                            <label class="modern-label">Bidang / Bagian</label>
                            <div class="relative">
                                <select x-model="formData.bidang_id" class="modern-input custom-select cursor-pointer bg-slate-50" :disabled="!formData.unit_kerja_id">
                                    <option value="">-- Pilih Bidang --</option>
                                    <template x-for="b in bidangList" :key="b.id">
                                        <option :value="b.id" x-text="b.nama_bidang"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Atasan Langsung --}}
                        <div class="col-span-2 bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                            <label class="modern-label text-emerald-700">Atasan Langsung (Validator LKH)</label>
                            <div class="relative mt-2">
                                <select x-model="formData.atasan_id" class="modern-input custom-select cursor-pointer border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500/20" :disabled="isFetchingAtasan">
                                    <option value="">-- Pilih Atasan --</option>
                                    <template x-for="a in atasanList" :key="a.id">
                                        <option :value="a.id" x-text="a.name + ' (' + (a.jabatan?.nama_jabatan ?? '-') + ')'"></option>
                                    </template>
                                </select>
                                <div x-show="isFetchingAtasan" class="absolute right-10 top-3">
                                    <i class="fas fa-circle-notch fa-spin text-emerald-600"></i>
                                </div>
                            </div>
                            <p class="text-xs text-emerald-600 mt-2 font-medium">
                                <i class="fas fa-info-circle mr-1"></i> Sistem menampilkan daftar pegawai di unit kerja yang sama.
                            </p>
                        </div>

                    </div>
                </form>
            </div>

            {{-- Footer --}}
            <div class="px-8 py-5 bg-slate-50 border-t border-slate-200 flex justify-between items-center shrink-0">
                <button type="button" @click="openAdd ? toggleAdd(false) : toggleEdit(false)"
                    class="text-sm font-bold text-slate-500 hover:text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-200/50 transition-colors">
                    Batal
                </button>
                <button form="pegawaiForm" type="submit"
                    class="px-6 py-2.5 bg-[#1C7C54] text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-600/20 hover:bg-[#166443] hover:shadow-emerald-600/30 transition-all transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Simpan Data</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- MODAL UPLOAD EXCEL --}}
    {{-- ======================================================================= --}}
    <div x-show="openUpload" x-cloak
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        role="dialog" aria-modal="true">
        
        <div x-show="openUpload" 
             x-transition.opacity.duration.300ms
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
             @click="toggleUpload(false)"></div>

        <div x-show="openUpload"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="relative w-full max-w-[500px] bg-white rounded-2xl shadow-2xl p-0 overflow-hidden">
            
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-800">Import Data Pegawai</h2>
                <button @click="toggleUpload(false)" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="px-8 py-8">
                <form @submit.prevent="submitImport()" class="space-y-6">
                    <input type="file" x-ref="csvFile" @change="fileUpload = $event.target.files[0]" accept=".xlsx, .xls, .csv" hidden>

                    {{-- Dropzone Area --}}
                    <div @click="$refs.csvFile.click()" 
                         class="group w-full rounded-2xl border-2 border-dashed transition-all duration-200 px-6 py-12 flex flex-col items-center justify-center text-center cursor-pointer relative overflow-hidden"
                         :class="fileUpload ? 'border-[#1C7C54] bg-emerald-50/50' : 'border-slate-300 hover:border-[#1C7C54] hover:bg-slate-50'">
                        
                        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300"
                             :class="fileUpload ? 'bg-emerald-100 text-emerald-600' : 'text-slate-400 group-hover:text-[#1C7C54] group-hover:bg-emerald-50'">
                            <i class="fas fa-cloud-upload-alt text-3xl"></i>
                        </div>

                        <div x-show="!fileUpload">
                            <p class="text-base font-bold text-slate-700">Klik untuk upload file Excel</p>
                            <p class="text-sm text-slate-400 mt-1">Format: .xlsx, .xls, atau .csv</p>
                        </div>

                        <div x-show="fileUpload" class="z-10">
                            <p class="text-sm font-bold text-[#1C7C54] bg-white/80 px-3 py-1 rounded-full shadow-sm backdrop-blur-sm"
                               x-text="fileUpload ? fileUpload.name : ''"></p>
                            <p class="text-xs text-emerald-600 mt-2 font-medium">Klik untuk ganti file</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="toggleUpload(false)" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-[#1C7C54] text-white font-bold hover:bg-[#166443] shadow-lg shadow-emerald-600/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2"
                            :disabled="!fileUpload || isImporting">
                            <span x-show="!isImporting"><i class="fas fa-file-import mr-1"></i> Mulai Import</span>
                            <span x-show="isImporting"><i class="fas fa-circle-notch fa-spin mr-1"></i> Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection