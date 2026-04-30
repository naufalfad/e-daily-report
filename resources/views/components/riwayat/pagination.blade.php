{{-- 
    KOMPONEN: PAGINATION CONTROL (ALPINE.JS)
    ----------------------------
    Menangani navigasi halaman (Next, Prev, First, Last) dan Info Data.
    Parent State: riwayatCore (Alpine.js)
--}}

<div x-show="pagination.total > 0" 
     class="px-6 py-4 bg-white border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 z-20 transition-all"
     style="display: none;">

    {{-- BAGIAN KIRI: INFO DATA --}}
    <div class="text-xs text-slate-500 font-bold bg-slate-50 px-3 py-2 rounded-lg border border-slate-200 w-full sm:w-auto text-center sm:text-left shadow-sm">
        Menampilkan 
        <span class="font-extrabold text-slate-800" x-text="pagination.from"></span> 
        - 
        <span class="font-extrabold text-slate-800" x-text="pagination.to"></span> 
        dari 
        <span class="font-extrabold text-[#1C7C54]" x-text="pagination.total"></span> 
        data
    </div>

    {{-- BAGIAN KANAN: TOMBOL NAVIGASI --}}
    <div class="flex items-center justify-center sm:justify-end gap-1.5 w-full sm:w-auto">
        
        {{-- Tombol: First Page --}}
        <button @click="changePage(1)" 
            :disabled="pagination.current_page === 1"
            class="px-3 py-2 text-slate-500 hover:text-[#1C7C54] disabled:opacity-40 disabled:cursor-not-allowed transition-all rounded-lg border border-slate-200 hover:border-[#1C7C54] hover:bg-emerald-50 active:bg-emerald-100 flex items-center justify-center shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30"
            title="Halaman Pertama">
            <i class="fas fa-angle-double-left text-[11px]"></i>
        </button>

        {{-- Tombol: Previous --}}
        <button @click="changePage(pagination.current_page - 1)" 
            :disabled="pagination.current_page <= 1"
            class="px-3.5 py-2 text-slate-500 hover:text-[#1C7C54] disabled:opacity-40 disabled:cursor-not-allowed transition-all rounded-lg border border-slate-200 hover:border-[#1C7C54] hover:bg-emerald-50 active:bg-emerald-100 text-xs font-bold flex items-center gap-1.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30"
            title="Halaman Sebelumnya">
            <i class="fas fa-chevron-left text-[10px]"></i> Prev
        </button>

        {{-- Info Halaman (Page X of Y) --}}
        <div class="bg-white border-y border-slate-200 text-slate-700 px-4 py-2 font-extrabold text-xs shadow-sm flex items-center gap-1 cursor-default">
            <span x-text="pagination.current_page" class="text-[#1C7C54]"></span>
            <span class="text-slate-300">/</span>
            <span x-text="pagination.last_page"></span>
        </div>

        {{-- Tombol: Next --}}
        <button @click="changePage(pagination.current_page + 1)" 
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-3.5 py-2 text-slate-500 hover:text-[#1C7C54] disabled:opacity-40 disabled:cursor-not-allowed transition-all rounded-lg border border-slate-200 hover:border-[#1C7C54] hover:bg-emerald-50 active:bg-emerald-100 text-xs font-bold flex items-center gap-1.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30"
            title="Halaman Selanjutnya">
            Next <i class="fas fa-chevron-right text-[10px]"></i>
        </button>

        {{-- Tombol: Last Page --}}
        <button @click="changePage(pagination.last_page)" 
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-2 text-slate-500 hover:text-[#1C7C54] disabled:opacity-40 disabled:cursor-not-allowed transition-all rounded-lg border border-slate-200 hover:border-[#1C7C54] hover:bg-emerald-50 active:bg-emerald-100 flex items-center justify-center shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30"
            title="Halaman Terakhir">
            <i class="fas fa-angle-double-right text-[11px]"></i>
        </button>

    </div>
</div>