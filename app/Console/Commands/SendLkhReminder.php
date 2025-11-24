<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\LaporanHarian;
use App\Services\NotificationService;
use Carbon\Carbon;

class SendLkhReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lkh:send-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi pengingat ke pegawai yang belum isi LKH hari ini (Optimized Batch Insert)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now()->format('Y-m-d');
        
        $this->info("Memulai proses pengecekan LKH untuk tanggal $today...");

        // 1. LOGICAL FILTERING: Ambil ID semua pegawai yang SUDAH lapor hari ini
        // Pluck ID saja untuk meminimalisir penggunaan memori
        $usersAlreadySubmitted = LaporanHarian::whereDate('tanggal_laporan', $today)
            ->pluck('user_id')
            ->unique();

        // 2. QUERY OPTIMIZATION: Ambil User Target
        // Filter user yang memiliki role 'Pegawai' DAN ID-nya TIDAK ADA di list yang sudah submit.
        // Kita hanya select 'id' dan 'name' karena field lain tidak dibutuhkan untuk notifikasi.
        $targetUsers = User::whereHas('roles', function($q) {
                $q->where('nama_role', 'Pegawai');
            })
            ->whereNotIn('id', $usersAlreadySubmitted)
            ->select('id', 'name') 
            ->get();

        if ($targetUsers->isEmpty()) {
            $this->info("Semua pegawai (role: Pegawai) sudah mengisi LKH. Tidak ada notifikasi dikirim.");
            return;
        }

        $count = $targetUsers->count();
        $this->info("Ditemukan $count pegawai belum lapor. Menyiapkan batch insert...");

        // 3. DATA CONSTRUCTION
        $notificationsPayload = [];
        $timestamp = Carbon::now();

        foreach ($targetUsers as $user) {
            $pesan = "Halo {$user->name}, jangan lupa isi Laporan Kinerja Harian untuk tanggal $today sebelum pulang ya!";

            $notificationsPayload[] = [
                'user_id_recipient' => $user->id,
                'tipe_notifikasi'   => 'reminder_lkh', 
                'pesan'             => $pesan,
                'related_id'        => null,
                'related_type'      => null, // WAJIB ADA: Tambahkan kolom ini agar batch insert sukses
                'is_read'           => 0,
                'created_at'        => $timestamp,
                'updated_at'        => $timestamp,
            ];
        }

        NotificationService::sendBatch($notificationsPayload);

        $this->info("SUKSES: Berhasil mengirim $count notifikasi pengingat dalam sekali proses batch!");
    }
}