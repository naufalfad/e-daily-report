<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\SkpRencana; // [PERBAIKAN] Ganti Skp jadi SkpRencana
use App\Models\User;
use App\Models\Bidang;
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

        // [LOGIKA BARU] Hitung total target dari tabel child (skp_target) via parent (skp_rencana)
        // Kita asumsikan yang dihitung adalah target Kuantitas agar angkanya relevan
        $totalTargetTahunan = SkpRencana::where('user_id', $userId)
            ->whereYear('periode_awal', $year) // [PERBAIKAN] periode_mulai -> periode_awal
            ->withSum(['targets' => function($q) {
                $q->where('jenis_aspek', 'Kuantitas');
            }], 'target')
            ->get()
            ->sum('targets_sum_target');

        // [LOGIKA BARU] Cek realisasi berdasarkan skp_rencana_id
        $realisasiSkp = LaporanHarian::where('user_id', $userId)
            ->whereNotNull('skp_rencana_id') // [PERBAIKAN] skp_id -> skp_rencana_id
            ->where('status', 'approved')
            ->whereYear('tanggal_laporan', $year)
            ->sum('volume'); // Asumsi: 1 LKH = 1 Poin Realisasi (atau bisa sum('volume'))

        $persenCapaian = $totalTargetTahunan > 0
            ? round(($realisasiSkp / $totalTargetTahunan) * 100, 1)
            : 0;

        // ==========================================
        // 2. STATISTIK KUALITAS LKH SKP
        // ==========================================

        $queryLkhSkp = LaporanHarian::where('user_id', $userId)
            ->whereNotNull('skp_rencana_id') // [PERBAIKAN] skp_id -> skp_rencana_id
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
            ->whereNull('skp_rencana_id') // [PERBAIKAN] skp_id -> skp_rencana_id
            ->whereYear('tanggal_laporan', $year);

        if ($request->has('month')) {
            $queryNonSkp->whereMonth('tanggal_laporan', $month);
        }

        $totalNonSkp = $queryNonSkp->whereNot('status', 'draft')->count();
        $nonSkpApproved = (clone $queryNonSkp)->where('status', 'approved')->count();
        $nonSkpRejected = (clone $queryNonSkp)->where('status', 'rejected')->count();

        $persenNonSkpDiterima = $totalNonSkp > 0 ? round(($nonSkpApproved / $totalNonSkp) * 100, 1) : 0;

        // ==========================================
        // 4. GRAFIK AKTIVITAS & DRAFT
        // ==========================================

        // [PERBAIKAN] Relasi 'skp' diganti 'rencana'
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
                'total_diterima' => $lkhSkpApproved,
                'total_ditolak' => $lkhSkpRejected,
                'persen_diterima' => $persenSkpDiterima,
                'persen_ditolak' => $persenSkpDitolak,
            ],
            'statistik_non_skp' => [
                'total_non_skp' => $totalNonSkp,
                'persen_diterima' => $persenNonSkpDiterima,
            ],
            'grafik_aktivitas' => $graphActivities,
            'aktivitas_terbaru' => $recentActivities,
            'draft_terbaru' => $recentDrafts,
            'draft_limit' => $draftsLimit,
        ]);
    }

   public function getStatsKadis(Request $request)
    {
        $kadis = Auth::user();
        
        // Filter Tahun (Default: Tahun Ini)
        $year = $request->input('year', date('Y'));

        // Validasi: Pastikan Kadis punya Unit Kerja
        if (!$kadis->unit_kerja_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda belum terhubung dengan Unit Kerja manapun.'
            ], 400);
        }

        // =====================================================================
        // QUERY UTAMA (EAGER LOADING)
        // Mengambil Bidang -> User -> LaporanHarian (Approved & Tahun Ini)
        // =====================================================================
        $dataBidang = \App\Models\Bidang::where('unit_kerja_id', $kadis->unit_kerja_id)
            ->with(['users.laporanHarian' => function($query) use ($year) {
                // Filter di level database untuk optimasi memori
                $query->where('status', 'approved')
                      ->whereYear('tanggal_laporan', $year)
                      ->select('id', 'user_id', 'tanggal_laporan'); // Ambil kolom perlu saja
            }])
            ->get();

        // =====================================================================
        // DATA PROCESSING (MAPPING KE FORMAT GRAFIK)
        // Output: Array of Objects per Bidang dengan data bulanan [Jan-Des]
        // =====================================================================
        $grafikKinerja = $dataBidang->map(function($bidang) {
            // Inisialisasi array 12 bulan dengan nilai 0
            // Index 0 = Januari, 11 = Desember
            $monthlyStats = array_fill(0, 12, 0);

            // Loop Pegawai di Bidang tersebut
            foreach ($bidang->users as $pegawai) {
                // Loop LKH Pegawai yang sudah di-filter (Approved & Tahun ini)
                foreach ($pegawai->laporanHarian as $lkh) {
                    // Ambil bulan (1-12) dari tanggal_laporan
                    // Karena array mulai dari 0, maka dikurang 1
                    $bulanIndex = (int) $lkh->tanggal_laporan->format('n') - 1;
                    
                    if (isset($monthlyStats[$bulanIndex])) {
                        $monthlyStats[$bulanIndex]++;
                    }
                }
            }

            return [
                'id_bidang' => $bidang->id,
                'nama_bidang' => $bidang->nama_bidang,
                // Kirim array angka saja [10, 20, 5, ...]
                'data_bulanan' => array_values($monthlyStats) 
            ];
        });

        // =====================================================================
        // RESPONSE JSON
        // Bersih, Ringan, dan Siap Konsumsi Frontend
        // =====================================================================
        return response()->json([
            'user_info' => [
                'name' => $kadis->name,
                'nip' => $kadis->nip,
                'jabatan' => $kadis->jabatan->nama_jabatan ?? 'Kepala Dinas',
                'unit_kerja' => $kadis->unitKerja->nama_unit ?? '-',
                'foto' => $kadis->foto_profil_url, // Menggunakan Accessor di Model User
                'alamat' => $kadis->alamat ?? '-',
            ],
            'periode_tahun' => (int) $year,
            'grafik_data' => $grafikKinerja,
        ]);
   }
}