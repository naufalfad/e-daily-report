@php($title = 'Akun Pengguna')

@extends('layouts.app', [
'title' => $title,
'role' => 'admin',
'active'=> 'akun-pengguna',
])

@section('content')

{{-- Wrapper utama biar konten bisa stretch vertikal --}}
<div class="flex-1 flex flex-col min-h-0">
    <section class="flex-1 flex flex-col rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5 mb-0">

        {{-- Header: Judul + tombol kanan --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
            <div>
                <h1 class="text-[20px] font-normal text-slate-800">Akun Pengguna</h1>
            </div>

            <div class="flex flex-wrap items-center gap-3 justify-end">
                <button type="button" id="btn-open-add-akun"
                    class="inline-flex items-center gap-2 rounded-[10px] bg-[#128C60] text-white text-[14px] px-5 py-2.5 hover:brightness-95 transition">

                    <img src="{{ asset('assets/icon/akun-pengguna.svg') }}" alt="Tambah Akun" class="h-4 w-4">

                    <span>Tambah Akun</span>
                </button>


            </div>
        </div>

        {{-- Tabel data akun (stretch sampai bawah) --}}
        <div class="flex-1 min-h-0 overflow-x-auto">
            <table class="min-w-full border-collapse text-[13px]">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="text-left font-medium text-slate-600 py-3 px-4">Nama</th>
                        <th class="text-left font-medium text-slate-600 py-3 px-4">NIP</th>
                        <th class="text-left font-medium text-slate-600 py-3 px-4">Email</th>
                        <th class="text-left font-medium text-slate-600 py-3 px-4">Username</th>
                        <th class="text-left font-medium text-slate-600 py-3 px-4">Atasan Langsung</th>
                        <th class="text-left font-medium text-slate-600 py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    {{-- Row 1 --}}
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-3 px-4 text-slate-800 align-top">
                            Fahrizal Mudzaqi Maulana
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top whitespace-nowrap">
                            196703101988030109
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top">
                            Staf BAPENDA
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top">
                            FahrizalMudzaqi07
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top">
                            Joko Anwar
                        </td>
                        <td class="py-3 px-4 text-center align-top">
                            <button type="button" class="hover:opacity-80 transition">
                                <img src="{{ asset('assets/icon/delete.svg') }}" class="h-5 w-5" alt="Hapus">
                            </button>
                        </td>

                    </tr>

                    {{-- Row 2 --}}
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-3 px-4 text-slate-800 align-top">
                            Muhammad Naufal
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top whitespace-nowrap">
                            196703101988030109
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top">
                            Kepala Bidang I
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top">
                            MNaufal20
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top">
                            Darius Sabon Rain
                        </td>
                        <td class="py-3 px-4 text-center align-top">
                            <button type="button" class="hover:opacity-80 transition">
                                <img src="{{ asset('assets/icon/delete.svg') }}" class="h-5 w-5" alt="Hapus">
                            </button>
                        </td>
                    </tr>

                    {{-- Row 3 --}}
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-3 px-4 text-slate-800 align-top">
                            Darius Sabon Rain
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top whitespace-nowrap">
                            196703101988030109
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top">
                            Kepala Dinas Badan Pendapatan Daerah
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top">
                            DariusSabon9
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top">
                            -
                        </td>
                        <td class="py-3 px-4 text-center align-top">
                            <button type="button" class="hover:opacity-80 transition">
                                <img src="{{ asset('assets/icon/delete.svg') }}" class="h-5 w-5" alt="Hapus">
                            </button>
                        </td>
                    </tr>

                    {{-- Row 4 --}}
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-3 px-4 text-slate-800 align-top">
                            Reno Sebastian Nugraha
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top whitespace-nowrap">
                            196703101988030109
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top">
                            Staf IT
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top">
                            Reno12
                        </td>
                        <td class="py-3 px-4 text-slate-700 align-top">
                            Matthew Siregar
                        </td>
                        <td class="py-3 px-4 text-center align-top">
                            <button type="button" class="hover:opacity-80 transition">
                                <img src="{{ asset('assets/icon/delete.svg') }}" class="h-5 w-5" alt="Hapus">
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- MODAL TAMBAH AKUN PENGGUNA --}}
<div id="modal-add-akun" class="fixed inset-0 z-[65] hidden items-center justify-center bg-black/60 px-4">
    <div class="relative w-full max-w-[520px] bg-white rounded-[24px] shadow-xl px-6 md:px-8 py-6 md:py-7">

        {{-- Tombol close (X) --}}
        <button type="button" id="btn-close-add-akun"
            class="absolute right-6 top-5 text-slate-400 hover:text-slate-600 text-xl leading-none">
            &times;
        </button>

        {{-- Judul modal --}}
        <h2 class="text-[18px] md:text-[20px] font-semibold text-slate-800 mb-4">
            Tambah Akun
        </h2>

        {{-- FORM TAMBAH AKUN (FE only, dummy) --}}
        <form action="#" method="POST" class="space-y-4">
            @csrf

            {{-- Pilih Pegawai --}}
            <div>
                <label class="block text-[13px] text-slate-600 mb-1">Pilih Pegawai</label>
                <div class="relative">
                    <select class="w-full h-[46px] rounded-[10px] border border-slate-200 bg-slate-50/60
                               px-3.5 pr-10 text-sm appearance-none
                               focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                               text-slate-700 placeholder:text-[#9CA3AF]">
                        <option value="" disabled selected hidden>Pilih Pegawai</option>
                        <option>Fahrizal Mudzaqi Maulana</option>
                        <option>Muhammad Naufal</option>
                        <option>Darius Sabon Rain</option>
                        <option>Reno Sebastian Nugraha</option>
                    </select>

                    <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                        class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none" alt="">
                </div>
            </div>

            {{-- Pilih Role --}}
            <div>
                <label class="block text-[13px] text-slate-600 mb-1">Pilih Role</label>
                <div class="relative">
                    <select class="w-full h-[46px] rounded-[10px] border border-slate-200 bg-slate-50/60
                               px-3.5 pr-10 text-sm appearance-none
                               focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                               text-slate-700 placeholder:text-[#9CA3AF]">
                        <option value="" disabled selected hidden>Pilih Role</option>
                        <option>Admin</option>
                        <option>Kepala Dinas</option>
                        <option>Kepala Bidang</option>
                        <option>Staf</option>
                    </select>

                    <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                        class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none" alt="">
                </div>
            </div>

            {{-- Username --}}
            <div>
                <label class="block text-[13px] text-slate-600 mb-1">Username</label>
                <input type="text" placeholder="Buat Username" class="w-full h-[46px] rounded-[10px] border border-slate-200 bg-slate-50/60
                              px-3.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                              text-slate-700 placeholder:text-[#9CA3AF]" />
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-[13px] text-slate-600 mb-1">Password</label>
                <input type="password" placeholder="Buat Password" class="w-full h-[46px] rounded-[10px] border border-slate-200 bg-slate-50/60
                              px-3.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                              text-slate-700 placeholder:text-[#9CA3AF]" />
            </div>

            {{-- Tombol aksi --}}
            <div class="pt-1 flex flex-wrap justify-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-[7px] bg-[#128C60]
                               px-6 py-2 text-[14px] text-white font-normal hover:brightness-95 transition">
                    Submit
                </button>

                <button type="button" id="btn-cancel-add-akun" class="inline-flex items-center justify-center rounded-[7px] bg-[#B6241C]
                               px-6 py-2 text-[14px] text-white font-normal hover:brightness-95 transition">
                    Batalkan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection