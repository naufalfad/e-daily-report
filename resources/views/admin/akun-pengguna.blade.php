@php($title = 'Akun Pengguna')

@extends('layouts.app', [
    'title' => $title,
    'role' => 'admin',
    'active' => 'akun-pengguna',
])

@section('content')
    <style>
        [x-cloak] { display: none !important; }
        .form-input-tegas { 
            @apply w-full rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none; 
        }
        .form-label-tegas { 
            @apply block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2; 
        }
        /* Custom Scrollbar for Table */
        .table-container::-webkit-scrollbar { height: 8px; }
        .table-container::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .table-container::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    {{-- 
        [DATA INJECTION] 
        Menyuntikkan data Role ke window object agar bisa diakses Alpine.js 
        tanpa perlu fetch API tambahan untuk dropdown role.
    --}}
    <script>
        window.Laravel = window.Laravel || {};
        window.Laravel.roles = @json($roles ?? []);
    </script>

    <div x-data="akunPenggunaData()" x-init="initPage()" class="flex-1 flex flex-col min-h-0 relative w-full h-full px-4 sm:px-6 py-6">
        
        <section class="flex-1 flex flex-col rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
            
            {{-- Header & Toolbar --}}
            <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white relative z-20">
                <div>
                    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Manajemen Akses & Kredensial</h1>
                    <p class="text-sm text-slate-500 mt-1">Kontrol keamanan, reset password, dan hak akses pengguna sistem.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                    {{-- Filter Role (Opsional, jika didukung oleh state Alpine Anda) --}}
                    <select x-model="filterRole" @change="fetchData(1)" class="w-full sm:w-40 form-input-tegas !py-2 !text-xs cursor-pointer bg-slate-50">
                        <option value="">Semua Role</option>
                        <template x-for="r in roleList" :key="r.id">
                            <option :value="r.id" x-text="r.nama_role"></option>
                        </template>
                    </select>

                    {{-- Search Input --}}
                    <div class="w-full sm:w-72 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400"></i>
                        </div>
                        <input type="text" x-model="search" @input.debounce.500ms="fetchData(1)" 
                            placeholder="Cari Username / Nama..." 
                            class="w-full pl-10 pr-4 py-2 rounded-xl border-2 border-slate-200 bg-slate-50 text-sm font-bold text-slate-700 focus:bg-white focus:border-blue-500 transition-all outline-none shadow-inner">
                    </div>
                </div>
            </div>

            {{-- Table Container --}}
            <div class="flex-1 overflow-x-auto relative table-container bg-slate-50/30">
                
                {{-- Loading State --}}
                <div x-show="isLoading" x-transition.opacity class="absolute inset-0 z-10 bg-white/80 backdrop-blur-[1px] flex flex-col items-center justify-center">
                    <svg class="animate-spin h-10 w-10 text-blue-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="font-bold text-slate-600 text-sm">Sinkronisasi data kredensial...</span>
                </div>

                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead class="bg-white border-b border-slate-200 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="py-3 px-6 text-xs font-extrabold text-slate-500 uppercase tracking-wider w-[30%]">Identitas Pegawai</th>
                            <th class="py-3 px-6 text-xs font-extrabold text-slate-500 uppercase tracking-wider w-[20%]">Username Login</th>
                            <th class="py-3 px-6 text-center text-xs font-extrabold text-slate-500 uppercase tracking-wider w-[15%]">Role</th>
                            <th class="py-3 px-6 text-center text-xs font-extrabold text-slate-500 uppercase tracking-wider w-[10%]">Status</th>
                            <th class="py-3 px-6 text-center text-xs font-extrabold text-slate-500 uppercase tracking-wider w-[25%]">Aksi Keamanan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-slate-50 transition-colors group">
                                
                                {{-- Kolom 1: Identitas --}}
                                <td class="py-4 px-6 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 text-blue-700 flex items-center justify-center font-black text-sm shrink-0 shadow-sm border border-blue-200">
                                            <span x-text="item.name.charAt(0).toUpperCase()"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-800 text-sm truncate" x-text="item.name"></div>
                                            <div class="text-xs font-medium text-slate-500 truncate mt-0.5" x-text="item.unit_kerja?.nama_unit || 'Tanpa Unit Kerja'"></div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kolom 2: Username --}}
                                <td class="py-4 px-6 align-middle">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-6 h-6 rounded bg-slate-100 flex items-center justify-center text-slate-400">
                                            <i class="fas fa-user-shield text-[10px]"></i>
                                        </div>
                                        <span class="font-mono text-sm font-bold text-slate-700 tracking-tight" x-text="item.username || '-'"></span>
                                    </div>
                                </td>

                                {{-- Kolom 3: Role --}}
                                <td class="py-4 px-6 align-middle text-center">
                                    <div class="flex justify-center">
                                        <span :class="{
                                            'bg-purple-50 text-purple-700 ring-purple-200': item.roles[0]?.nama_role === 'Admin',
                                            'bg-blue-50 text-blue-700 ring-blue-200': item.roles[0]?.nama_role === 'Kadis' || item.roles[0]?.nama_role === 'Penilai',
                                            'bg-slate-50 text-slate-600 ring-slate-200': item.roles[0]?.nama_role === 'Staf'
                                        }"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-bold ring-1 ring-inset uppercase tracking-wider shadow-sm"
                                        x-text="item.roles[0]?.nama_role || 'No Role'">
                                        </span>
                                    </div>
                                </td>

                                {{-- Kolom 4: Status --}}
                                <td class="py-4 px-6 align-middle text-center">
                                    <span :class="item.is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-red-50 text-red-700 ring-red-200'"
                                          class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 ring-1 ring-inset shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="item.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'"></span>
                                        <span x-text="item.is_active ? 'Aktif' : 'Suspend'"></span>
                                    </span>
                                </td>

                                {{-- Kolom 5: Aksi --}}
                                <td class="py-4 px-6 align-middle text-center">
                                    <div class="flex items-center justify-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        
                                        {{-- Reset Password --}}
                                        <button @click="openModalCred(item)" 
                                            class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 text-blue-600 rounded-lg hover:bg-blue-50 hover:border-blue-200 transition-all shadow-sm focus:ring-2 focus:ring-blue-500/20"
                                            title="Reset Password / Username">
                                            <i class="fas fa-key"></i>
                                        </button>

                                        {{-- Ganti Role --}}
                                        <button @click="openModalRole(item)" 
                                            class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 text-amber-500 rounded-lg hover:bg-amber-50 hover:border-amber-200 transition-all shadow-sm focus:ring-2 focus:ring-amber-500/20"
                                            title="Ubah Hak Akses">
                                            <i class="fas fa-user-tag"></i>
                                        </button>

                                        {{-- Toggle Status --}}
                                        <button @click="toggleStatus(item)" 
                                            :class="item.is_active ? 'text-red-500 hover:bg-red-50 hover:border-red-200' : 'text-emerald-500 hover:bg-emerald-50 hover:border-emerald-200'"
                                            class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-lg transition-all shadow-sm focus:ring-2 focus:ring-slate-500/20"
                                            :title="item.is_active ? 'Nonaktifkan Akun (Suspend)' : 'Aktifkan Akun'">
                                            <i class="fas" :class="item.is_active ? 'fa-user-slash' : 'fa-user-check'"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        </template>

                        {{-- Empty State --}}
                        <tr x-show="!isLoading && items.length === 0" x-cloak>
                            <td colspan="5" class="py-16 text-center bg-slate-50/50">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 mb-4 bg-slate-100 rounded-full flex items-center justify-center text-slate-300">
                                        <i class="fas fa-user-shield text-2xl"></i>
                                    </div>
                                    <p class="text-slate-600 font-bold text-base">Tidak ada akun yang ditemukan.</p>
                                    <p class="text-slate-400 text-sm mt-1">Coba gunakan kata kunci pencarian lain.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination Controls (Dihibridasi untuk kompatibilitas script existing) --}}
            <div class="px-6 py-4 bg-white border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 z-20">
                <span class="text-xs font-bold text-slate-500 bg-slate-50 px-3 py-1.5 rounded-md border border-slate-100" id="pagination-info">Menunggu data...</span>
                <div class="flex gap-1.5">
                    <button id="prev-page" class="px-3.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 text-xs font-bold hover:bg-slate-50 hover:text-blue-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm flex items-center gap-1">
                        <i class="fas fa-chevron-left text-[10px]"></i> Prev
                    </button>
                    <div id="pagination-numbers" class="flex gap-1.5 overflow-x-auto hide-scrollbar"></div>
                    <button id="next-page" class="px-3.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 text-xs font-bold hover:bg-slate-50 hover:text-blue-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm flex items-center gap-1">
                        Next <i class="fas fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>

        </section>

        {{-- ======================================================================= --}}
        {{-- MODAL 1: EDIT CREDENTIALS (Username & Password) --}}
        {{-- ======================================================================= --}}
        <div x-show="openCred" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-0" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div x-show="openCred" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="toggleCred(false)"></div>

            {{-- Panel --}}
            <div x-show="openCred" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-md border border-slate-100 relative z-10 overflow-hidden">
                
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm">
                            <i class="fas fa-key"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 tracking-tight">Reset Kredensial</h3>
                    </div>
                    <button type="button" @click="toggleCred(false)" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="px-6 py-6">
                    <form @submit.prevent="submitCredentialUpdate()" class="space-y-5">
                        <div class="p-3 bg-blue-50 rounded-xl border border-blue-100 flex gap-3 items-center">
                            <div class="w-10 h-10 rounded-full bg-white text-blue-600 flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                <span x-text="targetName.charAt(0).toUpperCase()"></span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-500 mb-0.5">Identitas Target</p>
                                <p class="text-sm font-bold text-blue-900 truncate" x-text="targetName"></p>
                            </div>
                        </div>

                        <div>
                            <label class="form-label-tegas" for="cred_username">Username Login Baru</label>
                            <input type="text" id="cred_username" x-model="formData.username" class="form-input-tegas bg-slate-50 focus:bg-white" placeholder="Ketik username login...">
                        </div>

                        <div class="relative py-2">
                            <div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="w-full border-t border-dashed border-slate-200"></div></div>
                            <div class="relative flex justify-center"><span class="bg-white px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Keamanan</span></div>
                        </div>

                        <div>
                            <label class="form-label-tegas text-amber-600" for="cred_password">Password Baru</label>
                            <input type="password" id="cred_password" x-model="formData.password" class="form-input-tegas border-amber-200 focus:border-amber-500 focus:ring-amber-500/20 placeholder-slate-300" placeholder="Biarkan kosong jika tidak diubah">
                            <p class="text-[11px] font-medium text-slate-500 mt-1.5"><i class="fas fa-info-circle text-amber-500 mr-1"></i>Minimal 6 karakter kombinasi angka & huruf.</p>
                        </div>

                        <div>
                            <label class="form-label-tegas text-amber-600" for="cred_password_confirmation">Konfirmasi Password</label>
                            <input type="password" id="cred_password_confirmation" x-model="formData.password_confirmation" class="form-input-tegas border-amber-200 focus:border-amber-500 focus:ring-amber-500/20 placeholder-slate-300" placeholder="Ketik ulang password baru">
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                            <button type="button" @click="toggleCred(false)" class="px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all">Batal</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold text-sm hover:bg-blue-700 shadow-lg shadow-blue-600/20 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all flex items-center gap-2">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ======================================================================= --}}
        {{-- MODAL 2: EDIT ROLE (Hak Akses) --}}
        {{-- ======================================================================= --}}
        <div x-show="openRole" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-0" role="dialog" aria-modal="true">
            <div x-show="openRole" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="toggleRole(false)"></div>

            <div x-show="openRole" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-sm border border-slate-100 relative z-10 overflow-hidden">
                
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800 tracking-tight">Ubah Hak Akses Role</h3>
                    <button type="button" @click="toggleRole(false)" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="px-6 py-6">
                    <form @submit.prevent="submitRoleUpdate()" class="space-y-6">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-amber-200 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner border border-amber-200">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <p class="text-sm font-medium text-slate-500 leading-relaxed px-4">Hak akses untuk <strong class="text-slate-800 font-bold" x-text="targetName"></strong> akan menentukan menu apa saja yang dapat diaksesnya.</p>
                        </div>

                        <div>
                            <label class="form-label-tegas text-center" for="role_select">Pilih Otoritas Role Baru</label>
                            <select id="role_select" x-model="formData.role_id" class="form-input-tegas text-center cursor-pointer appearance-none bg-slate-50 focus:bg-white text-base py-3">
                                <option value="" disabled selected>-- Pilih Role Akses --</option>
                                <template x-for="r in roleList" :key="r.id">
                                    <option :value="r.id" x-text="r.nama_role"></option>
                                </template>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-6 flex items-center px-2 text-slate-500 pt-[104px]">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-center gap-3">
                            <button type="button" @click="toggleRole(false)" class="px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 hover:border-slate-300 transition-all focus:outline-none focus:ring-2 focus:ring-slate-200">Batal</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 text-white font-bold text-sm hover:bg-amber-600 shadow-lg shadow-amber-500/20 transition-all focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> Tetapkan Role
                            </button>
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