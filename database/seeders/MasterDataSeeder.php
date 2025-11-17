<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Roles Sesuai Rancangan
        $roles = ['Super Admin', 'Pegawai', 'Penilai', 'Kadis'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['nama_role' => $role]);
        }

        // 2. Buat Jabatan Dummy
        $jabatans = ['Kepala Dinas', 'Kepala Bidang', 'Staff Pelaksana', 'Admin IT'];
        foreach ($jabatans as $jab) {
            Jabatan::firstOrCreate(['nama_jabatan' => $jab]);
        }

        // 3. Buat Unit Kerja Dummy
        $bapenda = UnitKerja::firstOrCreate(['nama_unit' => 'Bapenda Kabupaten Mimika', 'parent_id' => null]);
        UnitKerja::firstOrCreate(['nama_unit' => 'Bidang Pendapatan', 'parent_id' => $bapenda->id]);
        
        // 4. Buat 1 Akun Super Admin untuk Login Pertama kali
        $adminRole = Role::where('nama_role', 'Super Admin')->first();
        $jabatanAdmin = Jabatan::where('nama_jabatan', 'Admin IT')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@bapenda.mimika.go.id'],
            [
                'name' => 'Super Admin IT',
                'nip' => '199901012025011001',
                'password' => Hash::make('password123'),
                'unit_kerja_id' => $bapenda->id,
                'jabatan_id' => $jabatanAdmin->id,
            ]
        );

        // Attach Role
        if (!$admin->roles()->exists()) {
            $admin->roles()->attach($adminRole->id);
        }
    }
}