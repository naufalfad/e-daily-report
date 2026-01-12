<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitKerja;
use App\Models\Bidang;
use App\Models\Tupoksi;
use App\Models\Role;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Support\Str;

class MasterDataController extends Controller
{
    // ===============================================================
    // DROPDOWN HELPERS (API untuk Form Select Frontend)
    // ===============================================================

    public function getRoles()
    {
        return response()->json(Role::select('id', 'nama_role')->get());
    }

    public function getJabatan()
    {
        // Tetap ambil semua jabatan tanpa batasan query disini
        // Validasi "Is Taken" sekarang ditangani dinamis via getBidangByUnitKerja
        $jabatan = Jabatan::select('id', 'nama_jabatan')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($jabatan);
    }

    public function getUnitKerja()
    {
        return response()->json(UnitKerja::select('id', 'nama_unit')->get());
    }

    /**
     * [ENRICHED QUERY] Mengambil Bidang dengan Metadata Lengkap
     * Menyertakan: Level, Parent, dan Daftar Jabatan yang SUDAH TERISI (Occupied).
     */
    public function getBidangByUnitKerja($unitKerjaId)
    {
        $bidangList = Bidang::where('unit_kerja_id', $unitKerjaId)
            ->with(['users' => function($query) {
                // Kita perlu tahu siapa saja yang aktif di bidang ini
                $query->where('is_active', true)->select('id', 'bidang_id', 'jabatan_id');
            }])
            ->orderBy('id', 'asc') // Urutkan ID biar rapi (Sekretariat biasanya di atas)
            ->get();

        $formatted = $bidangList->map(function ($item) {
            // Logic: Ambil ID jabatan apa saja yang sudah ada usernya di bidang ini
            $occupiedJabatanIds = $item->users->pluck('jabatan_id')->unique()->values()->toArray();

            return [
                'id' => $item->id,
                'nama_bidang' => $item->nama_bidang,
                'level' => $item->level, // Penting untuk logic Skenario 2,3,6,7,8
                'parent_id' => $item->parent_id, // Penting untuk Skenario 11
                'occupied_positions' => $occupiedJabatanIds, // Array ID Jabatan yang sudah terisi (Untuk disable opsi di frontend)
            ];
        });

        return response()->json($formatted);
    }

    /**
     * [THE BRAIN] Logika Pencarian Atasan (Hierarchy Decision Tree)
     * Mengimplementasikan 13 Skenario user.
     * * ID JABATAN (Acuan):
     * 1 = Kepala Badan
     * 2 = Sekretaris
     * 3 = Kepala Bidang
     * 4 = Kasub (Kepala Sub Bagian/Bidang)
     * 5 = Staf Pelaksana
     */
    public function getCalonAtasan(Request $request)
    {
        $unitKerjaId = $request->unit_kerja_id;
        $bidangId    = $request->bidang_id;
        $jabatanId   = $request->jabatan_id;

        // Validasi dasar
        if (!$unitKerjaId || !$jabatanId) return response()->json([]);

        // Setup Variabel Logic
        $userJabatanId = (int) $jabatanId;
        $targetBidang = Bidang::find($bidangId);
        
        // Default: tidak ada atasan
        $query = User::query()
            ->where('unit_kerja_id', $unitKerjaId)
            ->where('is_active', true)
            ->where('id', '!=', auth()->id()); // exclude diri sendiri

        // ----------------------------------------------------------------------
        // SKENARIO LEVEL TINGGI (Jabatan)
        // ----------------------------------------------------------------------

        // Case: Kepala Badan (ID 1) -> Tidak punya atasan di sistem
        if ($userJabatanId === 1) {
            return response()->json([]);
        }

        // Skenario 5 & 13: Sekretaris (2) atau Kabid (3) -> Atasan: Kepala Badan (1)
        // Skenario 13: Jika bidang "Unsur Pimpinan" -> Atasan: Kepala Badan (1)
        $levelName = strtolower($targetBidang->level ?? '');
        if ($userJabatanId === 2 || $userJabatanId === 3 || $levelName === 'unsur_pimpinan' || $levelName === 'unsur pimpinan') {
            $kaban = $query->where('jabatan_id', 1)->get();
            return $this->formatCandidates($kaban);
        }

        // ----------------------------------------------------------------------
        // SKENARIO BERDASARKAN LEVEL BIDANG & JABATAN USER
        // ----------------------------------------------------------------------
        
        // Pastikan data bidang ada untuk logic selanjutnya
        if (!$targetBidang) return response()->json([]);

        // Normalize level string
        $level = strtolower($targetBidang->level ?? '');
        
        // 1. JIKA BIDANG = SEKRETARIAT (Skenario 4 & 12)
        // Level: 'sekretariat'
        if (str_contains($level, 'sekretariat') && !str_contains($level, 'sub')) {
            // Jabatan apapun (Staf/Kasub) di Sekretariat -> Atasan: Sekretaris (2)
            $query->where('jabatan_id', 2)->where('bidang_id', $targetBidang->id);
        }

        // 2. JIKA BIDANG = SUB BIDANG / SUB BAGIAN (Skenario 2 & 11)
        // Level: 'sub_bidang' atau 'sub_bagian'
        elseif (str_contains($level, 'sub')) {
            
            // Case A: User adalah STAF (5) (Skenario 2)
            if ($userJabatanId === 5) {
                // Atasan: Kasub (4) di bidang yang sama
                $query->where('jabatan_id', 4)->where('bidang_id', $targetBidang->id);
            }
            
            // Case B: User adalah KASUB (4) (Skenario 11 - Sub Sekretaris/Sub Bidang)
            elseif ($userJabatanId === 4) {
                // Atasan: Kabid (3) atau Sekretaris (2) di PARENT bidang ini
                $parentId = $targetBidang->parent_id;
                
                if ($parentId) {
                    $query->where('bidang_id', $parentId)
                          ->whereIn('jabatan_id', [2, 3]); // Cari Sekretaris atau Kabid di parent
                } else {
                    return response()->json([]); // Error data: Sub bidang harus punya parent
                }
            }
        }

        // 3. JIKA BIDANG = BIDANG (Level Null atau 'bidang') (Skenario 3 & 8)
        // Level: 'bidang' atau null
        elseif ($level === 'bidang' || empty($level)) {
            
            // Case A: User adalah STAF (5) (Skenario 3)
            if ($userJabatanId === 5) {
                // Atasan: Kabid (3) di bidang yang sama
                $query->where('jabatan_id', 3)->where('bidang_id', $targetBidang->id);
            }
            
            // Case B: User adalah KASUB (4) (Logic tambahan Skenario 3)
            // Jika ada Kasub di level Bidang (jarang, tapi mungkin), dia lapor ke Kabid di bidang yang sama
            elseif ($userJabatanId === 4) {
                $query->where('jabatan_id', 3)->where('bidang_id', $targetBidang->id);
            }
        }

        // Eksekusi Query Final
        $candidates = $query->with('jabatan')->get();

        return $this->formatCandidates($candidates);
    }

    /**
     * Helper untuk format output JSON dropdown
     */
    private function formatCandidates($collection)
    {
        return response()->json($collection->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'display' => $user->name . ' (' . ($user->jabatan->nama_jabatan ?? '-') . ')'
            ];
        }));
    }
    
    // ===============================================================
    // CRUD METHODS (Unit Kerja, Bidang, Tupoksi)
    // ===============================================================

    public function indexUnitKerja() { return response()->json(UnitKerja::all()); }
    
    public function storeUnitKerja(Request $request) { 
        $request->validate(['nama_unit' => 'required']);
        UnitKerja::create($request->all()); 
        return response()->json(['message'=>'Unit Kerja berhasil disimpan']); 
    }
    
    // Updated: indexBidang menggunakan getBidangByUnitKerja logic jika ada filter
    public function indexBidang(Request $request) { 
        if($request->unit_kerja_id) {
            return $this->getBidangByUnitKerja($request->unit_kerja_id);
        }
        // Default all with unit kerja
        return response()->json(Bidang::with('unitKerja')->get()); 
    }

    public function storeBidang(Request $request) { 
        $request->validate(['nama_bidang' => 'required', 'unit_kerja_id' => 'required']);
        Bidang::create($request->all()); 
        return response()->json(['message'=>'Bidang berhasil disimpan']); 
    }

    public function updateBidang(Request $request, $id) { 
        Bidang::find($id)->update($request->all()); 
        return response()->json(['message'=>'Bidang berhasil diupdate']); 
    }

    public function destroyBidang($id) { 
        Bidang::destroy($id); 
        return response()->json(['message'=>'Bidang berhasil dihapus']); 
    }

    public function indexTupoksi(Request $request) { return response()->json(Tupoksi::with('bidang')->get()); }
    
    public function storeTupoksi(Request $request) { 
        Tupoksi::create($request->all()); 
        return response()->json(['message'=>'Tupoksi berhasil disimpan']); 
    }

    public function updateTupoksi(Request $request, $id) { 
        Tupoksi::find($id)->update($request->all()); 
        return response()->json(['message'=>'Tupoksi berhasil diupdate']); 
    }

    public function destroyTupoksi($id) { 
        Tupoksi::destroy($id); 
        return response()->json(['message'=>'Tupoksi berhasil dihapus']); 
    }
}