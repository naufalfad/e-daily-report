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

class ValidatorController extends Controller
{
    /**
     * 1. LIST LKH BAWAHAN (Inbox Validasi)
     */
    public function index(Request $request)
    {
        $atasanId = Auth::id();

        // [PERBAIKAN UTAMA] Ganti 'skp' menjadi 'rencana'
        // Karena di Model LaporanHarian, relasinya bernama: public function rencana()
        $query = LaporanHarian::with(['user', 'rencana', 'bukti']) 
            ->where('atasan_id', $atasanId) 
            ->where('status', '!=', 'draft'); 

        // Filter status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        } else {
            // Prioritaskan yang 'waiting_review' agar muncul paling atas
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

        // [PERBAIKAN UTAMA] Ganti 'skp' menjadi 'rencana'
        $lkh = LaporanHarian::with(['user', 'rencana', 'bukti'])
            ->where('atasan_id', $atasanId) // Pastikan hanya akses milik bawahannya
            ->find($id);

        if (!$lkh) {
            return response()->json([
                'message' => 'Laporan tidak ditemukan atau Anda tidak memiliki akses validasi.'
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
        
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'komentar_validasi' => 'required_if:status,rejected|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Cek Hak Akses & Keberadaan Data
        $lkh = LaporanHarian::where('atasan_id', $atasanId)->find($id);

        if (!$lkh) {
            return response()->json(['message' => 'Laporan tidak ditemukan atau akses ditolak'], 403);
        }

        try {
            // 3. Mulai Transaksi Database
            DB::beginTransaction();

            // Update Data LKH
            $lkh->update([
                'status'            => $request->status,
                'waktu_validasi'    => now(),
                'komentar_validasi' => $request->komentar_validasi
            ]);

            // 4. Logika Notifikasi
            $tglIndo = Carbon::parse($lkh->tanggal_laporan)->translatedFormat('d F Y');
            
            if ($request->status == 'approved') {
                $type = NotificationType::LKH_APPROVED->value; // Pastikan ambil value dari Enum
                $msg  = "Selamat! Laporan Harian tanggal {$tglIndo} telah DISETUJUI.";
            } else {
                $type = NotificationType::LKH_REJECTED->value;
                $previewKomentar = \Illuminate\Support\Str::limit($request->komentar_validasi, 50);
                $msg  = "Mohon revisi. Laporan tanggal {$tglIndo} DITOLAK. Catatan: {$previewKomentar}";
            }

            // Kirim Notifikasi
            try {
                NotificationService::send(
                    $lkh->user_id,
                    $type,
                    $msg,
                    $lkh 
                );
            } catch (\Exception $e) {
                // Silent fail notif
            }

            DB::commit();

            return response()->json([
                'message' => $request->status == 'approved' ? 'Laporan berhasil disetujui' : 'Laporan berhasil ditolak',
                'data'    => $lkh
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses validasi', 
                'error' => $e->getMessage()
            ], 500);
        }
    }
}