{{-- 
    KOMPONEN: MODAL PREVIEW FILE
    ----------------------------
    Menampilkan preview gambar, PDF, atau Video secara fullscreen.
    Parent State: riwayatCore (Alpine.js)
--}}

<div x-show="showPreview"
    class="fixed inset-0 bg-black/80 backdrop-blur-md z-[70] flex items-center justify-center p-4 md:p-8"
    style="display:none;" @click.self.stop="showPreview = false"
    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div class="relative bg-white rounded-2xl overflow-hidden shadow-2xl max-w-4xl w-full flex flex-col max-h-[90vh]">
        
        {{-- Header Preview --}}
        <div class="flex items-center justify-between px-4 py-3 bg-slate-900 text-white shrink-0">
            <span class="text-sm font-medium truncate opacity-90" x-text="selectedBukti ? selectedBukti.file_url.split('/').pop() : 'Preview'"></span>
            <button @click.stop="showPreview = false" class="text-white/70 hover:text-white p-1 rounded-md hover:bg-white/10 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Content Preview --}}
        <div class="flex-1 bg-slate-100 overflow-y-auto flex items-center justify-center p-4">
            
            {{-- IMAGE --}}
            <template x-if="selectedBukti && getFileType(selectedBukti.file_url) === 'image'">
                <img :src="selectedBukti.file_url" class="max-w-full max-h-full rounded shadow-lg object-contain" />
            </template>

            {{-- PDF --}}
            <template x-if="selectedBukti && getFileType(selectedBukti.file_url) === 'pdf'">
                <iframe :src="selectedBukti.file_url" class="w-full h-full min-h-[500px] rounded-lg shadow border border-slate-300"></iframe>
            </template>

            {{-- VIDEO --}}
            <template x-if="selectedBukti && getFileType(selectedBukti.file_url) === 'video'">
                <video controls class="max-w-full max-h-full rounded-lg shadow-lg bg-black">
                    <source :src="selectedBukti.file_url" type="video/mp4">
                    Browser tidak support video.
                </video>
            </template>

            {{-- OTHER (DOWNLOAD ONLY) --}}
            <template x-if="selectedBukti && getFileType(selectedBukti.file_url) === 'other'">
                <div class="text-center">
                    <div class="w-20 h-20 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="text-slate-600 font-medium mb-4">File ini tidak dapat dipreview.</p>
                    <a :href="selectedBukti.file_url" target="_blank"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#155FA6] text-white rounded-xl font-bold hover:bg-[#0f4a85] transition shadow-lg shadow-blue-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download File
                    </a>
                </div>
            </template>
        </div>
    </div>
</div>