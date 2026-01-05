{{-- 
    KOMPONEN: MODAL DETAIL LAPORAN
    ------------------------------
    Menampilkan detail lengkap laporan harian (Attributes, Validasi, Komentar).
    Parent State: riwayatCore (Alpine.js)
--}}

<div x-show="open" x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
    style="display: none;">

    <div @click.outside="open = false"
        class="relative w-full max-w-2xl bg-white rounded-[20px] shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-slide-up">

        {{-- Header Modal --}}
        <div class="bg-gradient-to-r from-slate-50 to-white px-6 py-5 border-b border-slate-100 flex items-start justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Detail Laporan</h3>
                <p class="text-xs text-slate-500 mt-1">ID Laporan: <span class="font-mono text-slate-400" x-text="'#'+modalData?.id"></span></p>
            </div>
            <button @click="open = false"
                class="text-slate-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-full transition-all">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Body: Scrollable Content --}}
        <div class="p-6 overflow-y-auto custom-scrollbar">
            <template x-if="modalData">
                <div class="space-y-6">

                    {{-- 1. Info Utama Card --}}
                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 relative overflow-hidden">
                        {{-- Background Decor --}}
                        <div class="absolute top-0 right-0 w-24 h-24 bg-blue-100/50 rounded-bl-[80px] -mr-4 -mt-4 pointer-events-none"></div>

                        <div class="relative z-10 grid grid-cols-2 gap-y-5 gap-x-8">
                            
                            {{-- Tanggal --}}
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Kegiatan</label>
                                <div class="flex items-center gap-2 mt-1">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <p class="text-sm font-bold text-slate-800" x-text="formatDate(modalData.tanggal_laporan)"></p>
                                </div>
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Saat Ini</label>
                                <div class="mt-1" x-html="statusBadgeHtml(modalData.status)"></div>
                            </div>

                            {{-- Kegiatan (Full Width) --}}
                            <div class="col-span-2 border-t border-slate-200/60 pt-4 mt-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kegiatan</label>
                                <p class="text-lg font-bold text-slate-800 leading-snug mt-1" x-text="modalData.jenis_kegiatan"></p>
                                <p class="text-sm text-slate-600 mt-2 leading-relaxed bg-white p-3 rounded-lg border border-slate-200/50" x-text="modalData.deskripsi_aktivitas"></p>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Statistik Grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-white border border-slate-200 p-3 rounded-xl hover:border-blue-300 transition-colors group">
                            <label class="text-[10px] text-slate-400 block mb-1 group-hover:text-blue-500">Output</label>
                            <p class="font-bold text-slate-700 text-sm truncate" x-text="modalData.output_hasil_kerja"></p>
                        </div>
                        <div class="bg-white border border-slate-200 p-3 rounded-xl hover:border-blue-300 transition-colors group">
                            <label class="text-[10px] text-slate-400 block mb-1 group-hover:text-blue-500">Volume</label>
                            <p class="font-bold text-slate-700 text-sm">
                                <span x-text="modalData.volume"></span> <span x-text="modalData.satuan" class="text-xs font-normal text-slate-500"></span>
                            </p>
                        </div>
                        <div class="bg-white border border-slate-200 p-3 rounded-xl hover:border-blue-300 transition-colors group">
                            <label class="text-[10px] text-slate-400 block mb-1 group-hover:text-blue-500">Jam</label>
                            <p class="font-bold text-slate-700 text-sm">
                                <span x-text="modalData.waktu_mulai.substring(0, 5)"></span> - <span x-text="modalData.waktu_selesai.substring(0, 5)"></span>
                            </p>
                        </div>
                        <div class="bg-white border border-slate-200 p-3 rounded-xl hover:border-blue-300 transition-colors group">
                            <label class="text-[10px] text-slate-400 block mb-1 group-hover:text-blue-500">Tipe</label>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold"
                                :class="modalData.skp_rencana_id ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-600 border border-slate-200'">
                                <span x-text="modalData.skp_rencana_id ? 'TARGET SKP' : 'NON-SKP'"></span>
                            </span>
                        </div>
                    </div>

                    {{-- 3. Lokasi & Bukti --}}
                    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between bg-slate-50 px-4 py-3 rounded-xl border border-slate-200 border-dashed">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lokasi Pengerjaan</label>
                            <div class="flex items-center gap-1.5 mt-1">
                                <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="text-sm font-medium text-slate-700 truncate max-w-[200px]" x-text="getLokasi(modalData)"></span>
                            </div>
                        </div>
                        <button @click="viewBukti(modalData.bukti)"
                            :disabled="!modalData.bukti || modalData.bukti.length === 0"
                            class="w-full sm:w-auto px-4 py-2 bg-white text-slate-700 border border-slate-300 rounded-lg text-xs font-bold hover:bg-slate-50 hover:text-blue-600 hover:border-blue-300 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            Lihat Lampiran
                        </button>
                    </div>

                    {{-- 4. Validator Info --}}
                    <div class="border-t border-slate-100 pt-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Diverifikasi oleh</p>
                                <p class="text-sm font-bold text-slate-800" x-text="modalData.validator ? modalData.validator.name : (modalData.atasan ? modalData.atasan.name : '-')"></p>
                            </div>
                        </div>

                        {{-- Komentar --}}
                        <div x-show="modalData.komentar_validasi" class="bg-amber-50 border border-amber-200 rounded-xl p-4 relative mt-2">
                            <div class="absolute -top-2 left-4 w-4 h-4 bg-amber-50 border-t border-l border-amber-200 transform rotate-45"></div>
                            <p class="text-xs font-bold text-amber-800 uppercase mb-1">Catatan Penolakan:</p>
                            <p class="text-sm text-amber-900 italic leading-relaxed">"<span x-text="modalData.komentar_validasi"></span>"</p>
                        </div>
                    </div>

                </div>
            </template>
        </div>

        {{-- Footer Action --}}
        <div class="p-5 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
            
            {{-- 
                Tombol Edit (Kondisional) 
                Hanya muncul jika:
                1. Status = Rejected/Draft
                2. Mode = 'mine' (Untuk Penilai) ATAU Role Staf (Implisit logicnya perlu disesuaikan jika staf pakai mode null)
            --}}
            <template x-if="(modalData?.status === 'rejected' || modalData?.status === 'draft') && (!filter.mode || filter.mode === 'mine')">
                <button @click="editLaporan(modalData.id)"
                    class="px-5 py-2.5 rounded-xl text-sm font-bold text-white shadow-md shadow-emerald-100 hover:shadow-lg hover:shadow-emerald-200 transition-all flex items-center gap-2"
                    :class="modalData.status === 'draft' ? 'bg-slate-600 hover:bg-slate-700' : 'bg-[#0E7A4A] hover:bg-[#0b633b]'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span x-text="modalData.status === 'draft' ? 'Lanjutkan Edit' : 'Perbaiki Laporan'"></span>
                </button>
            </template>

            <button @click="open = false"
                class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all shadow-sm">
                Tutup
            </button>
        </div>
    </div>
</div>