<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SkpRencana;
use App\Models\SkpTarget;
use App\Models\Tupoksi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SkpSimulationSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Memulai simulasi data SKP sesuai skema PGSQL...');

        $users = User::whereNotNull('atasan_id')->get();

        DB::beginTransaction();
        try {
            foreach ($users as $user) {
                // Buat Header Rencana SKP sesuai kolom di DB Anda
                $rencana = SkpRencana::create([
                    'user_id'             => $user->id,
                    'periode_awal'        => '2026-01-01',
                    'periode_akhir'       => '2026-12-31',
                    'rhk_intervensi'      => 'Meningkatkan kualitas pelayanan pajak dan retribusi daerah',
                    'rencana_hasil_kerja' => 'Terlaksananya tugas kedinasan sesuai dengan tupoksi ' . ($user->bidang->nama_bidang ?? 'Unit Kerja'),
                ]);

                // Ambil Tupoksi bidang user
                $tupoksis = Tupoksi::where('bidang_id', $user->bidang_id)->get();

                if ($tupoksis->isEmpty()) {
                    // Default jika tidak ada tupoksi
                    SkpTarget::create([
                        'skp_rencana_id'   => $rencana->id,
                        'uraian_tugas'     => 'Melaksanakan koordinasi teknis operasional harian',
                        'target_kuantitas' => 12,
                        'satuan'           => 'Laporan',
                        'target_kualitas'  => 100,
                        'waktu'            => 12,
                        'satuan_waktu'     => 'Bulan',
                    ]);
                } else {
                    foreach ($tupoksis as $tupoksi) {
                        SkpTarget::create([
                            'skp_rencana_id'   => $rencana->id,
                            'uraian_tugas'     => $tupoksi->uraian,
                            'target_kuantitas' => rand(10, 50),
                            'satuan'           => 'Dokumen',
                            'target_kualitas'  => 100,
                            'waktu'            => 12,
                            'satuan_waktu'     => 'Bulan',
                        ]);
                    }
                }
            }
            DB::commit();
            $this->command->info('SkpSimulationSeeder Berhasil.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal SKP: ' . $e->getMessage());
        }
    }
}