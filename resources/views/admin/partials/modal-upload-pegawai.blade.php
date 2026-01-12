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

        <div class="p-8 overflow-y-auto">
            <form id="uploadForm" @submit.prevent="submitUpload" enctype="multipart/form-data">
                @csrf
                
                {{-- Step 1: Download Template --}}
                <div class="mb-8 p-5 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                            <span class="font-bold">1</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-700 mb-1">Download Template</h3>
                            <p class="text-sm text-slate-500 mb-4">Gunakan template ini untuk mengisi data pegawai. Jangan ubah header kolom.</p>
                            <a href="{{ asset('assets/template/template_import_user.csv') }}" class="inline-flex items-center gap-2 text-sm font-bold text-blue-600 hover:text-blue-700 hover:underline">
                                <i class="fas fa-download"></i> Download Template CSV
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Upload File --}}
                <div class="mb-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                            <span class="font-bold">2</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-700">Upload File</h3>
                            <p class="text-sm text-slate-500">Pilih file CSV yang sudah diisi data.</p>
                        </div>
                    </div>

                    <div class="relative group cursor-pointer">
                        <input type="file" name="file" accept=".xlsx, .xls, .csv" 
                            @change="handleFileUpload($event)"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                        
                        <div class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center group-hover:border-emerald-400 group-hover:bg-emerald-50/30 transition-all">
                            <div x-show="!fileUpload">
                                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400 group-hover:text-emerald-500 group-hover:bg-emerald-100 transition-colors">
                                    <i class="fas fa-cloud-upload-alt text-xl"></i>
                                </div>
                                <p class="font-medium text-slate-600">Klik atau drag file ke sini</p>
                                <p class="text-sm text-slate-400 mt-1">Format: .xlsx, .xls, atau .csv</p>
                            </div>

                            <div x-show="fileUpload" class="z-10">
                                <p class="text-sm font-bold text-[#1C7C54] bg-white/80 px-3 py-1 rounded-full shadow-sm backdrop-blur-sm"
                                   x-text="fileUpload ? fileUpload.name : ''"></p>
                                <p class="text-xs text-emerald-600 mt-2 font-medium">Klik untuk ganti file</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Reference Accordion (NEW FEATURE) --}}
                <div class="mb-6 border rounded-xl overflow-hidden border-slate-200" x-data="{ showRef: false }">
                    <button type="button" @click="showRef = !showRef" class="w-full px-5 py-3 bg-slate-50 flex justify-between items-center text-sm font-semibold text-slate-600 hover:bg-slate-100">
                        <span><i class="fas fa-info-circle mr-2 text-blue-500"></i> Referensi ID Bidang (Untuk Pengisian Excel)</span>
                        <i class="fas fa-chevron-down transition-transform duration-300" :class="{'rotate-180': showRef}"></i>
                    </button>
                    <div x-show="showRef" x-collapse class="p-5 bg-white max-h-60 overflow-y-auto">
                        <table class="w-full text-xs text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2">ID</th>
                                    <th class="py-2">Nama Unit/Bidang</th>
                                    <th class="py-2">Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Loop Data Bidang Hierarki untuk Referensi --}}
                                @foreach($bidang as $parent)
                                    <tr class="bg-gray-50 font-bold">
                                        <td class="py-1 border-b">{{ $parent->id }}</td>
                                        <td class="py-1 border-b">{{ $parent->nama_bidang }}</td>
                                        <td class="py-1 border-b text-blue-600">INDUK</td>
                                    </tr>
                                    @if($parent->children)
                                        @foreach($parent->children as $child)
                                            <tr>
                                                <td class="py-1 border-b pl-2">{{ $child->id }}</td>
                                                <td class="py-1 border-b pl-4 text-slate-500">↳ {{ $child->nama_bidang }}</td>
                                                <td class="py-1 border-b text-emerald-600">ANAK</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
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
                        <span x-show="isImporting" class="flex items-center gap-2">
                            <i class="fas fa-circle-notch fa-spin"></i> Memproses...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>