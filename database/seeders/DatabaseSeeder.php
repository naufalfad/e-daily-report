<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            //BidangSeeder::class,
            RoleSeeder::class,
            UnitKerjaSeeder::class,
            JabatanSeeder::class,
            StrukturOrganisasiSeeder::class,
            TupoksiSeeder::class,
            UserSeeder::class,
            WilayahPapuaTengahSeeder::class,
            // SkpSimulationSeeder::class,
            // LkhSimulationSeeder::class,
        ]);
    }
}
