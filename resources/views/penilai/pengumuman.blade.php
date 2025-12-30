@extends('layouts.app', ['title' => 'Pengumuman', 'role' => 'penilai', 'active' => 'pengumuman'])

@section('content')
{{-- 
    [FIX LAYOUT]: 
    - Menggunakan 'h-auto' agar tinggi container mengikuti konten.
    - 'min-h-[600px]' menjaga estetika saat data kosong/sedikit.
--}}
<section id="pengumuman-root"
    class="rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-6 flex flex-col h-auto relative">

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-[20px] font-bold text-slate-800">Papan Pengumuman</h2>
            <p class="text-sm text-slate-500 mt-1">Informasi terkini dan agenda kegiatan kantor</p>
        </div>
        
        <button id="btn-open-pengumuman" type="button"
            class="inline-flex items-center gap-2 rounded-[12px] bg-[#0E7A4A] text-white text-[13px] font-medium px-5 py-2.5 shadow-sm hover:bg-[#0b633c] hover:shadow-md transition-all active:scale-95">
            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-sm leading-none font-bold">+</span>
            <span>Buat Pengumuman Baru</span>
        </button>
    </div>

    {{-- [NEW] Filter Bar Section --}}
    <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-100">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            {{-- Search Keyword --}}
            <div class="md:col-span-5">
                <label for="filter-search" class="block text-[11px] font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Cari Pengumuman</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" id="filter-search" 
                        class="block w-full pl-9 pr-3 py-2.5 rounded-lg border border-slate-200 bg-white text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] transition-all" 
                        placeholder="Judul, isi, atau nama pembuat...">
                </div>
            </div>

            {{-- Date Range Start --}}
            <div class="md:col-span-3">
                <label for="filter-start-date" class="block text-[11px] font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Dari Tanggal</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <input type="date" id="filter-start-date" 
                        class="block w-full pl-9 pr-3 py-2.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] transition-all cursor-pointer">
                </div>
            </div>

            {{-- Date Range End --}}
            <div class="md:col-span-3">
                <label for="filter-end-date" class="block text-[11px] font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Sampai Tanggal</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <input type="date" id="filter-end-date" 
                        class="block w-full pl-9 pr-3 py-2.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] transition-all cursor-pointer">
                </div>
            </div>

            {{-- Reset Button --}}
            <div class="md:col-span-1">
                <button id="btn-reset-filter" type="button" 
                    class="w-full h-[42px] flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-rose-500 hover:border-rose-200 transition-all"
                    title="Reset Filter">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Tips Section --}}
    <div class="rounded-[20px] bg-[#1C7C54] text-white flex items-center gap-6 px-6 py-5 mb-6 shadow-lg shadow-emerald-700/20">
        <div class="hidden md:block">
            <img src="{{ asset('assets/tips.svg') }}" alt="Tips Pengumuman" class="w-[150px] h-auto object-contain drop-shadow-md">
        </div>
        <div class="flex-1">
            <div class="text-[26px] leading-snug font-semibold mb-1">Tips!</div>
            <div class="text-[14px] font-medium opacity-90">Gunakan pengumuman dengan gaya personal</div>
            <p class="text-[12px] text-white/80 mt-1 max-w-lg">
                Tambahkan emoji, gunakan bahasa yang hangat, dan buat pegawai lebih semangat setiap hari! ✨
            </p>
        </div>
    </div>

    {{-- Content Area --}}
    <div class="flex-1 relative">
        {{-- Loader --}}
        <div id="loading-indicator" class="hidden absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/70 backdrop-blur-[1px] rounded-xl h-[300px]">
            <div class="animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-[#1C7C54]"></div>
            <p class="mt-3 text-xs text-slate-500 font-bold tracking-wide">MEMUAT...</p>
        </div>

        {{-- Empty State --}}
        <div id="announcement-empty" class="hidden flex flex-col items-center justify-center py-16">
            <div class="h-24 w-24 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                <img src="{{ asset('assets/icon/pengumuman.svg') }}" alt="Empty" class="w-10 h-10 opacity-30 grayscale">
            </div>
            <p class="text-slate-800 font-semibold mb-1">Tidak ada pengumuman ditemukan</p>
            <p class="text-[13px] text-slate-500 max-w-xs text-center">
                Coba ubah kata kunci atau filter tanggal pencarian Anda.
            </p>
        </div>

        {{-- List Grid (12 Item) --}}
        <div id="announcement-list" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 pb-4">
            {{-- Cards di-inject oleh JS --}}
        </div>
    </div>

    {{-- Pagination Footer --}}
    <div class="mt-auto pt-6 border-t border-slate-50">
        <div id="pagination-container" class="flex justify-center items-center w-full">
            {{-- Skeleton Pagination --}}
            <div class="flex gap-1 animate-pulse opacity-50">
                <div class="h-9 w-20 bg-slate-100 rounded-lg"></div> 
                <div class="h-9 w-9 bg-slate-100 rounded-lg"></div>  
                <div class="h-9 w-9 bg-slate-100 rounded-lg"></div>  
                <div class="h-9 w-20 bg-slate-100 rounded-lg"></div> 
            </div>
        </div>
    </div>

</section>

{{-- Modal Form --}}
<div id="modal-pengumuman" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" id="modal-backdrop"></div>

    <div class="w-full max-w-xl rounded-[24px] bg-white shadow-2xl ring-1 ring-slate-200 px-8 py-7 relative transform transition-all scale-100 mx-4">

        <button id="btn-close-pengumuman" type="button"
            class="absolute right-5 top-5 h-8 w-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="mb-6">
            <h3 class="text-xl font-bold text-slate-800">Buat Pengumuman</h3>
            <p class="text-sm text-slate-500 mt-1">Bagikan informasi ke seluruh pegawai atau unit kerja.</p>
        </div>

        <form id="form-pengumuman" class="space-y-5">
            {{-- Input Judul --}}
            <div>
                <label for="input-judul" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Judul Pengumuman
                    <span class="text-red-500">*</span></label>
                <input id="input-judul" name="judul" type="text" required
                    class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] transition-all placeholder:text-slate-400"
                    placeholder="Contoh: Agenda Rapat Senin Pagi">
            </div>

            {{-- Input Target --}}
            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-2">Penerima Pengumuman <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative flex cursor-pointer rounded-xl border border-slate-200 bg-slate-50 p-3 focus-within:ring-2 focus-within:ring-[#1C7C54]/20 transition-all hover:bg-slate-100">
                        <input type="radio" name="target" value="umum" class="sr-only" checked>
                        <div class="flex flex-col">
                            <span class="text-[13px] font-bold text-slate-800">Seluruh Kantor</span>
                            <span class="text-[11px] text-slate-500">Pesan bersifat Umum</span>
                        </div>
                        <div class="radio-custom-marker ml-auto self-center h-4 w-4 rounded-full border-2 border-slate-300"></div>
                    </label>
                    
                    <label class="relative flex cursor-pointer rounded-xl border border-slate-200 bg-slate-50 p-3 focus-within:ring-2 focus-within:ring-[#1C7C54]/20 transition-all hover:bg-slate-100">
                        <input type="radio" name="target" value="divisi" class="sr-only">
                        <div class="flex flex-col">
                            <span class="text-[13px] font-bold text-slate-800">Divisi Saya</span>
                            <span class="text-[11px] text-slate-500">Hanya rekan satu Bidang</span>
                        </div>
                        <div class="radio-custom-marker ml-auto self-center h-4 w-4 rounded-full border-2 border-slate-300"></div>
                    </label>
                </div>
            </div>

            {{-- Input Isi --}}
            <div>
                <label for="input-isi" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Isi Pesan <span
                        class="text-red-500">*</span></label>
                <textarea id="input-isi" name="isi_pengumuman" rows="4" required
                    class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] transition-all placeholder:text-slate-400 resize-none"
                    placeholder="Tulis detail pengumuman di sini..."></textarea>
            </div>

            {{-- Preview Mini --}}
            <div class="pt-2">
                <p class="text-[12px] font-medium text-slate-500 mb-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Live Preview
                </p>
                <div class="rounded-[16px] border border-emerald-100 bg-emerald-50/30 px-5 py-4">
                    <div class="flex justify-between items-start mb-1">
                        <h4 id="preview-title" class="text-[15px] font-bold text-slate-800 truncate pr-4">Judul Pengumuman...</h4>
                        <span id="preview-scope-badge" class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-600">UMUM</span>
                    </div>
                    <p id="preview-body" class="text-[13px] text-slate-600 leading-relaxed line-clamp-2 italic">Isi pengumuman akan muncul di sini...</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-50 mt-2">
                <button id="btn-cancel-pengumuman" type="button"
                    class="rounded-[12px] bg-white border border-slate-200 px-5 py-2.5 text-[13px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button id="btn-submit-pengumuman" type="submit"
                    class="rounded-[12px] bg-[#0E7A4A] px-6 py-2.5 text-[13px] font-bold text-white hover:bg-[#0b633c] shadow-lg shadow-emerald-700/20 hover:shadow-emerald-700/30 transition-all flex items-center gap-2">
                    <span>Terbitkan</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Radio Custom Style */
    input[type="radio"]:checked + div + .radio-custom-marker {
        border-color: #1C7C54;
        background-color: #1C7C54;
        box-shadow: inset 0 0 0 3px white;
    }
    input[type="radio"]:checked ~ div span:first-child {
        color: #1C7C54;
    }
</style>
@endsection

@push('scripts')
@vite('resources/js/pages/penilai/pengumuman.js')
@endpush