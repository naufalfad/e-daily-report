<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Harian Pegawai</title>

    {{-- Tailwind Precompiled (AMAN DOMPDF) --}}
    <style>
    /* Tailwind Utility Core untuk PDF */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 12px;
    }

    .text-center {
        text-align: center;
    }

    .font-bold {
        font-weight: bold;
    }

    .font-semibold {
        font-weight: 600;
    }

    .uppercase {
        text-transform: uppercase;
    }

    .mt-1 {
        margin-top: 4px;
    }

    .mt-2 {
        margin-top: 8px;
    }

    .mt-4 {
        margin-top: 16px;
    }

    .mt-6 {
        margin-top: 24px;
    }

    .mb-1 {
        margin-bottom: 4px;
    }

    .mb-2 {
        margin-bottom: 8px;
    }

    .mb-3 {
        margin-bottom: 12px;
    }

    .mb-4 {
        margin-bottom: 16px;
    }

    .mb-6 {
        margin-bottom: 24px;
    }

    .p-4 {
        padding: 16px;
    }

    .p-6 {
        padding: 24px;
    }

    .border {
        border-width: 1px;
        border-color: #333;
    }

    .rounded {
        border-radius: 4px;
    }

    .w-full {
        width: 100%;
    }

    .text-sm {
        font-size: 12px;
    }

    .leading-relaxed {
        line-height: 1.6;
    }

    .bg-gray {
        background: #f8f8f8;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    td {
        padding: 6px 4px;
        vertical-align: top;
    }

    .label {
        width: 170px;
        font-weight: bold;
    }

    .header-line {
        border-bottom: 2px solid #000;
        padding-bottom: 12px;
        margin-bottom: 24px;
    }
    </style>
</head>

<body class="p-6">

    {{-- HEADER --}}
    <div class="text-center header-line">
        <h1 class="font-bold uppercase text-sm mb-1">Laporan Kerja Harian Pegawai</h1>
        <p class="text-sm">Aplikasi E-Daily Report</p>
        <p class="text-sm">Badan Pendapatan Daerah — Kabupaten Mimika</p>
    </div>

    {{-- INFORMASI PEGAWAI --}}
    <h2 class="font-bold text-sm uppercase mb-2">Informasi Pegawai</h2>
    <table class="text-sm mb-4">
        <tr>
            <td class="label">Nama Pegawai</td>
            <td>: {{ $pegawai_nama }}</td>
        </tr>
        <tr>
            <td class="label">NIP</td>
            <td>: {{ $pegawai_nip }}</td>
        </tr>
        <tr>
            <td class="label">Unit Kerja</td>
            <td>: {{ $pegawai_unit }}</td>
        </tr>
    </table>

    {{-- INFORMASI LAPORAN --}}
    <h2 class="font-bold text-sm uppercase mb-2">Informasi Laporan</h2>
    <table class="text-sm mb-4">
        <tr>
            <td class="label">Tanggal</td>
            <td>: {{ $tanggal }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Kegiatan</td>
            <td>: {{ $jenis_kegiatan }}</td>
        </tr>
        <tr>
            <td class="label">Referensi Tupoksi</td>
            <td>: {{ $tupoksi }}</td>
        </tr>
        <tr>
            <td class="label">Kategori</td>
            <td>: {{ $kategori }}</td>
        </tr>
        @if($kategori === 'SKP')
        <tr>
            <td class="label">Target SKP</td>
            <td>: {{ $target_skp }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Jam Mulai</td>
            <td>: {{ $jam_mulai }}</td>
        </tr>
        <tr>
            <td class="label">Jam Selesai</td>
            <td>: {{ $jam_selesai }}</td>
        </tr>
        <tr>
            <td class="label">Lokasi</td>
            <td>: {{ $lokasi }}</td>
        </tr>
    </table>

    {{-- URAIAN KEGIATAN --}}
    <h2 class="font-bold text-sm uppercase mb-2">Uraian Kegiatan</h2>
    <div class="border rounded bg-gray p-4 leading-relaxed text-sm">
        {!! nl2br(e($uraian_kegiatan)) !!}
    </div>

    {{-- OUTPUT --}}
    <h2 class="font-bold text-sm uppercase mt-6 mb-2">Output Kegiatan</h2>
    <table class="text-sm">
        <tr>
            <td class="label">Output</td>
            <td>: {{ $output }}</td>
        </tr>
        <tr>
            <td class="label">Volume</td>
            <td>: {{ $volume }}</td>
        </tr>
        <tr>
            <td class="label">Satuan</td>
            <td>: {{ $satuan }}</td>
        </tr>
    </table>

    {{-- TTD --}}
    <div class="mt-6 text-sm" style="text-align:right;">
        <p>Mimika, {{ now()->translatedFormat('d F Y') }}</p>
        <br><br><br>
        <p class="font-semibold" style="text-decoration: underline;">{{ $pegawai_nama }}</p>
        <p>NIP. {{ $pegawai_nip }}</p>
    </div>

</body>

</html>