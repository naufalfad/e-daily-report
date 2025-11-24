@php($title = 'Log Aktivitas')
@extends('layouts.app', ['title' => $title, 'role' => 'admin', 'active' => 'log'])

@section('content')
<section x-data="logActivityData()" x-init="initLog()" class="flex-1 flex flex-col min-h-0">

    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 flex-1 flex flex-col min-h-0">

        <h2 class="text-[20px] font-normal mb-6">Log Aktivitas Pegawai</h2>

        <!-- FILTER -->
        <form class="mb-6" @submit.prevent="filterData()">
            <label class="block text-xs font-normal text-slate-600 mb-2">Filter Berdasarkan Tanggal</label>
            <div class="grid md:grid-cols-[1fr_1fr_auto] gap-3 max-w-3xl">
                <input x-model="filter.from" type="date"
                    class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm" />
                <input x-model="filter.to" type="date"
                    class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm" />
                <button type="submit"
                    class="rounded-[10px] bg-[#0E7A4A] px-6 py-2.5 text-sm text-white hover:brightness-95">
                    Terapkan
                </button>
            </div>
        </form>

        <!-- ISI BISA SCROLL TANPA MUNCUL SCROLLBAR -->
        <div class="relative pl-4 md:pl-8 border-l-2 border-slate-200 space-y-8
                    ml-2 md:ml-4 flex-1 overflow-y-auto min-h-0 scrollbar-hide">

            <template x-for="item in filteredItems" :key="item.id">
                <div class="relative">
                    <div class="absolute -left-[25px] md:-left-[41px] top-1.5 h-4 w-4 rounded-full border-2 border-white shadow-sm"
                        :class="{
                            'bg-[#155FA6]': item.tipe === 'system',
                            'bg-[#0E7A4A]': item.tipe === 'create',
                            'bg-[#F59E0B]': item.tipe === 'update'
                        }">
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-6 group">
                        <div class="sm:w-32 flex-shrink-0 pt-1">
                            <div class="font-semibold text-slate-800 text-sm" x-text="formatDate(item.tanggal)"></div>
                            <div class="text-xs text-slate-500" x-text="item.waktu"></div>
                        </div>

                        <div class="flex-grow bg-slate-50 rounded-xl p-4 ring-1 ring-slate-200">
                            <h4 class="font-medium text-slate-900 text-sm mb-1" x-text="item.aktivitas"></h4>
                            <p class="text-xs text-slate-600" x-text="item.deskripsi"></p>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="filteredItems.length === 0" class="py-8 text-sm text-slate-500 italic">
                Tidak ada aktivitas ditemukan.
            </div>
        </div>

    </div>
</section>
@endsection