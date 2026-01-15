<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker    = Faker::create('id_ID');
        $password = Hash::make('password123');

        DB::beginTransaction();

        try {
            /**
             * ======================================================
             * 1. REFERENSI MASTER DATA
             * ======================================================
             */
            $unitKerjaId = UnitKerja::first()->id ?? 1;

            // Roles
            $roleSuperAdmin = Role::where('nama_role', 'superadmin')->value('id');
            // $roleKaban      = Role::where('nama_role', 'kaban')->value('id');
            // $rolePenilai    = Role::where('nama_role', 'penilai')->value('id');
            // $roleStaf       = Role::where('nama_role', 'staf')->value('id');

            // // Jabatans (sesuai JabatanSeeder final)
            // $jabKaban   = Jabatan::where('nama_jabatan', 'Kepala Badan')->value('id');
            // $jabKabid   = Jabatan::where('nama_jabatan', 'Kepala Bidang')->value('id');
            // $jabKasubag = Jabatan::where('nama_jabatan', 'Kepala Sub Bagian')->value('id');
            // $jabKasubid = Jabatan::where('nama_jabatan', 'Kepala Sub Bidang')->value('id');
            $jabStaf    = Jabatan::where('nama_jabatan', 'Staf Pelaksana')->value('id');

            /**
             * ======================================================
             * 2. KEPALA BADAN (TOP LEVEL)
             * ======================================================
             */
            // $kaban = User::firstOrCreate(
            //     ['username' => 'kaban'],
            //     [
            //         'name'          => 'Kepala Badan Pendapatan Daerah',
            //         'nip'           => '197401092006041001',
            //         'email'         => 'kaban@bapenda.go.id',
            //         'password'      => $password,
            //         'unit_kerja_id' => $unitKerjaId,
            //         'jabatan_id'    => $jabKaban,
            //         'atasan_id'     => null,
            //         'is_active'     => true,
            //     ]
            // );
            // $kaban->roles()->sync([$roleKaban, $rolePenilai]);

            /**
             * ======================================================
             * 3. SUPERADMIN (NON STRUKTURAL)
             * ======================================================
             */
            $admin = User::firstOrCreate(
                ['username' => 'superadmin'],
                [
                    'name'          => 'Super Administrator',
                    'nip'           => '000000000000000000',
                    'email'         => 'admin@bapenda.go.id',
                    'password'      => $password,
                    'unit_kerja_id' => $unitKerjaId,
                    'jabatan_id'    => $jabStaf,
                    'atasan_id'     => null,
                    'is_active'     => true,
                ]
            );
            $admin->roles()->sync([$roleSuperAdmin]);

            /**
             * ======================================================
             * 4. STRUKTUR BIDANG & SUB BIDANG
             * ======================================================
             */
            // $bidangInduk = Bidang::whereNull('parent_id')->get();

            // foreach ($bidangInduk as $bidang) {

            //     // Kepala Bidang / Sekretariat
            //     $kepalaBidang = User::create([
            //         'name'          => 'Kepala ' . $bidang->nama_bidang,
            //         'username'      => 'kabid.' . Str::slug($bidang->nama_bidang),
            //         'nip'           => $faker->unique()->numerify('19##########'),
            //         'email'         => Str::slug($bidang->nama_bidang) . '@bapenda.go.id',
            //         'password'      => $password,
            //         'unit_kerja_id' => $unitKerjaId,
            //         'jabatan_id'    => $jabKabid,
            //         'bidang_id'     => $bidang->id,
            //         'atasan_id'     => $kaban->id,
            //         'is_active'     => true,
            //     ]);
            //     $kepalaBidang->roles()->sync([$rolePenilai]);

            //     // Sub Bidang / Sub Bagian
            //     foreach ($bidang->children as $sub) {

            //         $isSubBagian = Str::contains($sub->nama_bidang, 'Sub Bagian');
            //         $jabatanSub = $isSubBagian ? $jabKasubag : $jabKasubid;

            //         $kepalaSub = User::create([
            //             'name'          => 'Kepala ' . $sub->nama_bidang,
            //             'username'      => 'kasub.' . Str::random(6),
            //             'nip'           => $faker->unique()->numerify('19##########'),
            //             'email'         => Str::slug($sub->nama_bidang) . '@bapenda.go.id',
            //             'password'      => $password,
            //             'unit_kerja_id' => $unitKerjaId,
            //             'jabatan_id'    => $jabatanSub,
            //             'bidang_id'     => $sub->id,
            //             'atasan_id'     => $kepalaBidang->id,
            //             'is_active'     => true,
            //         ]);
            //         $kepalaSub->roles()->sync([$rolePenilai]);

            //         // 2 staf per sub bidang
            //         for ($i = 1; $i <= 2; $i++) {
            //             $staf = User::create([
            //                 'name'          => 'Staf ' . $faker->firstName,
            //                 'username'      => 'staf.' . Str::random(8),
            //                 'nip'           => $faker->unique()->numerify('20##########'),
            //                 'email'         => $faker->unique()->safeEmail,
            //                 'password'      => $password,
            //                 'unit_kerja_id' => $unitKerjaId,
            //                 'jabatan_id'    => $jabStaf,
            //                 'bidang_id'     => $sub->id,
            //                 'atasan_id'     => $kepalaSub->id,
            //                 'is_active'     => true,
            //             ]);
            //             $staf->roles()->sync([$roleStaf]);
            //         }
            //     }
            // }

            DB::commit();
            $this->command->info('✅ UserSeeder berhasil — struktur organisasi dummy terbentuk.');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('❌ UserSeeder gagal: ' . $e->getMessage());
        }
    }
}
