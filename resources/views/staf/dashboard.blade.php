@php($title = 'Dashboard Staf')
@extends('layouts.app', ['title' => $title, 'role' => 'staf', 'active' => 'dashboard'])

@section('content')

{{-- Banner sambutan --}}
<section class="grid gap-4 lg:gap-5 lg:grid-cols-[1fr_380px]">
    {{-- Banner kiri --}}
    <div class="relative rounded-[20px] bg-[#1C7C54] text-white overflow-hidden
             p-6 md:py-8 md:pl-8 md:pr-10 flex justify-between items-start h-[250px]">

        {{-- Kolom teks kiri --}}
        <div class="relative z-10 flex-1 max-w-[64%]">
            {{-- Badge tanggal --}}
            <div class="inline-flex items-center gap-2 rounded-[10px] bg-white/40 px-3 py-1
                    text-sm ring-1 ring-white/20 mb-10">
                <img src="{{ asset('assets/icon/date.svg') }}" alt="Tanggal"
                    class="h-4 w-4 filter invert brightness-0" />
                <span>{{ now()->setTimezone('Asia/Jayapura')->translatedFormat('d F Y | H:i') }} WIT</span>
            </div>

            {{-- Teks utama --}}
            <p class="text-[20px] md:text-[28px] font-bold leading-tight">Selamat Datang,</p>
            <h1 class="text-[20px] md:text-[28px] font-bold leading-tight mt-1 md:whitespace-nowrap" id="banner-nama">
                User...
            </h1>
            <p class="mt-3 text-white/90 text-[16px]">Semoga harimu menyenangkan!</p>
        </div>

        {{-- Ilustrasi kanan --}}
        <div class="relative flex-shrink-0 self-center">
            <img src="{{ asset('img/dashboard-illustration.svg') }}" alt="Dashboard Illustration"
                class="w-[197px] h-[218px] object-contain select-none pointer-events-none translate-x-2" />
        </div>
    </div>

    {{-- CARD PROFIL SAYA --}}
    <aside class="rounded-[15px] bg-white ring-1 ring-slate-200 shadow-[0_6px_18px_rgba(15,23,42,0.06)]
               overflow-hidden mt-0">

        {{-- BODY --}}
        <div class="px-5 pt-4 pb-5">

            {{-- AVATAR + TEKS UTAMA --}}
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div class="flex-shrink-0">
                    <div
                        class="h-[78px] w-[78px] rounded-full overflow-hidden bg-[#FF8A3D] flex items-center justify-center">
                        <img src="{{ asset('assets/icon/avatar.png') }}" class="h-full w-full object-cover"
                            alt="Avatar">
                    </div>
                </div>

                {{-- Info teks utama --}}
                <div class="min-w-0 flex flex-col">
                    <h3 class="text-[17px] font-semibold text-slate-900 leading-snug" id="profile-nama">
                        -
                    </h3>
                    <p class="mt-[2px] text-[13px] text-slate-500 leading-snug" id="profile-nip">
                        -
                    </p>

                    {{-- Lokasi --}}
                    <div class="mt-1.5 flex items-center gap-1.5 text-[13px] text-slate-500">
                        <img src="{{ asset('assets/icon/location.svg') }}" class="h-4 w-4" alt="Lokasi" />
                        <span class="truncate" id="profile-lokasi">-</span>
                    </div>
                </div>
            </div>

            {{-- EMAIL + TELEPON --}}
            <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-1 text-[13px] text-slate-600">
                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                    <img src="{{ asset('assets/icon/email.svg') }}" class="h-3.5 w-3.5" alt="Email" />
                    <span class="truncate" id="profile-email">-</span>
                </div>
                <div class="flex items-center gap-1.5 whitespace-nowrap">
                    <img src="{{ asset('assets/icon/telpon.svg') }}" class="h-3.5 w-3.5" alt="Telepon" />
                    <span id="profile-telepon">-</span>
                </div>
            </div>

            {{-- JABATAN / DINAS / ALAMAT --}}
            <div class="mt-4 border-t border-slate-200 pt-3 grid grid-cols-3 text-[13px] text-slate-700">
                {{-- Jabatan --}}
                <div class="pr-3">
                    <p class="text-[11px] tracking-[0.06em] uppercase text-slate-400">Jabatan</p>
                    <p class="mt-1 font-semibold text-slate-900 leading-snug" id="profile-jabatan">-</p>
                </div>

                {{-- Dinas --}}
                <div class="px-3 border-l border-slate-200">
                    <p class="text-[11px] tracking-[0.06em] uppercase text-slate-400">Unit Kerja</p>
                    <p class="mt-1 font-semibold text-slate-900 leading-snug" id="profile-dinas">
                        -
                    </p>
                </div>

                {{-- Alamat --}}
                <div class="pl-3 border-l border-slate-200">
                    <p class="text-[11px] tracking-[0.06em] uppercase text-slate-400">Target</p>
                    <p class="mt-1 font-semibold text-slate-900 leading-snug" id="profile-target">
                        -
                    </p>
                </div>
            </div>

        </div>
    </aside>
</section>

{{-- Statistik ringkas --}}
<section class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mt-4">
    {{-- Card 1: Total Laporan (Mapping ke SKP Diajukan) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-semibold tracking-tight" id="stat-val-1">0</div>
            <div class="flex items-center justify-center h-10 w-10 rounded-[10px] bg-[#155FA6]/50">
                <img src="{{ asset('assets/icon/send.svg') }}" class="h-5 w-5 object-contain">
            </div>
        </div>
        <div class="text-xs text-slate-500">Total Laporan SKP</div>
        <div class="text-xs text-slate-400 font-medium">Total diajukan</div>
    </div>

    {{-- Card 2: Menunggu Verifikasi (Mapping ke Realisasi Tahunan / Waiting) --}}
    {{-- Karena JSON tidak punya count 'Waiting', kita pakai Realisasi Tahunan sebagai pengganti --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-semibold tracking-tight" id="stat-val-2">0</div>
            <div class="flex items-center justify-center h-10 w-10 rounded-[10px] bg-[#D8A106]/50">
                <img src="{{ asset('assets/icon/pending.svg') }}" class="h-5 w-5 object-contain">
            </div>
        </div>
        <div class="text-xs text-slate-500">Realisasi Tahunan</div>
        <div class="text-xs text-emerald-600 font-medium" id="stat-desc-2">0% Capaian</div>
    </div>

    {{-- Card 3: Disetujui (Mapping ke Persen Diterima) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-semibold tracking-tight" id="stat-val-3">0</div>
            <div class="flex items-center justify-center h-10 w-10 rounded-[10px] bg-[#128C60]/50">
                <img src="{{ asset('assets/icon/approve.svg') }}" class="h-5 w-5 object-contain">
            </div>
        </div>
        <div class="text-xs text-slate-500">Rate Disetujui</div>
        <div class="text-xs text-emerald-600 font-medium" id="stat-desc-3">0% Dari total laporan</div>
    </div>

    {{-- Card 4: Ditolak (Mapping ke Persen Ditolak) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        <div class="flex items-start justify-between">
            <div class="text-4xl font-semibold tracking-tight" id="stat-val-4">0</div>
            <div class="flex items-center justify-center h-10 w-10 rounded-[10px] bg-[#B6241C]/50">
                <img src="{{ asset('assets/icon/reject.svg') }}" class="h-5 w-5 object-contain">
            </div>
        </div>
        <div class="text-xs text-slate-500">Rate Ditolak</div>
        <div class="text-xs text-rose-600 font-medium" id="stat-desc-4">0% Dari total laporan</div>
    </div>
</section>

{{-- Grafik + Aktivitas terkini + Draft Laporan --}}
<section class="mt-4 grid grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.4fr)] gap-4">

    {{-- GRAFIK (kartu tinggi tetap, span 2 baris) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 lg:row-span-2">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">Grafik Kinerja Bulanan</h3>
            <button
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-sm hover:bg-slate-50">
                {{ date('Y') }}
            </button>
        </div>

        <div class="mt-4 h-[380px] md:h-[420px] lg:h-[450px]">
            <canvas id="kinerjaBulananChart" class="w-full h-full"></canvas>
        </div>
    </div>

    {{-- AKTIVITAS TERKINI (kanan atas) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4">
        <h3 class="font-semibold mb-3">Aktivitas Terkini</h3>

        <ul class="space-y-3">
            @foreach ([
            [
            'title' => 'Rapat Koordinasi Pendapatan',
            'status' => 'Menunggu Validasi Laporan',
            'date' => '07 Nov 2025',
            'tone' => 'bg-[#D8A106]/50',
            'icon' => 'pending.svg',
            ],
            [
            'title' => 'Rapat Kerja Pajak',
            'status' => 'Laporan Disetujui',
            'date' => '09 Nov 2025',
            'tone' => 'bg-[#128C60]/50',
            'icon' => 'approve.svg',
            ],
            [
            'title' => 'Perjalanan Dinas',
            'status' => 'Laporan Ditolak',
            'date' => '13 Nov 2025',
            'tone' => 'bg-[#B6241C]/50',
            'icon' => 'reject.svg',
            ],
            [
            'title' => 'Kunjungan Lapangan',
            'status' => 'Laporan Disetujui',
            'date' => '15 Nov 2025',
            'tone' => 'bg-[#128C60]/50',
            'icon' => 'approve.svg',
            ],
            ] as $activity)
            <li class="flex items-start gap-3">
                <div class="h-10 w-10 rounded-[10px] flex items-center justify-center {{ $activity['tone'] }}">
                    <img src="{{ asset('assets/icon/' . $activity['icon']) }}" class="h-5 w-5 opacity-90" alt="">
                </div>

                <div class="flex-1">
                    <div class="text-[15px] font-medium leading-snug">
                        {{ $activity['title'] }}
                    </div>
                    <div class="flex justify-between mt-[2px]">
                        <span class="text-xs text-slate-500 leading-snug">
                            {{ $activity['status'] }}
                        </span>
                        <span class="text-xs text-slate-500 whitespace-nowrap leading-snug">
                            {{ $activity['date'] }}
                        </span>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- DRAFT LAPORAN (kanan bawah) --}}
    <ul class="space-y-3" id="aktivitas-list">
        {{-- Diisi via JS --}}
        <li class="text-sm text-slate-400 italic">Memuat aktivitas...</li>
    </ul>
    </div>


    {{-- DRAFT LAPORAN --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">Draft Laporan</h3>
            <a href="#" class="text-sm text-[#1C7C54] hover:underline">Lihat Semua</a>
        </div>
        <div class="space-y-2">
            <<<<<<< HEAD @foreach ([1, 2] as $i) <div
                class="rounded-xl bg-[#F1F5F9] px-3 py-2.5 flex items-center justify-between">
                <div>
                    <div class="font-medium leading-tight text-[15px]">
                        Rapat Koordinasi Pendapatan
                    </div>
                    <div class="text-xs text-slate-500 mt-[2px] leading-tight">
                        Disimpan: {{ now()->translatedFormat('d F Y | H:i') }}
                    </div>
                </div>

                <div class="flex items-center gap-2 ml-2">
                    <button
                        class="rounded-[6px] bg-emerald-600 text-white text-[13px] px-3 py-[4px] leading-none shadow-sm hover:brightness-95">
                        Lanjutkan
                    </button>
                    <button
                        class="rounded-[6px] bg-[#B6241C] text-white text-[13px] px-3 py-[4px] leading-none shadow-sm hover:bg-rose-600/80">
                        Hapus
                    </button>
                </div>
        </div>
        @endforeach
        =======
        <div class="text-xs text-slate-400 text-center py-4">Fitur draft akan segera hadir</div>
        >>>>>>> d32d87034f696939db594942e0659778cb3ef3c3
    </div>
    </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", async function() {
    // Ubah token retrieval sesuai metode auth Anda.
    // Jika tes di browser yang sudah login via session, tidak perlu header Authorization
    const token = localStorage.getItem("auth_token");

    const headers = {
        "Accept": "application/json"
    };
    if (token) {
        headers["Authorization"] = "Bearer " + token;
    }

    try {
        // Panggil API
        const response = await fetch("http://127.0.0.1:8000/api/dashboard/stats", {
            method: "GET",
            headers: headers
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        // Debugging: lihat apa yang diterima
        console.log("Data JSON:", data);

        /* =======================================================
         * 1. BANNER + PROFIL
         * =======================================================*/
        // JSON: user_info -> {name, jabatan, unit}
        // Tidak ada: nip, email, telepon, alamat

        const uInfo = data.user_info || {};

        // Set Nama
        document.getElementById("banner-nama").innerText = uInfo.name || "User";
        document.getElementById("profile-nama").innerText = uInfo.name || "-";

        // Set Jabatan & Unit
        document.getElementById("profile-jabatan").innerText = uInfo.jabatan || "-";
        document.getElementById("profile-dinas").innerText = uInfo.unit || "-";

        // Set Placeholder untuk data yang tidak ada di JSON
        document.getElementById("profile-nip").innerText = uInfo.nip || "-"; // Tidak ada di JSON
        document.getElementById("profile-lokasi").innerText = "-"; // Tidak ada di JSON
        document.getElementById("profile-email").innerText = uInfo.email || "-"; // Tidak ada di JSON
        document.getElementById("profile-telepon").innerText = "-"; // Tidak ada di JSON

        // Menggunakan kolom "Alamat" di UI untuk menampilkan "Target Tahunan" dari skor
        if (data.skoring_utama) {
            document.getElementById("profile-target").innerText = data.skoring_utama.target_tahunan +
                " Dokumen";
        }

        /* =======================================================
         * 2. STATISTIK RINGKAS
         * =======================================================*/

        // Card 1: Total Laporan SKP (Dari statistik_skp.total_diajukan)
        if (data.statistik_skp) {
            document.getElementById("stat-val-1").innerText = data.statistik_skp.total_diajukan;

            // Card 3: Persen Diterima
            document.getElementById("stat-val-3").innerText = data.statistik_skp.total_diterima;
            document.getElementById("stat-desc-3").innerText = data.statistik_skp.persen_diterima +
                "% Dari total diterima";

            // Card 4: Persen Ditolak
            document.getElementById("stat-val-4").innerText = data.statistik_skp.total_ditolak;
            document.getElementById("stat-desc-4").innerText = data.statistik_skp.persen_ditolak +
                "% Dari total ditolak";
        }

        // Card 2: Realisasi Tahunan (Dari skoring_utama)
        if (data.skoring_utama) {
            document.getElementById("stat-val-2").innerText = data.skoring_utama.realisasi_tahunan;
            document.getElementById("stat-desc-2").innerText = data.skoring_utama.persen_capaian +
                "% Capaian";
        }

        /* =======================================================
         * 3. AKTIVITAS TERKINI
         * =======================================================*/
        const listContainer = document.getElementById("aktivitas-list");
        listContainer.innerHTML = ""; // Clear loading text

        const aktivitas = data.aktivitas_terbaru || [];

        if (aktivitas.length === 0) {
            listContainer.innerHTML =
            '<li class="text-sm text-slate-500">Belum ada aktivitas terbaru.</li>';
        } else {
            aktivitas.forEach(item => {
                const dateObj = new Date(item.tanggal_laporan);
                const tanggalFormatted = dateObj.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long', // Ganti 'numeric' jika ingin angka bulan (11), 'long' jika nama bulan (November)
                    year: 'numeric'
                });
                // Tentukan warna/icon berdasarkan status text
                let tone = 'bg-slate-200';
                let iconName = 'pending.svg'; // default
                let statusLabel = item.status;

                if (item.status === 'approved') {
                    tone = 'bg-[#128C60]/50'; // Hijau
                    iconName = 'approve.svg';
                    statusLabel = 'Disetujui';
                } else if (item.status === 'rejected' || item.status.includes('reject')) {
                    tone = 'bg-[#B6241C]/50'; // Merah
                    iconName = 'reject.svg';
                    statusLabel = 'Ditolak';
                } else if (item.status === 'waiting_review') {
                    tone = 'bg-[#D8A106]/50'; // Kuning
                    iconName = 'pending.svg';
                    statusLabel = 'Menunggu Review';
                }

                const htmlItem = `
                <li class="flex items-start gap-3">
                    <div class="h-8 w-8 rounded-[10px] flex items-center justify-center ${tone}">
                        <img src="/assets/icon/${iconName}" class="h-5 w-5 opacity-90" alt="">
                    </div>
                    <div class="flex-1">
                        <div class="text-[13px] font-medium leading-snug truncate overflow-hidden text-ellipsis whitespace-nowrap" 
                            style="max-width: 250px;"
                            title="${item.deskripsi_aktivitas}">
                            ${item.deskripsi_aktivitas}
                        </div>
                        <div class="flex justify-between mt-[2px]">
                            <span class="text-xs text-slate-500 capitalize">${statusLabel}</span>
                            <span class="text-xs text-slate-500 whitespace-nowrap">${tanggalFormatted}</span>
                        </div>
                    </div>
                </li>`;

                listContainer.insertAdjacentHTML('beforeend', htmlItem);
            });
        }

        /* =======================================================
         * 4. GRAFIK KINERJA BULANAN
         * =======================================================*/
        // JSON 'grafik_kinerja' hanya array [0,0,0...].
        // Kita asumsikan itu data "Total". 
        // Karena tidak ada breakdown accepted/rejected bulanan di JSON, kita sembunyikan dataset lain atau set 0.

        const rawData = data.grafik_kinerja || [];
        const labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

        const ctx = document.getElementById('kinerjaBulananChart').getContext('2d');

        // Gradient
        const gradientTotal = ctx.createLinearGradient(0, 0, 0, 260);
        gradientTotal.addColorStop(0, 'rgba(30, 64, 175, 0.25)');
        gradientTotal.addColorStop(1, 'rgba(30, 64, 175, 0.00)');

        new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [{
                    label: "Kinerja Bulanan",
                    data: rawData,
                    borderColor: "#1E40AF",
                    backgroundColor: gradientTotal,
                    pointBackgroundColor: "#1E40AF",
                    fill: true,
                    tension: 0.3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

    } catch (err) {
        console.error("Gagal mengambil data API:", err);
    }
});
</script>
@endpush

@endsection