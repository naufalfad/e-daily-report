<!DOCTYPE html>
<html>

<head>
    <style>
    body {
        font-family: sans-serif;
        font-size: 12px;
    }

    .title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .map-img {
        width: 100%;
        border-radius: 12px;
        border: 2px solid #1C7C54;
        margin-bottom: 15px;
    }

    .legend {
        margin: 10px 0 20px 0;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-right: 18px;
    }

    .dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 6px;
    }

    .dot-green {
        background: #22c55e;
    }

    .dot-yellow {
        background: #f59e0b;
    }

    .dot-red {
        background: #ef4444;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th,
    td {
        padding: 6px 5px;
        border-bottom: 1px solid #ddd;
        font-size: 11px;
    }

    th {
        background: #f0f0f0;
        font-weight: 600;
    }
    </style>
</head>

<body>

    <div class="title">Laporan Peta Aktivitas</div>

    {{-- BLOK META DIHAPUS SEPENUHNYA UNTUK MENGHINDARI ERROR --}}

    @isset($image)
    <img src="{{ $image }}" class="map-img">
    @else
    {{-- Placeholder yang lebih profesional --}}
    <div style="width: 100%; height: 350px; border: 1px dashed #cccccc; background: #f9f9f9; text-align: center; line-height: 350px; font-size: 12px; color: #666; border-radius: 12px;">
        Gagal memuat visual peta dari service renderer (Pastikan service Node.js berjalan di port 3000).
    </div>
    @endisset

    <div class="legend">
        <div class="legend-item">
            <div class="dot dot-green"></div> Disetujui
        </div>
        <div class="legend-item">
            <div class="dot dot-yellow"></div> Menunggu Validasi
        </div>
        <div class="legend-item">
            <div class="dot dot-red"></div> Ditolak
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kegiatan</th>
                <th>Tanggal & Waktu</th>
                <th>Status</th>
                <th>Lokasi Teks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $a)
            <tr>
                {{-- Menggunakan array syntax $a['key'] --}}
                <td>{{ $a['kegiatan'] }}</td> 
                <td>{{ $a['tanggal'] }} {{ $a['waktu'] }}</td>
                <td>
                    {{-- Menggunakan array syntax $a['status'] --}}
                    @if($a['status'] == 'approved')
                    Disetujui
                    @elseif($a['status'] == 'rejected')
                    Ditolak
                    @else
                    Menunggu Validasi
                    @endif
                </td>
                <td>
                    {{-- Menggunakan array syntax $a['lokasi_teks'] --}}
                    {{ $a['lokasi_teks'] ?? '—' }} 
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>