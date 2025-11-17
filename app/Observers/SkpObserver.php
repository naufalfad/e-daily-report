<?php

namespace App\Observers;

use App\Models\Skp;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class SkpObserver
{
    /**
     * Menangani event "created" (dibuat) pada Skp.
     */
    public function created(Skp $skp)
    {
        ActivityLog::create([
            'user_id' => $skp->user_id, // Aktor adalah pemilik SKP
            'deskripsi_aktivitas' => 'Membuat SKP baru (ID: ' . $skp->id . ') dengan nama: "' . $skp->nama_skp . '"'
        ]);
    }

    /**
     * Menangani event "updated" (diperbarui) pada Skp.
     */
    public function updated(Skp $skp)
    {
        ActivityLog::create([
            'user_id' => $skp->user_id, // Aktor adalah pemilik SKP
            'deskripsi_aktivitas' => 'Mengedit SKP (ID: ' . $skp->id . '). Nama baru: "' . $skp->nama_skp . '"'
        ]);
    }

    /**
     * Menangani event "deleted" (dihapus) pada Skp.
     */
    public function deleted(Skp $skp)
    {
        ActivityLog::create([
            'user_id' => Auth::id(), // Bisa jadi pemilik SKP atau Admin yang menghapus
            'deskripsi_aktivitas' => 'Menghapus SKP (ID: ' . $skp->id . ') dengan nama: "' . $skp->nama_skp . '"'
        ]);
    }
}