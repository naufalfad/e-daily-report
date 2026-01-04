<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Peta Aktivitas</title>
    <style>
        /* --- PAGE SETUP --- */
        @page {
            margin: 110px 30px 80px 30px; /* Top margin besar untuk Header */
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.3;
        }

        /* --- HEADER (KOP SURAT) --- */
        header {
            position: fixed;
            top: -90px;
            left: 0px;
            right: 0px;
            height: 90px;
            border-bottom: 2px solid #333;
            /* background-color: #fce; Debugging Only */
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: 80px;
            text-align: center;
            vertical-align: middle;
        }

        .logo-img {
            width: 65px;
            height: auto;
        }

        .kop-text {
            text-align: center;
            vertical-align: middle;
            padding-right: 80px; /* Balance logo width */
        }

        .kop-text h1 { font-size: 14pt; margin: 0; text-transform: uppercase; font-weight: 800; }
        .kop-text h2 { font-size: 12pt; margin: 2px 0; text-transform: uppercase; font-weight: 700; }
        .kop-text p { font-size: 9pt; margin: 0; font-style: italic; }

        /* --- FOOTER (METADATA) --- */
        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 50px;
            border-top: 1px solid #aaa;
            font-size: 8pt;
            color: #666;
            padding-top: 5px;
        }

        .footer-table { width: 100%; }
        .page-number:before { content: counter(page); }

        /* --- CONTENT STYLING --- */
        .meta-info {
            margin-bottom: 15px;
            width: 100%;
            font-size: 9pt;
        }
        .meta-info td { padding: 2px 0; vertical-align: top; }
        .label { font-weight: bold; width: 120px; }

        /* Map Styling */
        .map-wrapper {
            width: 100%;
            border: 1px solid #ccc;
            padding: 4px;
            border-radius: 4px;
            margin-bottom: 20px;
            background-color: #fff;
        }
        .map-img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Fallback Box if No Map */
        .map-placeholder {
            width: 100%;
            height: 300px;
            background: #f8f9fa;
            color: #adb5bd;
            text-align: center;
            line-height: 300px;
            font-size: 10pt;
            border: 1px dashed #ced4da;
        }

        /* Legend */
        .legend-bar {
            margin-top: -10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 8pt;
        }
        .legend-item { display: inline-block; margin: 0 10px; }
        .dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 4px; }
        .bg-green { background-color: #10b981; }
        .bg-yellow { background-color: #f59e0b; }
        .bg-red { background-color: #ef4444; }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            font-size: 8pt;
        }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-approved { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-waiting { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-rejected { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

    </style>
</head>
<body>

    {{-- 1. HEADER / KOP SURAT (Fixed on every page) --}}
    <header>
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    {{-- Pastikan file ini ada di public/img/ --}}
                    <img src="{{ public_path('img/logo-kab-mimika.png') }}" class="logo-img" alt="Logo">
                </td>
                <td class="kop-text">
                    <h1>Pemerintah Kabupaten Mimika</h1>
                    <h2>{{ $meta['unit_kerja'] ?? 'Satuan Kerja Perangkat Daerah' }}</h2>
                    <p>Laporan Digital Peta Sebaran Aktivitas & Kinerja Pegawai</p>
                </td>
            </tr>
        </table>
    </header>

    {{-- 2. FOOTER (Fixed on every page) --}}
    <footer>
        <table class="footer-table">
            <tr>
                <td style="width: 40%;">
                    Generated by: <b>{{ $meta['generated_by'] ?? 'System' }}</b><br>
                    NIP: {{ $meta['user_nip'] ?? '-' }}
                </td>
                <td style="width: 20%; text-align: center;">
                    Halaman <span class="page-number"></span>
                </td>
                <td style="width: 40%; text-align: right;">
                    Waktu Cetak: {{ $meta['timestamp'] ?? date('d-m-Y H:i') }}<br>
                    <span style="font-family: monospace; font-size: 7pt;">ID: {{ $meta['trx_id'] ?? 'N/A' }}</span>
                </td>
            </tr>
        </table>
    </footer>

    {{-- 3. BODY CONTENT --}}
    <main>
        
        {{-- Title Section --}}
        <div style="text-align: center; margin-bottom: 20px;">
            <h3 style="margin: 0; text-transform: uppercase; border-bottom: 1px solid #333; display: inline-block; padding-bottom: 2px;">
                Laporan Sebaran Aktivitas
            </h3>
            <div style="margin-top: 5px; font-size: 9pt; color: #555;">
                Periode: {{ $meta['filter_scope'] ?? 'Semua Data' }}
            </div>
        </div>

        {{-- Metadata Summary (Optional) --}}
        <table class="meta-info">
            <tr>
                <td class="label">Total Data</td>
                <td>: {{ $meta['data_count'] ?? count($activities) }} Titik Aktivitas</td>
                <td class="label">Status Data</td>
                <td>: {{ $meta['security_hash'] ? 'Terverifikasi (Hash)' : 'Standard' }}</td>
            </tr>
        </table>

        {{-- Map Visual --}}
        <div class="map-wrapper">
            @isset($image)
                <img src="{{ $image }}" class="map-img">
            @else
                <div class="map-placeholder">
                    Visualisasi Peta Tidak Tersedia (Render Service Offline)
                </div>
            @endisset
        </div>

        {{-- Legend --}}
        <div class="legend-bar">
            <div class="legend-item"><span class="dot bg-green"></span> Disetujui</div>
            <div class="legend-item"><span class="dot bg-yellow"></span> Menunggu Validasi</div>
            <div class="legend-item"><span class="dot bg-red"></span> Ditolak/Revisi</div>
        </div>

        {{-- Data Table --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Uraian Kegiatan</th>
                    <th style="width: 20%;">Waktu & Tanggal</th>
                    <th style="width: 25%;">Lokasi Tercatat (Geo-Tag)</th>
                    <th style="width: 15%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $index => $a)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-weight: bold; margin-bottom: 2px;">{{ $a['kegiatan'] }}</div>
                        <div style="color: #666; font-size: 8pt; font-style: italic;">
                            Output: {{ Str::limit($a['deskripsi'], 50) }}
                        </div>
                    </td>
                    <td>
                        {{ $a['tanggal'] }}<br>
                        <span style="color: #666;">{{ $a['waktu'] }}</span>
                    </td>
                    <td>
                        @if(!empty($a['lokasi_teks']))
                            {{ $a['lokasi_teks'] }}
                        @else
                            <span style="color: #999; font-style: italic;">Koordinat: {{ $a['lat'] }}, {{ $a['lng'] }}</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($a['status'] == 'approved')
                            <span class="badge badge-approved">Disetujui</span>
                        @elseif($a['status'] == 'rejected')
                            <span class="badge badge-rejected">Ditolak</span>
                        @else
                            <span class="badge badge-waiting">Menunggu</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #999;">
                        Tidak ada data aktivitas untuk ditampilkan pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </main>

</body>
</html>