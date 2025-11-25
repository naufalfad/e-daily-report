<?php

namespace App\Observers;

use App\Models\LaporanHarian;
use App\Models\ActivityLog;
// use App\Services\NotificationService; // Import Service
// use App\Enums\NotificationType;       // Import Enum
use Illuminate\Support\Facades\Auth;

class LaporanHarianObserver
{
    /**
     * Menangani event "created" (dibuat).
     * Skenario: Pegawai membuat LKH -> Notifikasi ke Atasan.
     */
    public function created(LaporanHarian $laporanHarian)
    {
        $actorId = Auth::id(); 
        
        // 1. Catat Log Aktivitas
        ActivityLog::create([
            'user_id' => $actorId,
            'deskripsi_aktivitas' => 'Membuat LKH baru (ID: ' . $laporanHarian->id . ')'
        ]);

        // 2. Kirim Notifikasi ke Atasan (Validator)
        // Pastikan model LaporanHarian memiliki kolom 'atasan_id' sesuai migrasi terakhir
        // if ($laporanHarian->atasan_id) {
        //     NotificationService::send(
        //         $laporanHarian->atasan_id,
        //         NotificationType::LKH_NEW_SUBMISSION->value,
        //         "Bawahan Anda (" . $laporanHarian->user->name . ") mengajukan LKH baru.",
        //         $laporanHarian // Polymorphic related object
        //     );
        // }
    }

    /**
     * Menangani event "updated" (diperbarui).
     * Skenario 1: Atasan merubah status (Approved/Rejected) -> Notifikasi ke Pegawai.
     * Skenario 2: Pegawai revisi (Rejected -> Pending) -> Notifikasi ke Atasan.
     */
    public function updated(LaporanHarian $laporanHarian)
    {
        $actorId = Auth::id();
        $ownerName = $laporanHarian->user->name;
        
        // Deteksi perubahan kolom 'status'
        if ($laporanHarian->isDirty('status')) {
            $oldStatus = $laporanHarian->getOriginal('status');
            $newStatus = $laporanHarian->status;
            
            $deskripsiLog = '';

            // --- LOGIKA VALIDASI ATASAN (Flow Downstream) ---
            if ($newStatus == 'approved') {
                $deskripsiLog = "Menyetujui (ACC) LKH milik {$ownerName}.";
                
                // // Notifikasi ke Pemilik LKH (Pegawai)
                // NotificationService::send(
                //     $laporanHarian->user_id,
                //     NotificationType::LKH_APPROVED->value,
                //     "Selamat! LKH tanggal " . $laporanHarian->tanggal . " telah disetujui.",
                //     $laporanHarian
                // );

            } elseif ($newStatus == 'rejected') {
                $deskripsiLog = "Menolak LKH milik {$ownerName}.";

                // // Notifikasi ke Pemilik LKH (Pegawai)
                // NotificationService::send(
                //     $laporanHarian->user_id,
                //     NotificationType::LKH_REJECTED->value,
                //     "LKH Anda ditolak. Alasan: " . ($laporanHarian->komentar_validasi ?? '-'),
                //     $laporanHarian
                // );
            
            // --- LOGIKA REVISI PEGAWAI (Flow Upstream) ---
            } elseif ($oldStatus == 'rejected' && ($newStatus == 'pending' || $newStatus == 'draft')) {
                $deskripsiLog = "Mengajukan ulang LKH yang ditolak.";
                $actorId = $laporanHarian->user_id; // Actor adalah pegawai

                // // Notifikasi ke Atasan bahwa ada revisi masuk
                // if ($laporanHarian->atasan_id) {
                //     NotificationService::send(
                //         $laporanHarian->atasan_id,
                //         NotificationType::LKH_UPDATE_SUBMISSION->value,
                //         "{$ownerName} telah memperbaiki LKH yang sebelumnya ditolak.",
                //         $laporanHarian
                //     );
                // }
            } else {
                $deskripsiLog = "Mengubah status LKH dari '{$oldStatus}' menjadi '{$newStatus}'.";
            }
            
            ActivityLog::create([
                'user_id' => $actorId,
                'deskripsi_aktivitas' => $deskripsiLog
            ]);
        } 
        
        // Deteksi perubahan konten (selain status) oleh Pegawai
        else if ($laporanHarian->isDirty()) {
            ActivityLog::create([
                'user_id' => $actorId,
                'deskripsi_aktivitas' => 'Mengedit isi LKH (ID: ' . $laporanHarian->id . ').'
            ]);
        }
    }

    /**
     * Menangani event "deleted".
     */
    public function deleted(LaporanHarian $laporanHarian)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'deskripsi_aktivitas' => 'Menghapus LKH milik ' . $laporanHarian->user->name
        ]);
        
        // Opsional: Hapus notifikasi terkait jika data masternya dihapus (Cleanup)
        // \App\Models\Notifikasi::where('related_id', $laporanHarian->id)
        //    ->where('related_type', get_class($laporanHarian))
        //    ->delete();
    }
}