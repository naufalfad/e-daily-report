<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\NotificationService;
use App\Enums\NotificationType; // IMPORT ENUM WAJIB

class ValidatorController extends Controller
{
    /**
     * 1. LIST LKH BAWAHAN (Inbox Validasi)
     */
    public function index(Request $request)
    {
        $atasanId = Auth::id();

        $query = LaporanHarian::with(['user', 'skp', 'bukti'])
            ->where('user_id', $atasanId);

        // Filter status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Prioritaskan waiting_review
            $query->orderByRaw("CASE WHEN status = 'waiting_review' THEN 1 ELSE 2 END");
        }

        // Filter tanggal
        if ($request->has('tanggal')) {
            $query->whereDate('tanggal_laporan', $request->tanggal);
        }

        $data = $query->latest('tanggal_laporan')->paginate(10);

        return response()->json($data);
    }

    /**
     * 2. SHOW DETAIL LKH
     */
    public function show($id)
    {
        $atasanId = Auth::id();

        $lkh = LaporanHarian::with(['user', 'skp', 'bukti'])
            ->where('atasan_id', $atasanId)
            ->find($id);

        if (!$lkh) {
            return response()->json([
                'message' => 'Laporan tidak ditemukan atau bukan milik bawahan Anda'
            ], 404);
        }

        return response()->json(['data' => $lkh]);
    }

    /**
     * 3. VALIDASI LKH (approve/reject)
     */
    public function validateLkh(Request $request, $id)
    {
        $atasanId = Auth::id();

        // Ambil laporan yang memang ditujukan ke atasan
        $lkh = LaporanHarian::where('atasan_id', $atasanId)->find($id);

        if (!$lkh) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'komentar_validasi' => 'required_if:status,rejected|nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update LKH
        $lkh->update([
            'status' => $request->status,
            'atasan_id' => $atasanId,
            'waktu_validasi' => now(),
            'komentar_validasi' => $request->komentar_validasi
        ]);

        // --- REFACTOR NOTIFIKASI DIMULAI ---
        
        // 1. Gunakan Enum untuk Type Safety
        $type = $request->status == 'approved' 
            ? NotificationType::LKH_APPROVED 
            : NotificationType::LKH_REJECTED;

        $msg  = $request->status == 'approved'
                ? 'Selamat! LKH tanggal ' . $lkh->tanggal_laporan . ' disetujui.'
                : 'LKH tanggal ' . $lkh->tanggal_laporan . ' ditolak. Cek komentar atasan.';

        // 2. Pass Object Model ($lkh) langsung, BUKAN ID-nya ($lkh->id)
        // Service akan otomatis mendeteksi:
        // related_id   = $lkh->id
        // related_type = 'App\Models\LaporanHarian'
        NotificationService::send(
            $lkh->user_id,
            $type,
            $msg,
            $lkh // <-- CRITICAL CHANGE: Pass Object, not Integer
        );

        // --- REFACTOR SELESAI ---

        return response()->json([
            'message' => $request->status == 'approved' ? 'Laporan diterima' : 'Laporan ditolak',
            'data' => $lkh
        ]);
    }
}