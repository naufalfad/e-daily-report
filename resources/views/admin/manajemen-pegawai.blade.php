@php($title = 'Manajemen Pegawai')

@extends('layouts.app', [
'title' => $title,
'role' => 'admin',
'active' => 'manajemen-pegawai',
])

@section('content')
{{-- Wrapper utama biar konten bisa stretch vertikal --}}
<div class="flex-1 flex flex-col min-h-0">
    <section class="flex-1 flex flex-col rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5 mb-0">

        {{-- Header: Judul + tombol kanan --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
            <div>
                <h1 class="text-[20px] font-normal text-slate-800">Data Master Pegawai</h1>
            </div>

            <div class="flex flex-wrap items-center gap-3 justify-end">
                {{-- Upload Excel --}}
                <button type="button" id="btn-open-upload-excel"
                    class="inline-flex items-center gap-2 rounded-[10px] bg-[#128C60] text-white text-[14px] px-4 py-2 hover:brightness-95 transition">
                    <img src="{{ asset('assets/icon/upload-excel.svg') }}" alt="" class="h-4 w-4">
                    <span>Upload File Excel</span>
                </button>

                {{-- Tambah Pegawai --}}
                <button type="button" id="btn-open-add-pegawai"
                    class="inline-flex items-center gap-2 rounded-[10px] bg-[#128C60] text-white text-[14px] px-4 py-2 hover:brightness-95 transition">
                    <img src="{{ asset('assets/icon/tambah-pegawai.svg') }}" alt="" class="h-4 w-4">
                    <span>Tambah Pegawai</span>
                </button>
            </div>
        </div>

        {{-- Filter bar --}}
        <div class="flex flex-col md:flex-row gap-3 mb-4">

            {{-- BIDANG --}}
            <div class="w-full md:w-1/3 relative">
                <select required class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                           px-3.5 py-2.5 text-sm pr-10 appearance-none
                           focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                    <option value="" disabled selected hidden>Semua Bidang</option>
                    <option value="bidang-1">Bidang I</option>
                    <option value="bidang-2">Bidang II</option>
                    <option value="bidang-3">Bidang III</option>
                </select>

                {{-- ICON CHEVRON DOWN --}}
                <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                    class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 pointer-events-none opacity-70" />
            </div>

            {{-- SUB BIDANG --}}
            <div class="w-full md:w-1/3 relative">
                <select required class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                           px-3.5 py-2.5 text-sm pr-10 appearance-none
                           focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                    <option value="" disabled selected hidden>Semua Sub Bidang</option>
                </select>

                <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                    class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 pointer-events-none opacity-70" />
            </div>

            {{-- JABATAN --}}
            <div class="w-full md:w-1/3 relative">
                <select required class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                           px-3.5 py-2.5 text-sm pr-10 appearance-none
                           focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]">
                    <option value="" disabled selected hidden>Semua Jabatan</option>
                </select>

                <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                    class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 pointer-events-none opacity-70" />
            </div>

        </div>

        {{-- Tabel data pegawai (stretch sampai bawah) --}}
        <div class="flex-1 min-h-0 overflow-x-auto">
            <table class="min-w-full border-collapse text-[13px]">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="text-left font-medium text-slate-600 py-3 px-4">Nama</th>
                        <th class="text-left font-medium text-slate-600 py-3 px-4">NIP</th>
                        <th class="text-left font-medium text-slate-600 py-3 px-4">Jabatan</th>
                        <th class="text-left font-medium text-slate-600 py-3 px-4">Unit Kerja</th>
                        <th class="text-left font-medium text-slate-600 py-3 px-4">Atasan Langsung</th>
                        <th class="text-left font-medium text-slate-600 py-3 px-4 text-center">Status</th>
                        <th class="text-left font-medium text-slate-600 py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    {{-- isi row pegawai di sini --}}
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- MODAL TAMBAH PEGAWAI --}}
<div id="modal-add-pegawai" class="fixed inset-0 z-[65] hidden items-center justify-center bg-black/60 px-4">
    <div class="relative w-full max-w-[920px] bg-white rounded-[24px] shadow-xl px-6 md:px-8 py-6 md:py-7">

        {{-- Tombol close (X) --}}
        <button type="button" id="btn-close-add-pegawai"
            class="absolute right-6 top-5 text-slate-400 hover:text-slate-600 text-xl leading-none">
            &times;
        </button>

        {{-- Judul modal --}}
        <h2 class="text-[18px] md:text-[20px] font-semibold text-slate-800 mb-4">
            Tambah Pegawai
        </h2>

        {{-- FORM TAMBAH PEGAWAI --}}
        <form action="#" method="POST" class="space-y-4">
            @csrf
            {{-- Grid 2 kolom --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Nama Pegawai --}}
                <div>
                    <label class="block text-[13px] text-slate-600 mb-1">Nama Pegawai</label>
                    <input type="text" name="nama" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                               px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                               focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                               placeholder:text-[#9CA3AF]" placeholder="Nama Pegawai">
                </div>

                {{-- NIP Pegawai --}}
                <div>
                    <label class="block text-[13px] text-slate-600 mb-1">NIP Pegawai</label>
                    <input type="text" name="nip" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                               px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                               focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                               placeholder:text-[#9CA3AF]" placeholder="NIP Pegawai">
                </div>

                {{-- Unit Kerja --}}
                <div>
                    <label class="block text-[13px] text-slate-600 mb-1">Unit Kerja</label>
                    <input type="text" name="unit_kerja" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                               px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                               focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                               placeholder:text-[#9CA3AF]" placeholder="Unit Kerja">
                </div>

                {{-- Jabatan --}}
                <div>
                    <label class="block text-[13px] text-slate-600 mb-1">Jabatan</label>
                    <input type="text" name="jabatan" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                               px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                               focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                               placeholder:text-[#9CA3AF]" placeholder="Jabatan">
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-[13px] text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                               px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                               focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                               placeholder:text-[#9CA3AF]" placeholder="example@gmail.com">
                </div>

                {{-- Nomor WhatsApp --}}
                <div>
                    <label class="block text-[13px] text-slate-600 mb-1">Nomor WhatsApp</label>
                    <input type="text" name="no_wa" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                               px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                               focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                               placeholder:text-[#9CA3AF]" placeholder="08XX-XXXX-XXXX">
                </div>

                {{-- ROW: Jenis Kelamin – Nama Atasan – Status (1 row, 3 kolom, full width) --}}
                <div class="md:col-span-2">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- Jenis Kelamin --}}
                        <div>
                            <label class="block text-[13px] text-slate-600 mb-1 leading-tight h-[32px] flex items-end">
                                Jenis Kelamin
                            </label>

                            <div class="relative">
                                <select class="w-full h-[46px] rounded-[10px] border border-slate-200 bg-slate-50/60
                           px-3.5 pr-10 text-sm appearance-none
                           focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                           text-slate-700 placeholder:text-[#9CA3AF]">
                                    <option>Laki-laki</option>
                                    <option>Perempuan</option>
                                </select>

                                <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none">
                            </div>
                        </div>

                        {{-- Nama Atasan Langsung --}}
                        <div>
                            <label class="block text-[13px] text-slate-600 mb-1 leading-tight h-[32px] flex items-end">
                                Nama Atasan Langsung
                            </label>

                            <input type="text" placeholder="Nama Atasan" class="w-full h-[46px] rounded-[10px] border border-slate-200 bg-slate-50/60
                          px-3.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                          text-slate-700 placeholder:text-[#9CA3AF]" />
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-[13px] text-slate-600 mb-1 leading-tight h-[32px] flex items-end">
                                Status
                            </label>

                            <div class="relative">
                                <select class="w-full h-[46px] rounded-[10px] border border-slate-200 bg-slate-50/60
                           px-3.5 pr-10 text-sm appearance-none
                           focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                           text-slate-700 placeholder:text-[#9CA3AF]">
                                    <option>Aktif</option>
                                    <option>Cuti</option>
                                    <option>Nonaktif</option>
                                </select>

                                <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alamat (full width) --}}
            <div>
                <label class="block text-[13px] text-slate-600 mb-1">Alamat</label>
                <textarea name="alamat" rows="3" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                           px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2
                           focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                           placeholder:text-[#9CA3AF]" placeholder="Alamat lengkap"></textarea>
            </div>

            {{-- Tombol aksi --}}
            <div class="pt-1 flex flex-wrap justify-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-[10px] bg-[#128C60]
                           px-4 py-2 text-[14px] text-white font-medium hover:brightness-95 transition">
                    Simpan Data
                </button>
                <button type="button" id="btn-cancel-add-pegawai" class="inline-flex items-center justify-center rounded-[10px] bg-[#B6241C]
                           px-4 py-2 text-[14px] text-white font-medium hover:brightness-95 transition">
                    Batalkan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL UPLOAD EXCEL --}}
<div id="modal-upload-excel" class="fixed inset-0 z-[65] hidden items-center justify-center bg-black/60 px-4">
    <div class="relative w-full max-w-[520px] bg-white rounded-[15px] shadow-xl px-6 md:px-8 py-6 md:py-7">

        {{-- Tombol close (X) --}}
        <button type="button" id="btn-close-upload-excel"
            class="absolute right-6 top-5 text-slate-400 hover:text-slate-600 text-xl leading-none">
            &times;
        </button>

        {{-- Judul modal --}}
        <h2 class="text-[18px] md:text-[20px] font-semibold text-slate-800 mb-4">
            Upload Excel
        </h2>

        {{-- FORM UPLOAD EXCEL --}}
        <form action="#" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Dropzone --}}
            <label class="block">
                <div class="w-full rounded-[20px] border-2 border-dashed border-slate-300 bg-slate-50/60
                           px-6 py-10 flex flex-col items-center justify-center text-center cursor-pointer
                           hover:border-[#1C7C54] hover:bg-emerald-50/40 transition">
                    {{-- Icon upload (pakai asset kamu) --}}
                    <img src="{{ asset('assets/icon/upload-excel.svg') }}" alt="Upload"
                        class="h-10 w-10 mb-3 opacity-70">

                    <p class="flex items-center justify-center gap-2 text-[14px] text-[#9CA3AF] mb-1">
                        <img src="{{ asset('assets/icon/upload-file.svg') }}" class="h-5 w-5 opacity-70" alt="">
                        <span>Upload File Excel</span>
                    </p>
                    <p class="text-[12px] text-slate-400">
                        Klik disini atau seret file ke area ini
                    </p>
                </div>

                {{-- Input file disembunyikan --}}
                <input type="file" name="file_excel" accept=".xls,.xlsx" class="hidden">
            </label>

            {{-- Tombol aksi --}}
            <div class="pt-1 flex flex-wrap justify-end gap-3">
                <button type="button" id="btn-cancel-upload-excel" class="inline-flex items-center justify-center rounded-[8px] bg-[#B6241C]
                               px-6 py-2 text-[14px] text-white font-normal hover:brightness-95 transition">
                    Batalkan
                </button>

                <button type="submit" class="inline-flex items-center justify-center rounded-[8px] bg-[#0E7A4A]
                               px-6 py-2 text-[14px] text-white font-normal hover:brightness-95 transition">
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>

@endsection