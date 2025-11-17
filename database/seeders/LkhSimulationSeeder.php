<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Skp;
use App\Models\LaporanHarian;
use Carbon\Carbon;

class LkhSimulationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::transaction(function () {
            // ================================================================
            // 1. PERSIAPAN DATA USER (Sesuai BapendaSeeder)
            // ================================================================
            
            // Rantai Komando: Staf -> Kasubid -> Kabid
            $stafUser    = User::where('email', 'staf.pbb.data@bapenda.go.id')->firstOrFail();
            $kasubidUser = User::where('email', 'kasub.pbb.data@bapenda.go.id')->firstOrFail();
            $kabidUser   = User::where('email', 'kabid.pbb@bapenda.go.id')->firstOrFail();

            // Ambil satu ID Kelurahan valid dari CSV (Contoh: 5315020021 - GOLO KONDENG)
            // Pastikan MasterDataSeeder sudah dijalankan sebelumnya
            $kelurahanId = '5315020021'; 

            $this->command->info('Menyiapkan skenario simulasi untuk Bidang PBB...');

            // ================================================================
            // 2. SKENARIO STAFF (Staf Pendataan PBB)
            // ================================================================
            
            // 2.1. Buat SKP Staf
            $skpStaf = Skp::create([
                'user_id'         => $stafUser->id,
                'nama_skp'        => 'Melakukan Pendataan Objek Pajak PBB di Wilayah Barat',
                'periode_mulai'   => Carbon::now()->startOfYear()->toDateString(),
                'periode_selesai' => Carbon::now()->endOfYear()->toDateString(),
                'rencana_aksi'    => 'Mendatangi wajib pajak door-to-door untuk pemutakhiran data SPPT',
                'indikator'       => 'Jumlah formulir SPOP yang terisi',
                'target'          => 500,
                // 'satuan' dihapus sesuai migrasi terakhir
            ]);

            // 2.2. Buat LKH Staf

            // LKH 1: DISETUJUI (ACC) oleh Kasubid
            LaporanHarian::create([
                'user_id'             => $stafUser->id,
                'skp_id'              => $skpStaf->id,
                'tanggal_laporan'     => Carbon::now()->subDays(2)->toDateString(),
                'waktu_mulai'         => '08:00:00',
                'waktu_selesai'       => '12:00:00',
                'deskripsi_aktivitas' => 'Melakukan verifikasi lapangan data PBB di Jl. Mawar No. 1-10.',
                'output_hasil_kerja'  => '10 Data SPPT terverifikasi valid.',
                'status'              => 'approved', // Status disetujui
                
                // Lokasi (Menggunakan Raw Query untuk Spatial Point - MySQL/MariaDB)
                'lokasi'              => DB::raw("ST_GeomFromText('POINT(120.123456 -3.123456)')"), 
                'lokasi_manual_text'  => 'Jl. Mawar, RT 01 RW 02',
                'master_kelurahan_id' => $kelurahanId,
                'is_luar_lokasi'      => false,

                // Data Validasi
                'validator_id'        => $kasubidUser->id,
                'waktu_validasi'      => Carbon::now()->subDays(2)->addHours(5),
                'komentar_validasi'   => 'Kerja bagus, lanjutkan ke RT sebelah besok.',
            ]);

            // LKH 2: DITOLAK oleh Kasubid
            LaporanHarian::create([
                'user_id'             => $stafUser->id,
                'skp_id'              => $skpStaf->id,
                'tanggal_laporan'     => Carbon::now()->subDays(1)->toDateString(),
                'waktu_mulai'         => '09:00:00',
                'waktu_selesai'       => '10:00:00',
                'deskripsi_aktivitas' => 'Rekapitulasi data harian di kantor.',
                'output_hasil_kerja'  => 'Laporan sementara excel.',
                'status'              => 'rejected', // Status ditolak
                
                'lokasi'              => DB::raw("ST_GeomFromText('POINT(120.123456 -3.123456)')"),
                'lokasi_manual_text'  => 'Kantor Bapenda',
                'master_kelurahan_id' => $kelurahanId,
                'is_luar_lokasi'      => false,

                // Data Validasi
                'validator_id'        => $kasubidUser->id,
                'waktu_validasi'      => Carbon::now()->subDays(1)->addHours(2),
                'komentar_validasi'   => 'Deskripsi kurang jelas, lampirkan file excel hasil rekapitulasinya.',
            ]);

            // LKH 3: BARU (Pending/Draft) - Belum diperiksa
            LaporanHarian::create([
                'user_id'             => $stafUser->id,
                'skp_id'              => $skpStaf->id,
                'tanggal_laporan'     => Carbon::now()->toDateString(),
                'waktu_mulai'         => '07:30:00',
                'waktu_selesai'       => '11:30:00',
                'deskripsi_aktivitas' => 'Rapat koordinasi teknis pendataan PBB bersama tim lapangan.',
                'output_hasil_kerja'  => 'Notulen rapat dan daftar hadir.',
                'status'              => 'pending', // Menunggu persetujuan
                
                'lokasi'              => DB::raw("ST_GeomFromText('POINT(120.123456 -3.123456)')"),
                'lokasi_manual_text'  => 'Ruang Rapat Lt. 2',
                'master_kelurahan_id' => $kelurahanId,
                'is_luar_lokasi'      => false,

                // Belum ada validasi
                'validator_id'        => $kasubidUser->id, // Target validator
                'waktu_validasi'      => null,
                'komentar_validasi'   => null,
            ]);

            // ================================================================
            // 3. SKENARIO KASUBID (Kasubid Pendataan PBB)
            // ================================================================

            // 3.1. Buat SKP Kasubid
            $skpKasubid = Skp::create([
                'user_id'         => $kasubidUser->id,
                'nama_skp'        => 'Pengendalian Operasional Pendataan PBB Tingkat Kabupaten',
                'periode_mulai'   => Carbon::now()->startOfYear()->toDateString(),
                'periode_selesai' => Carbon::now()->endOfYear()->toDateString(),
                'rencana_aksi'    => 'Supervisi dan monitoring kinerja staf pendataan di seluruh kecamatan',
                'indikator'       => 'Persentase peningkatan basis data pajak',
                'target'          => 100, // persen
            ]);

            // 3.2. Buat LKH Kasubid

            // LKH 1: DISETUJUI (ACC) oleh Kabid
            LaporanHarian::create([
                'user_id'             => $kasubidUser->id,
                'skp_id'              => $skpKasubid->id,
                'tanggal_laporan'     => Carbon::now()->subDays(2)->toDateString(),
                'waktu_mulai'         => '13:00:00',
                'waktu_selesai'       => '16:00:00',
                'deskripsi_aktivitas' => 'Melakukan evaluasi kinerja mingguan staf pendataan.',
                'output_hasil_kerja'  => 'Laporan Evaluasi Kinerja Mingguan.',
                'status'              => 'approved',
                
                'lokasi'              => DB::raw("ST_GeomFromText('POINT(120.123456 -3.123456)')"),
                'lokasi_manual_text'  => 'Ruang Rapat Bidang PBB',
                'master_kelurahan_id' => $kelurahanId,
                'is_luar_lokasi'      => false,

                'validator_id'        => $kabidUser->id,
                'waktu_validasi'      => Carbon::now()->subDays(2)->addHours(4),
                'komentar_validasi'   => 'Laporan diterima. Tingkatkan target minggu depan.',
            ]);

            // LKH 2: DITOLAK oleh Kabid
            LaporanHarian::create([
                'user_id'             => $kasubidUser->id,
                'skp_id'              => $skpKasubid->id,
                'tanggal_laporan'     => Carbon::now()->subDays(1)->toDateString(),
                'waktu_mulai'         => '10:00:00',
                'waktu_selesai'       => '12:00:00',
                'deskripsi_aktivitas' => 'Koordinasi dengan pihak bank terkait pembayaran.',
                'output_hasil_kerja'  => 'Draft MoU.',
                'status'              => 'rejected',
                
                'lokasi'              => DB::raw("ST_GeomFromText('POINT(120.123456 -3.123456)')"),
                'lokasi_manual_text'  => 'Kantor Bank Daerah',
                'master_kelurahan_id' => $kelurahanId,
                'is_luar_lokasi'      => true, // Dinas Luar

                'validator_id'        => $kabidUser->id,
                'waktu_validasi'      => Carbon::now()->subDays(1)->addHours(3),
                'komentar_validasi'   => 'Mohon lampirkan foto dokumentasi pertemuan.',
            ]);

            // LKH 3: MENUNGGU (Pending)
            LaporanHarian::create([
                'user_id'             => $kasubidUser->id,
                'skp_id'              => $skpKasubid->id,
                'tanggal_laporan'     => Carbon::now()->toDateString(),
                'waktu_mulai'         => '08:00:00',
                'waktu_selesai'       => '09:00:00',
                'deskripsi_aktivitas' => 'Briefing pagi bersama seluruh staf bidang PBB.',
                'output_hasil_kerja'  => 'Arahan kerja harian tersampaikan.',
                'status'              => 'pending',
                
                'lokasi'              => DB::raw("ST_GeomFromText('POINT(120.123456 -3.123456)')"),
                'lokasi_manual_text'  => 'Halaman Kantor',
                'master_kelurahan_id' => $kelurahanId,
                'is_luar_lokasi'      => false,

                'validator_id'        => $kabidUser->id,
                'waktu_validasi'      => null,
                'komentar_validasi'   => null,
            ]);
        });

        $this->command->info('Data simulasi SKP dan LKH berhasil dibuat untuk Staf PBB dan Kasubid PBB.');
    }
}