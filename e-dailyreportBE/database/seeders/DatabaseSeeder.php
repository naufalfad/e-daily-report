<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil seeder yang kita buat
        $this->call([
            RolesSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}