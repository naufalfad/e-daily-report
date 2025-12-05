<!DOCTYPE html>
<html>

<head>
    <style>
    body {
        font-family: sans-serif;
        font-size: 11px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 5px;
        vertical-align: top;
    }

    .header {
        font-weight: bold;
        text-align: center;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .subheader {
        font-weight: bold;
        text-align: center;
        margin-bottom: 20px;
    }
    </style>
</head>

<body>

    @php $first = $skp->first(); @endphp

    <div class="header">RENCANA KINERJA PEGAWAI (SKP)</div>

    @if($first)
    <div class="subheader">
        Periode:
        {{ \Carbon\Carbon::parse($first->periode_awal)->translatedFormat('d M Y') }}
        s.d.
        {{ \Carbon\Carbon::parse($first->periode_akhir)->translatedFormat('d M Y') }}
    </div>
    @endif

    {{-- ============================================= --}}
    {{-- HEADER PEGAWAI & PENILAI --}}
    {{-- ============================================= --}}
    <table>
        <tr>
            <th colspan="2">PEGAWAI YANG DINILAI</th>
            <th colspan="2">PEJABAT PENILAI KINERJA</th>
        </tr>
        <tr>
            <td>Nama</td>
            <td>{{ $user->name }}</td>
            <td>Nama</td>
            <td>{{ optional($penilai)->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>{{ $user->nip }}</td>
            <td>NIP</td>
            <td>{{ optional($penilai)->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>{{ optional($user->jabatan)->nama_jabatan ?? '-' }}</td>
            <td>Jabatan</td>
            <td>{{ optional(optional($penilai)->jabatan)->nama_jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td>{{ optional($user->unitKerja)->nama_unit ?? '-' }}</td>
            <td>Unit Kerja</td>
            <td>{{ optional(optional($penilai)->unitKerja)->nama_unit ?? '-' }}</td>
        </tr>
    </table>

    <br>

    {{-- ============================================= --}}
    {{-- TABEL SKP --}}
    {{-- ============================================= --}}
    <table>
        <tr>
            <th style="width: 30px;">No</th>
            <th style="width: 180px;">RHK Pimpinan</th>
            <th style="width: 180px;">Rencana Hasil Kerja</th>
            <th style="width: 80px;">Aspek</th>
            <th style="width: 180px;">Indikator Kinerja Individu</th>
            <th style="width: 120px;">Target</th>
        </tr>

        @foreach ($skp as $i => $item)

        @php
        $targets = $item->targets;
        $rowCount = max(1, $targets->count());
        @endphp

        {{-- ================================ --}}
        {{-- BARIS PERTAMA --}}
        {{-- ================================ --}}
        <tr>
            <td rowspan="{{ $rowCount }}">{{ $i + 1 }}</td>
            <td rowspan="{{ $rowCount }}">{{ $item->rhk_intervensi }}</td>
            <td rowspan="{{ $rowCount }}">{{ $item->rencana_hasil_kerja }}</td>

            @if($targets->count() > 0)
            <td>{{ $targets[0]->jenis_aspek }}</td>
            <td>{{ $targets[0]->indikator }}</td>
            <td>{{ $targets[0]->target }} {{ $targets[0]->satuan }}</td>
            @else
            <td>-</td>
            <td>-</td>
            <td>-</td>
            @endif
        </tr>

        {{-- ================================ --}}
        {{-- BARIS SISANYA --}}
        {{-- ================================ --}}
        @if($targets->count() > 1)
        @foreach ($targets->slice(1) as $t)
        <tr>
            <td>{{ $t->jenis_aspek }}</td>
            <td>{{ $t->indikator }}</td>
            <td>{{ $t->target }} {{ $t->satuan }}</td>
        </tr>
        @endforeach
        @endif

        @endforeach
    </table>

</body>

</html>