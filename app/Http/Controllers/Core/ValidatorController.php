<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\NotificationService; // <-- PENTING: Import Service Notifikasi

class ValidatorController extends Controller
{
    /**
     * 1. LIST LKH BAWAHAN (Inbox Validasi)
     */
    public function index(Request $request)
    {
        $atasanId = Auth::id();
        $bawahanIds = User::where('atasan_id', $atasanId)->pluck('id');

        $query = LaporanHarian::with(['user', 'skp', 'bukti'])
            ->whereIn('user_id', $bawahanIds);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->orderByRaw("CASE WHEN status = 'waiting_review' THEN 1 ELSE 2 END");
        }

        if ($request->has('tanggal')) {
            $query->whereDate('tanggal_laporan', $request->tanggal);
        }

        $data = $query->latest('tanggal_laporan')->paginate(10);

        return response()->json($data);
    }

    /**
     * 2. SHOW (Detail LKH Bawahan)
     */
    public function show($id)
    {
        $atasanId = Auth::id();
        $bawahanIds = User::where('atasan_id', $atasanId)->pluck('id');

        $lkh = LaporanHarian::with(['user', 'skp', 'bukti'])
            ->whereIn('user_id', $bawahanIds)
            ->find($id);

        if (!$lkh) {
            return response()->json(['message' => 'Laporan tidak ditemukan atau bukan milik bawahan Anda'], 404);
        }

        return response()->json(['data' => $lkh]);
    }

    /**
     * 3. ACTION (Approve / Reject + Notifikasi)
     */
    public function validateLkh(Request $request, $id)
    {
        $atasanId = Auth::id();
        $bawahanIds = User::where('atasan_id', $atasanId)->pluck('id');

        $lkh = LaporanHarian::whereIn('user_id', $bawahanIds)->find($id);

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

        // Update Data LKH
        $lkh->update([
            'status' => $request->status,
            'validator_id' => $atasanId,
            'waktu_validasi' => now(),
            'komentar_validasi' => $request->komentar_validasi
        ]);

        // [BARU] Kirim Notifikasi Balik ke Pegawai
        $type = $request->status == 'approved' ? 'lkh_approved' : 'lkh_rejected';
        $msg  = $request->status == 'approved' 
                ? 'Selamat! LKH tanggal ' . $lkh->tanggal_laporan . ' disetujui.' 
                : 'LKH tanggal ' . $lkh->tanggal_laporan . ' ditolak. Cek komentar atasan.';

        NotificationService::send(
            $lkh->user_id,
            $type,
            $msg,
            $lkh->id
        );

        $pesan = $request->status == 'approved' ? 'Laporan diterima' : 'Laporan ditolak';

        return response()->json([
            'message' => $pesan,
            'data' => $lkh
        ]);
    }
}