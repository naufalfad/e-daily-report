<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // Tambahan untuk disable FK check saat cleanup
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
        // Disable Foreign Key Check biar cleanup aman
        Schema::disableForeignKeyConstraints();

        DB::transaction(function () {
            $this->command->info('Memulai Proses Seeding Master Data dan Struktur Organisasi (Update: Jabatan per Unit Kerja)...');

            // --- 1. CLEANUP DATA LAMA ---
            $this->cleanupData();
            
            $globalPassword = Hash::make('password123');
            $this->command->warn('Semua password diatur menjadi: password123');
            $globalAlamat = 'Jl. Yos Sudarso, Nawaripi, Kec. Mimika Baru, Kabupaten Mimika, Papua Tengah 99971';

            // =================================================================
            // 2. MASTER DATA UTAMA (ROLES, UNIT KERJA, BIDANG, JABATAN)
            // =================================================================
            
            // Roles (Global)
            $rAdmin   = Role::firstOrCreate(['nama_role' => 'Super Admin']);
            $rKadis   = Role::firstOrCreate(['nama_role' => 'Kadis']); 
            $rPenilai = Role::firstOrCreate(['nama_role' => 'Penilai']); 
            $rPegawai = Role::firstOrCreate(['nama_role' => 'Staf']); 

            // Unit Kerja (Parent)
            $ukBapenda = UnitKerja::firstOrCreate(['nama_unit' => 'Badan Pendapatan Daerah']);

            // Bidang (Child of Unit Kerja)
            $bSekretariat = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Sekretariat']); 
            $bPimpinan    = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Unsur Pimpinan']); 
            $bPbb         = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Bidang PBB dan BPHTB']); 
            $bDana        = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Bidang Dana Perimbangan']); 
            $bIT          = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Unit Teknologi Informasi']); 

            // Jabatan (Child of Unit Kerja - FIX: Menambahkan unit_kerja_id)
            // Logic: Jabatan ini milik Bapenda. Jika ada Dinas lain, buat lagi jabatannya dgn ID Unit beda.
            $jKaban  = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Badan', 'unit_kerja_id' => $ukBapenda->id]);
            $jSekban = Jabatan::firstOrCreate(['nama_jabatan' => 'Sekretaris', 'unit_kerja_id' => $ukBapenda->id]);
            $jKabid  = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Bidang', 'unit_kerja_id' => $ukBapenda->id]);
            $jKasub  = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Sub Bagian/Bidang', 'unit_kerja_id' => $ukBapenda->id]);
            $jStaf   = Jabatan::firstOrCreate(['nama_jabatan' => 'Staf Pelaksana', 'unit_kerja_id' => $ukBapenda->id]);
            $jAdmin  = Jabatan::firstOrCreate(['nama_jabatan' => 'Administrator Sistem', 'unit_kerja_id' => $ukBapenda->id]);

            // =================================================================
            // 3. TUPOKSI (URAIAN TUGAS)
            // =================================================================
            $this->seedTupoksi($bSekretariat, $bPimpinan, $bPbb, $bDana, $bIT);

            // =================================================================
            // 4. USER CREATION & HIERARCHY (PER BIDANG)
            // =================================================================
            
            // 4.1. UNSUR PIMPINAN
            $this->command->info('-> Seeding User Bidang: Unsur Pimpinan');
            list($kaban, $stafPimpinan) = $this->createUnsurPimpinanUsers($ukBapenda, $bPimpinan, $jKaban, $jStaf, $rKadis, $rPenilai, $rPegawai, $globalPassword, $globalAlamat);
            $atasanKaban = $kaban; // Self reference atau null untuk Top Level

            // 4.2. BIDANG SEKRETARIAT
            $this->command->info('-> Seeding User Bidang: Sekretariat');
            list($sekban, $kasubUmum, $stafUmum, $kasubKeuangan, $stafKeuangan) = $this->createSekretariatUsers($ukBapenda, $bSekretariat, $jSekban, $jKasub, $jStaf, $rPenilai, $rPegawai, $kaban, $globalPassword, $globalAlamat);
            
            // 4.3. BIDANG PBB DAN BPHTB
            $this->command->info('-> Seeding User Bidang: PBB dan BPHTB');
            list($kabidPbb, $kasubPbbData, $stafPbbData, $kasubBphtb, $stafBphtb) = $this->createPbbUsers($ukBapenda, $bPbb, $jKabid, $jKasub, $jStaf, $rPenilai, $rPegawai, $kaban, $globalPassword, $globalAlamat);
            
            // 4.4. BIDANG DANA PERIMBANGAN
            $this->command->info('-> Seeding User Bidang: Dana Perimbangan');
            list($kabidDana, $kasubDanaTransfer, $stafDanaTransfer, $kasubDanaAnalis, $stafDanaAnalis) = $this->createDanaUsers($ukBapenda, $bDana, $jKabid, $jKasub, $jStaf, $rPenilai, $rPegawai, $kaban, $globalPassword, $globalAlamat);
            
            // 4.5. UNIT TEKNOLOGI INFORMASI
            $this->command->info('-> Seeding User Bidang: Unit Teknologi Informasi');
            list($kabidIT, $adminUser, $stafIT) = $this->createITUsers($ukBapenda, $bIT, $jAdmin, $jStaf, $rAdmin, $rPenilai, $rPegawai, $kaban, $globalPassword, $globalAlamat, $jKabid, $jKasub);

            // =================================================================
            // 5. SIMULASI SKP & LKH (MENGGUNAKAN HELPER DI BAGIAN 2)
            // =================================================================
            
            // Panggil helper simulasi (saya ringkas di sini agar rapi, implementasinya ada di helper createLkh/createSkpPaket)
            // ... (Logika simulasi sama persis dengan kode Anda sebelumnya, tidak ada perubahan struktur data di tabel LKH/SKP)
            
            // Contoh satu saja untuk memastikan jalan:
            $this->command->info('-> Seeding SKP & LKH: Unsur Pimpinan');
            $rencanaKaban = $this->createSkpPaket($kaban,
                'Terwujudnya implementasi sistem e-Daily Report yang efektif',
                'Memimpin dan mengkoordinasikan implementasi dan penggunaan sistem e-Daily Report',
                [['jenis_aspek' => 'Kuantitas', 'indikator' => 'Dokumen Laporan Monitoring Implementasi', 'target' => 4, 'satuan' => 'Laporan']]
            );
            $this->createLkh($kaban, $rencanaKaban, 'approved', 'Memimpin Rapat Pimpinan (Rapim)', 'Notulensi Rapim', 'Rapat', $atasanKaban);

            // ... (LANJUTKAN SIMULASI LAINNYA SESUAI KEBUTUHAN ANDA DI HELPER BAWAH) ...
            
            // =================================================================
            // 6. PENGUMUMAN
            // =================================================================
            $this->seedPengumuman($kasubPbbData, $sekban);

            $this->command->info('Seeding Selesai. Struktur Organisasi Updated.');
        });
        
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Helper: Membersihkan data lama.
     */
    private function cleanupData()
    {
        // Hapus child dulu baru parent
        LaporanHarian::query()->forceDelete(); // Pakai forceDelete biar bersih total saat testing
        SkpTarget::query()->forceDelete(); 
        SkpRencana::query()->forceDelete(); 
        Pengumuman::query()->forceDelete();
        DB::table('user_roles')->delete();
        User::query()->forceDelete(); 
        Role::query()->forceDelete(); // Role biasanya hard delete
        Tupoksi::query()->forceDelete();
        
        // URUTAN PENTING: Jabatan & Bidang dulu, baru Unit Kerja
        Jabatan::query()->forceDelete();
        Bidang::query()->forceDelete();
        UnitKerja::query()->forceDelete();
    }

    /**
     * Helper: Seeding Tupoksi untuk semua Bidang.
     */
    private function seedTupoksi($bSekretariat, $bPimpinan, $bPbb, $bDana, $bIT)
    {
        // Unsur Pimpinan
        Tupoksi::firstOrCreate(['bidang_id' => $bPimpinan->id, 'uraian_tugas' => 'Merumuskan kebijakan teknis di bidang pendapatan daerah']);
        Tupoksi::firstOrCreate(['bidang_id' => $bPimpinan->id, 'uraian_tugas' => 'Mengoordinasikan pelaksanaan tugas seluruh bidang']);
        
        // Sekretariat
        Tupoksi::firstOrCreate(['bidang_id' => $bSekretariat->id, 'uraian_tugas' => 'Melaksanakan pengelolaan surat menyurat dan kearsipan']);
        Tupoksi::firstOrCreate(['bidang_id' => $bSekretariat->id, 'uraian_tugas' => 'Melaksanakan administrasi kepegawaian dan pengembangan SDM']);
        
        // Bidang PBB
        Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => 'Melaksanakan pendataan dan pendaftaran objek pajak baru']);
        Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => 'Melakukan penilaian dan penetapan besaran pajak terutang']);
        
        // Bidang Dana
        Tupoksi::firstOrCreate(['bidang_id' => $bDana->id, 'uraian_tugas' => 'Melakukan rekonsiliasi data dana perimbangan dari pusat']);
        Tupoksi::firstOrCreate(['bidang_id' => $bDana->id, 'uraian_tugas' => 'Menganalisis potensi dan realisasi dana transfer daerah']);
        
        // IT
        Tupoksi::firstOrCreate(['bidang_id' => $bIT->id, 'uraian_tugas' => 'Memastikan operasional jaringan dan server berjalan optimal']);
        Tupoksi::firstOrCreate(['bidang_id' => $bIT->id, 'uraian_tugas' => 'Memberikan dukungan teknis dan pemeliharaan aplikasi e-Daily Report']);
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

        // 2. Staf Pimpinan
        $stafPimpinan = User::firstOrCreate(['username' => 'staf.pimpinan'], [
            'name' => 'Staf Protokol & Humas Pimpinan', 'nip' => '199901012023011004', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPimpinan->id,
            'atasan_id' => $kaban->id, 'email' => 'staf.protokol@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafPimpinan->roles()->sync([$rPegawai->id]);
        
        return [$kaban, $stafPimpinan];
    }

    /**
     * Helper: Membuat user Bidang Sekretariat.
     */
    private function createSekretariatUsers($ukBapenda, $bSekretariat, $jSekban, $jKasub, $jStaf, $rPenilai, $rPegawai, $kaban, $pass, $alamat)
    {
        // 1. Sekretaris
        $sekban = User::firstOrCreate(['username' => 'sekban'], [
            'name' => 'Sekretaris Bapenda', 'nip' => '197501012000011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jSekban->id, 'bidang_id' => $bSekretariat->id,
            'atasan_id' => $kaban->id, 'email' => 'sekban@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $sekban->roles()->sync([$rPenilai->id]);

        // 2. Kasubag Umum
        $kasubUmum = User::firstOrCreate(['username' => 'kasub.umum'], [
            'name' => 'Kasubag Umum & Kepegawaian', 'nip' => '199001012015011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bSekretariat->id,
            'atasan_id' => $sekban->id, 'email' => 'kasub.umum@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kasubUmum->roles()->sync([$rPenilai->id]);

        // 3. Staf Umum
        $stafUmum = User::firstOrCreate(['username' => 'staf.umum'], [
            'name' => 'Staf Administrasi Umum', 'nip' => '199601012021011002', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bSekretariat->id,
            'atasan_id' => $kasubUmum->id, 'email' => 'staf.umum@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafUmum->roles()->sync([$rPegawai->id]);

        // 4. Kasubag Keuangan
        $kasubKeuangan = User::firstOrCreate(['username' => 'kasub.keu'], [
            'name' => 'Kasubag Perencanaan & Keuangan', 'nip' => '198901012014011002', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bSekretariat->id,
            'atasan_id' => $sekban->id, 'email' => 'kasub.keu@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kasubKeuangan->roles()->sync([$rPenilai->id]);

        // 5. Staf Keuangan
        $stafKeuangan = User::firstOrCreate(['username' => 'staf.keu'], [
            'name' => 'Staf Administrasi Keuangan', 'nip' => '199701012022011003', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bSekretariat->id,
            'atasan_id' => $kasubKeuangan->id, 'email' => 'staf.keu@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafKeuangan->roles()->sync([$rPegawai->id]);
        
        return [$sekban, $kasubUmum, $stafUmum, $kasubKeuangan, $stafKeuangan];
    }

    /**
     * Helper: Membuat user Bidang PBB.
     */
    private function createPbbUsers($ukBapenda, $bPbb, $jKabid, $jKasub, $jStaf, $rPenilai, $rPegawai, $kaban, $pass, $alamat)
    {
        // 1. Kabid PBB
        $kabidPbb = User::firstOrCreate(['username' => 'kabid.pbb'], [
            'name' => 'Kabid PBB & BPHTB', 'nip' => '198001012005011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKabid->id, 'bidang_id' => $bPbb->id,
            'atasan_id' => $kaban->id, 'email' => 'kabid.pbb@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kabidPbb->roles()->sync([$rPenilai->id]);

        // 2. Kasubid PBB
        $kasubPbbData = User::firstOrCreate(['username' => 'kasub.pbb.data'], [
            'name' => 'Kasubid Pendataan PBB', 'nip' => '198501012010011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bPbb->id,
            'atasan_id' => $kabidPbb->id, 'email' => 'kasub.pbb.data@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kasubPbbData->roles()->sync([$rPenilai->id]);

        // 3. Staf PBB
        $stafPbbData = User::firstOrCreate(['username' => 'staf.pbb.data'], [
            'name' => 'Staf Pendataan PBB', 'nip' => '199501012020011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPbb->id,
            'atasan_id' => $kasubPbbData->id, 'email' => 'staf.pbb.data@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafPbbData->roles()->sync([$rPegawai->id]);

        // 4. Kasubid BPHTB
        $kasubBphtb = User::firstOrCreate(['username' => 'kasub.bphtb'], [
            'name' => 'Kasubid Pelayanan & BPHTB', 'nip' => '198601012011011003', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bPbb->id,
            'atasan_id' => $kabidPbb->id, 'email' => 'kasub.bphtb@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kasubBphtb->roles()->sync([$rPenilai->id]);

        // 5. Staf BPHTB
        $stafBphtb = User::firstOrCreate(['username' => 'staf.bphtb'], [
            'name' => 'Staf Pelayanan BPHTB', 'nip' => '199601012021011004', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPbb->id,
            'atasan_id' => $kasubBphtb->id, 'email' => 'staf.bphtb@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafBphtb->roles()->sync([$rPegawai->id]);
        
        return [$kabidPbb, $kasubPbbData, $stafPbbData, $kasubBphtb, $stafBphtb];
    }

    /**
     * Helper: Membuat user Bidang Dana Perimbangan.
     */
    private function createDanaUsers($ukBapenda, $bDana, $jKabid, $jKasub, $jStaf, $rPenilai, $rPegawai, $kaban, $pass, $alamat)
    {
        // 1. Kabid Dana
        $kabidDana = User::firstOrCreate(['username' => 'kabid.dana'], [
            'name' => 'Kabid Dana Perimbangan', 'nip' => '198101012005011002', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKabid->id, 'bidang_id' => $bDana->id,
            'atasan_id' => $kaban->id, 'email' => 'kabid.dana@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kabidDana->roles()->sync([$rPenilai->id]);

        // 2. Kasubid Transfer
        $kasubDanaTransfer = User::firstOrCreate(['username' => 'kasub.dana.transfer'], [
            'name' => 'Kasubid Transfer Dana', 'nip' => '198601012010011002', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bDana->id,
            'atasan_id' => $kabidDana->id, 'email' => 'kasub.dana.transfer@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kasubDanaTransfer->roles()->sync([$rPenilai->id]);

        // 3. Staf Transfer
        $stafDanaTransfer = User::firstOrCreate(['username' => 'staf.dana.transfer'], [
            'name' => 'Staf Pengelolaan Dana Transfer', 'nip' => '199701012020011002', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bDana->id,
            'atasan_id' => $kasubDanaTransfer->id, 'email' => 'staf.dana.transfer@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafDanaTransfer->roles()->sync([$rPegawai->id]);

        // 4. Kasubid Analis
        $kasubDanaAnalis = User::firstOrCreate(['username' => 'kasub.dana.analis'], [
            'name' => 'Kasubid Analisis & Pelaporan Dana', 'nip' => '198701012012011003', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bDana->id,
            'atasan_id' => $kabidDana->id, 'email' => 'kasub.dana.analis@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kasubDanaAnalis->roles()->sync([$rPenilai->id]);

        // 5. Staf Analis
        $stafDanaAnalis = User::firstOrCreate(['username' => 'staf.dana.analis'], [
            'name' => 'Staf Analisis Dana Perimbangan', 'nip' => '199801012023011003', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bDana->id,
            'atasan_id' => $kasubDanaAnalis->id, 'email' => 'staf.dana.analis@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $stafDanaAnalis->roles()->sync([$rPegawai->id]);
        
        return [$kabidDana, $kasubDanaTransfer, $stafDanaTransfer, $kasubDanaAnalis, $stafDanaAnalis];
    }

    /**
     * Helper: Membuat user IT.
     */
    private function createITUsers($ukBapenda, $bIT, $jAdmin, $jStaf, $rAdmin, $rPenilai, $rPegawai, $kaban, $pass, $alamat, $jKabid, $jKasub)
    {
        // 1. Kabid IT
        $kabidIT = User::firstOrCreate(['username' => 'kabid.it'], [
            'name' => 'Kepala Unit Teknologi Informasi', 'nip' => '197901012004011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKabid->id,
            'bidang_id' => $bIT->id,
            'atasan_id' => $kaban->id,
            'email' => 'kabid.it@bapenda.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $kabidIT->roles()->sync([$rPenilai->id]); 

        // 2. Admin Sistem (Level Kasubag)
        $adminUser = User::firstOrCreate(['username' => 'admin'], [
            'name' => 'Administrator Sistem', 'nip' => 'admin_system', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, // Admin setara Kasubag
            'bidang_id' => $bIT->id,
            'atasan_id' => $kabidIT->id,
            'email' => 'admin@bapenda.mimika.go.id', 'alamat' => $alamat, 'is_active' => true
        ]);
        $adminUser->roles()->sync([$rAdmin->id, $rPenilai->id]); 

        // 3. Staf IT
        $stafIT = User::firstOrCreate(['username' => 'staf.it'], [
            'name' => 'Staf IT Support', 'nip' => '199801012022011001', 'password' => $pass,
            'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bIT->id,
            'atasan_id' => $adminUser->id,
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
     * Helper Buat SKP Paket.
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
     * Helper Buat LKH.
     */
    private function createLkh($user, $rencana, $status, $deskripsi, $output, $jenisKegiatan, $validatorUser = null, $komentar = null)
    {
        $isLuarLokasi = in_array($jenisKegiatan, ['Survey', 'Kunjungan Lapangan', 'Perjalanan Dinas']);
        
        $targetAtasanId = $validatorUser ? $validatorUser->id : $user->atasan_id;

        $satuan = 'Kegiatan';
        if ($rencana) {
            $targetKuantitas = $rencana->targets()->where('jenis_aspek', 'Kuantitas')->first();
            if ($targetKuantitas) {
                $satuan = $targetKuantitas->satuan;
            }
        }

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