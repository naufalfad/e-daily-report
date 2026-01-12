<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;
use App\Models\UnitKerja;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* * Analisis Logic:
         * Urutan insert tidak terlalu berpengaruh karena ini master data flat,
         * tapi kita urutkan berdasarkan hierarki umum untuk kerapian data ID.
         */
        $jabatans = [
            'Kepala Badan',           // Eselon II
            'Sekretaris',             // Eselon III (Sekretariat)
            'Kepala Bidang',          // Eselon III (Bidang Teknis)
            'Kepala Sub Bagian',      // Eselon IV (Di bawah Sekretaris)
            'Kepala Sub Bidang',      // Eselon IV (Di bawah Kabid)
            'Administrator Sistem',   // Jabatan Fungsional Tertentu (JFT) / Khusus IT
            'Staf Pelaksana',         // Pelaksana Umum (JFU)
        ];

        foreach ($jabatans as $namaJabatan) {
            Jabatan::firstOrCreate(
                ['nama_jabatan' => $namaJabatan],
                ['unit_kerja_id' => 1] 
            );
        }

        $this->command->info('JabatanSeeder berhasil dijalankan.');
    }
}