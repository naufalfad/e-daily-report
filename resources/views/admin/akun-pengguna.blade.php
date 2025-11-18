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
                {{-- Tambah Akun --}}
                <button type="button" id="btn-open-add-akun"
                    class="inline-flex items-center gap-2 rounded-[10px] bg-[#128C60] text-white text-[14px] px-4 py-2 hover:brightness-95 transition">
                    <span>+ Tambah Akun</span>
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
                            <button type="button"
                                class="inline-flex items-center justify-center rounded-full border border-red-200 bg-red-50 px-2.5 py-1 hover:bg-red-100 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    class="h-4 w-4 text-red-600">
                                    <path fill-rule="evenodd"
                                        d="M8.75 3a1.5 1.5 0 0 0-1.06.44L7 4.13H4.75a.75.75 0 0 0 0 1.5h.39l.46 8.2A2.25 2.25 0 0 0 7.84 16h4.32a2.25 2.25 0 0 0 2.24-2.17l.46-8.2h.39a.75.75 0 0 0 0-1.5H13l-.69-.69A1.5 1.5 0 0 0 11.25 3h-2.5Zm0 2.25a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75Zm2.5 0a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="sr-only">Hapus akun</span>
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
                            <button type="button"
                                class="inline-flex items-center justify-center rounded-full border border-red-200 bg-red-50 px-2.5 py-1 hover:bg-red-100 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    class="h-4 w-4 text-red-600">
                                    <path fill-rule="evenodd"
                                        d="M8.75 3a1.5 1.5 0 0 0-1.06.44L7 4.13H4.75a.75.75 0 0 0 0 1.5h.39l.46 8.2A2.25 2.25 0 0 0 7.84 16h4.32a2.25 2.25 0 0 0 2.24-2.17l.46-8.2h.39a.75.75 0 0 0 0-1.5H13l-.69-.69A1.5 1.5 0 0 0 11.25 3h-2.5Zm0 2.25a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75Zm2.5 0a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="sr-only">Hapus akun</span>
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
                            <button type="button"
                                class="inline-flex items-center justify-center rounded-full border border-red-200 bg-red-50 px-2.5 py-1 hover:bg-red-100 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    class="h-4 w-4 text-red-600">
                                    <path fill-rule="evenodd"
                                        d="M8.75 3a1.5 1.5 0 0 0-1.06.44L7 4.13H4.75a.75.75 0 0 0 0 1.5h.39l.46 8.2A2.25 2.25 0 0 0 7.84 16h4.32a2.25 2.25 0 0 0 2.24-2.17l.46-8.2h.39a.75.75 0 0 0 0-1.5H13l-.69-.69A1.5 1.5 0 0 0 11.25 3h-2.5Zm0 2.25a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75Zm2.5 0a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="sr-only">Hapus akun</span>
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
                            <button type="button"
                                class="inline-flex items-center justify-center rounded-full border border-red-200 bg-red-50 px-2.5 py-1 hover:bg-red-100 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    class="h-4 w-4 text-red-600">
                                    <path fill-rule="evenodd"
                                        d="M8.75 3a1.5 1.5 0 0 0-1.06.44L7 4.13H4.75a.75.75 0 0 0 0 1.5h.39l.46 8.2A2.25 2.25 0 0 0 7.84 16h4.32a2.25 2.25 0 0 0 2.24-2.17l.46-8.2h.39a.75.75 0 0 0 0-1.5H13l-.69-.69A1.5 1.5 0 0 0 11.25 3h-2.5Zm0 2.25a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75Zm2.5 0a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="sr-only">Hapus akun</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

@endsection