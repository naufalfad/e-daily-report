<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\Skp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        // Filter Tahun & Bulan (Default: Bulan ini)
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        // ==========================================
        // 1. SKORING CAPAIAN SKP (Target vs Realisasi)
        // ==========================================

        $totalTargetTahunan = Skp::where('user_id', $userId)
            ->whereYear('periode_mulai', $year)
            ->sum('target');

        $realisasiSkp = LaporanHarian::where('user_id', $userId)
            ->whereNotNull('skp_id')
            ->where('status', 'approved')
            ->whereYear('tanggal_laporan', $year)
            ->count();

        $persenCapaian = $totalTargetTahunan > 0
            ? round(($realisasiSkp / $totalTargetTahunan) * 100, 1)
            : 0;

        // ==========================================
        // 2. STATISTIK KUALITAS LKH SKP
        // ==========================================

        $queryLkhSkp = LaporanHarian::where('user_id', $userId)
            ->whereNotNull('skp_id')
            ->whereYear('tanggal_laporan', $year);

        if ($request->has('month')) {
            $queryLkhSkp->whereMonth('tanggal_laporan', $month);
        }

        $totalLkhSkp = $queryLkhSkp->whereNot('status', 'draft')->count();
        $lkhSkpApproved = (clone $queryLkhSkp)->where('status', 'approved')->count();
        $lkhSkpRejected = (clone $queryLkhSkp)->where('status', 'rejected')->count();

        $persenSkpDiterima = $totalLkhSkp > 0 ? round(($lkhSkpApproved / $totalLkhSkp) * 100, 1) : 0;
        $persenSkpDitolak = $totalLkhSkp > 0 ? round(($lkhSkpRejected / $totalLkhSkp) * 100, 1) : 0;

        // ==========================================
        // 3. STATISTIK LKH NON-SKP
        // ==========================================

        $queryNonSkp = LaporanHarian::where('user_id', $userId)
            ->whereNull('skp_id')
            ->whereYear('tanggal_laporan', $year);

        if ($request->has('month')) {
            $queryNonSkp->whereMonth('tanggal_laporan', $month);
        }

        $totalNonSkp = $queryNonSkp->whereNot('status', 'draft')->count();
        $nonSkpApproved = (clone $queryNonSkp)->where('status', 'approved')->count();
        $nonSkpRejected = (clone $queryNonSkp)->where('status', 'rejected')->count();

        $persenNonSkpDiterima = $totalNonSkp > 0 ? round(($nonSkpApproved / $totalNonSkp) * 100, 1) : 0;

        // ==========================================
        // 4. GRAFIK KINERJA BULANAN
        // ==========================================

        // $chartData = LaporanHarian::select(
        //         DB::raw('COUNT(id) as count'), 
        //         DB::raw('EXTRACT(MONTH FROM tanggal_laporan) AS month')
        //     )
        //     ->where('user_id', $userId)
        //     ->where('status', 'approved')
        //     ->whereYear('tanggal_laporan', $year)
        //     ->groupBy('month')
        //     ->orderBy('month')
        //     ->pluck('count', 'month')
        //     ->toArray();

        // // Mapping ke array 1-12
        // $monthlyChart = [];
        // for ($i = 1; $i <= 12; $i++) {
        //     $monthlyChart[] = isset($chartData[$i]) ? (int) $chartData[$i] : 0;
        // }

        // ==========================================
        // 5. AKTIVITAS TERBARU
        // ==========================================

        $recentActivities = LaporanHarian::with('skp')
            ->where('user_id', $userId)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $graphActivities = LaporanHarian::with('skp')
            ->where('user_id', $userId)
            ->whereNotNull('skp_id')
            ->latest('created_at')
            ->get();

        $recentDrafts = LaporanHarian::with('skp')
            ->where('user_id', $userId)
            ->where('status', 'draft')
            ->latest('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'user_info' => [
                'name' => $user->name,
                'nip' => $user->nip,
                'email' => $user->email,
                'no_telp' => $user->no_telp,
                'alamat' => $user->alamat,
                'jabatan' => $user->jabatan->nama_jabatan ?? '-',
                'unit' => $user->unitKerja->nama_unit ?? '-'
            ],
            'skoring_utama' => [
                'target_tahunan' => (int) $totalTargetTahunan,
                'realisasi_tahunan' => $realisasiSkp,
                'persen_capaian' => $persenCapaian,
            ],
            'statistik_skp' => [
                'total_diajukan' => $totalLkhSkp,
                'total_diterima' => $lkhSkpApproved,
                'total_ditolak' => $lkhSkpRejected,
                'persen_diterima' => $persenSkpDiterima,
                'persen_ditolak' => $persenSkpDitolak,
            ],
            'statistik_non_skp' => [
                'total_diajukan' => $totalNonSkp,
                'persen_diterima' => $persenNonSkpDiterima,
            ],
            //'grafik_kinerja' => $monthlyChart,
            'grafik_aktivitas' => $graphActivities,
            'aktivitas_terbaru' => $recentActivities,
            'draft_terbaru' => $recentDrafts,
        ]);
    }

    public function getStatsKadis()
    {
        $user = Auth::user();
        $user->load(['jabatan', 'unitKerja']);

        // Ambil laporan NON-DRAFT saja (validasi selesai / sedang berjalan)
        $all = LaporanHarian::select('status', 'tanggal_laporan', 'deskripsi_aktivitas')
            ->where('status', '!=', 'draft')
            ->orderBy('tanggal_laporan', 'desc')
            ->get();

        // Hitung statistik
        $totalHariIni = LaporanHarian::whereDate('tanggal_laporan', today())
            ->where('status', '!=', 'draft')
            ->count();

        $totalMenunggu = $all->where('status', 'waiting_review')->count();
        $totalDisetujui = $all->where('status', 'approved')->count();
        $totalDitolak = $all->where('status', 'rejected')->count();
        $totalSemua = $all->count();

        // Persentase
        $rateDisetujui = $totalSemua > 0 ? round(($totalDisetujui / $totalSemua) * 100) : 0;
        $rateDitolak = $totalSemua > 0 ? round(($totalDitolak / $totalSemua) * 100) : 0;

        // Perubahan dari kemarin
        $kemarin = LaporanHarian::whereDate('tanggal_laporan', today()->subDay())
            ->where('status', '!=', 'draft')
            ->count();

        $rateTotal = $kemarin > 0
            ? round((($totalHariIni - $kemarin) / $kemarin) * 100)
            : 0;

        // Aktivitas terbaru (5 terakhir)
        $aktivitas = $all->take(5);

        return response()->json([
            "user_info" => [
                "name" => $user->name,
                "nip" => $user->nip,
                "alamat" => $user->alamat,
                "jabatan" => $user->jabatan->nama_jabatan ?? "-",
                "unit" => $user->unitKerja->nama_unit ?? "-",
                "daerah" => "Mimika, Papua Tengah",
            ],

            "statistik" => [
                "total_hari_ini" => $totalHariIni,
                "total_menunggu" => $totalMenunggu,
                "total_disetujui" => $totalDisetujui,
                "total_ditolak" => $totalDitolak,
                "rate_total" => $rateTotal,
                "rate_disetujui" => $rateDisetujui,
                "rate_ditolak" => $rateDitolak,
            ],

            "aktivitas_terbaru" => $aktivitas,

            "grafik" => $all
        ]);
    }

}