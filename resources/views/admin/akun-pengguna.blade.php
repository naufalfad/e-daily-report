@php($title = 'Akun Pengguna')

@extends('layouts.app', [
    'title' => $title,
    'role' => 'admin',
    'active' => 'akun-pengguna',
])

@section('content')
    <style>
        .form-input-tegas { @apply w-full rounded-lg border-2 border-slate-400 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 transition-all; }
        .form-label-tegas { @apply block text-sm font-bold text-slate-700 mb-1.5; }
    </style>

    {{-- Script JS diganti ke file akun-pengguna.js --}}
    <div x-data="akunPenggunaData()" x-init="initPage()" class="flex-1 flex flex-col min-h-0 relative">
        
        <section class="flex-1 flex flex-col rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5 mb-0">
            
            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
                <h1 class="text-[20px] font-bold text-slate-800">Manajemen Akses & Kredensial</h1>
            </div>

            {{-- Filter Search --}}
            <div class="flex gap-3 mb-4">
                <div class="w-full md:w-1/3">
                    <input type="text" x-model="search" @input.debounce.500ms="fetchData()" placeholder="Cari Nama / Username..." class="w-full rounded-[10px] border-2 border-slate-300 bg-slate-50 px-4 py-2.5 text-sm focus:border-[#1C7C54] font-bold">
                </div>
            </div>

            {{-- Table --}}
            <div class="flex-1 overflow-x-auto border border-slate-200 rounded-xl relative">
                <div x-show="isLoading" class="absolute inset-0 z-10 bg-white/80 flex items-center justify-center"><span class="font-bold text-slate-600 animate-pulse">Memuat data...</span></div>
                <table class="min-w-full text-[13px]">
                    <thead class="bg-slate-100 border-b-2 border-slate-200">
                        <tr>
                            <th class="text-left font-bold text-slate-700 py-3 px-4">Nama Pegawai & Unit</th>
                            <th class="text-left font-bold text-slate-700 py-3 px-4">Username</th>
                            <th class="text-left font-bold text-slate-700 py-3 px-4">Role Saat Ini</th>
                            <th class="text-center font-bold text-slate-700 py-3 px-4">Status</th>
                            <th class="text-center font-bold text-slate-700 py-3 px-4">Aksi Keamanan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800" x-text="item.name"></div>
                                    <div class="text-xs font-bold text-slate-500 mt-1" x-text="item.unit_kerja?.nama_unit || '-'"></div>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-blue-600" x-text="item.username"></td>
                                <td class="py-3 px-4">
                                    <span :class="{'bg-red-100 text-red-700': item.roles[0]?.nama_role === 'Super Admin', 'bg-emerald-100 text-emerald-700': item.roles[0]?.nama_role === 'Kadis', 'bg-yellow-100 text-yellow-700': item.roles[0]?.nama_role === 'Penilai', 'bg-slate-100 text-slate-700': item.roles[0]?.nama_role === 'Staf'}"
                                        class="px-2 py-0.5 rounded-full text-xs font-bold"
                                        x-text="item.roles[0]?.nama_role || 'No Role'">
                                    </span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                        class="px-2 py-0.5 rounded text-xs font-bold"
                                        x-text="item.is_active ? 'Aktif' : 'Suspend'">
                                    </span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <div class="flex flex-col gap-2 items-center">
                                        {{-- 1. TOMBOL CREDENTIALS --}}
                                        <button @click="openModalCred(item)" class="text-xs px-3 py-1.5 w-40 rounded-lg bg-blue-500 text-white font-bold hover:bg-blue-600 transition flex items-center justify-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                                            Reset Kredensial
                                        </button>
                                        
                                        {{-- 2. TOMBOL ROLE --}}
                                        <button @click="openModalRole(item)" class="text-xs px-3 py-1.5 w-40 rounded-lg bg-amber-500 text-white font-bold hover:bg-amber-600 transition flex items-center justify-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                            Ganti Role
                                        </button>

                                        {{-- 3. TOMBOL SUSPEND/ACTIVATE --}}
                                        <button @click="toggleStatus(item)" :class="item.is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'" 
                                            class="text-xs px-3 py-1.5 w-40 rounded-lg text-white font-bold transition flex items-center justify-center gap-1">
                                            <svg x-show="item.is_active" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                            <svg x-show="!item.is_active" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M12 2v2"></path><path d="M12 17v-6"></path><path d="M12 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                            <span x-text="item.is_active ? 'Suspend Akun' : 'Aktifkan Akun'"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- MODAL 1: EDIT CREDENTIALS (Username & Password) --}}
        <div x-show="openCred" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4" x-transition.opacity>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-slate-200" @click.away="toggleCred(false)">
                <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white z-20">
                    <h3 class="text-xl font-bold text-slate-800">Edit Kredensial Akun (<span x-text="targetName"></span>)</h3>
                    <button @click="toggleCred(false)" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
                </div>
                <div class="px-8 py-8">
                    <form @submit.prevent="submitCredentialUpdate()" class="space-y-6">
                        <p class="text-sm text-slate-500">Gunakan form ini hanya untuk **Reset Password** atau **Mengganti Username**.</p>

                        <div><label class="form-label-tegas text-blue-700">Username Baru</label><input type="text" x-model="formData.username" class="form-input-tegas bg-blue-50 border-blue-400" placeholder="Username Login"></div>
                        
                        <div class="border-t pt-4">
                            <label class="form-label-tegas text-red-700">Password Baru</label>
                            <input type="password" x-model="formData.password" class="form-input-tegas border-red-400" placeholder="Kosongkan jika tidak diubah">
                            <p class="text-xs text-red-500 mt-1">Isi field ini dan konfirmasi di bawah untuk reset password.</p>
                        </div>
                        
                        <div>
                            <label class="form-label-tegas text-red-700">Konfirmasi Password</label>
                            <input type="password" x-model="formData.password_confirmation" class="form-input-tegas border-red-400" placeholder="Ulangi Password Baru">
                        </div>

                        <div class="mt-8 pt-4 border-t border-slate-100 flex justify-end gap-4">
                            <button type="button" @click="toggleCred(false)" class="px-6 py-2.5 rounded-lg border-2 border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition">Batal</button>
                            <button type="submit" class="px-6 py-2.5 rounded-lg bg-blue-600 text-white font-bold hover:bg-blue-700 transition">Update Kredensial</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        {{-- MODAL 2: EDIT ROLE (Hak Akses) --}}
        <div x-show="openRole" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4" x-transition.opacity>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm max-h-[90vh] overflow-y-auto border border-slate-200" @click.away="toggleRole(false)">
                <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white z-20">
                    <h3 class="text-xl font-bold text-slate-800">Ganti Role (<span x-text="targetName"></span>)</h3>
                    <button @click="toggleRole(false)" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
                </div>
                <div class="px-8 py-8">
                    <form @submit.prevent="submitRoleUpdate()" class="space-y-6">
                        <p class="text-sm text-slate-500">Memindahkan role akan mengubah hak akses pengguna di seluruh aplikasi.</p>
                        
                        <div>
                            <label class="form-label-tegas text-amber-700">Role Baru</label>
                            <select x-model="formData.role_id" class="form-input-tegas bg-amber-50 border-amber-400 cursor-pointer">
                                <option value="">-- Pilih Role --</option>
                                <template x-for="r in roleList" :key="r.id">
                                    <option :value="r.id" x-text="r.nama_role"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div class="mt-8 pt-4 border-t border-slate-100 flex justify-end gap-4">
                            <button type="button" @click="toggleRole(false)" class="px-6 py-2.5 rounded-lg border-2 border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition">Batal</button>
                            <button type="submit" class="px-6 py-2.5 rounded-lg bg-amber-600 text-white font-bold hover:bg-amber-700 transition">Simpan Role Baru</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection