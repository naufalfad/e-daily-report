<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Harian Pegawai</title>

    <style>
        /* ====== CORE RESET & GENERAL STYLING ====== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b; /* Slate 800 */
            line-height: 1.5;
            background-color: #ffffff;
            padding: 40px 50px;
        }

        /* ====== HEADER (KOP SURAT) ====== */
        .kop-surat {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
            min-height: 60px;
        }

        .kop-logo {
            position: absolute;
            left: 5px;
            top: -5px;
            width: 50px;
            height: auto;
        }

        .kop-instansi {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a; /* Slate 900 */
        }

        .kop-dinas {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1c7c54; /* Main Theme Accent */
            margin-top: 2px;
        }

        .kop-alamat {
            font-size: 9px;
            color: #64748b; /* Slate 500 */
            margin-top: 3px;
            font-style: italic;
        }

        .kop-line {
            height: 3px;
            background-color: #1c7c54;
            margin-top: 8px;
            margin-bottom: 1px;
        }

        .kop-line-sub {
            height: 1px;
            background-color: #1c7c54;
            margin-bottom: 20px;
        }

        /* ====== DOCUMENT TITLE ====== */
        .doc-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
            text-decoration: underline;
        }

        /* ====== METADATA LAYOUT (TWO COLUMNS TABLE) ====== */
        .meta-container {
            width: 100%;
            margin-bottom: 20px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            vertical-align: top;
            padding: 3px 0;
        }

        .meta-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1c7c54;
            margin-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }

        .label {
            width: 120px;
            color: #64748b;
            font-size: 10px;
            font-weight: 500;
        }

        .colon {
            width: 15px;
            text-align: center;
            color: #64748b;
        }

        .value {
            color: #1e293b;
            font-weight: 500;
        }

        /* ====== SECTIONS & BOXES ====== */
        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-top: 15px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .description-box {
            background-color: #f8fafc; /* Slate 50 */
            border-left: 4px solid #1c7c54;
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 12px 15px;
            font-size: 11px;
            color: #334155;
            text-align: justify;
            margin-bottom: 15px;
        }

        /* ====== TABLE FOR METRICS & OUTPUTS ====== */
        .metrics-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .metrics-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
        }

        .metrics-table td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            color: #1e293b;
        }

        /* ====== VALIDATION BADGES ====== */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #ffffff;
        }

        .badge-approved {
            background-color: #10b981; /* Emerald 500 */
        }

        .badge-rejected {
            background-color: #ef4444; /* Red 500 */
        }

        .badge-pending {
            background-color: #f59e0b; /* Amber 500 */
        }

        .badge-draft {
            background-color: #64748b; /* Slate 500 */
        }

        .validation-card {
            background-color: #fffbeb; /* Amber 50 */
            border: 1px dashed #fcd34d; /* Amber 300 */
            border-radius: 4px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .validation-comment {
            font-style: italic;
            color: #b45309; /* Amber 700 */
            margin-top: 5px;
        }

        /* ====== WATERMARK / DRAFT INDICATOR ====== */
        .draft-banner {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #991b1b;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        /* ====== SIGNATURE BLOCK ====== */
        .signature-section {
            width: 100%;
            margin-top: 35px;
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
            font-size: 11px;
            font-weight: 500;
            color: #475569;
            margin-bottom: 60px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }

        .signature-nip {
            font-size: 10px;
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

    {{-- DRAFT BANNER --}}
    @if(isset($bukti_status) && $bukti_status !== null)
    <div class="draft-banner">
        Dokumen ini merupakan draf preview sementara dan belum divalidasi oleh atasan.
    </div>
    @endif

    {{-- TITLE --}}
    <div class="doc-title">
        Laporan Kinerja Harian (LKH) Pegawai
    </div>

    {{-- METADATA GRIDS --}}
    <table class="meta-container">
        <tr>
            <td style="width: 48%; vertical-align: top;">
                <div class="meta-title">Identitas Pegawai</div>
                <table class="meta-table">
                    <tr>
                        <td class="label">Nama Lengkap</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $pegawai_nama }}</td>
                    </tr>
                    <tr>
                        <td class="label">NIP</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $pegawai_nip }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jabatan</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $pegawai_jabatan }}</td>
                    </tr>
                    <tr>
                        <td class="label">Unit Kerja</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $pegawai_unit }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top;">
                <div class="meta-title">Detail Laporan</div>
                <table class="meta-table">
                    <tr>
                        <td class="label">Tanggal Kegiatan</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $tanggal }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kategori Lokasi</td>
                        <td class="colon">:</td>
                        <td class="value">
                            @if($kategori_lokasi === 'DL')
                                Dinas Luar (DL)
                            @else
                                {{ $kategori_lokasi }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Target Kinerja</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $kategori }}</td>
                    </tr>
                    @if($kategori === 'SKP' && !empty($target_skp))
                    <tr>
                        <td class="label">Sasaran Kerja</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $target_skp }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">Waktu Pengerjaan</td>
                        <td class="colon">:</td>
                        <td class="value">{{ substr($jam_mulai, 0, 5) }} s/d {{ substr($jam_selesai, 0, 5) }} WIT</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- URAIAN KEGIATAN --}}
    <div class="section-title">Uraian / Deskripsi Aktivitas</div>
    <div class="description-box">
        {!! nl2br(e($uraian_kegiatan)) !!}
    </div>

    {{-- METRICS TABLE --}}
    <div class="section-title">Output dan Volume Hasil Kerja</div>
    <table class="metrics-table">
        <thead>
            <tr>
                <th style="width: 50%;">Nama Output Kerja</th>
                <th style="width: 25%;">Volume Realisasi</th>
                <th style="width: 25%;">Satuan Output</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $output }}</td>
                <td>{{ $volume }}</td>
                <td>{{ $satuan }}</td>
            </tr>
        </tbody>
    </table>

    {{-- LOKASI --}}
    <div class="section-title">Lokasi Koordinat Presisi</div>
    <div style="font-size: 10px; color: #475569; margin-bottom: 25px;">
        📍 {{ $lokasi }}
    </div>

    {{-- VALIDATOR FEEDBACK IF EXISTS --}}
    @if($status === 'approved' || $status === 'rejected')
    <div class="validation-card">
        <div style="font-weight: bold; font-size: 10px; color: #78350f; text-transform: uppercase;">
            Status Verifikasi: 
            @if($status === 'approved')
                <span class="badge badge-approved" style="margin-left: 5px;">Disetujui</span>
            @else
                <span class="badge badge-rejected" style="margin-left: 5px;">Ditolak / Revisi</span>
            @endif
        </div>
        <div style="font-size: 9px; color: #92400e; margin-top: 3px;">
            Waktu Validasi: {{ $waktu_validasi }}
        </div>
        @if(!empty($komentar_validasi))
        <div class="validation-comment">
            Catatan Penilai: "{{ $komentar_validasi }}"
        </div>
        @endif
    </div>
    @endif

    {{-- SIGNATURE SECTION --}}
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <p class="signature-title">
                        Timika, {{ $waktu_validasi ? explode(' ', $waktu_validasi)[0] . ' ' . explode(' ', $waktu_validasi)[1] . ' ' . explode(' ', $waktu_validasi)[2] : now()->translatedFormat('d F Y') }}<br>
                        Pegawai Yang Melaporkan,
                    </p>
                    <br><br><br>
                    <p class="signature-name">{{ $pegawai_nama }}</p>
                    <p class="signature-nip">NIP. {{ $pegawai_nip }}</p>
                </td>
                <td>
                    <p class="signature-title">
                        <br>
                        Pejabat Penilai / Atasan,
                    </p>
                    @if($status === 'approved')
                        <br><br><br>
                        <p class="signature-name">{{ $atasan_nama }}</p>
                        <p class="signature-nip">NIP. {{ $atasan_nip }}</p>
                    @else
                        <br><br>
                        <div style="font-size: 10px; color: #94a3b8; font-style: italic; border: 1px dashed #e2e8f0; display: inline-block; padding: 10px 15px; border-radius: 4px; margin-top: 5px;">
                            (Belum Divalidasi /<br>Draft Preview)
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

</body>

</html>