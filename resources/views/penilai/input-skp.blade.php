@php($title = 'Input SKP')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'skp'])

@section('content')

{{-- 
    SOLUSI FINAL: 
    Script dipindahkan langsung ke bawah file ini menggunakan 'alpine:init'.
    Ini menjamin Alpine mengenali komponen 'skpPageData' sebelum render.
--}}

<section x-data="skpPageData()" x-init="initPage()"
    class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)] gap-4 flex-1">

    {{-- KOLOM KIRI: FORM DAN DAFTAR SKP --}}
    <div class="space-y-4">

        {{-- 1. FORM INPUT SKP (CREATE) --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
            <h2 class="text-[20px] font-normal mb-4">Form Input SKP</h2>

            <form class="space-y-4" @submit.prevent="submitCreate">
                {{-- Row 1: Periode --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-normal text-[#5B687A] mb-[10px]">Periode Mulai</label>
                        <input type="date" x-model="formData.periode_mulai" required
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" />
                    </div>

                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Periode
                            Selesai</label>
                        <div class="relative">
                            <input id="periode_selesai" type="date" x-model="formData.periode_selesai" required
                                class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                            <button type="button" id="periode_selesai_btn"
                                class="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 flex items-center justify-center">
                                <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-80">
                            </button>
                        </div>

                        <label class="block text-xs font-normal text-[#5B687A] mb-[10px]">Periode Selesai</label>
                        <input type="date" x-model="formData.periode_selesai" required
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" />
                    </div>
                </div>

                {{-- Row 2: Sasaran & Indikator --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Sasaran
                            Kinerja</label>
                        <label class="block text-xs font-normal text-[#5B687A] mb-[10px]">Sasaran Kinerja</label>
                        <input type="text" x-model="formData.nama_skp" required
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                            placeholder="Contoh: Meningkatkan PAD...">
                    </div>

                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Indikator
                            Kinerja</label>
                        <label class="block text-xs font-normal text-[#5B687A] mb-[10px]">Indikator Kinerja</label>
                        <input type="text" x-model="formData.indikator" required
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                            placeholder="Contoh: Jumlah dokumen terverifikasi...">
                    </div>
                </div>

                {{-- Row 3: Rencana Aksi --}}
                <div>
                    <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Rencana
                        Aksi</label>
                    <label class="block text-xs font-normal text-[#5B687A] mb-[10px]">Rencana Aksi</label>
                    <textarea x-model="formData.rencana_aksi" rows="3" required
                        class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                        placeholder="Tulis uraian rencana aksi..."></textarea>
                </div>

                {{-- Row 4: Target & Atasan --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Target
                            (Angka)</label>
                        <label class="block text-xs font-normal text-[#5B687A] mb-[10px]">Target (Angka)</label>
                        <input type="number" x-model="formData.target" required min="1"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                            placeholder="Contoh: 12">
                    </div>

                    <div>
                        <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Atasan
                            Langsung</label>
                        <input type="text" :value="atasanName" readonly disabled
                            class="w-full rounded-[10px] border border-slate-200 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-500 cursor-not-allowed focus:outline-none">
                        <p class="text-[10px] text-gray-400 mt-1">*Sesuai struktur organisasi user saat
                            ini.</p>
                        <p class="text-[10px] text-gray-400 mt-1">*Sesuai struktur organisasi.</p>
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" @click="resetForm"
                        class="rounded-[10px] bg-[#B6241C] px-4 py-2 text-sm font-normal text-white hover:brightness-95 ring-1 ring-slate-300">
                        Reset
                    </button>
                    <button type="submit"
                        class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm font-normal text-white hover:brightness-95 disabled:opacity-50"
                        :disabled="isLoading">
                        <span x-show="!isLoading">Tambahkan SKP</span>
                        <span x-show="isLoading">Menyimpan...</span>
                    </button>
                </div>

            </form>
        </div>

        {{-- DAFTAR SKP --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
            <h2 class="text-[20px] font-normal mb-4">Daftar SKP Saya</h2>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-500 uppercase bg-slate-50/50">
                            <th class="px-3 py-3 font-medium">Periode</th>
                            <th class="px-3 py-3 font-medium">Sasaran Kinerja</th>
                            <th class="px-3 py-3 font-medium">Indikator</th>
                            <th class="px-3 py-3 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 divide-y divide-slate-100">
                        <template x-for="skp in skpList" :key="skp.id">
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-3 py-3 whitespace-nowrap text-xs">
                                    <div class="font-medium" x-text="formatDate(skp.periode_mulai)"></div>
                                    <div class="text-slate-500" x-text="formatDate(skp.periode_selesai)"></div>
                                </td>
                                <td class="px-3 py-3 font-medium text-slate-800" x-text="skp.nama_skp"></td>
                                <td class="px-3 py-3 text-slate-500" x-text="skp.indikator"></td>
                                <td class="px-3 py-3 text-center">
                                    <button @click.prevent="openDetailModal(skp)"
                                        class="rounded-[8px] bg-[#155FA6]/10 text-[#155FA6] border border-[#155FA6]/20 text-xs px-3 py-1.5 font-medium hover:bg-[#155FA6] hover:text-white transition-all">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="skpList.length === 0" style="display: none;">
                            <td colspan="4" class="px-3 py-8 text-center text-slate-400 italic">Belum ada data SKP.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SIDEBAR INFO --}}
    <div class="space-y-4 flex flex-col">
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-800">Panduan Singkat</h3>
            <div class="mt-3 space-y-2 max-h-[420px] overflow-y-auto pr-1 custom-scrollbar">
                <div class="rounded-[10px] bg-[#155FA6] px-3 py-2.5 text-white text-xs leading-snug">
                    <p class="font-semibold">Periode</p>
                    <p class="mt-[2px] opacity-90">Tentukan tanggal mulai dan selesai.</p>
                </div>
                <div class="rounded-[10px] bg-[#155FA6] px-3 py-2.5 text-white text-xs leading-snug">
                    <p class="font-semibold">Sasaran</p>
                    <p class="mt-[2px] opacity-90">Isi target kinerja utama Anda.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <div x-show="openDetail" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm p-4"
        x-transition.opacity>
        <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl" @click.outside="openDetail = false">
            <button @click="openDetail = false"
                class="absolute top-4 right-5 text-slate-400 hover:text-slate-600">&times;</button>
            <h3 class="text-lg font-semibold text-slate-800">Detail SKP</h3>

            <template x-if="detailData">
                <div class="mt-6 space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-4 border-b border-slate-100 pb-4">
                        <div><label class="text-xs text-slate-500">Mulai</label>
                            <p class="font-medium" x-text="formatDate(detailData.periode_mulai)"></p>
                        </div>
                        <div><label class="text-xs text-slate-500">Selesai</label>
                            <p class="font-medium" x-text="formatDate(detailData.periode_selesai)"></p>
                        </div>
                    </div>
                    <div><label class="text-xs text-slate-500">Sasaran</label>
                        <p x-text="detailData.nama_skp"></p>
                    </div>
                    <div><label class="text-xs text-slate-500">Indikator</label>
                        <p x-text="detailData.indikator"></p>
                    </div>
                    <div><label class="text-xs text-slate-500">Target</label>
                        <p class="font-bold" x-text="detailData.target"></p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                        <button @click="openDetail = false"
                            class="px-4 py-2 bg-slate-100 rounded text-slate-700">Tutup</button>
                        <button @click="openEditModal()" class="px-4 py-2 bg-[#155FA6] text-white rounded">Edit
                            SKP</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div x-show="openEdit" style="display: none;"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
        x-transition.opacity>
        <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl" @click.outside="openEdit = false">
            <div class="px-6 py-4 border-b bg-slate-50 flex justify-between">
                <h3 class="font-bold text-slate-800">Edit SKP</h3>
                <button @click="openEdit = false">&times;</button>
            </div>
            <form @submit.prevent="submitEdit" class="p-6 space-y-5">
                <template x-if="editData">
                    <div class="space-y-4 text-sm">
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-xs font-semibold mb-1">Mulai</label><input type="date"
                                    x-model="editData.periode_mulai" class="w-full border rounded p-2"></div>
                            <div><label class="block text-xs font-semibold mb-1">Selesai</label><input type="date"
                                    x-model="editData.periode_selesai" class="w-full border rounded p-2"></div>
                        </div>
                        <div><label class="block text-xs font-semibold mb-1">Sasaran</label><input type="text"
                                x-model="editData.nama_skp" class="w-full border rounded p-2"></div>
                        <div><label class="block text-xs font-semibold mb-1">Indikator</label><input type="text"
                                x-model="editData.indikator" class="w-full border rounded p-2"></div>
                        <div><label class="block text-xs font-semibold mb-1">Rencana Aksi</label><textarea
                                x-model="editData.rencana_aksi" class="w-full border rounded p-2" rows="2"></textarea>
                        </div>
                        <div><label class="block text-xs font-semibold mb-1">Target</label><input type="number"
                                x-model="editData.target" class="w-full border rounded p-2"></div>
                    </div>
                </template>
                <div class="pt-4 border-t flex justify-end gap-3">
                    <button type="button" @click="openEdit = false" class="px-4 py-2 border rounded">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#0F4C75] text-white rounded"
                        :disabled="isLoading">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</section>

{{-- SCRIPT INLINE AGAR TERBACA ALPINE --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('skpPageData', () => ({
        // State
        skpList: [],
        atasanName: 'Memuat...',
        isLoading: false,

        // Modal State
        openDetail: false,
        openEdit: false,
        detailData: null,
        editData: null,

        // Form
        formData: {
            nama_skp: '',
            periode_mulai: '',
            periode_selesai: '',
            indikator: '',
            rencana_aksi: '',
            target: ''
        },

        // Init
        initPage() {
            if (!localStorage.getItem('auth_token')) {
                window.location.href = '/login';
                return;
            }
            this.fetchProfile();
            this.fetchSkpList();
        },

        // API Calls
        async fetchProfile() {
            const token = localStorage.getItem('auth_token');
            try {
                const res = await fetch('/api/me', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();
                this.atasanName = json.atasan ? json.atasan.name : '- Tidak Ada Atasan -';
            } catch (e) {
                this.atasanName = 'Gagal memuat';
            }
        },

        async fetchSkpList() {
            const token = localStorage.getItem('auth_token');
            try {
                const res = await fetch('/api/skp', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();
                this.skpList = json.data || [];
            } catch (e) {
                this.skpList = [];
            }
        },

        async submitCreate() {
            this.isLoading = true;
            const token = localStorage.getItem('auth_token');
            try {
                const res = await fetch('/api/skp', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.formData)
                });
                if (res.ok) {
                    Swal.fire('Berhasil', 'SKP ditambahkan', 'success');
                    this.resetForm();
                    this.fetchSkpList();
                } else {
                    Swal.fire('Gagal', 'Periksa input anda', 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Gagal koneksi server', 'error');
            }
            this.isLoading = false;
        },

        async submitEdit() {
            this.isLoading = true;
            const token = localStorage.getItem('auth_token');
            try {
                const res = await fetch(`/api/skp/${this.editData.id}`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.editData)
                });
                if (res.ok) {
                    Swal.fire('Berhasil', 'Perubahan disimpan', 'success');
                    this.openEdit = false;
                    this.fetchSkpList();
                } else {
                    Swal.fire('Gagal', 'Gagal update data', 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Gagal koneksi server', 'error');
            }
            this.isLoading = false;
        },

        // Utilities
        resetForm() {
            this.formData = {
                nama_skp: '',
                periode_mulai: '',
                periode_selesai: '',
                indikator: '',
                rencana_aksi: '',
                target: ''
            };
        },
        openDetailModal(item) {
            this.detailData = item;
            this.openDetail = true;
        },
        openEditModal() {
            this.editData = JSON.parse(JSON.stringify(this.detailData));
            // Format date for input type=date
            if (this.editData.periode_mulai) this.editData.periode_mulai = this.editData
                .periode_mulai.substring(0, 10);
            if (this.editData.periode_selesai) this.editData.periode_selesai = this.editData
                .periode_selesai.substring(0, 10);
            this.openDetail = false;
            this.openEdit = true;
        },
        formatDate(date) {
            if (!date) return '-';
            try {
                return new Date(date).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            } catch (e) {
                return date;
            }
        }
    }));
});
</script>

@endsection