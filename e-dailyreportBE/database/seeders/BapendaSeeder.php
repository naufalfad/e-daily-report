<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\Bidang;
use App\Models\Tupoksi;

class BapendaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {

            //hapus seeder
            User::query()->delete(); 
            Role::query()->delete();
            Jabatan::query()->delete();
            Tupoksi::query()->delete();
            Bidang::query()->delete();
            UnitKerja::query()->delete();

            
            // =================================================================
            // 1. BUAT ROLES
            // =================================================================
            $rAdmin   = Role::firstOrCreate(['nama_role' => 'Super Admin']);
            $rKadis   = Role::firstOrCreate(['nama_role' => 'Kadis']); // Kepala Badan
            $rPenilai = Role::firstOrCreate(['nama_role' => 'Penilai']); // Sekban, Kabid, Kasub
            $rPegawai = Role::firstOrCreate(['nama_role' => 'Pegawai']); // Staf

            // =================================================================
            // 2. BUAT JABATAN
            // =================================================================
            $jKaban   = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Badan']);
            $jSekban  = Jabatan::firstOrCreate(['nama_jabatan' => 'Sekretaris']);
            $jKabid   = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Bidang']);
            $jKasub   = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Sub Bagian/Bidang']);
            $jStaf    = Jabatan::firstOrCreate(['nama_jabatan' => 'Staf Pelaksana']);
            $jAjudan  = Jabatan::firstOrCreate(['nama_jabatan' => 'Ajudan/Staf Pimpinan']);

            // =================================================================
            // 3. BUAT UNIT KERJA UTAMA
            // =================================================================
            $ukBapenda = UnitKerja::firstOrCreate(['nama_unit' => 'Badan Pendapatan Daerah']);

            // =================================================================
            // 4. BUAT BIDANG (Sesuai Dokumen DARIUS)
            // =================================================================
            $bPimpinan    = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Unsur Pimpinan & Staf Khusus']);
            $bSekretariat = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Sekretariat']);
            $bPbb         = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Bidang PBB dan BPHTB']);
            $bPajak       = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Bidang Pajak']);
            $bDana        = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Bidang Dana Perimbangan & Lain-lain Pendapatan']);
            $bBuku        = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Bidang Pembukuan dan Pelaporan']);

            // =================================================================
            // 5. BUAT TUPOKSI (Lengkap Sesuai Dokumen)
            // =================================================================

            // --- TUPOKSI KEPALA BADAN ---
            $tupoksiKaban = [
                'Penyelenggaraan perumusan, penetapan, pengaturan dan koordinasi pelaksanaan kebijakan teknis Pendapatan daerah',
                'Penyelenggaraan penyusunan dan pelaksanaan kebijakan Pendapatan daerah',
                'Penyelenggaraan pemberian saran dan pertimbangan kepada Bupati',
                'Penyelenggaraan urusan kesekretariatan',
                'Penyelenggaraan koordinasi dan kerjasama dalam rangka tugas dan fungsi Badan',
                'Pelaksanaan tugas lain yang diberikan oleh Bupati'
            ];
            foreach ($tupoksiKaban as $t) { Tupoksi::firstOrCreate(['bidang_id' => $bPimpinan->id, 'uraian_tugas' => $t]); }
            
            Tupoksi::firstOrCreate(['bidang_id' => $bPimpinan->id, 'uraian_tugas' => 'Mendampingi pimpinan dalam pelaksanaan tugas kedinasan']);

            // --- TUPOKSI SEKRETARIAT ---
            $tupoksiSekretariat = [
                'Penyusunan rencana kebijakan teknis program pembinaan dan pelayanan administrasi umum',
                'Pengoordinasian penyelenggaraan tugas pelayanan administrasi umum, kepegawaian dan keuangan',
                'Pengelolaan kegiatan pelayanan administrasi umum, kepegawaian dan administrasi keuangan',
                'Pengawasan atas pelaksanaan kegiatan administrasi umum dan perencanaan',
                'Pelaksanaan monitoring, evaluasi dan pelaporan kegiatan',
                'Penginventarisasian permasalahan tugas sekretariat dan penyiapan tindak lanjut'
            ];
            foreach ($tupoksiSekretariat as $t) { Tupoksi::firstOrCreate(['bidang_id' => $bSekretariat->id, 'uraian_tugas' => $t]); }

            // --- TUPOKSI BIDANG PBB & BPHTB ---
            $tupoksiPbb = [
                'Penyusunan rencana dan program kerja Bidang PBB dan BPHTB',
                'Pelaksanaan perencanaan, pengendalian dan operasional di bidang pendapatan PBB dan BPHTB',
                'Penyelenggaraan penatausahaan dan administrasi pendapatan PBB dan BPHTB',
                'Pelaksanaan pendaftaran, pendataan, penilaian dan penetapan objek pajak PBB',
                'Perumusan kebijakan teknis pemberian bimbingan dan pembinaan aparatur PBB dan BPHTB',
                'Pelaksanaan koordinasi terkait pemungutan BPHTB dengan instansi terkait (BPN/Notaris)'
            ];
            foreach ($tupoksiPbb as $t) { Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => $t]); }

            // --- TUPOKSI BIDANG PAJAK ---
            $tupoksiPajak = [
                'Penyusunan rencana dan program kerja Bidang Pajak Daerah',
                'Penyelenggaraan perumusan kebijakan teknis pendataan dan pendaftaran pajak',
                'Penyelenggaraan kebijakan teknis operasional penetapan dan penagihan pajak daerah',
                'Penyelenggaraan koordinasi, integrasi dan sinkronisasi sesuai tugas pokok dan fungsi',
                'Penyelenggaraan monitoring, evaluasi dan pelaporan serta capaian kinerja pajak'
            ];
            foreach ($tupoksiPajak as $t) { Tupoksi::firstOrCreate(['bidang_id' => $bPajak->id, 'uraian_tugas' => $t]); }

            // --- TUPOKSI BIDANG DANA PERIMBANGAN ---
            $tupoksiDana = [
                'Penyusunan rencana dan program kerja Bidang Dana Perimbangan',
                'Penyiapan dan perumusan bahan petunjuk teknis di bidang Dana Perimbangan',
                'Pelaksanaan intensifikasi Dana Perimbangan dan Lain Lain Pendapatan Daerah yang sah',
                'Penyusunan regulasi tentang Dana Perimbangan dan Lain Lain Pendapatan Daerah',
                'Pengoordinasian pelaksanaan kegiatan Bidang Dana Perimbangan',
                'Pengawasan, pemantauan dan evaluasi pelaksanaan Dana Perimbangan'
            ];
            foreach ($tupoksiDana as $t) { Tupoksi::firstOrCreate(['bidang_id' => $bDana->id, 'uraian_tugas' => $t]); }

            // --- TUPOKSI BIDANG PEMBUKUAN ---
            $tupoksiBuku = [
                'Penyusunan rencana dan program kerja Bidang Pembukuan dan Pelaporan',
                'Penyiapan dan perumusan bahan petunjuk teknis di bidang pembukuan dan pelaporan',
                'Penyusunan regulasi tentang pembukuan dan pelaporan',
                'Pengoordinasian pelaksanaan kegiatan bidang pembukuan dan pelaporan',
                'Pelaksanaan dan pembinaan pembukuan dan pelaporan',
                'Pengawasan, pemantauan dan evaluasi pelaksanaan pembukuan dan pelaporan'
            ];
            foreach ($tupoksiBuku as $t) { Tupoksi::firstOrCreate(['bidang_id' => $bBuku->id, 'uraian_tugas' => $t]); }

            // =================================================================
            // 6. BUAT USER & HIERARKI (KOREKSI SESUAI TITAH PADUKA)
            // =================================================================
            $pass = Hash::make('password');

            // --- SUPER ADMIN ---
            $admin = User::firstOrCreate(
                ['email' => 'admin@bapenda.go.id'],
                ['name' => 'Super Admin', 'nip' => '00001', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bSekretariat->id]
            );
            $admin->roles()->sync([$rAdmin->id]);

            // --- LEVEL 1: KEPALA BADAN ---
            $kaban = User::firstOrCreate(
                ['email' => 'darius.rain@bapenda.go.id'],
                // [PERBAIKAN] Mengganti password 'password123' menjadi $pass
                ['name' => 'Darius Sabon Rain, SE, M.Ec.Dev.', 'nip' => '197301032007011031', 'password' => 'password123', 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKaban->id, 'bidang_id' => $bPimpinan->id, 'atasan_id' => null]
            );
            $kaban->roles()->sync([$rKadis->id, $rPenilai->id]);

                // 1.1.1. Staff Kepala Badan (Ajudan) -> Melapor ke Kaban
                $staffKaban = User::firstOrCreate(
                    ['email' => 'staf.kaban@bapenda.go.id'],
                    ['name' => 'Staf Khusus Kaban (Ajudan)', 'nip' => '11111', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jAjudan->id, 'bidang_id' => $bPimpinan->id, 'atasan_id' => $kaban->id]
                );
                $staffKaban->roles()->sync([$rPegawai->id]);

                // [BARU] 1.1.2. Staff Fungsional Kaban -> Melapor ke Kaban
                $staffFungsional = User::firstOrCreate(
                    ['email' => 'staf.fungsional.kaban@bapenda.go.id'],
                    [
                        'name' => 'Staf Fungsional (Kaban)', 
                        'nip' => '11112', // NIP Fungsional
                        'password' => $pass, 
                        'unit_kerja_id' => $ukBapenda->id, 
                        'jabatan_id' => $jStaf->id, // Jabatan-nya adalah 'Staf Pelaksana'
                        'bidang_id' => $bPimpinan->id, // Masuk di bidang Pimpinan
                        'atasan_id' => $kaban->id // Melapor langsung ke Kaban
                    ]
                );
                $staffFungsional->roles()->sync([$rPegawai->id]);


            // --- LEVEL 2: SEKRETARIAT ---
            $sekban = User::firstOrCreate(
                ['email' => 'sekban@bapenda.go.id'],
                ['name' => 'Sekretaris Bapenda', 'nip' => '22222', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jSekban->id, 'bidang_id' => $bSekretariat->id, 'atasan_id' => $kaban->id]
            );
            $sekban->roles()->sync([$rPenilai->id]);

                // 1.2.1. Staff Sekban -> Melapor ke Sekban
                $staffSekban = User::firstOrCreate(
                    ['email' => 'staf.sekban@bapenda.go.id'],
                    ['name' => 'Staf Administrasi Sekban', 'nip' => '22222-A', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bSekretariat->id, 'atasan_id' => $sekban->id]
                );
                $staffSekban->roles()->sync([$rPegawai->id]);

                // --- Kasubag di bawah Sekban ---
                // 1) Kasubag Umum & Kepegawaian
                $kasubUmum = User::firstOrCreate(
                    ['email' => 'kasub.umum@bapenda.go.id'],
                    ['name' => 'Kasubag Umum & Kepegawaian', 'nip' => '22222-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bSekretariat->id, 'atasan_id' => $sekban->id]
                );
                $kasubUmum->roles()->sync([$rPenilai->id]);
                    // Staff Kasubag Umum
                    $stafUmum = User::firstOrCreate(
                        ['email' => 'staf.umum@bapenda.go.id'],
                        ['name' => 'Staf Umum', 'nip' => '22222-1-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bSekretariat->id, 'atasan_id' => $kasubUmum->id]
                    );
                    $stafUmum->roles()->sync([$rPegawai->id]);

                // 2) Kasubag Keuangan
                $kasubKeuangan = User::firstOrCreate(
                    ['email' => 'kasub.keuangan@bapenda.go.id'],
                    ['name' => 'Kasubag Keuangan', 'nip' => '22222-2', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bSekretariat->id, 'atasan_id' => $sekban->id]
                );
                $kasubKeuangan->roles()->sync([$rPenilai->id]);
                    // Staff Kasubag Keuangan
                    $stafKeuangan = User::firstOrCreate(
                        ['email' => 'staf.keuangan@bapenda.go.id'],
                        ['name' => 'Staf Keuangan', 'nip' => '22222-2-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bSekretariat->id, 'atasan_id' => $kasubKeuangan->id]
                    );
                    $stafKeuangan->roles()->sync([$rPegawai->id]);

                // 3) Kasubag Program
                $kasubProgram = User::firstOrCreate(
                    ['email' => 'kasub.program@bapenda.go.id'],
                    ['name' => 'Kasubag Program', 'nip' => '22222-3', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bSekretariat->id, 'atasan_id' => $sekban->id]
                );
                $kasubProgram->roles()->sync([$rPenilai->id]);
                    // Staff Kasubag Program
                    $stafProgram = User::firstOrCreate(
                        ['email' => 'staf.program@bapenda.go.id'],
                        ['name' => 'Staf Program', 'nip' => '22222-3-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bSekretariat->id, 'atasan_id' => $kasubProgram->id]
                    );
                    $stafProgram->roles()->sync([$rPegawai->id]);

            // --- LEVEL 2: BIDANG PBB & BPHTB ---
            $kabidPbb = User::firstOrCreate(
                ['email' => 'kabid.pbb@bapenda.go.id'],
                ['name' => 'Kabid PBB & BPHTB', 'nip' => '33333', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKabid->id, 'bidang_id' => $bPbb->id, 'atasan_id' => $kaban->id]
            );
            $kabidPbb->roles()->sync([$rPenilai->id]);

                // 1.3.1.1. Staff Kabid PBB -> Melapor ke Kabid PBB
                $stafKabidPbb = User::firstOrCreate(
                    ['email' => 'staf.kabid.pbb@bapenda.go.id'],
                    ['name' => 'Staf Administrasi Kabid PBB', 'nip' => '33333-A', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPbb->id, 'atasan_id' => $kabidPbb->id]
                );
                $stafKabidPbb->roles()->sync([$rPegawai->id]);

                // --- Kasubid di bawah Kabid PBB ---
                // 1) Kasubid Pendataan PBB
                $kasubPbbData = User::firstOrCreate(
                    ['email' => 'kasub.pbb.data@bapenda.go.id'],
                    ['name' => 'Kasubid Pendataan & Pendaftaran PBB', 'nip' => '33333-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bPbb->id, 'atasan_id' => $kabidPbb->id]
                );
                $kasubPbbData->roles()->sync([$rPenilai->id]);
                    // Staff Kasubid Pendataan PBB
                    $stafPbbData = User::firstOrCreate(
                        ['email' => 'staf.pbb.data@bapenda.go.id'],
                        ['name' => 'Staf Pendataan PBB', 'nip' => '33333-1-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPbb->id, 'atasan_id' => $kasubPbbData->id]
                    );
                    $stafPbbData->roles()->sync([$rPegawai->id]);
                
                // 2) Kasubid Penilaian PBB
                $kasubPbbNilai = User::firstOrCreate(
                    ['email' => 'kasub.pbb.nilai@bapenda.go.id'],
                    ['name' => 'Kasubid Penilaian & Penetapan PBB', 'nip' => '33333-2', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bPbb->id, 'atasan_id' => $kabidPbb->id]
                );
                $kasubPbbNilai->roles()->sync([$rPenilai->id]);
                    // Staff Kasubid Penilaian PBB
                    $stafPbbNilai = User::firstOrCreate(
                        ['email' => 'staf.pbb.nilai@bapenda.go.id'],
                        ['name' => 'Staf Penilaian PBB', 'nip' => '33333-2-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPbb->id, 'atasan_id' => $kasubPbbNilai->id]
                    );
                    $stafPbbNilai->roles()->sync([$rPegawai->id]);
                
                // 3) Kasubid Penagihan PBB
                $kasubPbbTagih = User::firstOrCreate(
                    ['email' => 'kasub.pbb.tagih@bapenda.go.id'],
                    ['name' => 'Kasubid Penagihan Restitusi PBB', 'nip' => '33333-3', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bPbb->id, 'atasan_id' => $kabidPbb->id]
                );
                $kasubPbbTagih->roles()->sync([$rPenilai->id]);
                    // Staff Kasubid Penagihan PBB
                    $stafPbbTagih = User::firstOrCreate(
                        ['email' => 'staf.pbb.tagih@bapenda.go.id'],
                        ['name' => 'Staf Penagihan PBB', 'nip' => '33333-3-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPbb->id, 'atasan_id' => $kasubPbbTagih->id]
                    );
                    $stafPbbTagih->roles()->sync([$rPegawai->id]);

            // --- LEVEL 2: BIDANG PAJAK ---
            $kabidPajak = User::firstOrCreate(
                ['email' => 'kabid.pajak@bapenda.go.id'],
                ['name' => 'Kabid Pajak', 'nip' => '44444', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKabid->id, 'bidang_id' => $bPajak->id, 'atasan_id' => $kaban->id]
            );
            $kabidPajak->roles()->sync([$rPenilai->id]);

                // 1.3.1.1. Staff Kabid Pajak -> Melapor ke Kabid Pajak
                $stafKabidPajak = User::firstOrCreate(
                    ['email' => 'staf.kabid.pajak@bapenda.go.id'],
                    ['name' => 'Staf Administrasi Kabid Pajak', 'nip' => '44444-A', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPajak->id, 'atasan_id' => $kabidPajak->id]
                );
                $stafKabidPajak->roles()->sync([$rPegawai->id]);
            
                // --- Kasubid di bawah Kabid Pajak ---
                // 1) Kasubid Pendataan Pajak
                $kasubPajakData = User::firstOrCreate(
                    ['email' => 'kasub.pajak.data@bapenda.go.id'],
                    ['name' => 'Kasubid Pendataan & Pendaftaran Pajak', 'nip' => '44444-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bPajak->id, 'atasan_id' => $kabidPajak->id]
                );
                $kasubPajakData->roles()->sync([$rPenilai->id]);
                    // Staff Kasubid Pendataan Pajak
                    $stafPajakData = User::firstOrCreate(
                        ['email' => 'staf.pajak.data@bapenda.go.id'],
                        ['name' => 'Staf Pendataan Pajak', 'nip' => '44444-1-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPajak->id, 'atasan_id' => $kasubPajakData->id]
                    );
                    $stafPajakData->roles()->sync([$rPegawai->id]);

                // 2) Kasubid Perhitungan Pajak
                $kasubPajakHitung = User::firstOrCreate(
                    ['email' => 'kasub.pajak.hitung@bapenda.go.id'],
                    ['name' => 'Kasubid Perhitungan & Penetapan Pajak', 'nip' => '44444-2', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bPajak->id, 'atasan_id' => $kabidPajak->id]
                );
                $kasubPajakHitung->roles()->sync([$rPenilai->id]);
                    // Staff Kasubid Perhitungan Pajak
                    $stafPajakHitung = User::firstOrCreate(
                        ['email' => 'staf.pajak.hitung@bapenda.go.id'],
                        ['name' => 'Staf Perhitungan Pajak', 'nip' => '44444-2-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPajak->id, 'atasan_id' => $kasubPajakHitung->id]
                    );
                    $stafPajakHitung->roles()->sync([$rPegawai->id]);
                
                // 3) Kasubid Konsultasi
                $kasubPajakBanding = User::firstOrCreate(
                    ['email' => 'kasub.pajak.banding@bapenda.go.id'],
                    ['name' => 'Kasubid Konsultasi Keberatan & Banding', 'nip' => '44444-3', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bPajak->id, 'atasan_id' => $kabidPajak->id]
                );
                $kasubPajakBanding->roles()->sync([$rPenilai->id]);
                    // Staff Kasubid Konsultasi
                    $stafPajakBanding = User::firstOrCreate(
                        ['email' => 'staf.pajak.banding@bapenda.go.id'],
                        ['name' => 'Staf Konsultasi Pajak', 'nip' => '44444-3-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPajak->id, 'atasan_id' => $kasubPajakBanding->id]
                    );
                    $stafPajakBanding->roles()->sync([$rPegawai->id]);


            // --- LEVEL 2: BIDANG DANA PERIMBANGAN ---
            $kabidDana = User::firstOrCreate(
                ['email' => 'kabid.dana@bapenda.go.id'],
                ['name' => 'Kabid Dana Perimbangan', 'nip' => '55555', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKabid->id, 'bidang_id' => $bDana->id, 'atasan_id' => $kaban->id]
            );
            $kabidDana->roles()->sync([$rPenilai->id]);

                // 1.3.1.1. Staff Kabid Dana -> Melapor ke Kabid Dana
                $stafKabidDana = User::firstOrCreate(
                    ['email' => 'staf.kabid.dana@bapenda.go.id'],
                    ['name' => 'Staf Administrasi Kabid Dana', 'nip' => '55555-A', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bDana->id, 'atasan_id' => $kabidDana->id]
                );
                $stafKabidDana->roles()->sync([$rPegawai->id]);

                // --- Kasubid di bawah Kabid Dana ---
                // 1) Kasubid Dana Perimbangan
                $kasubDanaPerimbang = User::firstOrCreate(
                    ['email' => 'kasub.dana.perimbang@bapenda.go.id'],
                    ['name' => 'Kasubid Dana Perimbangan', 'nip' => '55555-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bDana->id, 'atasan_id' => $kabidDana->id]
                );
                $kasubDanaPerimbang->roles()->sync([$rPenilai->id]);
                    // Staff Kasubid Dana Perimbangan
                    $stafDanaPerimbang = User::firstOrCreate(
                        ['email' => 'staf.dana.perimbang@bapenda.go.id'],
                        ['name' => 'Staf Dana Perimbangan', 'nip' => '55555-1-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bDana->id, 'atasan_id' => $kasubDanaPerimbang->id]
                    );
                    $stafDanaPerimbang->roles()->sync([$rPegawai->id]);

                // 2) Kasubid Retribusi
                $kasubDanaRetribusi = User::firstOrCreate(
                    ['email' => 'kasub.dana.retribusi@bapenda.go.id'],
                    ['name' => 'Kasubid Retribusi & Pendapatan Lainnya', 'nip' => '55555-2', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bDana->id, 'atasan_id' => $kabidDana->id]
                );
                $kasubDanaRetribusi->roles()->sync([$rPenilai->id]);
                    // Staff Kasubid Retribusi
                    $stafDanaRetribusi = User::firstOrCreate(
                        ['email' => 'staf.dana.retribusi@bapenda.go.id'],
                        ['name' => 'Staf Retribusi', 'nip' => '55555-2-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bDana->id, 'atasan_id' => $kasubDanaRetribusi->id]
                    );
                    $stafDanaRetribusi->roles()->sync([$rPegawai->id]);
                
                // 3) Kasubid Evaluasi
                $kasubDanaEval = User::firstOrCreate(
                    ['email' => 'kasub.dana.eval@bapenda.go.id'],
                    ['name' => 'Kasubid Evaluasi & Perencanaan Pendapatan', 'nip' => '55555-3', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bDana->id, 'atasan_id' => $kabidDana->id]
                );
                $kasubDanaEval->roles()->sync([$rPenilai->id]);
                    // Staff Kasubid Evaluasi
                    $stafDanaEval = User::firstOrCreate(
                        ['email' => 'staf.dana.eval@bapenda.go.id'],
                        ['name' => 'Staf Evaluasi Pendapatan', 'nip' => '55555-3-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bDana->id, 'atasan_id' => $kasubDanaEval->id]
                    );
                    $stafDanaEval->roles()->sync([$rPegawai->id]);

            // --- LEVEL 2: BIDANG PEMBUKUAN & PELAPORAN ---
            $kabidBuku = User::firstOrCreate(
                ['email' => 'kabid.buku@bapenda.go.id'],
                ['name' => 'Kabid Pembukuan & Pelaporan', 'nip' => '66666', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKabid->id, 'bidang_id' => $bBuku->id, 'atasan_id' => $kaban->id]
            );
            $kabidBuku->roles()->sync([$rPenilai->id]);

                // 1.3.1.1. Staff Kabid Buku -> Melapor ke Kabid Buku
                $stafKabidBuku = User::firstOrCreate(
                    ['email' => 'staf.kabid.buku@bapenda.go.id'],
                    ['name' => 'Staf Administrasi Kabid Buku', 'nip' => '66666-A', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bBuku->id, 'atasan_id' => $kabidBuku->id]
                );
                $stafKabidBuku->roles()->sync([$rPegawai->id]);

                // --- Kasubid di bawah Kabid Buku ---
                // 1) Kasubid Pembukuan
                $kasubBukuLapor = User::firstOrCreate(
                    ['email' => 'kasub.buku.lapor@bapenda.go.id'],
                    ['name' => 'Kasubid Pembukuan & Pelaporan', 'nip' => '66666-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bBuku->id, 'atasan_id' => $kabidBuku->id]
                );
                $kasubBukuLapor->roles()->sync([$rPenilai->id]);
                    // Staff Kasubid Pembukuan
                    $stafBukuLapor = User::firstOrCreate(
                        ['email' => 'staf.buku.lapor@bapenda.go.id'],
                        ['name' => 'Staf Pembukuan', 'nip' => '66666-1-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bBuku->id, 'atasan_id' => $kasubBukuLapor->id]
                    );
                    $stafBukuLapor->roles()->sync([$rPegawai->id]);

                // 2) Kasubid Pemeriksaan
                $kasubBukuPeriksa = User::firstOrCreate(
                    ['email' => 'kasub.buku.periksa@bapenda.go.id'],
                    ['name' => 'Kasubid Pemeriksaan & Verifikasi', 'nip' => '66666-2', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bBuku->id, 'atasan_id' => $kabidBuku->id]
                );
                $kasubBukuPeriksa->roles()->sync([$rPenilai->id]);
                    // Staff Kasubid Pemeriksaan
                    $stafBukuPeriksa = User::firstOrCreate(
                        ['email' => 'staf.buku.periksa@bapenda.go.id'],
                        ['name' => 'Staf Verifikasi', 'nip' => '66666-2-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bBuku->id, 'atasan_id' => $kasubBukuPeriksa->id]
                    );
                    $stafBukuPeriksa->roles()->sync([$rPegawai->id]);
                
                // 3) Kasubid Penagihan
                $kasubBukuTagih = User::firstOrCreate(
                    ['email' => 'kasub.buku.tagih@bapenda.go.id'],
                    ['name' => 'Kasubid Penagihan & Restitusi Pajak Daerah', 'nip' => '66666-3', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bBuku->id, 'atasan_id' => $kabidBuku->id]
                );
                $kasubBukuTagih->roles()->sync([$rPenilai->id]);
                    // Staff Kasubid Penagihan
                    $stafBukuTagih = User::firstOrCreate(
                        ['email' => 'staf.buku.tagih@bapenda.go.id'],
                        ['name' => 'Staf Penagihan Pajak', 'nip' => '66666-3-1', 'password' => $pass, 'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bBuku->id, 'atasan_id' => $kasubBukuTagih->id]
                    );
                    $stafBukuTagih->roles()->sync([$rPegawai->id]);

        });
    }
}