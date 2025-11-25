@php($title = 'Log Aktivitas')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'log'])

@section('content')
<section x-data="logActivityPenilai()" x-init="initLog()" class="flex-1 flex flex-col">

    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-6 flex-1 flex flex-col">

        {{-- TITLE --}}
        <h2 class="text-[20px] font-normal mb-6 text-slate-800">
            Log Aktivitas Pegawai
        </h2>

        {{-- FILTER --}}
        <form @submit.prevent="filterData()" class="mb-8 max-w-3xl">
            <label class="block text-[13px] text-slate-600 mb-2 font-medium">
                Filter Berdasarkan Tanggal
            </label>

            <div class="grid md:grid-cols-[1fr_1fr_auto] gap-3">

                <input 
                    x-model="filter.from"
                    type="date"
                    class="w-full rounded-[10px] border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                >

                <input 
                    x-model="filter.to"
                    type="date"
                    class="w-full rounded-[10px] border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                >

                <button
                    type="submit"
                    class="rounded-[10px] bg-[#0E7A4A] px-6 py-2.5 text-sm text-white hover:brightness-95 transition-all"
                >Terapkan</button>

            </div>
        </form>

        {{-- TIMELINE --}}
        <div class="relative border-l-[2px] border-slate-200 pl-6 sm:pl-10 space-y-8 flex-1 overflow-y-auto">

            <template x-for="item in filteredItems" :key="item.id">
                <div class="relative">

                    {{-- DOT --}}
                    <div 
                        class="absolute -left-[14px] sm:-left-[30px] top-1.5 h-4 w-4 rounded-full border-[3px] border-white shadow-md transition 
                        bg-[#1C7C54]">
                    </div>

                    {{-- CONTENT --}}
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-8">

                        {{-- DATE --}}
                        <div class="sm:w-36 flex-shrink-0 pt-1">
                            <div class="font-semibold text-slate-800 text-[14px]" 
                                 x-text="formatDate(item.timestamp_fixed)">
                            </div>

                            <div class="text-xs text-slate-500"
                                 x-text="formatTime(item.timestamp_fixed)">
                            </div>
                        </div>

                        {{-- DETAIL BOX --}}
                        <div class="flex-grow bg-slate-50 rounded-xl p-4 ring-1 ring-slate-200 hover:ring-[#1C7C54]/40 transition-all">
                            <h4 class="font-medium text-slate-900 text-[14px] mb-1"
                                x-text="item.user_name + ' (' + item.user_role + ')'">
                            </h4>

                            <p class="text-[13px] text-slate-600 leading-relaxed"
                               x-text="item.deskripsi_aktivitas">
                            </p>
                        </div>

                    </div>

                </div>
            </template>

            {{-- EMPTY STATE --}}
            <div x-show="filteredItems.length === 0" class="py-8 text-center text-slate-500 text-sm italic">
                Tidak ada aktivitas ditemukan pada rentang tanggal ini.
            </div>

        </div>
    </div>

</section>
@endsection
