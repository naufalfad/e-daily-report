<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bidang;
use App\Models\UnitKerja;

class StrukturOrganisasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Pastikan Unit Kerja ada
        $unitKerja = UnitKerja::firstOrCreate(
            ['id' => 1],
            ['nama_unit' => 'Badan Pendapatan Daerah Kabupaten Mimika']
        );

        // 2. Struktur Organisasi sesuai Perbup No 3 Tahun 2023
        $struktur = [

            [
                'nama' => 'Sekretariat',
                'level' => 'bidang',
                'childs' => [
                    'Sub Bagian Umum dan Kepegawaian',
                    'Sub Bagian Keuangan',
                    'Sub Bagian Program',
                ]
            ],

            [
                'nama' => 'Bidang Pajak',
                'level' => 'bidang',
                'childs' => [
                    'Sub Bidang Pendataan dan Pendaftaran Pajak',
                    'Sub Bidang Perhitungan dan Penetapan Pajak Daerah',
                    'Sub Bidang Pemeriksaan Pajak, Konsultasi, Keberatan dan Banding',
                ]
            ],

            [
                'nama' => 'Bidang Perencanaan dan Pengembangan Pendapatan Daerah',
                'level' => 'bidang',
                'childs' => [
                    'Sub Bidang Regulasi Pendapatan Daerah',
                    'Sub Bidang Retribusi dan Evaluasi Pendapatan Daerah',
                    'Sub Bidang Pengembangan Sistem Informatika dan Inovasi Pendapatan Daerah',
                ]
            ],

            [
                'nama' => 'Bidang PBB dan BPHTB',
                'level' => 'bidang',
                'childs' => [
                    'Sub Bidang Pendataan dan Pendaftaran PBB dan BPHTB',
                    'Sub Bidang Penilaian dan Penetapan PBB dan BPHTB',
                    'Sub Bidang Penagihan Restitusi PBB dan BPHTB',
                ]
            ],

            [
                'nama' => 'Bidang Pembukuan dan Pelaporan',
                'level' => 'bidang',
                'childs' => [
                    'Sub Bidang Pembukuan dan Pelaporan',
                    'Sub Bidang Pemeriksaan dan Verifikasi',
                    'Sub Bidang Penagihan',
                ]
            ],

            [
                'nama' => 'Kelompok Jabatan Fungsional',
                'level' => 'bidang',
                'childs' => []
            ],
        ];

        // 3. Eksekusi Seeder
        foreach ($struktur as $parentData) {

            $parent = Bidang::firstOrCreate(
                [
                    'nama_bidang'   => $parentData['nama'],
                    'unit_kerja_id' => $unitKerja->id
                ],
                [
                    'level'     => $parentData['level'],
                    'parent_id' => null
                ]
            );

            $this->command->info("Created: {$parent->nama_bidang}");

            foreach ($parentData['childs'] as $childName) {
                Bidang::firstOrCreate(
                    [
                        'nama_bidang'   => $childName,
                        'unit_kerja_id' => $unitKerja->id
                    ],
                    [
                        'level'     => 'sub_bidang',
                        'parent_id' => $parent->id
                    ]
                );
            }
        }

        $this->command->info('StrukturOrganisasiSeeder berhasil dijalankan sesuai Perbup.');
    }
}
