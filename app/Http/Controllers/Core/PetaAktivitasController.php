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
use Illuminate\Support\Str;

class PetaAktivitasController extends Controller
{
    /**
     * Mengambil Peta Aktivitas User yang Sedang Login
     */
    public function getPetaAktivitas(Request $request)
    {
        try {
            $userId = Auth::id();

            $query = LaporanHarian::with(['tupoksi', 'user'])
                ->where('user_id', $userId)
                ->whereNot('status', 'draft');

            if ($request->has('from_date') && !empty($request->from_date)) {
                $query->whereDate('tanggal_laporan', '>=', $request->from_date);
            }

            if ($request->has('to_date') && !empty($request->to_date)) {
                $query->whereDate('tanggal_laporan', '<=', $request->to_date);
            }

            $lkh = $query->orderBy('tanggal_laporan', 'desc')->get();

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
     */
    public function getStafAktivitas(Request $request)
    {
        try {
            $userId = Auth::id();

            $query = LaporanHarian::with(['tupoksi', 'user'])
                ->where('atasan_id', $userId)
                ->whereNot('status', 'draft');

            if ($request->has('from_date') && !empty($request->from_date)) {
                $query->whereDate('tanggal_laporan', '>=', $request->from_date);
            }

            if ($request->has('to_date') && !empty($request->to_date)) {
                $query->whereDate('tanggal_laporan', '<=', $request->to_date);
            }

            $lkh = $query->orderBy('tanggal_laporan', 'desc')->get();

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
     */
    public function getAllAktivitas(Request $request)
    {
        try {
            $query = LaporanHarian::with(['tupoksi', 'user'])
                ->whereNot('status', 'draft');

            if ($request->has('from_date') && !empty($request->from_date)) {
                $query->whereDate('tanggal_laporan', '>=', $request->from_date);
            }

            if ($request->has('to_date') && !empty($request->to_date)) {
                $query->whereDate('tanggal_laporan', '<=', $request->to_date);
            }

            $lkh = $query->orderBy('tanggal_laporan', 'desc')->get();

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
     * Preview / Export PDF Peta Aktivitas (Dual Mode Support)
     */
    public function previewMapPdf(Request $request)
    {
        try {
            $user = Auth::user();
            $mapImageBase64 = null;

            // 1. TANGKAP INPUT MODE DARI USER
            // 'heatmap' = Peta Sebaran (Gradasi Warna)
            // 'cluster' = Peta Titik (Marker Grouping)
            $mode = $request->input('mode', 'heatmap');

            // 2. Logika Query Data & Filtering
            $query = LaporanHarian::with(['user.jabatan', 'user.unitKerja', 'tupoksi']);
                
            // Filter Unit Kerja (Security Scope)
            if ($user->unit_kerja_id) {
                $unitKerjaId = $user->unit_kerja_id;
                $query->whereHas('user', function ($q) use ($unitKerjaId) {
                    $q->where('unit_kerja_id', $unitKerjaId);
                });
            }
            
            $query->whereNot('status', 'draft');
            
            // Filter Tanggal
            $fromDate = $request->query('from_date');
            $toDate = $request->query('to_date');
            
            if (!empty($fromDate)) {
                $query->whereDate('tanggal_laporan', '>=', $fromDate);
            }
            if (!empty($toDate)) {
                $query->whereDate('tanggal_laporan', '<=', $toDate);
            }

            $laporanHarian = $query->orderBy('tanggal_laporan', 'desc')->get();
            
            // 3. Data Mapping (Hanya valid coordinates)
            $activities = $laporanHarian->map(function ($item) {
                return $this->formatMapData($item);
            })->filter(function($item) {
                return !empty($item['lat']) && !empty($item['lng']);
            });
            
            // 4. Headless Renderer (External Service)
            if ($activities->isNotEmpty()) {
                try {
                    $client = new Client();
                    // Ambil center dari titik pertama atau default center kabupaten
                    $firstActivity = $activities->first();
                    $centerLat = $firstActivity['lat'] ?? -4.5467;
                    $centerLng = $firstActivity['lng'] ?? 136.8833;

                    // URL Service Node.js
                    $rendererUrl = env('MAP_RENDER_URL', 'http://127.0.0.1:3000') . '/render-map'; 

                    $response = $client->post($rendererUrl, [
                        'timeout' => 20.0, 
                        'json' => [
                            'activities' => $activities->values()->all(),
                            'center' => [
                                'lat' => $centerLat,
                                'lng' => $centerLng
                            ],
                            'zoom' => 13,
                            'mode' => $mode // <-- INJEKSI PARAMETER MODE KE RENDERER
                        ]
                    ]);

                    $result = json_decode($response->getBody(), true);
                    
                    if (isset($result['success']) && $result['success']) {
                        $mapImageBase64 = $result['image'];
                    }
                } catch (\Exception $e) {
                    Log::error('Map Renderer Service Failed: ' . $e->getMessage());
                    // Fallback: Code akan lanjut tanpa gambar peta (View handle ini)
                }
            }
            
            // 5. TRACEABILITY & INTEGRITY METADATA
            $now = Carbon::now('Asia/Jakarta');
            
            // Generate Transaction ID unik (Hash)
            $trxId = 'TRX-' . $user->id . '-' . strtoupper(dechex($now->timestamp)) . '-' . strtoupper(Str::random(4));

            // Logika Teks Periode (Human Readable)
            $scopeText = 'Semua Riwayat Data';
            if ($fromDate && $toDate) {
                $scopeText = Carbon::parse($fromDate)->translatedFormat('d F Y') . ' s.d ' . Carbon::parse($toDate)->translatedFormat('d F Y');
            } elseif ($fromDate) {
                $scopeText = 'Sejak ' . Carbon::parse($fromDate)->translatedFormat('d F Y');
            } elseif ($toDate) {
                $scopeText = 'Sampai dengan ' . Carbon::parse($toDate)->translatedFormat('d F Y');
            }

            $meta = [
                // Identity
                'generated_by'   => $user->name,
                'user_nip'       => $user->nip ?? '-',
                'user_jabatan'   => $user->jabatan->nama_jabatan ?? ($user->role == 'kadis' ? 'Kepala Dinas' : 'Staff'),
                'unit_kerja'     => $user->unitKerja->nama_unit_kerja ?? 'Pemerintah Kabupaten Mimika',
                
                // Timestamp & Scope
                'timestamp'      => $now->format('d F Y, H:i') . ' WIB',
                'filter_scope'   => $scopeText,
                'data_count'     => $activities->count(),
                
                // Integrity
                'trx_id'         => $trxId,
                'security_hash'  => md5($trxId . $activities->count()), 
                'app_version'    => 'v2.1 (E-Daily Report)',
                'vis_mode'       => $mode === 'heatmap' ? 'Heatmap' : 'Clustering' // Info tambahan
            ];

            // 6. Generate PDF
            $pdf = Pdf::loadView('pdf.peta-aktivitas', [
                'activities' => $activities,
                'meta'       => $meta,
                'image'      => $mapImageBase64, 
            ])->setPaper('a4', 'portrait');

            // 7. Return PDF Stream
            $safeFilename = 'Laporan_Peta_' . preg_replace('/[^A-Za-z0-9]/', '', $user->username) . '_' . $now->format('YmdHis') . '.pdf';

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $safeFilename . '"');

        } catch (\Throwable $e) {
            // Adaptive Error Handling (JSON for AJAX, HTML for Direct)
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses dokumen: ' . $e->getMessage(),
                    'trace'   => config('app.debug') ? $e->getTraceAsString() : null
                ], 500);
            }
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

            "tanggal"            => Carbon::parse($item->tanggal_laporan)->translatedFormat('d M Y'),
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