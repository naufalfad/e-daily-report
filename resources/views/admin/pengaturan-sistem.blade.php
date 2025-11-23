@php($title = 'Pengaturan Sistem')

@extends('layouts.app', [
'title' => $title,
'role' => 'admin',
'active' => 'pengaturan-sistem',
])

@section('content')

<section class="rounded-2xl flex flex-1 flex-col min-h-0 bg-white ring-1 ring-slate-200 px-6 py-5 min-h-[520px]">

    {{-- FLEX UTAMA: KIRI (MENU) & KANAN (ISI) --}}
    <div class="flex flex-col lg:flex-row gap-10 items-start">
        {{-- ================= MENU KIRI ================= --}}
        <div class="w-full lg:w-72 shrink-0">
            <h2 class="text-[18px] font-semibold text-slate-900">
                Pengaturan Sistem
            </h2>

            {{-- dikasih margin-top supaya sejajar dengan card pertama di kanan --}}
            <nav class="mt-[90px] space-y-4">
                {{-- TIAP BUTTON PUNYA data-settings-menu UNTUK JS --}}
                <button type="button" data-settings-menu="sistem"
                    class="settings-menu-btn w-full text-left text-[15px] font-medium text-[#0E1726]">
                    Pengaturan Sistem
                </button>

                <button type="button" data-settings-menu="role"
                    class="settings-menu-btn w-full text-left text-[14px] font-normal text-[#9CA3AF]">
                    Pengaturan Role dan Jabatan
                </button>

                <button type="button" data-settings-menu="keamanan"
                    class="settings-menu-btn w-full text-left text-[14px] font-normal text-[#9CA3AF]">
                    Pengaturan Keamanan
                </button>

                <button type="button" data-settings-menu="reset"
                    class="settings-menu-btn w-full text-left text-[14px] font-normal text-[#9CA3AF]">
                    Reset Password
                </button>
            </nav>
        </div>

        {{-- ================= ISI KANAN ================= --}}
        <div class="flex-1">

            {{-- ============ PANEL: PENGATURAN SISTEM ============ --}}
            <div data-settings-panel="sistem" class="settings-panel">
                <h2 class="text-[18px] font-semibold text-slate-900 mb-2">
                    Pengaturan Bawaan
                </h2>

                {{-- DESKRIPSI GENERIK, DIPAKAI DI SEMUA MENU --}}
                <p class="text-[13px] leading-relaxed text-slate-500 mb-5 max-w-3xl">
                    Halaman Pengaturan Sistem digunakan untuk mengelola konfigurasi utama aplikasi,
                    termasuk informasi organisasi, pengaturan role dan jabatan, keamanan akses,
                    pengelolaan notifikasi, hingga reset password. Sesuaikan pengaturan berikut agar
                    sistem berjalan sesuai kebutuhan operasional dan kebijakan dinas.
                </p>

                <div class="space-y-3">

                    {{-- Mode Pemeliharaan Sistem --}}
                    <div
                        class="w-[400px] h-[70px] rounded-[15px] border border-[#CBD6E0] bg-white px-4 flex items-center justify-between">

                        <!-- KIRI: ICON + TEXT -->
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/icon/maintenance-mode.svg') }}" alt="Maintenance Icon"
                                class="h-[38px] w-[38px]">

                            <div class="leading-tight">
                                <div class="text-[17px] font-medium text-[#0E1726]">
                                    Mode Pemeliharaan
                                </div>
                                <p class="text-[12px] font-thin text-[#5B687A]">
                                    Aktifkan mode maintenance.
                                </p>
                            </div>
                        </div>

                        <!-- KANAN: TOGGLE -->
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-[#CBD6E0] peer-checked:bg-[#128C60] rounded-full px-[3px] flex items-center transition-all duration-200">
                                <div
                                    class="w-4 h-4 bg-white rounded-full shadow-sm transform transition-transform duration-200 peer-checked:translate-x-5">
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Backup Data --}}
                    <div
                        class="w-[400px] h-[70px] rounded-[15px] border border-[#CBD6E0] bg-white px-4 flex items-center justify-between">

                        <!-- KIRI: ICON + TEXT -->
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/icon/backup-data.svg') }}" alt="Backup Icon"
                                class="h-[38px] w-[38px]">

                            <div class="leading-tight">
                                <div class="text-[15px] font-semibold text-[#0E1726]">
                                    Backup Data
                                </div>
                                <p class="text-[12px] font-thin text-[#5B687A]">
                                    Backup data, file, dan config.
                                </p>
                            </div>
                        </div>

                        <button type="button" class="flex items-center">
                            <img src="{{ asset('assets/icon/download-data.svg') }}" alt="Download Data"
                                class="h-[34px] w-[34px] cursor-pointer hover:opacity-80 transition">
                        </button>
                    </div>

                    {{-- Update Logo Aplikasi --}}
                    <div class="space-y-2">
                        <p class="text-[15px] text-[#5B687A] font-normal">Update Logo Aplikasi</p>

                        <label class="block">
                            <div
                                class="w-[400px] h-[38px] rounded-[10px] border border-dashed border-[#CBD6E0] bg-white px-4 py-4 flex items-center justify-left gap-2 cursor-pointer hover:bg-slate-100 transition">
                                <img src="{{ asset('assets/icon/upload-logo.svg') }}" alt="Upload Logo"
                                    class="h-[19px] w-[19px] cursor-pointer hover:opacity-80 transition">
                                <span class="text-[13px] font-normal text-[#9CA3AF]">
                                    Pilih File Logo
                                </span>
                            </div>
                            <input type="file" class="hidden" accept=".png,.ico,.jpg,.jpeg,.svg">
                        </label>

                        <p class="text-[11px] text-slate-400">
                            *Mendukung file .png, .ico, .jpg, .jpeg, dan .svg dengan ukuran kecil.
                        </p>
                    </div>

                    {{-- Update Footer --}}
                    <div class="space-y-1">
                        <label class="block text-[15px] text-[#5B687A] font-normal mb-1">Update Footer</label>
                        <input type="text"
                            class="w-[400px] h-[38px] rounded-[10px] border border-[#CBD6E0] bg-white px-3.5 py-2.5 text-sm text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                            value="© 2025 Badan Pendapatan Daerah Kabupaten Mimika | Sistem E-Daily Report versi 1.0">
                    </div>

                    {{-- Zona Waktu --}}
                    <div class="space-y-1">
                        <label class="block text-[15px] text-[#5B687A] font-normal mb-1">Zona Waktu</label>

                        <div class="relative w-[400px]">
                            <select class="w-[400px] h-[38px] rounded-[10px] border border-[#CBD6E0] bg-white 
                                        px-4 pr-12 text-[13px] text-[#9CA3AF] 
                                        appearance-none focus:outline-none 
                                        focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                                <option value="" disabled selected hidden>Pilih zona waktu yang sesuai</option>
                                <option>WIT (UTC +9)</option>
                                <option>WITA (UTC +8)</option>
                                <option>WIB (UTC +7)</option>
                            </select>

                            <!-- Chevron masuk ke dalam form -->
                            <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                                class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none"
                                alt="">
                        </div>
                    </div>

                    {{-- Tombol Simpan / Reset --}}
                    <div class="pt-1 flex flex-wrap gap-3">
                        <button type="button"
                            class="w-[110px] h-[34px] inline-flex items-center justify-center rounded-[8px] bg-[#B6241C] px-5 py-2 text-[14px] text-white font-normal hover:brightness-95 transition">
                            Reset
                        </button>
                        <button type="button"
                            class="w-[110px] h-[34px] inline-flex items-center justify-center rounded-[8px] bg-[#0E7A4A] px-5 py-2 text-[14px] text-white font-normal hover:brightness-95 transition">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>

            {{-- ============ PANEL: PENGATURAN ROLE & JABATAN ============ --}}
            <div data-settings-panel="role" class="settings-panel hidden min-h-[750px]">
                <h2 class="text-[18px] font-semibold text-slate-900 mb-2">
                    Pengaturan Bawaan
                </h2>

                {{-- DESKRIPSI SAMA DENGAN PANEL LAIN --}}
                <p class="text-[13px] leading-relaxed text-slate-500 mb-5 max-w-3xl">
                    Halaman Pengaturan Sistem digunakan untuk mengelola konfigurasi utama aplikasi,
                    termasuk informasi organisasi, pengaturan role dan jabatan, keamanan akses,
                    pengelolaan notifikasi, hingga reset password. Sesuaikan pengaturan berikut agar
                    sistem berjalan sesuai kebutuhan operasional dan kebijakan dinas.
                </p>

                <div class="space-y-3 w-full max-w-md">

                    {{-- Nama Kepala Dinas --}}
                    <div class="space-y-1">
                        <label class="block text-[15px] text-[#5B687A] font-normal mb-1">
                            Nama Kepala Dinas
                        </label>
                        <input type="text" class="w-[400px] h-[38px] rounded-[10px] border border-[#CBD6E0] bg-white px-3.5 py-2.5
                                   text-sm text-[#9CA3AF] placeholder:text-[#9CA3AF]
                                   focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                            placeholder="Tuliskan nama kepala dinas">
                    </div>

                    {{-- Pilih Bidang --}}
                    <div class="space-y-1">
                        <label class="block text-[15px] text-[#5B687A] font-normal mb-1">
                            Pilih Bidang
                        </label>
                        <div class="relative w-[400px]">
                            <select class="w-full h-[38px] rounded-[10px] border border-[#CBD6E0] bg-white 
                                        px-4 pr-12 text-[13px] text-[#9CA3AF]
                                        appearance-none focus:outline-none
                                        focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                                <option value="" disabled selected hidden>Pilih bagian</option>
                                <option>Bidang 1</option>
                                <option>Bidang 2</option>
                            </select>
                            <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                                class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none"
                                alt="">
                        </div>
                    </div>

                    {{-- Nama Kepala Bidang --}}
                    <div class="space-y-1">
                        <label class="block text-[15px] text-[#5B687A] font-normal mb-1">
                            Nama Kepala Bidang
                        </label>
                        <input type="text" class="w-[400px] h-[38px] rounded-[10px] border border-[#CBD6E0] bg-white px-3.5 py-2.5
                                   text-sm text-[#9CA3AF] placeholder:text-[#9CA3AF]
                                   focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                            placeholder="Masukkan nama kepala bidang">
                    </div>

                    {{-- Pilih Sub Bidang --}}
                    <div class="space-y-1">
                        <label class="block text-[15px] text-[#5B687A] font-normal mb-1">
                            Pilih Sub Bidang
                        </label>
                        <div class="relative w-[400px]">
                            <select class="w-full h-[38px] rounded-[10px] border border-[#CBD6E0] bg-white 
                                        px-4 pr-12 text-[13px] text-[#9CA3AF]
                                        appearance-none focus:outline-none
                                        focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                                <option value="" disabled selected hidden>Pilih sub bidang</option>
                                <option>Sub Bidang 1</option>
                                <option>Sub Bidang 2</option>
                            </select>
                            <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                                class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none"
                                alt="">
                        </div>
                    </div>

                    {{-- Nama Kepala Sub Bidang --}}
                    <div class="space-y-1">
                        <label class="block text-[15px] text-[#5B687A] font-normal mb-1">
                            Nama Kepala Sub Bidang
                        </label>
                        <input type="text" class="w-[400px] h-[38px] rounded-[10px] border border-[#CBD6E0] bg-white px-3.5 py-2.5
                                   text-sm text-[#9CA3AF] placeholder:text-[#9CA3AF]
                                   focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                            placeholder="Masukkan nama kepala sub bidang">
                    </div>

                    {{-- Tombol Simpan / Reset --}}
                    <div class="pt-3 flex flex-wrap gap-3">
                        <button type="button"
                            class="w-[110px] h-[34px] inline-flex items-center justify-center rounded-[8px] bg-[#B6241C] px-5 py-2 text-[14px] text-white font-normal hover:brightness-95 transition">
                            Reset
                        </button>
                        <button type="button"
                            class="w-[110px] h-[34px] inline-flex items-center justify-center rounded-[8px] bg-[#0E7A4A] px-5 py-2 text-[14px] text-white font-normal hover:brightness-95 transition">
                            Simpan
                        </button>
                    </div>

                </div>
            </div>

            {{-- =========================== PENGATURAN KEAMANAN ============================ --}}
            <div data-settings-panel="keamanan" class="settings-panel hidden min-h-[750px]">

                <h2 class="text-[18px] font-semibold text-slate-900 mb-2">
                    Pengaturan Bawaan
                </h2>

                <p class="text-[13px] leading-relaxed text-slate-500 mb-5 max-w-3xl">
                    Halaman Pengaturan Sistem digunakan untuk mengelola konfigurasi utama aplikasi,
                    termasuk informasi organisasi, pengaturan role dan jabatan, keamanan akses,
                    pengelolaan notifikasi, hingga reset password. Sesuaikan pengaturan berikut agar
                    sistem berjalan sesuai kebutuhan operasional dan kebijakan dinas.
                </p>

                <div class="space-y-4 w-full max-w-md">

                    {{-- ================= SESSION TIMEOUT ================= --}}
                    <div
                        class="w-[500px] min-h-[70px] rounded-[15px] border border-[#CBD6E0] bg-white px-4 py-3 flex items-center justify-between">

                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/icon/timeout.svg') }}" class="h-[38px] w-[38px]" alt="">
                            <div>
                                <div class="text-[15px] font-semibold text-[#0E1726]">Session Timeout</div>
                                <p class="text-[12px] text-[#5B687A] font-thin">Otomatis keluar jika pengguna tidak
                                    aktif.</p>
                            </div>
                        </div>

                        <div class="relative">
                            <select class="w-[90px] h-[30px] rounded-[8px] border border-[#CBD6E0] bg-white 
                    px-2 pr-8 text-[12px] text-[#5B687A] appearance-none
                    focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                                <option>10 menit</option>
                                <option>15 menit</option>
                                <option>30 menit</option>
                                <option>1 jam</option>
                            </select>

                            <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 opacity-70 pointer-events-none">
                        </div>
                    </div>

                    {{-- ================= BATAS PERCOBAAN LOGIN ================= --}}
                    <div
                        class="w-[500px] min-h-[70px] rounded-[15px] border border-[#CBD6E0] bg-white px-4 py-3 flex items-center justify-between">

                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/icon/batas-login.svg') }}" class="h-[38px] w-[38px]" alt="">
                            <div>
                                <div class="text-[15px] font-semibold text-[#0E1726]">Batas Percobaan Login</div>
                                <p class="text-[12px] text-[#5B687A] font-thin">
                                    Blokir sementara setelah gagal login berulang.
                                </p>
                            </div>
                        </div>

                        <div class="relative">
                            <select class="w-[90px] h-[30px] rounded-[8px] border border-[#CBD6E0] bg-white 
                    px-2 pr-8 text-[12px] text-[#5B687A] appearance-none
                    focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                                <option>5 kali</option>
                                <option>3 kali</option>
                                <option>10 kali</option>
                            </select>

                            <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 opacity-70 pointer-events-none">
                        </div>
                    </div>

                    {{-- TOMBOL --}}
                    <div class="pt-3 flex flex-wrap gap-3">
                        <button type="button"
                            class="w-[110px] h-[34px] inline-flex items-center justify-center rounded-[8px] bg-[#B6241C] px-5 py-2 text-[14px] text-white font-normal hover:brightness-95 transition">
                            Reset
                        </button>
                        <button type="button"
                            class="w-[110px] h-[34px] inline-flex items-center justify-center rounded-[8px] bg-[#0E7A4A] px-5 py-2 text-[14px] text-white font-normal hover:brightness-95 transition">
                            Simpan
                        </button>
                    </div>

                </div>
            </div>

            <!-- RESET PASSWORD -->
            <div data-settings-panel="reset" class="settings-panel hidden min-h-[750px]">
                <h2 class="text-[18px] font-semibold text-slate-900 mb-2">
                    Pengaturan Bawaan
                </h2>
                <p class="text-[13px] leading-relaxed text-slate-500 mb-5 max-w-3xl">
                    Halaman Pengaturan Sistem digunakan untuk mengelola konfigurasi utama aplikasi,
                    termasuk informasi organisasi, pengaturan role dan jabatan, keamanan akses,
                    pengelolaan notifikasi, hingga reset password. Sesuaikan pengaturan berikut agar
                    sistem berjalan sesuai kebutuhan operasional dan kebijakan dinas.
                </p>

                <div class="space-y-4 w-full max-w-md">

                    {{-- CARD: RESET PASSWORD ADMIN (BUKA MODAL) --}}
                    <button type="button" id="reset-admin-card" class="w-[400px] min-h-[70px] rounded-[15px] border border-[#CBD6E0] bg-white px-4 py-3
                   flex items-center justify-between text-left hover:bg-slate-50 transition">

                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/icon/reset-password-admin.svg') }}" alt="Reset Admin"
                                class="h-[38px] w-[38px]">
                            <div>
                                <div class="text-[15px] font-semibold text-[#0E1726]">Reset Password Admin</div>
                                <p class="text-[12px] text-[#5B687A] font-thin">Reset password hanya untuk admin.</p>
                            </div>
                        </div>
                    </button>

                    {{-- CARD: RESET PASSWORD PENGGUNA (TOGGLE DARURAT) --}}
                    <div class="w-[400px] min-h-[70px] rounded-[15px] border border-[#E39A9A] bg-white px-4 py-3
                   flex items-center justify-between">

                        <div class="flex items-center gap-3">
                            <img src="{{ asset('assets/icon/reset-password-user.svg') }}" alt="Reset Pengguna"
                                class="h-[38px] w-[38px]">
                            <div>
                                <div class="text-[15px] font-semibold text-[#B6241C]">Reset Password Pengguna</div>
                                <p class="text-[12px] text-[#B6241C] font-thin">
                                    Gunakan saat keadaan darurat.
                                </p>
                            </div>
                        </div>

                        <!-- Toggle merah -->
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-[#F4D4D4] peer-checked:bg-[#B6241C] rounded-full px-[3px] flex items-center transition-all duration-200">
                                <div
                                    class="w-4 h-4 bg-white rounded-full shadow-sm transform transition-transform duration-200 peer-checked:translate-x-5">
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- MODAL RESET PASSWORD ADMIN --}}
                <div id="reset-admin-modal" class="fixed inset-0 z-40 hidden">
                    {{-- overlay --}}
                    <div class="absolute inset-0 bg-black/20"></div>

                    {{-- card --}}
                    <div class="relative z-50 w-full h-full flex items-center justify-center px-4">
                        <div class="w-full max-w-xl rounded-[20px] bg-white px-6 py-5 shadow-lg">

                            <h3 class="text-[18px] font-semibold text-slate-900 mb-4">
                                Reset Password Admin
                            </h3>

                            <div class="space-y-3 mb-5">

                                {{-- Password Lama --}}
                                <div class="space-y-1">
                                    <label class="block text-[14px] text-[#5B687A] font-normal mb-1">
                                        Password Lama
                                    </label>
                                    <div class="relative">
                                        <input id="old_password" type="password" class="w-full h-[38px] rounded-[10px] border border-[#CBD6E0] bg-white
                                       px-3.5 pr-10 py-2.5 text-sm text-[#0E1726]
                                       placeholder:text-[#9CA3AF]
                                       focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                                            placeholder="Masukkan password lama">
                                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2"
                                            data-eye-target="old_password">
                                            <img src="{{ asset('assets/icon/eye-show.svg') }}" alt="Show"
                                                class="eye-show h-4 w-4">
                                            <img src="{{ asset('assets/icon/eye-hide.svg') }}" alt="Hide"
                                                class="eye-hide h-4 w-4 hidden">
                                        </button>
                                    </div>
                                </div>

                                {{-- Password Baru --}}
                                <div class="space-y-1">
                                    <label class="block text-[14px] text-[#5B687A] font-normal mb-1">
                                        Masukkan Password Baru
                                    </label>
                                    <div class="relative">
                                        <input id="new_password" type="password" class="w-full h-[38px] rounded-[10px] border border-[#CBD6E0] bg-white
                                       px-3.5 pr-10 py-2.5 text-sm text-[#0E1726]
                                       placeholder:text-[#9CA3AF]
                                       focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                                            placeholder="Masukkan password baru">
                                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2"
                                            data-eye-target="new_password">
                                            <img src="{{ asset('assets/icon/eye-show.svg') }}" alt="Show"
                                                class="eye-show h-4 w-4">
                                            <img src="{{ asset('assets/icon/eye-hide.svg') }}" alt="Hide"
                                                class="eye-hide h-4 w-4 hidden">
                                        </button>
                                    </div>
                                </div>

                                {{-- Konfirmasi Password Baru --}}
                                <div class="space-y-1">
                                    <label class="block text-[14px] text-[#5B687A] font-normal mb-1">
                                        Konfirmasi Password Baru
                                    </label>
                                    <div class="relative">
                                        <input id="confirm_password" type="password" class="w-full h-[38px] rounded-[10px] border border-[#CBD6E0] bg-white
                                       px-3.5 pr-10 py-2.5 text-sm text-[#0E1726]
                                       placeholder:text-[#9CA3AF]
                                       focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                                            placeholder="Konfirmasi password baru">
                                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2"
                                            data-eye-target="confirm_password">
                                            <img src="{{ asset('assets/icon/eye-show.svg') }}" alt="Show"
                                                class="eye-show h-4 w-4">
                                            <img src="{{ asset('assets/icon/eye-hide.svg') }}" alt="Hide"
                                                class="eye-hide h-4 w-4 hidden">
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- BUTTON FOOTER --}}
                            <div class="flex justify-end gap-3">
                                <button type="button" id="btn-reset-admin-cancel"
                                    class="w-[110px] h-[34px] inline-flex items-center justify-center rounded-[8px] bg-[#B6241C] px-5 py-2 text-[14px] text-white font-normal hover:brightness-95 transition">
                                    Batalkan
                                </button>
                                <button type="button" id="btn-reset-admin-save"
                                    class="w-[110px] h-[34px] inline-flex items-center justify-center rounded-[8px] bg-[#0E7A4A] px-5 py-2 text-[14px] text-white font-normal hover:brightness-95 transition">
                                    Simpan
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection