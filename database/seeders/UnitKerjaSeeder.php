<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitKerja;

class UnitKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Kita kunci ID = 1 sebagai "Root" instansi.
        // Ini menjaga konsistensi relasi dengan tabel Bidang, User, dll.
        UnitKerja::updateOrCreate(
            ['id' => 1], 
            [
                'nama_unit' => 'Badan Pendapatan Daerah Kabupaten Timika',
                // Field di bawah ini opsional, sesuaikan dengan kolom di migration Anda
                // 'alamat'    => 'Jl. Cenderawasih SP 2, Timika',
                // 'email'     => 'bapenda@mimikakab.go.id',
                // 'telepon'   => '(0901) 123456' 
            ]
        );

        $this->command->info('Unit Kerja Utama (BAPENDA) berhasil di-seed.');
    }
}