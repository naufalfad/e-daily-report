<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Jabatan;
use App\Models\Bidang;
use App\Models\UnitKerja;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Exception;

class UserCsvImport implements ToCollection, WithHeadingRow
{
    /**
     * Menyimpan daftar error baris per baris untuk feedback ke user
     */
    public $errors = [];

    /**
     * In-Memory Cache untuk Master Data
     * Disimpan dalam array untuk menghindari query berulang (N+1 Problem)
     */
    private $unitKerjaMap;
    private $jabatanMap;
    private $bidangMap;
    private $roleMap;

    public function __construct()
    {
        // =====================================================================
        // FASE 3: LOADING DATA MASTER KE MEMORY (CACHING)
        // =====================================================================
        // Kita load seluruh data master ke RAM sekali saja saat class di-init.
        // Key array di-lowercase agar pencarian case-insensitive.
        
        // 1. Unit Kerja (Mapping: nama_unit -> id)
        $this->unitKerjaMap = UnitKerja::pluck('id', 'nama_unit')
            ->mapWithKeys(fn($id, $nama) => [Str::lower(trim($nama)) => $id])
            ->toArray();

        // 2. Jabatan (Mapping: nama_jabatan -> id)
        $this->jabatanMap = Jabatan::pluck('id', 'nama_jabatan')
            ->mapWithKeys(fn($id, $nama) => [Str::lower(trim($nama)) => $id])
            ->toArray();

        // 3. Bidang (Mapping: nama_bidang -> id)
        $this->bidangMap = Bidang::pluck('id', 'nama_bidang')
            ->mapWithKeys(fn($id, $nama) => [Str::lower(trim($nama)) => $id])
            ->toArray();

        // 4. Role (Mapping: nama_role -> id)
        // Sesuai request: 'kadis', 'penilai', 'staf', 'superadmin'
        $this->roleMap = Role::pluck('id', 'nama_role') 
            ->mapWithKeys(fn($id, $nama) => [Str::lower(trim($nama)) => $id])
            ->toArray();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Baris Excel (Header ada di baris 1)

            try {
                // Skip jika baris kosong total (nama & nip tidak ada)
                // Cek apakah kolomnya 'name' atau 'nama'
                $nameVal = $row['name'] ?? $row['nama'] ?? null;
                $nipVal  = $row['nip'] ?? null;

                // Skip jika baris kosong total
                if (empty($nameVal) && empty($nipVal)) continue;

                // Validasi Nama Wajib Ada (agar tidak error Undefined index)
                if (empty($nameVal)) {
                    $this->errors[] = "Baris {$rowNum}: Kolom Nama kosong atau Header salah (Gunakan 'name' atau 'nama').";
                    continue;
                }

                // =============================================================
                // 1. SANITASI & NORMALISASI INPUT
                // =============================================================
                
                // Bersihkan NIP dari spasi, strip, dll. Contoh: "1998 1010" -> "19981010"
                $cleanNip = preg_replace('/[^0-9]/', '', $row['nip']);

                // Validasi NIP Wajib Ada
                if (empty($cleanNip)) {
                    $this->errors[] = "Baris {$rowNum}: NIP Kosong/Tidak Valid.";
                    continue;
                }

                // Normalisasi string lookup (lowercase & trim) agar cocok dengan key di Map
                $csvUnit    = Str::lower(trim($row['unit_kerja'] ?? ''));
                $csvJabatan = Str::lower(trim($row['jabatan'] ?? ''));
                $csvBidang  = Str::lower(trim($row['bidang'] ?? ''));
                $csvRole    = Str::lower(trim($row['role'] ?? '')); // e.g. "kadis", "penilai"

                // =============================================================
                // 2. STRICT LOOKUP (VALIDASI DATA MASTER)
                // =============================================================

                // A. Validasi Unit Kerja
                $unitId = $this->unitKerjaMap[$csvUnit] ?? null;
                // Jika Unit Kerja wajib ada, uncomment baris di bawah:
                /*
                if (!$unitId) {
                    $this->errors[] = "Baris {$rowNum} [{$row['nama']}]: Unit Kerja '{$row['unit_kerja']}' tidak ditemukan.";
                    continue; 
                }
                */
                // Default ke 1 (Bapenda) jika null/tidak ketemu (Opsional, sesuaikan kebutuhan)
                if (!$unitId) $unitId = 1; 

                // B. Validasi Jabatan
                $jabatanId = $this->jabatanMap[$csvJabatan] ?? null;
                if (!$jabatanId && !empty($csvJabatan)) {
                    // Log error tapi mungkin tetap lanjut import user tanpa jabatan?
                    // Disini saya buat strict: Jabatan salah = Skip user.
                    $this->errors[] = "Baris {$rowNum} [{$nameVal}]: Jabatan '{$row['jabatan']}' tidak ditemukan di sistem.";
                    continue; 
                }

                // C. Validasi Bidang
                // Bidang boleh kosong (misal Kepala Badan tidak punya bidang)
                $bidangId = null;
                if (!empty($csvBidang)) {
                    $bidangId = $this->bidangMap[$csvBidang] ?? null;
                    if (!$bidangId) {
                        $this->errors[] = "Baris {$rowNum} [{$nameVal}]: Bidang '{$row['bidang']}' tidak ditemukan di sistem.";
                        continue;
                    }
                }

                // D. Validasi Role
                $roleId = $this->roleMap[$csvRole] ?? null;
                
                // Jika role di CSV kosong atau salah ketik, default ke 'staf' (ID 4 di contoh Anda)
                // Atau skip jika wajib valid. Di sini saya set default ke null jika tidak ketemu.
                if (!$roleId && !empty($csvRole)) {
                     $this->errors[] = "Baris {$rowNum} [{$nameVal}]: Role '{$row['role']}' tidak valid (Gunakan: kadis, penilai, atau staf).";
                     continue; 
                }

                // =============================================================
                // 3. EKSEKUSI DATABASE (UPSERT USER)
                // =============================================================
                
                // Sesuai request: Username & Password diambil dari NIP
                $generatedUsername = $cleanNip;
                $generatedPassword = Hash::make($cleanNip);

                $user = User::updateOrCreate(
                    ['nip' => $cleanNip], // Kunci pencarian (Unique Key)
                    [
                        'name'          => $nameVal,
                        'email'         => null, // Email nullable sesuai migrasi terakhir
                        'username'      => $generatedUsername, // [REQUEST] Username = NIP
                        'password'      => $generatedPassword, // [REQUEST] Password = Hash(NIP)
                        
                        // Metadata Pegawai
                        'pangkat'       => $row['pangkat_golongan'] ?? null,
                        'unit_kerja_id' => $unitId,
                        'jabatan_id'    => $jabatanId,
                        'bidang_id'     => $bidangId,
                        
                        // Status & System
                        'is_active'     => true,
                    ]
                );

                // =============================================================
                // 4. ASSIGN ROLE (SYNC)
                // =============================================================
                if ($roleId) {
                    // Sync akan menghapus role lama dan mengganti dengan yang baru dari CSV
                    $user->roles()->sync([$roleId]);
                } else {
                    // Jika di CSV tidak ada role, apakah role lama dihapus atau dibiarkan?
                    // Default behavior: biarkan role lama jika CSV kosong.
                    // Jika ingin force set 'staf':
                    // $stafId = $this->roleMap['staf'] ?? 4;
                    // $user->roles()->sync([$stafId]);
                }

            } catch (Exception $e) {
                // Tangkap error sistem (misal database mati, duplikat key lain, dll)
                Log::error("Import Error Row {$rowNum}: " . $e->getMessage());
                $this->errors[] = "Baris {$rowNum}: Gagal simpan ke database ({$e->getMessage()})";
            }
        }
    }
}