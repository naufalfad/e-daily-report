<?php

namespace App\Services;

use App\Models\LaporanHarian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service Layer untuk sentralisasi logika perhitungan Skoring Kinerja.
 * Bertanggung jawab menghitung skor, predikat, dan statistik LKH.
 */
class SkoringService
{
    /**
     * Hitung Skor Kinerja untuk satu pegawai dalam periode tertentu.
     * * @param int $userId ID Pegawai yang akan dinilai
     * @param int|null $month Bulan (1-12), default: bulan ini
     * @param int|null $year Tahun, default: tahun ini
     * @return array Data lengkap (skor, predikat, statistik)
     */
    public function calculatePegawaiScore(int $userId, ?int $month = null, ?int $year = null): array
    {
        // 1. Tentukan Periode Waktu
        $month = $month ?? now()->month;
        $year  = $year ?? now()->year;

        // 2. Query Agregasi LKH (Hanya Approved & Rejected)
        // Kita menggunakan query builder untuk performa, daripada meload semua model
        $stats = LaporanHarian::where('user_id', $userId)
            ->whereMonth('tanggal_laporan', $month)
            ->whereYear('tanggal_laporan', $year)
            ->selectRaw("
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as total_approved,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as total_rejected,
                COUNT(CASE WHEN status = 'waiting_review' THEN 1 END) as total_waiting
            ")
            ->first();

        // 3. Ekstrak Data
        $approved = $stats->total_approved ?? 0;
        $rejected = $stats->total_rejected ?? 0;
        $waiting  = $stats->total_waiting ?? 0;

        // 4. Hitung Pembagi (Denominator)
        // Aturan Bisnis: Hanya Approved + Rejected. Waiting TIDAK dihitung.
        $totalValid = $approved + $rejected;

        // 5. Hitung Skor (Rumus: Approved / TotalValid * 100)
        $skor = 0;
        if ($totalValid > 0) {
            $skor = round(($approved / $totalValid) * 100, 2); // 2 desimal untuk presisi
        }

        // 6. Tentukan Predikat
        $predikat = $this->determinePredicate($skor);

        // 7. Return Data Transfer Object (DTO) dalam bentuk Array
        return [
            'user_id'        => $userId,
            'periode'        => [
                'bulan' => $month,
                'tahun' => $year,
                'label' => Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y')
            ],
            'statistik'      => [
                'approved' => $approved,
                'rejected' => $rejected,
                'waiting'  => $waiting, // Info tambahan, tidak masuk rumus
                'total_valid' => $totalValid // Pembagi
            ],
            'skor_akhir'     => $skor,
            'predikat'       => $predikat,
            'color_class'    => $this->getPredicateColorClass($predikat) // Helper untuk UI (Badge Color)
        ];
    }

    /**
     * Mengambil Data Skoring untuk Daftar Bawahan (Bulk).
     * Digunakan oleh Penilai/Atasan Langsung.
     * * @param int $atasanId
     * @param int|null $month
     * @param int|null $year
     * @param string|null $search Keyword pencarian nama/NIP
     * @return Collection Collection of calculated stats
     */
    public function getBawahanReports(int $atasanId, ?int $month = null, ?int $year = null, ?string $search = null): Collection
    {
        // Ambil bawahan
        $query = User::where('atasan_id', $atasanId)
            ->with(['jabatan', 'unitKerja']) // Eager load relasi penting
            ->where('is_active', true); // Hanya user aktif

        // Filter Pencarian (Opsional, jika ingin filter di level DB user)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $bawahans = $query->get();

        // Loop dan hitung skor masing-masing menggunakan logic sentral
        return $bawahans->map(function ($user) use ($month, $year) {
            $scoreData = $this->calculatePegawaiScore($user->id, $month, $year);

            // Merge data user dengan hasil perhitungan
            return [
                'id'        => $user->id,
                'nama'      => $user->name,
                'nip'       => $user->nip,
                'foto'      => $user->avatar_url, // Pastikan accessor ini ada di Model User
                'jabatan'   => $user->jabatan->nama_jabatan ?? '-',
                'unit_kerja'=> $user->unitKerja->nama_unit ?? '-',
                
                // Data Hasil Perhitungan
                'realisasi' => $scoreData['statistik']['approved'],
                'target'    => $scoreData['statistik']['total_valid'], // Target dinamis berdasarkan total LKH yg dinilai
                'satuan'    => 'Laporan',
                'capaian'   => $scoreData['skor_akhir'],
                'predikat'  => $scoreData['predikat'],
                'badge_color' => $scoreData['color_class']
            ];
        });
    }

    /**
     * Logika Penentuan Predikat (Business Rule).
     */
    public function determinePredicate(float $score): string
    {
        if ($score >= 90) return 'Sangat Baik';
        if ($score >= 75) return 'Baik';
        if ($score >= 60) return 'Cukup';
        return 'Kurang';
    }

    /**
     * Helper untuk konsistensi warna UI (Tailwind CSS).
     */
    private function getPredicateColorClass(string $predikat): string
    {
        return match ($predikat) {
            'Sangat Baik' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Baik'        => 'bg-blue-50 text-blue-700 border-blue-200',
            'Cukup'       => 'bg-yellow-50 text-yellow-700 border-yellow-200',
            default       => 'bg-red-50 text-red-700 border-red-200',
        };
    }
}