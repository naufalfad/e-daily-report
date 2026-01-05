{{-- 
    KOMPONEN: MODAL BUKTI (EVIDENCE)
    --------------------------------
    Digunakan untuk menampilkan daftar lampiran (Gambar, PDF, Video).
    Parent State: riwayatCore (Alpine.js)
--}}

<div x-show="openBukti" style="display: none;"
    class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4"
    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div class="relative w-full max-w-lg bg-white rounded-[24px] p-6 shadow-2xl" @click.outside="openBukti = false"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

        {{-- Header Modal --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Lampiran Bukti</h3>
                <p class="text-sm text-slate-500">Dokumen pendukung aktivitas</p>
            </div>
            <button @click="openBukti = false"
                class="bg-slate-100 p-2 rounded-full hover:bg-slate-200 transition-colors">
                <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Grid Daftar File --}}
        <div class="grid grid-cols-2 gap-4 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
            <template x-for="(bukti, index) in daftarBukti" :key="index">
                <div class="group relative bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden hover:border-[#155FA6] hover:shadow-md transition-all cursor-pointer"
                    @click="preview(bukti)">

                    {{-- THUMBNAIL AREA --}}
                    <div class="h-32 bg-slate-100 flex items-center justify-center overflow-hidden relative">

                        {{-- Tipe: IMAGE --}}
                        <template x-if="getFileType(bukti.file_url) === 'image'">
                            <img :src="bukti.file_url"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        </template>

                        {{-- Tipe: PDF --}}
                        <template x-if="getFileType(bukti.file_url) === 'pdf'">
                            <div class="flex flex-col items-center gap-2 text-red-500">
                                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="text-[10px] font-bold">PDF FILE</span>
                            </div>
                        </template>

                        {{-- Tipe: VIDEO --}}
                        <template x-if="getFileType(bukti.file_url) === 'video'">
                            <div class="flex flex-col items-center gap-2 text-blue-500">
                                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span class="text-[10px] font-bold">VIDEO</span>
                            </div>
                        </template>

                        {{-- Tipe: LAINNYA --}}
                        <template x-if="getFileType(bukti.file_url) === 'other'">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="text-[10px] font-bold">FILE</span>
                            </div>
                        </template>

                        {{-- Overlay Hover Effect --}}
                        <div
                            class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                            <span
                                class="bg-white/90 px-3 py-1 rounded-full text-xs font-bold text-slate-800 shadow-sm backdrop-blur-sm">Lihat</span>
                        </div>
                    </div>

                    {{-- Footer Item --}}
                    <div class="p-3 bg-white">
                        <p class="text-xs font-bold text-slate-700 truncate" x-text="'Lampiran #' + (index + 1)"></p>
                        <p class="text-[10px] text-slate-400 truncate mt-0.5" x-text="bukti.file_url.split('/').pop()">
                        </p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>