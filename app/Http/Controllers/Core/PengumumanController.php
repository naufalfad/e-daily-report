<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

// Import Service & Enum untuk Notifikasi
use App\Services\NotificationService;
use App\Enums\NotificationType;

class PengumumanController extends Controller
{
    /**
     * 1. LIST PENGUMUMAN (API)
     * Menggabungkan List Basic, Filter Tanggal, Search Keyword, dan Scope dalam satu endpoint.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized - Sesi Habis'], 401);
        }

        // Mulai Query dengan Eager Loading User Creator
        $query = Pengumuman::with('creator');

        // --- FILTER 1: SEARCH KEYWORD (Judul, Isi, Nama Pembuat) ---
        if ($request->filled('q')) {
            $keyword = $request->q;
            // Jika keyword < 3 karakter, biasanya diabaikan untuk performance, 
            // tapi untuk fleksibilitas kita izinkan search apa adanya.
            $query->where(function($subQ) use ($keyword) {
                // Full Text Search PostgreSQL untuk performa (jika sudah di-setup)
                // Atau fallback ke ILIKE standar Laravel
                $subQ->whereRaw("to_tsvector('indonesian', judul || ' ' || COALESCE(isi_pengumuman, '')) @@ plainto_tsquery('indonesian', ?)", [$keyword])
                     ->orWhere('judul', 'ILIKE', "%{$keyword}%") // Fallback manual
                     ->orWhere('isi_pengumuman', 'ILIKE', "%{$keyword}%")
                     ->orWhereHas('creator', function($creatorQ) use ($keyword) {
                         $creatorQ->where('name', 'ILIKE', "%{$keyword}%");
                     });
            });
        }

        // --- FILTER 2: DATE RANGE (Rentang Tanggal) ---
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // --- FILTER 3: SCOPE & HAK AKSES (Logic Inti) ---
        // Logika dasar: Tampilkan yang (UMUM) OR (BIDANG SAYA) OR (SAYA BUAT)
        $query->where(function($q) use ($user) {
            $q->whereNull('bidang_id') // Umum
              ->orWhere('bidang_id', $user->bidang_id) // Bidang User
              ->orWhere('user_id_creator', $user->id); // Owner (Penting untuk Kadis)
        });

        // --- FILTER 4: UNIT KERJA (Global Scope) ---
        if ($user->unit_kerja_id) {
            $query->where(function($q) use ($user) {
                $q->where('unit_kerja_id', $user->unit_kerja_id)
                  ->orWhereNull('unit_kerja_id');
            });
        }

        // Eksekusi Pagination (12 item per halaman untuk Grid 3 kolom)
        $data = $query->latest()->paginate(12);

        // Pertahankan query string (q, start_date, dll) pada link pagination
        $data->appends($request->all());

        return response()->json($data);
    }

    /**
     * 2. CREATE PENGUMUMAN (API)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Cek apakah user adalah Kadis/Kaban
        $isKadis = $user->roles->contains('nama_role', 'Kadis') || $user->roles->contains('nama_role', 'Kaban');

        // Validasi Input
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'isi_pengumuman' => 'required|string',
            'target' => 'required|in:umum,divisi',
            'target_bidang_id' => $isKadis && $request->target === 'divisi' ? 'required|exists:bidang,id' : 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $bidangId = null;
            if ($request->target === 'divisi') {
                if ($isKadis) {
                    $bidangId = $request->target_bidang_id;
                } else {
                    $bidangId = $user->bidang_id;
                }
            }

            $pengumuman = Pengumuman::create([
                'user_id_creator' => $user->id,
                'judul'           => $request->judul,
                'isi_pengumuman'  => $request->isi_pengumuman,
                'unit_kerja_id'   => $user->unit_kerja_id,
                'bidang_id'       => $bidangId,
            ]);
            
            $this->dispatchNotification($pengumuman, $user);

            DB::commit();

            return response()->json([
                'message' => 'Pengumuman berhasil disebarkan',
                'data' => $pengumuman
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat pengumuman', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 3. DELETE PENGUMUMAN (API)
     */
    public function destroy($id)
    {
        $pengumuman = Pengumuman::find($id);
        
        if (!$pengumuman) {
            return response()->json(['message' => 'Pengumuman tidak ditemukan'], 404);
        }

        if ($pengumuman->user_id_creator != Auth::id()) {
            return response()->json(['message' => 'Anda tidak memiliki otoritas menghapus pesan ini.'], 403);
        }
        
        $pengumuman->delete();
        return response()->json(['message' => 'Pengumuman berhasil dihapus']);
    }

    /**
     * Helper Private: Mengirim Notifikasi
     */
    private function dispatchNotification($pengumuman, $sender)
    {
        $type = NotificationType::PENGUMUMAN->value; 
        $message = "📢 Pengumuman " . ($pengumuman->bidang_id ? "(Divisi)" : "(Umum)") . ": " . $pengumuman->judul;

        $recipientQuery = User::where('id', '!=', $sender->id);

        if ($pengumuman->bidang_id) {
            $recipientQuery->where('bidang_id', $pengumuman->bidang_id);
        } else {
            if ($pengumuman->unit_kerja_id) {
                $recipientQuery->where('unit_kerja_id', $pengumuman->unit_kerja_id);
            }
        }

        $recipientIds = $recipientQuery->pluck('id'); 

        if ($recipientIds->isNotEmpty()) {
            $payload = [];
            $now = now();

            foreach ($recipientIds as $uid) {
                $payload[] = [
                    'user_id_recipient' => $uid,
                    'tipe_notifikasi'   => $type,
                    'pesan'             => $message,
                    'related_id'        => $pengumuman->id,
                    'related_type'      => get_class($pengumuman),
                    'is_read'           => 0,
                    'created_at'        => $now,
                    'updated_at'        => $now
                ];
            }

            NotificationService::sendBatch($payload);
        }
    }

    /**
     * SEARCH (DEPRECATED - MERGED INTO INDEX)
     * Method ini bisa dihapus atau di-redirect ke index() jika masih ada frontend lama yg manggil.
     * Tapi sebaiknya frontend diarahkan ke index() dengan parameter ?q=...
     */
    public function search(Request $request)
    {
        // Redirect ke index dengan parameter pencarian
        return $this->index($request);
    }
}