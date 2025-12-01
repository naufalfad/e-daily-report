

@php($title = 'Manajemen Pegawai')

@extends('layouts.app', [
    'title' => $title,
    'role' => 'admin',
    'active' => 'manajemen-pegawai',
])

@section('content')
    <style>
        .form-input-tegas { @apply w-full rounded-lg border-2 border-slate-400 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 transition-all; }
        .form-label-tegas { @apply block text-sm font-bold text-slate-700 mb-1.5; }
    </style>

    <div x-data="manajemenPegawaiData()" x-init="initPage()" class="flex-1 flex flex-col min-h-0 relative">
        
        <section class="flex-1 flex flex-col rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5 mb-0">
            {{-- Header & Buttons --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
                <h1 class="text-[20px] font-bold text-slate-800">Data Master Pegawai</h1>
                <div class="flex gap-3">
                    <button @click="toggleUpload(true)" class="bg-[#128C60] text-white px-4 py-2 rounded-[10px] text-sm font-medium hover:brightness-95 flex items-center gap-2">
                        <img src="{{ asset('assets/icon/upload-excel.svg') }}" class="h-4 w-4"> Upload Excel
                    </button>
                    <button @click="toggleAdd(true)" class="bg-[#128C60] text-white px-4 py-2 rounded-[10px] text-sm font-medium hover:brightness-95 flex items-center gap-2">
                        <img src="{{ asset('assets/icon/tambah-pegawai.svg') }}" class="h-4 w-4"> Tambah Pegawai
                    </button>
                </div>
            </div>

            {{-- Filter Search --}}
            <div class="flex gap-3 mb-4">
                <div class="w-full md:w-1/3">
                    <input type="text" x-model="search" @input.debounce.500ms="fetchData()" placeholder="Cari Nama / NIP / Username..." class="w-full rounded-[10px] border-2 border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[#1C7C54] font-bold">
                </div>
            </div>

            {{-- Table --}}
            <div class="flex-1 overflow-x-auto border border-slate-200 rounded-xl relative">
                <div x-show="isLoading" class="absolute inset-0 z-10 bg-white/80 flex items-center justify-center"><span class="font-bold text-slate-600 animate-pulse">Memuat data...</span></div>
                <table class="min-w-full text-[13px]">
                    <thead class="bg-slate-100 border-b-2 border-slate-200">
                        <tr>
                            <th class="text-left font-bold text-slate-700 py-3 px-4">Nama & NIP</th>
                            <th class="text-left font-bold text-slate-700 py-3 px-4">Username</th>
                            <th class="text-left font-bold text-slate-700 py-3 px-4">Jabatan</th>
                            <th class="text-left font-bold text-slate-700 py-3 px-4">Unit Kerja</th>
                            <th class="text-center font-bold text-slate-700 py-3 px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800" x-text="item.name"></div>
                                    <div class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded mt-1 w-fit" x-text="item.nip"></div>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-blue-600" x-text="item.username"></td>
                                <td class="py-3 px-4 font-medium text-slate-600" x-text="item.jabatan?.nama_jabatan || '-'"></td>
                                <td class="py-3 px-4 font-medium text-slate-600" x-text="item.unit_kerja?.nama_unit || '-'"></td>
                                <td class="text-center py-3 px-4 flex justify-center gap-2">
                                    <button @click="openModalEdit(item)" class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg"><svg xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></button>
                                    <button @click="deleteItem(item.id)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg"><svg xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- MODAL FORM (ADD/EDIT) --}}
        <div x-show="openAdd || openEdit" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4" x-transition.opacity>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto border border-slate-200" @click.away="openAdd ? toggleAdd(false) : toggleEdit(false)">
                <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white z-20">
                    <h3 class="text-xl font-bold text-slate-800" x-text="openEdit ? 'Edit Pegawai' : 'Tambah Pegawai'"></h3>
                    <button @click="openAdd ? toggleAdd(false) : toggleEdit(false)" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
                </div>
                <div class="px-8 py-8">
                    <form @submit.prevent="submitForm(openEdit ? 'edit' : 'add')" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><label class="form-label-tegas">Nama Lengkap</label><input type="text" x-model="formData.name" class="form-input-tegas" placeholder="Nama Pegawai"></div>
                            <div><label class="form-label-tegas">NIP</label><input type="text" x-model="formData.nip" class="form-input-tegas" placeholder="NIP"></div>
                            <div><label class="form-label-tegas text-blue-700">Username</label><input type="text" x-model="formData.username" class="form-input-tegas bg-blue-50 border-blue-400" placeholder="Username Login"></div>
                            <div><label class="form-label-tegas">Password</label><input type="text" x-model="formData.password" :placeholder="openEdit ? 'Kosongkan jika tetap' : 'Password'" class="form-input-tegas"></div>
                            
                            <div class="col-span-2 border-t my-2"></div>

                            {{-- UNIT KERJA (DINAMIS) --}}
                            <div>
                                <label class="form-label-tegas">Unit Kerja</label>
                                <select x-model="formData.unit_kerja_id" class="form-input-tegas cursor-pointer">
                                    <option value="">-- Pilih Unit --</option>
                                    <template x-for="u in unitKerjaList" :key="u.id">
                                        <option :value="u.id" x-text="u.nama_unit"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- JABATAN (DINAMIS) --}}
                            <div>
                                <label class="form-label-tegas">Jabatan</label>
                                <select x-model="formData.jabatan_id" class="form-input-tegas cursor-pointer">
                                    <option value="">-- Pilih Jabatan --</option>
                                    <template x-for="j in jabatanList" :key="j.id">
                                        <option :value="j.id" x-text="j.nama_jabatan"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- BIDANG (DINAMIS - FILTERED BY UNIT) --}}
                            <div>
                                <label class="form-label-tegas">Bidang</label>
                                <select x-model="formData.bidang_id" class="form-input-tegas cursor-pointer" :disabled="!formData.unit_kerja_id">
                                    <option value="">-- Pilih Bidang --</option>
                                    <template x-for="b in bidangList" :key="b.id">
                                        <option :value="b.id" x-text="b.nama_bidang"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- ATASAN (DINAMIS - SMART FILTER) --}}
                            <div>
                                <label class="form-label-tegas text-emerald-700">Atasan Langsung</label>
                                <div class="relative">
                                    <select x-model="formData.atasan_id" class="form-input-tegas cursor-pointer bg-emerald-50 border-emerald-400" :disabled="isFetchingAtasan">
                                        <option value="">-- Pilih Atasan --</option>
                                        <template x-for="a in atasanList" :key="a.id">
                                            <option :value="a.id" x-text="a.name"></option>
                                        </template>
                                    </select>
                                    <div x-show="isFetchingAtasan" class="absolute right-3 top-3"><svg class="animate-spin h-5 w-5 text-emerald-600" xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>
                                </div>
                                <p class="text-xs text-emerald-600 mt-1 font-medium" x-show="atasanList.length > 0">Sistem merekomendasikan atasan sesuai struktur.</p>
                            </div>

                            {{-- ROLE (DINAMIS) --}}
                            <div class="col-span-2 md:col-span-1">
                                <label class="form-label-tegas text-amber-700">Role Aplikasi</label>
                                <select x-model="formData.role_id" class="form-input-tegas bg-amber-50 border-amber-400">
                                    <option value="">-- Pilih Role --</option>
                                    <template x-for="r in roleList" :key="r.id">
                                        <option :value="r.id" x-text="r.nama_role"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div class="mt-8 pt-4 border-t border-slate-100 flex justify-end gap-4">
                            <button type="button" @click="openAdd ? toggleAdd(false) : toggleEdit(false)" class="px-6 py-2.5 rounded-lg border-2 border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition">Batal</button>
                            <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#128C60] text-white font-bold hover:bg-emerald-700 hover:shadow-lg transition transform hover:-translate-y-0.5">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL UPLOAD EXCEL (TETAP ADA) --}}
        <div x-show="openUpload" x-cloak class="fixed inset-0 z-[65] flex items-center justify-center bg-black/60 px-4 backdrop-blur-sm" x-transition.opacity>
            <div class="relative w-full max-w-[520px] bg-white rounded-[15px] shadow-xl px-6 md:px-8 py-6 md:py-7" @click.away="toggleUpload(false)">
                <button @click="toggleUpload(false)" class="absolute right-6 top-5 text-slate-400 hover:text-slate-600 text-xl">&times;</button>
                <h2 class="text-[18px] md:text-[20px] font-bold text-slate-800 mb-4">Upload Excel</h2>
                <form class="space-y-5">
                    <div class="w-full rounded-[20px] border-2 border-dashed border-slate-300 bg-slate-50/60 px-6 py-10 flex flex-col items-center justify-center text-center cursor-pointer hover:border-[#1C7C54]">
                        <img src="{{ asset('assets/icon/upload-excel.svg') }}" class="h-10 w-10 mb-3 opacity-70">
                        <p class="text-sm text-slate-500 font-bold">Upload File Excel (.xls, .xlsx)</p>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="toggleUpload(false)" class="px-6 py-2 rounded-lg bg-[#B6241C] text-white font-bold hover:brightness-95">Batalkan</button>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-[#0E7A4A] text-white font-bold hover:brightness-95">Upload</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection