<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bidang;
use App\Models\UnitKerja;

class BidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Catatan desain:
         * - Bidang = struktur organisasi (HIRARKIS)
         * - level: bidang | sub_bidang
         * - parent_id hanya dipakai oleh sub_bidang
         * - unit_kerja_id = 1 (BAPENDA)
         */

        // 0. Pastikan Unit Kerja ada
        $unitKerja = UnitKerja::firstOrCreate(
            ['id' => 1],
            [
                'nama_unit' => 'Badan Pendapatan Daerah',
            ]
        );

        /**
         * ======================================================
         * 1. SEKRETARIAT
         * ======================================================
         */
        $sekretariat = $this->createBidang('Sekretariat', $unitKerja->id);

        $this->createSubBidang($sekretariat, 'Sub Bagian Umum dan Kepegawaian');
        $this->createSubBidang($sekretariat, 'Sub Bagian Keuangan');
        $this->createSubBidang($sekretariat, 'Sub Bagian Program');

        /**
         * ======================================================
         * 2. BIDANG PAJAK
         * ======================================================
         */
        $bidangPajak = $this->createBidang('Bidang Pajak', $unitKerja->id);

        $this->createSubBidang($bidangPajak, 'Sub Bidang Pendataan dan Pendaftaran Pajak');
        $this->createSubBidang($bidangPajak, 'Sub Bidang Perhitungan dan Penetapan Pajak Daerah');
        $this->createSubBidang(
            $bidangPajak,
            'Sub Bidang Pemeriksaan Pajak, Konsultasi, Keberatan dan Banding'
        );

        /**
         * ======================================================
         * 3. BIDANG PERENCANAAN DAN PENGEMBANGAN
         *    PENDAPATAN DAERAH
         * ======================================================
         */
        $bidangRenbang = $this->createBidang(
            'Bidang Perencanaan dan Pengembangan Pendapatan Daerah',
            $unitKerja->id
        );

        $this->createSubBidang($bidangRenbang, 'Sub Bidang Regulasi Pendapatan Daerah');
        $this->createSubBidang($bidangRenbang, 'Sub Bidang Retribusi dan Evaluasi Pendapatan Daerah');
        $this->createSubBidang(
            $bidangRenbang,
            'Sub Bidang Pengembangan Sistem Informatika dan Inovasi Pendapatan Daerah'
        );

        /**
         * ======================================================
         * 4. BIDANG PBB DAN BPHTB
         * ======================================================
         */
        $bidangPbb = $this->createBidang('Bidang PBB dan BPHTB', $unitKerja->id);

        $this->createSubBidang($bidangPbb, 'Sub Bidang Pendataan dan Pendaftaran PBB dan BPHTB');
        $this->createSubBidang($bidangPbb, 'Sub Bidang Penilaian dan Penetapan PBB dan BPHTB');
        $this->createSubBidang(
            $bidangPbb,
            'Sub Bidang Penagihan Restitusi PBB dan BPHTB'
        );

        /**
         * ======================================================
         * 5. BIDANG PEMBUKUAN DAN PELAPORAN
         * ======================================================
         */
        $bidangPembukuan = $this->createBidang(
            'Bidang Pembukuan dan Pelaporan',
            $unitKerja->id
        );

        $this->createSubBidang($bidangPembukuan, 'Sub Bidang Pembukuan dan Pelaporan');
        $this->createSubBidang($bidangPembukuan, 'Sub Bidang Pemeriksaan dan Verifikasi');
        $this->createSubBidang($bidangPembukuan, 'Sub Bidang Penagihan');
    }

    /**
     * ======================================================
     * Helper: buat bidang (level = bidang)
     * ======================================================
     */
    private function createBidang(string $namaBidang, int $unitKerjaId): Bidang
    {
        return Bidang::firstOrCreate(
            ['nama_bidang' => $namaBidang],
            [
                'unit_kerja_id' => $unitKerjaId,
                'parent_id'     => null,
                'level'         => 'bidang',
            ]
        );
    }

    /**
     * ======================================================
     * Helper: buat sub bidang / sub bagian (level = sub_bidang)
     * ======================================================
     */
    private function createSubBidang(Bidang $parent, string $namaSubBidang): Bidang
    {
        return Bidang::firstOrCreate(
            ['nama_bidang' => $namaSubBidang],
            [
                'unit_kerja_id' => $parent->unit_kerja_id,
                'parent_id'     => $parent->id,
                'level'         => 'sub_bidang',
            ]
        );
    }
}
