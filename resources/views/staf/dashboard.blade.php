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
            <h1 class="text-[20px] md:text-[28px] font-bold leading-tight mt-1 md:whitespace-nowrap">
                Fahrizal Mudzaqi Maulana!
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
               overflow-hidden -mt-[50px]">

        {{-- HEADER HIJAU --}}
        <div class="bg-[#1C7C54] text-white px-5 py-4 text-[15px] font-semibold leading-tight">
            Profil Saya
        </div>

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
                    <h3 class="text-[17px] font-semibold text-slate-900 leading-snug">
                        Fahrizal Mudzaqi Maulana
                    </h3>
                    <p class="mt-[2px] text-[13px] text-slate-500 leading-snug">
                        196703101988030109
                    </p>

                    {{-- Lokasi --}}
                    <div class="mt-1.5 flex items-center gap-1.5 text-[13px] text-slate-500">
                        <img src="{{ asset('assets/icon/location.svg') }}" class="h-4 w-4" alt="Lokasi" />
                        <span class="truncate" id="profile-alamat">-</span>
                    </div>
                </div>
            </div>

            {{-- EMAIL + TELEPON --}}
            <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-1 text-[13px] text-slate-600">
                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                    <img src="{{ asset('assets/icon/email.svg') }}" class="h-3.5 w-3.5" alt="Email" />
                    <span class="truncate">sari.dewi@bapendamimika.go.id</span>
                </div>
                <div class="flex items-center gap-1.5 whitespace-nowrap">
                    <img src="{{ asset('assets/icon/telpon.svg') }}" class="h-3.5 w-3.5" alt="Telepon" />
                    <span>081234567891</span>
                </div>
            </div>

            {{-- JABATAN / DINAS / ALAMAT --}}
            <div class="mt-4 border-t border-slate-200 pt-3 grid grid-cols-3 text-[13px] text-slate-700">
                {{-- Jabatan --}}
                <div class="pr-3">
                    <p class="text-[11px] tracking-[0.06em] uppercase text-slate-400">Jabatan</p>
                    <p class="mt-1 font-semibold text-slate-900 leading-snug">Staf BAPENDA</p>
                </div>

                {{-- Dinas --}}
                <div class="px-3 border-l border-slate-200">
                    <p class="text-[11px] tracking-[0.06em] uppercase text-slate-400">Dinas</p>
                    <p class="mt-1 font-semibold text-slate-900 leading-snug">
                        Badan Pendapatan<br>Daerah
                    </p>
                </div>

                {{-- Alamat --}}
                <div class="pl-3 border-l border-slate-200">
                    <p class="text-[11px] tracking-[0.06em] uppercase text-slate-400">Alamat</p>
                    <p class="mt-1 font-semibold text-slate-900 leading-snug">
                        Jl. Cenderawasih,<br>Mimika Baru
                    </p>
                </div>
            </div>

        </div>
    </aside>
</section>

{{-- Statistik ringkas --}}
<section class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mt-4">

    @foreach ([
    [
    'val' => '10',
    'label' => 'Total Laporan Terkirim Hari ini',
    'tone' => 'bg-[#155FA6]/50',
    'icon' => 'send'
    ],
    [
    'val' => '4',
    'label' => 'Menunggu Verifikasi',
    'tone' => 'bg-[#D8A106]/50',
    'icon' => 'pending'
    ],
    [
    'val' => '2',
    'label' => 'Disetujui',
    'tone' => 'bg-[#128C60]/50',
    'icon' => 'approve'
    ],
    [
    'val' => '4',
    'label' => 'Ditolak',
    'tone' => 'bg-[#B6241C]/50',
    'icon' => 'reject'
    ],
    ] as $stat)

    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col gap-2">
        {{-- NILAI + ICON --}}
        <div class="flex items-start justify-between">
            {{-- Angka --}}
            <div class="text-4xl font-semibold tracking-tight">
                {{ $stat['val'] }}
            </div>

            {{-- Icon Wrapper --}}
            <div class="flex items-center justify-center h-10 w-10 rounded-[10px] {{ $stat['tone'] }}">
                <img src="{{ asset('assets/icon/' . $stat['icon'] . '.svg') }}" alt="{{ $stat['icon'] }}"
                    class="h-5 w-5 object-contain">
            </div>
        </div>

        {{-- Label --}}
        <div class="text-xs text-slate-500">{{ $stat['label'] }}</div>

        {{-- Additional Info --}}
        @if ($stat['icon'] === 'approve')
        <div class="text-xs text-emerald-600 font-medium">↑ 85% Approval Rate</div>
        @elseif ($stat['icon'] === 'reject')
        <div class="text-xs text-rose-600 font-medium">↓ 5% Rejection Rate</div>
        @elseif ($stat['icon'] === 'send')
        <div class="text-xs text-emerald-600 font-medium">↑ 12% dari kemarin</div>
        @else
        <div class="text-xs text-amber-600 font-medium">⚠ Perlu perhatian</div>
        @endif
    </div>

    @endforeach
</section>

{{-- Grafik + Aktivitas terkini + Draft Laporan --}}
<section class="mt-4 grid grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.4fr)] gap-4">

    {{-- GRAFIK (kartu tinggi, span 2 baris) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 lg:row-span-2 flex flex-col">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">Grafik Kinerja Bulanan</h3>
            <button class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-sm hover:bg-slate-50">
                {{ date('Y') }}
            </button>
        </div>

        {{-- Area chart fleksibel + canvas --}}
        <div class="mt-1 flex-1">
        {{-- Area chart fleksibel + canvas --}}
        <div class="mt-1 flex-1">
            <canvas id="kinerjaBulananChart" class="w-full h-full"></canvas>
        </div>
    </div>

    {{-- AKTIVITAS TERKINI (kanan atas) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4">
        <h3 class="font-semibold mb-3">Aktivitas Terkini</h3>
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

        {{-- LIST DRAFT DINAMIS --}}
        <ul id="draft-list" class="space-y-2">
            <li class="text-sm text-slate-500">Memuat...</li> {{-- default loading --}}
        </ul>
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
        document.getElementById("profile-alamat").innerText = uInfo.alamat || "-"; // Tidak ada di JSON
        document.getElementById("profile-email").innerText = uInfo.email || "-"; // Tidak ada di JSON
        document.getElementById("profile-telepon").innerText = uInfo.no_telp || "-"; // Tidak ada di JSON

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
        * 4. DAFTAR DRAFT
        * =======================================================*/
        const draftContainer = document.getElementById("draft-list");
        draftContainer.innerHTML = ""; // Clear loading

        const draft = data.draft_terbaru || [];

        if (draft.length === 0) {
            draftContainer.innerHTML =
                '<li class="text-sm text-slate-500">Belum ada draft.</li>';
        } else {
            draft.forEach(item => {

                // Format tanggal
                const dateObj = new Date(item.updated_at);
                const tanggalFormatted = dateObj.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });

                const htmlItem = `
                <li class="rounded-xl bg-[#F1F5F9] px-3 py-2.5 flex items-start justify-between gap-4">
                    
                    <!-- BAGIAN TEKS -->
                    <div class="flex-1 min-w-0"> 
                        <div class="font-medium leading-tight text-[15px] truncate"
                            title="${item.deskripsi_aktivitas}">
                            ${item.deskripsi_aktivitas}
                        </div>
                        <div class="text-xs text-slate-500 mt-[2px] leading-tight">
                            Disimpan: ${tanggalFormatted}
                        </div>
                    </div>

                    <!-- BAGIAN TOMBOL -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button 
                            onclick="window.location.href='/penilai/input-laporan/${item.id}'"
                            class="rounded-[6px] bg-emerald-600 text-white text-[13px] px-3 py-[4px] leading-none shadow-sm hover:brightness-95">
                            Lanjutkan
                        </button>
                        <button type="button" onclick="deleteDraft('${item.id}')"
                            class="rounded-[6px] bg-[#B6241C] text-white text-[13px] px-3 py-[4px] leading-none shadow-sm hover:bg-rose-600/80">
                            Hapus
                        </button>
                    </div>
                </li>
                `;
                draftContainer.insertAdjacentHTML('beforeend', htmlItem);
            });
        }

        /* =======================================================
        * 5. GRAFIK KINERJA BULANAN (Dari aktivitas_terbaru)
        * =======================================================*/
        // Ambil aktivitas terbaru
        const aktivitasAll = data.grafik_aktivitas || [];

        // Siapkan array 12 bulan, default 0
        let monthlyTotal = Array(12).fill(0);
        let monthlyApproved = Array(12).fill(0);
        let monthlyRejected = Array(12).fill(0);

        // Loop aktivitas dan kelompokkan ke bulan
        aktivitasAll.forEach(item => {
            const dateObj = new Date(item.tanggal_laporan);
            const month = dateObj.getMonth(); // 0-11

            monthlyTotal[month]++;

            if (item.status === "approved") {
                monthlyApproved[month]++;
            } 
            else if (item.status === "rejected" || item.status.includes("reject")) {
                monthlyRejected[month]++;
            }
            else if (item.status === "draft") {
                monthlyTotal[month]--;
            }
        });

        // Label bulan
        const labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul",
                        "Agu", "Sep", "Okt", "Nov", "Des"];

        // Render Chart
        const ctx = document.getElementById("kinerjaBulananChart").getContext("2d");

        // Gradient Total
        const gradientTotal = ctx.createLinearGradient(0, 0, 0, 260);
        gradientTotal.addColorStop(0, "rgba(30, 64, 175, 0.25)");
        gradientTotal.addColorStop(1, "rgba(30, 64, 175, 0.00)");

        new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Total Laporan",
                        data: monthlyTotal,
                        borderColor: "#1E40AF",
                        backgroundColor: gradientTotal,
                        pointBackgroundColor: "#1E40AF",
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: "Laporan Diterima",
                        data: monthlyApproved,
                        borderColor: "#128C60",
                        pointBackgroundColor: "#128C60",
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: "Laporan Ditolak",
                        data: monthlyRejected,
                        borderColor: "#B6241C",
                        pointBackgroundColor: "#B6241C",
                        fill: false,
                        tension: 0.3
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom"
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

    } catch (err) {
        console.error("Gagal mengambil data API:", err);
    }
});
// Fungsi Hapus Laporan Global
window.deleteDraft = async function(id) {
    // 1. Konfirmasi User
    if(!confirm('Apakah Anda yakin ingin menghapus draft laporan ini?')) {
        return;
    }

    const token = localStorage.getItem("auth_token");

    try {
        // 2. Kirim Request DELETE
        const response = await fetch(`/api/lkh/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        // 3. Cek Response
        if (response.ok) {
            alert('Draft berhasil dihapus!');
            window.location.reload(); 
        } else {
            const res = await response.json();
            alert('Gagal menghapus: ' + (res.message || 'Terjadi kesalahan'));
        }

    } catch (error) {
        console.error('Error saat menghapus:', error);
        alert('Terjadi kesalahan koneksi.');
    }
}
</script>
@endpush

@endsection