<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

// [BARU] Import Service & Enum untuk Notifikasi
use App\Services\NotificationService;
use App\Enums\NotificationType;

class PengumumanController extends Controller
{
    /**
     * 1. LIST PENGUMUMAN (API)
     * Endpoint ini dipanggil via AJAX (fetch) oleh halaman Staf & Penilai
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Logika: Tampilkan pengumuman yang ditujukan untuk Unit Kerja saya, 
        // ATAU pengumuman Global (yang unit_kerja_id-nya NULL)
        $query = Pengumuman::with('creator')
            ->where(function($q) use ($user) {
                $q->where('unit_kerja_id', $user->unit_kerja_id)
                  ->orWhereNull('unit_kerja_id'); // Global
            });

        // Ambil 5 data terbaru (bisa disesuaikan)
        $data = $query->latest()->paginate(5);

        return response()->json($data);
    }

    /**
     * 2. CREATE PENGUMUMAN (API)
     * Hanya bisa diakses oleh Role tertentu (selain Staf biasa)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validasi Akses (Logic: Jika hanya punya role 'Pegawai', tolak)
        // Note: Logic ini bisa dipindah ke Middleware/Policy di masa depan
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

        // 1. Simpan ke Database
        $pengumuman = Pengumuman::create([
            'user_id_creator' => $user->id,
            'judul' => $request->judul,
            'isi_pengumuman' => $request->isi_pengumuman,
            'unit_kerja_id' => $request->unit_kerja_id,
        ]);
        
        // 2. Trigger Notifikasi (The Magic Happens Here)
        $this->dispatchNotification($pengumuman, $user);

        return response()->json([
            'message' => 'Pengumuman berhasil dibuat dan disebarkan',
            'data' => $pengumuman
        ], 201);
    }

    /**
     * 3. DELETE PENGUMUMAN (API)
     */
    public function destroy($id)
    {
        // Pastikan hanya menghapus milik sendiri
        $pengumuman = Pengumuman::where('user_id_creator', Auth::id())->find($id);
        
        if (!$pengumuman) {
            return response()->json(['message' => 'Pengumuman tidak ditemukan'], 404);
        }
        
        $pengumuman->delete();
        return response()->json(['message' => 'Pengumuman dihapus']);
    }

    /**
     * Helper Private: Mengirim Notifikasi ke Target Audience
     */
    private function dispatchNotification($pengumuman, $sender)
    {
        $type = NotificationType::PENGUMUMAN->value; // 'pengumuman'
        $message = "📢 Pengumuman: " . $pengumuman->judul;

        if ($pengumuman->unit_kerja_id) {
            // SKENARIO A: Broadcast ke Unit Kerja Spesifik
            // Menggunakan method sakti yang sudah ada di Service
            NotificationService::broadcastToUnit(
                $pengumuman->unit_kerja_id,
                $type,
                $message,
                $pengumuman // Polymorph relation
            );
        } else {
            // SKENARIO B: Broadcast Global (Semua Pegawai)
            // Kita ambil semua User ID kecuali si pembuat pengumuman
            $recipientIds = User::where('id', '!=', $sender->id)
                                ->pluck('id'); 

            $payload = [];
            $now = now();

            foreach ($recipientIds as $uid) {
                $payload[] = [
                    'user_id_recipient' => $uid,
                    'tipe_notifikasi'   => $type,
                    'pesan'             => $message,
                    'related_id'        => $pengumuman->id,
                    'related_type'      => get_class($pengumuman),
                    'is_read'           => 0, // false
                    'created_at'        => $now,
                    'updated_at'        => $now
                ];
            }

            // Kirim secara massal (Batch Insert) agar cepat
            NotificationService::sendBatch($payload);
        }
    }
}