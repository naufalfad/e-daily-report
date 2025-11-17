<?php

namespace App\Observers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Menangani event "created" (dibuat) pada User.
     */
    public function created(User $user)
    {
        $actorId = Auth::id(); // Admin yang membuat
        
        // Jangan catat log jika user dibuat via Seeder (karena $actorId = null)
        if ($actorId) {
            ActivityLog::create([
                'user_id' => $actorId,
                'deskripsi_aktivitas' => 'Membuat user baru: ' . $user->name . ' (NIP: ' . $user->nip . ')'
            ]);
        }
    }

    /**
     * Menangani event "updated" (diperbarui) pada User.
     */
    public function updated(User $user)
    {
        $actorId = Auth::id(); // Admin yang mengedit atau user itu sendiri (edit profil)
        
        if (!$actorId) return; // Jangan catat log dari seeder/system

        // Log jika password diubah
        if ($user->isDirty('password')) {
            ActivityLog::create([
                'user_id' => $actorId,
                'deskripsi_aktivitas' => 'Mengubah password untuk user: ' . $user->name
            ]);
        }
        
        // Log jika data lain diubah (Contoh: NIP, Nama, Role, Jabatan)
        // Kita hanya buat 1 log umum agar tidak spam
        $dirtyFields = $user->getDirty();
        unset($dirtyFields['password']); // Hapus password dari daftar
        unset($dirtyFields['remember_token']);
        unset($dirtyFields['updated_at']);

        if (count($dirtyFields) > 0) {
            ActivityLog::create([
                'user_id' => $actorId,
                'deskripsi_aktivitas' => 'Mengedit data profil user: ' . $user->name . '. (Perubahan: ' . implode(', ', array_keys($dirtyFields)) . ')'
            ]);
        }
    }

    /**
     * Menangani event "deleted" (dihapus) pada User.
     */
    public function deleted(User $user)
    {
        $actorId = Auth::id(); // Admin yang menghapus
        
        if ($actorId) {
            ActivityLog::create([
                'user_id' => $actorId,
                'deskripsi_aktivitas' => 'Menghapus user: ' . $user->name . ' (NIP: ' . $user->nip . ')'
            ]);
        }
    }
}