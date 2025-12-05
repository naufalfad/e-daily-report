@extends('layouts.app', ['role' => 'kadis'])

@section('content')
<div class="px-6 py-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800">📊 Skoring Kinerja Per Bidang</h1>
            <p class="text-gray-500 mt-1">Evaluasi performa Laporan Kinerja Harian (LKH) setiap Bidang.</p>
        </div>

        {{-- Button Export PDF --}}
        <button id="export-pdf"
            class="mt-4 md:mt-0 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl shadow transition-all flex items-center gap-2 font-medium text-sm disabled:opacity-50"
            title="Export Laporan Kinerja Bidang">
            <i class="fas fa-file-export"></i> Export Laporan
        </button>
    </div>

    {{-- FILTER CARD --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Pilih Periode Evaluasi</h3>
        <form id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            
            {{-- BULAN --}}
            <div>
                <label for="month" class="text-sm text-gray-600 font-medium block mb-1">Bulan</label>
                <select id="month" name="month"
                    class="rounded-xl border-gray-300 px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500 text-sm w-full transition duration-150 ease-in-out">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $i == date('n') ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $i)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- TAHUN --}}
            <div>
                <label for="year" class="text-sm text-gray-600 font-medium block mb-1">Tahun</label>
                <select id="year" name="year"
                    class="rounded-xl border-gray-300 px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500 text-sm w-full transition duration-150 ease-in-out">
                    {{-- Loop dari tahun sekarang hingga 3 tahun ke belakang --}}
                    @for ($i = date('Y'); $i >= date('Y') - 3; $i--)
                        <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- BUTTON --}}
            <div class="md:col-span-2">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl shadow text-sm font-semibold transition w-full flex items-center justify-center gap-2">
                    <i class="fas fa-filter"></i> Tampilkan Data
                </button>
            </div>

        </form>
    </div>

    {{-- STATISTICS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 transition duration-300 hover:shadow-xl">
            <p class="text-gray-500 text-sm font-medium flex items-center gap-2"><i class="fas fa-building text-indigo-500"></i> Total Bidang</p>
            <p class="text-4xl font-extrabold text-gray-800 mt-3" id="stat-total-bidang">0</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 transition duration-300 hover:shadow-xl">
            <p class="text-gray-500 text-sm font-medium flex items-center gap-2"><i class="fas fa-chart-line text-blue-500"></i> Rata-rata Persentase</p>
            <p class="text-4xl font-extrabold text-gray-800 mt-3" id="stat-avg">0%</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 transition duration-300 hover:shadow-xl">
            <p class="text-gray-500 text-sm font-medium flex items-center gap-2"><i class="fas fa-medal text-yellow-500"></i> Bidang Sangat Baik</p>
            <p class="text-4xl font-extrabold text-gray-800 mt-3" id="stat-sb">0</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 transition duration-300 hover:shadow-xl">
            <p class="text-gray-500 text-sm font-medium flex items-center gap-2"><i class="fas fa-exclamation-triangle text-red-500"></i> Bidang Perlu Pembinaan</p>
            <p class="text-4xl font-extrabold text-gray-800 mt-3" id="stat-pembinaan">0</p>
        </div>

    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800 mb-2 sm:mb-0">Daftar Skoring Kinerja</h3>

            <div class="relative w-full sm:w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" id="search-input"
                    class="pl-10 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm"
                    placeholder="Cari bidang atau Kabid...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs font-semibold tracking-wider">
                        <th class="py-3 px-6 text-center w-12">No</th>
                        <th class="py-3 px-6 w-1/4">Bidang</th>
                        <th class="py-3 px-6 w-1/4">Kepala Bidang (Kabid)</th>
                        <th class="py-3 px-6 text-center">LKH Submitted</th>
                        <th class="py-3 px-6 text-center">LKH Approved</th>
                        <th class="py-3 px-6 text-center">Persentase</th>
                        <th class="py-3 px-6 text-center">Predikat</th>
                    </tr>
                </thead>

                <tbody id="skoringTableBody"
                    class="text-gray-700 text-sm divide-y divide-gray-100">
                    {{-- Data akan diisi oleh JavaScript --}}
                    <tr><td colspan="7" class="text-center py-4 text-gray-500">Silakan pilih periode atau tunggu data dimuat...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

@vite(['resources/js/pages/kadis/skoring-bidang.js'])
@endsection