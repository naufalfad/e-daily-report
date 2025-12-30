@extends('layouts.app', ['title' => 'Pengumuman', 'role' => 'kadis', 'active' => 'pengumuman'])

@section('content')
<section id="pengumuman-root" class="rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5 flex flex-col h-full min-h-[500px]">

    <h2 class="text-[18px] font-normal mb-4">Pengumuman</h2>

    {{-- Tips Section --}}
    <div class="rounded-[20px] bg-[#1C7C54] text-white flex items-center gap-6 px-6 py-5 mb-4 shadow-lg shadow-emerald-700/20">
        <div class="hidden md:block">
            <img src="{{ asset('assets/tips.svg') }}" alt="Tips Pengumuman" class="w-[150px] h-auto object-contain drop-shadow-md">
        </div>
        <div class="flex-1">
            <div class="text-[26px] leading-snug font-semibold mb-1">Otoritas Kepala Badan</div>
            <div class="text-[14px] font-medium opacity-90">Sampaikan instruksi strategis ke bidang terkait</div>
            <p class="text-[12px] text-white/80 mt-1 max-w-lg">
                Sebagai Kepala, Anda dapat mengirimkan pengumuman secara global ke seluruh kantor atau spesifik ke bidang tertentu. ✨
            </p>
        </div>
    </div>

    {{-- Action Button --}}
    <div class="mb-4 flex justify-between items-center">
        <button id="btn-open-pengumuman" type="button"
            class="inline-flex items-center gap-2 rounded-[12px] bg-[#0E7A4A] text-white text-[13px] font-medium px-5 py-2.5 shadow-sm hover:bg-[#0b633c] hover:shadow-md transition-all active:scale-95">
            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-sm leading-none font-bold">+</span>
            <span>Buat Pengumuman Baru</span>
        </button>
    </div>

    {{-- STATE 1: LOADING --}}
    <div id="loading-indicator" class="hidden text-center py-20">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-[#1C7C54]"></div>
        <p class="mt-2 text-xs text-slate-400 font-medium">Sedang memuat data...</p>
    </div>

    {{-- STATE 2: KOSONG --}}
    <div id="announcement-empty" class="hidden flex-1 flex flex-col items-center justify-center gap-4 py-16">
        <div class="h-24 w-24 bg-slate-50 rounded-full flex items-center justify-center mb-2">
            <img src="{{ asset('assets/icon/pengumuman.svg') }}" alt="Empty" class="w-10 h-10 opacity-30 grayscale">
        </div>
        <div class="text-center">
            <p class="text-slate-800 font-semibold mb-1">Belum ada pengumuman</p>
            <p class="text-[13px] text-slate-500 max-w-xs mx-auto">
                Riwayat instruksi Anda akan muncul di sini.
            </p>
        </div>
    </div>

    {{-- STATE 3: LIST PENGUMUMAN --}}
    <div id="announcement-list" class="hidden grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        {{-- Cards akan di-inject oleh JavaScript --}}
    </div>
    
    {{-- Pagination Container --}}
    <div id="pagination-container" class="mt-auto pt-6 flex justify-center"></div>

</section>

{{-- Modal Form --}}
<div id="modal-pengumuman" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" id="modal-backdrop"></div>
    
    <div class="w-full max-w-xl rounded-[24px] bg-white shadow-2xl ring-1 ring-slate-200 px-8 py-7 relative transform transition-all scale-100 mx-4">
        
        <button id="btn-close-pengumuman" type="button"
            class="absolute right-5 top-5 h-8 w-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="mb-6">
            <h3 class="text-xl font-bold text-slate-800">Buat Pengumuman Strategis</h3>
            <p class="text-sm text-slate-500 mt-1">Pilih target penerima untuk mendistribusikan informasi.</p>
        </div>

        <form id="form-pengumuman" class="space-y-5">
            {{-- Input Judul --}}
            <div>
                <label for="input-judul" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Judul Pengumuman <span class="text-red-500">*</span></label>
                <input id="input-judul" name="judul" type="text" required
                    class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] transition-all placeholder:text-slate-400"
                    placeholder="Contoh: Arahan Percepatan Target Pajak">
            </div>

            {{-- Input Target Bidang (Khusus Kadis) --}}
            <div>
                <label for="select-target-bidang" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Kirim Ke <span class="text-red-500">*</span></label>
                <select id="select-target-bidang" name="target_bidang_id" required
                    class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] transition-all appearance-none cursor-pointer">
                    <option value="umum">Seluruh Kantor (Umum)</option>
                    <optgroup label="Spesifik Bidang / Divisi">
                        @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}">{{ $bidang->nama_bidang }}</option>
                        @endforeach
                    </optgroup>
                </select>
                <p class="text-[11px] text-slate-400 mt-1.5 italic">* Jika memilih bidang spesifik, hanya pegawai di bidang tersebut yang akan menerima notifikasi.</p>
            </div>

            {{-- Input Isi --}}
            <div>
                <label for="input-isi" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Isi Pesan <span class="text-red-500">*</span></label>
                <textarea id="input-isi" name="isi_pengumuman" rows="4" required
                    class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] transition-all placeholder:text-slate-400 resize-none"
                    placeholder="Tulis detail arahan Anda di sini..."></textarea>
            </div>

            {{-- Preview Mini --}}
            <div class="pt-2">
                <p class="text-[12px] font-medium text-slate-500 mb-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Live Preview
                </p>
                <div class="rounded-[16px] border border-emerald-100 bg-emerald-50/30 px-5 py-4">
                    <div class="flex justify-between items-start mb-1">
                        <h4 id="preview-title" class="text-[15px] font-bold text-slate-800 truncate pr-4">Judul Pengumuman...</h4>
                        <span id="preview-scope-badge" class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-600">UMUM</span>
                    </div>
                    <p id="preview-body" class="text-[13px] text-slate-600 leading-relaxed line-clamp-2 italic">Isi arahan akan muncul di sini...</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-50 mt-2">
                <button id="btn-cancel-pengumuman" type="button"
                    class="rounded-[12px] bg-white border border-slate-200 px-5 py-2.5 text-[13px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button id="btn-submit-pengumuman" type="submit"
                    class="rounded-[12px] bg-[#0E7A4A] px-6 py-2.5 text-[13px] font-bold text-white hover:bg-[#0b633c] shadow-lg shadow-emerald-700/20 hover:shadow-emerald-700/30 transition-all flex items-center gap-2">
                    <span>Terbitkan Instruksi</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/kadis/pengumuman.js')
@endpush