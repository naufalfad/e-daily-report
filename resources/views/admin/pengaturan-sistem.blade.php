@php($title = 'Pengaturan Sistem')

@extends('layouts.app', [
'title' => $title,
'role' => 'admin',
'active' => 'pengaturan-sistem',
])

@section('content')

{{-- GRID UTAMA: PENGATURAN KIRI, RESET PASSWORD + LOG KANAN --}}
<section class="grid grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.2fr)] gap-4">

    {{-- PANEL KIRI: PENGATURAN SISTEM --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
        <h2 class="text-[20px] font-normal mb-4">Pengaturan Sistem</h2>

        <div class="space-y-5">

            {{-- Mode Pemeliharaan Sistem --}}
            <div class="flex items-center justify-between">
                <span class="text-[14px] text-slate-700">
                    Mode Pemeliharaan Sistem
                </span>

                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div
                        class="w-11 h-6 bg-slate-300 peer-checked:bg-[#128C60] rounded-full px-[3px] flex items-center transition-colors duration-200">
                        <div
                            class="w-4 h-4 bg-white rounded-full shadow-sm transform transition-transform duration-200 peer-checked:translate-x-5">
                        </div>
                    </div>
                </label>
            </div>

            {{-- Backup Lengkap --}}
            <div class="space-y-2">
                <p class="text-[14px] text-slate-700">
                    Backup Lengkap (Database, File, Config)
                </p>
                <button type="button"
                    class="inline-flex items-center gap-2 rounded-[10px] bg-[#128C60] text-white text-[14px] px-4 py-2 hover:brightness-95 transition">
                    {{-- pakai inline icon biar tidak tergantung asset --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                        <path
                            d="M10 2a.75.75 0 0 1 .75.75V11l3.22-3.22a.75.75 0 1 1 1.06 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 0 1 1.06-1.06L9.25 11V2.75A.75.75 0 0 1 10 2Z" />
                        <path
                            d="M3.5 12.75a.75.75 0 0 1 .75.75v1A1.5 1.5 0 0 0 5.75 16h8.5a1.5 1.5 0 0 0 1.5-1.5v-1a.75.75 0 0 1 1.5 0v1A3 3 0 0 1 14.25 18h-8.5A3 3 0 0 1 2 14.25v-1a.75.75 0 0 1 .75-.75Z" />
                    </svg>
                    <span>Backup Sekarang</span>
                </button>
            </div>

            {{-- Update Logo Aplikasi (Favicon) --}}
            <div class="space-y-2">
                <p class="text-[14px] text-slate-700">Update Logo Aplikasi (Favicon)</p>

                <label class="block">
                    <div
                        class="w-full rounded-[12px] border border-slate-200 bg-slate-50/60 px-4 py-3 flex items-center justify-center gap-2 cursor-pointer hover:bg-slate-100 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            class="h-4 w-4 text-slate-500">
                            <path
                                d="M10 2.75a.75.75 0 0 1 .75.75v7.19l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 0 1 1.06-1.06L9.25 10.7V3.5A.75.75 0 0 1 10 2.75Z" />
                            <path
                                d="M4.5 12.75a.75.75 0 0 1 .75.75v1A1.5 1.5 0 0 0 6.75 16h6.5a1.5 1.5 0 0 0 1.5-1.5v-1a.75.75 0 0 1 1.5 0v1A3 3 0 0 1 13.25 18h-6.5A3 3 0 0 1 3 14.25v-1a.75.75 0 0 1 .75-.75Z" />
                        </svg>
                        <span class="text-[14px] text-slate-700">Upload Logo</span>
                    </div>
                    <input type="file" class="hidden" accept=".png,.ico,.jpg,.jpeg">
                </label>

                <p class="text-[11px] text-slate-400">
                    *Hanya mendukung tipe file .png, .ico, dan .jpeg saja.
                </p>
            </div>

            {{-- Update Footer --}}
            <div class="space-y-1">
                <label class="block text-[14px] text-slate-700 mb-1">Update Footer</label>
                <input type="text"
                    class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm text-slate-700"
                    value="© 2025 Badan Pendapatan Daerah Kabupaten Mimika | Sistem E-Daily Report versi 1.0">
            </div>

            {{-- Jadwal Backup Data Otomatis --}}
            <div class="space-y-1">
                <label class="block text-[14px] text-slate-700 mb-1">
                    Jadwal Backup Data Otomatis (Setiap Hari)
                </label>

                <div class="relative max-w-xs">
                    <input type="time"
                        class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 pr-10 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path
                            d="M10 2a8 8 0 1 0 8 8 8.01 8.01 0 0 0-8-8Zm.75 4.5a.75.75 0 0 0-1.5 0v3.25c0 .2.08.39.22.53l2 2a.75.75 0 1 0 1.06-1.06l-1.78-1.78Z" />
                    </svg>
                </div>
            </div>

            {{-- Lokasi Penyimpanan Backup --}}
            <div class="space-y-1">
                <label class="block text-[14px] text-slate-700 mb-1">
                    Lokasi Penyimpanan Backup
                </label>

                <div class="relative max-w-sm">
                    <select
                        class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 pr-10 text-sm text-slate-700 appearance-none focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                        <option value="" disabled selected hidden>Pilih lokasi penyimpanan backup</option>
                        <option>Server Lokal</option>
                        <option>Cloud Storage</option>
                        <option>External Drive</option>
                    </select>
                    <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                        class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none" alt="">
                </div>
            </div>

            {{-- Tombol Simpan / Reset --}}
            <div class="pt-2 flex flex-wrap gap-3">
                <button type="button"
                    class="inline-flex items-center justify-center rounded-[10px] bg-[#128C60] px-5 py-2 text-[14px] text-white font-medium hover:brightness-95 transition">
                    Simpan
                </button>
                <button type="button"
                    class="inline-flex items-center justify-center rounded-[10px] bg-[#C68A1E] px-5 py-2 text-[14px] text-white font-medium hover:brightness-95 transition">
                    Reset
                </button>
            </div>
        </div>
    </div>

    {{-- PANEL KANAN: RESET PASSWORD + LOG SISTEM --}}
    <div class="space-y-4">

        {{-- CARD RESET PASSWORD --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
            <h3 class="text-[16px] font-semibold text-slate-800 mb-4">Reset Password</h3>

            <div class="space-y-3">
                {{-- Password Lama --}}
                <div>
                    <label class="block text-[13px] text-slate-600 mb-1">Password Lama</label>
                    <div class="relative">
                        <input type="password" placeholder="Masukkan password lama"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 pr-10 text-sm text-slate-700 placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                        <span
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs select-none">👁</span>
                    </div>
                </div>

                {{-- Password Baru --}}
                <div>
                    <label class="block text-[13px] text-slate-600 mb-1">Password Baru</label>
                    <div class="relative">
                        <input type="password" placeholder="Masukkan password baru"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 pr-10 text-sm text-slate-700 placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                        <span
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs select-none">👁</span>
                    </div>
                </div>

                {{-- Konfirmasi Password Baru --}}
                <div>
                    <label class="block text-[13px] text-slate-600 mb-1">Konfirmasi Password Baru</label>
                    <div class="relative">
                        <input type="password" placeholder="Masukkan kembali password baru"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 pr-10 text-sm text-slate-700 placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                        <span
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs select-none">👁</span>
                    </div>
                </div>
            </div>

            <div class="pt-3 flex flex-wrap gap-3">
                <button type="button"
                    class="inline-flex items-center justify-center rounded-[10px] bg-[#128C60] px-5 py-2 text-[14px] text-white font-medium hover:brightness-95 transition">
                    Simpan
                </button>
                <button type="button"
                    class="inline-flex items-center justify-center rounded-[10px] bg-[#B6241C] px-5 py-2 text-[14px] text-white font-medium hover:brightness-95 transition">
                    Batalkan
                </button>
            </div>
        </div>

        {{-- CARD LOG SISTEM --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
            <h3 class="text-[16px] font-semibold text-slate-800 mb-3">Log Sistem</h3>

            <div class="space-y-1 text-[13px] text-slate-700">
                <p>2024-12-15 10:30: Sistem backup otomatis</p>
                <p>2024-12-15 09:15: User login: ahmad.surya</p>
                <p>2024-12-14 16:45: Laporan divalidasi: 8 laporan</p>
                <p>2024-12-14 14:20: Update pengaturan sistem</p>
            </div>
        </div>
    </div>

</section>

@endsection