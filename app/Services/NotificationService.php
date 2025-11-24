<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model; // Import Model

class NotificationService
{
    /**
     * Helper Private untuk mengekstrak ID dan Type
     * Mengembalikan array [id, type]
     */
    private static function parseRelated($related = null)
    {
        if ($related instanceof Model) {
            return [
                'id'   => $related->getKey(),
                'type' => $related->getMorphClass() // Otomatis: 'App\Models\LaporanHarian'
            ];
        }

        // Jika manual input ID dan Type (Array atau null)
        if (is_array($related)) {
             return [
                 'id'   => $related['id'] ?? null,
                 'type' => $related['type'] ?? null
             ];
        }

        return ['id' => null, 'type' => null];
    }

    /**
     * Kirim Notifikasi (Single) - Updated for Polymorph
     * $related bisa berupa Object Model atau Array ['id' => 1, 'type' => '...']
     */
    public static function send($recipientId, $type, $message, $related = null)
    {
        $parsed = self::parseRelated($related);

        Notifikasi::create([
            'user_id_recipient' => $recipientId,
            'tipe_notifikasi'   => $type,
            'pesan'             => $message,
            'related_id'        => $parsed['id'],
            'related_type'      => $parsed['type'],
            'is_read'           => false
        ]);
    }

    /**
     * Batch Insert (Array Processing)
     * Pastikan array input sudah memiliki key 'related_type'
     */
    public static function sendBatch(array $notificationsData)
    {
        if (empty($notificationsData)) return;

        foreach (array_chunk($notificationsData, 1000) as $chunk) {
            Notifikasi::insert($chunk);
        }
    }

    /**
     * Broadcast Unit - Updated for Polymorph
     */
    public static function broadcastToUnit($unitId, $type, $message, $related = null)
    {
        $users = User::where('unit_kerja_id', $unitId)->pluck('id');
        if ($users->isEmpty()) return;

        $parsed = self::parseRelated($related); // Parse sekali di luar loop
        
        $payload = [];
        $timestamp = Carbon::now();

        foreach ($users as $uid) {
            $payload[] = [
                'user_id_recipient' => $uid,
                'tipe_notifikasi'   => $type,
                'pesan'             => $message,
                'related_id'        => $parsed['id'],
                'related_type'      => $parsed['type'], // Field baru
                'is_read'           => 0,
                'created_at'        => $timestamp,
                'updated_at'        => $timestamp,
            ];
        }

        self::sendBatch($payload);
    }
}