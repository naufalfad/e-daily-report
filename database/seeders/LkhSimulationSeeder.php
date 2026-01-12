<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LaporanHarian;
use App\Models\LkhBukti;
use App\Models\SkpTarget;
use App\Models\Tupoksi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class LkhSimulationSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');
        $this->command->info('Memulai simulasi LKH Realistis (Fixed Schema PGSQL)...');

        // 1. Identifikasi User kecuali Kepala Badan
        // Mengambil user yang memiliki atasan (karena staf/pejabat struktural harus lapor ke atasan)
        $users = User::whereNotNull('atasan_id')
                     ->where('is_active', true)
                     ->get();

        if ($users->isEmpty()) {
            $this->command->error('Tidak ada user ditemukan. Pastikan UserSeeder sudah dijalankan!');
            return;
        }

        DB::beginTransaction();

        try {
            foreach ($users as $user) {
                // Minimal 2 laporan per user sesuai request
                $jumlahLaporan = rand(2, 3); 

                for ($i = 0; $i < $jumlahLaporan; $i++) {
                    // A. Tentukan Isi Kegiatan (Logic Prioritas: SKP Target -> Tupoksi -> Generic)
                    $aktivitas = $this->getContentForLkh($user, $faker);
                    
                    // B. Koordinat Random Area Mimika/Papua Tengah
                    $lat = -4.546 + ($faker->randomFloat(6, -0.05, 0.05)); 
                    $long = 136.88 + ($faker->randomFloat(6, -0.05, 0.05));

                    // C. Create LKH dengan penamaan kolom sesuai DB riil Anda
                    $lkh = LaporanHarian::create([
                        'user_id'             => $user->id,
                        'atasan_id'           => $user->atasan_id,
                        'tanggal_laporan'     => Carbon::now()->subDays(rand(1, 5))->format('Y-m-d'),
                        'waktu_mulai'         => '08:00:00',
                        'waktu_selesai'       => '16:00:00',
                        'deskripsi_aktivitas' => $aktivitas['deskripsi'],
                        'output_hasil_kerja'  => $aktivitas['output'],
                        'status'              => 'waiting_review',
                        'jenis_kegiatan'      => $faker->randomElement(['Tupoksi', 'Rapat', 'Pelayanan Publik']),
                        'volume'              => rand(1, 5),
                        'satuan'              => $aktivitas['satuan'],
                        'skp_rencana_id'      => $aktivitas['skp_id'],
                        'tupoksi_id'          => $aktivitas['tupoksi_id'],
                        'mode_lokasi'         => 'geofence',
                        'location_provider'   => 'gps_device',
                        'lokasi_teks'         => 'Kabupaten Mimika, Papua Tengah',
                        'is_luar_lokasi'      => false,
                        // Gunakan Raw SQL untuk tipe data Geometry di PostgreSQL
                        'lokasi'              => DB::raw("ST_GeomFromText('POINT($long $lat)', 4326)"),
                    ]);

                    // D. Create Bukti Dukung (Dummy)
                    LkhBukti::create([
                        'laporan_harian_id' => $lkh->id,
                        'file_path'         => 'dummy/bukti_' . rand(1, 5) . '.jpg',
                        'nama_file'         => 'dokumentasi_kegiatan.jpg',
                        'tipe_file'         => 'image/jpeg'
                    ]);
                }
                
                $this->command->info("LKH dikirim untuk: {$user->name} -> Validator: User ID {$user->atasan_id}");
            }

            DB::commit();
            $this->command->info('Seeder LKH selesai! Dashboard atasan kini terisi data antrean validasi.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error seeding LKH: " . $e->getMessage());
        }
    }

    /**
     * Logic cerdas pengambilan konten laporan
     */
    private function getContentForLkh($user, $faker)
    {
        // 1. Cek SKP Target (Join ke skp_rencana karena tabel ini yang punya user_id)
        $target = SkpTarget::whereHas('rencana', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->inRandomOrder()->first();

        if ($target) {
            return [
                'deskripsi'  => "Melaksanakan tahapan: " . $target->uraian_tugas,
                'output'     => "Tersedianya " . rand(1, 3) . " " . $target->satuan,
                'satuan'     => $target->satuan,
                'skp_id'     => $target->skp_rencana_id,
                'tupoksi_id' => null
            ];
        }

        // 2. Cek Tupoksi Bidang
        if ($user->bidang_id) {
            $tupoksi = Tupoksi::where('bidang_id', $user->bidang_id)->inRandomOrder()->first();
            if ($tupoksi) {
                return [
                    'deskripsi'  => "Koordinasi pelaksanaan " . $tupoksi->uraian,
                    'output'     => "1 Laporan Kegiatan",
                    'satuan'     => "Laporan",
                    'skp_id'     => null,
                    'tupoksi_id' => $tupoksi->id
                ];
            }
        }

        // 3. Fallback Generic
        return [
            'deskripsi'  => $faker->randomElement(['Rekapitulasi data harian pendapatan', 'Koordinasi internal bidang']),
            'output'     => "1 Berkas",
            'satuan'     => "Berkas",
            'skp_id'     => null,
            'tupoksi_id' => null
        ];
    }
}