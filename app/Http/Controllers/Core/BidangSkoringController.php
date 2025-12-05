<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\KabanSkoringService; // Import Service Layer
use Illuminate\Support\Facades\Validator;

/**
 * TAHAP 3.1: Controller untuk menangani permintaan API Skoring Kinerja Per Bidang.
 * Bertindak sebagai Controller/Front Controller yang mendelegasikan tugas ke Service Layer.
 */
class BidangSkoringController extends Controller
{
    protected $kabanSkoringService;

    /**
     * Dependency Injection (DI) Service Layer.
     */
    public function __construct(KabanSkoringService $kabanSkoringService)
    {
        $this->kabanSkoringService = $kabanSkoringService;
    }

    /**
     * Menampilkan hasil skoring kinerja per Bidang.
     * Endpoint: GET /api/kadis/skoring-bidang
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // 1. Validasi Input (Month dan Year, sesuai filter bulanan yang disepakati)
        $validator = Validator::make($request->all(), [
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $month = $request->input('month');
            $year = $request->input('year');

            // 2. Delegasi Tugas ke Service Layer (Logika perhitungan ada di Service)
            $skoringData = $this->kabanSkoringService->getSkoringPerBidang($month, $year);

            // 3. Kembalikan Respon Sukses
            return response()->json([
                'status' => 'success',
                'message' => 'Data skoring per bidang berhasil diambil.',
                'data' => $skoringData,
            ]);

        } catch (\Exception $e) {
            // Menangani error umum di lapisan Controller
            \Log::error("Error fetching Bidang Skoring data: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            // 4. Kembalikan Respon Error
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data skoring. Periksa log sistem.',
            ], 500);
        }
    }
}