<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Bidang;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PatchPegawaiData extends Command
{
    protected $signature = 'patch:pegawai';
    protected $description = 'Mapping bidang_id, role_id, and atasan_id for imported users based on absen_clean.csv';

    public function handle()
    {
        $this->info('Starting data patching...');

        // Create "Dana Perimbangan" if it doesn't exist
        $danaPerimbangan = Bidang::firstOrCreate(
            ['nama_bidang' => 'Sub Bidang Dana Perimbangan'],
            [
                'unit_kerja_id' => 1,
                'parent_id' => 9, // Bidang Perencanaan
                'level' => 'sub_bidang'
            ]
        );

        $bidangMap = [
            "SEKRETARIAT" => 1,
            "SUB BAGIAN UMUM DAN KEPEGAWAIAN" => 2,
            "SUB BAGIAN UMUM" => 2,
            "SUB BAGIAN UMUM (BENDAHARA BARANG)" => 2,
            "SUB BAGIAN KEPEGAWAIAN" => 2,
            "SUB BIDANG UMUM" => 2,
            "SUB BAGIAN KEUANGAN" => 3,
            "SUB BAGIAN KEUANGAN (PMB. BENDAHARA PENERIMA)" => 3,
            "SUB BAGIAN KEUANGAN (BDH.PENERIMA)" => 3,
            "SUB BAGIAN PROGRAM" => 4,
            "BIDANG PAJAK" => 5,
            "SUB BIDANG PENDATAAN DAN PENDAFTARAN" => 6,
            "SUB BIDANG PENDATAAN DAN PENDAFTARAN PAJAK" => 6,
            "SUB BIDANG  PENDATAAN DAN PENDAFTARAN PAJAK" => 6,
            "SUB BIDANG PERHITUNGAN DAN PENETAPAN PAJAK DAERAH" => 7,
            "SUB BIDANG PEMERIKSA PAJAK, KONSULTASI, KEBERATAN DAN BANDING" => 8,
            "SUB BIDANG PEMERIKSA PAJAK, KONSULTASI KEBERATAN DAN BANDING" => 8,
            "BIDANG PERENCANAAN DAN PENGEMBANGAN PENDAPATAN DAERAH" => 9,
            "SUB BIDANG REGULASI PENDAPATAN DAERAH" => 10,
            "SUB BIDANG RETRIBUSI DAN EVALUASI PENDAPATAN DAERAH" => 11,
            "SUB BIDANG RETRIBUSI DAN PENDAPATAN DAERAH LAINNYA" => 11,
            "SUB BIDANG PENGEMBANGAN SISTEM INFORMATIKA DAN INOVASI PENDAPATAN DAERAH" => 12,
            "SUB BIDANG PENGEMBANGAN SISTEM INFORMATIKA DAN INOVASI PENDAPTAN DAERAH" => 12,
            "SUB BIDANG DANA PERIMBANGAN" => $danaPerimbangan->id,
            "BIDANG PBB DAN BPHTB" => 13,
            "BIDANG PBBP2 DAN BPHTB" => 13,
            "SUB BIDANG PENDATAAN DAN PENDAFTARAN PBB DAN BPHTB" => 14,
            "SUB BIDANG PENDATAAN DAN PENDAFTARAN  PBB DAN BPHTB" => 14,
            "SUB BIDANG PENDATAAN PBB DAN BPHTB" => 14,
            "SUB BIDANG PENDATAAN DAN PENDAFTRAN PBB DAN BPHTB" => 14,
            "SUB BIDANG PENILAIAN DAN PENETAPAN PBB DAN BPHTB" => 15,
            "SUB BIDANG PENAGIHAN RESTITUSI PBB DAN BPHTB" => 16,
            "SUB BIDANG PENAGIHAN RETITUSI PBB DAN BPHTB" => 16,
            "BIDANG PEMBUKUAN DAN PELAPORAN" => 17,
            "SUB BIDANG PEMBUKUAN DAN PELAPORAN" => 18,
            "SUB BIDANG PEMERIKSAAN DAN VERIFIKASI" => 19,
            "SUB BIDANG PENAGIHAN" => 20,
            "SUB BAGIAN PENAGIHAN" => 20,
        ];

        $csvPath = base_path('absen_clean.csv');
        if (!file_exists($csvPath)) {
            $this->error('absen_clean.csv not found!');
            return;
        }

        $lines = file($csvPath);
        $headers = str_getcsv(array_shift($lines));
        
        $kadis = null;
        $sekretaris = null;
        
        // Pass 1: Set bidang_id and roles
        $this->info('Pass 1: Updating Bidang and Roles...');
        DB::beginTransaction();
        try {
            foreach ($lines as $line) {
                if (trim($line) == '') continue;
                $row = str_getcsv($line);
                if (count($row) < 6) continue;
                
                $nama = trim($row[0]);
                $nip = trim(str_replace(' ', '', $row[1]));
                $jabatanStr = strtoupper(trim($row[3]));
                $bidangStr = strtoupper(trim($row[4]));
                
                $user = User::where('nip', $nip)->first();
                if (!$user) {
                    $user = User::where('name', 'like', "%$nama%")->first();
                }
                
                if ($user) {
                    // Update bidang
                    if (isset($bidangMap[$bidangStr])) {
                        $user->bidang_id = $bidangMap[$bidangStr];
                    } else {
                        $this->warn("Bidang not mapped for $nama: $bidangStr");
                    }
                    
                    // Update Role
                    $roleId = 4; // staf
                    if (str_contains($jabatanStr, 'KEPALA BADAN')) {
                        $roleId = 2; // kadis
                        $kadis = $user;
                    } elseif (
                        str_contains($jabatanStr, 'SEKRETARIS') || 
                        str_contains($jabatanStr, 'KEPALA BIDANG') || 
                        str_contains($jabatanStr, 'KEPALA SUB BAGIAN') || 
                        str_contains($jabatanStr, 'KEPALA SUB BIDANG') ||
                        str_contains($jabatanStr, 'PLT. KEPALA') ||
                        str_contains($jabatanStr, 'PLT. KEPLA')
                    ) {
                        $roleId = 3; // penilai
                        if (str_contains($jabatanStr, 'SEKRETARIS')) {
                            $sekretaris = $user;
                        }
                    }
                    
                    $user->save();
                    
                    // Sync Role via pivot (bypass spatie if missing)
                    DB::table('user_roles')->updateOrInsert(
                        ['user_id' => $user->id],
                        ['role_id' => $roleId]
                    );
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error Pass 1: ' . $e->getMessage());
            return;
        }

        // Pass 2: Set Hierarchy (atasan_id)
        $this->info('Pass 2: Setting up Atasan Hierarchy...');
        DB::beginTransaction();
        try {
            $users = User::with('bidang')->get();
            
            // Re-fetch kadis and sekretaris just in case
            if (!$kadis) {
                $kadis = User::whereHas('roles', function($q){ $q->where('roles.id', 2); })->first();
            }
            if (!$sekretaris) {
                $sekretaris = User::whereHas('roles', function($q){ $q->where('roles.id', 3); })
                                  ->where('bidang_id', 1)->first();
            }

            foreach ($users as $user) {
                if ($user->username === 'superadmin' || $user->id === 1) continue;

                $userRoles = DB::table('user_roles')->where('user_id', $user->id)->pluck('role_id')->toArray();
                $isKadis = in_array(2, $userRoles);
                $isPenilai = in_array(3, $userRoles);
                $isStaf = in_array(4, $userRoles);
                
                $atasanId = null;
                $bidang = $user->bidang;

                if ($isKadis) {
                    $atasanId = null;
                } elseif ($isPenilai) {
                    // Check if it's Sekretaris or Kabid (level = bidang)
                    if ($bidang && $bidang->level === 'bidang') {
                        $atasanId = $kadis->id ?? null;
                    } elseif ($bidang && $bidang->level === 'sub_bidang') {
                        // If Kasubag (parent is Sekretariat)
                        if ($bidang->parent_id == 1) {
                            $atasanId = $sekretaris->id ?? $kadis->id ?? null;
                        } else {
                            // Find Kabid
                            $kabid = User::where('bidang_id', $bidang->parent_id)
                                ->whereHas('roles', function($q){ $q->where('roles.id', 3); })
                                ->first();
                            $atasanId = $kabid->id ?? $kadis->id ?? null;
                        }
                    }
                } elseif ($isStaf) {
                    if ($bidang) {
                        // Find direct head in the same bidang/sub_bidang
                        $head = User::where('bidang_id', $bidang->id)
                            ->whereHas('roles', function($q){ $q->where('roles.id', 3); })
                            ->where('id', '!=', $user->id)
                            ->first();
                        
                        if ($head) {
                            $atasanId = $head->id;
                        } else {
                            // Fallback to parent
                            if ($bidang->level === 'sub_bidang') {
                                if ($bidang->parent_id == 1) {
                                    $atasanId = $sekretaris->id ?? null;
                                } else {
                                    $parentHead = User::where('bidang_id', $bidang->parent_id)
                                        ->whereHas('roles', function($q){ $q->where('roles.id', 3); })
                                        ->first();
                                    $atasanId = $parentHead->id ?? null;
                                }
                            }
                        }
                    }
                }

                $user->atasan_id = $atasanId;
                $user->save();
            }

            DB::commit();
            $this->info('Hierarchy set successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error Pass 2: ' . $e->getMessage() . " on line " . $e->getLine());
        }

        $this->info('Patching Completed!');
    }
}
