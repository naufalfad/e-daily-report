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

class UserSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');
        $password = Hash::make('password123'); // Password default

        $this->command->info('Memulai generate hierarchy User Bapenda...');

        DB::beginTransaction();

        try {
            // 1. AMBIL DATA REFERENSI (Pastikan seeder lain sudah jalan)
            $unitKerja = UnitKerja::first()->id ?? 1;

            // Roles
            $roleSuperAdmin = Role::where('nama_role', 'superadmin')->first()->id;
            $roleKaban      = Role::where('nama_role', 'kaban')->first()->id;
            $rolePenilai    = Role::where('nama_role', 'penilai')->first()->id;
            $roleStaf       = Role::where('nama_role', 'staf')->first()->id;

            // Jabatans
            $jabKaban        = Jabatan::where('nama_jabatan', 'Kepala Badan')->first()->id;
            $jabSekban       = Jabatan::where('nama_jabatan', 'Sekretaris')->first()->id;
            $jabKabid        = Jabatan::where('nama_jabatan', 'Kepala Bidang')->first()->id;
            $jabKasubag      = Jabatan::where('nama_jabatan', 'Kepala Sub Bagian')->first()->id;
            $jabKasubid      = Jabatan::where('nama_jabatan', 'Kepala Sub Bidang')->first()->id;
            $jabStaf         = Jabatan::where('nama_jabatan', 'Staf Pelaksana')->first()->id;
            $jabAdmin        = Jabatan::where('nama_jabatan', 'Administrator Sistem')->first()->id;

            // ======================================================
            // 2. CREATE KEPALA BADAN (Top Level)
            // ======================================================
            $kaban = User::create([
                'name'          => 'Kepala Badan',
                'username'      => 'kaban',
                'nip'           => '197301032007011031',
                'email'         => 'kaban@bapenda.go.id',
                'password'      => $password,
                'unit_kerja_id' => $unitKerja,
                'jabatan_id'    => $jabKaban,
                'atasan_id'     => null, // Tidak punya atasan
                'is_active'     => true,
                'alamat'        => 'Jl. Yos Sudarso, Nawaripi, Mimika'
            ]);
            // Assign Role: Kaban & Penilai
            $kaban->roles()->attach([$roleKaban, $rolePenilai]);
            $this->command->info('User Kaban created.');

            // ======================================================
            // 3. CREATE ADMINISTRATOR
            // ======================================================
            $admin = User::create([
                'name'          => 'Administrator Sistem',
                'username'      => 'admin',
                'nip'           => 'admin_system', // NIP dummy
                'email'         => 'admin@bapenda.mimika.go.id',
                'password'      => $password,
                'unit_kerja_id' => $unitKerja,
                'jabatan_id'    => $jabAdmin,
                'atasan_id'     => $kaban->id, // Secara struktur di bawah kaban/sekretariat, tapi role superadmin
                'is_active'     => true,
                'alamat'        => 'Ruang Server'
            ]);
            // Assign Role: Superadmin
            $admin->roles()->attach([$roleSuperAdmin]);


            // ======================================================
            // 4. GENERATE STRUKTURAL (Sekretariat, Bidang, Sub-Bidang)
            // ======================================================
            
            // Ambil semua Parent (Level Bidang/Sekretariat)
            $parentBidangs = Bidang::whereNull('parent_id')->get();

            foreach ($parentBidangs as $parent) {
                // Tentukan Jabatan untuk Kepala Parent (Sekretaris atau Kabid)
                $isSekretariat = stripos($parent->nama_bidang, 'Sekretariat') !== false;
                $jabatanParent = $isSekretariat ? $jabSekban : $jabKabid;
                
                // Buat User Kepala Bidang / Sekretaris
                $kepalaParent = User::create([
                    'name'          => $isSekretariat ? 'Sekretaris Bapenda' : 'Kabid ' . str_replace('Bidang ', '', $parent->nama_bidang),
                    'username'      => $isSekretariat ? 'sekban' : 'kabid.' . \Illuminate\Support\Str::slug(str_replace('Bidang ', '', $parent->nama_bidang)),
                    'nip'           => $faker->unique()->numerify('19##########00##'),
                    'email'         => $faker->unique()->email,
                    'password'      => $password,
                    'unit_kerja_id' => $unitKerja,
                    'jabatan_id'    => $jabatanParent,
                    'bidang_id'     => $parent->id,
                    'atasan_id'     => $kaban->id, // Atasannya Kaban
                    'is_active'     => true,
                    'alamat'        => $faker->address
                ]);
                $kepalaParent->roles()->attach($rolePenilai);

                $this->command->info('Created: ' . $kepalaParent->name);

                // --- PROSES SUB BIDANG / SUB BAGIAN (Children) ---
                $childBidangs = Bidang::where('parent_id', $parent->id)->get();

                foreach ($childBidangs as $child) {
                    // Tentukan Jabatan (Kasubag atau Kasubid)
                    $isSubBagian = stripos($child->nama_bidang, 'Sub Bagian') !== false;
                    $jabatanChild = $isSubBagian ? $jabKasubag : $jabKasubid;
                    $prefixName   = $isSubBagian ? 'Kasubag ' : 'Kasubid ';

                    // 4a. Buat User Kepala Sub Bidang
                    $kasubid = User::create([
                        'name'          => $prefixName . \Illuminate\Support\Str::limit(str_replace(['Sub Bidang ', 'Sub Bagian '], '', $child->nama_bidang), 20, ''),
                        'username'      => 'kasub.' . rand(100, 999), // Random simple username
                        'nip'           => $faker->unique()->numerify('19##########00##'),
                        'email'         => $faker->unique()->email,
                        'password'      => $password,
                        'unit_kerja_id' => $unitKerja,
                        'jabatan_id'    => $jabatanChild,
                        'bidang_id'     => $child->id,
                        'atasan_id'     => $kepalaParent->id, // Atasannya Kabid/Sekban
                        'is_active'     => true,
                        'alamat'        => $faker->address
                    ]);
                    $kasubid->roles()->attach($rolePenilai);

                    // 4b. Buat 2 Staf untuk setiap Sub Bidang
                    for ($i = 1; $i <= 2; $i++) {
                        $staf = User::create([
                            'name'          => 'Staf ' . $faker->firstName,
                            'username'      => 'staf.' . $faker->userName,
                            'nip'           => $faker->unique()->numerify('20##########00##'), // NIP lebih muda
                            'email'         => $faker->unique()->email,
                            'password'      => $password,
                            'unit_kerja_id' => $unitKerja,
                            'jabatan_id'    => $jabStaf,
                            'bidang_id'     => $child->id,
                            'atasan_id'     => $kasubid->id, // Atasannya Kasubid
                            'is_active'     => true,
                            'alamat'        => $faker->address
                        ]);
                        $staf->roles()->attach($roleStaf);
                    }
                }
            }

            // ======================================================
            // 5. CREATE JABATAN FUNGSIONAL TERTENTU (JFT)
            // ======================================================
            // Contoh: Auditor atau P2UPD yang langsung dibawah Kaban secara fungsional
            $fungsional = User::create([
                'name'          => 'Pejabat Fungsional Auditor',
                'username'      => 'auditor_utama',
                'nip'           => $faker->unique()->numerify('19##########00##'),
                'email'         => 'auditor@bapenda.go.id',
                'password'      => $password,
                'unit_kerja_id' => $unitKerja,
                'jabatan_id'    => $jabKabid, // Anggap setara Kabid atau jabatan khusus jika ada
                'atasan_id'     => $kaban->id, // Langsung ke Kaban
                'is_active'     => true,
            ]);
            $fungsional->roles()->attach($roleStaf); // Atau role khusus jika ada

            DB::commit();
            $this->command->info('User Seeder berhasil dijalankan! Semua hierarki terbentuk.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal seeding user: ' . $e->getMessage());
        }
    }
}