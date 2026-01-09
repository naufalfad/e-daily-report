<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bidang; // Pastikan Model Bidang diimport
use App\Models\UnitKerja;
use Illuminate\Support\Facades\DB;

class BapendaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Pastikan ada Root Unit Kerja (Badan)
        $root = UnitKerja::firstOrCreate(
            ['nama_unit' => 'Badan Pendapatan Daerah'],
            ['jenis_unit' => 'Badan', 'alamat' => 'Jl. Yos Sudarso, Timika']
        );

        // --- MAPPING STRUKTUR ORGANISASI (Sesuai PDF Perbup No 3 Th 2023) ---

        // 1. SEKRETARIAT (Level Induk)
        $sekretariat = Bidang::firstOrCreate(
            ['nama_bidang' => 'Sekretariat'],
            ['unit_kerja_id' => $root->id, 'parent_id' => null, 'level' => 'bidang'] // Level Bidang
        );
            // 1.1 Sub Bagian di bawah Sekretariat (Opsional, jika ada di PDF)
            $this->createSubBidang($sekretariat, 'Sub Bagian Umum dan Kepegawaian');
            $this->createSubBidang($sekretariat, 'Sub Bagian Perencanaan dan Keuangan');


        // 2. BIDANG PAJAK DAERAH (Level Induk)
        $bidangPajak = Bidang::firstOrCreate(
            ['nama_bidang' => 'Bidang Pajak Daerah'],
            ['unit_kerja_id' => $root->id, 'parent_id' => null, 'level' => 'bidang']
        );
            // 2.1 Anak-anak dari Bidang Pajak Daerah
            $this->createSubBidang($bidangPajak, 'Sub Bidang Pendataan dan Pendaftaran Pajak');
            $this->createSubBidang($bidangPajak, 'Sub Bidang Perhitungan dan Penetapan Pajak Daerah');
            $this->createSubBidang($bidangPajak, 'Sub Bidang Pemeriksaan Pajak, Konsultasi Keberatan, dan Banding');


        // 3. BIDANG PBB-P2 DAN BPHTB (Level Induk)
        $bidangPbb = Bidang::firstOrCreate(
            ['nama_bidang' => 'Bidang PBB-P2 dan BPHTB'],
            ['unit_kerja_id' => $root->id, 'parent_id' => null, 'level' => 'bidang']
        );
            // 3.1 Anak-anak dari Bidang PBB
            $this->createSubBidang($bidangPbb, 'Sub Bidang Pendataan dan Pendaftaran PBB-P2 dan BPHTB');
            $this->createSubBidang($bidangPbb, 'Sub Bidang Penilaian dan Penetapan PBB-P2 dan BPHTB');
            $this->createSubBidang($bidangPbb, 'Sub Bidang Penagihan dan Restitusi PBB-P2 dan BPHTB');


        // 4. BIDANG KOORDINASI, PENGEMBANGAN, EVALUASI DAN IT (Level Induk - Nama disesuaikan PDF Pasal 19)
        $bidangRenbang = Bidang::firstOrCreate(
            ['nama_bidang' => 'Bidang Koordinasi, Pengembangan, Evaluasi dan Sistem Informasi'],
            ['unit_kerja_id' => $root->id, 'parent_id' => null, 'level' => 'bidang']
        );
            // 4.1 Anak-anak dari Bidang Renbang
            $this->createSubBidang($bidangRenbang, 'Sub Bidang Regulasi Pendapatan Daerah');
            $this->createSubBidang($bidangRenbang, 'Sub Bidang Restribusi dan Evaluasi Pendapatan Daerah');
            $this->createSubBidang($bidangRenbang, 'Sub Bidang Pengembangan Sistem Informatika dan Inovasi Pendapatan Daerah');


        // 5. BIDANG PEMBUKUAN DAN PELAPORAN (Level Induk)
        $bidangPembukuan = Bidang::firstOrCreate(
            ['nama_bidang' => 'Bidang Pembukuan dan Pelaporan'],
            ['unit_kerja_id' => $root->id, 'parent_id' => null, 'level' => 'bidang']
        );
            // 5.1 Anak-anak dari Bidang Pembukuan
            $this->createSubBidang($bidangPembukuan, 'Sub Bidang Pembukuan dan Pelaporan');
            $this->createSubBidang($bidangPembukuan, 'Sub Bidang Pemeriksaan dan Verifikasi');
            $this->createSubBidang($bidangPembukuan, 'Sub Bidang Penagihan dan Restitusi Pajak Daerah');
    }

    /**
     * Helper function untuk membuat sub-bidang agar kodingan lebih rapi
     */
    private function createSubBidang($parent, $namaSubBidang)
    {
        return Bidang::firstOrCreate(
            ['nama_bidang' => $namaSubBidang],
            [
                'unit_kerja_id' => $parent->unit_kerja_id,
                'parent_id' => $parent->id, // INI KUNCINYA: Link ke ID Bapaknya
                'level' => 'sub_bidang'
            ]
        );
    }
}
