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
            ->latest('updated_at')
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
            ->get();

        $draftsLimit = LaporanHarian::with('skp')
            ->where('user_id', $userId)
            ->where('status', 'draft')
            ->latest('created_at')
            ->limit(3)
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
            'draft_limit' => $draftsLimit,
        ]);
    }

    public function getStatsKadis(Request $request)
    {
        $kadis = Auth::user();

        // Ambil seluruh pegawai di bawah Kadis
        $pegawaiIds = \App\Models\User::where('atasan_id', $kadis->id)->pluck('id');

        // =============================
        // Statistik Dasar Dashboard Kadis
        // =============================

        $today = now()->toDateString();

        $totalHariIni = LaporanHarian::whereIn('user_id', $pegawaiIds)
            ->whereDate('tanggal_laporan', $today)
            ->whereNot('status', 'draft')
            ->count();

        $menunggu = LaporanHarian::whereIn('user_id', $pegawaiIds)
            ->where('status', 'waiting_review')
            ->count();

        $disetujui = LaporanHarian::whereIn('user_id', $pegawaiIds)
            ->where('status', 'approved')
            ->count();

        $ditolak = LaporanHarian::whereIn('user_id', $pegawaiIds)
            ->where('status', 'rejected')
            ->count();

        // =============================
        // Aktivitas Terbaru
        // =============================

        $recentActivities = LaporanHarian::with('user')
            ->whereIn('user_id', $pegawaiIds)
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function ($x) {
                return [
                    'deskripsi_aktivitas' => $x->nama_kegiatan ?? '-',
                    'tanggal_laporan' => $x->tanggal_laporan,
                    'status' => $x->status,
                    'user' => $x->user->name ?? '-',
                ];
            });

        // =============================
        // Grafik (ambil seluruh laporan navigasi 1 tahun)
        // =============================

        $grafik = LaporanHarian::whereIn('user_id', $pegawaiIds)
            ->whereYear('tanggal_laporan', now()->year)
            ->get(['tanggal_laporan', 'status']);

        return response()->json([
            'user_info' => [
                'name' => $kadis->name,
                'nip' => $kadis->nip,
                'daerah' => $kadis->alamat ?? '-',
                'jabatan' => $kadis->jabatan->nama_jabatan ?? '-',
                'unit' => $kadis->unitKerja->nama_unit ?? '-',
                'alamat' => $kadis->alamat ?? '-',
            ],

            'statistik' => [
                'total_hari_ini' => $totalHariIni,
                'total_menunggu' => $menunggu,
                'total_disetujui' => $disetujui,
                'total_ditolak' => $ditolak,

                'rate_total' => 0,
                'rate_disetujui' => 0,
                'rate_ditolak' => 0,
            ],

            'aktivitas_terbaru' => $recentActivities,
            'grafik' => $grafik,
        ]);
    }

}