<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\SkpRencana; // [PERBAIKAN] Ganti Skp jadi SkpRencana
use App\Models\User;
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
            ->whereNotNull('skp_rencana_id')
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
            'grafik_aktivitas' => $graphActivities,
            'aktivitas_terbaru' => $recentActivities,
            'draft_terbaru' => $recentDrafts,
            'draft_limit' => $draftsLimit,
        ]);
    }

    public function getStatsKadis()
    {
        $kadis = Auth::user();

        // ======== PROFIL KADIS ========
        $kadis->load(['jabatan', 'unitKerja']);
        $dataKadis = [
            'name' => $kadis->name,
            'nip' => $kadis->nip,
            'jabatan' => $kadis->jabatan->nama_jabatan ?? '-',
            'unit' => $kadis->unitKerja->nama_unit ?? '-',
            'alamat' => $kadis->alamat ?? '-',
            'foto' => $kadis->foto_profil ? asset('storage/' . $kadis->foto_profil) : null
        ];

        // ======== AMBIL KABID (BAWAHAN LANGSUNG KADIS) ========
        $kabidIds = User::where('atasan_id', $kadis->id)->pluck('id');

        // ======== STATISTIK HARI INI ========
        $today = today();

        $totalHariIni = LaporanHarian::whereIn('user_id', $kabidIds)
            ->whereDate('tanggal_laporan', $today)
            ->count();

        $totalMenunggu = LaporanHarian::whereIn('user_id', $kabidIds)
            ->where('status', 'waiting_review')
            ->count();

        $totalDisetujui = LaporanHarian::whereIn('user_id', $kabidIds)
            ->where('status', 'approved')
            ->count();

        $totalDitolak = LaporanHarian::whereIn('user_id', $kabidIds)
            ->where('status', 'rejected')
            ->count();

        $totalSemua = $totalMenunggu + $totalDisetujui + $totalDitolak;

        // ======== RATE ========
        $rateDisetujui = $totalSemua > 0 ? round(($totalDisetujui / $totalSemua) * 100) : 0;
        $rateDitolak = $totalSemua > 0 ? round(($totalDitolak / $totalSemua) * 100) : 0;

        $kemarin = LaporanHarian::whereIn('user_id', $kabidIds)
            ->whereDate('tanggal_laporan', today()->subDay())
            ->count();

        $rateTotal = $kemarin > 0
            ? round((($totalHariIni - $kemarin) / $kemarin) * 100)
            : 0;

        // ======== AKTIVITAS TERBARU (HANYA Laporan Kabid) ========
        $aktivitas = LaporanHarian::with('user')
            ->whereIn('user_id', $kabidIds)
            ->orderBy('tanggal_laporan', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($x) {
                return [
                    'deskripsi_aktivitas' => $x->deskripsi_aktivitas ?? '-', // [PERBAIKAN] nama_kegiatan -> deskripsi_aktivitas
                    'tanggal_laporan' => $x->tanggal_laporan,
                    'status' => $x->status,
                    'user' => $x->user->name ?? '-',
                ];
            });

        // =============================
        // Grafik
        // =============================

        $grafik = LaporanHarian::whereIn('user_id', $pegawaiIds)
            ->whereYear('tanggal_laporan', now()->year)
            ->get(['tanggal_laporan', 'status']);

        // ======== RETURN JSON KE JS ========
        return response()->json([
            "user_info" => $dataKadis,

            "statistik" => [
                "total_hari_ini" => $totalHariIni,
                "total_menunggu" => $totalMenunggu,
                "total_disetujui" => $totalDisetujui,
                "total_ditolak" => $totalDitolak,
                "rate_total" => $rateTotal,
                "rate_disetujui" => $rateDisetujui,
                "rate_ditolak" => $rateDitolak,
            ],
            'statistik' => [
                'total_hari_ini' => $totalHariIni,
                'total_menunggu' => $menunggu,
                'total_disetujui' => $disetujui,
                'total_ditolak' => $ditolak,
                // Rate bisa dihitung di frontend atau backend jika perlu
                'rate_total' => 0,
                'rate_disetujui' => 0,
                'rate_ditolak' => 0,
            ],
            'aktivitas_terbaru' => $recentActivities,
            'grafik' => $grafik,
        ]);
    }
}