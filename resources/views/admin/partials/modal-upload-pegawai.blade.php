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
         class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl p-0 overflow-hidden flex flex-col max-h-[90vh]">
        
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-800">Import Data Pegawai</h2>
            <button @click="toggleUpload(false)" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        {{-- Modal Upload Data Pegawai --}}
<div x-show="openUpload" style="display: none;" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
    x-cloak>
    
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden transform transition-all"
         @click.away="toggleUpload(false)">
        
        {{-- Header Modal --}}
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Import Data Pegawai</h3>
                <p class="text-xs text-slate-500 mt-0.5">Upload file CSV/Excel sesuai template.</p>
            </div>
            <button @click="toggleUpload(false)" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Form Upload --}}
            {{-- [CRITICAL FIX]: Menggunakan 'submitImport' agar match dengan JS --}}
                <form @submit.prevent="submitImport" class="p-6">
                    {{-- Area Dropzone / Input --}}
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih File</label>
                        <div class="relative group">
                            <input type="file" id="file_import" 
                                @change="handleFileUpload" 
                                accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                class="block w-full text-sm text-slate-500
                                        file:mr-4 file:py-2.5 file:px-4
                                        file:rounded-xl file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-emerald-50 file:text-emerald-700
                                        hover:file:bg-emerald-100
                                        border border-slate-200 rounded-xl cursor-pointer
                                        focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500
                                        transition-all">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2">
                            Format yang didukung: .xlsx, .xls, .csv (Maks. 10MB). 
                            <br>Pastikan header kolom sesuai template.
                        </p>
                    </div>

                    {{-- Footer / Action Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="toggleUpload(false)" 
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-all">
                            Batal
                        </button>
                        <button type="submit" 
                            :disabled="isImporting || !fileUpload"
                            class="flex items-center gap-2 px-6 py-2.5 bg-[#1C7C54] text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-700/20 hover:bg-[#166443] hover:shadow-emerald-700/30 transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            
                            <template x-if="isImporting">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <template x-if="!isImporting">
                                <i class="fas fa-upload"></i>
                            </template>
                            
                            <span x-text="isImporting ? 'Memproses...' : 'Upload & Proses'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>