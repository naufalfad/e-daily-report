<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kinerja Harian</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; text-align: center; }
        .header { text-align: center; margin-bottom: 30px; }
        .meta { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h3>LAPORAN KINERJA HARIAN (LKH)</h3>
        <p>Badan Pendapatan Daerah Kabupaten Mimika</p>
    </div>

    <div class="meta">
        <b>Periode:</b> {{ $startDate }} s.d {{ $endDate }} <br>
        <b>Dicetak Oleh:</b> {{ auth()->user()->name }} <br>
        <b>Tanggal Cetak:</b> {{ now()->format('d-m-Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Waktu</th>
                <th style="width: 20%">Pegawai</th>
                <th style="width: 15%">Kegiatan</th>
                <th style="width: 35%">Uraian & Output</th>
                <th style="width: 15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $lkh)
            <tr>
                <td>
                    {{ $lkh->tanggal_laporan }}<br>
                    <small>{{ $lkh->waktu_mulai }} - {{ $lkh->waktu_selesai }}</small>
                </td>
                <td>{{ $lkh->user->name }}</td>
                <td>{{ $lkh->jenis_kegiatan }}</td>
                <td>
                    <b>Aktivitas:</b> {{ $lkh->deskripsi_aktivitas }}<br>
                    <b>Output:</b> {{ $lkh->output_hasil_kerja }}
                </td>
                <td style="text-align: center">
                    {{ strtoupper($lkh->status) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>