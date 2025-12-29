<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Models\LaporanHarian;
use App\Models\Notifikasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * [BARU] Method khusus untuk notifikasi ke Atasan LKH
     * Dipanggil oleh SubmitLkhAction
     */
    public function notifyAtasan(LaporanHarian $lkh)
    {
        // 1. Cek apakah ada atasan (jika null, skip)
        if (!$lkh->atasan_id) {
            return;
        }

        // 2. Load data user pengirim agar nama tampil di pesan
        // (Menggunakan lazy loading jika relasi belum ter-load)
        $namaPegawai = $lkh->user->name ?? 'Pegawai';
        $tanggal = Carbon::parse($lkh->tanggal_laporan)->translatedFormat('d F Y');

        // 3. Susun Pesan
        $message = "Pegawai {$namaPegawai} mengajukan LKH baru tanggal {$tanggal}.";

        // 4. Kirim menggunakan method static send()
        self::send(
            $lkh->atasan_id,
            NotificationType::LKH_NEW_SUBMISSION->value, // Ambil value string dari Enum
            $message,
            $lkh // Pass object LKH untuk polymorphic relation
        );
    }
}
