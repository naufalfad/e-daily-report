<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\LaporanHarian;
use App\Services\NotificationService;
use Carbon\Carbon;

class SendLkhReminder extends Command
{
    // Nama perintah yang nanti dijalankan scheduler
    protected $signature = 'lkh:send-reminder';
    protected $description = 'Kirim notifikasi pengingat ke pegawai yang belum isi LKH hari ini';

    public function handle()
    {
        $today = Carbon::now()->format('Y-m-d');
        
        // 1. Ambil ID semua pegawai yang SUDAH lapor hari ini
        $usersAlreadySubmitted = LaporanHarian::whereDate('tanggal_laporan', $today)
            ->pluck('user_id')
            ->unique();

        // 2. Ambil Pegawai (Role Pegawai) yang BELUM ada di list atas
        $targetUsers = User::whereHas('roles', function($q) {
                $q->where('nama_role', 'Pegawai');
            })
            ->whereNotIn('id', $usersAlreadySubmitted)
            ->get();

        $count = 0;
        foreach ($targetUsers as $user) {
            // Kirim Notif Pakai Service yang sudah kita buat
            NotificationService::send(
                $user->id,
                'reminder_lkh', // Pastikan enum ini ada di DB
                'Halo ' . $user->name . ', jangan lupa isi Laporan Kinerja Harian untuk tanggal ' . $today . ' sebelum pulang ya!',
                null
            );
            $count++;
        }

        $this->info("Pengingat berhasil dikirim ke $count pegawai.");
    }
}