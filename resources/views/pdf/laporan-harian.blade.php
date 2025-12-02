<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Harian Pegawai</title>

    {{-- Tailwind CDN (DOMPDF aman karena dicompile jadi CSS biasa) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
    /* Override agar DOMPDF lebih stabil */
    body {
        font-family: sans-serif;
    }

    .page-break {
        page-break-after: always;
    }
    </style>
</head>

<body class="p-8">

    {{-- HEADER --}}
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold">Laporan Kerja Harian</h1>
        <p class="text-sm text-gray-600">Aplikasi E-Daily Report — Bapenda Kabupaten Mimika</p>
    </div>

    {{-- INFORMASI PEGAWAI --}}
    <h2 class="text-lg font-semibold mb-2">Informasi Pegawai</h2>

    <table class="w-full text-sm mb-6">
        <tr>
            <td class="w-40 font-medium">Nama Pegawai</td>
            <td>: {{ $pegawai_nama }}</td>
        </tr>
        <tr>
            <td class="font-medium">NIP</td>
            <td>: {{ $pegawai_nip }}</td>
        </tr>
        <tr>
            <td class="font-medium">Unit Kerja</td>
            <td>: {{ $pegawai_unit }}</td>
        </tr>
    </table>

    {{-- INFORMASI LAPORAN --}}
    <h2 class="text-lg font-semibold mb-2">Informasi Laporan</h2>

    <table class="w-full text-sm mb-6">
        <tr>
            <td class="w-40 font-medium">Tanggal</td>
            <td>: {{ $tanggal }}</td>
        </tr>
        <tr>
            <td class="font-medium">Jenis Kegiatan</td>
            <td>: {{ $jenis_kegiatan }}</td>
        </tr>
        <tr>
            <td class="font-medium">Referensi Tupoksi</td>
            <td>: {{ $tupoksi }}</td>
        </tr>
        <tr>
            <td class="font-medium">Kategori</td>
            <td>: {{ $kategori }}</td>
        </tr>

        @if($kategori === 'SKP')
        <tr>
            <td class="font-medium">Target SKP</td>
            <td>: {{ $target_skp }}</td>
        </tr>
        @endif

        <tr>
            <td class="font-medium">Jam Mulai</td>
            <td>: {{ $jam_mulai }}</td>
        </tr>
        <tr>
            <td class="font-medium">Jam Selesai</td>
            <td>: {{ $jam_selesai }}</td>
        </tr>
        <tr>
            <td class="font-medium">Lokasi</td>
            <td>: {{ $lokasi }}</td>
        </tr>
    </table>

    {{-- URAIAN --}}
    <h2 class="text-lg font-semibold mb-2">Uraian Kegiatan</h2>

    <div class="border rounded-lg p-4 text-sm leading-relaxed bg-gray-50">
        {!! nl2br(e($uraian_kegiatan)) !!}
    </div>

    {{-- OUTPUT --}}
    <h2 class="text-lg font-semibold mt-6 mb-2">Output Kegiatan</h2>

    <table class="w-full text-sm">
        <tr>
            <td class="w-40 font-medium">Output</td>
            <td>: {{ $output }}</td>
        </tr>
        <tr>
            <td class="font-medium">Volume</td>
            <td>: {{ $volume }}</td>
        </tr>
        <tr>
            <td class="font-medium">Satuan</td>
            <td>: {{ $satuan }}</td>
        </tr>
    </table>

</body>

</html>