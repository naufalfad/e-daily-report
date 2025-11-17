@php
$title = 'Skoring Kinerja Pegawai';

// Data dummy skoring – bisa kamu ganti dengan data dari DB nanti
$rows = [
[
'nama' => 'Fahrizal Mudzaqi Maulana',
'unit' => 'Unit Pajak',
'total' => 45,
'accepted' => '95%',
'avg_hours' => '7,5 jam/hari',
],
[
'nama' => 'Muhammad Naufal',
'unit' => 'Unit Pajak',
'total' => 48,
'accepted' => '94%',
'avg_hours' => '7,8 jam/hari',
],
[
'nama' => 'Reno Sebastian',
'unit' => 'Unit Pajak',
'total' => 44,
'accepted' => '93%',
'avg_hours' => '7,4 jam/hari',
],
[
'nama' => 'Silvia Lestari',
'unit' => 'Unit Pendataan',
'total' => 42,
'accepted' => '92%',
'avg_hours' => '7,2 jam/hari',
],
[
'nama' => 'Agus Prasetyo',
'unit' => 'Unit Penagihan',
'total' => 40,
'accepted' => '90%',
'avg_hours' => '7,0 jam/hari',
],
[
'nama' => 'Intan Permata',
'unit' => 'Unit Pengawasan',
'total' => 39,
'accepted' => '89%',
'avg_hours' => '6,9 jam/hari',
],
[
'nama' => 'Rai Fazri',
'unit' => 'Unit Pajak',
'total' => 38,
'accepted' => '88%',
'avg_hours' => '6,8 jam/hari',
],
[
'nama' => 'Anna Septiani',
'unit' => 'Unit Pendataan',
'total' => 37,
'accepted' => '87%',
'avg_hours' => '6,7 jam/hari',
],
[
'nama' => 'Bima Pratama',
'unit' => 'Unit Penagihan',
'total' => 35,
'accepted' => '85%',
'avg_hours' => '6,5 jam/hari',
],
[
'nama' => 'Dewi Rahma',
'unit' => 'Unit Pengawasan',
'total' => 34,
'accepted' => '84%',
'avg_hours' => '6,4 jam/hari',
],
];
@endphp

@extends('layouts.app', ['title' => $title, 'role' => 'penilai', 'active' => 'skoring'])

@section('content')
<section class="rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5 flex flex-col h-full">
    <h2 class="text-[18px] font-normal mb-4">Skoring Kinerja Pegawai</h2>

    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-100 text-[13px] text-slate-600">
                <tr>
                    <th class="px-4 py-2 text-left font-medium w-[120px]">Peringkat</th>
                    <th class="px-4 py-2 text-left font-medium">Nama Pegawai</th>
                    <th class="px-4 py-2 text-left font-medium">Unit</th>
                    <th class="px-4 py-2 text-left font-medium">Total Laporan</th>
                    <th class="px-4 py-2 text-left font-medium">Persentase Diterima</th>
                    <th class="px-4 py-2 text-left font-medium">Rata-rata Waktu Kerja</th>
                </tr>
            </thead>
            <tbody class="text-[13px] text-slate-700">
                @foreach ($rows as $row)
                @php
                $rank = $loop->iteration;
                @endphp
                <tr class="border-t border-slate-200">
                    <td class="px-4 py-2 whitespace-nowrap">
                        @if ($rank <= 3) {{-- Icon medal untuk peringkat 1, 2, 3 --}} <div
                            class="flex items-center gap-2">
                            <img src="{{ asset('assets/icon/rank-' . $rank . '.svg') }}" alt="Peringkat {{ $rank }}"
                                class="h-5 w-5">
                            <span>{{ $rank }}</span>
    </div>
    @else
    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-[12px]">
        {{ $rank }}
    </span>
    @endif
    </td>
    <td class="px-4 py-2">{{ $row['nama'] }}</td>
    <td class="px-4 py-2 whitespace-nowrap">{{ $row['unit'] }}</td>
    <td class="px-4 py-2 whitespace-nowrap">{{ $row['total'] }}</td>
    <td class="px-4 py-2 whitespace-nowrap">{{ $row['accepted'] }}</td>
    <td class="px-4 py-2 whitespace-nowrap">{{ $row['avg_hours'] }}</td>
    </tr>
    @endforeach
    </tbody>
    </table>
    </div>
</section>
@endsection