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
     * LIST LKH KABID UNTUK KADIS
     * [REFACTORED] Dynamic Pagination & Server-side Filtering
     */
    public function index(Request $request)
    {
        $kadisId = Auth::id();

        $query = LaporanHarian::with(['user.jabatan', 'user.unitKerja', 'rencana', 'bukti'])
            ->where('atasan_id', $kadisId)
            ->where('status', '!=', 'draft');

        // === FILTER STATUS ===
        $query->when(
            $request->filled('status') && $request->status !== 'all',
            fn($q) => $q->where('status', $request->status)
        );

        // === FILTER BULAN ===
        $query->when(
            $request->filled('month'),
            fn($q) => $q->whereMonth('tanggal_laporan', $request->month)
        );

        // === FILTER TAHUN ===
        $query->when(
            $request->filled('year'),
            fn($q) => $q->whereYear('tanggal_laporan', $request->year)
        );

        // === SEARCH (Nama User & Deskripsi Aktivitas) ===
        if ($request->filled('search')) {
            $search = trim($request->search);
            $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($sub) use ($search, $like) {
                $sub->whereHas('user', fn($u) => 
                        $u->where('name', $like, "%{$search}%")
                    )
                    ->orWhere('deskripsi_aktivitas', $like, "%{$search}%");
            });
        }

        // Sorting: Waiting Review Paling Atas
        $query->orderByRaw("CASE WHEN status = 'waiting_review' THEN 1 ELSE 2 END")
            ->latest('tanggal_laporan');

        // --- PAGINATION LOGIC ---
        // Sanitasi input per_page (Default: 10, Max: 100)
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 10;
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * SHOW DETAIL LKH KABID
     */
    public function show($id)
    {
        $kadisId = Auth::id();

        $lkh = LaporanHarian::with(['user', 'rencana', 'bukti'])
            ->where('atasan_id', $kadisId)
            ->find($id);

        if (!$lkh) {
            return response()->json([
                'message' => 'Laporan tidak ditemukan atau bukan laporan bawahan Anda.'
            ], 404);
        }

        return response()->json(['data' => $lkh]);
    }

    /**
     * VALIDASI LKH OLEH KADIS
     */
    public function validateLkh(Request $request, $id)
    {
        $kadisId = Auth::id();

        // === VALIDASI INPUT ===
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'komentar_validasi' => 'required_if:status,rejected|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // === CEK DATA LKH ===
        $lkh = LaporanHarian::where('atasan_id', $kadisId)->find($id);

        if (!$lkh) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Hanya status waiting_review yang boleh divalidasi
        if ($lkh->status !== 'waiting_review') {
            return response()->json([
                'message' => 'Laporan ini sudah divalidasi sebelumnya.'
            ], 422);
        }

        // === PROSES VALIDASI ===
        try {
            DB::beginTransaction();

            $lkh->update([
                'status' => $request->status,
                'waktu_validasi' => now(),
                'komentar_validasi' => $request->komentar_validasi
            ]);

            // === SIAPKAN NOTIFIKASI ===
            $tglIndo = Carbon::parse($lkh->tanggal_laporan)
                        ->translatedFormat('d F Y');

            if ($request->status === 'approved') {
                $notifType = NotificationType::LKH_APPROVED->value;
                $notifMsg  = "Laporan Kabid tanggal {$tglIndo} telah DISETUJUI oleh Kadis.";
            } else {
                $notifType = NotificationType::LKH_REJECTED->value;
                $notifMsg  = "Laporan Kabid tanggal {$tglIndo} DITOLAK oleh Kadis. Catatan: {$request->komentar_validasi}";
            }

            NotificationService::send(
                $lkh->user_id,
                $notifType,
                $notifMsg,
                $lkh
            );

            DB::commit();

            return response()->json([
                'message' => 'Validasi berhasil dilakukan.',
                'data' => $lkh
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * MONITORING LKH STAF (yang sudah disetujui Kabid)
     * [REFACTORED] Consistent Pagination
     */
    public function monitoringStaf(Request $request)
    {
        $query = LaporanHarian::with(['user', 'rencana'])
            ->where('status', 'approved')
            // Filter hanya user dengan jabatan mengandung kata 'staf' (Opsional, tergantung bussines logic)
            ->whereHas('user.jabatan', function ($j) {
                $j->where('nama_jabatan', 'ilike', '%staf%')
                  ->orWhere('nama_jabatan', 'ilike', '%pelaksana%');
            });

        // === FILTER BULAN & TAHUN ===
        $query->when($request->month, fn($q, $m) => $q->whereMonth('tanggal_laporan', $m));
        $query->when($request->year, fn($q, $y) => $q->whereYear('tanggal_laporan', $y));

        // 2. Search (Cari nama staf tertentu)
        $query->when($request->search, function ($q, $search) {
            $like = config('database.default') === 'pgsql' ? 'ilike' : 'like';
            $q->whereHas('user', fn($u) => $u->where('name', $like, "%{$search}%"));
        });

        // --- PAGINATION LOGIC (Consistent with Index) ---
        $perPage = (int) $request->input('per_page', 20); // Default beda (20) karena monitoring biasanya butuh view lebih banyak
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 20;
        }

        return response()->json(
            $query->latest('tanggal_laporan')->paginate($perPage)
        );
    }
}