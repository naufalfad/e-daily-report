<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LaporanHarian;
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
        $file = $request->query('file');

        $path = storage_path('app/public/' . $file);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $file . '"'
        ]);
    }

    public function exportMap(Request $request)
    {
        $request->validate([
            'image' => 'required'
        ]);

        $user = Auth::user();

        // Ambil semua aktivitas user
        $activities = \App\Models\Aktivitas::where('user_id', $user->id)
            ->orderBy('tanggal_laporan', 'desc')
            ->get();

        $meta = [
            'nama'   => $user->name,
            'role'   => $user->role,
            'tanggal_laporan'=> now()->format('d M Y, H:i'),
        ];

        $image = $request->image;

        $pdf = Pdf::loadView('pdf.peta-aktivitas', [
            'image'      => $image,
            'meta'       => $meta,
            'activities' => $activities,
        ])->setPaper('a4', 'portrait');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename=peta-aktivitas.pdf');
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