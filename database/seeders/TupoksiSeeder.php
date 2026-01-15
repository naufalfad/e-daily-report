<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tupoksi;
use App\Models\Bidang;
use Illuminate\Support\Facades\Schema;

class TupoksiSeeder extends Seeder
{
    public function run()
    {
        // Disable FK
        Schema::disableForeignKeyConstraints();
        Tupoksi::truncate();
        Schema::enableForeignKeyConstraints();

        $tupoksiMapping = [

            // ===================== SEKRETARIAT =====================
            'Sekretariat' => [
                'uraian_tugas' => [
                    'Mengoordinasikan pelaksanaan administrasi umum, kepegawaian, keuangan, dan perencanaan Badan Pendapatan Daerah.',
                    'Mengoordinasikan penyusunan rencana kerja, program, dan kegiatan Badan.',
                    'Mengoordinasikan pelaksanaan pengelolaan keuangan dan aset Badan.',
                    'Mengoordinasikan penyusunan laporan kinerja dan laporan administrasi Badan.',
                    'Melaksanakan tugas lain yang diberikan oleh Kepala Badan sesuai tugas dan fungsi.'
                ]
            ],

            'Sub Bagian Umum dan Kepegawaian' => [
                'uraian_tugas' => [
                    'Melaksanakan urusan surat menyurat, kearsipan, dan tata naskah dinas.',
                    'Melaksanakan administrasi kepegawaian, mutasi, kenaikan pangkat, dan cuti pegawai.',
                    'Melaksanakan pengelolaan barang milik daerah dan rumah tangga kantor.',
                    'Melaksanakan pelayanan administrasi kegiatan kedinasan.',
                    'Menyusun laporan administrasi umum dan kepegawaian.'
                ]
            ],

            'Sub Bagian Keuangan' => [
                'uraian_tugas' => [
                    'Menyusun rencana anggaran dan dokumen pelaksanaan anggaran Badan.',
                    'Melaksanakan penatausahaan dan pembukuan keuangan.',
                    'Melaksanakan pengelolaan bendahara penerimaan dan pengeluaran.',
                    'Melaksanakan verifikasi administrasi keuangan.',
                    'Menyusun laporan keuangan Badan.'
                ]
            ],

            'Sub Bagian Program' => [
                'uraian_tugas' => [
                    'Menyusun rencana kerja dan program kegiatan Badan.',
                    'Melaksanakan monitoring dan evaluasi pelaksanaan program.',
                    'Menghimpun dan mengolah data perencanaan.',
                    'Menyusun laporan kinerja Badan.',
                    'Melaksanakan tugas perencanaan lainnya sesuai ketentuan.'
                ]
            ],

            // ===================== BIDANG PAJAK =====================
            'Bidang Pajak' => [
                'uraian_tugas' => [
                    'Merumuskan kebijakan teknis pengelolaan pajak daerah.',
                    'Mengoordinasikan pendataan, penetapan, dan pemeriksaan pajak daerah.',
                    'Melaksanakan pembinaan dan pengawasan pajak daerah.',
                    'Menyusun laporan pelaksanaan tugas bidang pajak.'
                ]
            ],

            'Sub Bidang Pendataan dan Pendaftaran Pajak' => [
                'uraian_tugas' => [
                    'Melaksanakan pendataan objek dan subjek pajak daerah.',
                    'Melaksanakan pendaftaran wajib pajak daerah.',
                    'Melaksanakan pemutakhiran data pajak daerah.',
                    'Menyusun laporan hasil pendataan dan pendaftaran pajak.'
                ]
            ],

            'Sub Bidang Perhitungan dan Penetapan Pajak Daerah' => [
                'uraian_tugas' => [
                    'Melaksanakan perhitungan pajak daerah terutang.',
                    'Melaksanakan penetapan pajak daerah.',
                    'Menyusun dokumen penetapan pajak.',
                    'Menyusun laporan penetapan pajak daerah.'
                ]
            ],

            'Sub Bidang Pemeriksaan Pajak, Konsultasi, Keberatan dan Banding' => [
                'uraian_tugas' => [
                    'Melaksanakan pemeriksaan kepatuhan wajib pajak.',
                    'Memberikan pelayanan konsultasi perpajakan daerah.',
                    'Menangani keberatan dan banding pajak.',
                    'Menyusun laporan hasil pemeriksaan dan penyelesaian sengketa pajak.'
                ]
            ],

            // ===================== BIDANG PERENCANAAN & PENGEMBANGAN =====================
            'Bidang Perencanaan dan Pengembangan Pendapatan Daerah' => [
                'uraian_tugas' => [
                    'Merumuskan kebijakan perencanaan pendapatan daerah.',
                    'Menyusun program pengembangan pendapatan daerah.',
                    'Melaksanakan evaluasi dan inovasi pendapatan daerah.',
                    'Menyusun laporan perencanaan dan pengembangan pendapatan.'
                ]
            ],

            'Sub Bidang Regulasi Pendapatan Daerah' => [
                'uraian_tugas' => [
                    'Menyusun rancangan regulasi pendapatan daerah.',
                    'Mengkaji peraturan pendapatan daerah yang berlaku.',
                    'Mengoordinasikan penyusunan regulasi dengan perangkat daerah terkait.',
                    'Menyusun laporan kegiatan regulasi pendapatan daerah.'
                ]
            ],

            'Sub Bidang Retribusi dan Evaluasi Pendapatan Daerah' => [
                'uraian_tugas' => [
                    'Melaksanakan analisis potensi retribusi daerah.',
                    'Melaksanakan monitoring dan evaluasi pendapatan daerah.',
                    'Mengoordinasikan evaluasi penerimaan retribusi.',
                    'Menyusun laporan evaluasi pendapatan daerah.'
                ]
            ],

            'Sub Bidang Pengembangan Sistem Informatika dan Inovasi Pendapatan Daerah' => [
                'uraian_tugas' => [
                    'Mengembangkan sistem informasi pendapatan daerah.',
                    'Melaksanakan inovasi pelayanan pendapatan daerah berbasis teknologi.',
                    'Mengelola aplikasi dan basis data pendapatan daerah.',
                    'Menyusun laporan pengembangan sistem dan inovasi.'
                ]
            ],

            // ===================== BIDANG PBB DAN BPHTB =====================
            'Bidang PBB dan BPHTB' => [
                'uraian_tugas' => [
                    'Melaksanakan pengelolaan Pajak Bumi dan Bangunan serta BPHTB.',
                    'Mengoordinasikan pendataan, penetapan, dan penagihan PBB dan BPHTB.',
                    'Menyusun laporan pelaksanaan pengelolaan PBB dan BPHTB.'
                ]
            ],

            'Sub Bidang Pendataan dan Pendaftaran PBB dan BPHTB' => [
                'uraian_tugas' => [
                    'Melaksanakan pendataan objek dan subjek PBB dan BPHTB.',
                    'Melaksanakan pendaftaran PBB dan BPHTB.',
                    'Melaksanakan pemutakhiran data PBB dan BPHTB.',
                    'Menyusun laporan pendataan dan pendaftaran.'
                ]
            ],

            'Sub Bidang Penilaian dan Penetapan PBB dan BPHTB' => [
                'uraian_tugas' => [
                    'Melaksanakan penilaian objek PBB dan BPHTB.',
                    'Melaksanakan penetapan PBB dan BPHTB.',
                    'Menyusun dokumen penetapan pajak.',
                    'Menyusun laporan penilaian dan penetapan.'
                ]
            ],

            'Sub Bidang Penagihan Restitusi PBB dan BPHTB' => [
                'uraian_tugas' => [
                    'Melaksanakan penagihan PBB dan BPHTB.',
                    'Melaksanakan pengelolaan restitusi PBB dan BPHTB.',
                    'Melaksanakan monitoring pembayaran pajak.',
                    'Menyusun laporan penagihan dan restitusi.'
                ]
            ],

            // ===================== BIDANG PEMBUKUAN DAN PELAPORAN =====================
            'Bidang Pembukuan dan Pelaporan' => [
                'uraian_tugas' => [
                    'Mengoordinasikan pembukuan dan pelaporan pendapatan daerah.',
                    'Mengoordinasikan pemeriksaan dan verifikasi pendapatan.',
                    'Menyusun laporan pendapatan daerah.'
                ]
            ],

            'Sub Bidang Pembukuan dan Pelaporan' => [
                'uraian_tugas' => [
                    'Melaksanakan pembukuan penerimaan pendapatan daerah.',
                    'Menyusun laporan realisasi pendapatan.',
                    'Melaksanakan rekonsiliasi data pendapatan.',
                    'Menyusun laporan pembukuan.'
                ]
            ],

            'Sub Bidang Pemeriksaan dan Verifikasi' => [
                'uraian_tugas' => [
                    'Melaksanakan pemeriksaan administrasi pendapatan daerah.',
                    'Melaksanakan verifikasi data pendapatan.',
                    'Menyusun laporan hasil pemeriksaan dan verifikasi.'
                ]
            ],

            'Sub Bidang Penagihan' => [
                'uraian_tugas' => [
                    'Melaksanakan penagihan pendapatan daerah.',
                    'Melaksanakan monitoring piutang pendapatan.',
                    'Menyusun laporan hasil penagihan.'
                ]
            ],
        ];

        foreach ($tupoksiMapping as $namaBidang => $data) {
            $bidang = Bidang::where('nama_bidang', $namaBidang)->first();

            if ($bidang) {
                foreach ($data['uraian_tugas'] as $uraian) {
                    Tupoksi::create([
                        'bidang_id'    => $bidang->id,
                        'uraian_tugas' => $uraian,
                    ]);
                }
                $this->command->info("Tupoksi inserted: {$namaBidang}");
            } else {
                $this->command->error("Bidang tidak ditemukan: {$namaBidang}");
            }
        }
    }
}
