@php($title = 'Pengaturan Sistem')

@extends('layouts.app', [
    'title' => $title,
    'role' => 'admin',
    'active' => 'pengaturan-sistem',
])

@section('content')

<section class="rounded-2xl flex flex-1 flex-col min-h-0 bg-white ring-1 ring-slate-200 px-6 py-8 min-h-[520px]">

    {{-- FLEX UTAMA: KIRI (MENU) & KANAN (ISI) --}}
    <div class="flex flex-col lg:flex-row gap-12 items-start">
        
        {{-- ================= MENU KIRI ================= --}}
        <div class="w-full lg:w-64 shrink-0">
            {{-- Judul Menu --}}
            <h2 class="text-[18px] font-bold text-slate-900 mb-1">
                Pengaturan Sistem
            </h2>
            <p class="text-[12px] text-slate-400 mb-8">Konfigurasi aplikasi</p>

            {{-- Navigasi Menu --}}
            <nav class="space-y-2">
                <button type="button" data-settings-menu="sistem"
                    class="settings-menu-btn w-full text-left px-4 py-2.5 rounded-lg text-[14px] font-medium text-[#0E1726] bg-slate-50 hover:bg-slate-100 transition-colors">
                    Pengaturan Sistem
                </button>

                <button type="button" data-settings-menu="role"
                    class="settings-menu-btn w-full text-left px-4 py-2.5 rounded-lg text-[14px] font-normal text-[#5B687A] hover:bg-slate-50 transition-colors">
                    Pengaturan Role & Jabatan
                </button>

                <button type="button" data-settings-menu="keamanan"
                    class="settings-menu-btn w-full text-left px-4 py-2.5 rounded-lg text-[14px] font-normal text-[#5B687A] hover:bg-slate-50 transition-colors">
                    Pengaturan Keamanan
                </button>

                <button type="button" data-settings-menu="reset"
                    class="settings-menu-btn w-full text-left px-4 py-2.5 rounded-lg text-[14px] font-normal text-[#5B687A] hover:bg-slate-50 transition-colors">
                    Reset Password
                </button>
            </nav>
        </div>

        {{-- ================= ISI KANAN ================= --}}
        <div class="flex-1 w-full">

            {{-- PANEL 1: PENGATURAN SISTEM --}}
            <div data-settings-panel="sistem" class="settings-panel animate-fade-in">
                <h2 class="text-[18px] font-semibold text-slate-900 mb-2">Pengaturan Sistem</h2>
                <p class="text-[13px] leading-relaxed text-slate-500 mb-6 max-w-3xl">
                    Kelola konfigurasi dasar aplikasi seperti mode pemeliharaan, backup data, logo, dan informasi footer instansi.
                </p>

                <div class="space-y-5">
                    {{-- Mode Pemeliharaan --}}
                    <div class="w-full max-w-[500px] p-4 rounded-xl border border-slate-200 flex items-center justify-between hover:border-[#1C7C54]/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                                <img src="{{ asset('assets/icon/maintenance-mode.svg') }}" class="w-6 h-6">
                            </div>
                            <div>
                                <div class="text-[15px] font-medium text-slate-800">Mode Pemeliharaan</div>
                                <p class="text-[12px] text-slate-500">Aktifkan mode maintenance untuk user.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#128C60]"></div>
                        </label>
                    </div>

                    {{-- Backup Data --}}
                    <div class="w-full max-w-[500px] p-4 rounded-xl border border-slate-200 flex items-center justify-between hover:border-[#1C7C54]/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                                <img src="{{ asset('assets/icon/backup-data.svg') }}" class="w-6 h-6">
                            </div>
                            <div>
                                <div class="text-[15px] font-medium text-slate-800">Backup Data</div>
                                <p class="text-[12px] text-slate-500">Unduh arsip database dan file konfigurasi.</p>
                            </div>
                        </div>
                        <button class="p-2 rounded-lg hover:bg-slate-100 text-[#128C60] transition">
                            <img src="{{ asset('assets/icon/download-data.svg') }}" class="w-6 h-6">
                        </button>
                    </div>

                    <hr class="border-slate-100 my-4 max-w-[500px]">

                    {{-- Form Input Biasa --}}
                    <div class="max-w-[500px] space-y-4">
                        <div>
                            <label class="block text-[14px] font-medium text-slate-700 mb-1">Update Footer</label>
                            <input type="text" class="form-input-tegas" value="© 2025 Badan Pendapatan Daerah Kabupaten Mimika">
                        </div>
                        
                        <div>
                            <label class="block text-[14px] font-medium text-slate-700 mb-1">Zona Waktu</label>
                            <select class="form-input-tegas cursor-pointer">
                                <option>WIT (UTC +9)</option>
                                <option>WITA (UTC +8)</option>
                                <option>WIB (UTC +7)</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button class="px-5 py-2 rounded-lg bg-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-300 transition">Reset</button>
                            <button class="px-5 py-2 rounded-lg bg-[#128C60] text-white text-sm font-medium hover:bg-emerald-700 transition">Simpan Perubahan</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PANEL 2: ROLE & JABATAN --}}
            <div data-settings-panel="role" class="settings-panel hidden animate-fade-in">
                <h2 class="text-[18px] font-semibold text-slate-900 mb-2">Pengaturan Role & Jabatan</h2>
                <p class="text-[13px] text-slate-500 mb-6 max-w-3xl">Sesuaikan struktur organisasi dan jabatan pegawai.</p>
                
                <div class="max-w-[500px] space-y-4">
                    <div>
                        <label class="block text-[14px] font-medium text-slate-700 mb-1">Nama Kepala Dinas</label>
                        <input type="text" class="form-input-tegas" placeholder="Nama Kepala Dinas">
                    </div>
                    <div>
                        <label class="block text-[14px] font-medium text-slate-700 mb-1">Nama Kepala Bidang</label>
                        <input type="text" class="form-input-tegas" placeholder="Nama Kepala Bidang">
                    </div>
                     <div class="flex justify-end gap-3 pt-2">
                        <button class="px-5 py-2 rounded-lg bg-[#128C60] text-white text-sm font-medium hover:bg-emerald-700 transition">Simpan</button>
                    </div>
                </div>
            </div>

            {{-- PANEL 3: KEAMANAN --}}
            <div data-settings-panel="keamanan" class="settings-panel hidden animate-fade-in">
                <h2 class="text-[18px] font-semibold text-slate-900 mb-2">Pengaturan Keamanan</h2>
                <p class="text-[13px] text-slate-500 mb-6 max-w-3xl">Konfigurasi timeout sesi dan batas percobaan login.</p>

                <div class="space-y-4">
                    {{-- Session Timeout --}}
                    <div class="w-full max-w-[500px] p-4 rounded-xl border border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/timeout.svg') }}" class="w-6 h-6">
                            </div>
                            <div>
                                <div class="text-[15px] font-medium text-slate-800">Session Timeout</div>
                                <p class="text-[12px] text-slate-500">Logout otomatis jika tidak aktif.</p>
                            </div>
                        </div>
                        <select class="rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54]">
                            <option>15 Menit</option>
                            <option>30 Menit</option>
                            <option>1 Jam</option>
                        </select>
                    </div>

                    {{-- Batas Login --}}
                    <div class="w-full max-w-[500px] p-4 rounded-xl border border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/batas-login.svg') }}" class="w-6 h-6">
                            </div>
                            <div>
                                <div class="text-[15px] font-medium text-slate-800">Batas Gagal Login</div>
                                <p class="text-[12px] text-slate-500">Blokir akun sementara.</p>
                            </div>
                        </div>
                        <select class="rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54]">
                            <option>3 Kali</option>
                            <option>5 Kali</option>
                            <option>10 Kali</option>
                        </select>
                    </div>
                     <div class="flex justify-start pt-2 max-w-[500px]">
                        <button class="w-full px-5 py-2 rounded-lg bg-[#128C60] text-white text-sm font-medium hover:bg-emerald-700 transition">Simpan Konfigurasi</button>
                    </div>
                </div>
            </div>

            {{-- PANEL 4: RESET PASSWORD --}}
            <div data-settings-panel="reset" class="settings-panel hidden animate-fade-in">
                <h2 class="text-[18px] font-semibold text-slate-900 mb-2">Reset Password</h2>
                <p class="text-[13px] text-slate-500 mb-6 max-w-3xl">Menu darurat untuk mereset akses pengguna.</p>

                <div class="space-y-4">
                    {{-- Card Reset Admin --}}
                    <button type="button" id="reset-admin-card" 
                        class="w-full max-w-[500px] p-4 rounded-xl border border-slate-200 bg-white flex items-center gap-4 hover:border-[#1C7C54] hover:bg-emerald-50/30 transition-all text-left group">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center group-hover:bg-white group-hover:shadow-sm transition">
                            <img src="{{ asset('assets/icon/reset-password-admin.svg') }}" class="w-6 h-6">
                        </div>
                        <div>
                            <div class="text-[15px] font-medium text-slate-800 group-hover:text-[#1C7C54]">Reset Password Admin</div>
                            <p class="text-[12px] text-slate-500">Ubah password akun admin saat ini.</p>
                        </div>
                    </button>

                    {{-- Card Reset User (Danger) --}}
                     <div class="w-full max-w-[500px] p-4 rounded-xl border border-red-200 bg-red-50/30 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/reset-password-user.svg') }}" class="w-6 h-6">
                            </div>
                            <div>
                                <div class="text-[15px] font-medium text-red-700">Reset Password Pengguna</div>
                                <p class="text-[12px] text-red-500">Fitur darurat (Maintenance).</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-red-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- MODAL RESET PASSWORD --}}
<div id="reset-admin-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 transform transition-all scale-100">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Reset Password Admin</h3>
        
        <form class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password Lama</label>
                <div class="relative">
                    <input type="password" id="old_password" class="form-input-tegas pr-10" placeholder="••••••">
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" data-eye-target="old_password">
                        <img src="{{ asset('assets/icon/eye-show.svg') }}" class="eye-show w-5 h-5">
                        <img src="{{ asset('assets/icon/eye-hide.svg') }}" class="eye-hide w-5 h-5 hidden">
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                <div class="relative">
                    <input type="password" id="new_password" class="form-input-tegas pr-10" placeholder="••••••">
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" data-eye-target="new_password">
                        <img src="{{ asset('assets/icon/eye-show.svg') }}" class="eye-show w-5 h-5">
                        <img src="{{ asset('assets/icon/eye-hide.svg') }}" class="eye-hide w-5 h-5 hidden">
                    </button>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" id="btn-reset-admin-cancel" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-600 font-medium hover:bg-slate-200 transition">Batal</button>
                <button type="button" id="btn-reset-admin-save" class="px-4 py-2 rounded-lg bg-[#128C60] text-white font-medium hover:bg-emerald-700 transition">Simpan Password</button>
            </div>
        </form>
    </div>
</div>

@endsection