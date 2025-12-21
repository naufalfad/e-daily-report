@php($title = 'Log Aktivitas')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'log'])

@section('content')

<section x-data="logActivityStaf()" x-init="initLog()" class="flex-1 flex flex-col h-full">

    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-6 flex-1 flex flex-col h-full overflow-hidden">

        {{-- HEADER & FILTER --}}
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-800 mb-4">
                Log Aktivitas Sistem
            </h2>

            {{-- FORM FILTER BARU (Month, Year, Search) --}}
            <form @submit.prevent="applyFilter()" class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    
                    {{-- 1. SEARCH INPUT --}}
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wider">Pencarian</label>
                        <div class="relative">
                            <input x-model="filter.search" type="text" placeholder="Cari Nama, NIP, atau Aktivitas..."
                                class="w-full pl-10 pr-4 py-2 rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54] focus:border-[#1C7C54] transition shadow-sm">
                            <span class="absolute left-3 top-2.5 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- 2. BULAN SELECT --}}
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wider">Bulan</label>
                        <select x-model="filter.month" 
                            class="w-full rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54] focus:border-[#1C7C54] shadow-sm cursor-pointer">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. TAHUN SELECT --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wider">Tahun</label>
                        <select x-model="filter.year" 
                            class="w-full rounded-lg border-slate-300 text-sm focus:ring-[#1C7C54] focus:border-[#1C7C54] shadow-sm cursor-pointer">
                            @for($y = date('Y'); $y >= 2023; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- 4. TOMBOL AKSI --}}
                    <div class="md:col-span-3 flex gap-2">
                        <button type="submit" 
                            class="flex-1 bg-[#1C7C54] hover:bg-[#156343] text-white py-2 px-4 rounded-lg text-sm font-medium transition shadow-sm flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filter
                        </button>
                        <button type="button" @click="resetFilter()"
                            class="px-3 py-2 bg-white border border-slate-300 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-50 transition shadow-sm"
                            title="Reset Filter">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- LIST TIMELINE --}}
        <div class="relative border-l-2 border-slate-200 pl-6 sm:pl-8 ml-2 sm:ml-4 space-y-8 flex-1 overflow-y-auto pr-2 custom-scrollbar">

            {{-- LOADING STATE --}}
            <div x-show="isLoading" class="py-10 text-center">
                <svg class="animate-spin h-8 w-8 text-[#1C7C54] mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-slate-500 text-sm">Sedang memuat data...</p>
            </div>

            {{-- DATA LOOP --}}
            <template x-for="item in items" :key="item.id">
                <div class="relative group">

                    {{-- TIMELINE DOT --}}
                    <div class="absolute -left-[31px] sm:-left-[39px] top-1.5 h-4 w-4 rounded-full border-[3px] border-white shadow-sm transition bg-[#1C7C54] group-hover:scale-110 z-10"></div>

                    {{-- CONTENT WRAPPER --}}
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-6">

                        {{-- WAKTU (Left Column) --}}
                        <div class="sm:w-32 flex-shrink-0 pt-1">
                            <div class="font-bold text-slate-700 text-sm" x-text="item.date_formatted"></div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs font-mono text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded" x-text="item.time_formatted"></span>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1 italic" x-text="item.time_ago"></div>
                        </div>

                        {{-- DETAIL BOX (Right Column) --}}
                        <div class="flex-grow bg-white rounded-xl p-4 ring-1 ring-slate-200 shadow-sm hover:shadow-md hover:ring-[#1C7C54]/30 transition-all duration-200">
                            
                            {{-- User Info --}}
                            <div class="flex justify-between items-start mb-2 border-b border-slate-50 pb-2">
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                        <span x-text="item.user_name"></span>
                                        <span x-show="item.user_role" class="text-[10px] px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 border border-blue-100 font-medium uppercase" x-text="item.user_role"></span>
                                    </h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5" x-text="item.jabatan"></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-400 bg-slate-50 px-2 py-1 rounded border border-slate-100" x-text="'NIP. ' + item.user_nip"></span>
                                </div>
                            </div>

                            {{-- Deskripsi Log --}}
                            <div class="text-[13px] text-slate-600 leading-relaxed">
                                <span x-text="item.deskripsi"></span>
                            </div>
                        </div>

                    </div>
                </div>
            </template>

            {{-- EMPTY STATE --}}
            <div x-show="!isLoading && items.length === 0" class="py-12 text-center" style="display: none;">
                <div class="bg-slate-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-slate-500 text-sm font-medium">Tidak ada aktivitas ditemukan.</p>
                <p class="text-slate-400 text-xs mt-1">Coba ubah filter bulan, tahun, atau kata kunci pencarian.</p>
            </div>

            {{-- LOAD MORE BUTTON --}}
            <div x-show="!isLoading && pagination.current_page < pagination.last_page" class="text-center pt-4 pb-8" style="display: none;">
                <button @click="loadMore()" 
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white border border-slate-200 text-slate-600 text-sm font-medium hover:border-[#1C7C54] hover:text-[#1C7C54] transition shadow-sm">
                    <span>Muat Lebih Banyak</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-y-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

</section>
@endsection