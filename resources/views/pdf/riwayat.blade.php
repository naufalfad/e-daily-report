<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat LKH</title>

    {{-- Tailwind Compiled (minified for PDF) --}}
    <style>
    /* ====== TAILWIND ESSENTIAL UTILITIES ====== */
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
    }

    .text-sm {
        font-size: 12px;
    }

    .text-xs {
        font-size: 11px;
    }

    .font-bold {
        font-weight: bold;
    }

    .mt-2 {
        margin-top: 8px;
    }

    .mb-2 {
        margin-bottom: 8px;
    }

    .mb-4 {
        margin-bottom: 16px;
    }

    .p-2 {
        padding: 8px;
    }

    .px-3 {
        padding-left: 12px;
        padding-right: 12px;
    }

    .py-2 {
        padding-top: 8px;
        padding-bottom: 8px;
    }

    .rounded {
        border-radius: 4px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th {
        background-color: #f1f5f9;
        font-weight: bold;
        padding: 8px;
        border: 1px solid #cbd5e1;
    }

    td {
        padding: 8px;
        border: 1px solid #e2e8f0;
    }

    .badge {
        color: white;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
    }

    .green {
        background-color: #059669;
    }

    .red {
        background-color: #dc2626;
    }

    .yellow {
        background-color: #d97706;
    }
    </style>
</head>

<body>

    <h2 class="text-lg font-bold mb-2">Riwayat Laporan Harian</h2>

    <p class="text-sm mb-4">
        {{ ucfirst($role) }} — {{ $user->name }} <br>
        <span class="text-xs text-gray-600">Periode: {{ $periode }}</span>
    </p>

    <table>
        <thead>
            <tr>
                <th class="text-xs">Tanggal</th>
                <th class="text-xs">Nama Kegiatan</th>
                @if($role === 'penilai')
                <th class="text-xs">Pegawai</th>
                @endif
                <th class="text-xs">Waktu</th>
                <th class="text-xs">Output</th>
                <th class="text-xs">Validator</th>
                <th class="text-xs">Status</th>
            </tr>
        </thead>

        <tbody>
            @foreach($items as $i)
            @php
            $statusColor = match ($i->status) {
            'approved' => 'green',
            'rejected' => 'red',
            default => 'yellow',
            };
            $statusText = match ($i->status) {
            'approved' => 'Diterima',
            'rejected' => 'Ditolak',
            default => 'Menunggu',
            };
            @endphp

            <tr>
                <td class="text-sm">{{ $i->tanggal_laporan }}</td>
                <td class="text-sm">{{ $i->deskripsi_aktivitas }}</td>

                @if($role === 'penilai')
                <td class="text-sm">{{ $i->user->name }}</td>
                @endif

                <td class="text-sm">
                    {{ \Carbon\Carbon::parse($i->waktu_mulai)->format('H:i') }}
                    -
                    {{ \Carbon\Carbon::parse($i->waktu_selesai)->format('H:i') }}
                </td>

                <td class="text-sm">{{ $i->output_hasil_kerja ?? '-' }}</td>

                <td class="text-sm">
                    {{ $i->validator->name ?? $i->atasan->name ?? '-' }}
                </td>

                <td>
                    <span class="badge {{ $statusColor }}">{{ $statusText }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>