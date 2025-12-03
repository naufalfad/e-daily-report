@extends('layouts.app', ['role' => 'penilai'])

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- HEADER DASHBOARD --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Skoring Kinerja Pegawai</h1>
            <p class="text-gray-500 mt-1">Monitor dan evaluasi performa pegawai di Unit Kerja Anda.</p>
        </div>
        <div class="flex gap-2">
            <button id="export-pdf"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2">
                <i class="fas fa-download"></i> Export Laporan
            </button>
        </div>
    </div>

    {{-- STATISTIK CARDS (Diisi oleh JS) --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <p class="text-gray-500 text-sm font-medium">Total Bawahan</p>
            <p class="text-2xl font-bold text-gray-800 mt-2" id="stat-total">0</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <p class="text-gray-500 text-sm font-medium">Rata-rata Skor</p>
            <p class="text-2xl font-bold text-gray-800 mt-2" id="stat-avg">0%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
            <p class="text-gray-500 text-sm font-medium">Predikat Sangat Baik</p>
            <p class="text-2xl font-bold text-gray-800 mt-2" id="stat-sb">0</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
            <p class="text-gray-500 text-sm font-medium">Perlu Pembinaan</p>
            <p class="text-2xl font-bold text-gray-800 mt-2" id="stat-pembinaan">0</p>
        </div>
    </div>

    {{-- TABEL DATA --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden min-h-[400px]">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Detail Kinerja Pegawai</h3>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </span>
                <input type="text" id="search-input"
                    class="pl-10 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 w-64 transition shadow-sm"
                    placeholder="Cari nama pegawai...">
            </div>
        </div>

        <div class="overflow-x-auto relative">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Nama Pegawai</th>
                        <th class="py-3 px-6 text-left">Unit Kerja</th>
                        <th class="py-3 px-6 text-center">Realisasi LKH<br><span
                                class="text-xs text-gray-400 normal-case">(Disetujui / Total)</span></th>
                        <th class="py-3 px-6 text-center">Skor Kinerja</th>
                        <th class="py-3 px-6 text-center">Predikat</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="text-gray-600 text-sm font-light">
                    {{-- Data akan di-inject oleh JS disini --}}
                </tbody>
            </table>

            {{-- Loading State --}}
            <div id="loading-state"
                class="absolute inset-0 bg-white bg-opacity-90 flex flex-col items-center justify-center z-10 hidden">
                <svg class="animate-spin h-10 w-10 text-indigo-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="text-gray-500 font-medium">Sedang memuat data...</p>
            </div>

            {{-- Empty State --}}
            <div id="empty-state" class="hidden py-12 flex flex-col items-center justify-center text-center">
                <img src="{{ asset('assets/icon/search.svg') }}" class="w-16 h-16 opacity-40 mb-3">
                <p class="text-gray-500">Data tidak ditemukan.</p>
            </div>
        </div>
    </div>
</div>

{{-- Load JS File --}}
@vite(['resources/js/pages/penilai/skoring-kinerja.js'])
@endsection