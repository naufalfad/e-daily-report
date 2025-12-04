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
use App\Models\SkpRencana; // [BARU]
use App\Models\SkpTarget;  // [BARU]
use App\Models\LaporanHarian;
use App\Models\Pengumuman;

class TestingSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $this->command->info('Memulai Proses Seeding Master Data dan Simulasi Aktivitas (New Structure)...');

            // --- 1. CLEANUP DATA LAMA ---
            LaporanHarian::query()->delete();
            SkpTarget::query()->delete(); // Hapus child dulu
            SkpRencana::query()->delete(); // Hapus parent
            Pengumuman::query()->delete();
            
            DB::table('user_roles')->delete();
            User::query()->delete(); 
            Role::query()->delete();
            Tupoksi::query()->delete();
            Bidang::query()->delete();
            Jabatan::query()->delete();
            UnitKerja::query()->delete();
            
            $globalPassword = Hash::make('password123');
            $this->command->warn('Semua password diatur menjadi: password123');
            $globalAlamat = 'Jl. Yos Sudarso, Nawaripi, Kec. Mimika Baru, Kabupaten Mimika, Papua Tengah 99971';

            // =================================================================
            // 2. MASTER DATA (ROLES & STRUKTUR) - TETAP SAMA
            // =================================================================
            $rAdmin   = Role::firstOrCreate(['nama_role' => 'Super Admin']);
            $rKadis   = Role::firstOrCreate(['nama_role' => 'Kadis']);
            $rPenilai = Role::firstOrCreate(['nama_role' => 'Penilai']); 
            $rPegawai = Role::firstOrCreate(['nama_role' => 'Staf']); 

            $jKaban   = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Badan']);
            $jSekban  = Jabatan::firstOrCreate(['nama_jabatan' => 'Sekretaris']);
            $jKabid   = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Bidang']);
            $jKasub   = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Sub Bagian/Bidang']);
            $jStaf    = Jabatan::firstOrCreate(['nama_jabatan' => 'Staf Pelaksana']);
            $jAdmin   = Jabatan::firstOrCreate(['nama_jabatan' => 'Administrator Sistem']);

            $ukBapenda = UnitKerja::firstOrCreate(['nama_unit' => 'Badan Pendapatan Daerah']);

            $bSekretariat = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Sekretariat']);
            $bPimpinan    = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Unsur Pimpinan']);
            $bPbb         = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Bidang PBB dan BPHTB']);
            $bDana        = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Bidang Dana Perimbangan']);
            $bIT          = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Unit Teknologi Informasi']);

            // =================================================================
            // 3. TUPOKSI - TETAP SAMA
            // =================================================================
            Tupoksi::firstOrCreate(['bidang_id' => $bSekretariat->id, 'uraian_tugas' => 'Melaksanakan pengelolaan surat menyurat dan kearsipan']);
            Tupoksi::firstOrCreate(['bidang_id' => $bSekretariat->id, 'uraian_tugas' => 'Melaksanakan administrasi kepegawaian dan pengembangan SDM']);
            Tupoksi::firstOrCreate(['bidang_id' => $bSekretariat->id, 'uraian_tugas' => 'Menyusun rencana anggaran dan pelaporan keuangan badan']);
            Tupoksi::firstOrCreate(['bidang_id' => $bSekretariat->id, 'uraian_tugas' => 'Memfasilitasi kebutuhan rumah tangga dan perlengkapan kantor']);

            Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => 'Melaksanakan pendataan dan pendaftaran objek pajak baru']);
            Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => 'Melakukan penilaian dan penetapan besaran pajak terutang']);
            Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => 'Melaksanakan pemutakhiran data subjek dan objek pajak (SISMIOP)']);
            Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => 'Melakukan pelayanan keberatan dan pengurangan pajak']);

            Tupoksi::firstOrCreate(['bidang_id' => $bPimpinan->id, 'uraian_tugas' => 'Merumuskan kebijakan teknis di bidang pendapatan daerah']);
            Tupoksi::firstOrCreate(['bidang_id' => $bPimpinan->id, 'uraian_tugas' => 'Mengoordinasikan pelaksanaan tugas seluruh bidang']);

            // =================================================================
            // 4. USER & HIERARCHY - TETAP SAMA (DITAMBAH IS_ACTIVE)
            // =================================================================
            
            // ADMIN
            $adminUser = User::firstOrCreate(['username' => 'admin'], [
                'name' => 'Administrator Sistem', 'nip' => 'admin_system', 'password' => $globalPassword,
                'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jAdmin->id, 'bidang_id' => $bIT->id,
                'atasan_id' => null, 'email' => 'admin@bapenda.mimika.go.id', 'alamat' => $globalAlamat, 'is_active' => true
            ]);
            $adminUser->roles()->sync([$rAdmin->id]);

            // KABAN
            $kaban = User::firstOrCreate(['username' => 'kaban'], [
                'name' => 'Darius Sabon Rain (Kaban)', 'nip' => '197301032007011031', 'password' => $globalPassword,
                'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKaban->id, 'bidang_id' => $bPimpinan->id,
                'atasan_id' => null, 'email' => null, 'alamat' => $globalAlamat, 'is_active' => true
            ]);
            $kaban->roles()->sync([$rKadis->id, $rPenilai->id]);

            // SEKBAN
            $sekban = User::firstOrCreate(['username' => 'sekban'], [
                'name' => 'Sekretaris Bapenda', 'nip' => '197501012000011001', 'password' => $globalPassword,
                'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jSekban->id, 'bidang_id' => $bSekretariat->id,
                'atasan_id' => $kaban->id, 'email' => null, 'alamat' => $globalAlamat, 'is_active' => true
            ]);
            $sekban->roles()->sync([$rPenilai->id]);

            // KABID PBB
            $kabidPbb = User::firstOrCreate(['username' => 'kabid.pbb'], [
                'name' => 'Kabid PBB & BPHTB', 'nip' => '198001012005011001', 'password' => $globalPassword,
                'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKabid->id, 'bidang_id' => $bPbb->id,
                'atasan_id' => $kaban->id, 'email' => null, 'alamat' => $globalAlamat, 'is_active' => true
            ]);
            $kabidPbb->roles()->sync([$rPenilai->id]);
            
            // KASUBID DATA PBB
            $kasubPbbData = User::firstOrCreate(['username' => 'kasub.pbb.data'], [
                'name' => 'Kasubid Pendataan PBB', 'nip' => '198501012010011001', 'password' => $globalPassword,
                'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bPbb->id,
                'atasan_id' => $kabidPbb->id, 'email' => null, 'alamat' => $globalAlamat, 'is_active' => true
            ]);
            $kasubPbbData->roles()->sync([$rPenilai->id]);

            // KASUBAG UMUM
            $kasubUmum = User::firstOrCreate(['username' => 'kasub.umum'], [
                'name' => 'Kasubag Umum & Kepegawaian', 'nip' => '199001012015011001', 'password' => $globalPassword,
                'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jKasub->id, 'bidang_id' => $bSekretariat->id,
                'atasan_id' => $sekban->id, 'email' => null, 'alamat' => $globalAlamat, 'is_active' => true
            ]);
            $kasubUmum->roles()->sync([$rPenilai->id]);

            // STAF PBB
            $stafPbbData = User::firstOrCreate(['username' => 'staf.pbb.data'], [
                'name' => 'Staf Pendataan PBB', 'nip' => '199501012020011001', 'password' => $globalPassword,
                'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bPbb->id,
                'atasan_id' => $kasubPbbData->id, 'email' => null, 'alamat' => $globalAlamat, 'is_active' => true
            ]);
            $stafPbbData->roles()->sync([$rPegawai->id]);

            // STAF UMUM
            $stafUmum = User::firstOrCreate(['username' => 'staf.umum'], [
                'name' => 'Staf Administrasi Umum', 'nip' => '199601012021011002', 'password' => $globalPassword,
                'unit_kerja_id' => $ukBapenda->id, 'jabatan_id' => $jStaf->id, 'bidang_id' => $bSekretariat->id,
                'atasan_id' => $kasubUmum->id, 'email' => null, 'alamat' => $globalAlamat, 'is_active' => true
            ]);
            $stafUmum->roles()->sync([$rPegawai->id]);


            // =================================================================
            // 5. SIMULASI SKP (STRUKTUR BARU: HEADER + TARGETS)
            // =================================================================
            
            // SKP STAF PBB
            $rencanaStaff = $this->createSkpPaket($stafPbbData,
                'Meningkatnya validitas data PBB Sektor Pedesaan & Perkotaan', // RHK Intervensi (Atasan)
                'Melakukan pendataan objek pajak PBB sektor pedesaan dan perkotaan secara door-to-door', // RHK Pegawai
                [
                    // Target Kuantitas
                    ['jenis_aspek' => 'Kuantitas', 'indikator' => 'Jumlah objek pajak terdata secara akurat', 'target' => 200, 'satuan' => 'Formulir SPOP'],
                    // Target Waktu
                    ['jenis_aspek' => 'Waktu', 'indikator' => 'Waktu pelaksanaan kegiatan', 'target' => 12, 'satuan' => 'Bulan'],
                    // Target Kualitas
                    ['jenis_aspek' => 'Kualitas', 'indikator' => 'Kesesuaian data lapangan dengan sistem', 'target' => 100, 'satuan' => 'Persen']
                ]
            );

            // SKP KASUBID (PENILAI)
            $rencanaKasubid = $this->createSkpPaket($kasubPbbData,
                'Tersedianya basis data pajak daerah yang akurat dan mutakhir',
                'Mengelola kegiatan pendataan, pendaftaran, dan pemutakhiran data PBB',
                [
                    ['jenis_aspek' => 'Kuantitas', 'indikator' => 'Laporan hasil monitoring pendataan', 'target' => 12, 'satuan' => 'Laporan'],
                    ['jenis_aspek' => 'Waktu', 'indikator' => 'Ketepatan waktu penyampaian laporan', 'target' => 12, 'satuan' => 'Bulan']
                ]
            );

            // SKP KABID (ESELON 3)
            $rencanaKabid = $this->createSkpPaket($kabidPbb,
                'Meningkatnya PAD Sektor Pajak Daerah',
                'Merumuskan kebijakan teknis intensifikasi PBB dan BPHTB',
                [
                    ['jenis_aspek' => 'Kuantitas', 'indikator' => 'Dokumen Kebijakan/Perbup', 'target' => 2, 'satuan' => 'Dokumen'],
                    ['jenis_aspek' => 'Waktu', 'indikator' => 'Waktu penyelesaian draf', 'target' => 6, 'satuan' => 'Bulan']
                ]
            );

            // SKP SEKBAN
            $rencanaSekban = $this->createSkpPaket($sekban,
                'Terwujudnya tata kelola administrasi Bapenda yang akuntabel',
                'Mengoordinasikan pelayanan administratif, kepegawaian, dan fasilitasi kinerja',
                [
                    ['jenis_aspek' => 'Kuantitas', 'indikator' => 'Laporan Kinerja Instansi', 'target' => 1, 'satuan' => 'Laporan'],
                    ['jenis_aspek' => 'Waktu', 'indikator' => 'Waktu penyelesaian', 'target' => 12, 'satuan' => 'Bulan']
                ]
            );
            
            // SKP KASUBAG UMUM
            $rencanaKasubag = $this->createSkpPaket($kasubUmum,
                'Terpeliharanya aset dan sarana prasarana kantor',
                'Pengelolaan Urusan Umum, Aset, dan Kepegawaian',
                [
                    ['jenis_aspek' => 'Kuantitas', 'indikator' => 'Laporan Inventarisasi BMD', 'target' => 2, 'satuan' => 'Laporan'],
                    ['jenis_aspek' => 'Waktu', 'indikator' => 'Waktu pelaksanaan sensus', 'target' => 6, 'satuan' => 'Bulan']
                ]
            );


            // =================================================================
            // 6. SIMULASI LKH (MENGGUNAKAN SKP_RENCANA_ID)
            // =================================================================
            
            // LKH 1: Staf PBB -> Kasubid (Waiting Review)
            $this->createLkh($stafPbbData, $rencanaStaff, 'waiting_review', 
                'Melakukan survei lapangan di Distrik Mimika Baru (Jalan Budi Utomo)', 
                '5 Formulir SPOP terisi lengkap dengan foto lokasi',
                'Kunjungan Lapangan',
                null // Atasan otomatis dari user structure
            );

            // LKH 2: Staf PBB -> Kasubid (Rejected)
            $this->createLkh($stafPbbData, $rencanaStaff, 'rejected', 
                'Menginput data SPPT PBB tahun berjalan ke Excel Manual', 
                'File Excel rekapitulasi sementara',
                'Penyusunan Dokumen',
                $kasubPbbData, // Validator
                'Mohon input langsung ke aplikasi SISMIOP, jangan pakai Excel manual lagi.'
            );

            // LKH 3: Staf PBB -> Kasubid (Approved)
            $this->createLkh($stafPbbData, $rencanaStaff, 'approved', 
                'Mencetak DHKP untuk kelurahan Timika Indah', 
                'Dokumen DHKP tercetak dan dijilid rapi',
                'Pelayanan Publik', 
                $kasubPbbData,
                'Terima kasih, segera distribusikan ke kelurahan.'
            );
            
            // LKH Kasubid -> Kabid (Approved)
            $this->createLkh($kasubPbbData, $rencanaKasubid, 'approved', 
                'Memverifikasi 50 berkas permohonan mutasi PBB dari loket pelayanan', 
                'Berkas terverifikasi dan diparaf',
                'Penyusunan Dokumen',
                $kabidPbb
            );

            // LKH Kabid -> Kaban (Approved)
            $this->createLkh($kabidPbb, $rencanaKabid, 'approved', 
                'Rapat Koordinasi dengan BPN terkait penetapan NJOP BPHTB', 
                'Berita Acara Kesepakatan NJOP',
                'Rapat',
                $kaban
            );
            
            // LKH Staf Umum -> Kasubag (Tanpa SKP / Tugas Tambahan)
            $this->createLkh($stafUmum, null, 'waiting_review', 
                'Mengagendakan surat masuk dari Provinsi Papua Tengah',
                'Surat terdisposisi ke Sekretaris',
                'Pelayanan Publik',
                null
            );

            // =================================================================
            // 7. PENGUMUMAN - TETAP SAMA
            // =================================================================
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

            $this->command->info('Simulasi Data Selesai. Akun Admin: admin / password123.');
        });
    }

    /**
     * Helper Buat SKP Paket (Header + Target)
     * Menggantikan Skp::create() yang lama.
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
        
        $targetAtasanId = $validatorUser ? $validatorUser->id : $user->atasan_id;

        // Ambil satuan otomatis dari target 'Kuantitas' rencana ini
        $satuan = 'Kegiatan'; // Default
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
            'skp_rencana_id'      => $rencana ? $rencana->id : null, // Gunakan ID Rencana baru
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