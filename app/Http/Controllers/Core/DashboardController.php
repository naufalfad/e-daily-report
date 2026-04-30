<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\SkpRencana;
use App\Models\User;
use App\Models\Bidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard Staf (Personal Statistik)
     */
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
        $totalTargetTahunan = SkpRencana::where('user_id', $userId)
            ->whereYear('periode_awal', $year) 
            ->withSum(['targets' => function($q) {
                $q->where('jenis_aspek', 'Kuantitas');
            }], 'target')
            ->get()
            ->sum('targets_sum_target');

        $realisasiSkp = LaporanHarian::where('user_id', $userId)
            ->whereNotNull('skp_rencana_id') 
            ->where('status', 'approved')
            ->whereYear('tanggal_laporan', $year)
            ->sum('volume');

        $persenCapaian = $totalTargetTahunan > 0
            ? round(($realisasiSkp / $totalTargetTahunan) * 100, 1)
            : 0;

        // ==========================================
        // 2. STATISTIK KUALITAS LKH SKP & NON-SKP
        // ==========================================
        $queryLkhSkp = LaporanHarian::where('user_id', $userId)
            ->whereNotNull('skp_rencana_id')
            ->whereYear('tanggal_laporan', $year);

        $queryNonSkp = LaporanHarian::where('user_id', $userId)
            ->whereNull('skp_rencana_id')
            ->whereYear('tanggal_laporan', $year);

        if ($request->has('month')) {
            $queryLkhSkp->whereMonth('tanggal_laporan', $month);
            $queryNonSkp->whereMonth('tanggal_laporan', $month);
        }

        $totalLkhSkp = $queryLkhSkp->whereNot('status', 'draft')->count();
        $lkhSkpApproved = (clone $queryLkhSkp)->where('status', 'approved')->count();
        $lkhSkpRejected = (clone $queryLkhSkp)->where('status', 'rejected')->count();

        $totalNonSkp = $queryNonSkp->whereNot('status', 'draft')->count();
        $nonSkpApproved = (clone $queryNonSkp)->where('status', 'approved')->count();
        $nonSkpRejected = (clone $queryNonSkp)->where('status', 'rejected')->count();

        $totalLaporan = $totalLkhSkp + $totalNonSkp;
        $totalDiterima = $lkhSkpApproved + $nonSkpApproved;
        $totalDitolak = $lkhSkpRejected + $nonSkpRejected;
        $persenDiterima = $totalLaporan > 0 ? round(($totalDiterima / $totalLaporan) * 100, 1) : 0;
        $persenDitolak = $totalLaporan > 0 ? round(($totalDitolak / $totalLaporan) * 100, 1) : 0;

        // ==========================================
        // [BARU] 3. ANALITIK KATEGORI LOKASI (Pie/Donut Chart)
        // Agregasi di level Database berdasarkan Kategori Lokasi
        // ==========================================
        $distribusiLokasiQuery = LaporanHarian::select('kategori_lokasi', DB::raw('count(*) as total'))
            ->where('user_id', $userId)
            ->whereNot('status', 'draft')
            ->whereYear('tanggal_laporan', $year);

        if ($request->has('month')) {
            $distribusiLokasiQuery->whereMonth('tanggal_laporan', $month);
        }

        $rawDistribusi = $distribusiLokasiQuery->groupBy('kategori_lokasi')->pluck('total', 'kategori_lokasi')->toArray();
        
        // Memastikan format konsisten (WFO, WFH, WFA, DL) selalu ada meski nilainya 0
        $distribusiLokasi = [
            'WFO' => $rawDistribusi[LaporanHarian::KAT_WFO] ?? 0,
            'WFH' => $rawDistribusi[LaporanHarian::KAT_WFH] ?? 0,
            'WFA' => $rawDistribusi[LaporanHarian::KAT_WFA] ?? 0,
            'DL'  => $rawDistribusi[LaporanHarian::KAT_DL] ?? 0,
        ];

        // ==========================================
        // 4. GRAFIK AKTIVITAS & DRAFT
        // ==========================================
        $recentActivities = LaporanHarian::with('rencana')
            ->where('user_id', $userId)
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $graphActivities = LaporanHarian::with('rencana')
            ->where('user_id', $userId)
            ->latest('created_at')
            ->get();

        $recentDrafts = LaporanHarian::with('rencana')
            ->where('user_id', $userId)
            ->where('status', 'draft')
            ->latest('created_at')
            ->get();

        $draftsLimit = LaporanHarian::with('rencana')
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
                'total_skp' => $totalLkhSkp,
                'total_diterima' => $totalDiterima,
                'total_ditolak' => $totalDitolak,
                'persen_diterima' => $persenDiterima,
                'persen_ditolak' => $persenDitolak,
                'total_non_skp' => $totalNonSkp,
            ],
            // [BARU] Injeksi Data ke Response
            'distribusi_lokasi' => $distribusiLokasi, 
            
            'grafik_aktivitas' => $graphActivities,
            'aktivitas_terbaru' => $recentActivities,
            'draft_terbaru' => $recentDrafts,
            'draft_limit' => $draftsLimit,
        ]);
    }

    /**
     * Dashboard Pemimpin (Statistik Global Unit Kerja)
     */
   public function getStatsKadis(Request $request)
    {
        $kadis = Auth::user();
        
        $year = $request->input('year', date('Y'));

        if (!$kadis->unit_kerja_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda belum terhubung dengan Unit Kerja manapun.'
            ], 400);
        }

        // =====================================================================
        // 1. QUERY GRAFIK KINERJA BULANAN PER BIDANG
        // =====================================================================
        $dataBidang = Bidang::where('unit_kerja_id', $kadis->unit_kerja_id)
            ->with(['users.laporanHarian' => function($query) use ($year) {
                $query->where('status', 'approved')
                      ->whereYear('tanggal_laporan', $year)
                      ->select('id', 'user_id', 'tanggal_laporan'); 
            }])
            ->get();

        $grafikKinerja = $dataBidang->map(function($bidang) {
            $monthlyStats = array_fill(0, 12, 0);

            foreach ($bidang->users as $pegawai) {
                foreach ($pegawai->laporanHarian as $lkh) {
                    $bulanIndex = (int) $lkh->tanggal_laporan->format('n') - 1;
                    if (isset($monthlyStats[$bulanIndex])) {
                        $monthlyStats[$bulanIndex]++;
                    }
                }
            }

            return [
                'id_bidang' => $bidang->id,
                'nama_bidang' => $bidang->nama_bidang,
                'data_bulanan' => array_values($monthlyStats) 
            ];
        });

        // =====================================================================
        // [BARU] 2. QUERY DISTRIBUSI LOKASI GLOBAL (Level Unit Kerja)
        // Analitik untuk melihat perilaku kerja satu dinas penuh
        // =====================================================================
        $distribusiLokasiQuery = DB::table('laporan_harian')
            ->join('users', 'laporan_harian.user_id', '=', 'users.id')
            ->select('laporan_harian.kategori_lokasi', DB::raw('count(laporan_harian.id) as total'))
            ->where('users.unit_kerja_id', $kadis->unit_kerja_id)
            ->where('laporan_harian.status', '!=', 'draft')
            ->whereYear('laporan_harian.tanggal_laporan', $year)
            ->groupBy('laporan_harian.kategori_lokasi')
            ->pluck('total', 'kategori_lokasi')
            ->toArray();

        $distribusiLokasiGlobal = [
            'WFO' => $distribusiLokasiQuery[LaporanHarian::KAT_WFO] ?? 0,
            'WFH' => $distribusiLokasiQuery[LaporanHarian::KAT_WFH] ?? 0,
            'WFA' => $distribusiLokasiQuery[LaporanHarian::KAT_WFA] ?? 0,
            'DL'  => $distribusiLokasiQuery[LaporanHarian::KAT_DL] ?? 0,
        ];

        // =====================================================================
        // RESPONSE JSON
        // =====================================================================
        return response()->json([
            'user_info' => [
                'name' => $kadis->name,
                'nip' => $kadis->nip,
                'jabatan' => $kadis->jabatan->nama_jabatan ?? 'Kepala Dinas',
                'unit_kerja' => $kadis->unitKerja->nama_unit ?? '-',
                'foto' => $kadis->foto_profil_url, 
                'alamat' => $kadis->alamat ?? '-',
            ],
            'periode_tahun' => (int) $year,
            'grafik_data' => $grafikKinerja,
            'distribusi_lokasi_global' => $distribusiLokasiGlobal, // [BARU] Disuntikkan ke response Kadis
        ]);
   }
}