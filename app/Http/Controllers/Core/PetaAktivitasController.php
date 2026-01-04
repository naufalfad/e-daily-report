<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LaporanHarian;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Aktivitas;
use Illuminate\Support\Facades\Log;

class PetaAktivitasController extends Controller
{
    /**
     * Mengambil Peta Aktivitas User yang Sedang Login
     * [UPDATED] Dengan Server-Side Filtering
     */
    public function getPetaAktivitas(Request $request)
    {
        try {
            $userId = Auth::id();

            // 1. Build Query Dasar
            $query = LaporanHarian::with(['tupoksi', 'user'])
                ->where('user_id', $userId)
                ->whereNot('status', 'draft');

            // 2. Terapkan Filter Tanggal (Jika ada request)
            if ($request->has('from_date') && !empty($request->from_date)) {
                $query->whereDate('tanggal_laporan', '>=', $request->from_date);
            }

            if ($request->has('to_date') && !empty($request->to_date)) {
                $query->whereDate('tanggal_laporan', '<=', $request->to_date);
            }

            // 3. Eksekusi Query
            $lkh = $query->orderBy('tanggal_laporan', 'desc')->get();

            // 4. Mapping Data (Sama seperti sebelumnya)
            $result = $lkh->map(function ($item) {
                return $this->formatMapData($item);
            });

            return response()->json([
                "success" => true,
                "message" => "Data peta aktivitas berhasil diambil",
                "data" => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Gagal mengambil data peta aktivitas",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil Peta Aktivitas Bawahan (Untuk Atasan)
     * [UPDATED] Dengan Server-Side Filtering
     */
    public function getStafAktivitas(Request $request)
    {
        try {
            $userId = Auth::id();

            // 1. Build Query Dasar (Filter by Atasan ID)
            $query = LaporanHarian::with(['tupoksi', 'user'])
                ->where('atasan_id', $userId)
                ->whereNot('status', 'draft');

            // 2. Terapkan Filter Tanggal
            if ($request->has('from_date') && !empty($request->from_date)) {
                $query->whereDate('tanggal_laporan', '>=', $request->from_date);
            }

            if ($request->has('to_date') && !empty($request->to_date)) {
                $query->whereDate('tanggal_laporan', '<=', $request->to_date);
            }

            // 3. Eksekusi Query
            $lkh = $query->orderBy('tanggal_laporan', 'desc')->get();

            // 4. Mapping Data
            $result = $lkh->map(function ($item) {
                return $this->formatMapData($item);
            });

            return response()->json([
                "success" => true,
                "message" => "Data peta aktivitas berhasil diambil",
                "data" => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Gagal mengambil data peta aktivitas",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil Semua Aktivitas (Admin/Global)
     * [UPDATED] Dengan Server-Side Filtering
     */
    public function getAllAktivitas(Request $request)
    {
        try {
            // 1. Build Query Dasar
            $query = LaporanHarian::with(['tupoksi', 'user'])
                ->whereNot('status', 'draft');

            // 2. Terapkan Filter Tanggal
            if ($request->has('from_date') && !empty($request->from_date)) {
                $query->whereDate('tanggal_laporan', '>=', $request->from_date);
            }

            if ($request->has('to_date') && !empty($request->to_date)) {
                $query->whereDate('tanggal_laporan', '<=', $request->to_date);
            }

            // 3. Eksekusi Query
            $lkh = $query->orderBy('tanggal_laporan', 'desc')->get();

            // 4. Mapping Data
            $result = $lkh->map(function ($item) {
                return $this->formatMapData($item);
            });

            return response()->json([
                "success" => true,
                "message" => "Data peta aktivitas berhasil diambil",
                "data" => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Gagal mengambil data peta aktivitas",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview / Export PDF Peta Aktivitas
     * Menangani request AJAX untuk progress loading
     */
    public function previewMapPdf(Request $request)
    {
        // Bungkus keseluruhan logic dalam try-catch untuk handling response
        try {
            $user = Auth::user();
            $mapImageBase64 = null;

            // 1. Logika Pengambilan Data dan Filtering
            $query = LaporanHarian::with(['user', 'tupoksi']);
                
            // Filter berdasarkan unit kerja (untuk Kepala Dinas/Unit)
            if ($user->unit_kerja_id) {
                $unitKerjaId = $user->unit_kerja_id;
                $query->whereHas('user', function ($q) use ($unitKerjaId) {
                    $q->where('unit_kerja_id', $unitKerjaId);
                });
            }
            
            $query->whereNot('status', 'draft');
            
            // 2. Terapkan Filter Tanggal
            $fromDate = $request->query('from_date');
            $toDate = $request->query('to_date');
            
            if (!empty($fromDate)) {
                $query->whereDate('tanggal_laporan', '>=', $fromDate);
            }
            if (!empty($toDate)) {
                $query->whereDate('tanggal_laporan', '<=', $toDate);
            }

            $laporanHarian = $query->orderBy('tanggal_laporan', 'desc')->get();
            
            // 3. Mapping Data untuk Renderer (Hanya yang punya koordinat)
            $activities = $laporanHarian->map(function ($item) {
                return $this->formatMapData($item);
            })->filter(function($item) {
                return !empty($item['lat']) && !empty($item['lng']);
            });
            
            // 4. Panggil Headless Renderer Service (External Microservice)
            if ($activities->isNotEmpty()) {
                try {
                    $client = new Client();
                    $firstActivity = $activities->first();
                    
                    // URL Service Node.js (Default port 3000)
                    $rendererUrl = env('MAP_RENDER_URL', 'http://127.0.0.1:3000') . '/render-map'; 

                    $response = $client->post($rendererUrl, [
                        'timeout' => 20.0, // Timeout dinaikkan sedikit untuk safety
                        'json' => [
                            'activities' => $activities->values()->all(),
                            'center' => [
                                'lat' => $firstActivity['lat'],
                                'lng' => $firstActivity['lng']
                            ],
                            'zoom' => 13
                        ]
                    ]);

                    $result = json_decode($response->getBody(), true);
                    
                    if (isset($result['success']) && $result['success']) {
                        $mapImageBase64 = $result['image'];
                    }
                } catch (\Exception $e) {
                    // Log error renderer tapi JANGAN gagalkan proses PDF.
                    // PDF tetap tergenerate dengan fallback image.
                    Log::error('Map Renderer Service Failed: ' . $e->getMessage());
                }
            }
            
            // 5. Siapkan Metadata Laporan
            $meta = [
                'nama'          => $user->name,
                'role'          => $user->role,
                'tanggal_cetak' => now()->format('d M Y, H:i'),
                'periode'       => ($fromDate && $toDate) ? 
                                   Carbon::parse($fromDate)->format('d M Y') . ' s.d ' . Carbon::parse($toDate)->format('d M Y') : 
                                   'Semua Periode',
                'unit_kerja'    => $user->unitKerja->nama_unit_kerja ?? '-',
                'generated_by'  => $user->name . ' (' . ($user->nip ?? '-') . ')'
            ];

            // 6. Generate PDF
            $pdf = Pdf::loadView('pdf.peta-aktivitas', [
                'activities' => $activities,
                'meta'       => $meta,
                'image'      => $mapImageBase64, 
            ])->setPaper('a4', 'portrait');

            // 7. Return PDF Stream
            $filename = 'Peta_Aktivitas_' . preg_replace('/[^A-Za-z0-9]/', '_', $user->username) . '_' . time() . '.pdf';

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');

        } catch (\Throwable $e) {
            // [NEW] Adaptive Error Handling
            // Jika request via AJAX (JS Export), return JSON 500 agar bisa dibaca Frontend
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses dokumen: ' . $e->getMessage(),
                    'trace'   => config('app.debug') ? $e->getTraceAsString() : null
                ], 500);
            }

            // Jika akses langsung via browser, lempar error standar Laravel
            throw $e;
        }
    }

    private function formatMapData($item)
    {
        return [
            "id"                 => $item->id,
            "kegiatan"           => $item->deskripsi_aktivitas,
            "deskripsi"          => $item->output_hasil_kerja,
            "kategori_aktivitas" => $item->jenis_kegiatan,

            "tanggal"            => Carbon::parse($item->tanggal_laporan)->format('d M Y'),
            "tanggal_raw"        => $item->tanggal_laporan, 
            "waktu"              => substr($item->waktu_mulai, 0, 5) . " - " . substr($item->waktu_selesai, 0, 5),

            "status"             => $item->status,
            "user"               => $item->user->name ?? "User",

            // Koordinat
            "lat"                => $item->lat,
            "lng"                => $item->lng,

            // Lokasi Teks & Mode
            "lokasi_teks"        => $item->lokasi_teks,
            "is_luar_lokasi"     => $item->is_luar_lokasi,
        ];
    }
}