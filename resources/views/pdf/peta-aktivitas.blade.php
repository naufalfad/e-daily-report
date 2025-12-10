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

    <p>
        Nama Pegawai: <strong>{{ $meta['nama'] }}</strong><br>
        Tanggal Export: <strong>{{ $meta['tanggal_laporan'] }}</strong>
    </p>

    <!-- MAP -->
    <img src="{{ $image }}" class="map-img">

    <!-- LEGENDA STATUS -->
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

    <!-- TABEL TITIK KOORDINAT -->
    <table>
        <tbody>
            @foreach($activities as $a)
            <tr>
                <td>{{ $a->kegiatan }}</td>
                <td>{{ $a->tanggal }} {{ $a->waktu }}</td>
                <td>
                    @if($a->status == 'approved')
                    Disetujui
                    @elseif($a->status == 'rejected')
                    Ditolak
                    @else
                    Menunggu Validasi
                    @endif
                </td>
                <td>{{ $a->lat }}</td>
                <td>{{ $a->lng }}</td>
                <td>
                    {{ $a->lokasi ?? '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>