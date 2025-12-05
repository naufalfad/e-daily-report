<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Role;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\Bidang;
use App\Models\Tupoksi;
use App\Models\SkpRencana;
use App\Models\SkpTarget;
use App\Models\LaporanHarian;
use App\Models\Pengumuman;

class TestingSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $this->command->info('Memulai Proses Seeding Master Data dan Struktur Organisasi (Finalisasi Struktur Organisasi)...');

            // --- 1. CLEANUP DATA LAMA ---
            $this->cleanupData();
            
            $globalPassword = Hash::make('password123');
            $this->command->warn('Semua password diatur menjadi: password123');
            $globalAlamat = 'Jl. Yos Sudarso, Nawaripi, Kec. Mimika Baru, Kabupaten Mimika, Papua Tengah 99971';

            // =================================================================
            // 2. MASTER DATA UTAMA (ROLES, JABATAN, UNIT KERJA, BIDANG)
            // =================================================================
            
            // Roles
            $rAdmin   = Role::firstOrCreate(['nama_role' => 'Super Admin']);
            $rKadis   = Role::firstOrCreate(['nama_role' => 'Kadis']); 
            $rPenilai = Role::firstOrCreate(['nama_role' => 'Penilai']); 
            $rPegawai = Role::firstOrCreate(['nama_role' => 'Staf']); 

            // Jabatan
            $jKaban   = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Badan']);
            $jSekban  = Jabatan::firstOrCreate(['nama_jabatan' => 'Sekretaris']);
            $jKabid   = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Bidang']);
            $jKasub   = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Sub Bagian/Bidang']);
            $jStaf    = Jabatan::firstOrCreate(['nama_jabatan' => 'Staf Pelaksana']);
            $jAdmin   = Jabatan::firstOrCreate(['nama_jabatan' => 'Administrator Sistem']);

            // Unit Kerja
            $ukBapenda = UnitKerja::firstOrCreate(['nama_unit' => 'Badan Pendapatan Daerah']);

            // Bidang (Sesuai database, 5 Bidang)
            $bSekretariat = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Sekretariat']); 
            $bPimpinan    = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Unsur Pimpinan']); 
            $bPbb         = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Bidang PBB dan BPHTB']); 
            $bDana        = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Bidang Dana Perimbangan']); 
            $bIT          = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Unit Teknologi Informasi']); 

            // =================================================================
            // 3. TUPOKSI (URAIAN TUGAS)
            // =================================================================
            $this->seedTupoksi($bSekretariat, $bPimpinan, $bPbb, $bDana, $bIT);

            // =================================================================
            // 4. USER CREATION & HIERARCHY (PER BIDANG)
            // =================================================================
            
            // 4.1. UN SUR PIMPINAN (Kaban + Staff Protokol Baru)
            $this->command->info('-> Seeding User Bidang: Unsur Pimpinan (Kabid Kaban + Staff)');
            list($kaban, $stafPimpinan) = $this->createUnsurPimpinanUsers($ukBapenda, $bPimpinan, $jKaban, $jStaf, $rKadis, $rPenilai, $rPegawai, $globalPassword, $globalAlamat);
            $atasanKaban = $kaban;

            // 4.2. BIDANG SEKRETARIAT (Subordinat Kaban)
            $this->command->info('-> Seeding User Bidang: Sekretariat');
            list($sekban, $kasubUmum, $stafUmum, $kasubKeuangan, $stafKeuangan) = $this->createSekretariatUsers($ukBapenda, $bSekretariat, $jSekban, $jKasub, $jStaf, $rPenilai, $rPegawai, $kaban, $globalPassword, $globalAlamat);
            
            // 4.3. BIDANG PBB DAN BPHTB (Subordinat Kaban)
            $this->command->info('-> Seeding User Bidang: PBB dan BPHTB');
            list($kabidPbb, $kasubPbbData, $stafPbbData, $kasubBphtb, $stafBphtb) = $this->createPbbUsers($ukBapenda, $bPbb, $jKabid, $jKasub, $jStaf, $rPenilai, $rPegawai, $kaban, $globalPassword, $globalAlamat);
            
            // 4.4. BIDANG DANA PERIMBANGAN (Subordinat Kaban)
            $this->command->info('-> Seeding User Bidang: Dana Perimbangan');
            list($kabidDana, $kasubDanaTransfer, $stafDanaTransfer, $kasubDanaAnalis, $stafDanaAnalis) = $this->createDanaUsers($ukBapenda, $bDana, $jKabid, $jKasub, $jStaf, $rPenilai, $rPegawai, $kaban, $globalPassword, $globalAlamat);
            
            // 4.5. UNIT TEKNOLOGI INFORMASI (Sudah ada Kabid)
            $this->command->info('-> Seeding User Bidang: Unit Teknologi Informasi');
            list($kabidIT, $adminUser, $stafIT) = $this->createITUsers($ukBapenda, $bIT, $jAdmin, $jStaf, $rAdmin, $rPenilai, $rPegawai, $kaban, $globalPassword, $globalAlamat, $jKabid, $jKasub);

            // =================================================================
            // 5. SIMULASI SKP & LKH (PER BIDANG)
            // =================================================================
            
            // 5.1. SIMULASI KINERJA: UN SUR PIMPINAN (KABAN & STAF)
            $this->command->info('-> Seeding SKP & LKH: Unsur Pimpinan');
            // Kaban
            $rencanaKaban = $this->createSkpPaket($kaban,
                'Terwujudnya implementasi sistem e-Daily Report yang efektif',
                'Memimpin dan mengkoordinasikan implementasi dan penggunaan sistem e-Daily Report',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Dokumen Laporan Monitoring Implementasi', 'target' => 4, 'satuan' => 'Laporan']]
            );
            $this->createLkh($kaban, $rencanaKaban, 'approved', 'Memimpin Rapat Pimpinan (Rapim) Evaluasi Kinerja Triwulan III', 'Notulensi Rapim dan Laporan Evaluasi', 'Rapat', $atasanKaban, 'Kinerja bidang PBB & BPHTB perlu ditingkatkan.');
            
            // Staf Protokol Pimpinan (Baru)
            $rencanaStaffPimpinan = $this->createSkpPaket($stafPimpinan,
                'Meningkatnya citra positif dan layanan informasi pimpinan',
                'Melaksanakan tugas protokoler dan pengelolaan media sosial pimpinan',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Laporan Kegiatan Protokoler', 'target' => 30, 'satuan' => 'Laporan']]
            );
            $this->createLkh($stafPimpinan, $rencanaStaffPimpinan, 'approved', 'Menyusun draf sambutan Kepala Badan untuk acara sosialisasi pajak', 'Draf sambutan siap diparaf', 'Penyusunan Dokumen', $kaban);
            $this->createLkh($stafPimpinan, $rencanaStaffPimpinan, 'waiting_review', 'Mengelola dan memposting 2 konten kegiatan harian di media sosial', '2 Postingan di Instagram & Facebook', 'Dukungan Teknis', $kaban);


            // 5.2. SIMULASI KINERJA: SEKRETARIAT
            $this->command->info('-> Seeding SKP & LKH: Sekretariat');
            // Sekban
            $rencanaSekban = $this->createSkpPaket($sekban,
                'Terwujudnya tata kelola administrasi Bapenda yang akuntabel',
                'Mengoordinasikan pelayanan administratif, kepegawaian, dan fasilitasi kinerja',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Laporan Kinerja Instansi', 'target' => 1, 'satuan' => 'Laporan']]
            );
            $this->createLkh($sekban, $rencanaSekban, 'approved', 'Menyusun draf Laporan Kinerja Instansi (LAKIP) Triwulan IV', 'Draf LAKIP siap diparaf', 'Penyusunan Dokumen', $kaban);

            // Kasubag Umum (Administrasi & Kepegawaian)
            $rencanaKasubagUmum = $this->createSkpPaket($kasubUmum,
                'Tertibnya administrasi kepegawaian dan kearsipan',
                'Mengelola Urusan Umum, Aset, dan Kepegawaian',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Laporan Inventarisasi BMD', 'target' => 2, 'satuan' => 'Laporan']]
            );
            $this->createLkh($kasubUmum, $rencanaKasubagUmum, 'rejected', 'Melakukan verifikasi berkas kenaikan pangkat 5 pegawai', 'Berkas terverifikasi lengkap', 'Administrasi Kepegawaian', $sekban, 'Berkas 1980xxxx Nip, mohon lengkapi surat pengantar dari BKD.');
            $this->createLkh($stafUmum, null, 'approved', 'Mengarsip 100 surat masuk dan keluar secara digital', '100 dokumen terindeks dalam sistem kearsipan', 'Penyusunan Dokumen', $kasubUmum);

            // Kasubag Keuangan (Baru)
            $rencanaKasubagKeu = $this->createSkpPaket($kasubKeuangan,
                'Tersusunnya Laporan Keuangan yang Akuntabel',
                'Mengelola dan menyusun dokumen perencanaan anggaran dan pelaporan keuangan',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Dokumen Laporan Pertanggungjawaban (LPJ)', 'target' => 12, 'satuan' => 'Dokumen']]
            );
            $this->createLkh($kasubKeuangan, $rencanaKasubagKeu, 'waiting_review', 'Menyusun Laporan Pertanggungjawaban Keuangan bulan November 2025', 'Draf LPJ November siap diperiksa', 'Penyusunan Dokumen', $sekban);
            $this->createLkh($stafKeuangan, null, 'waiting_review', 'Merekapitulasi nota dinas dan SPJ perjalanan dinas Kabid Dana', 'Rekapitulasi SPJ terlampir', 'Administrasi Keuangan', $kasubKeuangan);


            // 5.3. SIMULASI KINERJA: PBB DAN BPHTB
            $this->command->info('-> Seeding SKP & LKH: PBB dan BPHTB');
            $rencanaKabidPbb = $this->createSkpPaket($kabidPbb,
                'Meningkatnya PAD Sektor Pajak Daerah',
                'Merumuskan kebijakan teknis intensifikasi PBB dan BPHTB',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Dokumen Kebijakan/Perbup', 'target' => 2, 'satuan' => 'Dokumen']]
            );
            $this->createLkh($kabidPbb, $rencanaKabidPbb, 'approved', 'Rapat Koordinasi dengan BPN terkait penetapan NJOP BPHTB', 'Berita Acara Kesepakatan NJOP', 'Rapat', $kaban);

            // Kasubid Pendataan PBB
            $rencanaKasubidPbb = $this->createSkpPaket($kasubPbbData,
                'Tersedianya basis data pajak daerah yang akurat dan mutakhir',
                'Mengelola kegiatan pendataan, pendaftaran, dan pemutakhiran data PBB',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Laporan hasil monitoring pendataan', 'target' => 12, 'satuan' => 'Laporan']]
            );
            $this->createLkh($kasubPbbData, $rencanaKasubidPbb, 'approved', 'Memverifikasi 50 berkas permohonan mutasi PBB dari loket pelayanan', 'Berkas terverifikasi dan diparaf', 'Penyusunan Dokumen', $kabidPbb);

            // Staf Pendataan PBB
            $rencanaStaffPbb = $this->createSkpPaket($stafPbbData,
                'Meningkatnya validitas data PBB Sektor Pedesaan & Perkotaan',
                'Melakukan pendataan objek pajak PBB sektor pedesaan dan perkotaan secara door-to-door',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Jumlah objek pajak terdata secara akurat', 'target' => 200, 'satuan' => 'Formulir SPOP']]
            );
            $this->createLkh($stafPbbData, $rencanaStaffPbb, 'waiting_review', 'Melakukan survei lapangan di Distrik Mimika Baru (Jalan Budi Utomo)', '5 Formulir SPOP terisi lengkap dengan foto lokasi', 'Kunjungan Lapangan', $kasubPbbData);

            // Kasubid BPHTB (Baru)
            $rencanaKasubidBphtb = $this->createSkpPaket($kasubBphtb,
                'Tercapainya target penerimaan BPHTB',
                'Mengelola pelayanan, verifikasi, dan penetapan BPHTB',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Laporan Rekapitulasi SKPD BPHTB', 'target' => 12, 'satuan' => 'Laporan']]
            );
            $this->createLkh($kasubBphtb, $rencanaKasubidBphtb, 'approved', 'Melaksanakan verifikasi 20 berkas permohonan BPHTB non-transaksi', '20 berkas BPHTB non-transaksi disetujui', 'Pelayanan Publik', $kabidPbb);
            $this->createLkh($stafBphtb, null, 'rejected', 'Membantu kasubid dalam penyusunan Laporan SKPD BPHTB', 'Draf Laporan SKPD BPHTB selesai', 'Penyusunan Dokumen', $kasubBphtb, 'Revisi, pastikan data NOP valid sebelum dimasukkan ke rekap.');


            // 5.4. SIMULASI KINERJA: DANA PERIMBANGAN
            $this->command->info('-> Seeding SKP & LKH: Dana Perimbangan');
            $rencanaKabidDana = $this->createSkpPaket($kabidDana,
                'Optimalisasi pengelolaan dana transfer dari pusat',
                'Mengoordinasikan pengelolaan dan analisis Dana Perimbangan',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Dokumen Laporan Realisasi Dana Transfer', 'target' => 4, 'satuan' => 'Dokumen']]
            );
            $this->createLkh($kabidDana, $rencanaKabidDana, 'waiting_review', 'Menghadiri Rapat Koordinasi Teknis (Rakornis) Dana Transfer Daerah di Jakarta', 'Laporan perjalanan dinas dan notulensi Rakornis', 'Perjalanan Dinas', $kaban);

            // Kasubid Transfer Dana
            $rencanaKasubDanaTransfer = $this->createSkpPaket($kasubDanaTransfer,
                'Tersedianya data dana perimbangan yang akurat',
                'Melaksanakan rekonsiliasi dan verifikasi data transfer dana',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Berita Acara Rekonsiliasi Data', 'target' => 12, 'satuan' => 'Dokumen']]
            );
            $this->createLkh($kasubDanaTransfer, $rencanaKasubDanaTransfer, 'approved', 'Melaksanakan rekonsiliasi data Dana Bagi Hasil Triwulan I dengan BPKAD', 'Berita Acara Rekonsiliasi (BAR) DBH', 'Rapat', $kabidDana);
            $this->createLkh($stafDanaTransfer, null, 'approved', 'Melakukan pengarsipan digital 50 dokumen Nota Transfer Dana Bagi Hasil', '50 file PDF terindeks di sistem kearsipan', 'Penyusunan Dokumen', $kasubDanaTransfer);

            // Kasubid Analisis Dana (Baru)
            $rencanaKasubDanaAnalis = $this->createSkpPaket($kasubDanaAnalis,
                'Penyusunan laporan analisis dana perimbangan yang tepat waktu',
                'Melakukan analisis dan pelaporan realisasi Dana Perimbangan',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Laporan Analisis Realisasi Dana Transfer', 'target' => 4, 'satuan' => 'Laporan']]
            );
            $this->createLkh($kasubDanaAnalis, $rencanaKasubDanaAnalis, 'rejected', 'Menyusun draf Analisis Dana Transfer semester 1', 'Draft analisis Dana Transfer S1', 'Penyusunan Dokumen', $kabidDana, 'Data serapan Dana Alokasi Umum (DAU) belum terupdate, mohon cek BPKAD.');
            $this->createLkh($stafDanaAnalis, null, 'waiting_review', 'Mengumpulkan dan merekapitulasi data serapan anggaran Bidang Dana', 'File rekapitulasi serapan anggaran', 'Penyusunan Dokumen', $kasubDanaAnalis);


            // 5.5. SIMULASI KINERJA: UNIT TEKNOLOGI INFORMASI
            $this->command->info('-> Seeding SKP & LKH: Unit Teknologi Informasi');
            // Kabid Unit TI SKP
            $rencanaKabidIT = $this->createSkpPaket($kabidIT,
                'Tercapainya performa sistem informasi yang optimal',
                'Menyusun strategi dan mengawasi implementasi infrastruktur IT',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Dokumen Strategi dan Roadmap IT', 'target' => 1, 'satuan' => 'Dokumen']]
            );
            $this->createLkh($kabidIT, $rencanaKabidIT, 'approved', 'Menyusun draf Dokumen Strategi Pembangunan Infrastruktur TI 5 tahunan', 'Draf Dokumen Strategi TI final', 'Penyusunan Dokumen', $kaban, 'Tanda tangan sudah dilakukan. Segera sosialisasikan.');

            // Admin User (sekarang Kasubid) SKP
            $rencanaAdminUser = $this->createSkpPaket($adminUser,
                'Terjaminnya ketersediaan dan keamanan sistem informasi',
                'Mengelola dan memaintenance seluruh infrastruktur dan aplikasi sistem',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Laporan Audit Keamanan Sistem', 'target' => 2, 'satuan' => 'Laporan']]
            );
            $this->createLkh($adminUser, $rencanaAdminUser, 'approved', 'Melakukan migrasi server database e-Daily Report ke cluster baru', 'Database berjalan stabil pada cluster baru', 'Dukungan Teknis', $kabidIT, 'Sistem berjalan 100% tanpa downtime. Baik.');
            
            // Staf IT LKH
            $rencanaStaffIT = $this->createSkpPaket($stafIT,
                'Terlaksananya pemeliharaan perangkat IT secara berkala',
                'Melaksanakan pemeliharaan hardware dan software di seluruh unit',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Formulir Hasil Pemeliharaan', 'target' => 50, 'satuan' => 'Unit Komputer']]
            );
            $this->createLkh($stafIT, $rencanaStaffIT, 'waiting_review', 'Melakukan instalasi ulang OS dan aplikasi dasar di Bidang PBB (10 unit)', 'Formulir hasil pemeliharaan terisi (10 unit)', 'Dukungan Teknis', $adminUser);
            $this->createLkh($stafIT, $rencanaStaffIT, 'approved', 'Memberikan dukungan teknis kepada Sekban terkait masalah printer jaringan', 'Printer Sekban berfungsi normal kembali', 'Dukungan Teknis', $adminUser, 'Baik, pertahankan respon cepat.');


            // =================================================================
            // 6. PENGUMUMAN
            // =================================================================
            $this->seedPengumuman($kasubPbbData, $sekban);


            $this->command->info('Simulasi Data Selesai. Akun Kabid TI: kabid.it / password123. Total 19 User. Akun lain menggunakan format: [username] / password123.');
        });
    }

    /**
     * Helper: Membersihkan data lama.
     */
    private function cleanupData()
    {
        LaporanHarian::query()->delete();
        SkpTarget::query()->delete(); 
        SkpRencana::query()->delete(); 
        Pengumuman::query()->delete();
        DB::table('user_roles')->delete();
        User::query()->delete(); 
        Role::query()->delete();
        Tupoksi::query()->delete();
        Bidang::query()->delete();
        Jabatan::query()->delete();
        UnitKerja::query()->delete();
    }

    /**
     * Helper: Seeding Tupoksi untuk semua Bidang.
     */
    private function seedTupoksi($bSekretariat, $bPimpinan, $bPbb, $bDana, $bIT)
    {
        // Unsur Pimpinan
        Tupoksi::firstOrCreate(['bidang_id' => $bPimpinan->id, 'uraian_tugas' => 'Merumuskan kebijakan teknis di bidang pendapatan daerah']);
        Tupoksi::firstOrCreate(['bidang_id' => $bPimpinan->id, 'uraian_tugas' => 'Mengoordinasikan pelaksanaan tugas seluruh bidang']);
        Tupoksi::firstOrCreate(['bidang_id' => $bPimpinan->id, 'uraian_tugas' => 'Melakukan monitoring dan evaluasi kinerja seluruh pegawai']);

        // Sekretariat (Lebih detail untuk umum dan keuangan)
        Tupoksi::firstOrCreate(['bidang_id' => $bSekretariat->id, 'uraian_tugas' => 'Melaksanakan pengelolaan surat menyurat dan kearsipan']);
        Tupoksi::firstOrCreate(['bidang_id' => $bSekretariat->id, 'uraian_tugas' => 'Melaksanakan administrasi kepegawaian dan pengembangan SDM']);
        Tupoksi::firstOrCreate(['bidang_id' => $bSekretariat->id, 'uraian_tugas' => 'Menyusun rencana anggaran dan pelaporan keuangan badan']); // Keuangan
        Tupoksi::firstOrCreate(['bidang_id' => $bSekretariat->id, 'uraian_tugas' => 'Memfasilitasi kebutuhan rumah tangga dan perlengkapan kantor']);

        // Bidang PBB dan BPHTB (Lebih detail untuk BPHTB)
        Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => 'Melaksanakan pendataan dan pendaftaran objek pajak baru']);
        Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => 'Melakukan penilaian dan penetapan besaran pajak terutang']);
        Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => 'Melaksanakan pemutakhiran data subjek dan objek pajak (SISMIOP)']);
        Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => 'Melakukan pelayanan keberatan dan pengurangan pajak']);
        Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => 'Memproses verifikasi dan validasi Bea Perolehan Hak atas Tanah dan Bangunan (BPHTB)']);

        // Bidang Dana Perimbangan (Lebih detail untuk analisis)
        Tupoksi::firstOrCreate(['bidang_id' => $bDana->id, 'uraian_tugas' => 'Melakukan rekonsiliasi data dana perimbangan dari pusat']);
        Tupoksi::firstOrCreate(['bidang_id' => $bDana->id, 'uraian_tugas' => 'Menganalisis potensi dan realisasi dana transfer daerah']);
        Tupoksi::firstOrCreate(['bidang_id' => $bDana->id, 'uraian_tugas' => 'Menyusun laporan pertanggungjawaban dana perimbangan']);
        Tupoksi::firstOrCreate(['bidang_id' => $bDana->id, 'uraian_tugas' => 'Mengidentifikasi kendala dalam penyerapan dana transfer daerah']);

        // Unit Teknologi Informasi
        Tupoksi::firstOrCreate(['bidang_id' => $bIT->id, 'uraian_tugas' => 'Memastikan operasional jaringan dan server berjalan optimal']);
        Tupoksi::firstOrCreate(['bidang_id' => $bIT->id, 'uraian_tugas' => 'Memberikan dukungan teknis dan pemeliharaan aplikasi e-Daily Report']);
        Tupoksi::firstOrCreate(['bidang_id' => $bIT->id, 'uraian_tugas' => 'Mengelola keamanan dan integritas data sistem informasi']);
    }

    /**
     * Helper: Membuat user Bidang Unsur Pimpinan (Kaban + Staff).
     */
    private function createUnsurPimpinanUsers($ukBapenda, $bPimpinan, $jKaban, $jStaf, $rKadis, $rPenilai, $rPegawai, $pass, $alamat)
    {
        // 1. KABAN (Kepala Badan)
        $kaban = User::firstOrCreate(['username' => 'kaban'], [
            'name' => 'Darius Sabon Rain (Kaban)', 'nip' => '197301032007011031', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKaban->id, 'bidang_id' => $bPimpinan->id,
            'atasan_id' => null, 'email' => 'kaban@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kaban->roles()->sync([$rKadis->id, $rPenilai->id]);

        // 2. Staf Pimpinan (Baru, untuk LKH variatif)
        $stafPimpinan = User::firstOrCreate(['username' => 'staf.pimpinan'], [
            'name' => 'Staf Protokol & Humas Pimpinan', 'nip' => '199901012023011004', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPimpinan->id,
            'atasan_id' => $kaban->id, 'email' => 'staf.protokol@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafPimpinan->roles()->sync([$rPegawai->id]);
        
        return [$kaban, $stafPimpinan];
    }


    /**
     * Helper: Membuat user Bidang Sekretariat (2 Kasubag, 2 Staf).
     */
    private function createSekretariatUsers($ukBapenda, $bSekretariat, $jSekban, $jKasub, $jStaf, $rPenilai, $rPegawai, $kaban, $pass, $alamat)
    {
        // 1. Sekretaris (Reports to Kaban)
        $sekban = User::firstOrCreate(['username' => 'sekban'], [
            'name' => 'Sekretaris Bapenda', 'nip' => '197501012000011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jSekban->id, 'bidang_id' => $bSekretariat->id,
            'atasan_id' => $kaban->id, 'email' => 'sekban@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $sekban->roles()->sync([$rPenilai->id]);

        // 2. Kasubag Umum (Reports to Sekretaris)
        $kasubUmum = User::firstOrCreate(['username' => 'kasub.umum'], [
            'name' => 'Kasubag Umum & Kepegawaian', 'nip' => '199001012015011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bSekretariat->id,
            'atasan_id' => $sekban->id, 'email' => 'kasub.umum@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kasubUmum->roles()->sync([$rPenilai->id]);

        // 3. Staf Umum (Reports to Kasubag Umum)
        $stafUmum = User::firstOrCreate(['username' => 'staf.umum'], [
            'name' => 'Staf Administrasi Umum', 'nip' => '199601012021011002', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bSekretariat->id,
            'atasan_id' => $kasubUmum->id, 'email' => 'staf.umum@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafUmum->roles()->sync([$rPegawai->id]);

        // 4. Kasubag Perencanaan & Keuangan (Reports to Sekretaris)
        $kasubKeuangan = User::firstOrCreate(['username' => 'kasub.keu'], [
            'name' => 'Kasubag Perencanaan & Keuangan', 'nip' => '198901012014011002', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bSekretariat->id,
            'atasan_id' => $sekban->id, 'email' => 'kasub.keu@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kasubKeuangan->roles()->sync([$rPenilai->id]);

        // 5. Staf Keuangan (Reports to Kasubag Keuangan)
        $stafKeuangan = User::firstOrCreate(['username' => 'staf.keu'], [
            'name' => 'Staf Administrasi Keuangan', 'nip' => '199701012022011003', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bSekretariat->id,
            'atasan_id' => $kasubKeuangan->id, 'email' => 'staf.keu@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafKeuangan->roles()->sync([$rPegawai->id]);
        
        return [$sekban, $kasubUmum, $stafUmum, $kasubKeuangan, $stafKeuangan];
    }

    /**
     * Helper: Membuat user Bidang PBB dan BPHTB (2 Kasubid, 2 Staf).
     */
    private function createPbbUsers($ukBapenda, $bPbb, $jKabid, $jKasub, $jStaf, $rPenilai, $rPegawai, $kaban, $pass, $alamat)
    {
        // 1. Kabid PBB (Reports to Kaban)
        $kabidPbb = User::firstOrCreate(['username' => 'kabid.pbb'], [
            'name' => 'Kabid PBB & BPHTB', 'nip' => '198001012005011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKabid->id, 'bidang_id' => $bPbb->id,
            'atasan_id' => $kaban->id, 'email' => 'kabid.pbb@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kabidPbb->roles()->sync([$rPenilai->id]);

        // 2. Kasubid PBB (Pendataan) (Reports to Kabid PBB)
        $kasubPbbData = User::firstOrCreate(['username' => 'kasub.pbb.data'], [
            'name' => 'Kasubid Pendataan PBB', 'nip' => '198501012010011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bPbb->id,
            'atasan_id' => $kabidPbb->id, 'email' => 'kasub.pbb.data@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kasubPbbData->roles()->sync([$rPenilai->id]);

        // 3. Staf PBB (Reports to Kasubid PBB)
        $stafPbbData = User::firstOrCreate(['username' => 'staf.pbb.data'], [
            'name' => 'Staf Pendataan PBB', 'nip' => '199501012020011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPbb->id,
            'atasan_id' => $kasubPbbData->id, 'email' => 'staf.pbb.data@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafPbbData->roles()->sync([$rPegawai->id]);

        // 4. Kasubid BPHTB (Baru)
        $kasubBphtb = User::firstOrCreate(['username' => 'kasub.bphtb'], [
            'name' => 'Kasubid Pelayanan & BPHTB', 'nip' => '198601012011011003', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bPbb->id,
            'atasan_id' => $kabidPbb->id, 'email' => 'kasub.bphtb@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kasubBphtb->roles()->sync([$rPenilai->id]);

        // 5. Staf BPHTB (Reports to Kasubid BPHTB)
        $stafBphtb = User::firstOrCreate(['username' => 'staf.bphtb'], [
            'name' => 'Staf Pelayanan BPHTB', 'nip' => '199601012021011004', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPbb->id,
            'atasan_id' => $kasubBphtb->id, 'email' => 'staf.bphtb@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafBphtb->roles()->sync([$rPegawai->id]);
        
        return [$kabidPbb, $kasubPbbData, $stafPbbData, $kasubBphtb, $stafBphtb];
    }

    /**
     * Helper: Membuat user Bidang Dana Perimbangan (2 Kasubid, 2 Staf).
     */
    private function createDanaUsers($ukBapenda, $bDana, $jKabid, $jKasub, $jStaf, $rPenilai, $rPegawai, $kaban, $pass, $alamat)
    {
        // 1. Kabid Dana (Reports to Kaban)
        $kabidDana = User::firstOrCreate(['username' => 'kabid.dana'], [
            'name' => 'Kabid Dana Perimbangan', 'nip' => '198101012005011002', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKabid->id, 'bidang_id' => $bDana->id,
            'atasan_id' => $kaban->id, 'email' => 'kabid.dana@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kabidDana->roles()->sync([$rPenilai->id]);

        // 2. Kasubid Transfer Dana (Reports to Kabid Dana)
        $kasubDanaTransfer = User::firstOrCreate(['username' => 'kasub.dana.transfer'], [
            'name' => 'Kasubid Transfer Dana', 'nip' => '198601012010011002', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bDana->id,
            'atasan_id' => $kabidDana->id, 'email' => 'kasub.dana.transfer@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kasubDanaTransfer->roles()->sync([$rPenilai->id]);

        // 3. Staf Dana Transfer (Reports to Kasubid Transfer Dana)
        $stafDanaTransfer = User::firstOrCreate(['username' => 'staf.dana.transfer'], [
            'name' => 'Staf Pengelolaan Dana Transfer', 'nip' => '199701012020011002', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bDana->id,
            'atasan_id' => $kasubDanaTransfer->id, 'email' => 'staf.dana.transfer@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafDanaTransfer->roles()->sync([$rPegawai->id]);

        // 4. Kasubid Analisis Dana (Baru)
        $kasubDanaAnalis = User::firstOrCreate(['username' => 'kasub.dana.analis'], [
            'name' => 'Kasubid Analisis & Pelaporan Dana', 'nip' => '198701012012011003', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bDana->id,
            'atasan_id' => $kabidDana->id, 'email' => 'kasub.dana.analis@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kasubDanaAnalis->roles()->sync([$rPenilai->id]);

        // 5. Staf Dana Analisis (Baru)
        $stafDanaAnalis = User::firstOrCreate(['username' => 'staf.dana.analis'], [
            'name' => 'Staf Analisis Dana Perimbangan', 'nip' => '199801012023011003', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bDana->id,
            'atasan_id' => $kasubDanaAnalis->id, 'email' => 'staf.dana.analis@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafDanaAnalis->roles()->sync([$rPegawai->id]);
        
        return [$kabidDana, $kasubDanaTransfer, $stafDanaTransfer, $kasubDanaAnalis, $stafDanaAnalis];
    }

    /**
     * Helper: Membuat user Unit Teknologi Informasi (DITAMBAH KEPALA UNIT/KABID).
     */
    private function createITUsers($ukBapenda, $bIT, $jAdmin, $jStaf, $rAdmin, $rPenilai, $rPegawai, $kaban, $pass, $alamat, $jKabid, $jKasub)
    {
        // 1. KABID/KEPALA UNIT TI (New Top position for this Bidang, Reports to Kaban)
        $kabidIT = User::firstOrCreate(['username' => 'kabid.it'], [
            'name' => 'Kepala Unit Teknologi Informasi', 'nip' => '197901012004011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKabid->id, // Kepala Bidang (ID 3)
            'bidang_id' => $bIT->id,
            'atasan_id' => $kaban->id,
            'email' => 'kabid.it@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kabidIT->roles()->sync([$rPenilai->id]); 

        // 2. Administrator Sistem (Berubah ke level Kasubid, Reports to Kabid IT)
        $adminUser = User::firstOrCreate(['username' => 'admin'], [
            'name' => 'Administrator Sistem', 'nip' => 'admin_system', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, // Kepala Sub Bagian/Bidang (ID 4)
            'bidang_id' => $bIT->id,
            'atasan_id' => $kabidIT->id, // Reports to new Kabid IT
            'email' => 'admin@bapenda.mimika.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $adminUser->roles()->sync([$rAdmin->id, $rPenilai->id]); 

        // 3. Staf IT (Reports to Admin Sistem / Kasubid)
        $stafIT = User::firstOrCreate(['username' => 'staf.it'], [
            'name' => 'Staf IT Support', 'nip' => '199801012022011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bIT->id,
            'atasan_id' => $adminUser->id, // Reports to Kasubid (Admin User)
            'email' => 'staf.it@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafIT->roles()->sync([$rPegawai->id]);
        
        return [$kabidIT, $adminUser, $stafIT];
    }
    
    /**
     * Helper: Membuat data Pengumuman.
     */
    private function seedPengumuman($kasubPbbData, $sekban)
    {
        Pengumuman::create([
            'user_id_creator' => $kasubPbbData->id,
            'unit_kerja_id'   => $kasubPbbData->unit_kerja_id,
            'judul'           => 'Rapat Evaluasi Pendataan',
            'isi_pengumuman'  => 'Besok seluruh staf pendata harap kumpul jam 08.00 WIT membawa laporan progres.',
        ]);

        Pengumuman::create([
            'user_id_creator' => $sekban->id,
            'unit_kerja_id'   => null,
            'judul'           => 'Sosialisasi Aplikasi e-Daily Report',
            'isi_pengumuman'  => 'Diberitahukan kepada seluruh pegawai untuk mulai menginput LKH melalui sistem baru mulai tanggal 1 bulan depan.',
        ]);
    }
    
    /**
     * Helper Buat SKP Paket (Header + Target)
     */
    private function createSkpPaket($user, $rhkIntervensi, $rhkPegawai, $targets)
    {
        $rencana = SkpRencana::create([
            'user_id' => $user->id,
            'periode_awal' => Carbon::now()->startOfYear(),
            'periode_akhir' => Carbon::now()->endOfYear(),
            'rhk_intervensi' => $rhkIntervensi,
            'rencana_hasil_kerja' => $rhkPegawai,
        ]);

        foreach ($targets as $t) {
            $rencana->targets()->create($t);
        }

        return $rencana;
    }

    /**
     * Helper Buat LKH (Updated Logic)
     */
    private function createLkh($user, $rencana, $status, $deskripsi, $output, $jenisKegiatan, $validatorUser = null, $komentar = null)
    {
        $isLuarLokasi = in_array($jenisKegiatan, ['Survey', 'Kunjungan Lapangan', 'Perjalanan Dinas']);
        
        // Use provided validator or the user's default atasan
        $targetAtasanId = $validatorUser ? $validatorUser->id : $user->atasan_id;

        // Ambil satuan otomatis dari target 'Kuantitas' rencana ini
        $satuan = 'Kegiatan'; // Default
        if ($rencana) {
            $targetKuantitas = $rencana->targets()->where('jenis_aspek', 'Kuantitas')->first();
            if ($targetKuantitas) {
                $satuan = $targetKuantitas->satuan;
            }
        }

        // Ambil Tupoksi secara acak dari bidang user
        $tupoksi = Tupoksi::where('bidang_id', $user->bidang_id)->inRandomOrder()->first();
        $tupoksiId = $tupoksi ? $tupoksi->id : null;

        return LaporanHarian::create([
            'user_id'             => $user->id,
            'skp_rencana_id'      => $rencana ? $rencana->id : null, 
            'tupoksi_id'          => $tupoksiId, 
            'tanggal_laporan'     => Carbon::today()->format('Y-m-d'),
            'waktu_mulai'         => '08:00:00',
            'waktu_selesai'       => '16:00:00',
            'deskripsi_aktivitas' => $deskripsi,
            'output_hasil_kerja'  => $output,
            'jenis_kegiatan'      => $jenisKegiatan,
            'volume'              => rand(1, 5),
            'satuan'              => $satuan,
            'status'              => $status,
            'lokasi'              => DB::raw("ST_GeomFromText('POINT(136.8851 -4.5461)')"),
            'is_luar_lokasi'      => $isLuarLokasi,
            'atasan_id'           => $targetAtasanId,
            'waktu_validasi'      => ($status !== 'waiting_review') ? Carbon::now() : null,
            'komentar_validasi'   => $komentar, 
        ]);
    }
}