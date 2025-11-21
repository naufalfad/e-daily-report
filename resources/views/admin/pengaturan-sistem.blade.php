@php($title = 'Pengaturan Sistem')

@extends('layouts.app', [
'title' => $title,
'role' => 'admin',
'active' => 'pengaturan-sistem',
])

@section('content')

<section class="rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5">

    {{-- FLEX UTAMA: KIRI (MENU) & KANAN (ISI) --}}
    <div class="flex flex-col lg:flex-row gap-10 items-start">
        {{-- ================= MENU KIRI ================= --}}
        <div class="w-full lg:w-72 shrink-0">
            {{-- Judul kiri, sejajar dengan judul kanan karena satu baris flex --}}
            <h2 class="text-[18px] font-semibold text-slate-900 mb-6">
                Pengaturan Sistem
            </h2>

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
            {{-- Judul kanan, otomatis sejajar dengan judul kiri --}}
            <h2 class="text-[18px] font-semibold text-slate-900 mb-2">
                Pengaturan Bawaan
            </h2>

            <p class="text-[13px] leading-relaxed text-slate-500 mb-5 max-w-3xl">
                Halaman Pengaturan Sistem digunakan untuk mengelola konfigurasi utama aplikasi,
                termasuk informasi organisasi, pengaturan role dan jabatan, keamanan akses,
                pengelolaan notifikasi, hingga reset password. Sesuaikan pengaturan berikut agar
                sistem berjalan sesuai kebutuhan operasional dan kebijakan dinas.
            </p>

            <div class="space-y-3">

                {{-- Mode Pemeliharaan Sistem --}}
                <div
                    class="w-[400px] h-[67px] rounded-[17px] border border-[#CBD6E0] bg-white px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center">
                            <img src="{{ asset('assets/icon/maintenance-mode.svg') }}" alt="Maintenance Icon"
                                class="h-[38px] w-[38px] teks-[#9CA3AF]">
                        </div>
                        <div>
                            <div class="text-[17px] font-medium text-[#0E1726]">
                                Mode Pemeliharaan
                            </div>
                            <p class="text-[12px] font-light text-[#5B687A]">
                                Aktifkan mode maintenance.
                            </p>
                        </div>
                    </div>

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

                {{-- Backup Data --}}
                <div
                    class="w-[400px] h-[67px] rounded-[17px] border border-[#CBD6E0] bg-white px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center">
                            <img src="{{ asset('assets/icon/backup-data.svg') }}" alt="Maintenance Icon"
                                class="h-[38px] w-[38px] teks-[#9CA3AF]">
                        </div>
                        <div>
                            <div class="text-[17px] font-medium text-[#0E1726]">
                                Backup Data
                            </div>
                            <p class="text-[12px] font-light text-[#5B687A]">
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
                    <p class="text-[14px] text-slate-700 font-medium">Update Logo Aplikasi</p>

                    <label class="block">
                        <div
                            class="w-full rounded-[12px] border border-dashed border-slate-200 bg-slate-50/60 px-4 py-4 flex items-center justify-center gap-2 cursor-pointer hover:bg-slate-100 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="h-4 w-4 text-slate-500"
                                fill="currentColor">
                                <path
                                    d="M10 2.75a.75.75 0 0 1 .75.75v7.19l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3-3a.75.75 0 0 1 1.06-1.06L9.25 10.7V3.5A.75.75 0 0 1 10 2.75Z" />
                                <path
                                    d="M4.5 12.75a.75.75 0 0 1 .75.75v1A1.5 1.5 0 0 0 6.75 16h6.5a1.5 1.5 0 0 0 1.5-1.5v-1a.75.75 0 0 1 1.5 0v1A3 3 0 0 1 13.25 18h-6.5A3 3 0 0 1 3 14.25v-1a.75.75 0 0 1 .75-.75Z" />
                            </svg>
                            <span class="text-[14px] text-slate-700">
                                Pilih File Logo
                            </span>
                        </div>
                        <input type="file" class="hidden" accept=".png,.ico,.jpg,.jpeg">
                    </label>

                    <p class="text-[11px] text-slate-400">
                        *Mendukung file .png, .ico, .jpg, dan .jpeg dengan ukuran kecil.
                    </p>
                </div>

                {{-- Update Footer --}}
                <div class="space-y-1">
                    <label class="block text-[14px] text-slate-700 mb-1">Update Footer</label>
                    <input type="text"
                        class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                        value="© 2025 Badan Pendapatan Daerah Kabupaten Mimika | Sistem E-Daily Report versi 1.0">
                </div>

                {{-- Zona Waktu --}}
                <div class="space-y-1">
                    <label class="block text-[14px] text-slate-700 mb-1">Zona Waktu</label>
                    <div class="relative max-w-md">
                        <select
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 pr-10 text-sm text-slate-700 appearance-none focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                            <option value="" disabled selected hidden>Pilih zona waktu yang sesuai</option>
                            <option>WIT (UTC +9)</option>
                            <option>WITA (UTC +8)</option>
                            <option>WIB (UTC +7)</option>
                        </select>
                        <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none"
                            alt="">
                    </div>
                </div>

                {{-- Tombol Simpan / Reset --}}
                <div class="pt-3 flex flex-wrap gap-3">
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
    </div>
</section>

{{-- JS KECIL UNTUK GANTI STYLE MENU SAAT DIKLIK --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.settings-menu-btn');

    function setActive(btn) {
        buttons.forEach(b => {
            b.classList.remove('text-[15px]', 'font-medium', 'text-[#0E1726]');
            b.classList.add('text-[14px]', 'font-normal', 'text-[#9CA3AF]');
        });

        btn.classList.remove('text-[14px]', 'font-normal', 'text-[#9CA3AF]');
        btn.classList.add('text-[15px]', 'font-medium', 'text-[#0E1726]');
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', () => setActive(btn));
    });
});
</script>
@endpush

@endsection