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
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized - Sesi Habis'], 401);
        }

        $query = Pengumuman::with('creator')
            ->where(function($q) use ($user) {
                // Skenario A: Pengumuman UMUM (bidang_id null)
                $q->whereNull('bidang_id')
                // Skenario B: Pengumuman Spesifik Divisi/Bidang User
                  ->orWhere('bidang_id', $user->bidang_id)
                  ->orWhere('user_id_creator', $user->id);
            });

        // Filter Unit Kerja tetap dipertahankan sebagai scope organisasi
        if ($user->unit_kerja_id) {
            $query->where(function($q) use ($user) {
                $q->where('unit_kerja_id', $user->unit_kerja_id)
                  ->orWhereNull('unit_kerja_id');
            });
        }

        $data = $query->latest()->paginate(10);

        return response()->json($data);
    }

    /**
     * 2. CREATE PENGUMUMAN (API)
     * Mendukung multi-role logic: Staf/Penilai (Auto-bidang) vs Kadis (Manual-bidang)
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
            // Jika Kadis memilih divisi, wajib menyertakan target_bidang_id
            'target_bidang_id' => $isKadis && $request->target === 'divisi' ? 'required|exists:bidang,id' : 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // LOGIKA PENENTUAN BIDANG_ID
            $bidangId = null;
            if ($request->target === 'divisi') {
                if ($isKadis) {
                    // Kadis: Ambil dari dropdown/input pilihan
                    $bidangId = $request->target_bidang_id;
                } else {
                    // Staf/Penilai: Ambil otomatis dari profil pengirim
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
            
            // Trigger Notifikasi ke audiens yang tepat
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

        // Validasi Kepemilikan (Strict Ownership)
        if ($pengumuman->user_id_creator != Auth::id()) {
            return response()->json(['message' => 'Anda tidak memiliki otoritas menghapus pesan ini.'], 403);
        }
        
        $pengumuman->delete();
        return response()->json(['message' => 'Pengumuman berhasil dihapus']);
    }

    /**
     * Helper Private: Mengirim Notifikasi secara efisien.
     */
    private function dispatchNotification($pengumuman, $sender)
    {
        $type = NotificationType::PENGUMUMAN->value; 
        $message = "📢 Pengumuman " . ($pengumuman->bidang_id ? "(Divisi)" : "(Umum)") . ": " . $pengumuman->judul;

        $recipientQuery = User::where('id', '!=', $sender->id);

        if ($pengumuman->bidang_id) {
            // Target hanya orang di bidang/divisi yang ditunjuk
            $recipientQuery->where('bidang_id', $pengumuman->bidang_id);
        } else {
            // Target semua orang di Unit Kerja yang sama
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
     * SEARCH (FTS PostgreSQL)
     */
    public function search(Request $request)
    {
        $q = $request->q;
        $user = Auth::user();

        if (!$q || strlen($q) < 3) {
            return response()->json([]);
        }

        $pengumuman = Pengumuman::with('creator')
            ->where(function($query) use ($user) {
                $query->whereNull('bidang_id')->orWhere('bidang_id', $user->bidang_id);
            })
            ->where(function($query) use ($q) {
                $query->whereRaw("to_tsvector('indonesian', judul || ' ' || COALESCE(isi_pengumuman, '')) @@ plainto_tsquery('indonesian', ?)", [$q])
                      ->orWhereHas('creator', fn($u) => $u->where('name', 'ILIKE', "%$q%"));
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($pengumuman);
    }
}