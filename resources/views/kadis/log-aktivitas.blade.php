@php($title = 'Log Aktivitas')
@extends('layouts.app', ['title' => $title, 'role' => 'kadis', 'active' => 'log'])

@section('content')
<section x-data="logActivityKadis()" x-init="initLog()" class="flex-1 flex flex-col">

    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 flex-1 flex flex-col">
        <h2 class="text-[20px] font-normal mb-6">Log Aktivitas Kepala Dinas</h2>

        {{-- FILTER --}}
        <form class="mb-8" @submit.prevent="filterData()">
            <label class="block text-xs font-normal text-slate-600 mb-2">
                Filter Berdasarkan Tanggal
            </label>

            <div class="grid md:grid-cols-[1fr_1fr_auto] gap-3 max-w-3xl">
                {{-- Dari --}}
                <div>
                    <input x-model="filter.from" type="date" class="w-full rounded-[10px] border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm
                        focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" />
                </div>

                {{-- Sampai --}}
                <div>
                    <input x-model="filter.to" type="date" class="w-full rounded-[10px] border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm
                        focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" />
                </div>

                {{-- Tombol --}}
                <button type="submit"
                    class="rounded-[10px] bg-[#0E7A4A] px-6 py-2.5 text-sm text-white hover:brightness-95 transition-all">
                    Terapkan
                </button>
            </div>
        </form>

        {{-- TIMELINE --}}
        <div class="relative pl-4 md:pl-8 border-l-2 border-slate-200 space-y-8 ml-2 md:ml-4 flex-1 overflow-y-auto">

            <template x-for="item in filteredItems" :key="item.id">
                <div class="relative">

                    {{-- DOT --}}
                    <div class="absolute -left-[25px] md:-left-[41px] top-1.5 h-4 w-4 rounded-full border-[3px] border-white shadow-sm"
                        :class="{
                            'bg-[#155FA6]': item.tipe === 'system',
                            'bg-[#0E7A4A]': item.tipe === 'create',
                            'bg-[#F59E0B]': item.tipe === 'update'
                        }">
                    </div>

                    {{-- CONTENT --}}
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-6 group">

                        {{-- DATE --}}
                        <div class="sm:w-32 flex-shrink-0 pt-1">
                            <div class="font-semibold text-slate-800 text-sm" x-text="formatDate(item.timestamp_fixed)">
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5" x-text="formatTime(item.timestamp_fixed)">
                            </div>
                        </div>

                        {{-- DETAIL --}}
                        <div
                            class="flex-grow bg-slate-50 rounded-xl p-4 ring-1 ring-slate-200 hover:ring-[#1C7C54]/40 transition-all">
                            <h4 class="font-medium text-slate-900 text-sm mb-1" x-text="item.aktivitas"></h4>

                            <p class="text-xs text-slate-600 leading-relaxed" x-text="item.deskripsi"></p>
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