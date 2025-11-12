<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // <-- Import User
use App\Models\Role; // <-- Import Role
use Illuminate\Support\Facades\Hash; // <-- Import Hash

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat User Admin
        $adminUser = User::create([
            'name' => 'Admin IT',
            'email' => 'admin@bapenda.go.id',
            'password' => Hash::make('password') // Ganti 'password' ini
            // Isi field lain jika perlu (nip, dll)
        ]);

        // 2. Cari Role 'Admin'
        $adminRole = Role::where('nama_role', 'Admin')->first();

        // 3. Hubungkan User Admin ke Role 'Admin'
        if ($adminUser && $adminRole) {
            $adminUser->roles()->attach($adminRole->id);
        }
    }
}