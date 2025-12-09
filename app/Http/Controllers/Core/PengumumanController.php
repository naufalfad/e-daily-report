<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // [FIX] Import DB Facade

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

        // [FIX CRITICAL] Cek apakah user login. Jika sesi habis, return 401.
        if (!$user) {
            return response()->json(['message' => 'Unauthorized - Sesi Habis'], 401);
        }

        // Logika: Tampilkan pengumuman Unit Kerja ATAU Global
        $query = Pengumuman::with('creator')
            ->where(function($q) use ($user) {
                $q->where('unit_kerja_id', $user->unit_kerja_id)
                  ->orWhereNull('unit_kerja_id'); // Global
            });

        $data = $query->latest()->paginate(5);

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

        // Validasi Akses
        if ($user->roles->contains('nama_role', 'Pegawai') && count($user->roles) == 1) {
            return response()->json(['message' => 'Anda tidak memiliki akses membuat pengumuman'], 403);
        }

        // Validasi Input
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'isi_pengumuman' => 'required|string',
            'unit_kerja_id' => 'nullable|exists:unit_kerja,id' // Null = Global
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // [FIX] Mulai Transaksi Database
            // Menjamin create data dan notifikasi berjalan atomik (sukses semua atau gagal semua)
            DB::beginTransaction();

            // 1. Simpan ke Database
            $pengumuman = Pengumuman::create([
                'user_id_creator' => $user->id,
                'judul' => $request->judul,
                'isi_pengumuman' => $request->isi_pengumuman,
                'unit_kerja_id' => $request->unit_kerja_id,
            ]);
            
            // 2. Trigger Notifikasi (Hanya dieksekusi sekali di sini)
            $this->dispatchNotification($pengumuman, $user);

            // [FIX] Commit Transaksi
            DB::commit();

            return response()->json([
                'message' => 'Pengumuman berhasil dibuat dan disebarkan',
                'data' => $pengumuman
            ], 201);

        } catch (\Exception $e) {
            // [FIX] Rollback jika ada error
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat pengumuman', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 3. DELETE PENGUMUMAN (API)
     */
    public function destroy($id)
    {
        // 1. Cari dulu datanya secara global (tanpa filter where user)
        $pengumuman = Pengumuman::find($id);
        
        // Jika data benar-benar tidak ada di DB
        if (!$pengumuman) {
            return response()->json(['message' => 'Pengumuman tidak ditemukan'], 404);
        }

        // 2. Validasi Kepemilikan (STRICT MODE)
        // Cek apakah ID yang login SAMA DENGAN ID pembuat pengumuman?
        if ($pengumuman->user_id_creator != Auth::id()) {
            return response()->json([
                'message' => 'Anda tidak dapat menghapus pengumuman ini karena bukan milik Anda.'
            ], 403); // 403 = Forbidden (Dilarang)
        }
        
        // 3. Jika lolos validasi (Milik Pribadi), baru hapus
        $pengumuman->delete();
        return response()->json(['message' => 'Pengumuman berhasil dihapus']);
    }

    /**
     * Helper Private: Mengirim Notifikasi ke Target Audience
     */
    private function dispatchNotification($pengumuman, $sender)
    {
        // Pastikan Enum NotificationType sudah ada, gunakan ->value untuk ambil string
        $type = NotificationType::PENGUMUMAN->value; 
        $message = "📢 Pengumuman: " . $pengumuman->judul;

        if ($pengumuman->unit_kerja_id) {
            // SKENARIO A: Broadcast ke Unit Kerja Spesifik
            NotificationService::broadcastToUnit(
                $pengumuman->unit_kerja_id,
                $type,
                $message,
                $pengumuman
            );
        } else {
            // SKENARIO B: Broadcast Global
            $recipientIds = User::where('id', '!=', $sender->id)->pluck('id'); 

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
}