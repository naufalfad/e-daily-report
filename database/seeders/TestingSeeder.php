<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Tambahkan ini untuk hashing password
use Carbon\Carbon;
use App\Models\User;
use App\Models\Skp;
use App\Models\LaporanHarian;
use App\Models\Pengumuman;

class TestingSeeder  extends Seeder
{
    /**
     * Menjalankan simulasi data sesuai skenario Yang Mulia Raja.
     */
    public function run()
    {
        DB::transaction(function () {
            $this->command->info('Memulai Simulasi Aktivitas Harian (Reset Credential & Data)...');

            // =================================================================
            // 0. LOAD ACTORS & RESET CREDENTIALS (NIP AS USERNAME & PASSWORD)
            // =================================================================
            
            // 1. STAFF
            $staff = User::where('email', 'staf.pbb.data@bapenda.go.id')->firstOrFail();
            $staff->update([
                'nip' => '199501012020011001', // Username Login
                'password' => Hash::make('password123')
            ]);

            // 2. KASUBID (Penilai)
            $kasubid = User::where('email', 'kasub.pbb.data@bapenda.go.id')->firstOrFail();
            $kasubid->update([
                'nip' => '198501012010011001', // Username Login
                'password' => Hash::make('password123')
            ]);

            // 3. KABID
            $kabid = User::where('email', 'kabid.pbb@bapenda.go.id')->firstOrFail();
            $kabid->update([
                'nip' => '198001012005011001', // Username Login
                'password' => Hash::make('password123')
            ]);

            // 4. SEKBAN
            $sekban = User::where('email', 'sekban@bapenda.go.id')->firstOrFail();
            $sekban->update([
                'nip' => '197501012000011001', // Username Login
                'password' => Hash::make('password123')
            ]);

            // 5. KABAN (KEPALA DINAS)
            $kaban = User::where('email', 'darius.rain@bapenda.go.id')->firstOrFail();
            $kaban->update([
                'nip' => '197001011995011001', // Username Login
                'password' => Hash::make('password123')
            ]);

            $this->command->info('Credentials Updated! Password semua user: password123');

            // =================================================================
            // 1. SKENARIO STAFF (STAF PENDATAAN PBB)
            // =================================================================
            $this->command->info('-> Simulasi Staff: ' . $staff->name);

            // 1.A. Bikin SKP Staff (Tanpa Status)
            $skpStaff = Skp::create([
                'user_id'        => $staff->id,
                'nama_skp'       => 'Melakukan pendataan objek pajak PBB sektor pedesaan dan perkotaan',
                'periode_mulai'  => Carbon::now()->startOfYear(),
                'periode_selesai'=> Carbon::now()->endOfYear(),
                'rencana_aksi'   => 'Melakukan survei ke lapangan',
                'indikator'      => 'Jumlah objek pajak terdata',
                'target'         => 100
            ]);

            // 1.B. Bikin 3 LKH Berbeda Status
            // Pending
            $this->createLkh($staff, $skpStaff, 'waiting_review', null, 
                'Melakukan survei lapangan di Distrik Mimika Baru', 
                'Tersedianya data objek pajak baru sebanyak 5 unit'
            );

            // Rejected (oleh Kasubid)
            $this->createLkh($staff, $skpStaff, 'rejected', $kasubid, 
                'Menginput data SPPT PBB tahun berjalan', 
                'Data terinput ke sistem SISMIOP',
                'Data tidak lengkap, mohon lampirkan foto lokasi, koordinat tidak sesuai.'
            );

            // Approved (oleh Kasubid)
            $this->createLkh($staff, $skpStaff, 'approved', $kasubid, 
                'Mencetak DHKP untuk kelurahan Timika Indah', 
                'Dokumen DHKP tercetak dan dijilid',
                'Kerja bagus, lanjutkan.'
            );

            // =================================================================
            // 2. SKENARIO KASUBID (PENILAI STAFF)
            // =================================================================
            $this->command->info('-> Simulasi Kasubid: ' . $kasubid->name);

            // 2.A. Bikin SKP Kasubid
            $skpKasubid = Skp::create([
                'user_id'        => $kasubid->id,
                'nama_skp'       => 'Mengelola kegiatan pendataan dan pendaftaran PBB',
                'periode_mulai'  => Carbon::now()->startOfYear(),
                'periode_selesai'=> Carbon::now()->endOfYear(),
                'rencana_aksi'   => 'Monitoring tim pendataan',
                'indikator'      => 'Laporan monitoring',
                'target'         => 12
            ]);

            // 2.B. Bikin LKH Kasubid (Approved oleh Kabid)
            $this->createLkh($kasubid, $skpKasubid, 'approved', $kabid, 
                'Memverifikasi hasil pendataan lapangan staf', 
                '15 Berkas pendataan terverifikasi'
            );

            // 2.C. Membuat Pengumuman
            Pengumuman::create([
                'user_id_creator' => $kasubid->id,
                'unit_kerja_id'   => $kasubid->unit_kerja_id,
                'judul'           => 'Rapat Koordinasi Tim Pendataan',
                'isi_pengumuman'  => 'Besok jam 08.00 WIT kumpul di ruang rapat untuk evaluasi target triwulan.',
            ]);

            // =================================================================
            // 3. SKENARIO KABID (ATASAN KASUBID)
            // =================================================================
            $this->command->info('-> Simulasi Kabid: ' . $kabid->name);

            // 3.A. Bikin SKP Kabid
            $skpKabid = Skp::create([
                'user_id'        => $kabid->id,
                'nama_skp'       => 'Merumuskan kebijakan teknis bidang PBB dan BPHTB',
                'periode_mulai'  => Carbon::now()->startOfYear(),
                'periode_selesai'=> Carbon::now()->endOfYear(),
                'rencana_aksi'   => 'Menyusun draf kebijakan',
                'indikator'      => 'Draf Perbup',
                'target'         => 1
            ]);

            // 3.B. Bikin LKH Kabid (Approved oleh Kaban)
            $this->createLkh($kabid, $skpKabid, 'approved', $kaban, 
                'Rapat Pimpinan evaluasi PAD Sektor PBB', 
                'Notulen rapat dan strategi percepatan realisasi'
            );

            // 3.C. Validasi LKH Bawahan (Kasubid)
            // Buat LKH Kasubid yg pending dulu
            $lkhKasubidPending = $this->createLkh($kasubid, $skpKasubid, 'waiting_review', null, 
                'Menyusun jadwal petugas loket pelayanan PBB', 
                'Jadwal piket bulan depan tersedia'
            );
            
            // Kabid melakukan Approval
            $lkhKasubidPending->update([
                'status' => 'approved',
                'validator_id' => $kabid->id,
                'waktu_validasi' => Carbon::now(),
                'komentar_validasi' => 'Oke, distribusikan segera.'
            ]);

            // 3.D. Pengumuman Kabid
            Pengumuman::create([
                'user_id_creator' => $kabid->id,
                'unit_kerja_id'   => $kabid->unit_kerja_id,
                'judul'           => 'Target Realisasi PBB Tahun Ini',
                'isi_pengumuman'  => 'Mohon seluruh bidang PBB meningkatkan kinerja untuk mencapai target.',
            ]);

            // =================================================================
            // 4. SKENARIO SEKBAN (SETARA KABID)
            // =================================================================
            $this->command->info('-> Simulasi Sekban: ' . $sekban->name);

            // 4.A. Input SKP
            $skpSekban = Skp::create([
                'user_id'        => $sekban->id,
                'nama_skp'       => 'Mengkoordinasikan urusan umum, kepegawaian, dan keuangan',
                'periode_mulai'  => Carbon::now()->startOfYear(),
                'periode_selesai'=> Carbon::now()->endOfYear(),
                'rencana_aksi'   => 'Evaluasi serapan anggaran',
                'indikator'      => 'Laporan realisasi fisik dan keuangan',
                'target'         => 4
            ]);

            // 4.B. Input LKH (Approved oleh Kaban)
            $this->createLkh($sekban, $skpSekban, 'approved', $kaban, 
                'Memeriksa laporan keuangan bulanan', 
                'Laporan keuangan tervalidasi'
            );

            // 4.C. Buat Pengumuman
            Pengumuman::create([
                'user_id_creator' => $sekban->id,
                'unit_kerja_id'   => $sekban->unit_kerja_id,
                'judul'           => 'Pemberitahuan Libur Nasional',
                'isi_pengumuman'  => 'Sehubungan dengan hari raya, kantor diliburkan pada tanggal berikut...',
            ]);

            // 4.D. Data Validasi LKH (Kasubag Umum)
            // Kita cari user Kasubag Umum, update credentialnya juga
            $kasubagUmum = User::where('email', 'kasub.umum@bapenda.go.id')->first();
            if ($kasubagUmum) {
                $kasubagUmum->update([
                    'nip' => '199001012015011001',
                    'password' => Hash::make('password123')
                ]);

                $skpKasubag = Skp::create([
                    'user_id' => $kasubagUmum->id, 
                    'nama_skp' => 'Urusan Rumah Tangga Kantor',
                    'periode_mulai' => Carbon::now()->startOfYear(),
                    'periode_selesai' => Carbon::now()->endOfYear(),
                    'rencana_aksi' => 'Maintenance kendaraan dinas',
                    'indikator' => 'Kendaraan layak jalan',
                    'target' => 10
                ]);
                
                $lkhKasubag = $this->createLkh($kasubagUmum, $skpKasubag, 'waiting_review', null,
                    'Pengecekan aset kendaraan dinas',
                    'Daftar kondisi kendaraan terkini'
                );

                // Sekban Reject
                $lkhKasubag->update([
                    'status' => 'rejected',
                    'validator_id' => $sekban->id,
                    'waktu_validasi' => Carbon::now(),
                    'komentar_validasi' => 'Cek ulang kendaraan nomor PA 1234 MM, sepertinya salah catat.'
                ]);
            }
            
            $this->command->info('Simulasi Data Selesai. User siap Login dengan NIP dan Password: password123');
        });
    }

    /**
     * Helper untuk membuat LKH.
     */
    private function createLkh($user, $skp, $status, $validator = null, $deskripsi, $output, $komentar = null)
    {
        return LaporanHarian::create([
            'user_id'             => $user->id,
            'skp_id'              => $skp->id,
            'tanggal_laporan'     => Carbon::today()->format('Y-m-d'),
            'waktu_mulai'         => '08:00:00',
            'waktu_selesai'       => '16:00:00',
            'deskripsi_aktivitas' => $deskripsi,
            'output_hasil_kerja'  => $output,
            'status'              => $status,
            
            'validator_id'        => ($status !== 'waiting_review' && $validator) ? $validator->id : null,
            'waktu_validasi'      => ($status !== 'waiting_review') ? Carbon::now() : null,
            'komentar_validasi'   => $komentar,
            'is_luar_lokasi'      => false,
            'jenis_kegiatan'      => 'Rutin' // Default
        ]);
    }
}