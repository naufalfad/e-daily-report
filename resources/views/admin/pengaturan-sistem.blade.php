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
    <div x-data="systemSettings()" x-init="init()" class="flex-1 flex flex-col min-h-0 bg-white rounded-2xl ring-1 ring-slate-200 m-0 overflow-hidden shadow-sm">
        
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
                                <input type="checkbox" class="sr-only peer">
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
                        {{-- Logo --}}
                        <div>
                            <label class="form-label-tegas">Logo Instansi</label>
                            <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 flex flex-col items-center justify-center gap-3 cursor-pointer hover:border-[#1C7C54] hover:bg-emerald-50/30 transition group">
                                <div class="p-3 bg-slate-50 rounded-full group-hover:scale-110 transition">
                                    <img src="{{ asset('assets/icon/upload-logo.svg') }}" class="w-6 h-6 opacity-50">
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-bold text-slate-700">Klik untuk upload logo baru</p>
                                    <p class="text-xs text-slate-400 mt-1">PNG, JPG, ICO (Max. 2MB)</p>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div>
                            <label class="form-label-tegas">Teks Footer Aplikasi</label>
                            <input type="text" value="© 2025 Badan Pendapatan Daerah Kabupaten Mimika" class="form-input-tegas">
                        </div>

                        {{-- Zona Waktu --}}
                        <div>
                            <label class="form-label-tegas">Zona Waktu Sistem</label>
                            <div class="relative">
                                <select class="form-input-tegas appearance-none cursor-pointer">
                                    <option>Waktu Indonesia Timur (WIT - UTC+9)</option>
                                    <option>Waktu Indonesia Tengah (WITA - UTC+8)</option>
                                    <option>Waktu Indonesia Barat (WIB - UTC+7)</option>
                                </select>
                                <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 opacity-60 pointer-events-none">
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                            <button class="px-6 py-3 rounded-xl border-2 border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 transition">Reset</button>
                            <button class="px-8 py-3 rounded-xl bg-[#128C60] text-white text-sm font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition transform hover:-translate-y-0.5">Simpan Perubahan</button>
                        </div>
                    </div>
                </div>

                {{-- PANEL 2: ROLE & JABATAN --}}
                <div x-show="activeTab === 'role'" x-cloak class="space-y-6 max-w-2xl">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-xl mb-6">
                        <p class="text-sm text-blue-700"><strong>Info:</strong> Struktur ini digunakan untuk menentukan alur validasi laporan secara otomatis.</p>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="form-label-tegas">Nama Kepala Dinas / Badan</label>
                            <input type="text" placeholder="Masukkan nama lengkap beserta gelar" class="form-input-tegas">
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="form-label-tegas">Pilih Bidang</label>
                                <div class="relative">
                                    <select class="form-input-tegas appearance-none">
                                        <option>Bidang Pendataan</option>
                                        <option>Sekretariat</option>
                                    </select>
                                    <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 opacity-60">
                                </div>
                            </div>
                            <div>
                                <label class="form-label-tegas">Nama Kepala Bidang</label>
                                <input type="text" placeholder="Nama Kabid" class="form-input-tegas">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="form-label-tegas">Pilih Sub-Bidang</label>
                                <div class="relative">
                                    <select class="form-input-tegas appearance-none">
                                        <option>Sub Bidang Pendaftaran</option>
                                    </select>
                                    <img src="{{ asset('assets/icon/chevron-down.svg') }}" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 opacity-60">
                                </div>
                            </div>
                            <div>
                                <label class="form-label-tegas">Nama Kepala Sub-Bidang</label>
                                <input type="text" placeholder="Nama Kasubid" class="form-input-tegas">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-6">
                        <button class="px-8 py-3 rounded-xl bg-[#128C60] text-white text-sm font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition">Simpan Struktur</button>
                    </div>
                </div>

                {{-- PANEL 3: JAM KERJA (NEW FEATURE REQUEST) --}}
                <div x-show="activeTab === 'jam_kerja'" x-cloak class="space-y-8 max-w-2xl">
                    
                    {{-- Jam Kerja --}}
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
                        <h3 class="text-[15px] font-bold text-slate-800 mb-5 flex items-center gap-2">
                            <img src="{{ asset('assets/icon/time.svg') }}" class="w-5 h-5 text-slate-500">
                            Waktu Operasional
                        </h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="form-label-tegas text-slate-600">Jam Masuk</label>
                                <input type="time" value="07:30" class="form-input-tegas cursor-pointer text-center text-lg tracking-wide">
                            </div>
                            <div>
                                <label class="form-label-tegas text-slate-600">Jam Pulang</label>
                                <input type="time" value="16:00" class="form-input-tegas cursor-pointer text-center text-lg tracking-wide">
                            </div>
                        </div>
                    </div>

                    {{-- Hari Kerja --}}
                    <div>
                        <label class="form-label-tegas mb-4">Hari Kerja Efektif</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                                <label class="flex items-center justify-between p-4 rounded-xl border-2 border-slate-200 cursor-pointer hover:border-[#1C7C54] hover:bg-emerald-50/20 transition group bg-white shadow-sm">
                                    <div class="flex items-center gap-3">
                                        {{-- Checkbox Custom --}}
                                        <input type="checkbox" class="checkbox-tegas" 
                                            {{ in_array($hari, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']) ? 'checked' : '' }}>
                                        <span class="text-[15px] font-bold text-slate-700 group-hover:text-[#1C7C54] transition">{{ $hari }}</span>
                                    </div>
                                    
                                    {{-- Badge Libur/Masuk --}}
                                    <span class="text-[11px] font-bold px-2 py-1 rounded uppercase tracking-wide transition
                                        {{ in_array($hari, ['Sabtu', 'Minggu']) ? 'bg-red-100 text-red-600 group-hover:bg-white' : 'bg-emerald-100 text-emerald-700 group-hover:bg-white' }}">
                                        {{ in_array($hari, ['Sabtu', 'Minggu']) ? 'Libur' : 'Masuk' }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <button class="px-6 py-3 rounded-xl border-2 border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 transition">Reset Default</button>
                        <button class="px-8 py-3 rounded-xl bg-[#128C60] text-white text-sm font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition transform hover:-translate-y-0.5">Simpan Jadwal</button>
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
                            <select class="form-input-tegas w-32 py-2 text-center">
                                <option>15 Menit</option>
                                <option>30 Menit</option>
                                <option>1 Jam</option>
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
                            <select class="form-input-tegas w-32 py-2 text-center">
                                <option>3 Kali</option>
                                <option>5 Kali</option>
                                <option>10 Kali</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                         <button class="px-8 py-3 rounded-xl bg-[#128C60] text-white text-sm font-bold hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition">Simpan Keamanan</button>
                    </div>
                </div>

                {{-- PANEL 5: RESET PASSWORD --}}
                <div x-show="activeTab === 'reset'" x-cloak class="space-y-6 max-w-2xl">
                    
                    {{-- Reset Admin --}}
                    <button @click="openResetModal = true" 
                        class="w-full p-6 rounded-2xl border-2 border-slate-200 bg-white flex items-center gap-5 hover:border-[#1C7C54] hover:bg-emerald-50/20 transition text-left group shadow-sm hover:shadow-md">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center group-hover:bg-white group-hover:shadow-inner transition">
                            <img src="{{ asset('assets/icon/reset-password-admin.svg') }}" class="w-6 h-6 transition transform group-hover:scale-110">
                        </div>
                        <div class="flex-1">
                            <div class="text-[16px] font-bold text-slate-800 group-hover:text-[#128C60] transition">Ganti Password Admin</div>
                            <p class="text-[13px] text-slate-500 mt-1">Ubah password akun administrator yang sedang login.</p>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-[#128C60] group-hover:text-white transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </button>

                    {{-- Reset User (Danger) --}}
                    <div class="p-6 rounded-2xl border-2 border-red-100 bg-red-50/30 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-5">
                            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-500">
                                <img src="{{ asset('assets/icon/reset-password-user.svg') }}" class="w-6 h-6">
                            </div>
                            <div>
                                <div class="text-[16px] font-bold text-red-700">Mode Darurat: Reset Password User</div>
                                <p class="text-[13px] text-red-500/80 mt-1">Izinkan admin mereset password pegawai lain.</p>
                            </div>
                        </div>
                        {{-- Red Toggle --}}
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div class="w-14 h-8 bg-red-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-red-600"></div>
                        </label>
                    </div>

                </div>

            </div>
        </div>

        {{-- MODAL RESET PASSWORD --}}
        <div x-show="openResetModal" x-cloak 
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
            x-transition.opacity.duration.300ms>
            
            <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-lg overflow-hidden transform transition-all" @click.away="openResetModal = false">
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-[20px] font-bold text-slate-900">Reset Password Admin</h3>
                    <button @click="openResetModal = false" class="text-slate-400 hover:text-slate-600 transition text-2xl">&times;</button>
                </div>
                
                <div class="px-8 py-6">
                    <form class="space-y-5">
                        {{-- Password Lama --}}
                        <div x-data="{ show: false }">
                            <label class="form-label-tegas">Password Lama</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" class="form-input-tegas pr-12" placeholder="••••••">
                                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 opacity-50 hover:opacity-100 transition">
                                    <img :src="show ? '{{ asset('assets/icon/eye-show.svg') }}' : '{{ asset('assets/icon/eye-hide.svg') }}'" class="w-5 h-5">
                                </button>
                            </div>
                        </div>

                        {{-- Password Baru --}}
                        <div x-data="{ show: false }">
                            <label class="form-label-tegas">Password Baru</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" class="form-input-tegas pr-12" placeholder="••••••">
                                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 opacity-50 hover:opacity-100 transition">
                                    <img :src="show ? '{{ asset('assets/icon/eye-show.svg') }}' : '{{ asset('assets/icon/eye-hide.svg') }}'" class="w-5 h-5">
                                </button>
                            </div>
                        </div>

                        {{-- Konfirmasi --}}
                        <div x-data="{ show: false }">
                            <label class="form-label-tegas">Konfirmasi Password Baru</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" class="form-input-tegas pr-12" placeholder="••••••">
                                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 opacity-50 hover:opacity-100 transition">
                                    <img :src="show ? '{{ asset('assets/icon/eye-show.svg') }}' : '{{ asset('assets/icon/eye-hide.svg') }}'" class="w-5 h-5">
                                </button>
                            </div>
                        </div>

                        {{-- Footer Buttons --}}
                        <div class="flex justify-end gap-3 pt-6">
                            <button type="button" @click="openResetModal = false" class="px-6 py-3 rounded-xl border-2 border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 transition">Batalkan</button>
                            <button type="button" class="px-8 py-3 rounded-xl bg-[#128C60] text-white text-sm font-bold hover:bg-emerald-700 shadow-lg transition transform hover:-translate-y-0.5">Simpan Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    {{-- ALPINE JS COMPONENT --}}
    <script>
        function systemSettings() {
            return {
                activeTab: 'sistem',
                openResetModal: false,
                menus: [
                    { id: 'sistem', label: 'Pengaturan Sistem', title: 'Pengaturan Bawaan' },
                    { id: 'role', label: 'Pengaturan Role dan Jabatan', title: 'Pengaturan Role dan Jabatan' },
                    { id: 'keamanan', label: 'Pengaturan Keamanan', title: 'Pengaturan Keamanan' },
                    { id: 'jam_kerja', label: 'Pengaturan Jam Kerja', title: 'Pengaturan Jam Kerja' }, // NEW MENU
                    { id: 'reset', label: 'Reset Password', title: 'Reset Password' },
                ],
                init() {
                    console.log('System Settings Loaded with Alpine.js');
                }
            }
        }
    </script>
@endsection
