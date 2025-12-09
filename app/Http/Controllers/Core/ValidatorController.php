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
     * [REFACTORED] Filter lengkap server-side
     */
    public function index(Request $request)
    {
        $atasanId = Auth::id();

        // Base Query: LKH milik bawahan (bukan diri sendiri) & bukan draft
        $query = LaporanHarian::with(['user', 'rencana', 'bukti']) 
            ->where('atasan_id', $atasanId) 
            ->where('user_id', '!=', $atasanId)
            ->where('status', '!=', 'draft'); 

        // 1. Filter Status
        $query->when($request->status && $request->status !== 'all', function ($q, $status) {
            $q->where('status', $status);
        });

        // 2. Filter Bulan & Tahun
        $query->when($request->month, function ($q, $month) {
            $q->whereMonth('tanggal_laporan', $month);
        });

        $query->when($request->year, function ($q, $year) {
            $q->whereYear('tanggal_laporan', $year);
        });

        // 3. Search (Nama Bawahan / Deskripsi Aktivitas)
        $query->when($request->search, function($q, $search) {
             $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';
             $q->where(function($sub) use ($search, $like) {
                // Cari di Nama User
                $sub->whereHas('user', function($u) use ($search, $like) {
                    $u->where('name', $like, "%{$search}%");
                })
                // Atau Cari di Deskripsi LKH
                ->orWhere('deskripsi_aktivitas', $like, "%{$search}%");
             });
        });

        // 4. Legacy Filter Tanggal Spesifik
        $query->when($request->tanggal, fn($q, $d) => $q->whereDate('tanggal_laporan', $d));

        // Sorting: Prioritaskan 'waiting_review' di atas, sisanya urut tanggal terbaru
        $query->orderByRaw("CASE WHEN status = 'waiting_review' THEN 1 ELSE 2 END")
              ->latest('tanggal_laporan');

        $data = $query->paginate(10);

        return response()->json($data);
    }

    /**
     * 2. SHOW DETAIL LKH
     */
    public function show($id)
    {
        $atasanId = Auth::id();

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
                $type = NotificationType::LKH_APPROVED->value; 
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