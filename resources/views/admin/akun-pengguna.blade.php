@php($title = 'Akun Pengguna')

@extends('layouts.app', [
    'title' => $title,
    'role' => 'admin',
    'active' => 'akun-pengguna',
])

@section('content')
    <style>
        [x-cloak] { display: none !important; }
        .form-input-tegas { @apply w-full rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none; }
        .form-label-tegas { @apply block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2; }
    </style>

    {{-- 
        [DATA INJECTION] 
        Menyuntikkan data Role ke window object agar bisa diakses Alpine.js 
        tanpa perlu fetch API tambahan untuk dropdown role.
    --}}
    <script>
        window.Laravel = window.Laravel || {};
        window.Laravel.roles = @json($roles);
    </script>

    <div x-data="akunPenggunaData()" x-init="initPage()" class="flex-1 flex flex-col min-h-0 relative w-full h-full px-6 py-6">
        
        <section class="flex-1 flex flex-col rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
            
            {{-- Header & Toolbar --}}
            <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Manajemen Akses & Kredensial</h1>
                    <p class="text-sm text-slate-500 mt-1">Kontrol keamanan, reset password, dan hak akses pengguna.</p>
                </div>

                <div class="w-full md:w-72 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400"></i>
                    </div>
                    <input type="text" x-model="search" @input.debounce.500ms="fetchData()" 
                        placeholder="Cari Username / Nama..." 
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold text-slate-700 focus:bg-white focus:border-blue-500 transition-all outline-none">
                </div>
            </div>

            {{-- Table Container --}}
            <div class="flex-1 overflow-x-auto relative">
                
                {{-- Loading State --}}
                <div x-show="isLoading" class="absolute inset-0 z-10 bg-white/80 backdrop-blur-[1px] flex flex-col items-center justify-center transition-opacity">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="font-bold text-slate-600 text-sm">Memuat data akun...</span>
                </div>

                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200 sticky top-0 z-10">
                        <tr>
                            <th class="py-3 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider w-[30%]">Identitas Pegawai</th>
                            <th class="py-3 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider w-[20%]">Username Login</th>
                            <th class="py-3 px-6 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-[15%]">Role</th>
                            <th class="py-3 px-6 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-[10%]">Status</th>
                            <th class="py-3 px-6 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-[25%]">Aksi Keamanan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                
                                {{-- Kolom 1: Identitas --}}
                                <td class="py-4 px-6 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm shrink-0">
                                            <span x-text="item.name.charAt(0)"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-800 text-sm truncate" x-text="item.name"></div>
                                            <div class="text-xs text-slate-500 truncate" x-text="item.unit_kerja?.nama_unit || '-'"></div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kolom 2: Username --}}
                                <td class="py-4 px-6 align-middle">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-user-shield text-slate-300 text-xs"></i>
                                        <span class="font-mono text-sm font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded" x-text="item.username"></span>
                                    </div>
                                </td>

                                {{-- Kolom 3: Role --}}
                                <td class="py-4 px-6 align-middle text-center">
                                    <div class="flex justify-center">
                                        <span :class="{
                                            'bg-purple-100 text-purple-700 border-purple-200': item.roles[0]?.nama_role === 'Admin',
                                            'bg-blue-100 text-blue-700 border-blue-200': item.roles[0]?.nama_role === 'Kadis' || item.roles[0]?.nama_role === 'Penilai',
                                            'bg-slate-100 text-slate-600 border-slate-200': item.roles[0]?.nama_role === 'Staf'
                                        }"
                                        class="px-2.5 py-1 rounded-md text-xs font-bold border uppercase tracking-wide"
                                        x-text="item.roles[0]?.nama_role || 'No Role'">
                                        </span>
                                    </div>
                                </td>

                                {{-- Kolom 4: Status --}}
                                <td class="py-4 px-6 align-middle text-center">
                                    <span :class="item.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                                          class="px-2.5 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="item.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                        <span x-text="item.is_active ? 'Aktif' : 'Suspend'"></span>
                                    </span>
                                </td>

                                {{-- Kolom 5: Aksi --}}
                                <td class="py-4 px-6 align-middle text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        
                                        {{-- Reset Password --}}
                                        <button @click="openModalCred(item)" 
                                            class="p-2 bg-white border border-slate-200 text-blue-600 rounded-lg hover:bg-blue-50 hover:border-blue-200 transition-all shadow-sm"
                                            title="Reset Password / Username">
                                            <i class="fas fa-key"></i>
                                        </button>

                                        {{-- Ganti Role --}}
                                        <button @click="openModalRole(item)" 
                                            class="p-2 bg-white border border-slate-200 text-amber-500 rounded-lg hover:bg-amber-50 hover:border-amber-200 transition-all shadow-sm"
                                            title="Ubah Hak Akses">
                                            <i class="fas fa-user-tag"></i>
                                        </button>

                                        {{-- Toggle Status --}}
                                        <button @click="toggleStatus(item)" 
                                            :class="item.is_active ? 'text-red-500 hover:bg-red-50 hover:border-red-200' : 'text-emerald-500 hover:bg-emerald-50 hover:border-emerald-200'"
                                            class="p-2 bg-white border border-slate-200 rounded-lg transition-all shadow-sm"
                                            :title="item.is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun'">
                                            <i class="fas" :class="item.is_active ? 'fa-ban' : 'fa-check-circle'"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        </template>

                        {{-- Empty State --}}
                        <tr x-show="!isLoading && items.length === 0">
                            <td colspan="5" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center opacity-60">
                                    <i class="fas fa-users-slash text-4xl text-slate-300 mb-3"></i>
                                    <p class="text-slate-500 font-medium">Tidak ada akun yang cocok.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination Controls --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500" id="pagination-info">...</span>
                <div class="flex gap-2">
                    <button id="prev-page" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-600 text-xs font-bold hover:bg-slate-100 disabled:opacity-50 transition">Prev</button>
                    <div id="pagination-numbers" class="flex gap-1"></div>
                    <button id="next-page" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-600 text-xs font-bold hover:bg-slate-100 disabled:opacity-50 transition">Next</button>
                </div>
            </div>

        </section>

        {{-- ======================================================================= --}}
        {{-- MODAL 1: EDIT CREDENTIALS (Username & Password) --}}
        {{-- ======================================================================= --}}
        <div x-show="openCred" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div x-show="openCred" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="toggleCred(false)"></div>

            {{-- Panel --}}
            <div x-show="openCred" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-slate-100 relative z-10 overflow-hidden">
                
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800">Reset Kredensial</h3>
                    <button @click="toggleCred(false)" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
                </div>

                <div class="px-6 py-6">
                    <form @submit.prevent="submitCredentialUpdate()" class="space-y-5">
                        <div class="p-3 bg-blue-50 rounded-lg border border-blue-100 flex gap-3">
                            <div class="shrink-0 text-blue-600 mt-0.5"><i class="fas fa-user-lock"></i></div>
                            <div>
                                <p class="text-xs font-bold text-blue-800">Akun Target:</p>
                                <p class="text-sm text-blue-600 font-medium" x-text="targetName"></p>
                            </div>
                        </div>

                        <div>
                            <label class="form-label-tegas">Username Login</label>
                            <input type="text" x-model="formData.username" class="form-input-tegas bg-slate-50" placeholder="Username">
                        </div>

                        <div class="border-t border-dashed border-slate-200 pt-4">
                            <label class="form-label-tegas text-red-600">Password Baru</label>
                            <input type="password" x-model="formData.password" class="form-input-tegas border-red-200 focus:border-red-500 focus:ring-red-500/20" placeholder="Kosongkan jika tidak ingin mengubah password">
                        </div>

                        <div>
                            <label class="form-label-tegas text-red-600">Konfirmasi Password</label>
                            <input type="password" x-model="formData.password_confirmation" class="form-input-tegas border-red-200 focus:border-red-500 focus:ring-red-500/20" placeholder="Ulangi password baru">
                        </div>

                        <div class="pt-2 flex justify-end gap-3">
                            <button type="button" @click="toggleCred(false)" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50">Batal</button>
                            <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white font-bold text-sm hover:bg-blue-700 shadow-lg shadow-blue-600/20">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ======================================================================= --}}
        {{-- MODAL 2: EDIT ROLE (Hak Akses) --}}
        {{-- ======================================================================= --}}
        <div x-show="openRole" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div x-show="openRole" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="toggleRole(false)"></div>

            <div x-show="openRole" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-sm border border-slate-100 relative z-10 overflow-hidden">
                
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800">Ubah Hak Akses</h3>
                    <button @click="toggleRole(false)" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
                </div>

                <div class="px-6 py-6">
                    <form @submit.prevent="submitRoleUpdate()" class="space-y-6">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <p class="text-sm text-slate-500 px-4">Anda sedang mengubah hak akses untuk <strong class="text-slate-800" x-text="targetName"></strong>.</p>
                        </div>

                        <div>
                            <label class="form-label-tegas text-center">Pilih Role Baru</label>
                            <select x-model="formData.role_id" class="form-input-tegas text-center cursor-pointer">
                                <option value="">-- Pilih Role --</option>
                                {{-- Loop Data Role dari Window Object --}}
                                <template x-for="r in roleList" :key="r.id">
                                    <option :value="r.id" x-text="r.nama_role"></option>
                                </template>
                            </select>
                        </div>

                        <div class="flex justify-center gap-3">
                            <button type="button" @click="toggleRole(false)" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50">Batal</button>
                            <button type="submit" class="px-5 py-2 rounded-lg bg-amber-500 text-white font-bold text-sm hover:bg-amber-600 shadow-lg shadow-amber-500/20">Simpan Role</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    @vite(['resources/js/pages/admin/akun-pengguna.js'])
@endpush