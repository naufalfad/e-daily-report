<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * 1. LIST NOTIFIKASI (Inbox User) - REFACTORED FOR POLYMORPH
     * Mengembalikan daftar notifikasi beserta URL redirect dinamis.
     */
    public function index()
    {
        $userId = Auth::id();

        // Eager Load 'related' agar tidak N+1 Query saat meloop data polymorphic
        $notif = Notifikasi::with('related') 
            ->where('user_id_recipient', $userId)
            ->latest()
            ->limit(20) 
            ->get()
            ->map(function ($item) {
                // Transformasi Data: Menambahkan 'redirect_url' dinamis
                return $this->formatNotification($item);
            });
            
        $unreadCount = Notifikasi::where('user_id_recipient', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'data' => $notif
        ]);
    }

    /**
     * Helper Logic untuk menentukan URL Redirect berdasarkan Tipe Model
     * Ini adalah penerapan Pattern Strategy sederhana.
     */
    private function formatNotification($item)
    {
        $redirectUrl = '#'; // Default jika tidak ada relasi

        if ($item->related_type && $item->related_id) {
            // Logika Routing Dinamis
            // Sesuaikan path ini dengan route frontend Yang Mulia
            switch ($item->related_type) {
                case 'App\Models\LaporanHarian':
                    // Jika user adalah Penilai/Atasan, mungkin redirect ke halaman validasi
                    // Jika user adalah Pegawai, redirect ke halaman detail riwayat
                    $redirectUrl = Auth::user()->hasRole('Atasan') 
                        ? "/penilai/validasi-lkh/{$item->related_id}" 
                        : "/pegawai/riwayat-lkh/{$item->related_id}";
                    break;

                case 'App\Models\Pengumuman':
                    $redirectUrl = "/dashboard/pengumuman/{$item->related_id}";
                    break;
                
                case 'App\Models\Skp':
                    $redirectUrl = "/pegawai/skp/{$item->related_id}";
                    break;
            }
        }

        // Kembalikan object notifikasi asli + field baru
        $item->redirect_url = $redirectUrl;
        
        // Opsional: Bersihkan nama class agar lebih cantik di JSON (misal: "LaporanHarian" saja)
        $item->related_model = class_basename($item->related_type);

        return $item;
    }

    /**
     * 2. MARK AS READ (Tandai Sudah Dibaca)
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