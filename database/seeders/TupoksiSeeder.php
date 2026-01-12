<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tupoksi;
use App\Models\Bidang;
use Illuminate\Support\Facades\Schema; // Tambahkan ini

class TupoksiSeeder extends Seeder
{
    public function run()
    {
        // 1. Matikan Foreign Key Check (Laravel akan mendeteksi driver PGSQL/MySQL otomatis)
        Schema::disableForeignKeyConstraints();

        // 2. Kosongkan tabel
        Tupoksi::truncate();

        // 3. Nyalakan kembali Foreign Key Check
        Schema::enableForeignKeyConstraints();

        /**
         * MAPPING TUPOKSI
         */
        $tupoksiMapping = [
            // ================= SEKRETARIAT =================
            'Sekretariat' => [
                'uraian_tugas' => [
                    'Mengoordinasikan penyusunan rencana strategis dan rencana kerja dinas.',
                    'Menyelenggarakan urusan administrasi umum, kepegawaian, dan rumah tangga.',
                    'Mengoordinasikan pengelolaan keuangan dan aset dinas.',
                ]
            ],
            'Sub Bagian Umum dan Kepegawaian' => [
                'uraian_tugas' => [
                    'Melaksanakan urusan surat menyurat, kearsipan, dan ekspedisi.',
                    'Mengelola administrasi kepegawaian, kenaikan pangkat, dan cuti pegawai.',
                    'Melaksanakan urusan rumah tangga, keamanan, dan kebersihan kantor.',
                ]
            ],
            'Sub Bagian Perencanaan dan Keuangan' => [
                'uraian_tugas' => [
                    'Menyusun rencana anggaran pendapatan dan belanja dinas.',
                    'Melakukan verifikasi dan pembukuan keuangan dinas.',
                    'Menyusun laporan realisasi anggaran dan laporan keuangan.',
                ]
            ],

            // ================= BIDANG PENDATAAN =================
            'Bidang Pendataan dan Pendaftaran' => [
                'uraian_tugas' => [
                    'Merumuskan kebijakan teknis di bidang pendataan dan pendaftaran pajak.',
                    'Mengoordinasikan kegiatan pendataan objek dan subjek pajak daerah.',
                    'Melaksanakan pemutakhiran data wajib pajak secara berkala.',
                ]
            ],
            'Sub Bidang Pendataan dan Pendaftaran Pajak Daerah' => [
                'uraian_tugas' => [
                    'Melaksanakan pendataan objek pajak hotel, restoran, dan hiburan.',
                    'Melakukan pendaftaran dan pendataan reklame dan air tanah.',
                    'Menyiapkan formulir pendaftaran dan pendataan pajak daerah.',
                ]
            ],
            'Sub Bidang Pendataan dan Pendaftaran PBB P2 dan BPHTB' => [
                'uraian_tugas' => [
                    'Melaksanakan pendataan objek PBB-P2 (Pajak Bumi dan Bangunan).',
                    'Melakukan verifikasi data SPOP dan LSPOP.',
                    'Mengelola basis data BPHTB (Bea Perolehan Hak atas Tanah dan Bangunan).',
                ]
            ],
            'Sub Bidang Pengolahan Data dan Sistem Informasi' => [
                'uraian_tugas' => [
                    'Mengelola sistem informasi manajemen pendapatan daerah (SIMPAD).',
                    'Melakukan pemeliharaan (maintenance) perangkat keras dan lunak.',
                    'Menyajikan data potensi pajak dalam bentuk statistik dan visualisasi.',
                    'Mengembangkan inovasi layanan berbasis digital/elektronik.',
                ]
            ],

            // ================= BIDANG PENETAPAN =================
            'Bidang Penetapan Pajak Daerah' => [
                'uraian_tugas' => [
                    'Menetapkan besaran pajak daerah terutang berdasarkan peraturan.',
                    'Menerbitkan Surat Ketetapan Pajak Daerah (SKPD).',
                    'Mengoordinasikan penyelesaian keberatan dan banding pajak.',
                ]
            ],
            'Sub Bidang Perhitungan dan Penetapan Pajak Daerah' => [
                'uraian_tugas' => [
                    'Melakukan perhitungan penetapan pajak hotel, restoran, dan hiburan.',
                    'Menyiapkan konsep Surat Ketetapan Pajak Daerah (SKPD).',
                    'Melakukan perhitungan pajak reklame dan pajak lainnya.',
                ]
            ],
            'Sub Bidang Penilaian dan Penetapan PBB P2 dan BPHTB' => [
                'uraian_tugas' => [
                    'Melakukan penilaian individual dan massal objek PBB-P2.',
                    'Menetapkan NJOP (Nilai Jual Objek Pajak) sebagai dasar pengenaan pajak.',
                    'Memproses penetapan SPPT PBB-P2 tahunan.',
                ]
            ],
            'Sub Bidang Keberatan dan Banding' => [
                'uraian_tugas' => [
                    'Memproses permohonan keberatan atas ketetapan pajak.',
                    'Melakukan kajian teknis terhadap pengajuan banding wajib pajak.',
                    'Menyiapkan administrasi pengurangan atau keringanan pajak.',
                ]
            ],

            // ================= BIDANG PENAGIHAN =================
            'Bidang Penagihan dan Pengawasan' => [
                'uraian_tugas' => [
                    'Merumuskan strategi penagihan pajak daerah yang efektif.',
                    'Melakukan pengawasan dan pengendalian terhadap kepatuhan wajib pajak.',
                    'Melaksanakan penagihan aktif terhadap tunggakan pajak.',
                ]
            ],
            'Sub Bidang Penagihan dan Restitusi Pajak Daerah' => [
                'uraian_tugas' => [
                    'Melaksanakan penagihan pajak daerah secara persuasif maupun teguran.',
                    'Memproses administrasi restitusi (pengembalian kelebihan bayar) pajak.',
                    'Menyusun daftar tunggakan pajak daerah.',
                ]
            ],
            'Sub Bidang Penagihan dan Restitusi PBB dan BPHTB' => [
                'uraian_tugas' => [
                    'Melaksanakan penagihan tunggakan PBB-P2 ke wilayah kecamatan/kelurahan.',
                    'Memproses permohonan restitusi PBB-P2 dan BPHTB.',
                    'Melakukan rekonsiliasi data piutang PBB-P2.',
                ]
            ],
            'Sub Bidang Pemeriksaan dan Pengawasan Pajak' => [
                'uraian_tugas' => [
                    'Melakukan pemeriksaan pembukuan wajib pajak (audit).',
                    'Mengawasi pelaporan omzet wajib pajak self-assessment (Hotel/Restoran).',
                    'Melakukan operasi sisir (monitoring lapangan) objek pajak.',
                ]
            ],

            // ================= BIDANG PERENCANAAN =================
            'Bidang Perencanaan dan Pelaporan' => [
                'uraian_tugas' => [
                    'Menyusun rencana target penerimaan pendapatan daerah.',
                    'Mengevaluasi realisasi penerimaan pajak secara periodik.',
                    'Menyusun regulasi (Perda/Perbup) terkait pajak daerah.',
                ]
            ],
            'Sub Bidang Regulasi Pendapatan Daerah' => [
                'uraian_tugas' => [
                    'Menyusun rancangan peraturan daerah tentang pajak dan retribusi.',
                    'Mengkaji ulang peraturan yang sudah ada untuk penyesuaian tarif.',
                    'Melakukan sosialisasi regulasi pajak kepada masyarakat.',
                ]
            ],
            'Sub Bidang Retribusi dan Evaluasi Pendapatan Daerah' => [
                'uraian_tugas' => [
                    'Melakukan analisis potensi retribusi daerah.',
                    'Mengevaluasi kinerja penerimaan retribusi dari OPD pemungut.',
                    'Menyusun laporan evaluasi pendapatan daerah.',
                ]
            ],
            'Sub Bidang Pembukuan dan Pelaporan' => [
                'uraian_tugas' => [
                    'Melakukan pembukuan penerimaan harian pajak daerah.',
                    'Menyusun laporan realisasi penerimaan pajak bulanan, triwulanan, dan tahunan.',
                    'Melakukan rekonsiliasi data penerimaan dengan Kas Daerah.',
                ]
            ],
        ];

        // LOGIC EKSEKUSI
        foreach ($tupoksiMapping as $namaBidang => $data) {
            // Cari ID bidang berdasarkan nama (Case Insensitive agar lebih aman)
            $bidang = Bidang::where('nama_bidang', $namaBidang)->first();

            if ($bidang) {
                foreach ($data['uraian_tugas'] as $uraian_tugasTugas) {
                    Tupoksi::create([
                        'bidang_id' => $bidang->id,
                        'uraian_tugas'    => $uraian_tugasTugas,
                    ]);
                }
                $this->command->info("Tupoksi inserted for: " . $namaBidang);
            } else {
                $this->command->error("Bidang not found: " . $namaBidang . " (Pastikan StrukturOrganisasiSeeder sudah dijalankan)");
            }
        }
    }
}