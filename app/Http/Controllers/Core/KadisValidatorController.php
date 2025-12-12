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
    public function index(Request $request)
    {
        $kadisId = Auth::id();

        // [DEBUG] Log filter yang diterima (Cek di storage/logs/laravel.log)
        \Log::info('Filter Kadis:', $request->all());

        $query = LaporanHarian::with(['user.jabatan', 'user.unitKerja', 'rencana', 'bukti'])
            ->where('atasan_id', $kadisId)
            ->where('user_id', '!=', $kadisId)
            ->where('status', '!=', 'draft');

        // 1. Filter Status
        $query->when(
            $request->filled('status') && $request->status !== 'all',
            fn($q) => $q->where('status', $request->status)
        );

        // 2. Search Fix (BUG utama)
        if (!empty(trim($request->search))) {
            $search = trim($request->search);
            $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($sub) use ($search, $like) {
                $sub->whereHas('user', function ($u) use ($search, $like) {
                    $u->where('name', $like, "%{$search}%");
                })
                    ->orWhere('deskripsi_aktivitas', $like, "%{$search}%");
            });
        }
        // 2. Filter Bulan
        $query->when($request->filled('month'), function ($q) use ($request) {
            $q->whereMonth('tanggal_laporan', $request->month);
        });

        // 3. Filter Tahun
        $query->when($request->filled('year'), function ($q) use ($request) {
            $q->whereYear('tanggal_laporan', $request->year);
        });

        // 4. Search (Nama User / Deskripsi)
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';

            $q->where(function ($sub) use ($search, $like) {
                $sub->whereHas('user', function ($u) use ($search, $like) {
                    $u->where('name', $like, "%{$search}%");
                })
                    ->orWhere('deskripsi_aktivitas', $like, "%{$search}%");
            });
        });

        // Sorting: Waiting Review Paling Atas
        $query->orderByRaw("CASE WHEN status = 'waiting_review' THEN 1 ELSE 2 END")
            ->latest('tanggal_laporan');

        return response()->json($query->paginate(10));
    }

    /**
     * 2. SHOW LKH KABID
     */
    public function show($id)
    {
        $kadisId = Auth::id();

        $lkh = LaporanHarian::with(['user', 'rencana', 'bukti'])
            ->where('atasan_id', $kadisId)
            ->find($id);

        if (!$lkh) {
            return response()->json([
                'message' => 'Laporan tidak ditemukan atau bukan laporan bawahan langsung Anda.'
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

            // Kirim Notifikasi
            $tglIndo = Carbon::parse($lkh->tanggal_laporan)->translatedFormat('d F Y');

            if ($request->status === 'approved') {
                $type = NotificationType::LKH_APPROVED->value;
                $msg = "Laporan Kabid pada tanggal {$tglIndo} telah disetujui Kadis.";
            } else {
                $type = NotificationType::LKH_REJECTED->value;
                $msg = "Laporan Kabid pada tanggal {$tglIndo} ditolak Kadis.";
            }

            try {
                NotificationService::send($lkh->user_id, $type, $msg, $lkh);
            } catch (\Exception $e) {
                \Log::warning("Gagal kirim notif: " . $e->getMessage());
            }

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
     * [REFACTORED] Tambahkan Filter agar Kadis bisa cari LKH Staf spesifik
     */
    public function monitoringStaf(Request $request)
    {
        // Ambil laporan staf yang statusnya APPROVED
        // [FIX RELASI] 'rencana'
        $query = LaporanHarian::with(['user', 'rencana'])
            ->where('status', 'approved')
            // Filter hanya user dengan jabatan mengandung kata 'staf' (Opsional, tergantung bussines logic)
            ->whereHas('user.jabatan', function ($j) {
                $j->where('nama_jabatan', 'ilike', '%staf%')
                    ->orWhere('nama_jabatan', 'ilike', '%pelaksana%'); // Tambahan coverage
            });

        // 1. Filter Bulan & Tahun (Wajib ada untuk monitoring)
        $query->when($request->month, fn($q, $m) => $q->whereMonth('tanggal_laporan', $m));
        $query->when($request->year, fn($q, $y) => $q->whereYear('tanggal_laporan', $y));

        // 2. Search (Cari nama staf tertentu)
        $query->when($request->search, function ($q, $search) {
            $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';
            $q->whereHas('user', fn($u) => $u->where('name', $like, "%{$search}%"));
        });

        // [FIX KOLOM]
        $data = $query->latest('tanggal_laporan')->paginate(20);

        return response()->json($data);
    }
}