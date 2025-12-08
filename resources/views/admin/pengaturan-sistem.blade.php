@php($title = 'Pengaturan Sistem')

@extends('layouts.app', [
    'title' => $title,
    'role' => 'admin',
    'active' => 'pengaturan-sistem',
])

@section('content')
    <style>
        /* Custom Scrollbar */
        .modal-scroll::-webkit-scrollbar { width: 6px; }
        .modal-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
        
        /* Styles Tampilan Tegas */
        .form-input-tegas {
            @apply w-full rounded-xl border-2 border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-800 placeholder:text-slate-400 focus:border-[#1C7C54] focus:ring-0 transition-all duration-200;
        }
        .form-label-tegas {
            @apply block text-sm font-bold text-slate-700 mb-2;
        }
        
        /* Checkbox Custom yang Lebih Besar & Tegas */
        .checkbox-tegas {
            @apply w-6 h-6 rounded-[6px] border-2 border-slate-400 text-[#128C60] focus:ring-0 focus:ring-offset-0 cursor-pointer transition-all;
        }
    </style>

    {{-- ROOT CONTAINER --}}
    {{-- [PERBAIKAN] Menggunakan nama komponen Alpine dari JS: systemSettingsData() --}}
    <div x-data="systemSettingsData()" x-init="init()" class="flex-1 flex flex-col min-h-0 bg-white rounded-2xl ring-1 ring-slate-200 m-0 overflow-hidden shadow-sm">
        
        <div class="flex flex-col lg:flex-row h-full">
            
            {{-- ================= SIDEBAR MENU (KIRI) ================= --}}
            <div class="w-full lg:w-[280px] shrink-0 border-b lg:border-b-0 lg:border-r border-slate-200 p-6 lg:py-8 bg-slate-50/30">
                <h2 class="text-[18px] font-bold text-slate-900 mb-8 px-2">Pengaturan Sistem</h2>

                <nav class="space-y-2">
                    <template x-for="menu in menus" :key="menu.id">
                        <button @click="activeTab = menu.id"
                            class="w-full text-left px-4 py-3.5 rounded-xl text-[14px] transition-all duration-200 flex items-center justify-between group border border-transparent"
                            :class="activeTab === menu.id ? 'bg-white text-slate-900 font-bold shadow-sm border-slate-200' : 'text-slate-500 font-medium hover:bg-white hover:text-slate-700 hover:shadow-sm'">
                            <span x-text="menu.label"></span>
                            
                            {{-- Indikator Aktif --}}
                            <div x-show="activeTab === menu.id" class="w-1.5 h-1.5 rounded-full bg-[#1C7C54]"></div>
                        </button>
                    </template>
                </nav>
            </div>

            {{-- ================= KONTEN UTAMA (KANAN) ================= --}}
            <div class="flex-1 p-6 lg:p-10 overflow-y-auto h-[calc(100vh-120px)] scroll-smooth">
                
                {{-- HEADER TAB --}}
                <div class="mb-8 border-b border-slate-100 pb-6">
                    <h2 class="text-[22px] font-bold text-slate-900 mb-2" x-text="menus.find(m => m.id === activeTab).title"></h2>
                    <p class="text-[14px] text-slate-500 leading-relaxed max-w-3xl">
                        Sesuaikan konfigurasi sistem sesuai kebijakan instansi. Pastikan data yang dimasukkan valid.
                    </p>
                </div>

                {{-- PANEL 1: PENGATURAN UMUM --}}
                <div x-show="activeTab === 'sistem'" x-transition.opacity.duration.300ms class="space-y-8 max-w-2xl">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Mode Pemeliharaan --}}
                        <div class="p-5 rounded-2xl border-2 border-slate-100 hover:border-[#1C7C54]/30 transition-colors bg-white flex items-center justify-between group shadow-sm hover:shadow-md">
                            <div class="flex items-center gap-4">
                                <div class="p-3 rounded-full bg-slate-50 group-hover:bg-[#1C7C54]/10 transition-colors">
                                    <img src="{{ asset('assets/icon/maintenance-mode.svg') }}" class="w-6 h-6 opacity-70 group-hover:opacity-100">
                                </div>
                                <div>
                                    <div class="text-[15px] font-bold text-slate-800">Mode Maintenance</div>
                                    <p class="text-[12px] text-slate-500">Tutup akses user sementara.</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                {{-- [PERBAIKAN] Hapus true-value/false-value. Gunakan boolean native. --}}
                                <input type="checkbox" x-model="settings.maintenance_mode" class="sr-only peer">
                                <div class="w-12 h-7 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1C7C54]"></div>
                            </label>
                        </div>
                        {{-- Backup Data --}}
                        <div class="p-5 rounded-2xl border-2 border-slate-100 hover:border-[#1C7C54]/30 transition-colors bg-white flex items-center justify-between group shadow-sm hover:shadow-md">
                            <div class="flex items-center gap-4">
                                <div class="p-3 rounded-full bg-slate-50 group-hover:bg-[#1C7C54]/10 transition-colors">
                                    <img src="{{ asset('assets/icon/backup-data.svg') }}" class="w-6 h-6 opacity-70 group-hover:opacity-100">
                                </div>
                                <div>
                                    <div class="text-[15px] font-bold text-slate-800">Backup Database</div>
                                    <p class="text-[12px] text-slate-500">Unduh arsip data sistem.</p>
                                </div>
                            </div>
                            <button class="p-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-[#1C7C54] hover:text-white transition">
                                <img src="{{ asset('assets/icon/download-data.svg') }}" class="w-5 h-5 filter hover:brightness-0 hover:invert">
                            </button>
                        </div>
                    </div>

                    <div class="space-y-6 pt-4">

                        {{-- Zona Waktu (Binding Select) --}}
                        <div>
                            <label class="form-label-tegas">Zona Waktu Sistem</label>
                            <div class="relative">
                                <select x-model="settings.timezone" class="form-input-tegas appearance-none cursor-pointer">
                                    <option value="Asia/Jayapura">Waktu Indonesia Timur (WIT - UTC+9)</option>
                                    <option value="Asia/Makassar">Waktu Indonesia Tengah (WITA - UTC+8)</option>
                                    <option value="Asia/Jakarta">Waktu Indonesia Barat (WIB - UTC+7)</option>
                                </select>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 opacity-60 pointer-events-none">
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                            {{-- [PERBAIKAN] Tombol Reset hanya me-refresh data dari DB --}}
                            <button @click.prevent="fetchSettings()" class="px-6 py-3 rounded-xl border-2 border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 transition">Reset</button>
                            {{-- [PERBAIKAN] Tombol Simpan (Memanggil fungsi dari JS Component) --}}
                            <button @click.prevent="submitGeneralSettings()" :disabled="isLoading" 
                                :class="{'opacity-70 cursor-not-allowed': isLoading}" 
                                class="px-8 py-3 rounded-xl bg-[#128C60] text-white text-sm font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition transform hover:-translate-y-0.5">
                                <span x-text="isLoading ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- PANEL 4: KEAMANAN --}}
                <div x-show="activeTab === 'keamanan'" x-cloak class="space-y-6 max-w-2xl">
                    
                    <div class="grid grid-cols-1 gap-5">
                        <div class="p-5 rounded-2xl border-2 border-slate-100 flex items-center justify-between bg-white shadow-sm">
                            <div class="flex items-center gap-4">
                                <img src="{{ asset('assets/icon/timeout.svg') }}" class="w-10 h-10">
                                <div>
                                    <div class="text-[15px] font-bold text-slate-800">Session Timeout</div>
                                    <p class="text-[12px] text-slate-500">Durasi sesi inaktif sebelum logout otomatis.</p>
                                </div>
                            </div>
                            {{-- Binding Select Timeout --}}
                            <select x-model="settings.session_timeout" class="form-input-tegas w-32 py-2 text-center">
                                <option value="15">15 Menit</option>
                                <option value="30">30 Menit</option>
                                <option value="60">1 Jam</option>
                            </select>
                        </div>

                        <div class="p-5 rounded-2xl border-2 border-slate-100 flex items-center justify-between bg-white shadow-sm">
                            <div class="flex items-center gap-4">
                                <img src="{{ asset('assets/icon/batas-login.svg') }}" class="w-10 h-10">
                                <div>
                                    <div class="text-[15px] font-bold text-slate-800">Limit Percobaan Login</div>
                                    <p class="text-[12px] text-slate-500">Batas gagal login sebelum akun dikunci.</p>
                                </div>
                            </div>
                            {{-- Binding Select Login Limit --}}
                            <select x-model="settings.login_limit" class="form-input-tegas w-32 py-2 text-center">
                                <option value="3">3 Kali</option>
                                <option value="5">5 Kali</option>
                                <option value="10">10 Kali</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                         <button class="px-8 py-3 rounded-xl bg-[#128C60] text-white text-sm font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition">Simpan Keamanan</button>
                    </div>
                </div>
            </div>
    </div>

    {{-- ALPINE JS COMPONENT --}}
    {{-- [PERBAIKAN] Hapus defininsi fungsi lama dan bergantung pada JS file yang sudah di-import di app.js --}}
    <script>
        // Gunakan nama fungsi yang sudah diexport di setting-sistem.js
        // window.systemSettingsData = systemSettingsData; 
        
        // Halaman ini sekarang sepenuhnya menggunakan logika dari resources/js/pages/admin/setting-sistem.js
        // Pastikan Anda telah menjalankan 'npm run dev'
    </script>
@endsection