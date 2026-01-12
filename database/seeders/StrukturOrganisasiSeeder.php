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
        // 1. Pastikan Unit Kerja ada (Fallback jika UnitKerjaSeeder belum jalan)
        $unitKerja = UnitKerja::firstOrCreate(
            ['id' => 1], // Kita kunci ID 1 sesuai request
            ['nama_unit' => 'Badan Pendapatan Daerah Kabupaten Timika']
        );

        // 2. Definisi Struktur Sesuai Perbup No 3 Tahun 2023
        $struktur = [
            [
                'nama' => 'Sekretariat',
                'level' => 'bidang',
                'childs' => [
                    'Sub Bagian Umum dan Kepegawaian',
                    'Sub Bagian Perencanaan dan Keuangan',
                ]
            ],
            [
                'nama' => 'Bidang Pendataan dan Pendaftaran',
                'level' => 'bidang',
                'childs' => [
                    'Sub Bidang Pendataan dan Pendaftaran Pajak Daerah',
                    'Sub Bidang Pendataan dan Pendaftaran PBB P2 dan BPHTB',
                    'Sub Bidang Pengolahan Data dan Sistem Informasi',
                ]
            ],
            [
                'nama' => 'Bidang Penetapan Pajak Daerah',
                'level' => 'bidang',
                'childs' => [
                    'Sub Bidang Perhitungan dan Penetapan Pajak Daerah',
                    'Sub Bidang Penilaian dan Penetapan PBB P2 dan BPHTB',
                    'Sub Bidang Keberatan dan Banding',
                ]
            ],
            [
                'nama' => 'Bidang Penagihan dan Pengawasan',
                'level' => 'bidang',
                'childs' => [
                    'Sub Bidang Penagihan dan Restitusi Pajak Daerah',
                    'Sub Bidang Penagihan dan Restitusi PBB dan BPHTB',
                    'Sub Bidang Pemeriksaan dan Pengawasan Pajak',
                ]
            ],
            [
                'nama' => 'Bidang Perencanaan dan Pelaporan',
                'level' => 'bidang', // Sesuai nomenklatur umum, kadang disebut Bidang Pembukuan & Pelaporan di dokumen lain, saya pakai Perbup 
                'childs' => [
                    'Sub Bidang Regulasi Pendapatan Daerah',
                    'Sub Bidang Retribusi dan Evaluasi Pendapatan Daerah',
                    'Sub Bidang Pembukuan dan Pelaporan',
                ]
            ],
            // Tambahan Khusus untuk UPT jika diperlukan (Optional sesuai Perbup)
            [
                'nama' => 'UPT Badan',
                'level' => 'bidang',
                'childs' => [] // Isi jika ada rincian UPT
            ],
             // Kelompok Jabatan Fungsional biasanya setara bidang dalam struktur administratif tertentu
            [
                'nama' => 'Kelompok Jabatan Fungsional', 
                'level' => 'bidang',
                'childs' => [] 
            ]
        ];

        // 3. Eksekusi Insert Data
        foreach ($struktur as $parentData) {
            // Create Parent (Bidang/Sekretariat)
            $parent = Bidang::firstOrCreate(
                ['nama_bidang' => $parentData['nama'], 'unit_kerja_id' => $unitKerja->id],
                [
                    'level' => $parentData['level'],
                    'parent_id' => null // Parent paling atas tidak punya induk di tabel bidang
                ]
            );

            $this->command->info("Created Parent: " . $parent->nama_bidang);

            // Create Children (Sub Bidang)
            foreach ($parentData['childs'] as $childName) {
                Bidang::firstOrCreate(
                    ['nama_bidang' => $childName, 'unit_kerja_id' => $unitKerja->id],
                    [
                        'level' => 'sub_bidang', // Sesuai instruksi
                        'parent_id' => $parent->id // Logical link ke ID parent yang baru dibuat
                    ]
                );
            }
        }

        $this->command->info('StrukturOrganisasiSeeder berhasil dijalankan sesuai Perbup.');
    }
}