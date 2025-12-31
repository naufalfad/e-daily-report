<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SkoringService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SkoringController extends Controller
{
    protected $skoringService;

    /**
     * Inject SkoringService agar controller tetap 'Slim'.
     * Logika bisnis didelegasikan ke Service.
     */
    public function __construct(SkoringService $skoringService)
    {
        $this->skoringService = $skoringService;
    }

    /**
     * API Endpoint untuk mengambil data tabel & statistik di halaman web.
     * Route: GET /api/skoring-kinerja
     * Menerima parameter: month, year, search
     */
    public function index(Request $request)
    {
        $atasanId = auth()->id();
        
        // 1. Penangkapan Parameter Filter dari Request Frontend
        $month  = $request->input('month');
        $year   = $request->input('year');
        $search = $request->input('search');

        // 2. Distribusi Parameter ke Service
        // Service akan mengembalikan Collection data yang sudah dihitung & difilter
        $data = $this->skoringService->getBawahanReports($atasanId, $month, $year, $search);

        return response()->json([
            'message' => 'Data skoring berhasil dimuat',
            'data'    => $data
        ]);
    }

    /**
     * Generate PDF Laporan Kinerja.
     * Route: GET /penilai/skoring/export-pdf
     * Menerima parameter: month, year (via Query String)
     */
    public function exportPdf(Request $request)
    {
        // 1. Ambil User Atasan (Untuk Header Laporan)
        $atasan = User::with(['unitKerja', 'bidang', 'jabatan'])
                    ->find(auth()->id());

        // 2. Penangkapan Parameter Filter dari URL
        // Contoh: /export-pdf?month=5&year=2024
        $month = $request->input('month');
        $year  = $request->input('year');

        // 3. Distribusi Parameter ke Service
        // Kita tidak mengirim 'search' karena laporan PDF biasanya mencetak semua bawahan
        $bawahan = $this->skoringService->getBawahanReports($atasan->id, $month, $year);

        // 4. Hitung Statistik Agregat (Header Laporan PDF)
        $avgScore  = $bawahan->avg('capaian') ?? 0;
        $pembinaan = $bawahan->where('capaian', '<', 60)->count();

        // 5. Render PDF dengan Data yang Terfilter
        $pdf = Pdf::loadView('pdf.skoring-kinerja', [
            'atasan'    => $atasan,
            'bawahan'   => $bawahan,
            'avgScore'  => round($avgScore, 1),
            'pembinaan' => $pembinaan,
            'periode'   => [
                // Jika null, gunakan bulan/tahun saat ini untuk label
                'bulan' => $month ?? now()->month,
                'tahun' => $year ?? now()->year
            ]
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_Skoring_Kinerja.pdf');
    }
}