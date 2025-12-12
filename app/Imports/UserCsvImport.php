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

    /**
     * Mapping: Header CSV → Internal Key
     */
    private $map = [
        'nip'              => 'nip',
        'nama'             => 'name',
        'nama_role'        => 'nama_role',
        'jabatan_atasan'   => 'jabatan_atasan',
        'unit'             => 'nama_unit',
        'jabatan'          => 'nama_jabatan',
        'bidang'           => 'nama_bidang',
    ];

    private function col($row, $key)
    {
        // Cari header CSV yang sesuai internal key
        $csvKey = array_search($key, $this->map);

        return $csvKey ? ($row[$csvKey] ?? null) : null;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            // ==========================
            // 1. Ambil nilai mapping
            // ==========================
            $nip         = $this->col($row, 'nip');
            $name        = $this->col($row, 'name');
            $roleName    = $this->col($row, 'nama_role');
            $jabatanAtasan  = $this->col($row, 'jabatan_atasan');
            $unitNama    = $this->col($row, 'nama_unit');
            $jabatanNama = $this->col($row, 'nama_jabatan');
            $bidangNama  = $this->col($row, 'nama_bidang');

            // ==========================
            // 2. VALIDASI DATA MINIMAL
            // ==========================
            if (!$nip) {
                $this->errors[] = "Baris " . ($index + 2) . ": NIP kosong.";
                continue;
            }

            if (!$name) {
                $this->errors[] = "Baris " . ($index + 2) . ": NAMA kosong.";
                continue;
            }

            if (!$roleName) {
                $this->errors[] = "Baris " . ($index + 2) . ": ROLE kosong.";
                continue;
            }

            // ==========================
            // 3. CEK ATASAN
            // ==========================
            $atasan = null;

            if (!empty($jabatanAtasan)) {

                // 1. Cari jabatan atasan
                $jabatanAtasanModel = Jabatan::where('nama_jabatan', 'ilike', $jabatanAtasan)->first();

                if (!$jabatanAtasanModel) {
                    $this->errors[] = "Baris " . ($index + 2) . ": Jabatan atasan '{$jabatanAtasan}' tidak ditemukan.";
                    continue;
                }

                // 2. Cari user yang memiliki jabatan itu
                $atasan = User::where('jabatan_id', $jabatanAtasanModel->id)->first();

                if (!$atasan) {
                    $this->errors[] = "Baris " . ($index + 2) . ": Tidak ada user dengan jabatan atasan '{$jabatanAtasan}'.";
                    continue;
                }
            }

            // ==========================
            // 4. AUTO CREATE ROLE
            // ==========================
            $role = Role::firstOrCreate(
                ['nama_role' => $roleName],
                ['nama_role' => $roleName]
            );

            // ==========================
            // 5. AUTO CREATE UNIT KERJA
            // ==========================
            $unit = null;
            if (!empty($unitNama)) {
                $unit = UnitKerja::firstOrCreate(
                    ['nama_unit' => $unitNama],
                    ['nama_unit' => $unitNama]
                );
            }

            // ==========================
            // 6. AUTO CREATE JABATAN
            // ==========================
            $jabatan = null;
            if (!empty($jabatanNama)) {
                $jabatan = Jabatan::firstOrCreate(
                    ['nama_jabatan' => $jabatanNama],
                    ['nama_jabatan' => $jabatanNama]
                );
            }

            // ==========================
            // 7. AUTO CREATE BIDANG
            // ==========================
            $bidang = null;
            if (!empty($bidangNama)) {
                $bidang = Bidang::firstOrCreate(
                    ['nama_bidang' => $bidangNama],
                    ['nama_bidang' => $bidangNama, 'unit_kerja_id' => $unit?->id]
                );
            }

            // ==========================
            // 8. CEK DUPLIKASI USER
            // ==========================
            $existing = User::where('nip', $nip)->first();
            if ($existing) {
                $this->errors[] = "Baris " . ($index + 2) . ": NIP '{$nip}' sudah terdaftar.";
                continue;
            }

            // ==========================
            // 9. BUAT USER BARU
            // ==========================
            $user = User::create([
                'name'          => $name,
                'nip'           => $nip,
                'username'      => $nip,
                'password'      => Hash::make($nip),
                'unit_kerja_id' => $unit?->id,
                'jabatan_id'    => $jabatan?->id,
                'bidang_id'     => $bidang?->id,
                'atasan_id'     => $atasan?->id,
                'is_active'     => true,
            ]);

            // ==========================
            // 10. ASSIGN ROLE
            // ==========================
            $user->roles()->sync([$role->id]);
        }
    }
}
