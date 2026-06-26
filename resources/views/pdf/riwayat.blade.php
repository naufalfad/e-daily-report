<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Laporan Kerja Harian</title>

    <style>
        /* ====== CORE RESET & GENERAL STYLING ====== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b; /* Slate 800 */
            line-height: 1.4;
            background-color: #ffffff;
            padding: 30px 40px;
        }

        /* ====== HEADER (KOP SURAT) ====== */
        .kop-surat {
            text-align: center;
            margin-bottom: 15px;
            position: relative;
            min-height: 60px;
        }

        .kop-logo {
            position: absolute;
            left: 10px;
            top: -5px;
            width: 50px;
            height: auto;
        }

        .kop-instansi {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a; /* Slate 900 */
        }

        .kop-dinas {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1c7c54; /* Main Theme Accent */
            margin-top: 2px;
        }

        .kop-alamat {
            font-size: 8px;
            color: #64748b; /* Slate 500 */
            margin-top: 3px;
            font-style: italic;
        }

        .kop-line {
            height: 2.5px;
            background-color: #1c7c54;
            margin-top: 8px;
            margin-bottom: 1px;
        }

        .kop-line-sub {
            height: 0.8px;
            background-color: #1c7c54;
            margin-bottom: 15px;
        }

        /* ====== DOCUMENT TITLE ====== */
        .doc-title {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
            color: #0f172a;
        }

        /* ====== METADATA GRIDS ====== */
        .meta-container {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .label {
            width: 120px;
            color: #64748b;
            font-size: 9px;
            font-weight: 500;
        }

        .colon {
            width: 15px;
            text-align: center;
            color: #64748b;
        }

        .value {
            color: #1e293b;
            font-weight: bold;
        }

        /* ====== TABLE FOR LKH LIST ====== */
        .riwayat-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .riwayat-table th {
            background-color: #1c7c54;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 6px;
            border: 1px solid #166443;
            text-align: center;
        }

        .riwayat-table td {
            padding: 6px;
            border: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: top;
            font-size: 9px;
        }

        .riwayat-table tr:nth-child(even) {
            background-color: #f8fafc; /* Slate 50 Zebra Striping */
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        /* ====== BADGES ====== */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            border-width: 1px;
            text-align: center;
        }

        .badge-green {
            background-color: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }

        .badge-red {
            background-color: #fef2f2;
            color: #991b1b;
            border-color: #fca5a5;
        }

        .badge-yellow {
            background-color: #fffbeb;
            color: #92400e;
            border-color: #fde68a;
        }

        .badge-kategori {
            background-color: #f1f5f9;
            color: #334155;
            border-color: #cbd5e1;
            font-size: 8px;
            font-weight: 600;
        }

        /* ====== SIGNATURE SECTION ====== */
        .signature-section {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature-title {
            font-size: 10px;
            font-weight: 500;
            color: #475569;
            margin-bottom: 50px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }

        .signature-nip {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <div class="kop-surat">
        <img src="{{ public_path('img/logo-kab-mimika.png') }}" class="kop-logo" alt="Logo Kab Mimika">
        <p class="kop-instansi">Pemerintah Kabupaten Mimika</p>
        <p class="kop-dinas">Badan Pendapatan Daerah</p>
        <p class="kop-alamat">Jl. C. Cenderawasih Km. 5, Timika - Papua Tengah</p>
        <div class="kop-line"></div>
        <div class="kop-line-sub"></div>
    </div>

    {{-- TITLE --}}
    <div class="doc-title">
        Rekapitulasi Riwayat Laporan Kerja Harian (LKH)
    </div>

    {{-- METADATA GRIDS --}}
    <table class="meta-container">
        <tr>
            <td style="width: 48%; vertical-align: top;">
                <table class="meta-table">
                    <tr>
                        <td class="label">Nama Pengguna</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">NIP</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $user->nip }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jabatan / Role</td>
                        <td class="colon">:</td>
                        <td class="value">
                            @if($user->jabatan)
                                {{ $user->jabatan->nama_jabatan }}
                            @else
                                {{ ucfirst($role) }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top;">
                <table class="meta-table">
                    <tr>
                        <td class="label">Periode Rekap</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $periode }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Laporan</td>
                        <td class="colon">:</td>
                        <td class="value">{{ count($items) }} Kegiatan</td>
                    </tr>
                    <tr>
                        <td class="label">Unit Kerja</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $user->unitKerja->nama_unit ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- DATA TABLE --}}
    <table class="riwayat-table">
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">No</th>
                <th style="width: 9%;" class="text-center">Tanggal</th>
                @if($role === 'penilai')
                <th style="width: 14%;" class="text-center">Pegawai</th>
                @endif
                <th style="width: 32%;">Kegiatan & Rincian Uraian</th>
                <th style="width: 7%;" class="text-center">Kategori</th>
                <th style="width: 9%;" class="text-center">Waktu</th>
                <th style="width: 10%;" class="text-center">Volume / Output</th>
                <th style="width: 11%;" class="text-center">Pejabat Verifikator</th>
                <th style="width: 5%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $i)
            @php
            $statusBadgeClass = match ($i->status) {
                'approved' => 'badge-green',
                'rejected' => 'badge-red',
                default => 'badge-yellow',
            };
            $statusText = match ($i->status) {
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                default => 'Menunggu',
            };
            $katColor = match($i->kategori_lokasi) {
                'WFO' => 'background-color: #ecfdf5; color: #065f46; border-color: #a7f3d0;',
                'WFH' => 'background-color: #eff6ff; color: #1e40af; border-color: #bfdbfe;',
                'WFA' => 'background-color: #e0e7ff; color: #3730a3; border-color: #c7d2fe;',
                'DL' => 'background-color: #faf5ff; color: #6b21a8; border-color: #e9d5ff;',
                default => 'background-color: #f1f5f9; color: #334155; border-color: #cbd5e1;'
            };
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($i->tanggal_laporan)->translatedFormat('d M Y') }}</td>
                
                @if($role === 'penilai')
                <td class="value">{{ $i->user->name ?? '-' }}</td>
                @endif

                <td>
                    <div style="font-weight: bold; color: #0f172a; margin-bottom: 2px;">{{ $i->jenis_kegiatan }}</div>
                    <div style="color: #475569; font-size: 8.5px; line-height: 1.3;">{{ $i->deskripsi_aktivitas }}</div>
                </td>

                <td class="text-center">
                    <span class="badge" style="{{ $katColor }}">{{ $i->kategori_lokasi ?? 'WFO' }}</span>
                </td>

                <td class="text-center">
                    {{ substr($i->waktu_mulai, 0, 5) }} - {{ substr($i->waktu_selesai, 0, 5) }}
                </td>

                <td class="text-center">
                    <div style="font-weight: bold;">{{ $i->volume }}</div>
                    <div style="color: #64748b; font-size: 8px;">{{ $i->satuan }}</div>
                    <div style="color: #475569; font-size: 8px; font-style: italic; margin-top: 1px;">"{{ $i->output_hasil_kerja ?? '-' }}"</div>
                </td>

                <td class="text-center">
                    <div style="font-weight: 500;">{{ $i->validator->name ?? $i->atasan->name ?? '-' }}</div>
                    @if($i->waktu_validasi)
                    <div style="color: #94a3b8; font-size: 7.5px; margin-top: 1px;">
                        {{ \Carbon\Carbon::parse($i->waktu_validasi)->translatedFormat('d/m/y H:i') }}
                    </div>
                    @endif
                </td>

                <td class="text-center">
                    <span class="badge {{ $statusBadgeClass }}">{{ $statusText }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $role === 'penilai' ? 9 : 8 }}" class="text-center" style="padding: 20px; color: #94a3b8; font-style: italic;">
                    Tidak ada riwayat laporan harian ditemukan pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SIGNATURE SECTION --}}
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <p class="signature-title">
                        <br>
                        Pejabat Penilai / Atasan,
                    </p>
                    @if($role === 'staf' && count($items) > 0 && isset($items[0]->atasan))
                        <br><br><br>
                        <p class="signature-name">{{ $items[0]->atasan->name }}</p>
                        <p class="signature-nip">NIP. {{ $items[0]->atasan->nip ?? '-' }}</p>
                    @elseif($role === 'penilai' && count($items) > 0 && isset($items[0]->atasan))
                        <br><br><br>
                        <p class="signature-name">{{ $items[0]->atasan->name }}</p>
                        <p class="signature-nip">NIP. {{ $items[0]->atasan->nip ?? '-' }}</p>
                    @else
                        <br><br><br>
                        <p style="color: #cbd5e1; font-style: italic;">(Atasan Penilai)</p>
                    @endif
                </td>
                <td>
                    <p class="signature-title">
                        Timika, {{ now()->translatedFormat('d F Y') }}<br>
                        @if($role === 'staf')
                            Pegawai Yang Melaporkan,
                        @else
                            Pejabat Penilai,
                        @endif
                    </p>
                    <br><br><br>
                    <p class="signature-name">{{ $user->name }}</p>
                    <p class="signature-nip">NIP. {{ $user->nip ?? '-' }}</p>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>