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
     * * [UPDATE] Mengembalikan 2 payload:
     * 1. table_data: Data list pegawai dengan pagination
     * 2. global_stats: Statistik agregat (Total, Rata-rata, dll) dari seluruh data
     */
    public function index(Request $request)
    {
        $atasanId = auth()->id();
        
        // 1. Penangkapan Parameter Filter
        $month  = $request->input('month');
        $year   = $request->input('year');
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10); // Default 10 baris per halaman

        // 2. Ambil Data Tabel (Paginated)
        // Menggunakan method getBawahanReports yang sudah diupdate menjadi paginate()
        $tableData = $this->skoringService->getBawahanReports($atasanId, $month, $year, $search, $perPage);

        // 3. Ambil Data Statistik Global (Non-Paginated)
        // Method baru ini melakukan query agregat database yang efisien
        $globalStats = $this->skoringService->getBawahanStats($atasanId, $month, $year);

        // 4. Return JSON Terstruktur
        return response()->json([
            'message'      => 'Data skoring berhasil dimuat',
            'table_data'   => $tableData,   // Object Pagination (data, links, meta)
            'global_stats' => $globalStats  // Object Statistik (avg, total, dll)
        ]);
    }

    /**
     * Generate PDF Laporan Kinerja.
     * Route: GET /penilai/skoring/export-pdf
     */
    public function exportPdf(Request $request)
    {
        // 1. Ambil User Atasan (Header Laporan)
        $atasan = User::with(['unitKerja', 'bidang', 'jabatan'])
                    ->find(auth()->id());

        // 2. Filter
        $month = $request->input('month');
        $year  = $request->input('year');

        // 3. Ambil Data Bawahan (Untuk PDF kita butuh SEMUA data, bukan per halaman)
        // Trik: Kita minta limit yang sangat besar (misal 1000) untuk "mematikan" efek paginasi di PDF
        $paginator = $this->skoringService->getBawahanReports($atasan->id, $month, $year, null, 1000);
        $bawahanCollection = $paginator->getCollection(); // Mengambil Collection murni dari Paginator

        // 4. Ambil Statistik Agregat (Menggunakan Service yang sama dengan Dashboard)
        // Ini menjamin angka di PDF sama persis dengan angka di Dashboard Web
        $stats = $this->skoringService->getBawahanStats($atasan->id, $month, $year);

        // 5. Render PDF
        $pdf = Pdf::loadView('pdf.skoring-kinerja', [
            'atasan'    => $atasan,
            'bawahan'   => $bawahanCollection,
            'avgScore'  => $stats['avg_skor'],   // Ambil dari hasil Service
            'pembinaan' => $stats['pembinaan'],  // Ambil dari hasil Service
            'periode'   => [
                'bulan' => $month ?? now()->month,
                'tahun' => $year ?? now()->year
            ]
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan_Skoring_Kinerja.pdf');
    }
}