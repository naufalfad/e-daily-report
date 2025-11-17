@php($title = 'Pengumuman')
@extends('layouts.app', ['title' => $title, 'role' => 'penilai', 'active' => 'pengumuman'])

@php
if (!isset($announcements) || !is_array($announcements)) {
$announcements = [
[
'judul' => 'Hari Batik Nasional!',
'isi' => 'Dalam rangka memperingati Hari Batik Nasional, seluruh pegawai diharapkan mengenakan batik pada hari Jumat
besok.',
'tanggal' => 'Diumumkan 9 November 2025',
],
[
'judul' => 'Isi Laporan Harian!',
'isi' => 'Pastikan laporan kegiatan harian diisi sebelum pukul 16.00 agar rekap berjalan lancar dan tepat waktu.',
'tanggal' => 'Diumumkan 8 November 2025',
],
[
'judul' => 'Selamat Datang Pegawai Baru!',
'isi' => 'Mari sambut rekan-rekan baru kita dengan senyum hangat dan kerja sama yang solid! 😊',
'tanggal' => 'Diumumkan 7 November 2025',
],
];
}

$hasAnnouncements = is_countable($announcements) && count($announcements) > 0;
@endphp

@section('content')
<section id="pengumuman-root" class="rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5 flex flex-col h-full">

        <h2 class="text-[18px] font-normal mb-4">Pengumuman</h2>

        <div class="rounded-[20px] bg-[#1C7C54] text-white flex items-center gap-6 px-6 py-5 mb-4">
                <div class="hidden md:block">
                        <img src="{{ asset('assets/illustration/pengumuman-tips.png') }}" alt="Tips Pengumuman"        
                        class="w-[150px] h-auto object-contain">
                    </div>
                <div class="flex-1">
                        <div class="text-[26px] leading-snug font-semibold mb-1">Tips!</div>
                        <div class="text-[14px] font-medium">Gunakan pengumuman dengan gaya personal</div>
                        <p class="text-[12px] text-white/90 mt-1">
                                Tambahkan emoji, gunakan bahasa yang hangat, dan buat pegawai lebih semangat setiap
                hari! ✨
                            </p>
                    </div>
            </div>

        <div class="mb-4">
                <button id="btn-open-pengumuman" type="button"            
            class="inline-flex items-center gap-2 rounded-[10px] bg-[#0E7A4A] text-white text-[13px] px-4 py-2 shadow-sm hover:brightness-95">
                        <span                
                class="flex h-5 w-5 items-center justify-center rounded-full bg-white/15 text-sm leading-none">+</span>
                        <span>Buat Pengumuman Baru</span>
                    </button>
            </div>

        <div id="announcement-empty"        
        class="flex-1 flex flex-col items-center justify-center gap-3 {{ $hasAnnouncements ? 'hidden' : '' }}">
                <img src="{{ asset('assets/icon/announcement-empty.png') }}" alt="Belum ada pengumuman"            
            class="w-[120px] h-[120px] object-contain">
                <p class="text-[13px] text-slate-500 text-center">
                        Anda belum membuat pengumuman<br>
                        <span class="font-medium text-slate-700">Tambah Pengumuman?</span>
                    </p>
            </div>

        <div id="announcement-list"
        class="{{ $hasAnnouncements ? 'grid' : 'hidden' }} grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($announcements as $ann)
                <article class="rounded-[18px] border border-[#BFD4FF] bg-[#F4F8FF] px-5 py-4 shadow-sm">
                        <h3 class="text-[14px] font-semibold text-slate-800 mb-1">
                                {{ $ann['judul'] ?? '-' }}
                            </h3>
                        <p class="text-[12px] text-slate-700 leading-snug mb-4">
                                {{ $ann['isi'] ?? '' }}
                            </p>
                        <p class="text-[11px] text-slate-400">
                                {{ $ann['tanggal'] ?? '' }}
                            </p>
                    </article>
                @endforeach
            </div>
</section>

<div id="modal-pengumuman"    
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="w-full max-w-xl rounded-2xl bg-white shadow-xl ring-1 ring-slate-200 px-6 py-5 relative">

                <button id="btn-close-pengumuman" type="button"            
            class="absolute right-4 top-4 h-8 w-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <span class="text-lg">&times;</span>
                    </button>

                <h3 class="text-base md:text-lg font-semibold text-slate-800 mb-4">Buat Pengumuman</h3>

                <form class="space-y-4">
                        <div>
                                <label for="input-judul" class="block text-[12px] text-slate-600 mb-1">Judul</label>
                                <input id="input-judul" type="text"                    
                    class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                                        placeholder="Judul pengumuman">
                            </div>

                        <div>
                                <label for="input-isi" class="block text-[12px] text-slate-600 mb-1">Isi
                    Pengumuman</label>
                                <textarea id="input-isi" rows="4"                    
                    class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                                        placeholder="Masukkan isi pengumuman"></textarea>
                            </div>

                        <div>
                                <p class="text-[12px] text-slate-500 mb-2">Preview</p>
                                <div class="rounded-[16px] border border-[#BFD4FF] bg-[#F4F8FF] px-4 py-4">
                                        <h4 id="preview-title" class="text-[14px] font-semibold text-slate-800 mb-1">
                                                Judul akan tampil di sini
                                            </h4>
                                        <p id="preview-body" class="text-[12px] text-slate-700 leading-snug mb-3">
                                                Isi pengumuman
                                            </p>
                                        <p id="preview-date" class="text-[11px] text-slate-400">
                                                Diumumkan pada tanggal -
                                            </p>
                                    </div>
                            </div>

                        <div class="flex justify-end gap-2 pt-1">
                                <button id="btn-cancel-pengumuman" type="button"                    
                    class="rounded-[10px] bg-slate-200 px-4 py-2 text-[13px] text-slate-700 hover:brightness-95">
                                        Batalkan
                                    </button>
                                <button id="btn-submit-pengumuman" type="submit"                    
                    class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-[13px] text-white hover:brightness-95">
                                        Terbitkan
                                    </button>
                            </div>
                    </form>
            </div>
</div>
@endsection

@push('scripts')
<script>
window.PENILAI_PENGUMUMAN_DATA = @json($announcements);
</script>
@endpush