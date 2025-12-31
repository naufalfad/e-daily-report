{{-- 
    KOMPONEN: PAGINATION CONTROL
    ----------------------------
    Menangani navigasi halaman (Next, Prev, First, Last) dan Info Data.
    Parent State: riwayatCore (Alpine.js)
--}}

<div x-show="pagination.total > 0" 
     class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col md:flex-row items-center justify-between gap-4 text-sm transition-all"
     style="display: none;">

    {{-- BAGIAN KIRI: INFO DATA --}}
    <div class="text-slate-500 font-medium">
        Menampilkan 
        <span class="font-bold text-slate-700" x-text="pagination.from"></span> 
        sampai 
        <span class="font-bold text-slate-700" x-text="pagination.to"></span> 
        dari 
        <span class="font-bold text-slate-700" x-text="pagination.total"></span> 
        data
    </div>

    {{-- BAGIAN KANAN: TOMBOL NAVIGASI --}}
    <div class="flex items-center gap-2">
        
        {{-- Tombol: First Page --}}
        <button @click="changePage(1)" 
            :disabled="pagination.current_page === 1"
            class="p-2 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-[#155FA6] hover:border-[#155FA6] disabled:opacity-40 disabled:cursor-not-allowed transition-all"
            title="Halaman Pertama">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
        </button>

        {{-- Tombol: Previous --}}
        <button @click="changePage(pagination.current_page - 1)" 
            :disabled="pagination.current_page <= 1"
            class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 hover:text-[#155FA6] hover:border-[#155FA6] disabled:opacity-40 disabled:cursor-not-allowed transition-all flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            <span>Prev</span>
        </button>

        {{-- Info Halaman (Page X of Y) --}}
        <div class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-lg font-bold text-xs shadow-sm">
            <span x-text="pagination.current_page"></span>
            <span class="text-slate-300 mx-1">/</span>
            <span x-text="pagination.last_page"></span>
        </div>

        {{-- Tombol: Next --}}
        <button @click="changePage(pagination.current_page + 1)" 
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 hover:text-[#155FA6] hover:border-[#155FA6] disabled:opacity-40 disabled:cursor-not-allowed transition-all flex items-center gap-1">
            <span>Next</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>

        {{-- Tombol: Last Page --}}
        <button @click="changePage(pagination.last_page)" 
            :disabled="pagination.current_page === pagination.last_page"
            class="p-2 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-[#155FA6] hover:border-[#155FA6] disabled:opacity-40 disabled:cursor-not-allowed transition-all"
            title="Halaman Terakhir">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
        </button>

    </div>
</div>