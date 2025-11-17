<?php

namespace App\Observers;

use App\Models\LaporanHarian;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LaporanHarianObserver
{
    /**
     * Menangani event "created" (dibuat) pada LaporanHarian.
     *
     * @param  \App\Models\LaporanHarian  $laporanHarian
     * @return void
     */
    public function created(LaporanHarian $laporanHarian)
    {
        // Mencatat siapa yang sedang login saat membuat LKH
        $actorId = Auth::id(); 
        
        ActivityLog::create([
            'user_id' => $actorId,
            'deskripsi_aktivitas' => 'Membuat LKH baru (ID: ' . $laporanHarian->id . ') dengan deskripsi: "' . $laporanHarian->deskripsi_aktivitas . '"'
        ]);
    }

    /**
     * Menangani event "updated" (diperbarui) pada LaporanHarian.
     *
     * @param  \App\Models\LaporanHarian  $laporanHarian
     * @return void
     */
    public function updated(LaporanHarian $laporanHarian)
    {
        $actorId = Auth::id(); // Ini adalah atasan (Validator) atau pegawai itu sendiri
        $ownerName = $laporanHarian->user->name; // Nama pemilik LKH

        // 1. Cek jika STATUS yang berubah (Validasi Atasan)
        if ($laporanHarian->isDirty('status')) {
            $oldStatus = $laporanHarian->getOriginal('status');
            $newStatus = $laporanHarian->status;
            
            $deskripsi = '';

            if ($newStatus == 'approved') {
                $deskripsi = "Menyetujui (ACC) LKH milik {$ownerName} (ID LKH: {$laporanHarian->id}).";
            } elseif ($newStatus == 'rejected') {
                $deskripsi = "Menolak LKH milik {$ownerName} (ID LKH: {$laporanHarian->id}). Alasan: " . $laporanHarian->komentar_validasi;
            } elseif ($oldStatus == 'rejected' && ($newStatus == 'pending' || $newStatus == 'draft')) {
                // Pegawai mengajukan ulang laporan yang ditolak
                $deskripsi = "Mengajukan ulang LKH yang ditolak (ID LKH: {$laporanHarian->id}).";
                $actorId = $laporanHarian->user_id; // Pastikan aktornya adalah si pegawai
            } else {
                // Perubahan status lainnya
                $deskripsi = "Mengubah status LKH milik {$ownerName} dari '{$oldStatus}' menjadi '{$newStatus}'.";
            }
            
            ActivityLog::create([
                'user_id' => $actorId,
                'deskripsi_aktivitas' => $deskripsi
            ]);

        } 
        // 2. Cek jika ada data lain yang di-edit (misal, pegawai edit deskripsi)
        else if ($laporanHarian->isDirty()) {
            ActivityLog::create([
                'user_id' => $actorId,
                'deskripsi_aktivitas' => 'Mengedit LKH (ID: ' . $laporanHarian->id . '). Deskripsi baru: "' . $laporanHarian->deskripsi_aktivitas . '"'
            ]);
        }
    }

    /**
     * Menangani event "deleted" (dihapus) pada LaporanHarian.
     *
     * @param  \App\Models\LaporanHarian  $laporanHarian
     * @return void
     */
    public function deleted(LaporanHarian $laporanHarian)
    {
        ActivityLog::create([
            'user_id' => Auth::id(), // Siapa yang menghapus
            'deskripsi_aktivitas' => 'Menghapus LKH (ID: ' . $laporanHarian->id . ') milik ' . $laporanHarian->user->name
        ]);
    }
}