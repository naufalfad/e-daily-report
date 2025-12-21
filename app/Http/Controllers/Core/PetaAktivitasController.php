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

    public function previewMapPdf(Request $request)
    {
        $user = Auth::user();
        $mapImageBase64 = null;

        // 1. Logika Pengambilan Data dan Filtering (Menggunakan logika getAllAktivitas/Kaban)
        $query = LaporanHarian::with(['user', 'tupoksi']);
            
        // **FIX KRITIS UNTUK KABAN/HEAD UNIT:** Filter berdasarkan unit kerja
        if ($user->unit_kerja_id) {
            $unitKerjaId = $user->unit_kerja_id;

            $query->whereHas('user', function ($q) use ($unitKerjaId) {
                $q->where('unit_kerja_id', $unitKerjaId);
            });
        }
        
        $query->whereNot('status', 'draft');
        
        // 2. Terapkan Filter Tanggal dari Request Query
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        
        if (!empty($fromDate)) {
            $query->whereDate('tanggal_laporan', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('tanggal_laporan', '<=', $toDate);
        }

        $laporanHarian = $query->orderBy('tanggal_laporan', 'desc')->get();
        
        // 3. Mapping Data untuk Renderer
        $activities = $laporanHarian->map(function ($item) {
            return $this->formatMapData($item);
        })->filter(function($item) {
            return !empty($item['lat']) && !empty($item['lng']);
        });
        
        // 4. Panggil Headless Renderer Service
        if ($activities->isNotEmpty()) {
            try {
                $client = new Client();
                $firstActivity = $activities->first();
                $rendererUrl = env('MAP_RENDER_URL', 'http://127.0.0.1:3000') . '/render-map'; 

                $response = $client->post($rendererUrl, [
                    'timeout' => 15.0, 
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
                \Log::error('Map Renderer Failed: ' . $e->getMessage());
            }
        }
        
        // 5. Siapkan Metadata
        $meta = [
            'nama'          => $user->name,
            'role'          => $user->role,
            'tanggal_cetak' => now()->format('d M Y, H:i'),
            'periode'       => ($fromDate && $toDate) ? 
                               Carbon::parse($fromDate)->format('d M M') . ' s/d ' . Carbon::parse($toDate)->format('d M Y') : 
                               'Semua Data',
            'unit_kerja'    => $user->unitKerja->nama_unit_kerja ?? 'Tidak Terdefinisi'
        ];

        // 6. Generate PDF
        $pdf = Pdf::loadView('pdf.peta-aktivitas', [
            'activities' => $activities,
            'meta'       => $meta,
            'image'      => $mapImageBase64, // Base64 dari Headless Renderer
        ])->setPaper('a4', 'portrait');

        // 7. Return PDF
        $filename = 'Peta_Aktivitas_' . $user->username . '_' . time() . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    private function formatMapData($item)
    {
        return [
            "id"                 => $item->id,
            "kegiatan"           => $item->deskripsi_aktivitas,
            "deskripsi"          => $item->output_hasil_kerja,
            "kategori_aktivitas" => $item->jenis_kegiatan,

            "tanggal"            => Carbon::parse($item->tanggal_laporan)->format('d M Y'),
            // Raw date berguna jika JS butuh sorting/filtering client-side tambahan
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