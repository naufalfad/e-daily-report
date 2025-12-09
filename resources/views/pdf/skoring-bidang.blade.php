<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Per Bidang</title>
    <style>
        /* Menggunakan Style "Masterpiece" yang sudah disetujui Yang Mulia */
        @page { margin: 0px; padding: 0px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; background-color: #ffffff; }
        .content-wrapper { padding: 40px; }
        
        /* HEADER HERO */
        .header-hero {
            background-color: #1e40af; color: #ffffff; padding: 30px 40px;
            border-bottom: 8px solid #f59e0b;
        }
        .header-title { font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .header-subtitle { font-size: 12px; opacity: 0.9; margin-top: 5px; }
        
        /* STATS CARDS */
        .stats-table {
            width: 100%; border-collapse: separate; border-spacing: 20px 0;
            margin-bottom: 30px; margin-left: -20px; margin-right: -20px; width: calc(100% + 40px);
        }
        .stat-card {
            background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px;
            padding: 20px; width: 33.33%; vertical-align: top;
        }
        .card-blue { border-top: 4px solid #3b82f6; }
        .card-purple { border-top: 4px solid #8b5cf6; }
        .card-red { border-top: 4px solid #ef4444; }
        .card-green { border-top: 4px solid #22c55e; }
        .stat-label { font-size: 11px; color: #64748b; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; }
        .stat-value { font-size: 32px; font-weight: 800; color: #0f172a; }

        /* DATA TABLE */
        .custom-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        .custom-table th {
            background-color: #eff6ff; color: #1e3a8a; border-bottom: 2px solid #3b82f6;
            padding: 12px 10px; text-align: left; text-transform: uppercase; font-weight: 800;
        }
        .custom-table td { padding: 12px 10px; border-bottom: 1px solid #e2e8f0; color: #334155; vertical-align: middle; }
        .custom-table tr:nth-child(even) { background-color: #f8fafc; }

        /* BADGES */
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 10px; font-weight: bold; text-transform: uppercase; display: inline-block; min-width: 80px; text-align: center; }
        .badge-sb { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-b  { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
        .badge-c  { background: #fef9c3; color: #a16207; border: 1px solid #fde047; }
        .badge-k  { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        /* SIGNATURE */
        .signature-container { margin-top: 50px; page-break-inside: avoid; width: 100%; }
        .signature-box { float: right; width: 250px; text-align: center; }
        .signature-line { margin-top: 70px; border-bottom: 1px solid #000; display: inline-block; width: 100%; }
    </style>
</head>
<body>

    <div class="header-hero">
        <h1 class="header-title">Laporan Kinerja Bidang</h1>
        <div class="header-subtitle">Badan Pendapatan Daerah Kabupaten Mimika</div>
        <div style="margin-top:15px; font-size:10px; background:rgba(255,255,255,0.2); display:inline-block; padding:5px 10px; border-radius:4px;">
            PERIODE: {{ $month ? \Carbon\Carbon::create()->month($month)->translatedFormat('F') : 'SEMUA' }} {{ $year ?? now()->year }}
        </div>
    </div>

    <div class="content-wrapper">
        {{-- STATS --}}
        <table class="stats-table">
            <tr>
                <td class="stat-card card-blue">
                    <div class="stat-label">Total Bidang</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </td>
                <td class="stat-card card-purple">
                    <div class="stat-label">Rata-rata Kinerja</div>
                    <div class="stat-value">{{ round($stats['avg']) }}%</div>
                </td>
                <td class="stat-card {{ $stats['alert'] > 0 ? 'card-red' : 'card-green' }}">
                    <div class="stat-label">Perlu Evaluasi</div>
                    <div class="stat-value" style="color: {{ $stats['alert'] > 0 ? '#ef4444' : '#22c55e' }}">
                        {{ $stats['alert'] }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- TABLE --}}
        <table class="custom-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="35%">Nama Bidang</th>
                    <th width="25%">Kepala Bidang</th>
                    <th width="15%" style="text-align:center;">Realisasi</th>
                    <th width="10%" style="text-align:center;">Skor</th>
                    <th width="10%" style="text-align:center;">Predikat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div style="font-weight:bold; color:#0f172a;">{{ $item['nama_bidang'] }}</div>
                    </td>
                    <td>
                        <div style="font-size:11px;">{{ $item['nama_kabid'] }}</div>
                    </td>
                    <td style="text-align:center;">
                        <span style="font-weight:bold;">{{ $item['total_approved'] }}</span> 
                        <span style="color:#94a3b8;">/ {{ $item['total_submitted'] }}</span>
                    </td>
                    <td style="text-align:center; font-weight:bold; font-size:13px;">
                        {{ $item['persentase'] }}%
                    </td>
                    <td style="text-align:center;">
                        @php
                            $p = strtolower(str_replace(' ', '', $item['predikat']));
                            $cls = 'badge-k';
                            if($p == 'sangatbaik') $cls = 'badge-sb';
                            elseif($p == 'baik') $cls = 'badge-b';
                            elseif($p == 'cukup') $cls = 'badge-c';
                        @endphp
                        <span class="badge {{ $cls }}">{{ $item['predikat'] }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:30px; font-style:italic; color:#94a3b8;">
                        Tidak ada data bidang yang tersedia.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- SIGNATURE --}}
        <div class="signature-container">
            <div class="signature-box">
                <div style="margin-bottom: 5px; color:#334155;">Mimika, {{ now()->translatedFormat('d F Y') }}</div>
                <div style="font-weight:bold; color:#0f172a;">Kepala Dinas,</div>
                <div class="signature-line"></div>
                <div style="margin-top: 5px; font-weight:bold; text-decoration:underline;">{{ $kadis->name }}</div>
                <div style="font-size:11px; color:#64748b;">NIP. {{ $kadis->nip ?? '-' }}</div>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
</body>
</html>