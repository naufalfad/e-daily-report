<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;

class NotificationService
{
    /**
     * Kirim notifikasi ke SATU orang
     */
    public static function send($recipientId, $type, $message, $relatedId = null)
    {
        Notifikasi::create([
            'user_id_recipient' => $recipientId,
            'tipe_notifikasi'   => $type, // enum: lkh_approved, lkh_rejected, dll
            'pesan'             => $message,
            'related_id'        => $relatedId,
            'is_read'           => false
        ]);
    }

    /**
     * Kirim notifikasi ke BANYAK orang (misal: Pengumuman Unit)
     */
    public static function broadcastToUnit($unitId, $type, $message, $relatedId = null)
    {
        // Ambil semua user di unit tersebut
        $users = User::where('unit_kerja_id', $unitId)->pluck('id');

        foreach ($users as $uid) {
            self::send($uid, $type, $message, $relatedId);
        }
    }
}