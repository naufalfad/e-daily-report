@php($title = 'Riwayat Laporan')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'riwayat'])

@section('content')
{{-- 
    ANALISIS: 
    Data $dummyDataList diasumsikan dikirim dari routes/web.php.
    Kita suntikkan data tersebut ke Alpine.js via @js($dummyDataList)
--}}
<section x-data="riwayatData(@js($dummyDataList))" x-init="initDatePickers()">

    {{-- CARD UTAMA --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
        <h2 class="text-[20px] font-normal mb-1">Riwayat Laporan</h2>

        {{-- FILTER TANGGAL --}}
        <form class="mt-4" @submit.prevent="filterData()">
            <label class="block text-xs font-normal text-slate-600 mb-2">Filter Berdasarkan Tanggal</label>
            <div class="grid md:grid-cols-[1fr_1fr_auto] gap-3">

                <div>
                    <label class="sr-only">Dari Tanggal</label>
                    <div class="relative">
                        <input x-model="filter.from" id="tgl_dari" type="date"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                        <button type="button" id="tgl_dari_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-80">
                        </button>
                    </div>
                </div>

                <div>
                    <label class="sr-only">Sampai Tanggal</label>
                    <div class="relative">
                        <input x-model="filter.to" id="tgl_sampai" type="date"
                            class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" />
                        <button type="button" id="tgl_sampai_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/tanggal.svg') }}" class="h-4 w-4 opacity-80">
                        </button>
                    </div>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="rounded-[10px] bg-[#0E7A4A] px-5 py-2.5 text-sm text-white hover:brightness-95 w-full md:w-auto">
                        Terapkan
                    </button>
                </div>
            </div>
        </form>

        {{-- TABLE --}}
        <div class="overflow-x-auto mt-6">
            <table class="w-full min-w-[900px] text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500 uppercase bg-slate-50">
                        <th class="px-3 py-2 font-medium">Tanggal Laporan</th>
                        <th class="px-3 py-2 font-medium">Nama Kegiatan</th>
                        <th class="px-3 py-2 font-medium">Tanggal Verifikasi</th>
                        <th class="px-3 py-2 font-medium">Pejabat Penilai</th>
                        <th class="px-3 py-2 font-medium">Status</th>
                        <th class="px-3 py-2 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    {{-- ANALISIS: Iterasi data menggunakan x-for dari Alpine --}}
                    <template x-for="item in filteredItems" :key="item.id">
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="px-3 py-3 whitespace-nowrap" x-text="item.tanggal_kirim"></td>
                            <td class="px-3 py-3" x-text="item.nama_kegiatan"></td>
                            <td class="px-3 py-3 whitespace-nowrap" x-text="item.tanggal_verifikasi"></td>
                            <td class="px-3 py-3" x-text="item.penilai"></td>
                            <td class="px-3 py-3">
                                {{-- ANALISIS: Class binding dinamis untuk status badge --}}
                                <span :class="{
                                    'rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-medium px-2.5 py-0.5': item.status === 'Diterima',
                                    'rounded-full bg-rose-100 text-rose-700 text-[11px] font-medium px-2.5 py-0.5': item.status === 'Ditolak',
                                    'rounded-full bg-amber-100 text-amber-700 text-[11px] font-medium px-2.5 py-0.5': item.status !== 'Diterima' && item.status !== 'Ditolak'
                                }" x-text="item.status"></span>
                            </td>
                            <td class="px-3 py-3">
                                <button @click="openModal(item)" class="rounded-[6px] bg-[#155FA6] text-white text-[11px] px-3 py-[4px] leading-none shadow-sm hover:brightness-95">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    {{-- END CARD --}}


    {{-- 
      MODAL DETAIL (PERBAIKAN)
      Analisis: Struktur HTML dirombak total agar sesuai desain di image_2b85a3.png
    --}}
    <div x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm p-4" 
         style="display: none;">
        
        {{-- Panel Modal --}}
        <div x-show="open" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.outside="open = false"
             class="relative w-full max-w-2xl rounded-2xl bg-white ring-1 ring-slate-200 p-6 shadow-xl">
            
            {{-- Tombol Close (Sesuai Desain) --}}
            <button @click="open = false" 
                    class="absolute top-4 right-5 h-8 w-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2.5" stroke-linecap="round" d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
            
            <h3 class="text-lg font-semibold text-slate-800">Detail Laporan</h3>

            {{-- Template untuk memastikan data ada sebelum render --}}
            <template x-if="modalData">
                <div class="mt-4 space-y-4 text-sm">
                    
                    {{-- Section 1: General Info --}}
                    <div class="space-y-2">
                        <div>
                            <label class="text-xs text-slate-500">Tanggal:</label>
                            <p class="text-slate-800" x-text="modalData.tanggal_kirim.split(' | ')[0]"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Nama Kegiatan:</label>
                            <p class="text-slate-800 font-semibold text-base" x-text="modalData.nama_kegiatan"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Uraian Kegiatan:</label>
                            <p class="text-slate-800" x-text="modalData.uraian"></p>
                        </div>
                    </div>

                    {{-- Section 2: Grid (Sesuai Desain) --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-3 pt-2">
                        <!-- Col 1 -->
                        <div>
                            <label class="text-xs text-slate-500">Output:</label>
                            <p class="text-slate-800" x-text="modalData.output"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Jam Mulai:</label>
                            <p class="text-slate-800" x-text="modalData.jam_mulai"></p>
                        </div>
                        <!-- Col 2 -->
                        <div>
                            <label class="text-xs text-slate-500">Volume:</label>
                            <p class="text-slate-800" x-text="modalData.volume"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Jam Selesai:</label>
                            <p class="text-slate-800" x-text="modalData.jam_selesai"></p>
                        </div>
                        <!-- Col 3 -->
                        <div>
                            <label class="text-xs text-slate-500">Satuan:</label>
                            <p class="text-slate-800" x-text="modalData.satuan"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Bukti:</label>
                            <button class="rounded-[6px] bg-[#155FA6] text-white text-[11px] px-3 py-[4px] leading-none shadow-sm hover:brightness-95">
                                Lihat Bukti
                            </button>
                        </div>
                        <!-- Col 4 -->
                        <div>
                            <label class="text-xs text-slate-500">Kategori:</label>
                            <p class="text-slate-800" x-text="modalData.kategori"></p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Lokasi:</label>
                            <p class="text-slate-800" x-text="modalData.lokasi"></p>
                        </div>
                    </div>
                    
                    {{-- Section 3: Status & Feedback (Garis Atas) --}}
                    <div class="border-t border-slate-200 pt-4 space-y-3">
                        <div>
                            <label class="text-xs text-slate-500">Status:</label>
                            <div>
                                {{-- Badge Diterima --}}
                                <span x-show="modalData.status === 'Diterima'" style="display: none;"
                                      class="rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium px-3 py-0.5">
                                    Diterima
                                </span>
                                {{-- Badge Ditolak --}}
                                <span x-show="modalData.status === 'Ditolak'" style="display: none;"
                                      class="rounded-full bg-rose-100 text-rose-700 text-xs font-medium px-3 py-0.5">
                                    Ditolak
                                </span>
                                {{-- Badge Menunggu (Fallback) --}}
                                <span x-show="modalData.status !== 'Diterima' && modalData.status !== 'Ditolak'" style="display: none;"
                                      class="rounded-full bg-amber-100 text-amber-700 text-xs font-medium px-3 py-0.5"
                                      x-text="modalData.status">
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Catatan:</label>
                            <p class="text-slate-800" x-text="modalData.catatan"></p>
                        </div>
                         <div>
                            <label class="text-xs text-slate-500">Nama Pejabat Penilai Kerja:</label>
                            <p class="text-slate-800 font-medium" x-text="modalData.penilai"></p>
                        </div>
                    </div>

                    {{-- Section 4: Tombol Aksi (Hanya jika Ditolak) --}}
                    <div x-show="modalData.status === 'Ditolak'" style="display: none;" 
                         class="flex justify-end pt-2">
                         <button
                            class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm font-normal text-white hover:brightness-95">
                            Perbaiki Laporan
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
    {{-- END MODAL --}}

</section>
@endsection

@push('scripts')
{{-- ANALISIS: Memuat Alpine.js v3 --}}
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
function riwayatData(initialData = []) {
    return {
        items: initialData,
        filteredItems: initialData,
        open: false,
        modalData: null,
        filter: { from: '', to: '' },

        // ANALISIS: Fungsi loadData() tidak lagi diperlukan karena data
        // sudah di-inject saat inisialisasi.

        filterData() {
            let from = this.filter.from ? new Date(this.filter.from) : null;
            let to = this.filter.to ? new Date(this.filter.to) : null;

            // Logika filter Anda sudah benar
            this.filteredItems = this.items.filter(item => {
                // Konversi tanggal 'dd-mm-yyyy' atau 'dd Nov yyyy' ke format Date
                let itemDateStr = item.tanggal_kirim.split(' | ')[0];
                // Perlu konversi format '07 Nov 2025'
                // Mari kita asumsikan (atau perbaiki) format tanggal agar konsisten
                // Untuk saat ini, kita anggap formatnya YYYY-MM-DD
                
                // --- PERBAIKAN LOGIKA PARSING TANGGAL ---
                // Data dummy Anda: "07 Nov 2025 | 12:10"
                // Kita perlu mengubah "07 Nov 2025" menjadi Sesuatu yang bisa diparsing
                // Ini adalah cara parsing yang lebih aman:
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const parts = itemDateStr.split(' ');
                let day = parts[0];
                let month = months.indexOf(parts[1]) + 1;
                let year = parts[2];
                
                // Pad bulan jika perlu (cth: '9' -> '09')
                if(month < 10) month = '0' + month;

                // Format YYYY-MM-DD
                let parsableDateStr = `${year}-${month}-${day}`;
                let date = new Date(parsableDateStr);
                // --- AKHIR PERBAIKAN LOGIKA PARSING ---

                if (from && date < from) return false;
                if (to && date > to) return false;
                return true;
            });
        },

        openModal(item) {
            this.modalData = item;
            this.open = true;
        },

        // Fungsi untuk menginisialisasi date picker
        initDatePickers() {
             ['tgl_dari', 'tgl_sampai'].forEach(id => {
                const input = document.getElementById(id);
                const btn = document.getElementById(id+'_btn');
                if (!input || !btn) return;
                btn.addEventListener('click', () => {
                    try { input.showPicker(); } catch { input.focus(); }
                });
            });
        }
    }
}

// Hapus event listener DOMContentLoaded yang lama, karena x-init akan menanganinya
</script>
@endpush