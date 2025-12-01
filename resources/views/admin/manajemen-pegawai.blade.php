@php($title = 'Manajemen Pegawai')

@extends('layouts.app', [
    'title' => $title,
    'role' => 'admin',
    'active' => 'manajemen-pegawai',
])

@section('content')
    {{-- STYLE KHUSUS: BORDER TEBAL & VISIBLE (Agar Field Lebih Kelihatan) --}}
    <style>
        .form-input-tegas {
            @apply w-full rounded-lg border-2 border-slate-400 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 transition-all duration-200;
        }
        .form-label-tegas {
            @apply block text-sm font-bold text-slate-700 mb-1.5;
        }
    </style>

    {{-- ROOT ALPINE.JS --}}
    <div x-data="manajemenPegawaiData()" x-init="initPage()" class="flex-1 flex flex-col min-h-0 relative">

        <section class="flex-1 flex flex-col rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5 mb-0">

            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
                <div>
                    <h1 class="text-[20px] font-bold text-slate-800">Data Master Pegawai</h1>
                </div>

                <div class="flex flex-wrap items-center gap-3 justify-end">
                    {{-- Tombol Upload Excel --}}
                    <button type="button" @click="toggleUpload(true)"
                        class="inline-flex items-center gap-2 rounded-[10px] bg-[#128C60] text-white text-[14px] px-4 py-2 hover:brightness-95 transition font-medium shadow-sm">
                        <img src="{{ asset('assets/icon/upload-excel.svg') }}" class="h-4 w-4">
                        <span>Upload Excel</span>
                    </button>

                    {{-- Tombol Tambah Pegawai --}}
                    <button type="button" @click="toggleAdd(true)"
                        class="inline-flex items-center gap-2 rounded-[10px] bg-[#128C60] text-white text-[14px] px-4 py-2 hover:brightness-95 transition font-medium shadow-sm">
                        <img src="{{ asset('assets/icon/tambah-pegawai.svg') }}" class="h-4 w-4">
                        <span>Tambah Pegawai</span>
                    </button>
                </div>
            </div>

            {{-- FILTER BAR (Hamba kembalikan sesuai aslinya + Search Bar) --}}
            <div class="flex flex-col md:flex-row gap-3 mb-4">
                
                {{-- Search Input (Aktif) --}}
                <div class="w-full md:w-1/4 relative">
                    <input type="text" x-model="search" @input.debounce.500ms="fetchData()" 
                        placeholder="Cari Nama / NIP / Username..."
                        class="w-full rounded-[10px] border-2 border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[#1C7C54] focus:bg-white transition-colors font-bold text-slate-700">
                </div>

                {{-- Filter Bidang (Visual Saja Dulu) --}}
                <div class="w-full md:w-1/4 relative">
                    <select class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm appearance-none cursor-not-allowed opacity-70" disabled>
                        <option value="">Semua Bidang</option>
                    </select>
                    <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50">
                </div>

                {{-- Filter Jabatan (Visual Saja Dulu) --}}
                <div class="w-full md:w-1/4 relative">
                    <select class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm appearance-none cursor-not-allowed opacity-70" disabled>
                        <option value="">Semua Jabatan</option>
                    </select>
                    <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50">
                </div>
            </div>

            {{-- TABEL DATA --}}
            <div class="flex-1 min-h-0 overflow-x-auto relative border border-slate-200 rounded-xl">
                
                {{-- Loading State --}}
                <div x-show="isLoading" class="absolute inset-0 z-10 bg-white/80 flex items-center justify-center">
                    <span class="text-slate-600 font-bold flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-[#1C7C54]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memuat data...
                    </span>
                </div>

                <table class="min-w-full border-collapse text-[13px]">
                    <thead class="bg-slate-100 sticky top-0 z-0">
                        <tr>
                            <th class="text-left font-bold text-slate-700 py-3 px-4 border-b-2 border-slate-200">Nama & NIP</th>
                            <th class="text-left font-bold text-slate-700 py-3 px-4 border-b-2 border-slate-200">Username</th>
                            <th class="text-left font-bold text-slate-700 py-3 px-4 border-b-2 border-slate-200">Jabatan</th>
                            <th class="text-left font-bold text-slate-700 py-3 px-4 border-b-2 border-slate-200">Unit Kerja</th>
                            <th class="text-center font-bold text-slate-700 py-3 px-4 border-b-2 border-slate-200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        {{-- LOOP DATA --}}
                        <template x-for="(item, index) in items" :key="item.id">
                            <tr class="hover:bg-slate-50 transition duration-150">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800" x-text="item.name"></div>
                                    <div class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md w-fit mt-1" x-text="item.nip || '-'"></div>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-xs text-blue-600" x-text="item.username || '-'"></td>
                                <td class="py-3 px-4 font-medium text-slate-600" x-text="item.jabatan?.nama_jabatan || '-'"></td>
                                <td class="py-3 px-4 font-medium text-slate-600" x-text="item.unit_kerja?.nama_unit || '-'"></td>
                                <td class="py-3 px-4 text-center flex justify-center gap-2">
                                    <button @click="openModalEdit(item)" class="p-2 rounded-lg hover:bg-amber-50 text-amber-600 transition" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                    </button>
                                    <button @click="deleteItem(item.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-600 transition" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        
                        {{-- KOSONG --}}
                        <tr x-show="!isLoading && items.length === 0">
                            <td colspan="5" class="py-12 text-center text-slate-400 italic font-medium">
                                Belum ada data pegawai.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- MODAL TAMBAH / EDIT PEGAWAI --}}
        <div x-show="openAdd || openEdit" x-cloak 
            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto border border-slate-200" 
                 @click.away="openAdd ? toggleAdd(false) : toggleEdit(false)">
                
                {{-- Modal Header --}}
                <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white z-20">
                    <h3 class="text-xl font-bold text-slate-800" x-text="openEdit ? 'Edit Data Pegawai' : 'Tambah Pegawai Baru'"></h3>
                    <button @click="openAdd ? toggleAdd(false) : toggleEdit(false)" class="p-2 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                
                {{-- FORM INPUT --}}
                <div class="px-8 py-8">
                    <form @submit.prevent="submitForm(openEdit ? 'edit' : 'add')" id="form-pegawai" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Nama --}}
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label-tegas">Nama Lengkap</label>
                                <input type="text" name="name" x-model="formData.name" required placeholder="Nama Pegawai" 
                                    class="form-input-tegas">
                            </div>
                            
                            {{-- NIP --}}
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label-tegas">NIP</label>
                                <input type="text" name="nip" x-model="formData.nip" placeholder="Nomor Induk Pegawai" 
                                    class="form-input-tegas">
                            </div>

                            {{-- USERNAME (PENGGANTI EMAIL) --}}
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label-tegas">Username Login</label>
                                <input type="text" name="username" x-model="formData.username" required placeholder="Cth: admin.bapenda" 
                                    class="form-input-tegas bg-blue-50/50 border-blue-400 focus:border-blue-600">
                                <p class="text-xs font-medium text-blue-600 mt-1 ml-1">*Wajib diisi untuk login</p>
                            </div>

                            {{-- Password --}}
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label-tegas">Password</label>
                                <input type="text" name="password" x-model="formData.password" 
                                    :placeholder="openEdit ? 'Kosongkan jika tidak ubah' : 'Minimal 6 karakter'" 
                                    class="form-input-tegas">
                            </div>

                            <div class="col-span-2 border-t-2 border-slate-100 my-2"></div>

                            {{-- Unit Kerja --}}
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label-tegas">Unit Kerja</label>
                                <select name="unit_kerja_id" x-model="formData.unit_kerja_id" required class="form-input-tegas cursor-pointer">
                                    <option value="">-- Pilih Unit Kerja --</option>
                                    <option value="1">Badan Pendapatan Daerah</option>
                                </select>
                            </div>

                            {{-- Jabatan --}}
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label-tegas">Jabatan</label>
                                <select name="jabatan_id" x-model="formData.jabatan_id" required class="form-input-tegas cursor-pointer">
                                    <option value="">-- Pilih Jabatan --</option>
                                    <option value="1">Kepala Badan</option>
                                    <option value="3">Kepala Bidang</option>
                                    <option value="4">Kasubid</option>
                                    <option value="5">Staf Pelaksana</option>
                                </select>
                            </div>
                            
                            {{-- Bidang --}}
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label-tegas">Bidang</label>
                                <select name="bidang_id" x-model="formData.bidang_id" required class="form-input-tegas cursor-pointer">
                                    <option value="">-- Pilih Bidang --</option>
                                    <option value="1">Sekretariat</option>
                                    <option value="2">Bidang Pendataan</option>
                                    <option value="3">Bidang Penagihan</option>
                                </select>
                            </div>

                            {{-- Role --}}
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label-tegas">Role Aplikasi</label>
                                <select name="role_id" x-model="formData.role_id" required class="form-input-tegas cursor-pointer bg-amber-50/50 border-amber-400">
                                    <option value="">-- Pilih Role --</option>
                                    <option value="1">Super Admin</option>
                                    <option value="2">Kadis/Kaban</option>
                                    <option value="3">Penilai (Eselon 3/4)</option>
                                    <option value="4">Staf</option>
                                </select>
                            </div>

                        </div>

                        {{-- Footer Tombol --}}
                        <div class="mt-8 pt-4 border-t border-slate-100 flex justify-end gap-4">
                            <button type="button" @click="openAdd ? toggleAdd(false) : toggleEdit(false)" 
                                class="px-6 py-2.5 rounded-lg border-2 border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition">
                                Batal
                            </button>
                            <button type="submit" 
                                class="px-6 py-2.5 rounded-lg bg-[#128C60] text-white font-bold hover:bg-emerald-700 hover:shadow-lg transition transform hover:-translate-y-0.5">
                                <span x-text="openEdit ? 'Simpan Perubahan' : 'Simpan Data'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL UPLOAD EXCEL (Hamba Kembalikan) --}}
        <div x-show="openUpload" x-cloak class="fixed inset-0 z-[65] flex items-center justify-center bg-black/60 px-4 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="relative w-full max-w-[520px] bg-white rounded-[15px] shadow-xl px-6 md:px-8 py-6 md:py-7" @click.away="toggleUpload(false)">

                <button type="button" @click="toggleUpload(false)"
                    class="absolute right-6 top-5 text-slate-400 hover:text-slate-600 text-xl leading-none">
                    &times;
                </button>

                <h2 class="text-[18px] md:text-[20px] font-bold text-slate-800 mb-4">
                    Upload Excel
                </h2>

                <form action="#" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <label class="block">
                        <div class="w-full rounded-[20px] border-2 border-dashed border-slate-300 bg-slate-50/60
                                    px-6 py-10 flex flex-col items-center justify-center text-center cursor-pointer
                                    hover:border-[#1C7C54] hover:bg-emerald-50/40 transition">
                            <img src="{{ asset('assets/icon/upload-excel.svg') }}" alt="Upload" class="h-10 w-10 mb-3 opacity-70">
                            <p class="flex items-center justify-center gap-2 text-[14px] text-[#9CA3AF] mb-1 font-medium">
                                <span>Upload File Excel (.xls, .xlsx)</span>
                            </p>
                            <p class="text-[12px] text-slate-400">Klik disini atau seret file ke area ini</p>
                        </div>
                        <input type="file" name="file_excel" accept=".xls,.xlsx" class="hidden">
                    </label>

                    <div class="pt-1 flex flex-wrap justify-end gap-3">
                        <button type="button" @click="toggleUpload(false)" class="inline-flex items-center justify-center rounded-[8px] bg-[#B6241C] px-6 py-2 text-[14px] text-white font-bold hover:brightness-95 transition">
                            Batalkan
                        </button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-[8px] bg-[#0E7A4A] px-6 py-2 text-[14px] text-white font-bold hover:brightness-95 transition">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
        {{-- END MODAL UPLOAD EXCEL --}}

    </div>
@endsection