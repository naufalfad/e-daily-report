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
        // Urutkan biar rapi (Kaban -> Kabid -> Kasubid -> Staf)
        return response()->json(Jabatan::select('id', 'nama_jabatan')->orderBy('id', 'asc')->get());
    }

    public function getUnitKerja()
    {
        return response()->json(UnitKerja::select('id', 'nama_unit')->get());
    }

    public function getBidangByUnitKerja($unitKerjaId)
    {
        $data = Bidang::where('unit_kerja_id', $unitKerjaId)->select('id', 'nama_bidang')->get();
        return response()->json($data);
    }

    /**
     * [LOGIKA PINTAR] Mencari Kandidat Atasan
     * Berdasarkan Jabatan & Bidang yang dipilih calon pegawai.
     */
    public function getCalonAtasan(Request $request)
    {
        $unitKerjaId = $request->unit_kerja_id;
        $bidangId    = $request->bidang_id;
        $jabatanId   = $request->jabatan_id;

        // Jika data belum lengkap, return kosong dulu
        if (!$unitKerjaId || !$jabatanId) {
            return response()->json([]);
        }

        // 1. Identifikasi Level Jabatan
        $jabatan = Jabatan::find($jabatanId);
        if (!$jabatan) return response()->json([]);

        $namaJabatan = strtolower($jabatan->nama_jabatan);
        
        // Base Query: Cari pegawai di Unit Kerja yang sama
        $query = User::where('unit_kerja_id', $unitKerjaId)
                     ->where('id', '!=', auth()->id()); // Bukan diri sendiri (jika admin ngedit diri sendiri)

        // 2. Logika Filter Berdasarkan Hierarki
        
        // KASUS A: Input "Staf/Pelaksana"
        // Atasan = Kasubid (Eselon 4) di Bidang yang SAMA
        if (str_contains($namaJabatan, 'staf') || str_contains($namaJabatan, 'pelaksana')) {
            $query->whereHas('jabatan', function($q) {
                $q->where('nama_jabatan', 'ilike', '%Sub%') // Cari yang jabatannya mengandung "Sub"
                  ->orWhere('nama_jabatan', 'ilike', '%Kasi%');
            });
            
            // Wajib satu bidang. Kalau bidang beda, bukan atasannya.
            if ($bidangId) {
                $query->where('bidang_id', $bidangId);
            }
        }
        
        // KASUS B: Input "Kasubid/Kasi" (Kepala Sub)
        // Atasan = Kabid (Eselon 3) di Bidang yang SAMA
        elseif (str_contains($namaJabatan, 'sub') || str_contains($namaJabatan, 'seksi')) {
            $query->whereHas('jabatan', function($q) {
                $q->where('nama_jabatan', 'ilike', '%Kepala Bidang%')
                  ->orWhere('nama_jabatan', 'ilike', '%Kabid%');
            });

            if ($bidangId) {
                $query->where('bidang_id', $bidangId);
            }
        }

        // KASUS C: Input "Kepala Bidang" (Kabid)
        // Atasan = Kepala Badan / Kepala Dinas / Sekretaris
        elseif (str_contains($namaJabatan, 'kepala bidang') || str_contains($namaJabatan, 'kabid')) {
            $query->whereHas('jabatan', function($q) {
                $q->where('nama_jabatan', 'ilike', '%Kepala Badan%')
                  ->orWhere('nama_jabatan', 'ilike', '%Kepala Dinas%')
                  ->orWhere('nama_jabatan', 'ilike', '%Sekretaris%');
            });
            // Kabid lapor ke Kaban, tidak perlu filter bidang_id (karena Kaban menaungi semua bidang)
        }

        // KASUS D: Kepala Badan / Top Level
        // Tidak punya atasan di sistem (return kosong)
        elseif (str_contains($namaJabatan, 'kepala badan') || str_contains($namaJabatan, 'kepala dinas')) {
            return response()->json([]);
        }

        // Ambil data (ID & Nama & Jabatan untuk display dropdown)
        $candidates = $query->with('jabatan')->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name . ' (' . ($user->jabatan->nama_jabatan ?? '-') . ')'
            ];
        });

        return response()->json($candidates);
    }
    
    // --- CRUD METHODS ---

    public function indexUnitKerja() { return response()->json(UnitKerja::all()); }
    public function storeUnitKerja(Request $request) { UnitKerja::create($request->all()); return response()->json(['message'=>'Saved']); }
    
    public function indexBidang(Request $request) { 
        $q = Bidang::with('unitKerja');
        if($request->unit_kerja_id) $q->where('unit_kerja_id', $request->unit_kerja_id);
        return response()->json($q->get()); 
    }
    public function storeBidang(Request $request) { Bidang::create($request->all()); return response()->json(['message'=>'Saved']); }
    public function updateBidang(Request $request, $id) { Bidang::find($id)->update($request->all()); return response()->json(['message'=>'Updated']); }
    public function destroyBidang($id) { Bidang::destroy($id); return response()->json(['message'=>'Deleted']); }

    public function indexTupoksi(Request $request) { return response()->json(Tupoksi::with('bidang')->get()); }
    public function storeTupoksi(Request $request) { Tupoksi::create($request->all()); return response()->json(['message'=>'Saved']); }
    public function updateTupoksi(Request $request, $id) { Tupoksi::find($id)->update($request->all()); return response()->json(['message'=>'Updated']); }
    public function destroyTupoksi($id) { Tupoksi::destroy($id); return response()->json(['message'=>'Deleted']); }
}