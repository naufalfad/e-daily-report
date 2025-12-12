<?php

namespace App\Imports;

use App\Models\User;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use App\Models\Bidang;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserCsvImport implements ToCollection, WithHeadingRow
{
    public $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            // ==========================
            // 1. VALIDASI DATA MINIMAL
            // ==========================
            if (!isset($row['nip']) || empty($row['nip'])) {
                $this->errors[] = "Baris " . ($index + 2) . ": NIP kosong.";
                continue;
            }

            if (!isset($row['name']) || empty($row['name'])) {
                $this->errors[] = "Baris " . ($index + 2) . ": NAME kosong.";
                continue;
            }

            if (!isset($row['nama_role']) || empty($row['nama_role'])) {
                $this->errors[] = "Baris " . ($index + 2) . ": ROLE kosong.";
                continue;
            }

            // ==========================
            // 2. CEK ATASAN
            // ==========================
            $atasan = null;
            if (!empty($row['nama_atasan'])) {
                $atasan = User::where('name', 'ilike', $row['nama_atasan'])->first();

                if (!$atasan) {
                    $this->errors[] = "Baris " . ($index + 2) . ": Atasan '{$row['nama_atasan']}' tidak ditemukan.";
                    continue; // aturan Anda: jika atasan tidak ada → FAIL baris tersebut
                }
            }

            // ==========================
            // 3. AUTO CREATE ROLE
            // ==========================
            $role = Role::firstOrCreate(
                ['nama_role' => $row['nama_role']],
                ['nama_role' => $row['nama_role']]
            );

            // ==========================
            // 4. AUTO CREATE UNIT KERJA
            // ==========================
            $unit = null;
            if (!empty($row['nama_unit'])) {
                $unit = UnitKerja::firstOrCreate(
                    ['nama_unit' => $row['nama_unit']],
                    ['nama_unit' => $row['nama_unit']]
                );
            }

            // ==========================
            // 5. AUTO CREATE JABATAN
            // ==========================
            $jabatan = null;
            if (!empty($row['nama_jabatan'])) {
                $jabatan = Jabatan::firstOrCreate(
                    ['nama_jabatan' => $row['nama_jabatan']],
                    ['nama_jabatan' => $row['nama_jabatan']]
                );
            }

            // ==========================
            // 6. AUTO CREATE BIDANG
            // ==========================
            $bidang = null;
            if (!empty($row['nama_bidang'])) {
                $bidang = Bidang::firstOrCreate(
                    ['nama_bidang' => $row['nama_bidang']],
                    ['nama_bidang' => $row['nama_bidang'], 'unit_kerja_id' => $unit?->id]
                );
            }

            // ==========================
            // 7. CEK DUPLIKASI USER
            // ==========================
            $existing = User::where('nip', $row['nip'])->first();
            if ($existing) {
                $this->errors[] = "Baris " . ($index + 2) . ": NIP '{$row['nip']}' sudah terdaftar.";
                continue;
            }

            // ==========================
            // 8. BUAT USER BARU
            // ==========================
            $user = User::create([
                'name'          => $row['name'],
                'nip'           => $row['nip'],
                'username'      => $row['nip'], // sesuai permintaan
                'password'      => Hash::make($row['nip']), // hash nip
                'unit_kerja_id' => $unit?->id,
                'jabatan_id'    => $jabatan?->id,
                'bidang_id'     => $bidang?->id,
                'atasan_id'     => $atasan?->id,
                'is_active'     => true,
            ]);

            // ==========================
            // 9. ASSIGN ROLE
            // ==========================
            $user->roles()->sync([$role->id]);
        }
    }
}
