<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */

    protected function schedule(Schedule $schedule): void
    {
        // [BARU] Pasang Jadwal Pengingat Di Sini
        $schedule->command('lkh:send-reminder')
                 ->weekdays() // Hanya Senin-Jumat
                 ->at('16:00') // Jam 4 Sore
                 ->timezone('Asia/Jayapura'); // Waktu Mimika
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Mendaftarkan semua command yang ada di folder Commands
        $this->load(__DIR__.'/Commands');

        // Bisa juga memuat command yang didefinisikan di routes/console.php
        require base_path('routes/console.php');
    }

    protected $commands = [
        // DAFTARKAN DI SINI MANUAL
        \App\Console\Commands\FetchAllWilayah::class,
    ];
}
