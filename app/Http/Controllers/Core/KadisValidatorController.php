<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use App\Enums\NotificationType;
use Carbon\Carbon;

class KadisValidatorController extends Controller
{
    /**
     * 1. LIST LKH dari KABID yang harus divalidasi Kadis
     */
    public function index(Request $request)
    {
        $kadisId = Auth::id(); // ID Kadis

        // Laporan MILIK KABID dan atasan_id = kadis
        $query = LaporanHarian::with(['user', 'skp', 'bukti'])
            ->whereHas('user', function ($q) {
                $q->whereHas('jabatan', function ($j) {
                    $j->where('nama_jabatan', 'like', '%kabid%');
                });
            })
            ->where('atasan_id', $kadisId)
            ->where('status', '!=', 'draft');

        // Filter status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $query->orderByRaw("CASE WHEN status = 'waiting_review' THEN 1 ELSE 2 END");

        return response()->json($query->latest('tanggal_laporan')->paginate(10));
    }

    /**
     * 2. SHOW LKH KABID
     */
    public function show($id)
    {
        $kadisId = Auth::id();

        $lkh = LaporanHarian::with(['user', 'skp', 'bukti'])
            ->where('atasan_id', $kadisId)
            ->find($id);

        if (!$lkh) {
            return response()->json([
                'message' => 'Laporan tidak ditemukan atau bukan laporan Kabid.'
            ], 404);
        }

        return response()->json(['data' => $lkh]);
    }


    /**
     * 3. VALIDASI LKH dari Kabid
     */
    public function validateLkh(Request $request, $id)
    {
        $kadisId = Auth::id();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'komentar_validasi' => 'required_if:status,rejected|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cari laporan milik KABID dengan atasan Kadis
        $lkh = LaporanHarian::where('atasan_id', $kadisId)->find($id);

        if (!$lkh) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        try {
            DB::beginTransaction();

            $lkh->update([
                'status' => $request->status,
                'waktu_validasi' => now(),
                'komentar_validasi' => $request->komentar_validasi
            ]);

            // Notifikasi ke Kabid
            $tglIndo = Carbon::parse($lkh->tanggal_laporan)->translatedFormat('d F Y');

            if ($request->status === 'approved') {
                $type = NotificationType::LKH_APPROVED;
                $msg = "Laporan Kabid pada tanggal {$tglIndo} telah disetujui Kadis.";
            } else {
                $type = NotificationType::LKH_REJECTED;
                $msg = "Laporan Kabid pada tanggal {$tglIndo} ditolak Kadis.";
            }

            NotificationService::send($lkh->user_id, $type, $msg, $lkh);

            DB::commit();

            return response()->json([
                'message' => 'Validasi berhasil',
                'data' => $lkh
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 4. MONITORING LAPORAN STAF (hanya yang sudah di-approve Kabid)
     */
    public function monitoringStaf()
    {
        // Ambil laporan staf yang statusnya APPROVED
        $data = LaporanHarian::with(['user', 'skp'])
            ->where('status', 'approved')
            ->whereHas('user.jabatan', function ($j) {
                $j->where('nama_jabatan', 'like', '%staf%');
            })
            ->latest('tanggal_laporan')
            ->paginate(20);

        return response()->json($data);
    }
}