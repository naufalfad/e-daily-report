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
         * - Jabatan dinormalisasi dari teks mentah jabatan pegawai
         * - firstOrCreate dipakai agar aman di-run berulang
         */
        $jabatans = [
            // Jabatan Struktural
            'Kepala Badan',
            'Kepala Bidang',
            'Kepala Sub Bagian',
            'Kepala Sub Bidang',

            // Jabatan Fungsional / Pelaksana
            'Staf Pelaksana',

            // Jabatan Bendahara
            'Bendahara Pengeluaran',
            'Bendahara Penerima',
            'Pembantu Bendahara Penerima',
            'Bendahara Barang',

            // Status Kepegawaian Khusus
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
