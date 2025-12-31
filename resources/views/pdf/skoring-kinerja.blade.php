<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Pegawai</title>
    <style>
        /* =========================================
           1. RESET & BASIC LAYOUT
           ========================================= */
        @page {
            margin: 0px; 
            padding: 0px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1e293b; /* Slate 800 */
            line-height: 1.4;
            background-color: #ffffff;
        }

        .content-wrapper {
            padding: 40px;
        }

        /* =========================================
           2. HEADER (HERO STYLE)
           ========================================= */
        .header-hero {
            background-color: #1e40af; /* Blue 800 */
            color: #ffffff;
            padding: 30px 40px;
            border-bottom: 8px solid #f59e0b; /* Amber 500 */
        }

        .header-title {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .header-subtitle {
            font-size: 12px;
            font-weight: normal;
            opacity: 0.9;
            margin-top: 5px;
        }

        .header-meta-badge {
            background-color: rgba(255,255,255, 0.15);
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
            margin-top: 15px;
            border: 1px solid rgba(255,255,255, 0.3);
        }

        /* =========================================
           3. STATS CARDS
           ========================================= */
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 20px 0;
            margin-bottom: 30px;
            margin-left: -20px; 
            margin-right: -20px;
            width: calc(100% + 40px);
        }

        .stat-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 20px;
            width: 33.33%;
            vertical-align: top;
        }

        .card-blue { border-top: 4px solid #3b82f6; }
        .card-purple { border-top: 4px solid #8b5cf6; }
        .card-red { border-top: 4px solid #ef4444; }
        .card-green { border-top: 4px solid #22c55e; }

        .stat-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
        }

        /* =========================================
           4. DATA TABLE
           ========================================= */
        .custom-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .custom-table th {
            background-color: #eff6ff;
            color: #1e3a8a;
            border-bottom: 2px solid #3b82f6;
            padding: 12px 10px;
            text-align: left;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .custom-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: middle;
        }

        .custom-table tr:nth-child(even) {
            background-color: #f8fafc; 
        }

        /* =========================================
           5. VISUAL ELEMENTS
           ========================================= */
        .bar-bg {
            background-color: #e2e8f0;
            height: 6px;
            width: 100%;
            border-radius: 3px;
            margin-top: 6px;
            overflow: hidden;
        }
        .bar-fill {
            background-color: #3b82f6;
            height: 100%;
            border-radius: 3px;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            display: inline-block;
            min-width: 80px;
        }
        .badge-sb { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-b  { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
        .badge-c  { background: #fef9c3; color: #a16207; border: 1px solid #fde047; }
        .badge-k  { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        .signature-container {
            margin-top: 50px;
            page-break-inside: avoid;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 250px;
            text-align: center;
        }
        .signature-line {
            margin-top: 70px;
            border-bottom: 1px solid #000;
            display: inline-block;
            width: 100%;
        }
    </style>
</head>

<body>

    {{-- HERO HEADER --}}
    <div class="header-hero">
        <h1 class="header-title">Laporan Kinerja Pegawai</h1>
        <div class="header-subtitle">
            Badan Pendapatan Daerah Kabupaten Mimika
        </div>
        <div class="header-meta-badge">
            PERIODE: {{ strtoupper(now()->translatedFormat('F Y')) }}
        </div>
    </div>

    {{-- CONTENT WRAPPER --}}
    <div class="content-wrapper">

        {{-- INFO SINGKAT --}}
        <table style="width: 100%; margin-bottom: 25px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <span style="color:#64748b; font-size:10px; font-weight:bold; letter-spacing:1px;">UNIT KERJA / BIDANG</span><br>
                    <span style="font-size:14px; font-weight:bold; color:#0f172a;">
                        {{-- Atasan masih berupa Object Eloquent, jadi gunakan -> --}}
                        {{ 
                            $atasan->unitKerja->nama 
                            ?? ($atasan->bidang->nama_bidang 
                            ?? 'BAPENDA KAB. MIMIKA') 
                        }}
                    </span>
                </td>
                <td style="width: 50%; text-align: right; vertical-align: top;">
                    <span style="color:#64748b; font-size:10px; font-weight:bold; letter-spacing:1px;">DICETAK OLEH</span><br>
                    <span style="font-size:14px; color:#0f172a;">
                        {{ $atasan->name }}
                    </span>
                    <div style="font-size:10px; color:#64748b; margin-top:2px;">
                        {{ $atasan->jabatan->nama_jabatan ?? 'Pejabat Penilai' }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- STATS CARDS --}}
        <table class="stats-table">
            <tr>
                <td class="stat-card card-blue">
                    <div class="stat-label">Total Pegawai</div>
                    <div class="stat-value">{{ $bawahan->count() }}</div>
                    <div style="font-size:10px; color:#94a3b8; margin-top:5px;">Orang</div>
                </td>
                
                <td class="stat-card card-purple">
                    <div class="stat-label">Rata-rata Skor</div>
                    <div class="stat-value">{{ round($avgScore) }}%</div>
                    <div style="font-size:10px; color:#94a3b8; margin-top:5px;">Keseluruhan Tim</div>
                </td>

                <td class="stat-card {{ $pembinaan > 0 ? 'card-red' : 'card-green' }}">
                    <div class="stat-label">Perlu Pembinaan</div>
                    <div class="stat-value" style="color: {{ $pembinaan > 0 ? '#ef4444' : '#22c55e' }}">
                        {{ $pembinaan }}
                    </div>
                    <div style="font-size:10px; color:#94a3b8; margin-top:5px;">
                        {{ $pembinaan > 0 ? 'Perhatian Diperlukan' : 'Semua Aman' }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- DATA TABLE --}}
        <table class="custom-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="35%">Pegawai</th>
                    <th width="25%">Realisasi LKH</th>
                    <th width="15%" style="text-align:center;">Skor</th>
                    <th width="20%" style="text-align:center;">Predikat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bawahan as $index => $b)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{-- FIX: Menggunakan Array Access $b['key'] --}}
                        <div style="font-weight:bold; color:#0f172a;">{{ $b['nama'] }}</div>
                        <div style="font-size:10px; color:#64748b; margin-top:2px;">NIP. {{ $b['nip'] ?? '-' }}</div>
                    </td>
                    <td>
                        <div style="font-size:12px; font-weight:bold;">
                            {{ $b['realisasi'] }} <span style="font-weight:normal; color:#94a3b8;">/ {{ $b['target'] }}</span>
                        </div>
                        {{-- Visual Bar: Langsung pakai capaian karena sudah berupa persentase di Service --}}
                        <div class="bar-bg">
                            <div class="bar-fill" style="width: {{ $b['capaian'] }}%;"></div>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <span style="font-size:14px; font-weight:800; color:#1e293b;">
                            {{ $b['capaian'] }}%
                        </span>
                    </td>
                    <td style="text-align:center;">
                        @php
                            $p = strtolower(str_replace(' ', '', $b['predikat']));
                            $cls = 'badge-k';
                            if($p == 'sangatbaik') $cls = 'badge-sb';
                            elseif($p == 'baik') $cls = 'badge-b';
                            elseif($p == 'cukup') $cls = 'badge-c';
                        @endphp
                        <span class="badge {{ $cls }}">
                            {{ $b['predikat'] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:30px; color:#94a3b8; font-style:italic;">
                        Data pegawai tidak ditemukan untuk periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- SIGNATURE --}}
        <div class="signature-container">
            <div class="signature-box">
                <div style="margin-bottom: 5px; color:#334155;">Ditetapkan di Mimika, {{ now()->translatedFormat('d F Y') }}</div>
                <div style="font-weight:bold; color:#0f172a;">Pejabat Penilai,</div>
                
                <div class="signature-line"></div>
                
                {{-- Atasan tetap menggunakan Object access karena tidak melalui SkoringService transformation --}}
                <div style="margin-top: 5px; font-weight:bold; text-decoration:underline;">{{ $atasan->name }}</div>
                <div style="font-size:11px; color:#64748b;">NIP. {{ $atasan->nip ?? '-' }}</div>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
</body>
</html>