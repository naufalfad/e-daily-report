@extends('layouts.app',
[
    'role' => 'penilai'
    ])

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Skoring Kinerja Pegawai</h1>
            <p class="text-gray-500 mt-1">Monitor dan evaluasi performa pegawai serta unit kerja.</p>
        </div>
        <div class="flex gap-2">
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2">
                <i class="fas fa-download"></i> Export Laporan
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <p class="text-gray-500 text-sm font-medium">Total Pegawai Dinilai</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">{{ $totalPegawai ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <p class="text-gray-500 text-sm font-medium">Rata-rata Skor</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($avgScore ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
            <p class="text-gray-500 text-sm font-medium">Predikat Sangat Baik</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">{{ $countSangatBaik ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
            <p class="text-gray-500 text-sm font-medium">Perlu Pembinaan</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">{{ $countPerluPembinaan ?? 0 }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Sebaran Predikat Kinerja</h3>
            <div class="relative h-64 w-full flex justify-center">
                <canvas id="performancePieChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Rata-rata Kinerja Unit Kerja</h3>
            <div class="relative h-64 w-full">
                <canvas id="unitStatsChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Detail Kinerja Pegawai</h3>
            <input type="text" placeholder="Cari pegawai..." class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Nama Pegawai</th>
                        <th class="py-3 px-6 text-left">Unit Kerja</th>
                        <th class="py-3 px-6 text-center">Skor SKP</th>
                        <th class="py-3 px-6 text-center">Skor LKH</th>
                        <th class="py-3 px-6 text-center">Total Nilai</th>
                        <th class="py-3 px-6 text-center">Predikat</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    {{-- Loop data pegawai disini --}}
                    @forelse($dataPegawai ?? [] as $pegawai)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6 text-left whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="font-medium">{{ $pegawai->name }}</div>
                            </div>
                        </td>
                        <td class="py-3 px-6 text-left">
                            <span>{{ $pegawai->unit_kerja }}</span>
                        </td>
                        <td class="py-3 px-6 text-center">{{ $pegawai->skor_skp }}</td>
                        <td class="py-3 px-6 text-center">{{ $pegawai->skor_lkh }}</td>
                        <td class="py-3 px-6 text-center font-bold text-blue-600">{{ $pegawai->total_nilai }}</td>
                        <td class="py-3 px-6 text-center">
                            @php
                                $badgeColor = match($pegawai->predikat) {
                                    'Sangat Baik' => 'bg-green-100 text-green-700',
                                    'Baik' => 'bg-blue-100 text-blue-700',
                                    'Cukup' => 'bg-yellow-100 text-yellow-700',
                                    default => 'bg-red-100 text-red-700',
                                };
                            @endphp
                            <span class="{{ $badgeColor }} py-1 px-3 rounded-full text-xs font-semibold">
                                {{ $pegawai->predikat }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-4 text-center text-gray-500">Belum ada data skoring.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{-- Pagination Links --}}
            {{-- {{ $dataPegawai->links() }} --}}
        </div>
    </div>
</div>

{{-- Load Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data dari Controller (Dipassing sebagai JSON)
        const pieData = @json($chartData['predikat'] ?? ['labels' => [], 'data' => []]);
        const barData = @json($chartData['unit'] ?? ['labels' => [], 'data' => []]);

        // 1. Pie Chart Configuration
        const ctxPie = document.getElementById('performancePieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: pieData.labels.length ? pieData.labels : ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'],
                datasets: [{
                    data: pieData.data.length ? pieData.data : [10, 25, 5, 2], // Dummy data fallback
                    backgroundColor: [
                        '#10B981', // Emerald 500
                        '#3B82F6', // Blue 500
                        '#F59E0B', // Amber 500
                        '#EF4444'  // Red 500
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // 2. Bar Chart Configuration
        const ctxBar = document.getElementById('unitStatsChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: barData.labels.length ? barData.labels : ['Sekretariat', 'Bidang A', 'Bidang B'],
                datasets: [{
                    label: 'Rata-rata Skor',
                    data: barData.data.length ? barData.data : [85, 92, 78], // Dummy data fallback
                    backgroundColor: '#6366F1', // Indigo 500
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    });
</script>
@endsection