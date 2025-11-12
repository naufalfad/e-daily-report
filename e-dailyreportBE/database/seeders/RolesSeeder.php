<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role; // <-- Jangan lupa import

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['nama_role' => 'Admin']);
        Role::create(['nama_role' => 'Kadis']);
        Role::create(['nama_role' => 'Penilai']);
        Role::create(['nama_role' => 'Pegawai']);
    }
}