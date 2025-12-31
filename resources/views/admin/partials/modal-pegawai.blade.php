{{-- MODAL ADD/EDIT --}}
{{-- Gunakan x-if agar elemen tidak berat di DOM saat hidden, TAPI x-show lebih smooth transisinya. Kita stick to x-show. --}}
<div x-show="openAdd || openEdit" 
     x-cloak 
     class="fixed inset-0 z-[70] flex items-center justify-center p-4 overflow-y-auto" 
     role="dialog" 
     aria-modal="true">
    
    {{-- Backdrop --}}
    <div x-show="openAdd || openEdit" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
         @click="openAdd ? toggleAdd(false) : toggleEdit(false)"></div>

    {{-- Modal Panel --}}
    <div x-show="openAdd || openEdit"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl flex flex-col relative z-10 overflow-hidden border border-slate-100 max-h-[90vh]"
         @click.stop> {{-- [PENTING] Mencegah klik di panel menutup modal --}}
        
        {{-- Header --}}
        <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-white shrink-0">
            <div>
                <h3 class="text-xl font-bold text-slate-800" x-text="openEdit ? 'Edit Data Pegawai' : 'Tambah Pegawai Baru'"></h3>
                <p class="text-sm text-slate-500 mt-0.5">Lengkapi informasi HR dan penempatan jabatan.</p>
            </div>
            <button type="button" @click="openAdd ? toggleAdd(false) : toggleEdit(false)"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-8 py-6 overflow-y-auto custom-scrollbar flex-1">
            <form @submit.prevent="submitForm(openEdit ? 'edit' : 'add')" id="pegawaiForm" class="space-y-6">
                
                {{-- Alert Info --}}
                <div x-show="openAdd" class="p-4 rounded-xl bg-blue-50 border border-blue-100 flex gap-3 items-start">
                    <div class="text-blue-600 mt-0.5"><i class="fas fa-info-circle text-lg"></i></div>
                    <div>
                        <h4 class="text-sm font-bold text-blue-800">Informasi Akun Otomatis</h4>
                        <p class="text-sm text-blue-700 mt-1 leading-relaxed">
                            Sistem akan otomatis membuat akun login dengan <strong>Username</strong> dan <strong>Password</strong> sesuai <span class="font-mono bg-blue-100 px-1 rounded">NIP</span>.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Data Diri --}}
                    <div class="col-span-2 md:col-span-1">
                        <label class="modern-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.name" class="modern-input" placeholder="Contoh: Budi Santoso, S.Kom">
                    </div>
                    
                    <div class="col-span-2 md:col-span-1">
                        <label class="modern-label">NIP <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.nip" class="modern-input font-mono" placeholder="19xxxxxxxxxxxxxx">
                    </div>

                    <div class="col-span-2 border-t border-dashed border-slate-200 my-1"></div>

                    {{-- Unit Kerja --}}
                    <div class="col-span-2 md:col-span-1">
                        <label class="modern-label">Unit Kerja <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select x-model="formData.unit_kerja_id" class="modern-input custom-select cursor-pointer">
                                <option value="">-- Pilih Unit Kerja --</option>
                                <template x-for="u in unitKerjaList" :key="u.id">
                                    <option :value="u.id" x-text="u.nama_unit"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Jabatan --}}
                    <div class="col-span-2 md:col-span-1">
                        <label class="modern-label">Jabatan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select x-model="formData.jabatan_id" class="modern-input custom-select cursor-pointer bg-slate-50" :disabled="!formData.unit_kerja_id">
                                <option value="">-- Pilih Jabatan --</option>
                                <template x-for="j in jabatanList" :key="j.id">
                                    <option :value="j.id" x-text="j.nama_jabatan"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Bidang --}}
                    <div class="col-span-2">
                        <label class="modern-label">Bidang / Bagian</label>
                        <div class="relative">
                            <select x-model="formData.bidang_id" class="modern-input custom-select cursor-pointer bg-slate-50" :disabled="!formData.unit_kerja_id">
                                <option value="">-- Pilih Bidang --</option>
                                <template x-for="b in bidangList" :key="b.id">
                                    <option :value="b.id" x-text="b.nama_bidang"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Atasan --}}
                    <div class="col-span-2 bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                        <label class="modern-label text-emerald-700">Atasan Langsung</label>
                        <div class="relative mt-2">
                            <select x-model="formData.atasan_id" class="modern-input custom-select cursor-pointer border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500/20" :disabled="isFetchingAtasan">
                                <option value="">-- Pilih Atasan --</option>
                                <template x-for="a in atasanList" :key="a.id">
                                    <option :value="a.id" x-text="a.name + ' (' + (a.jabatan?.nama_jabatan ?? '-') + ')'"></option>
                                </template>
                            </select>
                            <div x-show="isFetchingAtasan" class="absolute right-10 top-3">
                                <i class="fas fa-circle-notch fa-spin text-emerald-600"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="px-8 py-5 bg-slate-50 border-t border-slate-200 flex justify-between items-center shrink-0">
            <button type="button" @click="openAdd ? toggleAdd(false) : toggleEdit(false)"
                class="text-sm font-bold text-slate-500 hover:text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-200/50 transition-colors">
                Batal
            </button>
            <button form="pegawaiForm" type="submit"
                class="px-6 py-2.5 bg-[#1C7C54] text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-600/20 hover:bg-[#166443] hover:shadow-emerald-600/30 transition-all transform active:scale-95 flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span>Simpan Data</span>
            </button>
        </div>
    </div>
</div>