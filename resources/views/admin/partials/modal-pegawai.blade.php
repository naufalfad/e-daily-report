{{-- MODAL ADD/EDIT --}}
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
         class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        
        {{-- Header --}}
        <div class="px-8 py-6 bg-white border-b border-slate-100 flex justify-between items-center shrink-0">
            <div>
                <h2 class="text-2xl font-bold text-slate-800" x-text="openAdd ? 'Tambah Pegawai Baru' : 'Edit Data Pegawai'"></h2>
                <p class="text-slate-500 text-sm mt-1">Lengkapi informasi data diri dan jabatan pegawai</p>
            </div>
            <button type="button" @click="openAdd ? toggleAdd(false) : toggleEdit(false)" class="text-slate-400 hover:text-rose-500 transition-colors p-2 hover:bg-rose-50 rounded-lg">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        {{-- Scrollable Content --}}
        <div class="flex-1 overflow-y-auto px-8 py-6">
            <form id="pegawaiForm" @submit.prevent="submitForm">
                <template x-if="openEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Left Column: Personal Info --}}
                    <div class="space-y-5">
                        <div class="flex items-center gap-3 mb-6 pb-2 border-b border-slate-100">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <h3 class="font-bold text-slate-700">Informasi Pribadi</h3>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap</label>
                            <input type="text" x-model="form.name" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-sm outline-none"
                                placeholder="Contoh: Budi Santoso, S.Kom">
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">NIP</label>
                            <input type="text" x-model="form.nip" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-sm outline-none font-mono"
                                placeholder="19xxxxxxxxxxxxxxxx">
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Email (Opsional)</label>
                            <input type="email" x-model="form.email"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-sm outline-none"
                                placeholder="email@bapenda.go.id">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                                <input type="text" x-model="form.username" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-sm outline-none"
                                    placeholder="Username login">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                                <input type="password" x-model="form.password" :required="openAdd"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-sm outline-none"
                                    placeholder="Min. 6 karakter">
                                <p x-show="openEdit" class="text-[10px] text-slate-400 mt-1">*Kosongkan jika tidak diubah</p>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Employment Info --}}
                    <div class="space-y-5">
                        <div class="flex items-center gap-3 mb-6 pb-2 border-b border-slate-100">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                <i class="fas fa-briefcase text-sm"></i>
                            </div>
                            <h3 class="font-bold text-slate-700">Informasi Kepegawaian</h3>
                        </div>

                        {{-- Select Unit Kerja (Dinamis dari Alpine) --}}
                        <div class="form-group">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Unit Kerja</label>
                            <select x-model="form.unit_kerja_id" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-sm outline-none appearance-none bg-white">
                                <option value="">Pilih Unit Kerja</option>
                                <template x-for="uk in unitKerjaList" :key="uk.id">
                                    <option :value="uk.id" x-text="uk.nama_unit"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Select Bidang (Dinamis dependent on Unit Kerja) --}}
                        <div class="form-group">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Bidang</label>
                            <div class="relative">
                                <select x-model="form.bidang_id" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-sm outline-none appearance-none bg-white"
                                    :disabled="!form.unit_kerja_id || bidangList.length === 0">
                                    <option value="">Pilih Bidang</option>
                                    <template x-for="bid in bidangList" :key="bid.id">
                                        <option :value="bid.id" x-text="bid.nama_bidang"></option>
                                    </template>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 mt-1" x-show="bidangList.length === 0 && form.unit_kerja_id">
                                *Tidak ada data bidang di unit ini.
                            </p>
                        </div>

                        {{-- Select Jabatan (Dinamis dengan Validasi Hierarki Backend) --}}
                        <div class="form-group">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Jabatan</label>
                            <select x-model="form.jabatan_id" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-sm outline-none appearance-none bg-white">
                                <option value="">Pilih Jabatan</option>
                                <template x-for="j in jabatanList" :key="j.id">
                                    {{-- 
                                        Logic Disable: 
                                        Disable jabatan kepemimpinan (ID 1-4) JIKA sudah ada di occupied_positions bidang tersebut.
                                        Kecuali jika form.jabatan_id saat ini sama dengan j.id (agar saat edit tidak ter-disable diri sendiri).
                                    --}}
                                    <option 
                                        :value="j.id" 
                                        x-text="j.nama_jabatan + ((selectedBidangMeta && selectedBidangMeta.occupied_positions.includes(j.id) && [1,2,3,4].includes(j.id)) ? ' (Sudah Terisi)' : '')"
                                        :disabled="selectedBidangMeta && selectedBidangMeta.occupied_positions.includes(j.id) && [1,2,3,4].includes(j.id) && form.jabatan_id != j.id"
                                        :class="{'text-gray-400 bg-gray-50': selectedBidangMeta && selectedBidangMeta.occupied_positions.includes(j.id) && [1,2,3,4].includes(j.id) && form.jabatan_id != j.id}">
                                    </option>
                                </template>
                            </select>
                            {{-- Pesan Helper jika user memaksa memilih atau saat reset --}}
                            <p class="text-xs text-amber-600 mt-1 flex items-center gap-1" 
                               x-show="selectedBidangMeta && selectedBidangMeta.occupied_positions.includes(parseInt(form.jabatan_id)) && [1,2,3,4].includes(parseInt(form.jabatan_id)) && form.jabatan_id != editId">
                               <i class="fas fa-exclamation-triangle"></i> Jabatan ini sudah terisi di bidang yang dipilih.
                            </p>
                        </div>

                        {{-- Select Role --}}
                        <div class="form-group">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Role Aplikasi</label>
                            <select x-model="form.role" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-sm outline-none appearance-none bg-white">
                                <option value="">Pilih Role</option>
                                <template x-for="role in roleList" :key="role.id">
                                    <option :value="role.nama_role" x-text="role.nama_role"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Select Atasan (Dinamis based on logic) --}}
                        <div class="form-group relative">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Atasan Langsung</label>
                            <select x-model="form.atasan_id"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-sm outline-none appearance-none bg-white"
                                :disabled="isFetchingAtasan">
                                <option value="">Pilih Atasan (Opsional)</option>
                                <template x-for="p in atasanList" :key="p.id">
                                    <option :value="p.id" x-text="p.display || p.name"></option>
                                </template>
                            </select>
                            {{-- Indikator Loading --}}
                            <div x-show="isFetchingAtasan" class="absolute right-4 bottom-3 flex items-center" x-cloak>
                                <i class="fas fa-circle-notch fa-spin text-emerald-600"></i>
                            </div>
                            <p class="text-xs text-slate-400 mt-1" x-show="atasanList.length === 0 && form.unit_kerja_id && !isFetchingAtasan">
                                *Tidak ditemukan kandidat atasan yang sesuai hierarki.
                            </p>
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
            <button form="pegawaiForm" type="submit" :disabled="isLoading"
                class="px-6 py-2.5 bg-[#1C7C54] text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-600/20 hover:bg-[#166443] hover:shadow-emerald-600/30 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas" :class="isLoading ? 'fa-circle-notch fa-spin' : 'fa-save'"></i>
                <span x-text="isLoading ? 'Menyimpan...' : (openAdd ? 'Simpan Data' : 'Update Perubahan')"></span>
            </button>
        </div>
    </div>
</div>