<?php

namespace App\Services;

use App\Models\Bidang;
use App\Models\LaporanHarian;
use App\Models\Jabatan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Service Layer untuk mengelola logika Skoring Kinerja Per Bidang.
 * Kelas ini bertindak sebagai Information Expert.
 */
class KabanSkoringService
{
    protected $bidang;
    protected $lkh;
    protected $jabatan;

    /**
     * Dependency Injection Model (TAHAP 2 Awal)
     * Model yang sudah di-enhance di TAHAP 1 di-inject di sini.
     */
    public function __construct(Bidang $bidang, LaporanHarian $lkh, Jabatan $jabatan)
    {
        $this->bidang = $bidang;
        $this->lkh = $lkh;
        $this->jabatan = $jabatan;
    }

    // =========================================================
    // TAHAP 2.2: Implementasi Logic Utama
    // =========================================================

    /**
     * Method Utama: Menghitung Skoring Kinerja untuk semua Bidang.
     * @param int|null $month
     * @param int|null $year
     * @return \Illuminate\Support\Collection
     */
    public function getSkoringPerBidang(?int $month = null, ?int $year = null)
    {
        // 1. Tentukan rentang waktu (default: bulan berjalan)
        $date = Carbon::createSafe($year ?? now()->year, $month ?? now()->month, 1);
        $startOfMonth = $date->startOfMonth()->toDateString();
        $endOfMonth = $date->endOfMonth()->toDateString();
        
        // 2. Ambil semua Bidang yang akan dinilai
        $bidangs = $this->bidang->with([
            // FIX: Mengubah 'nama' menjadi 'name' untuk kolom user
            'kepalaBidang:id,name,jabatan_id,bidang_id', 
            'users' 
        ])->get();
        
        $results = collect();

        // 3. Loop melalui setiap Bidang dan hitung statistik LKH
        foreach ($bidangs as $bidang) {
            
            // Dapatkan ID user staf dan Kabid di bidang ini 
            $userIds = $bidang->users->pluck('id');
            
            // Skip Bidang yang tidak memiliki pegawai
            if ($userIds->isEmpty()) {
                continue; 
            }
            
            // Panggil method private untuk menghitung statistik LKH
            $stats = $this->calculateLkhStats($userIds, $startOfMonth, $endOfMonth);
            
            $persentase = $stats['total_submitted'] > 0 
                ? round(($stats['total_approved'] / $stats['total_submitted']) * 100, 2)
                : 0;

            $results->push([
                'id' => $bidang->id,
                // FIX LOGIKA: Mengganti $bidang->nama menjadi $bidang->nama_bidang
                'nama_bidang' => $bidang->nama_bidang, 
                // Mengakses kolom 'name' dari model User untuk Kepala Bidang
                'nama_kabid' => $bidang->kepalaBidang->name ?? 'N/A', 
                'total_submitted' => $stats['total_submitted'],
                'total_approved' => $stats['total_approved'],
                'persentase' => $persentase,
                'predikat' => $this->determinePredicate($persentase),
            ]);
        }
        
        return $results;
    }
    
    // =========================================================
    // TAHAP 2.3: Implementasi Logic Perhitungan dan Predikat
    // =========================================================

    /**
     * Method private: Melakukan Aggregation Query ke tabel LaporanHarian.
     * @param \Illuminate\Support\Collection $userIds
     * @param string $start
     * @param string $end
     * @return array
     */
    private function calculateLkhStats($userIds, $start, $end): array
    {
        // STATUS VALID SESUAI DATABASE
        $submittedStatuses = ['approved', 'waiting_review', 'rejected'];

        // Query PostgreSQL yang valid
        $result = DB::table('laporan_harian')
            ->selectRaw("
                COUNT(CASE WHEN status IN ('approved', 'waiting_review', 'rejected') THEN 1 END) AS total_submitted,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) AS total_approved
            ")
            ->whereIn('user_id', $userIds)
            ->whereBetween('tanggal_laporan', [$start, $end]) // FIX KOLOM
            ->first();

        return [
            'total_submitted' => $result->total_submitted ?? 0,
            'total_approved'  => $result->total_approved ?? 0,
        ];
    }


    /**
     * Method private: Menentukan Predikat berdasarkan Persentase Kinerja.
     * @param float $percentage
     * @return string
     */
    private function determinePredicate(float $percentage): string
    {
        // Logika Predikat yang disepakati oleh Yang Mulia Raja
        if ($percentage >= 95.0) {
            return 'Sangat Baik';
        } elseif ($percentage >= 85.0) {
            return 'Baik';
        } elseif ($percentage >= 75.0) {
            return 'Cukup';
        } else {
            return 'Kurang';
        }
    }
}