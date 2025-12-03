@php($title = 'Input SKP')
@extends('layouts.app', ['title' => $title, 'role' => 'penilai', 'active' => 'skp'])

@section('content')

<section 
    x-data="skpPageData()" 
    x-init="initPage()"
    class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)] gap-4 flex-1"
>

    {{-- ============================================
        KOLOM KIRI: FORM INPUT + DAFTAR SKP PENILAI
    ============================================ --}}
    <div class="space-y-4">

        {{-- ============================================
            FORM INPUT SKP (PREMIUM – SAMA SEPERTI STAF)
        ============================================ --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
            <h2 class="text-[20px] font-normal mb-4">Form Input SKP</h2>

            {{-- Menggunakan submitCreate dari Alpine --}}
            <form class="space-y-4" @submit.prevent="submitCreate">

                {{-- Row 1: Periode --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[15px] text-[#5B687A] mb-2">Periode Mulai</label>
                        <input type="date" x-model="formData.periode_mulai" required
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 
                            px-3.5 py-2.5 text-sm focus:outline-none 
                            focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" />
                    </div>

                    <div>
                        <label class="block text-[15px] text-[#5B687A] mb-2">Periode Selesai</label>
                        <input type="date" x-model="formData.periode_selesai" required
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 
                            px-3.5 py-2.5 text-sm focus:outline-none 
                            focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" />
                    </div>
                </div>

                {{-- Row 2: Sasaran Kinerja + Indikator Kinerja --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[15px] text-[#5B687A] mb-2">Sasaran Kinerja</label>
                        <input type="text" x-model="formData.nama_skp" required
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 
                            px-3.5 py-2.5 text-sm focus:outline-none 
                            focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                            placeholder="Contoh: Meningkatkan PAD..." />
                    </div>

                    <div>
                        <label class="block text-[15px] text-[#5B687A] mb-2">Indikator Kinerja</label>
                        <input type="text" x-model="formData.indikator" required
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 
                            px-3.5 py-2.5 text-sm focus:outline-none 
                            focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                            placeholder="Contoh: Jumlah dokumen terverifikasi..." />
                    </div>
                </div>

                {{-- Row 3: Rencana Aksi --}}
                <div>
                    <label class="block text-[15px] text-[#5B687A] mb-2">Rencana Aksi</label>
                    <textarea x-model="formData.rencana_aksi" rows="3" required
                        class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 
                        px-3.5 py-2.5 text-sm resize-none focus:outline-none 
                        focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                        placeholder="Tulis uraian rencana aksi..."></textarea>
                </div>

                {{-- Row 4: Target Kuantitas & Atasan --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[15px] text-[#5B687A] mb-2">Target (Angka)</label>
                        <input type="number" x-model="formData.target" required min="1"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 
                            px-3.5 py-2.5 text-sm focus:outline-none 
                            focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                            placeholder="Contoh: 12" />
                    </div>

                    <div>
                        <label class="block text-[15px] text-[#5B687A] mb-2">Atasan Langsung</label>
                        <input type="text" :value="atasanName" readonly disabled
                            class="w-full rounded-[10px] border border-slate-200 bg-gray-100 
                            px-3.5 py-2.5 text-sm text-gray-500 cursor-not-allowed" />
                        <p class="text-xs text-gray-400 mt-1">*Sesuai struktur organisasi.</p>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="button" @click="resetForm"
                        class="rounded-[10px] bg-slate-100 px-4 py-2 text-sm text-slate-700 
                        hover:bg-slate-200 ring-1 ring-slate-300">
                        Reset
                    </button>

                    <button type="submit"
                        class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm text-white 
                        hover:brightness-95 disabled:opacity-50"
                        :disabled="isLoading">
                        <span x-show="!isLoading">Tambahkan SKP</span>
                        <span x-show="isLoading">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ============================================
            DAFTAR SKP (PREMIUM SEPERTI STAF)
        ============================================ --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
            <h2 class="text-[20px] font-normal mb-4">Daftar SKP Saya</h2>


            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-500 uppercase bg-slate-50/50">
                            <th class="px-3 py-3 font-medium">Periode</th>
                            <th class="px-3 py-3 font-medium">Sasaran Kinerja</th>
                            <th class="px-3 py-3 font-medium">Indikator</th>
                            <th class="px-3 py-3 font-medium text-center">Target</th>
                            <th class="px-3 py-3 font-medium text-center">Target</th>
                            <th class="px-3 py-3 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="text-slate-700 divide-y divide-slate-100">

                        <template x-for="skp in skpList" :key="skp.id">
                            <tr class="hover:bg-slate-50 transition-colors">
                                {{-- Periode --}}
                                <td class="px-3 py-3 whitespace-nowrap text-xs">
                                    <div class="font-medium" x-text="formatDate(skp.periode_mulai)"></div>
                                    <div class="text-slate-500" x-text="formatDate(skp.periode_selesai)"></div>
                                </td>

                                {{-- Sasaran --}}
                                <td class="px-3 py-3 font-medium text-slate-800" x-text="skp.nama_skp"></td>

                                {{-- Indikator --}}
                                <td class="px-3 py-3 text-slate-500" x-text="skp.indikator"></td>

                                {{-- Target --}}
                                <td class="px-3 py-3 text-center font-bold text-[#155FA6]" 
                                    x-text="skp.target">
                                </td>

                                {{-- Aksi --}}
                                <td class="px-3 py-3 text-center">
                                    <button 
                                        @click.prevent="openDetailModal(skp)"
                                        class="rounded-[8px] bg-[#155FA6]/10 text-[#155FA6] 
                                        border border-[#155FA6]/20 text-xs px-3 py-1.5 font-medium 
                                        hover:bg-[#155FA6] hover:text-white transition-all"
                                    >
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        </template>

                        {{-- EMPTY STATE --}}
                        <tr x-show="skpList.length === 0" style="display: none;">
                            <td colspan="5" class="px-3 py-8 text-center text-slate-400 italic">
                                Belum ada data SKP yang ditambahkan.
                            </td>
                            <td colspan="5" class="px-3 py-8 text-center text-slate-400 italic">
                                Belum ada data SKP yang ditambahkan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div> {{-- END KOLOM KIRI --}}
    {{-- ============================================
        KOLOM KANAN: PANDUAN + STATUS LAPORAN
    ============================================ --}}
    <div class="space-y-4 flex flex-col">

        {{-- PANDUAN PREMIUM --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col">
            <h3 class="text-sm font-semibold text-slate-800">Panduan Singkat</h3>

            <div class="mt-3 space-y-2 max-h-[420px] overflow-y-auto pr-1 custom-scrollbar">

                @foreach ([
                    ['title' => 'Periode Awal', 'desc' => 'Pilih tanggal penetapan awal SKP.'],
                    ['title' => 'Periode Akhir', 'desc' => 'Pilih tanggal penetapan akhir SKP.'],
                    ['title' => 'Sasaran Kerja', 'desc' => 'Tuliskan sasaran kerja yang ingin dicapai.'],
                    ['title' => 'Indikator Kerja', 'desc' => 'Tuliskan indikator keberhasilan sasaran kerja.'],
                    ['title' => 'Rencana Aksi', 'desc' => 'Tuliskan uraian rencana aksi yang dilakukan.'],
                    ['title' => 'Target Kuantitas', 'desc' => 'Tentukan target angka sesuai rencana aksi.'],
                    ['title' => 'Atasan Langsung', 'desc' => 'Nama atasan terisi otomatis dari profil Anda.'],
                ] as $guide)

                <div class="rounded-[10px] bg-[#155FA6] px-3 py-2.5 text-white text-xs leading-snug">
                    <p class="text-[13px] font-semibold">{{ $guide['title'] }}</p>
                    <p class="mt-[2px] text-[11px] text-white/90">{{ $guide['desc'] }}</p>
                </div>

                @endforeach
            </div>
        </div>

        {{-- STATUS LAPORAN (Premium seperti Staf) --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex-1">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">Status Laporan Terakhir</h3>

            <div class="space-y-2 text-xs">

                {{-- Contoh Item 1 --}}
                <div class="flex items-center justify-between rounded-[10px] bg-slate-50 px-3 py-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 
                            text-amber-600 text-[11px] font-semibold">
                            P
                        </span>

                        <div>
                            <p class="font-medium text-slate-800">Rapat Koordinasi Pendapatan</p>
                            <p class="text-[11px] text-slate-500">Menunggu Validasi Laporan</p>
                        </div>
                    </div>

                    <span class="text-[11px] text-slate-400 whitespace-nowrap">07 Nov 2025</span>
                </div>

                {{-- Contoh Item 2 --}}
                <div class="flex items-center justify-between rounded-[10px] bg-slate-50 px-3 py-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 
                            text-emerald-600 text-[11px] font-semibold">
                            D
                        </span>

                        <div>
                            <p class="font-medium text-slate-800">Rapat Kerja Pajak</p>
                            <p class="text-[11px] text-slate-500">Laporan Disetujui</p>
                        </div>
                    </div>

                    <span class="text-[11px] text-slate-400 whitespace-nowrap">09 Nov 2025</span>
                </div>

            </div>
        </div>
    </div> {{-- END KOLOM KANAN --}}

    {{-- ============================================
        MODAL DETAIL SKP (PREMIUM)
    ============================================ --}}
    <div 
        x-show="openDetail" 
        x-transition.opacity 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm p-4"
        style="display: none;"
    >
        <div 
            @click.outside="openDetail = false"
            x-transition.scale
            class="relative w-full max-w-2xl rounded-2xl bg-white ring-1 ring-slate-200 
            p-6 shadow-xl"
        >
            <button 
                @click="openDetail = false"
                class="absolute top-4 right-5 h-8 w-8 rounded-full flex items-center 
                justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600"
            >
                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-width="2.5" stroke-linecap="round" d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>

            <h3 class="text-lg font-semibold text-slate-800">Detail SKP</h3>


            <template x-if="detailData">
                <div class="mt-6 space-y-5 text-sm">

                    {{-- Periode --}}
                    <div class="grid grid-cols-2 gap-4 border-b pb-4">
                        <div>
                            <p class="text-xs text-slate-500">Periode Mulai</p>
                            <p class="font-medium text-slate-800" 
                               x-text="formatDate(detailData.periode_mulai)">
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Periode Selesai</p>
                            <p class="font-medium text-slate-800" 
                               x-text="formatDate(detailData.periode_selesai)">
                            </p>
                        </div>
                    </div>

                    {{-- Sasaran --}}
                    <div>
                        <p class="text-xs text-slate-500">Sasaran Kinerja</p>
                        <p class="text-slate-800" x-text="detailData.nama_skp"></p>
                    </div>

                    {{-- Indikator --}}
                    <div>
                        <p class="text-xs text-slate-500">Indikator Kinerja</p>
                        <p class="text-slate-800" x-text="detailData.indikator"></p>
                    </div>

                    {{-- Rencana Aksi --}}
                    <div>
                        <p class="text-xs text-slate-500">Rencana Aksi</p>
                        <p class="text-slate-800" x-text="detailData.rencana_aksi"></p>
                    </div>

                    {{-- Target & Atasan --}}
                    <div class="grid grid-cols-2 gap-4 pt-3 bg-slate-50 p-3 rounded-lg">
                        <div>
                            <p class="text-xs text-slate-500">Target (Angka)</p>
                            <p class="text-slate-800 font-bold" x-text="detailData.target"></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Atasan Langsung</p>
                            <p class="text-slate-800" x-text="atasanName"></p>
                        </div>
                    </div>

                    {{-- Tombol --}}
                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button 
                            @click="openDetail = false"
                            class="px-4 py-2 rounded-[10px] bg-slate-100 text-sm 
                            text-slate-700 hover:bg-slate-200"
                        >
                            Tutup
                        </button>

                        <button 
                            @click="openEditModal()"
                            class="px-4 py-2 rounded-[10px] bg-[#155FA6] text-white text-sm 
                            hover:brightness-95"
                        >
                            Edit SKP
                        </button>
                    </div>

                </div>
            </template>
        </div>
    </div>

    {{-- ============================================
        MODAL EDIT SKP (PREMIUM)
    ============================================ --}}
    <div 
        x-show="openEdit"
        x-transition.opacity
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 
        backdrop-blur-sm p-4"
        style="display: none;"
    >
        <div 
            @click.outside="openEdit = false"
            class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl overflow-hidden"
        >
            <div class="px-6 py-4 border-b bg-slate-50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800">Edit SKP</h3>

                <button @click="openEdit = false" 
                        class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" 
                              stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitEdit" class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                
                <template x-if="editData">
                    <div class="space-y-4 text-sm">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Periode Mulai
                                </label>
                                <input type="date" x-model="editData.periode_mulai"
                                       class="w-full rounded-[8px] border border-slate-300 
                                       px-3 py-2 outline-none focus:ring-2 focus:ring-[#155FA6]/20 
                                       focus:border-[#155FA6]">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Periode Selesai
                                </label>
                                <input type="date" x-model="editData.periode_selesai"
                                       class="w-full rounded-[8px] border border-slate-300 
                                       px-3 py-2 outline-none focus:ring-2 focus:ring-[#155FA6]/20 
                                       focus:border-[#155FA6]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Sasaran Kinerja
                            </label>
                            <input type="text" x-model="editData.nama_skp"
                                   class="w-full rounded-[8px] border border-slate-300 px-3 py-2 
                                   outline-none focus:ring-2 focus:ring-[#155FA6]/20 
                                   focus:border-[#155FA6]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Indikator Kinerja
                            </label>
                            <input type="text" x-model="editData.indikator"
                                   class="w-full rounded-[8px] border border-slate-300 px-3 py-2 
                                   outline-none focus:ring-2 focus:ring-[#155FA6]/20 
                                   focus:border-[#155FA6]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Rencana Aksi
                            </label>
                            <textarea x-model="editData.rencana_aksi" rows="3"
                                class="w-full rounded-[8px] border border-slate-300 px-3 py-2 
                                outline-none focus:ring-2 focus:ring-[#155FA6]/20 
                                focus:border-[#155FA6] resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Target (Angka)
                                </label>
                                <input type="number" x-model="editData.target"
                                       class="w-full rounded-[8px] border border-slate-300 
                                       px-3 py-2 outline-none focus:ring-2 
                                       focus:ring-[#155FA6]/20 focus:border-[#155FA6]">
{{-- ============================================
    SCRIPT ALPINE.JS PREMIUM (FULL VERSION)
============================================ --}}
<script>
document.addEventListener("alpine:init", () => {
    Alpine.data("skpPageData", () => ({
        
        // =====================================================
        // STATE
        // =====================================================
        skpList: [],
        atasanName: 'Memuat...',
        isLoading: false,

        openDetail: false,
        openEdit: false,
        detailData: null,
        editData: null,

        formData: {
            nama_skp: '',
            periode_mulai: '',
            periode_selesai: '',
            indikator: '',
            rencana_aksi: '',
            target: ''
        },

        // =====================================================
        // INIT PAGE
        // =====================================================
        initPage() {
            const token = localStorage.getItem('auth_token');

            if (!token) {
                window.location.href = '/login';
                return;
            }

            this.fetchProfile();
            this.fetchSkpList();
        },

        // =====================================================
        // FETCH PROFILE (Untuk Atasan)
        // =====================================================
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

            } catch (err) {
                console.error('fetchProfile error:', err);
                this.atasanName = 'Gagal memuat';
            }
        },

        // =====================================================
        // FETCH LIST SKP
        // =====================================================
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

            } catch (err) {
                console.error('fetchSkpList error:', err);
                this.skpList = [];
            }
        },

        // =====================================================
        // CREATE SKP
        // =====================================================
        async submitCreate() {
            this.isLoading = true;
            const token = localStorage.getItem('auth_token');

            // Validasi manual
            if (
                !this.formData.periode_mulai ||
                !this.formData.periode_selesai ||
                !this.formData.nama_skp ||
                !this.formData.indikator ||
                !this.formData.rencana_aksi ||
                !this.formData.target
            ) {
                Swal.fire({
                    icon: "warning",
                    title: "Form Belum Lengkap",
                    text: "Mohon isi semua field sebelum menambahkan SKP.",
                    confirmButtonColor: "#F97316"
                });
                this.isLoading = false;
                return;
            }

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

                const json = await res.json();


                const json = await res.json();

                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'SKP Berhasil Ditambahkan!',
                        text: 'Data SKP baru sudah masuk daftar.',
                        confirmButtonColor: '#1C7C54'
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'SKP Berhasil Ditambahkan!',
                        text: 'Data SKP baru sudah masuk daftar.',
                        confirmButtonColor: '#1C7C54'
                    });

                    this.resetForm();
                    this.fetchSkpList();

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan SKP',
                        text: json.message || 'Terjadi kesalahan pada input.',
                        confirmButtonColor: '#DC2626'
                    });
                }

            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Server',
                    text: 'Tidak dapat menghubungi server.',
                });

            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Server',
                    text: 'Tidak dapat menghubungi server.',
                });
            }


            this.isLoading = false;
        },

        // =====================================================
        // RESET FORM
        // =====================================================
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

        // =====================================================
        // OPEN DETAIL MODAL
        // =====================================================
        openDetailModal(item) {
            this.detailData = item;
            this.openDetail = true;
        },

        // =====================================================
        // OPEN EDIT MODAL
        // =====================================================
        openEditModal() {
            this.editData = JSON.parse(JSON.stringify(this.detailData));

            if (this.editData.periode_mulai) {
                this.editData.periode_mulai = this.editData.periode_mulai.substring(0, 10);
            }
            if (this.editData.periode_selesai) {
                this.editData.periode_selesai = this.editData.periode_selesai.substring(0, 10);
            }

            this.openDetail = false;
            this.openEdit = true;
        },

        // =====================================================
        // SUBMIT EDIT
        // =====================================================
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

                const json = await res.json();


                const json = await res.json();

                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Perubahan Disimpan!',
                        confirmButtonColor: '#155FA6'
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Perubahan Disimpan!',
                        confirmButtonColor: '#155FA6'
                    });

                    this.openEdit = false;
                    this.fetchSkpList();

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Update',
                        text: json.message || 'Periksa kembali data!',
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Update',
                        text: json.message || 'Periksa kembali data!',
                    });
                }

            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Koneksi',
                    text: 'Tidak dapat terhubung dengan server.',
                });
            }


            this.isLoading = false;
        },

        // =====================================================
        // FORMAT TANGGAL
        // =====================================================
        formatDate(dateString) {
            if (!dateString) return '-';

            try {
                return new Date(dateString).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            } catch (e) {
                return dateString;
            }
        }


    }));
});
</script>

@endsection

