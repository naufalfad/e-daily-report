<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Sesuai request: superadmin, kaban, penilai, staf
        // Disimpan ke kolom 'nama_role'
        $roles = [
            'superadmin',
            'kaban',
            'penilai',
            'staf'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['nama_role' => $roleName] // Kunci pencarian & data yang diinsert
            );
        }
        
        $this->command->info('RoleSeeder berhasil diperbaiki (Columns: nama_role).');
    }
}