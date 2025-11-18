@php($title = 'Dashboard Penilai')
@extends('layouts.app', ['title' => $title, 'role' => 'kadis', 'active' => 'dashboard'])

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
                    <h3 class="text-[17px] font-semibold text-slate-900 leading-snug">
                        Fahrizal Mudzaqi Maulana
                    </h3>
                    <p class="mt-[2px] text-[13px] text-slate-500 leading-snug">
                        196703101988030109
                    </p>

                    {{-- Lokasi --}}
                    <div class="mt-1.5 flex items-center gap-1.5 text-[13px] text-slate-500">
                        <img src="{{ asset('assets/icon/location.svg') }}" class="h-4 w-4" alt="Lokasi" />
                        <span class="truncate">Mimika, Papua Tengah</span>
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
            <button
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-sm hover:bg-slate-50">
                Pilih Tahun
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor">
                    <path stroke-width="1.6" stroke-linecap="round" d="m6 9 6 6 6-6" />
                </svg>
            </button>
        </div>

        {{-- Area chart fleksibel + canvas --}}
        <div class="mt-1 flex-1">
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

                {{-- ICON DI KOTAK, GAYA SAMA DENGAN STATISTIK RINGKAS --}}
                <div class="h-10 w-10 rounded-[10px] flex items-center justify-center 
                    {{ $activity['tone'] }} ">
                    <img src="{{ asset('assets/icon/' . $activity['icon']) }}" class="h-5 w-5 opacity-90" alt="">
                </div>

                {{-- TEKS --}}
                <div class="flex-1">
                    {{-- Judul --}}
                    <div class="text-[15px] font-medium leading-snug">
                        {{ $activity['title'] }}
                    </div>

                    {{-- Status + Tanggal sejajar --}}
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


    {{-- DRAFT LAPORAN (kanan bawah, sejajar bawah dengan grafik) --}}
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 flex flex-col">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold">Draft Laporan</h3>
            <a href="#" class="text-sm text-[#1C7C54] hover:underline">Lihat Semua Draft</a>
        </div>

        <div class="space-y-2">
            @foreach ([1, 2] as $i)
            <div class="rounded-xl bg-[#F1F5F9] px-3 py-2.5 flex items-center justify-between">

                {{-- Judul + Tanggal --}}
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
                        class="rounded-[6px] bg-[#B6241C] text-white text-[13px] px-3 py-[4px] leading-none shadow-sm hover:bg-rose-600/15">
                        Hapus
                    </button>
                </div>

            </div>
            @endforeach
        </div>
    </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('kinerjaBulananChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    // Data dummy – nanti bisa diganti dari database
    const dataTotal = [78, 38, 18, 68, 32, 12, 44, 96, 82, 15, 44, 88];
    const dataDiterima = [30, 98, 48, 68, 94, 36, 84, 60, 59, 38, 62, 94];
    const dataDitolak = [86, 40, 92, 20, 68, 84, 88, 78, 92, 20, 78, 65];

    const gradientTotal = ctx.createLinearGradient(0, 0, 0, 260);
    gradientTotal.addColorStop(0, 'rgba(30, 64, 175, 0.25)');
    gradientTotal.addColorStop(1, 'rgba(30, 64, 175, 0.00)');

    const gradientDiterima = ctx.createLinearGradient(0, 0, 0, 260);
    gradientDiterima.addColorStop(0, 'rgba(28, 124, 84, 0.25)');
    gradientDiterima.addColorStop(1, 'rgba(28, 124, 84, 0.00)');

    const gradientDitolak = ctx.createLinearGradient(0, 0, 0, 260);
    gradientDitolak.addColorStop(0, 'rgba(182, 36, 28, 0.25)');
    gradientDitolak.addColorStop(1, 'rgba(182, 36, 28, 0.00)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                    label: 'Total Laporan',
                    data: dataTotal,
                    borderColor: '#1E40AF',
                    backgroundColor: gradientTotal,
                    pointBackgroundColor: '#1E40AF',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    tension: 0,
                    fill: true
                },
                {
                    label: 'Laporan Diterima',
                    data: dataDiterima,
                    borderColor: '#1C7C54',
                    backgroundColor: gradientDiterima,
                    pointBackgroundColor: '#1C7C54',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    tension: 0,
                    fill: true,
                },
                {
                    label: 'Laporan Ditolak',
                    data: dataDitolak,
                    borderColor: '#B6241C',
                    backgroundColor: gradientDitolak,
                    pointBackgroundColor: '#B6241C',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    tension: 0,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        boxWidth: 10,
                        font: {
                            size: 11
                        },
                    },
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                },
            },
            scales: {
                x: {
                    grid: {
                        display: true,
                        color: 'rgba(148, 163, 184, 0.2)',
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.25)',
                    },
                    ticks: {
                        stepSize: 20,
                        font: {
                            size: 11
                        },
                    },
                },
            },
        },
    });
});
</script>
@endpush

@endsection