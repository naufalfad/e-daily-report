<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LaporanHarian;

class PetaAktivitasController extends Controller
{
    public function getPetaAktivitas(Request $request)
    {
        try {
            $userId = Auth::id();

            $lkh = LaporanHarian::with(['tupoksi', 'user'])
                ->where('user_id', $userId)
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

    public function getStafAktivitas(Request $request)
    {
        try {
            $userId = Auth::id();

            $lkh = LaporanHarian::with(['tupoksi', 'user'])
                ->where('atasan_id', $userId)
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
    
    public function getAllAktivitas(Request $request)
    {
        try {
            $lkh = LaporanHarian::with(['tupoksi', 'user'])
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
