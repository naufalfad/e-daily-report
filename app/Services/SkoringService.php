<?php

namespace App\Services;

use App\Models\LaporanHarian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service Layer untuk sentralisasi logika perhitungan Skoring Kinerja.
 * Bertanggung jawab menghitung skor, predikat, dan statistik LKH.
 */
class SkoringService
{
    /**
     * Hitung Skor Kinerja untuk satu pegawai dalam periode tertentu.
     * @param int $userId ID Pegawai yang akan dinilai
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
            $skor = round(($approved / $totalValid) * 100, 2); 
        }

        // 6. Tentukan Predikat
        $predikat = $this->determinePredicate($skor);

        // 7. Return Data Transfer Object (DTO)
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
                'waiting'  => $waiting,
                'total_valid' => $totalValid
            ],
            'skor_akhir'     => $skor,
            'predikat'       => $predikat,
            'color_class'    => $this->getPredicateColorClass($predikat)
        ];
    }

    /**
     * [REFACTORED] Mengambil Data Tabel Bawahan dengan PAGINASI.
     * Menggunakan paginate() agar ringan, lalu diproses dengan through().
     * * @param int $atasanId
     * @param int|null $month
     * @param int|null $year
     * @param string|null $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getBawahanReports(int $atasanId, ?int $month = null, ?int $year = null, ?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        // 1. Query Dasar User
        $query = User::where('atasan_id', $atasanId)
            ->with(['jabatan', 'unitKerja'])
            ->where('is_active', true);

        // 2. Filter Pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // 3. Eksekusi Paginasi
        $paginator = $query->paginate($perPage);

        // 4. Transformasi Data (Hitung Skor per Item)
        // 'through' sangat efisien karena hanya memproses item yang ada di halaman saat ini
        $paginator->through(function ($user) use ($month, $year) {
            $scoreData = $this->calculatePegawaiScore($user->id, $month, $year);

            return [
                'id'        => $user->id,
                'nama'      => $user->name,
                'nip'       => $user->nip,
                'foto'      => $user->avatar_url,
                'jabatan'   => $user->jabatan->nama_jabatan ?? '-',
                'unit_kerja'=> $user->unitKerja->nama_unit ?? '-',
                'realisasi' => $scoreData['statistik']['approved'],
                'target'    => $scoreData['statistik']['total_valid'],
                'satuan'    => 'Laporan',
                'capaian'   => $scoreData['skor_akhir'],
                'predikat'  => $scoreData['predikat'],
                'badge_color' => $scoreData['color_class']
            ];
        });

        return $paginator;
    }

    /**
     * [NEW] Mengambil Statistik Global Seluruh Bawahan (Tanpa Paginasi).
     * Digunakan untuk Kartu Dashboard (Total, Rata-rata, dll).
     * * @return array
     */
    public function getBawahanStats(int $atasanId, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?? now()->month;
        $year  = $year ?? now()->year;

        // Kita gunakan Query Builder + Subquery untuk performa maksimal.
        // Subquery: Hitung approved & valid per user
        // Main Query: Average dari hasil subquery
        
        // 1. Ambil ID semua bawahan aktif
        $bawahanIds = User::where('atasan_id', $atasanId)
            ->where('is_active', true)
            ->pluck('id');

        if ($bawahanIds->isEmpty()) {
            return [
                'total_bawahan' => 0,
                'avg_skor' => 0,
                'sangat_baik' => 0,
                'pembinaan' => 0
            ];
        }

        // 2. Hitung Skor per User secara raw (Agregasi DB)
        // Rumus SQL: (COUNT(approved) / NULLIF(COUNT(valid), 0)) * 100
        $rawScores = DB::table('laporan_harian')
            ->select('user_id')
            ->selectRaw("
                COALESCE(
                    (COUNT(CASE WHEN status = 'approved' THEN 1 END)::float / 
                    NULLIF(COUNT(CASE WHEN status IN ('approved', 'rejected') THEN 1 END), 0)) * 100, 
                0) as skor_final
            ")
            ->whereIn('user_id', $bawahanIds)
            ->whereMonth('tanggal_laporan', $month)
            ->whereYear('tanggal_laporan', $year)
            ->groupBy('user_id')
            ->get();

        // Gabungkan dengan user yang belum punya LKH (skor 0)
        // Karena query 'laporan_harian' di atas hanya mengembalikan user yang punya LKH.
        // Kita butuh mapping manual agar akurat.
        
        $totalScores = [];
        foreach ($bawahanIds as $id) {
            $found = $rawScores->firstWhere('user_id', $id);
            $totalScores[] = $found ? $found->skor_final : 0;
        }

        $collection = collect($totalScores);

        return [
            'total_bawahan' => $collection->count(),
            'avg_skor'      => round($collection->avg(), 1),
            'sangat_baik'   => $collection->filter(fn($s) => $s >= 90)->count(),
            'pembinaan'     => $collection->filter(fn($s) => $s < 60)->count()
        ];
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