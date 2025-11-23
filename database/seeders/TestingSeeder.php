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
use App\Models\Skp;
use App\Models\LaporanHarian;
use App\Models\Pengumuman;

class TestingSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $this->command->info('Memulai Proses Seeding Master Data dan Simulasi Aktivitas...');

            // --- HAPUS DATA LAMA ---
            LaporanHarian::query()->delete();
            Skp::query()->delete();
            Pengumuman::query()->delete();
            User::query()->delete(); 
            Role::query()->delete();
            Jabatan::query()->delete();
            Tupoksi::query()->delete();
            Bidang::query()->delete();
            UnitKerja::query()->delete();
            
            $globalPassword = Hash::make('password123');
            $this->command->warn('Semua password diatur menjadi: password123');

            // =================================================================
            // 1. MASTER DATA
            // =================================================================
            $rAdmin   = Role::firstOrCreate(['nama_role' => 'Super Admin']);
            $rKadis   = Role::firstOrCreate(['nama_role' => 'Kadis']);
            $rPenilai = Role::firstOrCreate(['nama_role' => 'Penilai']); 
            $rPegawai = Role::firstOrCreate(['nama_role' => 'Pegawai']); 

            $jKaban   = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Badan']);
            $jSekban  = Jabatan::firstOrCreate(['nama_jabatan' => 'Sekretaris']);
            $jKabid   = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Bidang']);
            $jKasub   = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Sub Bagian/Bidang']);
            $jStaf    = Jabatan::firstOrCreate(['nama_jabatan' => 'Staf Pelaksana']);

            $ukBapenda = UnitKerja::firstOrCreate(['nama_unit' => 'Badan Pendapatan Daerah']);

            $bPimpinan    = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Unsur Pimpinan & Staf Khusus']);
            $bSekretariat = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Sekretariat']);
            $bPbb         = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Bidang PBB dan BPHTB']);
            $bDana        = Bidang::firstOrCreate(['unit_kerja_id' => $ukBapenda->id, 'nama_bidang' => 'Bidang Dana Perimbangan & Lain-lain Pendapatan']);
            
            // Tupoksi
            Tupoksi::firstOrCreate(['bidang_id' => $bPimpinan->id, 'uraian_tugas' => 'Penyelenggaraan kebijakan teknis Pendapatan daerah']);
            Tupoksi::firstOrCreate(['bidang_id' => $bPbb->id, 'uraian_tugas' => 'Pelaksanaan perencanaan, pengendalian dan operasional PBB']);
            Tupoksi::firstOrCreate(['bidang_id' => $bSekretariat->id, 'uraian_tugas' => 'Pelayanan administrasi umum dan kepegawaian']);

            // =================================================================
            // 2. USER HIERARCHY & CREDENTIALS (USERNAME)
            // =================================================================
            
            // --- LEVEL 1: KABAN ---
            $kaban = User::firstOrCreate(
                ['username' => 'kaban'], // ganti email jadi username
                [
                    'name' => 'Darius Sabon Rain (Kaban)',
                    'nip' => '197001011995011001',
                    'password' => $globalPassword,
                    'unit_kerja_id' => $ukBapenda->id,
                    'jabatan_id' => $jKaban->id,
                    'bidang_id' => $bPimpinan->id,
                    'atasan_id' => null,
                    'email' => null
                ]
            );
            $kaban->roles()->sync([$rKadis->id, $rPenilai->id]);

            // --- LEVEL 2: SEKRETARIS ---
            $sekban = User::firstOrCreate(
                ['username' => 'sekban'],
                [
                    'name' => 'Sekretaris Bapenda',
                    'nip' => '197501012000011001',
                    'password' => $globalPassword,
                    'unit_kerja_id' => $ukBapenda->id,
                    'jabatan_id' => $jSekban->id,
                    'bidang_id' => $bSekretariat->id,
                    'atasan_id' => $kaban->id,
                    'email' => null
                ]
            );
            $sekban->roles()->sync([$rPenilai->id]);

            // --- LEVEL 2: KABID PBB ---
            $kabidPbb = User::firstOrCreate(
                ['username' => 'kabid.pbb'],
                [
                    'name' => 'Kabid PBB & BPHTB',
                    'nip' => '198001012005011001',
                    'password' => $globalPassword,
                    'unit_kerja_id' => $ukBapenda->id,
                    'jabatan_id' => $jKabid->id,
                    'bidang_id' => $bPbb->id,
                    'atasan_id' => $kaban->id,
                    'email' => null
                ]
            );
            $kabidPbb->roles()->sync([$rPenilai->id]);
            
            // --- LEVEL 3: KASUBID PBB ---
            $kasubPbbData = User::firstOrCreate(
                ['username' => 'kasub.pbb.data'],
                [
                    'name' => 'Kasubid Pendataan PBB',
                    'nip' => '198501012010011001',
                    'password' => $globalPassword,
                    'unit_kerja_id' => $ukBapenda->id,
                    'jabatan_id' => $jKasub->id,
                    'bidang_id' => $bPbb->id,
                    'atasan_id' => $kabidPbb->id,
                    'email' => null
                ]
            );
            $kasubPbbData->roles()->sync([$rPenilai->id]);

            // --- LEVEL 4: STAF PBB ---
            $stafPbbData = User::firstOrCreate(
                ['username' => 'staf.pbb.data'],
                [
                    'name' => 'Staf Pendataan PBB',
                    'nip' => '199501012020011001',
                    'password' => $globalPassword,
                    'unit_kerja_id' => $ukBapenda->id,
                    'jabatan_id' => $jStaf->id,
                    'bidang_id' => $bPbb->id,
                    'atasan_id' => $kasubPbbData->id,
                    'email' => null
                ]
            );
            $stafPbbData->roles()->sync([$rPegawai->id]);
            
            // --- LEVEL 3: KASUBAG UMUM ---
            $kasubUmum = User::firstOrCreate(
                ['username' => 'kasub.umum'],
                [
                    'name' => 'Kasubag Umum & Kepegawaian',
                    'nip' => '199001012015011001',
                    'password' => $globalPassword,
                    'unit_kerja_id' => $ukBapenda->id,
                    'jabatan_id' => $jKasub->id,
                    'bidang_id' => $bSekretariat->id,
                    'atasan_id' => $sekban->id,
                    'email' => null
                ]
            );
            $kasubUmum->roles()->sync([$rPenilai->id]);

            

            // =================================================================
            // 3. SIMULASI SKP (CREATE)
            // =================================================================
            
            $skpStaff = Skp::create([
                'user_id'        => $stafPbbData->id,
                'nama_skp'       => 'Melakukan pendataan objek pajak PBB sektor pedesaan dan perkotaan',
                'periode_mulai'  => Carbon::now()->startOfYear(),
                'periode_selesai'=> Carbon::now()->endOfYear(),
                'rencana_aksi'   => 'Melakukan survei ke lapangan dan menginput SPOP',
                'indikator'      => 'Jumlah objek pajak terdata',
                'target'         => 100
            ]);

            $skpKasubid = Skp::create([
                'user_id'        => $kasubPbbData->id,
                'nama_skp'       => 'Mengelola kegiatan pendataan dan pendaftaran PBB',
                'periode_mulai'  => Carbon::now()->startOfYear(),
                'periode_selesai'=> Carbon::now()->endOfYear(),
                'rencana_aksi'   => 'Monitoring tim pendataan dan verifikasi berkas',
                'indikator'      => 'Laporan monitoring',
                'target'         => 12
            ]);

            $skpKabid = Skp::create([
                'user_id'        => $kabidPbb->id,
                'nama_skp'       => 'Merumuskan kebijakan teknis bidang PBB dan BPHTB',
                'periode_mulai'  => Carbon::now()->startOfYear(),
                'periode_selesai'=> Carbon::now()->endOfYear(),
                'rencana_aksi'   => 'Menyusun draf kebijakan',
                'indikator'      => 'Draf Perbup',
                'target'         => 1
            ]);

            $skpSekban = Skp::create([
                'user_id'        => $sekban->id,
                'nama_skp'       => 'Mengkoordinasikan urusan umum, kepegawaian, dan keuangan',
                'periode_mulai'  => Carbon::now()->startOfYear(),
                'periode_selesai'=> Carbon::now()->endOfYear(),
                'rencana_aksi'   => 'Evaluasi serapan anggaran',
                'indikator'      => 'Laporan realisasi fisik dan keuangan',
                'target'         => 4
            ]);
            
            $skpKasubag = Skp::create([
                'user_id' => $kasubUmum->id, 
                'nama_skp' => 'Urusan Rumah Tangga Kantor',
                'periode_mulai' => Carbon::now()->startOfYear(),
                'periode_selesai' => Carbon::now()->endOfYear(),
                'rencana_aksi' => 'Maintenance kendaraan dinas',
                'indikator' => 'Kendaraan layak jalan',
                'target' => 10
            ]);

            
            // =================================================================
            // 4. SIMULASI LKH BERJENJANG (CREATE)
            // =================================================================
            
            // -------------------------------------------------------------
            // A. LKH STAF PBB (BAWAHAN KASUBID)
            // -------------------------------------------------------------
            
            // LKH 1: Waiting Review (Menunggu Kasubid)
            $this->createLkh($stafPbbData, $skpStaff, 'waiting_review', $kasubPbbData, 
                'Melakukan survei lapangan di Distrik Mimika Baru (5 unit)', 
                '5 Data objek pajak baru',
                'Survey'
            );

            // LKH 2: Rejected (Ditolak Kasubid)
            $this->createLkh($stafPbbData, $skpStaff, 'rejected', $kasubPbbData, 
                'Menginput data SPPT PBB tahun berjalan', 
                'Data terinput ke sistem SISMIOP',
                'Rekapitulasi',
                'Data tidak lengkap, mohon lampirkan foto lokasi, koordinat tidak sesuai.'
            );

            // LKH 3: Approved (Diterima Kasubid)
            $this->createLkh($stafPbbData, $skpStaff, 'approved', $kasubPbbData, 
                'Mencetak DHKP untuk kelurahan Timika Indah', 
                'Dokumen DHKP tercetak dan dijilid',
                'Rutin', 
                'Kerja bagus, lanjutkan.'
            );
            
            // -------------------------------------------------------------
            // B. LKH KASUBID PBB (BAWAHAN KABID)
            // -------------------------------------------------------------
            
            // LKH Kasubid 1: Approved (oleh Kabid PBB)
            $this->createLkh($kasubPbbData, $skpKasubid, 'approved', $kabidPbb, 
                'Memverifikasi hasil pendataan lapangan staf', 
                '15 Berkas pendataan terverifikasi',
                'Validasi',
            );

            // LKH Pending untuk Validasi Kabid (Kasubid mengajukan)
            $this->createLkh($kasubPbbData, $skpKasubid, 'waiting_review', $kabidPbb, 
                'Menyusun jadwal petugas loket pelayanan PBB', 
                'Jadwal piket bulan depan tersedia',
                'Rutin',
            );

            // -------------------------------------------------------------
            // C. LKH KABID PBB (BAWAHAN KABAN)
            // -------------------------------------------------------------

            // LKH Kabid: Approved (oleh Kaban)
            $this->createLkh($kabidPbb, $skpKabid, 'approved', $kaban, 
                'Rapat Pimpinan evaluasi PAD Sektor PBB', 
                'Notulen rapat dan strategi percepatan realisasi',
                'Rapat',
            );
            
            // -------------------------------------------------------------
            // D. LKH KASUBAG UMUM (BAWAHAN SEKBAN) & VALIDASI SEKBAN
            // -------------------------------------------------------------

            // LKH Pending untuk Validasi Sekban (Kasubag Umum mengajukan) -> Rejected
            $lkhKasubagRejected = $this->createLkh($kasubUmum, $skpKasubag, 'waiting_review', $sekban,
                'Pengecekan aset kendaraan dinas',
                'Daftar kondisi kendaraan terkini',
                'Inspeksi',
            );
            
            // Sekban Reject
            $lkhKasubagRejected->update([
                'status' => 'rejected',
                'atasan_id' => $sekban->id,
                'waktu_validasi' => Carbon::now(),
                'komentar_validasi' => 'Cek ulang kendaraan nomor PA 1234 MM, sepertinya salah catat.'
            ]);
            
            // -------------------------------------------------------------
            // E. PENGUMUMAN
            // -------------------------------------------------------------
            
            // Pengumuman Kasubid (Muncul di Staf PBB)
            Pengumuman::create([
                'user_id_creator' => $kasubPbbData->id,
                'unit_kerja_id'   => $kasubPbbData->unit_kerja_id,
                'judul'           => 'Rapat Koordinasi Tim Pendataan',
                'isi_pengumuman'  => 'Besok jam 08.00 WIT kumpul di ruang rapat untuk evaluasi target triwulan.',
            ]);

            // Pengumuman Sekban (Global)
            Pengumuman::create([
                'user_id_creator' => $sekban->id,
                'unit_kerja_id'   => null, // Global
                'judul'           => 'Pemberitahuan Libur Nasional',
                'isi_pengumuman'  => 'Sehubungan dengan hari raya, kantor diliburkan pada tanggal berikut...',
            ]);


            $this->command->info('Simulasi Data Selesai. Kredensial: NIP & password123. System Ready.');
        });
    }

    /**
     * Helper untuk membuat LKH.
     */
    private function createLkh($user, $skp, $status, $validator = null, $deskripsi, $output, $jenisKegiatan, $komentar = null)
    {
        $isLuarLokasi = in_array($jenisKegiatan, ['Survey', 'Inspeksi', 'Kunjungan']);

        return LaporanHarian::create([
            'user_id'             => $user->id,
            'skp_id'              => $skp->id,
            'tanggal_laporan'     => Carbon::today()->format('Y-m-d'),
            'waktu_mulai'         => '08:00:00',
            'waktu_selesai'       => '16:00:00',
            'deskripsi_aktivitas' => $deskripsi,
            'output_hasil_kerja'  => $output,
            'jenis_kegiatan'      => $jenisKegiatan,
            'status'              => $status,
            
            'lokasi'              => DB::raw("ST_GeomFromText('POINT(136.8851 -4.5461)')"),
            'is_luar_lokasi'      => $isLuarLokasi,
            
            'atasan_id'        => ($status !== 'waiting_review' && $validator) ? $validator->id : null,
            'waktu_validasi'      => ($status !== 'waiting_review') ? Carbon::now() : null,
            'komentar_validasi'   => $komentar,
        ]);
    }
}