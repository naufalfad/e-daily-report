@php($title = 'Input LKH')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'input-lkh'])

@section('content')

{{-- GRID UTAMA: FORM KIRI, PANDUAN + STATUS KANAN --}}
<section class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)] gap-4">

    {{-- FORM INPUT LKH --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-5">
        <h2 class="text-[20px] font-normal mb-4">Form Input LKH</h2>

        <div class="space-y-4">
            {{-- Row 1: Tanggal + Jenis Kegiatan --}}
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Tanggal</label>
                    <div class="relative">
                        <input id="tanggal_lkh" type="date" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                   px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                   focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none" placeholder="dd/mm/yyyy" />

                        {{-- Icon kalender lokal, klik untuk buka datepicker --}}
                        <button type="button" id="tanggal_lkh_btn" class="absolute right-3 top-1/2 -translate-y-1/2
                        h-7 w-7 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/tanggal.svg') }}" alt="Pilih tanggal"
                                class="h-4 w-4 opacity-80" {{-- HAPUS filter/invert-nya --}} />
                        </button>

                    </div>
                </div>

                <style>
                /* Placeholder abu-abu ketika value masih kosong */
                select:invalid {
                    color: #9CA3AF;
                }

                /* Warna teks untuk semua pilihan di dropdown */
                select option {
                    color: #111827;
                    /* atau pakai #1F2933 / warna teks utama kamu */
                }

                /* Kalau mau placeholder di list tetap abu-abu (opsional) */
                select option[disabled][value=""] {
                    color: #9CA3AF;
                }
                </style>

                <div>
                    <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Jenis Kegiatan</label>

                    <div class="relative">
                        <select required class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                   px-3.5 py-2.5 text-sm pr-9
                   focus:outline-none focus:ring-2
                   focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                   appearance-none">

                            <option value="" disabled selected hidden>Pilih Jenis Kegiatan</option>

                            <option value="rapat">Rapat</option>
                            <option value="pelayanan">Pelayanan Publik</option>
                            <option value="dokumen">Penyusunan Dokumen</option>
                            <option value="lapangan">Kunjungan Lapangan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>

                        <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none" />
                    </div>
                </div>

            </div>
            {{-- Row 2: Referensi Tupoksi --}}
            <style>
            /* Placeholder abu-abu ketika value masih kosong */
            select:invalid {
                color: #9CA3AF;
            }

            /* Warna teks untuk semua pilihan di dropdown */
            select option {
                color: #111827;
                /* atau pakai #1F2933 / warna teks utama kamu */
            }

            /* Kalau mau placeholder di list tetap abu-abu (opsional) */
            select option[disabled][value=""] {
                color: #9CA3AF;
            }
            </style>

            <div>
                <label class="block font-normal text-[15px] text-[#5B687A] mb-[10px]">Referensi Tupoksi</label>

                <div class="relative">
                    <select required class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                   px-3.5 py-2.5 text-sm pr-9 focus:outline-none focus:ring-2
                   focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]
                   appearance-none">

                        <option value="" disabled selected hidden>Pilih Referensi Tupoksi</option>

                        <option>Penyusunan rencana dan program kerja</option>
                        <option>Penyusunan dan perumusan bahan petunjuk teknis</option>
                        <option>Pengawasan, pemantauan dan evaluasi pelaksanaan</option>
                        <option>Pelaksanaan dan pembinaan pembukuan</option>
                    </select>

                    <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                        class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none" />
                </div>
            </div>

            {{-- Row 3: Uraian Kegiatan --}}
            <div>
                <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Uraian Kegiatan</label>
                <textarea rows="3" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                                     px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2
                                     focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]"
                    placeholder="Tulis uraian kegiatan yang dilakukan..."></textarea>
            </div>

            {{-- Row 4: Output --}}
            <div>
                <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Output</label>
                <input type="text" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                               px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                               focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" placeholder="Contoh: Notulensi Rapat">
            </div>

            {{-- Row 5: Volume + Satuan + Kategori --}}
            <style>
            /* Placeholder abu-abu ketika value masih kosong */
            select:invalid {
                color: #9CA3AF;
            }

            /* Warna teks untuk semua pilihan di dropdown */
            select option {
                color: #111827;
                /* atau pakai #1F2933 / warna teks utama kamu */
            }

            /* Kalau mau placeholder di list tetap abu-abu (opsional) */
            select option[disabled][value=""] {
                color: #9CA3AF;
            }
            </style>

            <div class="grid md:grid-cols-[120px_1fr_1fr] gap-4">

                {{-- Volume --}}
                <div>
                    <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Volume</label>
                    <input type="number" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                      px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                      focus:ring-[#1C7C54]/30 focus:border-[#1C7C54]" placeholder="Volume">
                </div>

                {{-- Satuan --}}
                <div>
                    <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Satuan</label>
                    <div class="relative">
                        <select required class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                       px-3.5 py-2.5 text-sm pr-9 focus:outline-none focus:ring-2
                       focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none">

                            <option value="" disabled selected hidden>Pilih Satuan</option>
                            <option>Jam</option>
                            <option>Dokumen</option>
                            <option>Kegiatan</option>
                        </select>

                        <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none" />
                    </div>
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Kategori</label>
                    <div class="relative">
                        <select required class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                        px-3.5 py-2.5 text-sm pr-9 focus:outline-none focus:ring-2
                       focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none">

                            <option value="" disabled selected hidden>Pilih Kategori</option>
                            <option>SKP</option>
                            <option>Non-SKP</option>
                        </select>

                        <img src="{{ asset('assets/icon/chevron-down.svg') }}"
                            class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-70 pointer-events-none" />
                    </div>
                </div>

            </div>

            {{-- Row 6: Jam Mulai + Jam Selesai --}}
            <div class="grid md:grid-cols-2 gap-4">

                {{-- Jam Mulai --}}
                <div>
                    <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Jam Mulai</label>
                    <div class="relative">
                        <input id="jam_mulai" type="time" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                       px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                       focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none time-placeholder">

                        {{-- Icon jam (ukuran konsisten) --}}
                        <button type="button" id="jam_mulai_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/time.svg') }}"
                                class="h-4 w-4 opacity-70 pointer-events-none" alt="Pilih jam mulai">
                        </button>
                    </div>
                </div>

                {{-- Jam Selesai --}}
                <div>
                    <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Jam Selesai</label>
                    <div class="relative">
                        <input id="jam_selesai" type="time" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                       px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                       focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] appearance-none time-placeholder">

                        {{-- Icon jam (ukuran konsisten) --}}
                        <button type="button" id="jam_mulai_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center">
                            <img src="{{ asset('assets/icon/time.svg') }}"
                                class="h-4 w-4 opacity-70 pointer-events-none" alt="Pilih jam mulai">
                        </button>
                    </div>
                </div>
            </div>

            {{-- Row 7: Unggah Bukti + Lokasi --}}
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Unggah Bukti</label>
                    <label class="w-full flex items-center justify-between rounded-[10px]
                   border border-dashed border-slate-300 bg-slate-50/60
                   px-3.5 py-2.5 text-sm text-slate-500 cursor-pointer">
                        <span class="truncate">Pilih File</span>
                        <img src="{{ asset('assets/icon/upload.svg') }}" class="h-4 w-4 opacity-70" alt="Upload">
                        <input type="file" class="hidden">
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-normal text-[15px] text-[#5B687A] mb-[10px]">Lokasi</label>
                    <input type="text" class="w-full rounded-[10px] border border-slate-200 bg-slate-50/60
                   px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2
                   focus:ring-[#1C7C54]/30 focus:border-[#1C7C54] placeholder:text-slate-400"
                        placeholder="Otomatis Terisi">
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                <button class="rounded-[10px] bg-[#155FA6] px-4 py-2 text-sm font-normal text-white hover:bg-slate-50">
                    Simpan Draft
                </button>
                <button
                    class="rounded-[10px] bg-[#0E7A4A] px-4 py-2 text-sm font-normal text-white hover:brightness-95">
                    Kirim LKH
                </button>
            </div>
        </div>

        {{-- Draft LKH di bawah form --}}
        <div class="mt-5 rounded-[14px] bg-slate-50/80 border border-slate-200 px-4 py-3">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-slate-800">Draft LKH</h3>
                <a href="#" class="text-[11px] text-slate-500 hover:underline">Lihat Semua Draft</a>
            </div>

            <div
                class="rounded-xl bg-white border border-slate-200 px-3 py-2.5 flex items-center justify-between text-xs">
                <div>
                    <div class="font-medium text-slate-800">Rapat Koordinasi Pajak</div>
                    <div class="mt-[2px] text-[11px] text-slate-500">
                        Disimpan: 09 November 2025 | 10:15
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-2">
                    <button
                        class="rounded-[6px] bg-emerald-600 text-white text-[11px] px-3 py-[4px] leading-none hover:brightness-95">
                        Lanjutkan
                    </button>
                    <button
                        class="rounded-[6px] bg-[#B6241C] text-white text-[11px] px-3 py-[4px] leading-none hover:brightness-95">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN: PANDUAN SINGKAT + STATUS LAPORAN --}}
    <div class="space-y-4">

        {{-- PANDUAN SINGKAT (SCROLLABLE BODY) --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col">
            <h3 class="text-sm font-semibold text-slate-800">Panduan Singkat</h3>

            {{-- BAGIAN INI YANG DISCROLL --}}
            <div class="mt-3 space-y-2 max-h-[420px] overflow-y-auto pr-1">
                @foreach ([
                ['title' => 'Tanggal', 'desc' => 'Pilih tanggal kegiatan dilakukan, bukan tanggal pengisian.'],
                ['title' => 'Jenis Kegiatan', 'desc' => 'Pilih jenis kegiatan yang dilakukan.'],
                ['title' => 'Referensi Tupoksi', 'desc' => 'Pilih jenis tupoksi yang sesuai.'],
                ['title' => 'Uraian Kegiatan', 'desc' => 'Isi dengan kalimat yang ringkas dan jelas.'],
                ['title' => 'Output', 'desc' => 'Sebutkan hasil nyata dari kegiatan.'],
                ['title' => 'Volume', 'desc' => 'Masukkan jumlah output kegiatan yang sesuai.'],
                ['title' => 'Satuan', 'desc' => 'Pilih satuan yang sesuai dengan output kegiatan.'],
                ['title' => 'Kategori', 'desc' => 'Pilih kategori SKP atau Non-SKP.'],
                ['title' => 'Jam Mulai & Jam Selesai', 'desc' => 'Isi jam mulai dan jam selesai kegiatan.'],
                ['title' => 'Unggah Bukti', 'desc' => 'Unggah bukti foto/dokumen kegiatan.'],
                ['title' => 'Lokasi', 'desc' => 'Sistem akan otomatis membaca lokasi Anda.'],
                ] as $guide)
                <div class="rounded-[10px] bg-[#155FA6] px-3 py-2.5 text-white text-xs leading-snug">
                    <p class="text-[13px] font-semibold">{{ $guide['title'] }}</p>
                    <p class="mt-[2px] text-[11px] text-white/90">{{ $guide['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- STATUS LAPORAN TERAKHIR --}}
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">Status Laporan Terakhir</h3>

            <div class="space-y-2 text-xs">
                {{-- Item 1 --}}
                <div class="flex items-center justify-between rounded-[10px] bg-slate-50 px-3 py-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-amber-600 text-[11px] font-semibold">
                            P
                        </span>
                        <div>
                            <p class="font-medium text-slate-800">Rapat Koordinasi Pendapatan</p>
                            <p class="text-[11px] text-slate-500">Menunggu Validasi Laporan</p>
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-400 whitespace-nowrap">07 Nov 2025</span>
                </div>

                {{-- Item 2 --}}
                <div class="flex items-center justify-between rounded-[10px] bg-slate-50 px-3 py-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-[11px] font-semibold">
                            D
                        </span>
                        <div>
                            <p class="font-medium text-slate-800">Rapat Kerja Pajak</p>
                            <p class="text-[11px] text-slate-500">Laporan Disetujui</p>
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-400 whitespace-nowrap">09 Nov 2025</span>
                </div>

                {{-- Item 3 --}}
                <div class="flex items-center justify-between rounded-[10px] bg-slate-50 px-3 py-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-100 text-rose-600 text-[11px] font-semibold">
                            L
                        </span>
                        <div>
                            <p class="font-medium text-slate-800">Perjalanan Dinas</p>
                            <p class="text-[11px] text-slate-500">Laporan Ditolak</p>
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-400 whitespace-nowrap">13 Nov 2025</span>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection