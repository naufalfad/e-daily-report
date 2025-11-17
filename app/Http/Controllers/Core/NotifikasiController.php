<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * 1. LIST NOTIFIKASI (Inbox User)
     */
    public function index()
    {
        $userId = Auth::id();

        $notif = Notifikasi::where('user_id_recipient', $userId)
            ->latest()
            ->limit(20) // Ambil 20 terakhir saja agar ringan
            ->get();
            
        // Hitung jumlah yang belum dibaca (untuk badge merah di UI)
        $unreadCount = Notifikasi::where('user_id_recipient', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'data' => $notif
        ]);
    }

    /**
     * 2. MARK AS READ (Tandai Sudah Dibaca)
     * Dipanggil saat user mengklik salah satu notif
     */
    public function markAsRead($id)
    {
        $notif = Notifikasi::where('user_id_recipient', Auth::id())->find($id);

        if ($notif) {
            $notif->update(['is_read' => true]);
        }

        return response()->json(['message' => 'Notifikasi ditandai sudah dibaca']);
    }

    /**
     * 3. MARK ALL READ (Tandai Semua Dibaca)
     */
    public function markAllRead()
    {
        Notifikasi::where('user_id_recipient', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Semua notifikasi ditandai sudah dibaca']);
    }
}