<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Skoring Kinerja Pegawai</title>

    <!-- TAILWIND COMPATIBLE INLINE CSS -->
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        padding: 24px;
        font-size: 14px;
        color: #111827;
    }

    .flex {
        display: flex;
    }

    .items-center {
        align-items: center;
    }

    .justify-between {
        justify-content: space-between;
    }

    .justify-center {
        justify-content: center;
    }

    .text-center {
        text-align: center;
    }

    .font-bold {
        font-weight: 700;
    }

    .font-semibold {
        font-weight: 600;
    }

    .font-medium {
        font-weight: 500;
    }

    .text-gray-500 {
        color: #6B7280;
    }

    .text-gray-700 {
        color: #374151;
    }

    .text-gray-800 {
        color: #1F2937;
    }

    .text-xl {
        font-size: 20px;
    }

    .text-2xl {
        font-size: 24px;
    }

    .text-sm {
        font-size: 13px;
    }

    .text-xs {
        font-size: 12px;
    }

    .mt-1 {
        margin-top: 4px;
    }

    .mt-6 {
        margin-top: 24px;
    }

    .mb-4 {
        margin-bottom: 16px;
    }

    .mb-6 {
        margin-bottom: 24px;
    }

    .mb-8 {
        margin-bottom: 32px;
    }

    .mr-1 {
        margin-right: 4px;
    }

    .p-4 {
        padding: 16px;
    }

    .p-3 {
        padding: 12px;
    }

    .p-2 {
        padding: 8px;
    }

    .rounded-lg {
        border-radius: 12px;
    }

    .rounded-xl {
        border-radius: 16px;
    }

    .border {
        border: 1px solid #D1D5DB;
    }

    .border-gray {
        border-color: #D1D5DB;
    }

    .bg-gray-50 {
        background: #F9FAFB;
    }

    .bg-gray-100 {
        background: #F3F4F6;
    }

    .grid-3 {
        display: flex;
        gap: 12px;
    }

    .grid-item {
        flex: 1;
    }

    /* BADGE COLORS */
    .badge {
        padding: 4px 10px;
        border-radius: 12px;
        color: white;
        font-weight: 700;
        font-size: 12px;
    }

    .sangatbaik {
        background: #16A34A;
    }

    .baik {
        background: #3B82F6;
    }

    .cukup {
        background: #F59E0B;
    }

    .kurang {
        background: #EF4444;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th {
        background: #F3F4F6;
        padding: 8px;
        border: 1px solid #D1D5DB;
        text-transform: uppercase;
        font-size: 11px;
    }

    td {
        padding: 8px;
        border: 1px solid #E5E7EB;
        font-size: 13px;
    }
    </style>
</head>

<body>

    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold">Laporan Skoring Kinerja Pegawai</h2>
        <p class="text-gray-600 mt-1 text-sm">
            Disusun oleh: <b>{{ $atasan->name }}</b> |
            Tanggal: {{ now()->format('d M Y') }}
        </p>
    </div>

    {{-- CARD STATISTIK (3 KOLOM) --}}
    <div class="grid-3 mb-6">
        <div class="grid-item bg-gray-50 border rounded-lg p-4 text-center">
            <div class="text-gray-700 text-sm font-medium">Total Bawahan</div>
            <div class="text-2xl font-bold mt-1">{{ $bawahan->count() }}</div>
        </div>

        <div class="grid-item bg-gray-50 border rounded-lg p-4 text-center">
            <div class="text-gray-700 text-sm font-medium">Rata-rata Skor</div>
            <div class="text-2xl font-bold mt-1">{{ round($avgScore) }}%</div>
        </div>

        <div class="grid-item bg-gray-50 border rounded-lg p-4 text-center">
            <div class="text-gray-700 text-sm font-medium">Perlu Pembinaan</div>
            <div class="text-2xl font-bold mt-1">{{ $pembinaan }}</div>
        </div>
    </div>

    {{-- TABEL --}}
    <table>
        <thead>
            <tr>
                <th>Nama Pegawai</th>
                <th>Unit Kerja</th>
                <th>Realisasi</th>
                <th>Skor</th>
                <th>Predikat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bawahan as $b)
            <tr>
                <td>{{ $b->name }}</td>
                <td>{{ $b->unitKerja->nama ?? '-' }}</td>
                <td>{{ $b->acc_lkh }} / {{ $b->total_lkh }}</td>
                <td>{{ $b->skor }}%</td>
                <td>
                    <span class="badge {{ strtolower(str_replace(' ', '', $b->predikat)) }}">
                        {{ $b->predikat }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>