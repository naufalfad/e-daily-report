{{-- MODAL UPLOAD EXCEL --}}
<div x-show="openUpload" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true">
    
    {{-- Backdrop --}}
    <div x-show="openUpload" 
         x-transition.opacity.duration.300ms
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
         @click="toggleUpload(false)"></div>

    {{-- Modal Panel --}}
    <div x-show="openUpload"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         class="relative w-full max-w-[500px] bg-white rounded-2xl shadow-2xl p-0 overflow-hidden">
        
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-800">Import Data Pegawai</h2>
            <button @click="toggleUpload(false)" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="px-8 py-8">
            <form @submit.prevent="submitImport()" class="space-y-6">
                <input type="file" x-ref="csvFile" @change="fileUpload = $event.target.files[0]" accept=".xlsx, .xls, .csv" hidden>

                {{-- Dropzone Area --}}
                <div @click="$refs.csvFile.click()" 
                     class="group w-full rounded-2xl border-2 border-dashed transition-all duration-200 px-6 py-12 flex flex-col items-center justify-center text-center cursor-pointer relative overflow-hidden"
                     :class="fileUpload ? 'border-[#1C7C54] bg-emerald-50/50' : 'border-slate-300 hover:border-[#1C7C54] hover:bg-slate-50'">
                    
                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300"
                         :class="fileUpload ? 'bg-emerald-100 text-emerald-600' : 'text-slate-400 group-hover:text-[#1C7C54] group-hover:bg-emerald-50'">
                        <i class="fas fa-cloud-upload-alt text-3xl"></i>
                    </div>

                    <div x-show="!fileUpload">
                        <p class="text-base font-bold text-slate-700">Klik untuk upload file Excel</p>
                        <p class="text-sm text-slate-400 mt-1">Format: .xlsx, .xls, atau .csv</p>
                    </div>

                    <div x-show="fileUpload" class="z-10">
                        <p class="text-sm font-bold text-[#1C7C54] bg-white/80 px-3 py-1 rounded-full shadow-sm backdrop-blur-sm"
                           x-text="fileUpload ? fileUpload.name : ''"></p>
                        <p class="text-xs text-emerald-600 mt-2 font-medium">Klik untuk ganti file</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="toggleUpload(false)" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-[#1C7C54] text-white font-bold hover:bg-[#166443] shadow-lg shadow-emerald-600/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2"
                        :disabled="!fileUpload || isImporting">
                        <span x-show="!isImporting"><i class="fas fa-file-import mr-1"></i> Mulai Import</span>
                        <span x-show="isImporting"><i class="fas fa-circle-notch fa-spin mr-1"></i> Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>