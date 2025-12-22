<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitKerjaController extends Controller
{
    /**
     * Menampilkan halaman daftar Unit Kerja.
     */
    public function index(Request $request)
    {
        // Jika request via AJAX (DataTables), kirim JSON
        if ($request->ajax()) {
            $data = UnitKerja::select(['id', 'nama_unit', 'created_at'])
                ->withCount(['bidang', 'jabatan', 'users']) // Hitung jumlah anak untuk info
                ->latest()
                ->get();

            return response()->json(['data' => $data]);
        }

        return view('admin.master.unit-kerja.index');
    }

    /**
     * Menyimpan Unit Kerja Baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_unit' => 'required|string|max:255|unique:unit_kerja,nama_unit',
        ], [
            'nama_unit.required' => 'Nama Unit Kerja wajib diisi.',
            'nama_unit.unique' => 'Nama Unit Kerja sudah ada.',
        ]);

        UnitKerja::create([
            'nama_unit' => $request->nama_unit
        ]);

        return response()->json(['message' => 'Unit Kerja berhasil ditambahkan.']);
    }

    /**
     * Update Unit Kerja.
     */
    public function update(Request $request, $id)
    {
        $unit = UnitKerja::findOrFail($id);

        $request->validate([
            'nama_unit' => 'required|string|max:255|unique:unit_kerja,nama_unit,' . $id,
        ]);

        $unit->update([
            'nama_unit' => $request->nama_unit
        ]);

        return response()->json(['message' => 'Unit Kerja berhasil diperbarui.']);
    }

    /**
     * Hapus Unit Kerja (Strict Mode).
     */
    public function destroy($id)
    {
        $unit = UnitKerja::withCount(['bidang', 'jabatan', 'users'])->findOrFail($id);

        // [LOGIC STRICT] Cek apakah ada anak?
        if ($unit->bidang_count > 0 || $unit->jabatan_count > 0 || $unit->users_count > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal hapus! Unit ini masih memiliki Bidang, Jabatan, atau Pegawai terkait.'
            ], 422);
        }

        $unit->delete(); // Soft Delete

        return response()->json(['message' => 'Unit Kerja berhasil dihapus.']);
    }
}