@php($title = 'Log Aktivitas')
@extends('layouts.app', ['title' => $title, 'role' => 'penilai', 'active' => 'log'])

@section('content')
<section x-data="logActivityData()" x-init="initLog()" class="flex-1 flex flex-col">

    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5 flex-1 flex flex-col">
        <h2 class="text-[20px] font-normal mb-6">Log Aktivitas Pegawai</h2>

        {{-- FILTER --}}
        <form class="mb-8" @submit.prevent="filterData()">
            <label class="block text-xs font-normal text-slate-600 mb-2">Filter Berdasarkan Tanggal</label>
            <div class="grid md:grid-cols-[1fr_1fr_auto] gap-3 max-w-3xl">

                <div class="relative">
                    <input x-model="filter.from" type="date"
                        class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" />
                </div>

                <div class="relative">
                    <input x-model="filter.to" type="date"
                        class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" />
                </div>

                <div>
                    <button type="submit"
                        class="w-full md:w-auto rounded-[10px] bg-[#0E7A4A] px-6 py-2.5 text-sm text-white hover:brightness-95">
                        Terapkan
                    </button>
                </div>

            </div>
        </form>

        {{-- TIMELINE --}}
        <div class="relative pl-4 md:pl-8 border-l-2 border-slate-200 space-y-8 ml-2 md:ml-4 flex-1 overflow-y-auto">

            <template x-for="item in filteredItems" :key="item.id">
                <div class="relative">

                    {{-- DOT --}}
                    <div class="absolute -left-[25px] md:-left-[41px] top-1.5 h-4 w-4 rounded-full border-2 border-white shadow-sm"
                        :class="{
                            'bg-[#155FA6]': item.tipe === 'system',
                            'bg-[#0E7A4A]': item.tipe === 'create',
                            'bg-[#F59E0B]': item.tipe === 'update'
                        }">
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-6 group">

                        {{-- TANGGAL --}}
                        <div class="sm:w-32 flex-shrink-0 pt-1">
                            <div class="font-semibold text-slate-800 text-sm" x-text="formatDate(item.timestamp)"></div>
                            <div class="text-xs text-slate-500 mt-0.5" x-text="formatTime(item.timestamp)"></div>
                        </div>

                        {{-- DETAIL --}}
                        <div class="flex-grow bg-slate-50 rounded-xl p-4 ring-1 ring-slate-200 hover:ring-[#1C7C54]/50 transition-all">
                            <h4 class="font-medium text-slate-900 text-sm mb-1" x-text="item.user_name + ' (' + item.user_role + ')'"></h4>
                            <p class="text-xs text-slate-600 leading-relaxed" x-text="item.deskripsi_aktivitas"></p>
                        </div>

                    </div>

                </div>
            </template>

            {{-- EMPTY --}}
            <div x-show="filteredItems.length === 0" style="display: none;" class="py-8">
                <p class="text-sm text-slate-500 italic">Tidak ada aktivitas ditemukan pada rentang tanggal ini.</p>
            </div>

        </div>
    </div>

</section>
@endsection

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('logActivityData', () => ({

        allItems: [],
        filteredItems: [],
        filter: { from: '', to: '' },

        initLog() {
            fetch('/api/log-aktivitas', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}` // jika pakai token
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal autentikasi atau fetch log');
                return res.json();
            })
            .then(json => {
                const data = json.data ?? [];

                this.allItems = data.map(log => ({
                    id: log.id,
                    user_name: log.user_name ?? 'SYSTEM',
                    user_role: log.user_role ?? '-',
                    deskripsi_aktivitas: log.deskripsi_aktivitas ?? '',
                    tipe: log.tipe ?? 'system',
                    timestamp: log.timestamp ?? ''
                }));

                // Sort terbaru berdasarkan timestamp
                this.allItems.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
                this.filteredItems = this.allItems;
            })
            .catch(err => console.error(err));
        },

        filterData() {
            const from = this.filter.from ? new Date(this.filter.from) : null;
            const to = this.filter.to ? new Date(this.filter.to) : null;

            if (from) from.setHours(0,0,0,0);
            if (to) to.setHours(23,59,59,999);

            this.filteredItems = this.allItems.filter(item => {
                const date = new Date(item.timestamp);
                if (from && date < from) return false;
                if (to && date > to) return false;
                return true;
            });
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric'
            });
        },

        formatTime(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit'
            });
        }

    }));
});
</script>
@endpush
