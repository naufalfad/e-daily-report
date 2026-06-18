<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Analisis Logic:
         * - Jabatan adalah master data FLAT (tidak hierarkis)
         * - Relasi struktural ditangani oleh tabel bidang
         */
        $jabatans = [
            // Jabatan Struktural Utama
            'Kepala Badan',
            'Sekretaris',
            
            // Jabatan Struktural Bidang/Bagian
            'Kepala Bidang',
            'Plt. Kepala Bidang',
            'Kepala Sub Bagian',
            'Plt. Kepala Sub Bagian',
            'Kepala Sub Bidang',
            'Plt. Kepala Sub Bidang',

            // Jabatan Fungsional / Pelaksana
            'Pelaksana',
            'Staf Pelaksana',
            'Pranata Komputer Ahli Pertama',

            // Jabatan Bendahara
            'Bendahara Pengeluaran',
            'Bendahara Penerima',
            'Pembantu Bendahara Penerima',
            'Bendahara Barang',

            // Status Kepegawaian Khusus (Opsional jika masih dipakai)
            'CPNS',
            'PPPK',
        ];

        foreach ($jabatans as $namaJabatan) {
            Jabatan::firstOrCreate(
                [
                    'nama_jabatan' => $namaJabatan,
                ],
                [
                    'unit_kerja_id' => 1,
                ]
            );
        }

        $this->command->info('✅ JabatanSeeder BAPENDA berhasil dijalankan.');
    }
}
