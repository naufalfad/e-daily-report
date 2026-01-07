<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LaporanHarian;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PetaAktivitasController extends Controller
{
    /**
     * Mengambil Peta Aktivitas User yang Sedang Login
     * Scope: Personal (JSON Data untuk Frontend)
     */
    public function getPetaAktivitas(Request $request)
    {
        try {
            $userId = Auth::id();

            $lkh = LaporanHarian::with(['tupoksi', 'user'])
                ->where('user_id', $userId)
                ->whereNot('status', 'draft')
                ->when($request->filled('from_date'), function ($q) use ($request) {
                    $q->whereDate('tanggal_laporan', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($q) use ($request) {
                    $q->whereDate('tanggal_laporan', '<=', $request->to_date);
                })
                ->orderBy('tanggal_laporan', 'desc')
                ->get();

            // Transformasi data menggunakan helper untuk konsistensi
            $result = $lkh->map(fn($item) => $this->formatMapData($item));

            return response()->json([
                "success" => true,
                "message" => "Data peta aktivitas pribadi berhasil diambil",
                "data"    => $result
            ]);

        } catch (\Exception $e) {
            Log::error("Error getPetaAktivitas: " . $e->getMessage());
            return response()->json([
                "success" => false,
                "message" => "Gagal mengambil data peta aktivitas",
                "error"   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil Peta Aktivitas Bawahan
     * Scope: Supervisor (JSON Data untuk Frontend)
     */
    public function getStafAktivitas(Request $request)
    {
        try {
            $userId = Auth::id();

            $lkh = LaporanHarian::with(['tupoksi', 'user'])
                ->where('atasan_id', $userId)
                ->whereNot('status', 'draft')
                ->when($request->filled('from_date'), function ($q) use ($request) {
                    $q->whereDate('tanggal_laporan', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($q) use ($request) {
                    $q->whereDate('tanggal_laporan', '<=', $request->to_date);
                })
                ->orderBy('tanggal_laporan', 'desc')
                ->get();

            $result = $lkh->map(fn($item) => $this->formatMapData($item));

            return response()->json([
                "success" => true,
                "message" => "Data peta aktivitas staf berhasil diambil",
                "data"    => $result
            ]);

        } catch (\Exception $e) {
            Log::error("Error getStafAktivitas: " . $e->getMessage());
            return response()->json([
                "success" => false,
                "message" => "Gagal mengambil data peta aktivitas staf",
                "error"   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil Semua Aktivitas
     * Scope: Admin/Global Monitoring (JSON Data untuk Frontend)
     */
    public function getAllAktivitas(Request $request)
    {
        try {
            $lkh = LaporanHarian::with(['tupoksi', 'user'])
                ->whereNot('status', 'draft')
                ->when($request->filled('from_date'), function ($q) use ($request) {
                    $q->whereDate('tanggal_laporan', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($q) use ($request) {
                    $q->whereDate('tanggal_laporan', '<=', $request->to_date);
                })
                ->orderBy('tanggal_laporan', 'desc')
                ->get();

            $result = $lkh->map(fn($item) => $this->formatMapData($item));

            return response()->json([
                "success" => true,
                "message" => "Data seluruh peta aktivitas berhasil diambil",
                "data"    => $result
            ]);

        } catch (\Exception $e) {
            Log::error("Error getAllAktivitas: " . $e->getMessage());
            return response()->json([
                "success" => false,
                "message" => "Gagal mengambil data peta aktivitas global",
                "error"   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview / Export PDF Peta Aktivitas (Dual Mode Support & Headless Rendering)
     * Menggunakan external Node.js service untuk rendering peta statis (Puppeteer).
     */
    public function previewMapPdf(Request $request)
    {
        // ------------------------------------------------------------------
        // FIX MEMORY LIMIT: Eskalasi resource khusus untuk proses PDF berat
        // ------------------------------------------------------------------
        // DOMPDF boros memori saat merender tabel panjang (Cellmap issue). 
        // Kita naikkan limit ke 2GB dan timeout ke 10 menit untuk request ini saja.
        ini_set('memory_limit', '2048M');
        set_time_limit(600); 

        try {
            $user = Auth::user();
            $mapImageBase64 = null;
            $rendererUrl = env('MAP_RENDER_URL', 'http://map-renderer-service:3000'); // Default Docker internal URL

            // 1. TANGKAP INPUT MODE
            // Default ke 'marker' jika tidak ada input valid
            $validModes = ['heatmap', 'clustering', 'marker'];
            $mode = $request->input('mode');
            if (!in_array($mode, $validModes)) {
                $mode = 'marker'; 
            }

            // 2. QUERY BUILDER
            $query = LaporanHarian::with(['user.jabatan', 'user.unitKerja', 'tupoksi'])
                ->whereNot('status', 'draft')
                // Filter Unit Kerja Scope (Jika user terikat unit kerja dan bukan superadmin)
                ->when($user->unit_kerja_id && $user->role !== 'admin', function ($q) use ($user) {
                    $q->whereHas('user', function ($subQ) use ($user) {
                        $subQ->where('unit_kerja_id', $user->unit_kerja_id);
                    });
                })
                // Filter Tanggal
                ->when($request->filled('from_date'), function ($q) use ($request) {
                    $q->whereDate('tanggal_laporan', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($q) use ($request) {
                    $q->whereDate('tanggal_laporan', '<=', $request->to_date);
                });

            $laporanHarian = $query->orderBy('tanggal_laporan', 'desc')->get();

            // 3. Data Processing & Geospatial Filtering
            // Penting: Hapus data yang tidak memiliki koordinat agar renderer tidak error
            $activities = $laporanHarian->map(fn($item) => $this->formatMapData($item))
                ->filter(fn($item) => !empty($item['lat']) && !empty($item['lng']));

            // ------------------------------------------------------------------
            // TAHAPAN 2: Komunikasi dengan Map Renderer Service
            // ------------------------------------------------------------------
            if ($activities->isNotEmpty()) {
                try {
                    // Set timeout client lebih lama untuk proses rendering peta
                    $client = new Client(['timeout' => 60.0]); 
                    
                    // Tentukan titik tengah peta (Fallback ke Mimika jika data kosong/error)
                    $firstActivity = $activities->first();
                    $centerLat = $firstActivity['lat'] ?? -4.5467;
                    $centerLng = $firstActivity['lng'] ?? 136.8833;

                    // OPTIMISASI: Buat array minimalis hanya Lat/Lng untuk dikirim ke Node.js
                    // Ini mengurangi beban memory saat JSON encode dan transfer data
                    $minimalDataForMap = $activities->map(function($act) {
                        return ['lat' => $act['lat'], 'lng' => $act['lng']];
                    })->values()->all();

                    // Kirim request ke Node.js Service
                    $response = $client->post($rendererUrl . '/render-map', [
                        'json' => [
                            'activities' => $minimalDataForMap,
                            'center' => [
                                'lat' => (float) $centerLat,
                                'lng' => (float) $centerLng
                            ],
                            'zoom' => 13,
                            'mode' => $mode // <--- Parameter Kunci dikirim ke Renderer
                        ]
                    ]);

                    $result = json_decode($response->getBody(), true);
                    
                    if (isset($result['success']) && $result['success']) {
                        // Image dikembalikan dalam bentuk Base64 Data URI
                        $mapImageBase64 = $result['image'];
                    } else {
                        Log::warning('Map Renderer returned false success status.');
                    }

                } catch (\Exception $e) {
                    // Fallback Strategy:
                    // Jika renderer mati, PDF tetap digenerate tapi tanpa gambar peta
                    Log::error('Map Renderer Service Failed: ' . $e->getMessage());
                }
            }

            // 5. Traceability & Metadata (Untuk Header PDF)
            $now = Carbon::now('Asia/Jakarta');
            
            // Generate Transaction ID Unik untuk Audit Trail
            $trxId = sprintf(
                'TRX-MAP-%s-%s', 
                $user->id, 
                strtoupper(Str::random(6))
            );

            // Logika Teks Periode Laporan
            $scopeText = 'Semua Riwayat Data';
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $scopeText = Carbon::parse($request->from_date)->translatedFormat('d M Y') . ' - ' . Carbon::parse($request->to_date)->translatedFormat('d M Y');
            }

            $meta = [
                'generated_by'   => $user->name,
                'user_nip'       => $user->nip ?? '-',
                'unit_kerja'     => $user->unitKerja->nama_unit_kerja ?? 'Pemerintah Kabupaten Mimika',
                'timestamp'      => $now->format('d F Y, H:i') . ' WIB',
                'filter_scope'   => $scopeText,
                'data_count'     => $activities->count(),
                'trx_id'         => $trxId,
                'vis_mode'       => ucfirst($mode), // Info mode untuk ditampilkan di PDF
                'security_hash'  => md5($trxId . $activities->count() . config('app.key'))
            ];

            // 6. Generate PDF View
            $pdf = Pdf::loadView('pdf.peta-aktivitas', [
                'activities' => $activities,
                'meta'       => $meta,
                'image'      => $mapImageBase64, // Hasil render dari Puppeteer
                'mode'       => $mode 
            ]);
            
            // Setting Kertas & Optimasi DOMPDF
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 96,
                'defaultFont' => 'sans-serif'
            ]);

            // 7. Stream Download
            $safeFilename = 'Peta_Aktivitas_' . $mode . '_' . $now->format('Ymd_His') . '.pdf';

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $safeFilename . '"');

        } catch (\Throwable $e) {
            // Error Handling jika terjadi crash fatal saat PDF generation
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses dokumen PDF: ' . $e->getMessage(),
                ], 500);
            }
            
            // Return error text sederhana jika bukan AJAX
            Log::error('PDF Gen Error: ' . $e->getMessage());
            return response("Terjadi kesalahan sistem (Memory Limit Exhausted). Data terlalu banyak untuk dicetak sekaligus. Silakan persempit rentang tanggal laporan.", 500);
        }
    }

    /**
     * Helper: Format Data Transform Object (DTO)
     * Memastikan tipe data float untuk lat/lng agar aman dikonsumsi JSON
     */
    private function formatMapData($item)
    {
        return [
            "id"                 => $item->id,
            "kegiatan"           => $item->deskripsi_aktivitas,
            "deskripsi"          => $item->output_hasil_kerja, // Ringkasan hasil
            "kategori_aktivitas" => $item->jenis_kegiatan,
            
            // Format Tanggal Indonesia
            "tanggal"            => Carbon::parse($item->tanggal_laporan)->translatedFormat('d M Y'),
            "waktu"              => substr($item->waktu_mulai, 0, 5) . " - " . substr($item->waktu_selesai, 0, 5),
            
            "status"             => $item->status,
            "user"               => $item->user->name ?? "User",
            "jabatan"            => $item->user->jabatan->nama_jabatan ?? "-",

            // Koordinat Geospasial (Casting ke float penting untuk Map Library)
            "lat"                => $item->lat ? (float) $item->lat : null,
            "lng"                => $item->lng ? (float) $item->lng : null,

            // Metadata Tambahan
            "lokasi_teks"        => $item->lokasi_teks,
            "is_luar_lokasi"     => (bool) $item->is_luar_lokasi, 
        ];
    }
}