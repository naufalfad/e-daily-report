<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\Role;
use App\Models\User;
use App\Models\Bidang; // [BARU]
use App\Models\Tupoksi; // [BARU]
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // [BARU]

class MasterDataController extends Controller
{
    // =========================================================================
    // BAGIAN PENGAMBILAN DATA DROPDOWN (EKSISTING)
    // =========================================================================

    // Mengambil semua roles (Dropdown)
    public function getRoles() { return response()->json(Role::all()); }
    
    // Mengambil semua jabatan (Dropdown)
    public function getJabatan() { return response()->json(Jabatan::all()); }
    
    // Mengambil semua unit kerja (Dropdown)
    public function getUnitKerja() { return response()->json(UnitKerja::all()); }
    
    // Mengambil user untuk dijadikan atasan (Dropdown)
    public function getCalonAtasan() { return response()->json(User::select('id','name','nip')->get()); }

    // =========================================================================
    // [BARU] BAGIAN PENGAMBILAN DATA DROPDOWN (CASCADING)
    // =========================================================================

    /**
     * [BARU] Ambil list Bidang berdasarkan Unit Kerja
     * Penting: Dipanggil di Form User Management setelah Admin memilih Unit Kerja.
     */
    public function getBidangByUnitKerja($unitKerjaId)
    {
        $bidang = Bidang::where('unit_kerja_id', $unitKerjaId)
                        ->select('id', 'nama_bidang')
                        ->get();
        return response()->json($bidang);
    }

    /**
     * [BARU] Ambil list Tupoksi berdasarkan Bidang
     * Penting: Dipanggil di Form LKH Pegawai (berdasarkan bidang pegawai ybs).
     */
    public function getTupoksiByBidang($bidangId)
    {
        $tupoksi = Tupoksi::where('bidang_id', $bidangId)
                         ->select('id', 'uraian_tugas')
                         ->get();
        return response()->json($tupoksi);
    }

    // =========================================================================
    // [BARU] BAGIAN CRUD (MANAJEMEN DATA OLEH ADMIN)
    // =========================================================================

    // --- CRUD UNIT KERJA ---
    public function indexUnitKerja() 
    {
        return response()->json(UnitKerja::withCount('bidang', 'users')->get());
    }

    public function storeUnitKerja(Request $request)
    {
        $validator = Validator::make($request->all(), ['nama' => 'required|string|unique:unit_kerja,nama']);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        
        $unitKerja = UnitKerja::create($validator->validated());
        return response()->json($unitKerja, 201);
    }

    // --- CRUD BIDANG ---
    public function indexBidang(Request $request)
    {
        $query = Bidang::with('unitKerja');
        if ($request->has('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }
        return response()->json($query->get());
    }

    public function storeBidang(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'nama_bidang'   => 'required|string|max:255',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        
        $bidang = Bidang::create($validator->validated());
        return response()->json($bidang->load('unitKerja'), 201);
    }

    public function updateBidang(Request $request, $id)
    {
        $bidang = Bidang::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'nama_bidang'   => 'required|string|max:255',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $bidang->update($validator->validated());
        return response()->json($bidang->load('unitKerja'));
    }

    public function destroyBidang($id)
    {
        // Tambahkan pengecekan jika bidang masih punya user
        $bidang = Bidang::withCount('users')->findOrFail($id);
        if ($bidang->users_count > 0) {
            return response()->json(['message' => 'Gagal hapus, masih ada user di bidang ini'], 422);
        }
        $bidang->delete();
        return response()->json(['message' => 'Bidang berhasil dihapus']);
    }

    // --- CRUD TUPOKSI ---
    public function indexTupoksi(Request $request)
    {
        $validator = Validator::make($request->all(), ['bidang_id' => 'required|exists:bidang,id']);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $tupoksi = Tupoksi::where('bidang_id', $request->bidang_id)->get();
        return response()->json($tupoksi);
    }

    public function storeTupoksi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bidang_id'    => 'required|exists:bidang,id',
            'uraian_tugas' => 'required|string',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        
        $tupoksi = Tupoksi::create($validator->validated());
        return response()->json($tupoksi, 201);
    }

    public function updateTupoksi(Request $request, $id)
    {
        $tupoksi = Tupoksi::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'uraian_tugas' => 'required|string',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $tupoksi->update($validator->validated());
        return response()->json($tupoksi);
    }

    public function destroyTupoksi($id)
    {
        Tupoksi::findOrFail($id)->delete();
        return response()->json(['message' => 'Tupoksi berhasil dihapus']);
    }
}