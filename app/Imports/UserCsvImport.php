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
use Exception;

class UserCsvImport implements ToCollection
{
    public $errors = [];

    private $unitKerjaMap;
    private $jabatanMap;
    private $bidangMap;
    private $roleMap;

    public function __construct()
    {
        $this->unitKerjaMap = UnitKerja::pluck('id', 'nama_unit')
            ->mapWithKeys(fn($id, $nama) => [Str::lower(trim($nama)) => $id])
            ->toArray();

        $this->jabatanMap = Jabatan::pluck('id', 'nama_jabatan')
            ->mapWithKeys(fn($id, $nama) => [Str::lower(trim($nama)) => $id])
            ->toArray();

        // BidangMap stores both Bidang and Sub Bidang names
        $this->bidangMap = Bidang::pluck('id', 'nama_bidang')
            ->mapWithKeys(fn($id, $nama) => [Str::lower(trim($nama)) => $id])
            ->toArray();

        $this->roleMap = Role::pluck('id', 'nama_role') 
            ->mapWithKeys(fn($id, $nama) => [Str::lower(trim($nama)) => $id])
            ->toArray();
            
        // Map alias for typos found in Excel to correct Bidang ID
        $this->setupBidangAliases();
    }
    
    private function setupBidangAliases()
    {
        // Aliases for common typos and literal strings in the Excel file -> Map to Clean DB Names
        $aliases = [
            'bidang pbbp2 dan bphtb' => 'bidang pbb dan bphtb',
            'sub bidang pendaftaran dan pendataan' => 'sub bidang pendataan dan pendaftaran',
            
            // From Excel Sub Bidang Typos
            'sub bidang pengembangan sistem informatika dan inovasi pendaptan daerah' => 'sub bidang pengembangan sistem informatika dan inovasi pendapatan daerah',
            'sub bidang pendataan dan pendaftran pbb dan bphtb' => 'sub bidang pendataan dan pendaftaran pbb dan bphtb',
            'sub bidang penagihan retitusi pbb dan bphtb' => 'sub bidang penagihan dan restitusi pbb dan bphtb',
            'sub bidang penagihan restitusi pbb dan bphtb' => 'sub bidang penagihan dan restitusi pbb dan bphtb',
            'sub bidang pendataan dan pendaftaran  pbb dan bphtb' => 'sub bidang pendataan dan pendaftaran pbb dan bphtb',
            'sub bidang pendataan pbb dan bphtb' => 'sub bidang pendataan dan pendaftaran pbb dan bphtb',
            'sub bidang  pendataan dan pendaftaran pajak' => 'sub bidang pendataan dan pendaftaran pajak',
            'sub bidang pendataan dan pendaftaran' => 'sub bidang pendataan dan pendaftaran pajak',
            'sub bidang pemeriksa pajak, konsultasi, keberatan dan banding' => 'sub bidang pemeriksaan, konsultasi, keberatan dan banding',
            'sub bidang pemeriksa pajak, konsultasi keberatan dan banding' => 'sub bidang pemeriksaan, konsultasi, keberatan dan banding',
            'sub bagian penagihan' => 'sub bidang penagihan',
            'sub bidang retribusi dan pendapatan daerah lainnya' => 'sub bidang retribusi dan evaluasi pendapatan daerah',
            'sub bidang umum' => 'sub bagian umum',
            'sub bagian keuangan (pmb. bendahara penerima)' => 'sub bagian perencanaan dan keuangan',
            'sub bagian keuangan (bdh.penerima)' => 'sub bagian perencanaan dan keuangan',
            'sub bagian umum (bendahara barang)' => 'sub bagian umum',
            'sub bagian keuangan' => 'sub bagian perencanaan dan keuangan',
        ];
        foreach ($aliases as $typo => $correct) {
            if (isset($this->bidangMap[$correct]) && !isset($this->bidangMap[$typo])) {
                $this->bidangMap[$typo] = $this->bidangMap[$correct];
            }
        }
    }

    public function collection(Collection $rows)
    {
        // 1. Detect Format
        $isAbsenFormat = false;
        $standardHeaders = [];
        
        // Cek beberapa baris pertama untuk menentukan format
        for ($i = 0; $i < min(5, $rows->count()); $i++) {
            $rowStr = Str::lower(implode('', $rows[$i]->toArray()));
            // Jika ada baris dengan kata "sekretariat" atau "bidang" yang menyendiri, kemungkinan besar Absen Format
            if ($rows[$i][0] && empty($rows[$i][1]) && empty($rows[$i][2]) && empty($rows[$i][3])) {
                if (Str::contains(Str::lower($rows[$i][0]), ['sekretariat', 'bidang'])) {
                    $isAbsenFormat = true;
                    break;
                }
            }
            if (Str::contains($rowStr, 'absen apel')) {
                $isAbsenFormat = true;
                break;
            }
        }

        if (!$isAbsenFormat) {
            // Assume Standard CSV format
            // Cari baris header
            $headerRowIndex = 0;
            foreach ($rows as $index => $row) {
                $rowStr = Str::lower(implode('', $row->toArray()));
                if (Str::contains($rowStr, ['nama', 'name', 'nip'])) {
                    $headerRowIndex = $index;
                    foreach ($row as $colIndex => $colName) {
                        if (!empty($colName)) {
                            $standardHeaders[Str::lower(trim($colName))] = $colIndex;
                        }
                    }
                    break;
                }
            }
            
            // Proses Standard CSV
            foreach ($rows as $index => $row) {
                if ($index <= $headerRowIndex) continue;
                $rowNum = $index + 1;
                
                $nameVal = $row[$standardHeaders['nama'] ?? $standardHeaders['name'] ?? -1] ?? null;
                $nipVal  = $row[$standardHeaders['nip'] ?? -1] ?? null;
                $csvUnit    = $row[$standardHeaders['unit_kerja'] ?? -1] ?? '';
                $csvJabatan = $row[$standardHeaders['jabatan'] ?? -1] ?? '';
                $csvBidang  = $row[$standardHeaders['bidang'] ?? -1] ?? '';
                $csvRole    = $row[$standardHeaders['role'] ?? -1] ?? '';
                $csvPangkat = $row[$standardHeaders['pangkat_golongan'] ?? $standardHeaders['pangkat'] ?? -1] ?? '';
                
                $this->processUser($rowNum, $nameVal, $nipVal, $csvUnit, $csvJabatan, $csvBidang, $csvRole, $csvPangkat);
            }
            
        } else {
            // Proses Format Absen Perbidang
            $currentBidangName = null;
            $currentBidangId = null;
            
            foreach ($rows as $index => $row) {
                $rowNum = $index + 1;
                
                $col0 = trim((string)($row[0] ?? ''));
                $col1 = trim((string)($row[1] ?? ''));
                $col2 = trim((string)($row[2] ?? ''));
                $col3 = trim((string)($row[3] ?? ''));
                $col4 = trim((string)($row[4] ?? '')); // Jabatan

                if (empty($col0) && empty($col1) && empty($col2)) continue;

                // Cek apakah baris ini adalah header Bidang (hanya kolom 0 yg terisi teks)
                if (!empty($col0) && empty($col1) && empty($col2) && empty($col4)) {
                    if (!is_numeric($col0) && Str::lower($col0) !== 'no' && Str::lower($col0) !== 'sekretariat' && !Str::contains(Str::lower($col0), 'bidang')) {
                        // might be SEKRETARIAT or BIDANG PAJAK etc.
                        // wait, SEKRETARIAT doesn't contain 'bidang'. Let's just check if it's not 'no' and not numeric.
                        if ($col0 !== 'NO') {
                            $currentBidangName = $col0;
                            $currentBidangId = $this->bidangMap[Str::lower($currentBidangName)] ?? null;
                        }
                    } else if (Str::lower($col0) === 'sekretariat' || Str::contains(Str::lower($col0), 'bidang')) {
                        $currentBidangName = $col0;
                        $currentBidangId = $this->bidangMap[Str::lower($currentBidangName)] ?? null;
                    }
                    continue;
                }

                // Cek apakah baris ini adalah baris data pegawai (kolom 0 adalah angka/NO)
                if (is_numeric($col0) && !empty($col1)) {
                    $nameVal = $col1;
                    $nipVal  = $col2;
                    $csvPangkat = $col3;
                    $csvJabatan = $col4;
                    
                    // Untuk format absen, Sub Bidang bisa diekstrak dari Jabatan jika ada
                    $csvBidang = $currentBidangName; // Default ke Bidang induk
                    
                    // Coba ekstrak Sub Bidang dari Jabatan
                    // Misal: KEPALA SUB BIDANG PENDATAAN...
                    if (preg_match('/(?:Plt\.\s*)?(?:KEPALA|PELAKSANA|SUB BIDANG)\s+(SUB\s+(?:BIDANG|BAGIAN)\s+.*)/i', $csvJabatan, $matches)) {
                        $extractedSubBidang = trim($matches[1]);
                        // Cek apakah Sub Bidang ini ada di map
                        if (isset($this->bidangMap[Str::lower($extractedSubBidang)])) {
                            $csvBidang = $extractedSubBidang;
                        }
                    }

                    // Role default untuk staf/pelaksana, dll
                    $csvRole = 'staf';
                    if (Str::contains(Str::lower($csvJabatan), ['kepala badan', 'kepala dinas', 'kadis'])) {
                        $csvRole = 'kadis';
                    }

                    $this->processUser($rowNum, $nameVal, $nipVal, 'badan pendapatan daerah', $csvJabatan, $csvBidang, $csvRole, $csvPangkat);
                }
            }
        }
    }

    private function resolveJabatan($jabatanStr)
    {
        $jabatanStr = Str::lower(trim($jabatanStr));
        if (Str::contains($jabatanStr, 'plt. kepala bidang') || Str::contains($jabatanStr, 'plt. kepla bidang')) {
            return 'plt. kepala bidang';
        } elseif (Str::contains($jabatanStr, 'plt. kepala sub')) {
            return 'plt. kepala sub bidang';
        } elseif (Str::contains($jabatanStr, 'kepala bidang') || Str::contains($jabatanStr, 'kepla bidang')) {
            return 'kepala bidang';
        } elseif (Str::contains($jabatanStr, 'kepala sub bagian')) {
            return 'kepala sub bagian';
        } elseif (Str::contains($jabatanStr, 'kepala sub bidang')) {
            return 'kepala sub bidang';
        } elseif (Str::contains($jabatanStr, 'kepala badan') || $jabatanStr === 'kepala' || $jabatanStr === 'plt. kepala' || $jabatanStr === 'sekretaris') {
            return $jabatanStr === 'sekretaris' ? 'sekretaris' : 'kepala badan';
        } elseif (Str::contains($jabatanStr, 'pranata komputer')) {
            return 'pranata komputer ahli pertama';
        } elseif (Str::contains($jabatanStr, 'bendahara pengeluaran')) {
            return 'bendahara pengeluaran';
        } elseif (Str::contains($jabatanStr, 'bendahara penerima')) {
            return (Str::contains($jabatanStr, 'pmb.') || Str::contains($jabatanStr, 'pembantu')) 
                ? 'pembantu bendahara penerima' 
                : 'bendahara penerima';
        } elseif (Str::contains($jabatanStr, 'bendahara barang')) {
            return 'bendahara barang';
        } elseif (Str::contains($jabatanStr, 'cpns')) {
            return 'cpns';
        } elseif (Str::contains($jabatanStr, 'pppk')) {
            return 'pppk';
        }
        return 'pelaksana';
    }

    private function processUser($rowNum, $nameVal, $nipVal, $csvUnit, $csvJabatan, $csvBidang, $csvRole, $csvPangkat)
    {
        try {
            if (empty($nameVal) && empty($nipVal)) return;

            if (empty($nameVal)) {
                $this->errors[] = "Baris {$rowNum}: Kolom Nama kosong.";
                return;
            }

            $cleanNip = preg_replace('/[^0-9]/', '', $nipVal);

            if (empty($cleanNip)) {
                $this->errors[] = "Baris {$rowNum}: NIP Kosong/Tidak Valid.";
                return;
            }

            $csvUnit    = Str::lower(trim($csvUnit ?? ''));
            $csvJabatan = Str::lower(trim($csvJabatan ?? ''));
            $csvBidang  = Str::lower(trim($csvBidang ?? ''));
            $csvRole    = Str::lower(trim($csvRole ?? ''));

            $unitId = $this->unitKerjaMap[$csvUnit] ?? 1; // Default ke 1 (Bapenda)

            // Resolve canonical Jabatan
            $canonicalJabatan = $this->resolveJabatan($csvJabatan);
            $jabatanId = $this->jabatanMap[$canonicalJabatan] ?? null;
            if (!$jabatanId && !empty($csvJabatan)) {
                // Fallback to strict map if it wasn't matched properly
                $jabatanId = $this->jabatanMap[$csvJabatan] ?? null;
                if (!$jabatanId) {
                    $this->errors[] = "Baris {$rowNum} [{$nameVal}]: Jabatan '{$csvJabatan}' tidak dapat dipetakan.";
                    return;
                }
            }

            $bidangId = null;
            if (!empty($csvBidang)) {
                $bidangId = $this->bidangMap[$csvBidang] ?? null;
                if (!$bidangId) {
                    $this->errors[] = "Baris {$rowNum} [{$nameVal}]: Bidang/Sub Bidang '{$csvBidang}' tidak ditemukan di sistem.";
                    return;
                }
            }

            $roleId = $this->roleMap[$csvRole] ?? null;
            
            $generatedUsername = $cleanNip;
            $generatedPassword = Hash::make($cleanNip);

            $user = User::updateOrCreate(
                ['nip' => $cleanNip],
                [
                    'name'          => $nameVal,
                    'email'         => null,
                    'username'      => $generatedUsername,
                    'password'      => $generatedPassword,
                    'pangkat'       => $csvPangkat,
                    'unit_kerja_id' => $unitId,
                    'jabatan_id'    => $jabatanId,
                    'bidang_id'     => $bidangId,
                    'is_active'     => true,
                ]
            );

            if ($roleId) {
                $user->roles()->sync([$roleId]);
            }

        } catch (Exception $e) {
            Log::error("Import Error Row {$rowNum}: " . $e->getMessage());
            $this->errors[] = "Baris {$rowNum}: Gagal simpan ke database ({$e->getMessage()})";
        }
    }
}