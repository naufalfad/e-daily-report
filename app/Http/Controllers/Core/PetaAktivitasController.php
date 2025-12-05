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
     */
    public function getPetaAktivitas(Request $request)
    {
        try {
            $userId = Auth::id();

            $lkh = LaporanHarian::with(['tupoksi', 'user'])
                ->where('user_id', $userId)
                ->whereNot('status', 'draft')
                ->orderBy('tanggal_laporan', 'desc')
                ->get();

            $result = $lkh->map(function ($item) {

                return [
                    "id" => $item->id,
                    "kegiatan" => $item->deskripsi_aktivitas,
                    "deskripsi" => $item->output_hasil_kerja,
                    "kategori_aktivitas" => $item->jenis_kegiatan,

                    "tanggal" => \Carbon\Carbon::parse($item->tanggal_laporan)->format('d M Y'),
                    "waktu" => substr($item->waktu_mulai, 0, 5) . " - " . substr($item->waktu_selesai, 0, 5),

                    "status" => $item->status,
                    "user" => $item->user->name ?? "User",

                    // langsung dari accessor Model
                    "lat" => $item->lat,
                    "lng" => $item->lng,

                    // [BARU] Tambahkan lokasi teks
                    "lokasi_teks" => $item->lokasi_teks,

                    "is_luar_lokasi" => $item->is_luar_lokasi,
                ];
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

            $lkh = LaporanHarian::with(['tupoksi', 'user'])
                ->where('atasan_id', $userId)
                ->whereNot('status', 'draft')
                ->orderBy('tanggal_laporan', 'desc')
                ->get();

            $result = $lkh->map(function ($item) {

                return [
                    "id" => $item->id,
                    "kegiatan" => $item->deskripsi_aktivitas,
                    "deskripsi" => $item->output_hasil_kerja,
                    "kategori_aktivitas" => $item->jenis_kegiatan,

                    "tanggal" => \Carbon\Carbon::parse($item->tanggal_laporan)->format('d M Y'),
                    "waktu" => substr($item->waktu_mulai, 0, 5) . " - " . substr($item->waktu_selesai, 0, 5),

                    "status" => $item->status,
                    "user" => $item->user->name ?? "User",

                    // langsung dari accessor Model
                    "lat" => $item->lat,
                    "lng" => $item->lng,

                    // [BARU] Tambahkan lokasi teks
                    "lokasi_teks" => $item->lokasi_teks,

                    "is_luar_lokasi" => $item->is_luar_lokasi,
                ];
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

    
    /**
     * Mengambil Semua Aktivitas (Mungkin untuk Admin/Dashboard Global)
     */
    public function getAllAktivitas(Request $request)
    {
        try {
            $lkh = LaporanHarian::with(['tupoksi', 'user'])
                ->whereNot('status', 'draft')
                ->orderBy('tanggal_laporan', 'desc')
                ->get();

            $result = $lkh->map(function ($item) {

                return [
                    "id"        => $item->id,
                    "kegiatan"  => $item->deskripsi_aktivitas,
                    "deskripsi" => $item->output_hasil_kerja,
                    "kategori_aktivitas" => $item->jenis_kegiatan,

                    "tanggal"   => \Carbon\Carbon::parse($item->tanggal_laporan)->format('d M Y'),
                    "waktu"     => substr($item->waktu_mulai, 0, 5) . " - " . substr($item->waktu_selesai, 0, 5),

                    "status"    => $item->status,
                    "user"      => $item->user->name ?? "User",

                    // langsung dari accessor Model
                    "lat"       => $item->lat,
                    "lng"       => $item->lng,

                    // [BARU] Tambahkan lokasi teks juga disini agar konsisten
                    "lokasi_teks" => $item->lokasi_teks,

                    "is_luar_lokasi" => $item->is_luar_lokasi,
                ];
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
}