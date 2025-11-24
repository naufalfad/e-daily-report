@extends('layouts.app', ['title' => 'Pengumuman', 'role' => 'penilai', 'active' => 'pengumuman'])

@section('content')
<section id="pengumuman-root" class="rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5 flex flex-col h-full min-h-[500px]">

    <h2 class="text-[18px] font-normal mb-4">Pengumuman</h2>

    {{-- Tips Section --}}
    <div class="rounded-[20px] bg-[#1C7C54] text-white flex items-center gap-6 px-6 py-5 mb-4">
        <div class="hidden md:block">
            <img src="{{ asset('assets/tips.svg') }}" alt="Tips Pengumuman" class="w-[150px] h-auto object-contain">
        </div>
        <div class="flex-1">
            <div class="text-[26px] leading-snug font-semibold mb-1">Tips!</div>
            <div class="text-[14px] font-medium">Gunakan pengumuman dengan gaya personal</div>
            <p class="text-[12px] text-white/90 mt-1">
                Tambahkan emoji, gunakan bahasa yang hangat, dan buat pegawai lebih semangat setiap hari! ✨
            </p>
        </div>
    </div>

    {{-- Action Button --}}
    <div class="mb-4">
        <button id="btn-open-pengumuman" type="button"
            class="inline-flex items-center gap-2 rounded-[10px] bg-[#0E7A4A] text-white text-[13px] px-4 py-2 shadow-sm hover:brightness-95 transition-all active:scale-95">
            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/15 text-sm leading-none">+</span>
            <span>Buat Pengumuman Baru</span>
        </button>
    </div>

    {{-- STATE 1: LOADING (Optional, bisa dihandle JS) --}}
    <div id="loading-indicator" class="hidden text-center py-10">
        <span class="loading-spinner text-[#1C7C54]">⏳ Memuat data...</span>
    </div>

    {{-- STATE 2: KOSONG --}}
    <div id="announcement-empty" class="hidden flex-1 flex flex-col items-center justify-center gap-3 py-10">
        <img src="{{ asset('assets/icon/announcement-empty.png') }}" alt="Belum ada pengumuman"
            class="w-[120px] h-[120px] object-contain opacity-80">
        <p class="text-[13px] text-slate-500 text-center">
            Belum ada pengumuman yang diterbitkan.<br>
            <span class="font-medium text-slate-700">Jadilah yang pertama membuat!</span>
        </p>
    </div>

    {{-- STATE 3: LIST PENGUMUMAN (Container untuk JS) --}}
    <div id="announcement-list" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Cards akan di-inject oleh JavaScript di sini --}}
    </div>

</section>

{{-- Modal Form --}}
<div id="modal-pengumuman" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4 transition-opacity">
    <div class="w-full max-w-xl rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 px-6 py-5 relative animate-fade-in-up">
        <button id="btn-close-pengumuman" type="button"
            class="absolute right-4 top-4 h-8 w-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
            <span class="text-lg">&times;</span>
        </button>

        <h3 class="text-base md:text-lg font-semibold text-slate-800 mb-4">Buat Pengumuman</h3>

        <form class="space-y-4">
            <div>
                <label for="input-judul" class="block text-[12px] text-slate-600 mb-1">Judul <span class="text-red-500">*</span></label>
                <input id="input-judul" type="text"
                    class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] transition-all"
                    placeholder="Contoh: Agenda Rapat Senin">
            </div>

            <div>
                <label for="input-isi" class="block text-[12px] text-slate-600 mb-1">Isi Pengumuman <span class="text-red-500">*</span></label>
                <textarea id="input-isi" rows="4"
                    class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] transition-all"
                    placeholder="Tulis detail pengumuman di sini..."></textarea>
            </div>

            {{-- Preview Mini --}}
            <div>
                <p class="text-[12px] text-slate-500 mb-2">Live Preview</p>
                <div class="rounded-[16px] border border-[#BFD4FF] bg-[#F4F8FF] px-4 py-4 opacity-80">
                    <h4 id="preview-title" class="text-[14px] font-semibold text-slate-800 mb-1">Judul...</h4>
                    <p id="preview-body" class="text-[12px] text-slate-700 leading-snug mb-3">Isi...</p>
                    <p id="preview-date" class="text-[11px] text-slate-400">Baru saja</p>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-1">
                <button id="btn-cancel-pengumuman" type="button"
                    class="rounded-[10px] bg-slate-100 px-4 py-2 text-[13px] text-slate-600 hover:bg-slate-200 transition-colors">
                    Batal
                </button>
                <button id="btn-submit-pengumuman" type="submit"
                    class="rounded-[10px] bg-[#0E7A4A] px-6 py-2 text-[13px] font-medium text-white hover:bg-[#0b633c] shadow-md hover:shadow-lg transition-all">
                    🚀 Terbitkan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

{{-- Load Script Khusus Penilai --}}
@push('scripts')
    @vite(['resources/js/pages/penilai/pengumuman.js'])
@endpush